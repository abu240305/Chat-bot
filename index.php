<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DIPA-Bot - Chatbot Cerdas Layanan Akademik Universitas Dipa Makassar">
    <meta name="author" content="Mohammad Ali Riedza">
    <title>DIPA-Bot - Layanan Akademik UNDIPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
            <div class="status-indicator">
                <span class="status-dot"></span>
                <span class="status-text">Online</span>
            </div>
        </header>

        <div class="chat-body" id="chatBody">
            
            <div class="message-wrapper bot-message">
                <div class="message-bubble">
                    <p>Halo! Saya <strong>DIPA-Bot</strong>, asisten virtual layanan akademik Universitas Dipa Makassar.</p>
                    <p>Ada yang bisa saya bantu? Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.</p>
                </div>
                <span class="message-time">Sekarang</span>
            </div>

            <div class="quick-replies" id="quickReplies">
                <button class="quick-reply-btn" data-text="Bagaimana cara mengisi KRS?">KRS</button>
                <button class="quick-reply-btn" data-text="Bagaimana cara melihat jadwal kuliah?">Jadwal</button>
                <button class="quick-reply-btn" data-text="Apa syarat mengambil skripsi?">Skripsi</button>
                <button class="quick-reply-btn" data-text="Dimana saya bisa download template skripsi?">Template</button>
                <button class="quick-reply-btn" data-text="Kapan jadwal UAS semester ini?">UAS</button>
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
