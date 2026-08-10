# DIPA-Bot — Chatbot Akademik UNDIPA

Pengembangan Chatbot Cerdas Berbasis Web menggunakan Natural Language Processing (NLP)
untuk Layanan Akademik Universitas Dipa Makassar.

- **Teknologi:** PHP Native (tanpa framework), MySQL, PDO Prepared Statements
- **Algoritma NLP:** TF-IDF & Cosine Similarity (threshold skor >= 0.25 = berhasil, < 0.25 = fallback)
- **Pengguna:** Mahasiswa (input teks 100%) & Admin Layanan Akademik
- **Peneliti:** Mohammad Ali Riedza (NIM: 221084)

## Struktur

```
config/   Koneksi database (PDO) + CSRF helper + .env loader
core/     Engine NLP: Preprocessing, Tfidf, CosineSimilarity
api/      process_chat.php (endpoint chat JSON + rate limiting)
admin/    Panel admin: dashboard, kelola Q&A & file, log, rebuild vektor
assets/   CSS, JS chat, folder unduhan dokumen akademik
database.sql  Skema + data dummy
```

## Cara Menjalankan (XAMPP)

1. Import `database.sql` ke MySQL (membuat DB `chatbot_undipa`).
2. Salin `.env.example` menjadi `.env`, sesuaikan kredensial:
   ```
   DB_HOST=localhost
   DB_NAME=chatbot_undipa
   DB_USER=root
   DB_PASS=
   ```
3. Jalankan Apache + MySQL, akses `http://localhost/<folder-proyek>/`.
4. Login admin di `admin/` → username `admin`, password `admin123`.
5. Jalankan `admin/rebuild_vektor.php` sekali agar vektor TF terisi.

## Keamanan

PDO Prepared Statements (anti SQLi), `htmlspecialchars()` (anti XSS), CSRF Token,
BCRYPT password, validasi upload (.pdf/.docx + MIME + rename hash), rate limit
chat (maks 5 request / 10 detik), serta `.htaccess` proteksi folder unduhan.