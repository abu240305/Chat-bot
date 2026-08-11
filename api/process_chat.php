<?php

session_start();

$rateLimitMax = 5;
$rateLimitWindow = 10;

if (!isset($_SESSION['last_chat_time']) || !is_numeric($_SESSION['last_chat_time'])) {
    $_SESSION['last_chat_time'] = 0;
    $_SESSION['chat_count'] = 0;
}

if ((time() - $_SESSION['last_chat_time']) >= $rateLimitWindow) {
    $_SESSION['last_chat_time'] = time();
    $_SESSION['chat_count'] = 1;
} else {
    $_SESSION['chat_count']++;
}

if ($_SESSION['chat_count'] > $rateLimitMax) {
    echo json_encode([
        'success' => false,
        'message' => 'Terlalu banyak permintaan, tunggu beberapa saat.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/validator.php';
require_once __DIR__ . '/../core/Preprocessing.php';
require_once __DIR__ . '/../core/CosineSimilarity.php';

try {
    $db = Database::getInstance()->getConnection();

    $rawInput = file_get_contents('php://input');

    if (empty($rawInput)) {
        throw new Exception('Request body kosong');
    }

    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Format JSON tidak valid');
    }

if (!isset($data['message']) || empty(trim($data['message']))) {
        if (!isset($data['pertanyaan']) || empty(trim($data['pertanyaan']))) {
            echo json_encode([
                'success' => false,
                'message' => 'Pesan tidak boleh kosong'
            ]);
            exit;
        }
        $userMessage = trim($data['pertanyaan']);
    } else {
        $userMessage = trim($data['message']);
    }

    $userMessage = strip_tags($userMessage);
    $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');

    $valErr = valChatMessage($userMessage);
    if ($valErr !== '') {
        echo json_encode([
            'success' => false,
            'message' => $valErr
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

$threshold = 0.25;
$fallbackMessage = "Maaf, DIPA-Bot belum memahami pertanyaan tersebut. Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.";

try {
    $stmt = $db->prepare("SELECT nilai FROM tb_pengaturan WHERE nama = 'fallback' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && trim($row['nilai']) !== '') {
        $fallbackMessage = $row['nilai'];
    }
} catch (Exception $e) {
    error_log("Fallback Setting Error: " . $e->getMessage());
}

    $logChat = function ($question, $answer, $score, $matchedId) use ($db) {
        try {
            $ipAddress = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP']
                : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';

            $stmt = $db->prepare("INSERT INTO tb_log_chat
                (pertanyaan_user, jawaban_bot, skor_similarity, id_pengetahuan_matched, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$question, $answer, $score, $matchedId, $ipAddress, $userAgent]);
        } catch (PDOException $e) {
            error_log("Error logging chat: " . $e->getMessage());
        }
    };

    $preprocessing = new Preprocessing($db);
    $cosine = new CosineSimilarity();

    $tokens = $preprocessing->process($userMessage, $db);

    if (empty($tokens)) {
        $logChat($userMessage, $fallbackMessage, 0, null);
        echo json_encode([
            'success' => true,
            'jawaban' => $fallbackMessage,
            'file_lampiran' => null,
            'skor' => 0,
            'matched_id' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tfCounts = array_count_values($tokens);
    $totalTerms = count($tokens);

    $docVectors = [];
    $df = [];
    $stmt = $db->query("SELECT id_pengetahuan, term, bobot_tfidf FROM tb_vektor_tfidf");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $did = (int)$row['id_pengetahuan'];
        $term = $row['term'];
        $docVectors[$did][$term] = (float)$row['bobot_tfidf'];
        $df[$term] = isset($df[$term]) ? $df[$term] + 1 : 1;
    }

    $N = count($docVectors);

    $queryVector = [];
    if ($N > 0) {
        foreach ($tfCounts as $term => $count) {
            if (!isset($df[$term]) || $df[$term] <= 0) {
                continue;
            }
            $tf = $totalTerms > 0 ? $count / $totalTerms : 0;
            $idf = log($N / $df[$term]);
            $queryVector[$term] = $tf * $idf;
        }
    }

    $bestScore = 0.0;
    $bestDocId = null;
    foreach ($docVectors as $docId => $docVector) {
        $score = $cosine->calculate($queryVector, $docVector);
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestDocId = $docId;
        }
    }

    if ($bestDocId !== null && $bestScore >= $threshold) {
        $stmt = $db->prepare("SELECT id_pengetahuan, jawaban, file_lampiran FROM tb_pengetahuan WHERE id_pengetahuan = ? LIMIT 1");
        $stmt->execute([$bestDocId]);
        $matched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($matched) {
            $fileLampiran = null;
            if (!empty($matched['file_lampiran'])) {
                $filePath = __DIR__ . '/../assets/downloads/' . $matched['file_lampiran'];
                if (file_exists($filePath)) {
                    $fileLampiran = $matched['file_lampiran'];
                }
            }

            $logChat($userMessage, $matched['jawaban'], $bestScore, $bestDocId);

            echo json_encode([
                'success' => true,
                'jawaban' => $matched['jawaban'],
                'file_lampiran' => $fileLampiran,
                'skor' => $bestScore,
                'matched_id' => $bestDocId
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    $logChat($userMessage, $fallbackMessage, $bestScore, null);

    echo json_encode([
        'success' => true,
        'jawaban' => $fallbackMessage,
        'file_lampiran' => null,
        'skor' => $bestScore,
        'matched_id' => null
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan server. Silakan coba lagi.'
    ]);
}