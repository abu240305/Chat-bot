const chatBody = document.getElementById('chatBody');
const chatForm = document.getElementById('chatForm');
const userInput = document.getElementById('userInput');
const sendBtn = document.getElementById('sendBtn');
const charCount = document.getElementById('charCount');
const toast = document.getElementById('toast');
const quickReplies = document.querySelectorAll('.quick-reply-btn');

let isProcessing = false;

userInput.addEventListener('input', function() {
    if (this.value.length > 250) {
        this.value = this.value.slice(0, 250);
    }

    const raw = this.value.length;
    const length = this.value.trim().length;

    charCount.textContent = raw;
    charCount.classList.toggle('warn', raw > 200 && raw <= 250);
    charCount.classList.toggle('error', raw === 250);

    if (length > 0 && length <= 250 && !isProcessing) {
        sendBtn.disabled = false;
        sendBtn.title = '';
    } else {
        sendBtn.disabled = true;
        sendBtn.title = length === 0 ? 'Ketik pertanyaan terlebih dahulu' : '';
    }
});

userInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!sendBtn.disabled) {
            chatForm.dispatchEvent(new Event('submit'));
        }
    }
});

chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const message = userInput.value.trim();
    
    if (message.length === 0 || message.length > 250 || isProcessing) {
        return;
    }
    
    sendMessage(message);
});

quickReplies.forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.getAttribute('data-text');
        userInput.value = text;
        userInput.dispatchEvent(new Event('input'));
        sendMessage(text);
    });
});

const resetBtn = document.getElementById('resetChat');
resetBtn.addEventListener('click', function() {
    document.querySelectorAll('#chatBody .message-wrapper').forEach(el => {
        if (el.id !== 'greetingMsg') {
            el.remove();
        }
    });

    const typing = document.getElementById('typingIndicator');
    if (typing) {
        typing.remove();
    }

    isProcessing = false;
    sendBtn.disabled = true;
    userInput.disabled = false;
    userInput.value = '';
    charCount.textContent = '0';
    quickReplies.forEach(btn => btn.style.display = '');
    userInput.focus();
    scrollToBottom();
});

function sendMessage(message) {
    isProcessing = true;
    sendBtn.disabled = true;
    userInput.disabled = true;
    
    appendUserMessage(message);
    
    userInput.value = '';
    charCount.textContent = '0';
    
    showTypingIndicator();
    
    fetch('api/process_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            message: message
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Respons server tidak valid');
        }
        return response.json();
    })
    .then(data => {
        removeTypingIndicator();
        
        if (data.success) {
            appendBotMessage(data.jawaban, data.file_lampiran);
        } else {
            appendBotMessage(data.message || 'Maaf, terjadi kesalahan sistem.');
            if (data.message && data.message.indexOf('Terlalu banyak') !== -1) {
                showToast(data.message);
            }
        }
        
        isProcessing = false;
        userInput.disabled = false;
        userInput.focus();
    })
    .catch(error => {
        removeTypingIndicator();
        console.error('Error:', error);
        showToast('Koneksi gagal. Periksa jaringan internet Anda.');
        appendRetryMessage(message);
        
        isProcessing = false;
        userInput.disabled = false;
        userInput.focus();
    });
}

function appendUserMessage(text) {
    const wrapper = document.createElement('div');
    wrapper.className = 'message-wrapper user-message';
    
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    
    const p = document.createElement('p');
    p.textContent = text;
    bubble.appendChild(p);
    
    const time = document.createElement('span');
    time.className = 'message-time';
    time.textContent = getCurrentTime();
    
    wrapper.appendChild(bubble);
    wrapper.appendChild(time);
    
    chatBody.appendChild(wrapper);
    scrollToBottom();
}

function formatBotResponse(text) {
    if (!text) return '';

    // 1. Pisahkan catatan / bantuan / footer bila ada
    let noteText = '';
    const notePattern = /(Jika mengalami kendala[^\n]+|Catatan:[^\n]+|Bantuan:[^\n]+|\*Bantuan[^\n]+|\*Catatan[^\n]+|\*Silakan unduh[^\n]+|\*Dokumen[^\n]+|\*Template[^\n]+|\*Kalender[^\n]+|\*Formulir[^\n]+)/i;
    const noteMatch = text.match(notePattern);
    let mainText = text;
    if (noteMatch) {
        noteText = noteMatch[0];
        mainText = text.replace(noteMatch[0], '').trim();
    }

    // 2. Deteksi poin / langkah bernomor (1) atau 1. atau bullet - / •)
    let intro = '';
    let steps = [];
    const stepSplitRegex = /(?:^|\s+)(?:\d+[\.\)]|[•\-])\s+/;
    if (stepSplitRegex.test(mainText)) {
        const parts = mainText.split(/(?=(?:^|\s+)(?:\d+[\.\)]|[•\-])\s+)/);
        intro = parts[0].trim();
        for (let i = 1; i < parts.length; i++) {
            let item = parts[i].trim();
            if (item) {
                const numMatch = item.match(/^(\d+[\.\)]|[•\-])\s+(.*)/s);
                if (numMatch) {
                    let badge = numMatch[1].replace(')', '.');
                    steps.push({ badge: badge, text: numMatch[2].trim() });
                } else {
                    steps.push({ badge: '•', text: item });
                }
            }
        }
    } else {
        intro = mainText;
    }

    function formatInline(str) {
        let safe = escapeHtml(str);
        // Tebal: **teks** -> <strong>teks</strong>
        safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Miring: *teks* -> <em>teks</em>
        safe = safe.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // URL otomatis jadi tautan interaktif yang rapi
        safe = safe.replace(/(https?:\/\/[^\s<\)]+)/g, function(url) {
            let clean = url.replace(/^https?:\/\//, '').replace(/\/$/, '');
            return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="chat-link-badge"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>${clean}</a>`;
        });
        return safe.replace(/\n/g, '<br>');
    }

    let html = '';
    if (intro) {
        html += `<div class="chat-intro-text">${formatInline(intro)}</div>`;
    }

    if (steps.length > 0) {
        html += `<div class="chat-steps-container">`;
        steps.forEach(s => {
            html += `<div class="chat-step-item">
                <span class="chat-step-badge">${s.badge}</span>
                <span class="chat-step-content">${formatInline(s.text)}</span>
            </div>`;
        });
        html += `</div>`;
    }

    if (noteText) {
        let cleanNote = noteText.replace(/^[\*\_]+|[\*\_]+$/g, '').trim();
        html += `<div class="chat-notice-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>${formatInline(cleanNote)}</span>
        </div>`;
    }

    return html;
}

function appendBotMessage(text, fileLink = null) {
    const wrapper = document.createElement('div');
    wrapper.className = 'message-wrapper bot-message';
    
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    
    const content = document.createElement('div');
    content.className = 'bot-response-content';
    content.innerHTML = formatBotResponse(text);
    bubble.appendChild(content);
    
    if (fileLink && fileLink.trim() !== '') {
        const downloadLink = document.createElement('a');
        downloadLink.href = 'assets/downloads/' + fileLink;
        downloadLink.className = 'download-btn';
        downloadLink.target = '_blank';
        downloadLink.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Unduh Dokumen
        `;
        bubble.appendChild(downloadLink);
    }
    
    const time = document.createElement('span');
    time.className = 'message-time';
    time.textContent = getCurrentTime();
    
    wrapper.appendChild(bubble);
    wrapper.appendChild(time);
    
    chatBody.appendChild(wrapper);
    scrollToBottom();
}

function showTypingIndicator() {
    const wrapper = document.createElement('div');
    wrapper.className = 'message-wrapper bot-message';
    wrapper.id = 'typingIndicator';
    
    const indicator = document.createElement('div');
    indicator.className = 'typing-indicator';
    indicator.innerHTML = `
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    `;
    
    wrapper.appendChild(indicator);
    chatBody.appendChild(wrapper);
    scrollToBottom();
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

function scrollToBottom() {
    chatBody.scrollTo({
        top: chatBody.scrollHeight,
        behavior: 'smooth'
    });
}

function appendRetryMessage(text) {
    const wrapper = document.createElement('div');
    wrapper.className = 'message-wrapper bot-message bubble-error';

    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';

    const p = document.createElement('p');
    p.textContent = 'Maaf, koneksi ke server gagal. Pesanmu belum terkirim.';
    bubble.appendChild(p);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'retry-btn';
    btn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="1 4 1 10 7 10"></polyline>
            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
        </svg>
        Coba Lagi
    `;
    btn.addEventListener('click', function() {
        wrapper.remove();
        sendMessage(text);
    });
    bubble.appendChild(btn);

    wrapper.appendChild(bubble);

    const time = document.createElement('span');
    time.className = 'message-time';
    time.textContent = getCurrentTime();
    wrapper.appendChild(time);

    chatBody.appendChild(wrapper);
    scrollToBottom();
}

function getCurrentTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
}

function showToast(message) {
    const toastMessage = document.getElementById('toastMessage');
    toastMessage.textContent = message;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

window.addEventListener('load', function() {
    userInput.focus();
    scrollToBottom();
});
