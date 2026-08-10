## 1. Alur Chat Mahasiswa (Text-Only NLP)
1. **Input Teks:** Mahasiswa mengetik pertanyaan (contoh: "gimana syarat krsan?").
2. **Validasi Klien:** JS mengecek apakah input kosong/melebihi batas (250 char). Jika valid, tampilkan bubble dan jalankan animasi loading.
3. **API Processing (PHP):**
   - Sanitasi teks (Anti-XSS).
   - Preprocessing: "gimana syarat krsan?" -> Case Folding -> Normalization (gimana->bagaimana, krsan->krs) -> Stopword -> Stemming ("bagaimana syarat krs").
4. **NLP Engine:** Algoritma TF-IDF & Cosine Similarity menghitung skor dokumen di database.
5. **Response Evaluation:**
   - Jika Skor $\ge 0.25$: Kembalikan teks jawaban (dan *Download Link* jika Q&A tersebut melampirkan berkas dari admin).
   - Jika Skor $< 0.25$: Kembalikan teks pesan Fallback.
6. **Rendering UI:** Animasi loading hilang, bubble bot muncul di layar.

---

## 2. Alur Dashboard Admin (Pengelolaan File & Data)
1. **Autentikasi:** Admin login dengan verifikasi BCRYPT.
2. **Kelola Knowledge Base:** Admin menambah pasangan (Pertanyaan - Jawaban) ke dalam database teks (Otomatis memicu kalkulasi ulang TF-IDF bobot).
3. **Kelola Berkas Fisik:**
   - Admin membuka menu Upload File.
   - Mengunggah file `.pdf` atau `.docx` terkait pedoman akademik.
   - Sistem memvalidasi ekstensi file dan MIME-type.
   - File di-*rename* secara acak (misal: `form_krs_839a.pdf`) lalu disimpan di folder `assets/downloads/` dengan proteksi `.htaccess`.
   - Nama file dikaitkan ke dalam database jawaban sehingga DIPA-bot bisa menyajikan tautan unduhan tersebut di jendela chat mahasiswa.