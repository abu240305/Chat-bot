<?php

session_start();

$rateLimitMax = 5;
$rateLimitWindow = 10;

$now = time();

if (!isset($_SESSION['rate_requests']) || !is_array($_SESSION['rate_requests'])) {
    $_SESSION['rate_requests'] = [];
}

$_SESSION['rate_requests'] = array_filter($_SESSION['rate_requests'], function ($timestamp) use ($now, $rateLimitWindow) {
    return $timestamp > ($now - $rateLimitWindow);
});

if (count($_SESSION['rate_requests']) >= $rateLimitMax) {
    echo json_encode([
        'success' => false,
        'message' => 'Terlalu banyak permintaan, tunggu beberapa saat.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['rate_requests'][] = $now;

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
        echo json_encode([
            'success' => false,
            'message' => 'Pesan tidak boleh kosong'
        ]);
        exit;
    }

    $userMessage = trim($data['message']);

    $userMessage = strip_tags($userMessage);
    $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');

    if (strlen($userMessage) < 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Pesan terlalu pendek. Minimal 1 karakter.'
        ]);
        exit;
    }

    if (strlen($userMessage) > 250) {
        echo json_encode([
            'success' => false,
            'message' => 'Pesan terlalu panjang. Maksimal 250 karakter.'
        ]);
        exit;
    }

    $threshold = 0.25;
    $fallbackMessage = "Maaf, DIPA-Bot belum memahami pertanyaan tersebut. Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.";

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
    $queryVector = [];
    foreach ($tfCounts as $term => $count) {
        $queryVector[$term] = $totalTerms > 0 ? $count / $totalTerms : 0;
    }

    $docVectors = [];
    $stmt = $db->query("SELECT id_pengetahuan, term, bobot_tfidf FROM tb_vektor_tfidf");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $docVectors[(int)$row['id_pengetahuan']][$row['term']] = (float)$row['bobot_tfidf'];
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