<?php

class Preprocessing {
    
    private $stopwords = [
        'yang', 'untuk', 'pada', 'ke', 'para', 'namun', 'menurut', 'antara', 'dia', 'dua', 'ia',
        'seperti', 'jika', 'jika', 'sehingga', 'kembali', 'dan', 'ini', 'itu', 'adalah', 'ada',
        'dari', 'dalam', 'akan', 'pada', 'juga', 'dengan', 'untuk', 'telah', 'tersebut', 'dapat',
        'oleh', 'sebagai', 'atau', 'saat', 'sangat', 'bisa', 'sudah', 'saya', 'si', 'kami', 'kita',
        'mereka', 'anda', 'kamu', 'ia', 'apa', 'siapa', 'mana', 'kenapa', 'mengapa', 'kapan',
        'di', 'ke', 'dari', 'dalam', 'luar', 'atas', 'bawah', 'depan', 'belakang', 'samping',
        'the', 'a', 'an', 'of', 'to', 'in', 'for', 'on', 'at', 'by', 'with', 'is', 'are', 'was'
    ];

    private $normalizationDict = [];

    public function __construct($db = null) {
        if ($db) {
            $this->loadNormalizationFromDB($db);
        } else {
            $this->normalizationDict = [
                'gimana' => 'bagaimana',
                'gmn' => 'bagaimana',
                'gmana' => 'bagaimana',
                'krsan' => 'krs',
                'krrs' => 'krs',
                'dospem' => 'dosen pembimbing',
                'pa' => 'pembimbing akademik',
                'prodi' => 'program studi',
                'skripsweet' => 'skripsi',
                'skripsih' => 'skripsi',
                'templat' => 'template',
                'jadwa' => 'jadwal',
                'jadwall' => 'jadwal',
                'donlot' => 'unduh',
                'download' => 'unduh',
                'ekskul' => 'ekstrakurikuler',
                'ukm' => 'unit kegiatan mahasiswa',
                'minta' => 'minta',
                'dong' => '',
                'min' => '',
                'gan' => '',
                'bro' => '',
                'sis' => ''
            ];
        }
    }

    private function loadNormalizationFromDB($db) {
        try {
            $sql = "SELECT kata_asli, kata_baku FROM tb_kata_kunci";
            $stmt = $db->query($sql);
            $results = $stmt->fetchAll();
            
            foreach ($results as $row) {
                $this->normalizationDict[strtolower($row['kata_asli'])] = strtolower($row['kata_baku']);
            }
        } catch (Exception $e) {
            error_log("Error loading normalization dict: " . $e->getMessage());
        }
    }

    public function caseFolding($text) {
        return strtolower($text);
    }

    public function filtering($text) {
        $text = preg_replace('/[^a-z0-9\s]/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    public function normalization($text) {
        $words = explode(' ', $text);
        $normalized = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            
            if (isset($this->normalizationDict[$word])) {
                $replacement = $this->normalizationDict[$word];
                if (!empty($replacement)) {
                    $normalized[] = $replacement;
                }
            } else {
                $normalized[] = $word;
            }
        }
        
        return implode(' ', $normalized);
    }

    public function tokenizing($text) {
        $text = preg_replace('/\s+/', ' ', $text);
        $tokens = explode(' ', trim($text));
        return array_filter($tokens, function($token) {
            return !empty($token);
        });
    }

    public function stopwordRemoval($tokens) {
        return array_values(array_filter($tokens, function($token) {
            return !in_array(strtolower($token), $this->stopwords);
        }));
    }

    public function stemming($tokens) {
        $stemmed = [];
        foreach ($tokens as $token) {
            $stemmed[] = $this->stemWord($token);
        }
        return $stemmed;
    }

    private function stemWord($word) {
        $word = strtolower($word);
        $original = $word;
        
        if (strlen($word) <= 3) {
            return $word;
        }

        $word = $this->removePrefixes($word);
        $word = $this->removeSuffixes($word);
        
        if (strlen($word) < 3) {
            return $original;
        }
        
        return $word;
    }

    private function removePrefixes($word) {
        $prefixes = ['meng', 'meny', 'men', 'mem', 'me', 'peng', 'pen', 'pem', 'di', 'ke', 'ter', 'ber', 'per', 'se'];
        
        foreach ($prefixes as $prefix) {
            if (substr($word, 0, strlen($prefix)) === $prefix) {
                $stem = substr($word, strlen($prefix));
                if (strlen($stem) >= 3) {
                    return $stem;
                }
            }
        }
        
        return $word;
    }

    private function removeSuffixes($word) {
        $suffixes = ['kan', 'an', 'i', 'nya', 'lah', 'kah'];
        
        foreach ($suffixes as $suffix) {
            if (substr($word, -strlen($suffix)) === $suffix) {
                $stem = substr($word, 0, -strlen($suffix));
                if (strlen($stem) >= 3) {
                    return $stem;
                }
            }
        }
        
        return $word;
    }

    public function process($text, $db = null) {
        if ($db && empty($this->normalizationDict)) {
            $this->loadNormalizationFromDB($db);
        }
        
        $text = $this->caseFolding($text);
        $text = $this->filtering($text);
        $text = $this->normalization($text);
        $tokens = $this->tokenizing($text);
        $tokens = $this->stopwordRemoval($tokens);
        $tokens = $this->stemming($tokens);
        
        return $tokens;
    }
}
