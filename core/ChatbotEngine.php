<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Preprocessing.php';
require_once __DIR__ . '/Tfidf.php';
require_once __DIR__ . '/CosineSimilarity.php';

class ChatbotEngine {
    
    private $db;
    private $preprocessing;
    private $tfidf;
    private $cosine;
    private $threshold = 0.25;
    private $fallbackMessage = "Maaf, DIPA-Bot belum memahami pertanyaan tersebut. Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.";
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->preprocessing = new Preprocessing($this->db);
        $this->tfidf = new Tfidf();
        $this->cosine = new CosineSimilarity();
    }
    
    public function processQuery($userQuery) {
        if (empty($userQuery) || strlen($userQuery) > 250) {
            return [
                'success' => false,
                'message' => 'Input tidak valid. Pastikan teks 1-250 karakter.',
                'skor' => 0,
                'matched_id' => null
            ];
        }
        
        $cleanQuery = $this->sanitizeInput($userQuery);
        
        $processedQuery = $this->preprocessing->process($cleanQuery, $this->db);
        
        if (empty($processedQuery)) {
            return [
                'success' => true,
                'jawaban' => $this->fallbackMessage,
                'file_lampiran' => null,
                'skor' => 0,
                'matched_id' => null
            ];
        }
        
        $knowledgeBase = $this->getKnowledgeBase();
        
        if (empty($knowledgeBase)) {
            return [
                'success' => true,
                'jawaban' => 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.',
                'file_lampiran' => null,
                'skor' => 0,
                'matched_id' => null
            ];
        }
        
        $allDocuments = [];
        foreach ($knowledgeBase as $kb) {
            $allDocuments[] = $this->preprocessing->process($kb['pertanyaan'], $this->db);
        }
        
        $this->tfidf->buildVocabulary($allDocuments);
        
        $queryVector = $this->tfidf->calculateTFIDF($processedQuery);
        
        $documentVectors = [];
        foreach ($allDocuments as $doc) {
            $documentVectors[] = $this->tfidf->calculateTFIDF($doc);
        }
        
        $bestMatch = $this->cosine->findBestMatch($queryVector, $documentVectors);
        
        $score = $bestMatch['score'];
        $index = $bestMatch['index'];
        
        if ($score >= $this->threshold && $index >= 0) {
            $matchedData = $knowledgeBase[$index];
            
            $fileLampiran = null;
            if (!empty($matchedData['file_lampiran'])) {
                $filePath = __DIR__ . '/../assets/downloads/' . $matchedData['file_lampiran'];
                if (file_exists($filePath)) {
                    $fileLampiran = $matchedData['file_lampiran'];
                }
            }
            
            $this->logChat(
                $userQuery,
                $matchedData['jawaban'],
                $score,
                $matchedData['id_pengetahuan']
            );
            
            return [
                'success' => true,
                'jawaban' => $matchedData['jawaban'],
                'file_lampiran' => $fileLampiran,
                'skor' => $score,
                'matched_id' => $matchedData['id_pengetahuan']
            ];
        } else {
            $this->logChat($userQuery, $this->fallbackMessage, $score, null);
            
            return [
                'success' => true,
                'jawaban' => $this->fallbackMessage,
                'file_lampiran' => null,
                'skor' => $score,
                'matched_id' => null
            ];
        }
    }
    
    private function sanitizeInput($input) {
        $input = strip_tags($input);
        $input = trim($input);
        $input = preg_replace('/\s+/', ' ', $input);
        return $input;
    }
    
    private function getKnowledgeBase() {
        try {
            $sql = "SELECT id_pengetahuan, pertanyaan, jawaban, file_lampiran 
                    FROM tb_pengetahuan 
                    ORDER BY id_pengetahuan ASC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching knowledge base: " . $e->getMessage());
            return [];
        }
    }
    
    private function logChat($userQuestion, $botAnswer, $score, $matchedId) {
        try {
            $ipAddress = $this->getClientIP();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
            
            $sql = "INSERT INTO tb_log_chat 
                    (pertanyaan_user, jawaban_bot, skor_similarity, id_pengetahuan_matched, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userQuestion,
                $botAnswer,
                $score,
                $matchedId,
                $ipAddress,
                $userAgent
            ]);
            
        } catch (PDOException $e) {
            error_log("Error logging chat: " . $e->getMessage());
        }
    }
    
    private function getClientIP() {
        $ipAddress = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ipAddress;
    }
    
    public function setThreshold($threshold) {
        if ($threshold >= 0 && $threshold <= 1) {
            $this->threshold = $threshold;
        }
    }
    
    public function getThreshold() {
        return $this->threshold;
    }
}
