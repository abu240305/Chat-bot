## 1. Struktur Direktori Proyek
chatbot-akademik-undipa/
│
├── config/                      # Koneksi Database
│   └── database.php             # PDO Prepared Statements Connection
│
├── core/                        # Engine NLP (PHP)
│   ├── Preprocessing.php        # Normalisasi, Tokenisasi, Stemming
│   ├── Tfidf.php                # Pembobotan
│   ├── CosineSimilarity.php     # Pencocokan Vektor
│   └── ChatbotEngine.php        # Alur Utama
│
├── api/                         # Endpoint Komunikasi
│   └── process_chat.php         # POST handler & JSON Response
│
├── assets/                      # Frontend Statis & Berkas
│   ├── css/style.css
│   ├── js/chat.js               # AJAX, Validasi UI
│   ├── img/
│   └── downloads/               # Direktori File Upload dari Admin
│       ├── .htaccess            # Proteksi Keamanan: php_flag engine off
│       ├── template-skripsi_8f91a.docx
│       └── panduan-krs_2b49c.pdf
│
├── admin/                       # Panel Pengelola (Admin)
│   ├── index.php                # Login Admin
│   ├── dashboard.php            # Log & Analitik
│   ├── qa_manage.php            # Kelola Teks Q&A
│   └── file_manage.php          # Upload/Hapus Dokumen Akademik
│
├── database.sql                 # Skema Tabel (tb_pengetahuan, tb_admin, dll)
└── index.php                    # Halaman Chat Utama (Mahasiswa)

---

## 2. Arsitektur Client-Server
1. **Web Client:** Mahasiswa mengetik teks, JS mengirim AJAX POST ke API.
2. **PHP API:** Menerima teks, membersihkan dari XSS, memproses NLP.
3. **Database:** Mengambil Q&A berdasarkan pencocokan Cosine.
4. **Respon:** Backend mengirim JSON kembali ke UI (Teks balasan + link file statis).

---

## 3. Rumus Algoritma (TF-IDF & Cosine Similarity)
* **TF (Term Frequency):** $tf(t, d) = \frac{f_{t,d}}{\sum_{t' \in d} f_{t',d}}$
* **IDF (Inverse Document Frequency):** $idf(t) = \log\left(\frac{N}{df_t}\right)$
* **Cosine Similarity:**
  $$\cos(\theta) = \frac{\sum (A_i \times B_i)}{\sqrt{\sum A_i^2} \times \sqrt{\sum B_i^2}}$$

---

## 4. Keamanan Sistem (Cybersecurity)
1. **Anti-SQLi:** Wajib menggunakan PDO dengan *Prepared Statements*.
2. **Anti-XSS:** `htmlspecialchars()` untuk menampilkan log teks di admin dan chat UI.
3. **Keamanan Upload (Admin Only):**
   - Ekstensi terbatas (`.pdf`, `.docx`).
   - Cek `finfo_file()` (MIME-type).
   - Enkripsi/Hash nama file saat disimpan.
   - `.htaccess` untuk memblokir eksekusi `.php` di folder `assets/downloads/`.
4. **Proteksi Admin:** Login menggunakan `password_hash(BCRYPT)`, CSRF Token pada form input, dan *Rate Limiting* API Chatbot.