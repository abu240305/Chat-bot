## 1. Ringkasan Eksekutif & Latar Belakang
* **Judul Penelitian:** Pengembangan Chatbot Cerdas Berbasis Web Menggunakan Natural Language Processing (NLP) untuk Layanan Akademik pada Universitas Dipa Makassar.
* **Peneliti:** Mohammad Ali Riedza (NIM: 221084).
* **Pembimbing:** Muh. Syukri Mustafa, S.Si., MMSI (Pembimbing I) & Asran, ST., MT. (Pembimbing II).
* **Institusi:** Program Studi Sistem Informasi, Universitas Dipa Makassar.

Penyampaian informasi akademik di Universitas Dipa Makassar saat ini masih bergantung pada layanan hotline, media sosial, dan sistem web konvensional. Sistem Chatbot Cerdas Berbasis Web ini dikembangkan dengan metode *Research and Development* (R&D) menggunakan algoritma **Term Frequency-Inverse Document Frequency (TF-IDF)** dan **Cosine Similarity**. Sistem diimplementasikan menggunakan **PHP Native** dan **MySQL** untuk menyajikan informasi akademik secara cepat, handal terhadap kesalahan pengguna (*user error*), aman dari serangan siber, dan tanpa terbatas jam operasional.

---

## 2. Tujuan & Indikator Keberhasilan (KPI)
### 2.1 Tujuan Penelitian
1. Mengembangkan chatbot cerdas berbasis web sebagai media layanan informasi akademik di Universitas Dipa Makassar.
2. Menerapkan pemrosesan bahasa alami (NLP) dengan metode TF-IDF dan Cosine Similarity untuk memahami pertanyaan mahasiswa.
3. Membangun sistem yang tangguh terhadap kesalahan input pengguna (*user error handling*) dan aman dari ancaman siber (*cybersecurity*).

### 2.2 Target KPI Sistem
* **Fungsionalitas Sistem:** $100\%$ lulus pengujian fungsionalitas melalui *Black Box Testing*.
* **Relevansi Jawaban:** Mampu memberikan jawaban yang sesuai berdasarkan pencocokan vektor kata.
* **Ketahanan Sistem (*Resilience*):** $100\%$ menangani *edge cases* (input kosong, typo, spam, berkas tidak ditemukan, percobaan SQLi/XSS) tanpa menyebabkan crash.
* **Ketersediaan Akses:** Dapat diakses $24/7$ secara efisien.

---

## 3. Profil Pengguna (User Persona)
* **Mahasiswa (Pengguna Utama):** Mengakses chatbot melalui web untuk mengajukan pertanyaan teks terkait layanan akademik. (Hanya input teks murni, tidak dapat mengirim file/gambar ke chatbot).
* **Admin / Staf Layanan Akademik:** Bertanggung jawab mengelola Q&A, mengunggah berkas akademik resmi (.pdf/.docx) ke server, dan memantau log pertanyaan.

---

## 4. Spesifikasi Fitur Utama
### 4.1 Modul Interaksi Percakapan (Chat UI)
* Antarmuka percakapan interaktif berbasis web (input teks).
* Fitur *Quick Reply Chips* (tombol rekomendasi topik).
* Indikator *Loading / Typing* untuk mencegah pengiriman pesan ganda.
* Balasan berupa teks presisi dan tautan unduhan dokumen statis (.pdf/.docx) jika relevan.

### 4.2 Engine NLP & Pencarian Informasi (Teks)
* **Preprocessing Teks:** *Case Folding*, *Filtering*, *Tokenizing*, *Stopword Removal*, *Normalization* (Singkatan/Typo), dan *Stemming*.
* **Pembobotan TF-IDF:** Mengukur bobot kepentingan kata terhadap dokumen dalam basis data.
* **Cosine Similarity:** Menghitung kemiripan vektor antara pertanyaan mahasiswa dan knowledge base.

### 4.3 Modul Admin (Pengelola Layanan Akademik)
* **Manajemen Q&A:** Fitur Tambah/Edit/Hapus pertanyaan kunci dan jawaban.
* **Manajemen Berkas:** Mengunggah berkas template/formulir akademik dengan validasi format (.pdf/.docx) dan ukuran file.
* **Monitoring Log:** Memantau riwayat pertanyaan, skor kemiripan, dan pertanyaan yang tidak dipahami (*fallback*).

### 4.4 Penanganan Kesalahan Pengguna (*User Error*)
* **Input Kosong / Teks Panjang:** Tombol kirim otomatis dinonaktifkan (maks. 250 karakter).
* **Typo & Singkatan:** Pemetaaan kata gaul (contoh: "krsan" -> "krs") pada kamus normalisasi.
* **Pertanyaan Luar Konteks:** Jika skor kemiripan $< 0.25$, bot merespon dengan *Fallback Response* yang sopan.

---

## 5. Keamanan Siber & Batasan Sistem
* **Keamanan Sistem:**
  - SQL Injection: Dicegah menggunakan PDO Prepared Statements.
  - XSS: Dicegah dengan `htmlspecialchars()`.
  - CSRF & Brute Force: Penggunaan CSRF Token dan BCRYPT Password Hashing untuk Admin.
  - File Upload (Admin): Validasi MIME-type, rename acak (hash), dan pembatasan eksekusi PHP (`.htaccess` `engine off`) di folder direktori unduhan.
* **Batasan Sistem (Penting):**
  - **Input Mahasiswa hanya berupa Teks Murni.** Sistem tidak memiliki fitur membaca dokumen (OCR) atau gambar yang dikirim oleh pengguna.
  - Penyediaan file (seperti Template Skripsi) dilakukan dalam bentuk **Tautan Unduhan (Download Link)** pada respon teks, di mana file tersebut diunggah oleh Admin melalui dashboard.