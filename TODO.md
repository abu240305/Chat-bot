# TODO — DIPA-Bot (Chatbot Akademik UNDIPA)

Status proyek per Tahap 7 (Penyempurnaan Akhir). Centang = sudah selesai.

## 1. Sistem Inti (Selesai)
- [x] Engine NLP: Preprocessing (case folding, filtering, tokenizing, stopword, normalisasi, stemming)
- [x] Algoritma TF-IDF & Cosine Similarity (PHP Native murni, threshold 0.25)
- [x] Chat UI mobile-first (index.php, style.css, chat.js) + Quick Reply + Typing Indicator + Toast
- [x] Input mahasiswa 100% teks (validasi 1-250 karakter, XSS)
- [x] Dashboard Admin (statistik Q&A, file, percakapan)
- [x] CRUD Q&A dengan CSRF Token
- [x] Upload File Admin (.pdf/.docx): validasi ekstensi + MIME + rename hash + .htaccess proteksi
- [x] Login Admin: BCRYPT, CSRF Token, session regenerate

## 2. Keamanan & Optimasi (Selesai)
- [x] PDO Prepared Statements di semua query (anti SQLi)
- [x] htmlspecialchars() di semua output (anti XSS)
- [x] Rate Limiting API (maks 5 request / 10 detik, $_SESSION)
- [x] Halaman Log Percakapan (admin/logs.php) dengan status Berhasil/Fallback
- [x] Tabel cache vektor `tb_vektor_tfidf` (preprocessing + bobot TF disimpan per id_pengetahuan)
- [x] Engine chat memakai vektor dari `tb_vektor_tfidf` (tidak hitung ulang seluruh KB)
- [x] Filename upload di-rename acak (hash)
- [x] .gitignore (abaikan file upload, kecuali .htaccess)

## 3. Database
- [x] Skema + data dummy di database.sql (tb_admin, tb_pengetahuan, tb_kata_kunci, tb_log_chat, tb_vektor_tfidf)
- [x] Import ulang/update database di XAMPP / Laragon agar tabel `tb_vektor_tfidf` ada
- [x] Backfill vektor: script sekali jalan di `admin/rebuild_vektor.php` (jalankan saat import database)

## 4. Konten & Berkas
- [ ] File fisik akademik di-upload ke `assets/downloads/` (panduan-krs, kalender, panduan-skripsi, template-skripsi, kartu kontrol, form ekstrakurikuler)
- [ ] Pastikan nama file di DB `file_lampiran` cocok dengan file di server

## 5. Pengujian (KPI PRD: 100% Black Box)
- [ ] Black Box: input kosong, 251 karakter, typo ("krsan"), singkatan ("uas"), di luar topik (fallback)
- [ ] Black Box: SQLi & XSS di input chat dan form admin
- [ ] Black Box: upload file MIME palsu (rename .pdf → .php), ukuran > 10MB
- [ ] Black Box: rate limit (6+ request dalam 10 detik)
- [ ] Black Box: akses admin tanpa login (redirect ke index.php)
- [ ] Dokumentasi hasil pengujian (tabel test case)
