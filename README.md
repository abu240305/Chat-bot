# 🎓 DIPA-Bot — Chatbot Akademik Universitas Dipa Makassar

> **Skripsi:** Pengembangan Chatbot Cerdas Berbasis Web Menggunakan Natural Language Processing (NLP) untuk Layanan Akademik pada Universitas Dipa Makassar

DIPA-Bot adalah **asisten virtual berbasis teks** yang menyediakan layanan informasi akademik Universitas Dipa Makassar (UNDIPA) secara **24/7 melalui web**. Mahasiswa cukup mengetik pertanyaan — misalnya *"Bagaimana cara mengisi KRS?"* — dan sistem akan memproses teks tersebut dengan metode NLP **TF-IDF & Cosine Similarity**, lalu memberikan jawaban resmi dari basis pengetahuan yang dikelola oleh admin Layanan Akademik.

Sistem dibangun **murni dengan PHP Native** (tanpa framework maupun Composer), sehingga seluruh logika pemrosesan teks dan matematika vektor ditulis dari nol dalam bahasa PHP.

---

## 1. Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | **PHP Native murni** + **PDO Prepared Statements** (tanpa framework/Composer) |
| Database | **MySQL / MariaDB** |
| Algoritma NLP | **TF-IDF** (Term Frequency – Inverse Document Frequency) & **Cosine Similarity** (threshold ≥ 0.25) |
| Frontend | **HTML5**, **CSS3** (Mobile‑First Responsive), **Vanilla JavaScript** (AJAX), UI Admin bergaya Modernize (Plus Jakarta Sans, pastel, rounded) |
| Server | Apache (XAMPP) atau Container PHP‑Apache (Docker) |

---

## 2. Fitur Utama Sistem

### Sisi Mahasiswa (Client‑Side)

| Fitur | Keterangan |
|---|---|
| Chat UI Responsive | Antarmuka percakapan mobile‑first, bubble bot & user, timestamp |
| Quick Reply Chips | Tombol pintas topik populer (KRS, Jadwal, Skripsi, Template, UAS) |
| Typing Indicator | Animasi "bot sedang mengetik" selama proses AJAX berjalan |
| Validasi Input | Minimal 1 karakter, maksimal 250 karakter, anti input kosong |
| Unduhan Dokumen | Tombol "Unduh Dokumen" muncul otomatis jika jawaban memiliki lampiran (.pdf/.docx) |
| Normalisasi Teks | Kamus typo/singkatan/sinonim ("krsan" → "krs", "uas" → "ujian akhir semester") |
| Fallback Response | Pesan sopan jika skor similarity < 0.25 |

### Sisi Admin (Dashboard)

| Fitur | Keterangan |
|---|---|
| Login Admin | Autentikasi BCRYPT + CSRF Token |
| Ubah Password Admin | Ganti password dengan verifikasi password lama (BCRYPT) |
| Dashboard Statistik | Ringkasan Q&A, file, percakapan, dan **Fallback Rate** |
| CRUD Data Q&A | Modal form (tambah/edit) dengan validasi inline + CSRF + rebuild vektor otomatis |
| Upload File Aman | Modal drag & drop, validasi .pdf/.docx, cek MIME, rename hash, proteksi `.htaccess` |
| Log Percakapan | Catat pertanyaan, jawaban, skor cosine, IP, status Berhasil/Fallback |
| Filter & Search Log | Cari keyword, filter status (Berhasil/Fallback), dan filter tanggal |
| Paginasi & Read‑More | 10/25/50 data per halaman, sticky header & kolom aksi, teks panjang di-truncate |
| Rebuild Vektor | Inisialisasi/refresh bobot TF‑IDF seluruh knowledge base |

---

## 3. Alur Kerja & Pemrosesan NLP

### Alur Chat Mahasiswa

```
Mahasiswa mengetik pertanyaan (teks)
        │
        ▼
Chat UI (AJAX fetch → api/process_chat.php)
        │
        ▼
Rate Limiting (maks 5 request / 10 detik per sesi)
        │
        ▼
Preprocessing: Case Folding → Filtering → Normalisasi Kamus
               → Tokenizing → Stopword Removal → Stemming
        │
        ▼
Pembobotan TF-IDF (tf × idf) & Cosine Similarity
   dengan vektor cache tb_vektor_tfidf
        │
        ├── Skor >= 0.25 → Jawaban resmi (tb_pengetahuan)
        │                  (+ tombol unduh bila ada file_lampiran)
        └── Skor < 0.25  → Pesan Fallback sopan
        │
        ▼
Simpan riwayat ke tb_log_chat (pertanyaan, jawaban, skor, IP)
        │
        ▼
JSON response → Chat UI menampilkan bubble balasan
```

### Contoh Preprocessing

```
Input   : "gimana syarat krsan?"
Step 1  : Case Folding   → "gimana syarat krsan?"
Step 2  : Filtering      → "gimana syarat krsan"
Step 3  : Normalisasi    → "bagaimana syarat krs"   (kamus tb_kata_kunci)
Step 4  : Tokenizing     → ["bagaimana", "syarat", "krs"]
Step 5  : Stopword       → ["bagaimana", "syarat", "krs"]
Step 6  : Stemming       → ["bagaimana", "syarat", "krs"]
```

---

## 4. Use Case Utama

**Aktor:** Mahasiswa (pengguna), Admin / Staf Layanan Akademik.

| Kode | Aktor | Use Case |
|---|---|---|
| UC-01 | Mahasiswa | Bertanya prosedur akademik (KRS, UAS, skripsi, dll.) melalui chat |
| UC-02 | Mahasiswa | Meminta template/form akademik dan mengunduh dokumen (.pdf/.docx) |
| UC-03 | Mahasiswa | Menulis dengan typo/singkatan ("krsan", "uas") — dinormalisasi otomatis |
| UC-04 | Mahasiswa | Mengirim pertanyaan di luar topik — mendapat pesan fallback sopan |
| UC-05 | Admin | Login ke panel admin (BCRYPT + CSRF) |
| UC-06 | Admin | Menambah/mengedit/menghapus data Q&A knowledge base |
| UC-07 | Admin | Mengunggah/menghapus dokumen akademik dengan validasi keamanan |
| UC-08 | Admin | Memantau log percakapan & skor similarity (Berhasil/Fallback) |
| UC-09 | Admin | Menjalankan rebuild vektor TF-IDF setelah perubahan data besar |
| UC-10 | Sistem | Mencegah spam dengan rate limiting (5 request / 10 detik) |
| UC-11 | Admin | Mengubah password akun sendiri (verifikasi password lama) |
| UC-12 | Admin | Mencari & memfilter log percakapan (keyword, status, tanggal) |

**Skenario Utama (UC-01):** Mahasiswa mengetik "Bagaimana cara mengisi KRS?" → sistem menormalisasi & menghitung TF-IDF → cosine similarity terhadap vektor `tb_vektor_tfidf` → skor 0.74 ≥ 0.25 → jawaban resmi KRS ditampilkan → riwayat disimpan ke `tb_log_chat`.

**Skenario Utama (UC-04):** Mahasiswa mengetik "resep nasi goreng" → skor similarity 0 < 0.25 → bot membalas pesan fallback yang sopan → log tetap tercatat berstatus **Fallback**.

## 5. Struktur Direktori Proyek

```
chatbot/
│
├── index.php                    # Halaman utama chat mahasiswa (UI)
├── database.sql                 # Skema database + data dummy (6 tabel)
├── docker-compose.yml           # Konfigurasi container untuk Docker
├── .env.example                 # Template konfigurasi database
├── .gitignore                   # Daftar file yang dibatasi untuk repo
│
├── config/
│   ├── database.php             # Koneksi PDO + loader .env + class Database
│   ├── csrf.php                 # Helper CSRF (generate, verify, render field)
│   └── validator.php            # Validasi terpusat semua form (chat, login, Q&A, upload, password, filter log)
│
├── core/                        # Engine NLP (murni PHP)
│   ├── Preprocessing.php        # Case folding, filtering, normalisasi kamus,
│   │                            #   tokenizing, stopword removal, stemming
│   ├── Tfidf.php                # Pembobotan TF / IDF / TF-IDF
│   ├── CosineSimilarity.php     # Perhitungan cosine similarity dua vektor
│   ├── VectorBuilder.php        # Rebuild vektor TF-IDF seluruh knowledge base
│   └── ChatbotEngine.php        # (Kode arsip) alur NLP versi awal
│
├── api/
│   └── process_chat.php         # Endpoint POST JSON: rate limit + sanitasi
│                                #  + NLP + cosine + logging → JSON response
│
├── admin/                       # Panel admin (proteksi sesi + CSRF)
│   ├── index.php                # Login admin (BCRYPT + CSRF)
│   ├── dashboard.php            # Statistik ringkas + Fallback Rate
│   ├── qa_manage.php            # CRUD knowledge base (modal) + rebuild vektor
│   ├── file_manage.php          # Upload/hapus dokumen (modal drag & drop)
│   ├── logs.php                 # Monitoring log + filter & search + paginasi
│   ├── change_password.php      # Ubah password admin (verifikasi password lama)
│   ├── rebuild_vektor.php       # Utility rebuild vektor TF-IDF sekali jalan
│   └── logout.php               # Keluar dari sesi admin
│
└── assets/
    ├── css/
    │   ├── style.css            # Gaya UI chat (mobile-first, warna UNDIPA)
    │   └── admin.css            # Gaya UI panel admin (Modernize: Plus Jakarta Sans)
    ├── js/
    │   ├── chat.js              # AJAX fetch, validasi, toast, retry, quick reply
    │   └── admin.js             # Toast, drawer, validasi inline, drag-drop, read-more
    └── downloads/               # Folder dokumen akademik (upload admin)
        └── .htaccess            # Proteksi: blokir eksekusi PHP di folder ini
```

---

## 6. Skema Database

| Tabel | Fungsi |
|---|---|
| `tb_admin` | Akun admin (password BCRYPT), username, nama lengkap, email |
| `tb_pengetahuan` | Knowledge base Q&A: pertanyaan, jawaban, file lampiran, kategori |
| `tb_kata_kunci` | Kamus normalisasi NLP: kata asli → kata baku (typo/singkatan/sinonim) |
| `tb_log_chat` | Riwayat percakapan: pertanyaan, jawaban, skor, IP, Waktu |
| `tb_vektor_tfidf` | Cache bobot TF-IDF (id_pengetahuan, term, bobot) agar chat cepat |
| `tb_pengaturan` | Pengaturan teks sistem (pesan sambutan & pesan fallback) yang bisa diubah tanpa edit kode |

**Relasi Foreign Key:**

```
tb_log_chat
  └─ id_pengetahuan_matched ──▶ tb_pengetahuan(id_pengetahuan)   [ON DELETE SET NULL]

tb_vektor_tfidf
  └─ id_pengetahuan ─────────▶ tb_pengetahuan(id_pengetahuan)   [ON DELETE CASCADE]
```

---

## 7. Panduan Setup & Instalasi Awal (POST-CLONE)

### a. Clone Repository & Setup Folder

```bash
git clone https://github.com/username/dipa-bot.git
cd dipa-bot
```

Pastikan PHP 8.x dan MySQL/MariaDB tersedia di mesin kamu.

### b. Konfigurasi Kredensial Database

Buka `config/database.php`. Kredensial default (XAMPP) sudah ditulis sebagai konstanta:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'chatbot_undipa');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

Sesuaikan `DB_HOST`, `DB_USER`, `DB_PASS` dengan server lokal kamu. Alternatif yang lebih rapi: salin `.env.example` menjadi `.env`, lalu isi nilainya — file `.env` otomatis dibaca oleh `config/database.php` (dan tidak di-commit ke repository).

### c. Import Database

**Via phpMyAdmin:**
1. Buka `http://localhost/phpmyadmin`.
2. Pilih menu **Import** → pilih file `database.sql` → klik **Go**.

**Via CLI MySQL/MariaDB:**

```bash
mysql -u root -p < database.sql
```

Skrip akan membuat database `chatbot_undipa` beserta 6 tabel dan data dummy akademik.

### d. Menjalankan Aplikasi

**Opsi 1 — XAMPP (Lokal):**
1. Letakkan folder proyek di `htdocs`.
2. Pastikan Apache + MySQL aktif di XAMPP Control Panel.
3. Buka `http://localhost/<nama-folder>/`.

**Opsi 2 — Docker (Rekomendasi):**
1. Pastikan Docker Desktop / Engine sudah terpasang.
2. Cukup jalankan perintah berikut di dalam folder proyek:
   ```bash
   docker-compose up -d
   ```
3. Akses aplikasi melalui `http://localhost:8080/`. Database otomatis berjalan di latar belakang (mariadb-pusat).

**Akses Halaman:**
- Chat mahasiswa: `http://localhost/<folder>/`
- Admin: `http://localhost/<folder>/admin/` — login default `admin` / `admin123`

### e. Langkah Wajib — Inisialisasi Bobot TF-IDF

> **PENTING:** Setelah login admin, buka halaman
> **`http://localhost/<folder>/admin/rebuild_vektor.php`**
> lalu klik tombol **"Jalankan Rebuild Vektor"**.

Langkah ini menghitung ulang bobot TF-IDF seluruh pertanyaan di `tb_pengetahuan` dan mengisi cache `tb_vektor_tfidf`. Tanpa langkah ini, sistem tidak memiliki vektor dokumen sehingga semua pertanyaan mahasiswa akan dijawab dengan pesan fallback.

---

## 8. Keamanan & Batasan Sistem

### Proteksi Keamanan

| Aspek | Implementasi |
|---|---|
| Anti‑SQL Injection | Semua query memakai **PDO Prepared Statements** |
| Anti‑XSS | Sanitasi input (`strip_tags`) & output (`htmlspecialchars`) |
| Anti‑CSRF | Token `bin2hex(random_bytes(32))` + `hash_equals` pada semua form admin |
| Brute Force Login | Password admin di‑hash **BCRYPT** + `session_regenerate_id()` |
| Upload Aman | Ekstensi .pdf/.docx, cek MIME (`finfo`), rename hash acak, `.htaccess` anti‑eksekusi di `assets/downloads/` |
| Anti‑Spam / Rate Limit | Maksimal **5 request per 10 detik** per sesi pada endpoint API chat |
| Validasi Terpusat | `config/validator.php` — validasi semua form (chat, login, Q&A, upload, ubah password, filter log) |

### Batasan Sistem

- Input mahasiswa **100% teks murni** — tidak mendukung upload gambar, OCR, maupun suara.
- Jawaban bot bersumber dari **knowledge base yang dikelola admin**; bot tidak belajar mandiri.
- Mencocokkan dengan **threshold Cosine Similarity ≥ 0.25**; di bawah itu dikembalikan pesan fallback.

---

## 9. Lisensi & Kontak Maintainer

**Hak Cipta:** © 2026 Mohammad Ali Riedza. Seluruh hak cipta dilindungi. Proyek ini dikembangkan untuk keperluan akademik (Skripsi) dan tidak untuk penggunaan komersial tanpa izin.

**Maintainer / Peneliti:**
- Nama: Mohammad Ali Riedza
- NIM: 221084 — Sistem Informasi, Universitas Dipa Makassar
- Email: `email.anda@example.com`
- GitHub: `https://github.com/username`
- LinkedIn: `https://www.linkedin.com/in/username`

---

> DIPA-Bot — Membantu layanan akademik UNDIPA dengan teknologi web & NLP sederhana namun kuat.