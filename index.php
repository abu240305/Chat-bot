<?php
require_once __DIR__ . '/config/database.php';

$quickReplies = [];
try {
    $db = Database::getInstance()->getConnection();

    $sql = "SELECT p.kategori, p.pertanyaan
            FROM tb_pengetahuan p
            INNER JOIN (
                SELECT kategori, MIN(id_pengetahuan) AS id_pertama
                FROM tb_pengetahuan
                GROUP BY kategori
            ) x ON p.id_pengetahuan = x.id_pertama
            ORDER BY p.id_pengetahuan ASC
            LIMIT 5";

    $quickReplies = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Quick Reply Error: " . $e->getMessage());
    $quickReplies = [];
}

if (empty($quickReplies)) {
    $quickReplies = [
        ['kategori' => 'KRS',        'pertanyaan' => 'Bagaimana cara mengisi KRS?'],
        ['kategori' => 'Jadwal',     'pertanyaan' => 'Bagaimana cara melihat jadwal kuliah?'],
        ['kategori' => 'Skripsi',    'pertanyaan' => 'Apa syarat mengambil skripsi?'],
        ['kategori' => 'Template',   'pertanyaan' => 'Dimana saya bisa download template skripsi?'],
        ['kategori' => 'UAS',        'pertanyaan' => 'Kapan jadwal UAS semester ini?']
    ];
}

$greeting = '';
try {
    $stmt = $db->prepare("SELECT nilai FROM tb_pengaturan WHERE nama = 'greeting' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $greeting = $row['nilai'];
    }
} catch (Exception $e) {
    error_log("Greeting Error: " . $e->getMessage());
}

if ($greeting === '') {
    $greeting = "Halo! Saya DIPA-Bot, asisten virtual layanan akademik Universitas Dipa Makassar.\nAda yang bisa saya bantu? Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DIPA-Bot - Chatbot Cerdas Layanan Akademik Universitas Dipa Makassar">
    <meta name="author" content="Mohammad Ali Riedza">
    <title>DIPA-Bot - Layanan Akademik UNDIPA</title>
    <link rel="stylesheet" href="assets/css/style.css?v=4">
</head>
<body>
    
    <div class="chat-container">
        
        <header class="chat-header">
            <div class="header-left">
                <div class="logo-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <div class="header-info">
                    <h1 class="header-title">DIPA-Bot</h1>
                    <p class="header-subtitle">Layanan Akademik UNDIPA</p>
                </div>
            </div>
                        <div class="header-right">
                <div class="status-indicator">
                    <span class="status-dot"></span>
                    <span class="status-text">Online</span>
                </div>
                <button type="button" id="resetChat" class="reset-btn" title="Hapus percakapan" aria-label="Hapus percakapan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 4 1 10 7 10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                </button>
            </div>
        </header>

        <div class="chat-body" id="chatBody">
            
            <div class="message-wrapper bot-message" id="greetingMsg">
                <div class="message-bubble">
                    <p><?php echo nl2br(htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8')); ?></p>
                </div>
                <span class="message-time">Sekarang</span>
            </div>

            <div class="quick-replies" id="quickReplies">
                <?php foreach ($quickReplies as $qr): ?>
                    <button class="quick-reply-btn" data-text="<?php echo htmlspecialchars($qr['pertanyaan'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($qr['kategori'], ENT_QUOTES, 'UTF-8'); ?></button>
                <?php endforeach; ?>
            </div>

        </div>

        <div class="chat-footer">
            <form id="chatForm" class="chat-form">
                <input 
                    type="text" 
                    id="userInput" 
                    class="chat-input" 
                    placeholder="Ketik pesan (maks 250 karakter)..." 
                    maxlength="250"
                    autocomplete="off"
                    required
                >
                <button type="submit" id="sendBtn" class="send-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
            <p class="char-counter">
                <span id="charCount">0</span>/250 karakter
            </p>
        </div>

    </div>

    <div class="toast" id="toast">
        <span id="toastMessage"></span>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html>
