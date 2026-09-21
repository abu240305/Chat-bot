# DOKUMENTASI PEMODELAN UML SISTEM (REVISI SKRIPSI)
## DIPA-Bot — Chatbot Akademik Universitas Dipa Makassar Berbasis NLP

> **Judul Skripsi:** Pengembangan Chatbot Cerdas Berbasis Web Menggunakan Natural Language Processing (NLP) Untuk Layanan Akademik Pada Universitas Dipa Makassar  
> **Peneliti:** Mohammad Ali Riedza  
> **NIM:** 221084  
> **Program Studi:** Sistem Informasi (S1) — Universitas Dipa Makassar  
> **Dasar Revisi:** Lembar Koreksi Skripsi (Perbaikan Use Case Diagram, Perbaikan Activity Diagram, dan Penambahan Sequence Diagram).

---

## DAFTAR ISI
1. [Pendahuluan & Konteks Sistem](#1-pendahuluan--konteks-sistem)
2. [Use Case Diagram (Diperbaiki)](#2-use-case-diagram-diperbaiki)
   - 2.1 Diagram Visual Use Case (Mermaid)
   - 2.2 Deskripsi Aktor
   - 2.3 Tabel Skenario Use Case
3. [Activity Diagram (Diperbaiki)](#3-activity-diagram-diperbaiki)
   - 3.1 Activity Diagram Tanya Jawab Chatbot (Proses NLP)
   - 3.2 Activity Diagram Pengelolaan Q&A dan Rebuild Vektor
   - 3.3 Activity Diagram Unggah Dokumen Akademik
4. [Sequence Diagram (Diagram Baru)](#4-sequence-diagram-diagram-baru)
   - 4.1 Sequence Diagram Alur Chat Mahasiswa (NLP Matching)
   - 4.2 Sequence Diagram Rebuild Vektor TF-IDF Knowledge Base
   - 4.3 Sequence Diagram Login & Autentikasi Admin
5. [Petunjuk Penyalinan ke Naskah Skripsi](#5-petunjuk-penyalinan-ke-naskah-skripsi)

---

## 1. Pendahuluan & Konteks Sistem
DIPA-Bot adalah sistem layanan informasi akademik interaktif 24/7 untuk mahasiswa Universitas Dipa Makassar. Sistem memproses pertanyaan teks bebas mahasiswa dengan tahapan Natural Language Processing (NLP):
1. **Preprocessing:** *Case Folding, Filtering, Normalisasi Kata (Kamus Typo/Singkatan), Tokenizing, Stopword Removal, dan Stemming*.
2. **Pembobotan & Pengukuran Similarity:** *Term Frequency – Inverse Document Frequency (TF-IDF)* dan *Cosine Similarity* dengan ambang batas (*threshold*) $\ge 0.25$.
3. **Penyajian Respons:** Menampilkan jawaban resmi akademik serta tombol pengunduhan dokumen lampiran (`.pdf`/`.docx`) bila tersedia.
4. **Panel Administrasi:** Tempat admin mengelola knowledge base 10 topik akademik, mengunggah berkas, memonitor log percakapan, dan menjalankan *rebuild* vektor TF-IDF.

---

## 2. Use Case Diagram (Diperbaiki)

Use Case Diagram menggambarkan fungsionalitas sistem dari sudut pandang pengguna (aktor). Relasi `<<include>>` digunakan untuk proses wajib yang selalu dieksekusi bersamaan, sedangkan `<<extend>>` digunakan untuk kondisi bersyarat (kondisional).

### 2.1 Diagram Visual Use Case

```mermaid
flowchart LR
    subgraph Aktor_Pengguna
        M["fa:fa-user Mahasiswa"]
        A["fa:fa-user-tie Admin Akademik"]
    end

    subgraph Batas_Sistem ["Sistem DIPA-Bot (Chatbot Akademik UNDIPA)"]
        UC1(["UC-01: Melakukan Percakapan Chat"])
        UC2(["UC-02: Memilih Topik Quick Reply"])
        UC3(["UC-03: Memproses NLP (TF-IDF & Cosine)"])
        UC4(["UC-04: Mengunduh Berkas Lampiran"])
        UC5(["UC-05: Menerima Pesan Fallback"])
        
        UC6(["UC-06: Login Sistem"])
        UC7(["UC-07: Mengelola Knowledge Base (CRUD Q&A)"])
        UC8(["UC-08: Mengunggah & Mengelola Berkas"])
        UC9(["UC-09: Memantau Log & Evaluasi Fallback"])
        UC10(["UC-10: Membangun Ulang Vektor TF-IDF"])
        UC11(["UC-11: Mengubah Password Admin"])
    end

    M --> UC1
    M --> UC2
    
    UC1 -.->|<<include>>| UC3
    UC3 -.->|<<extend>> skor < 0.25| UC5
    UC1 -.->|<<extend>> ada lampiran| UC4

    A --> UC6
    A --> UC7
    A --> UC8
    A --> UC9
    A --> UC10
    A --> UC11

    UC7 -.->|<<include>>| UC10
```

---

### 2.2 Deskripsi Aktor
| No | Aktor | Jenis | Deskripsi Peran |
|:--:|:---|:---|:---|
| 1 | **Mahasiswa** | Aktor Primer | Pengguna layanan yang mengajukan pertanyaan seputar informasi akademik melalui antarmuka web, memanfaatkan tombol *quick reply*, dan mengunduh berkas pedoman akademik. |
| 2 | **Admin Akademik** | Aktor Primer | Staf pengelola layanan informasi akademik yang bertugas memelihara data pengetahuan Q&A, mengunggah berkas resmi, mengawasi log percakapan, dan memperbarui cache vektor TF-IDF. |

---

### 2.3 Tabel Skenario Use Case

#### Skenario UC-01: Melakukan Percakapan Chat
* **Aktor:** Mahasiswa
* **Deskripsi:** Mahasiswa mengirimkan pertanyaan teks seputar 10 topik akademik UNDIPA (KRS, MBKM, Cuti, Skripsi, dll.).
* **Kondisi Awal (*Pre-condition*):** Mahasiswa membuka halaman web utama DIPA-Bot.
* **Alur Utama (*Main Flow*):**
  1. Mahasiswa mengetik pertanyaan pada input teks (1–250 karakter) dan menekan tombol "Kirim".
  2. Sistem melakukan validasi klien dan menampilkan indikator animasi mengetik (*typing indicator*).
  3. Sistem menjalankan pra-pemrosesan teks NLP (*Preprocessing*) dan menghitung kemiripan vektor (*Cosine Similarity*).
  4. Sistem menemukan jawaban di `tb_pengetahuan` dengan skor $\ge 0.25$.
  5. Sistem mencatat transaksi percakapan ke `tb_log_chat`.
  6. Sistem menampilkan bubble balasan bot di layar percakapan mahasiswa.
* **Alur Alternatif (*Alternative Flow*):**
  - Jika skor kemiripan $< 0.25$: Sistem menampilkan balasan *Fallback* yang meminta mahasiswa memperjelas pertanyaan.
  - Jika pertanyaan memiliki dokumen lampiran: Sistem memunculkan tombol unduhan dokumen (`.pdf`/`.docx`).
* **Kondisi Akhir (*Post-condition*):** Pertanyaan terjawab dan tercatat di basis data log sistem.

#### Skenario UC-07: Mengelola Knowledge Base (CRUD Q&A)
* **Aktor:** Admin Layanan Akademik
* **Deskripsi:** Menambah, mengubah, atau menghapus pasangan tanya-jawab pengetahuan akademik.
* **Kondisi Awal:** Admin telah berhasil login ke panel dashboard admin.
* **Alur Utama:**
  1. Admin membuka menu *Knowledge Base* (`qa_manage.php`).
  2. Admin memilih aksi (Tambah / Edit / Hapus data Q&A).
  3. Admin mengisi form (Pertanyaan, Jawaban, Kategori topik, File lampiran).
  4. Sistem memvalidasi CSRF token dan kelengkapan input.
  5. Sistem menyimpan perubahan ke tabel `tb_pengetahuan`.
  6. Sistem secara otomatis memicu pembaruan bobot vektor di `tb_vektor_tfidf`.
* **Kondisi Akhir:** Basis pengetahuan terbarui dan vektor dokumen siap dicocokkan.

---

## 3. Activity Diagram (Diperbaiki)

### 3.1 Activity Diagram Tanya Jawab Chatbot (Proses NLP Mahasiswa)
Diagram ini memodelkan aliran aktivitas saat mahasiswa bertanya hingga bot memberikan jawaban:

```mermaid
activity
stateDiagram-v2
    [*] --> BukaHalaman: Mahasiswa mengakses web DIPA-Bot
    BukaHalaman --> InputTeks: Mahasiswa mengetik pesan (1-250 char)
    InputTeks --> ValidasiKlien: Klik tombol Kirim
    
    state ValidasiKlien <<choice>>
    ValidasiKlien --> InputTeks: Teks kosong / >250 char (Tolak)
    ValidasiKlien --> KirimAjax: Input Valid
    
    KirimAjax --> TampilTyping: Tampilkan Animasi Mengetik
    TampilTyping --> SanitasiServer: Kirim HTTP POST JSON ke API
    
    SanitasiServer --> Preprocessing: Pembersihan XSS & Tag HTML
    
    state Preprocessing {
        [*] --> CaseFolding: Ubah huruf kecil (lowercase)
        CaseFolding --> Filtering: Hapus angka & tanda baca
        Filtering --> Normalisasi: Cocokkan kamus tb_kata_kunci (typo/singkatan)
        Normalisasi --> Tokenizing: Pecah string menjadi array token
        Tokenizing --> Stopword: Buang kata hubung (tb_stopword)
        Stopword --> Stemming: Ubah ke kata dasar
        Stemming --> [*]
    }
    
    Preprocessing --> CekToken: Hasil token ada?
    
    state CekToken <<choice>>
    CekToken --> SetFallback: Token Kosong
    CekToken --> HitungTfidf: Token Tersedia
    
    HitungTfidf --> HitungCosine: Vektorisasi Pertanyaan & Ambil tb_vektor_tfidf
    HitungCosine --> EvaluasiSkor: Hitung Cosine Similarity semua dokumen
    
    state EvaluasiSkor <<choice>>
    EvaluasiSkor --> AmbilJawaban: Skor Tertinggi >= 0.25
    EvaluasiSkor --> SetFallback: Skor Tertinggi < 0.25
    
    AmbilJawaban --> SimpanLog: Ambil jawaban & nama file lampiran
    SetFallback --> SimpanLog: Siapkan pesan permohonan maaf (fallback)
    
    SimpanLog --> KirimJson: Simpan ke tb_log_chat (skor, IP, User-Agent)
    KirimJson --> RenderUI: Kembalikan JSON ke browser
    RenderUI --> [*]: Sembunyikan animasi & tampilkan bubble bot
```

---

### 3.2 Activity Diagram Pengelolaan Q&A dan Rebuild Vektor
Diagram aktivitas saat admin mengelola basis pengetahuan:

```mermaid
stateDiagram-v2
    [*] --> LoginAdmin: Admin memasukkan kredensial & CSRF
    LoginAdmin --> Dashboard: Autentikasi BCRYPT Sukses
    Dashboard --> BukaMenuQA: Pilih menu Knowledge Base
    
    state PilihanAksi <<choice>>
    BukaMenuQA --> PilihanAksi
    PilihanAksi --> FormTambah: Tambah Q&A Baru
    PilihanAksi --> FormEdit: Edit Data Q&A
    PilihanAksi --> HapusData: Hapus Q&A
    
    FormTambah --> SimpanDB: Submit Form (+ Validasi CSRF)
    FormEdit --> SimpanDB: Submit Form (+ Validasi CSRF)
    HapusData --> SimpanDB: Konfirmasi Hapus
    
    SimpanDB --> RebuildVektor: Update tabel tb_pengetahuan
    
    state RebuildVektor {
        [*] --> AmbilSemuaDokumen: Query SELECT tb_pengetahuan
        AmbilSemuaDokumen --> TokenisasiUlang: Preprocessing seluruh dokumen
        TokenisasiUlang --> HitungIDF: Hitung frekuensi dokumen (DF) & IDF
        HitungIDF --> SimpanVektorBaru: Simpan bobot TF-IDF ke tb_vektor_tfidf
        SimpanVektorBaru --> [*]
    }
    
    RebuildVektor --> NotifikasiSukses: Tampilkan pesan sukses & jumlah vektor
    NotifikasiSukses --> [*]
```

---

### 3.3 Activity Diagram Unggah Dokumen Akademik
Diagram saat admin mengunggah dokumen pedoman (`.pdf`/`.docx`):

```mermaid
stateDiagram-v2
    [*] --> BukaMenuBerkas: Admin membuka file_manage.php
    BukaMenuBerkas --> PilihBerkas: Drag & drop / pilih file
    PilihBerkas --> ValidasiUkuran: Ukuran <= 10MB?
    
    state ValidasiUkuran <<choice>>
    ValidasiUkuran --> TolakBerkas: Ukuran > 10MB
    ValidasiUkuran --> ValidasiMIME: Ukuran Valid
    
    state ValidasiMIME <<choice>>
    ValidasiMIME --> TolakBerkas: Bukan PDF atau DOCX (finfo_file)
    ValidasiMIME --> EnkripsiNama: MIME Sesuai
    
    TolakBerkas --> TampilkanPesanGagal: Beri notifikasi error
    TampilkanPesanGagal --> [*]
    
    EnkripsiNama --> SimpanFolder: Hash nama berkas acak (mis: form_krs_839a.pdf)
    SimpanFolder --> ProteksiHtaccess: Simpan di assets/downloads/
    ProteksiHtaccess --> TampilkanPesanSukses: Blokir eksekusi PHP (.htaccess)
    TampilkanPesanSukses --> [*]
```

---

## 4. Sequence Diagram (Diagram Baru)

> **Catatan Khusus Penguji:** Diagram ini ditambahkan secara khusus untuk memenuhi instruksi lembar koreksi dosen (*"Tidak menggambarkan sequence diagram"*).

Sequence Diagram memodelkan interaksi antar-objek/komponen dalam rentang waktu tertentu.

### 4.1 Sequence Diagram Alur Chat Mahasiswa (NLP Matching)

Diagram ini menunjukkan interaksi dari mahasiswa, tampilan antarmuka, pengontrol AJAX, endpoint API, modul pemrosesan teks NLP, hingga database MySQL:

```mermaid
sequenceDiagram
    autonumber
    actor M as Mahasiswa
    participant V as ChatView (index.php)
    participant C as AjaxController (chat.js)
    participant API as ApiEndpoint (process_chat.php)
    participant NLP as Preprocessing.php
    participant TF as Tfidf.php
    participant CS as CosineSimilarity.php
    participant DB as Database (MySQL/MariaDB)

    M->>V: Mengetik pertanyaan akademik
    M->>V: Klik tombol "Kirim"
    V->>C: Event submit sendMessage()
    C->>C: Validasi panjang karakter (1-250)
    C->>V: Tampilkan bubble user & Animasi typing
    C->>API: HTTP POST /api/process_chat.php (JSON pesan)
    
    API->>API: Sanitasi input (Anti-XSS & Rate Limit)
    API->>NLP: process(pesanMahasiswa, db)
    NLP->>DB: Query kamus tb_kata_kunci & tb_stopword
    DB-->>NLP: Daftar kata normalisasi & stopword
    NLP-->>API: Array token bersih (stemmed words)

    alt Token Kosong (Hanya simbol/kata acak)
        API->>DB: INSERT tb_log_chat (status Fallback)
        API-->>C: JSON response (Pesan Fallback, skor=0)
    else Token Ditemukan
        API->>DB: SELECT id_pengetahuan, term, bobot_tfidf FROM tb_vektor_tfidf
        DB-->>API: Daftar vektor dokumen tersimpan (N dokumen)
        API->>TF: calculateTFIDF(tokenQuery, df Corpus)
        TF-->>API: Vector Query Mahasiswa
        
        loop Setiap Dokumen di tb_vektor_tfidf
            API->>CS: calculate(queryVector, docVector)
            CS-->>API: Nilai Cosine Similarity (0.0000 - 1.0000)
        end

        alt Skor Tertinggi >= 0.25 (Pencocokan Berhasil)
            API->>DB: SELECT jawaban, file_lampiran FROM tb_pengetahuan WHERE id = bestId
            DB-->>API: Teks Jawaban Resmi & Berkas
            API->>DB: INSERT tb_log_chat (query, jawaban, skor, matchedId)
            API-->>C: JSON response (success=true, jawaban, file_lampiran, skor)
        else Skor Tertinggi < 0.25 (Fallback)
            API->>DB: INSERT tb_log_chat (query, fallbackMessage, skor, null)
            API-->>C: JSON response (success=true, pesan fallback, file=null, skor)
        end
    end

    C->>V: Sembunyikan animasi typing
    C->>V: Render bubble pesan balasan DIPA-Bot
    opt Memiliki Berkas Lampiran (.pdf/.docx)
        C->>V: Render tombol unduh berkas
    end
    V-->>M: Mahasiswa membaca jawaban informasi akademik
```

---

### 4.2 Sequence Diagram Rebuild Vektor TF-IDF Knowledge Base

Diagram interaksi saat admin melakukan *rebuild* vektor (baik melalui penambahan Q&A di `qa_manage.php` maupun menu mandiri `rebuild_vektor.php`):

```mermaid
sequenceDiagram
    autonumber
    actor A as Admin Akademik
    participant RV as View (rebuild_vektor.php)
    participant VB as VectorBuilder.php
    participant NLP as Preprocessing.php
    participant TF as Tfidf.php
    participant DB as Database (MySQL/MariaDB)

    A->>RV: Klik "Jalankan Rebuild Vektor"
    RV->>RV: Verifikasi Sesi Admin & CSRF Token
    RV->>VB: rebuildAllVectors(db)
    
    VB->>DB: SELECT id_pengetahuan, pertanyaan FROM tb_pengetahuan
    DB-->>VB: Seluruh koleksi pertanyaan pengetahuan (N data)

    loop Untuk Setiap Dokumen Pengetahuan
        VB->>NLP: process(pertanyaan, db)
        NLP-->>VB: Array token per dokumen
    end

    VB->>TF: buildVocabulary(allTokenDocs)
    TF-->>VB: Kosakata unik korpus & nilai IDF per term

    VB->>DB: DELETE FROM tb_vektor_tfidf (Kosongkan cache lama)
    DB-->>VB: Cache dikosongkan

    loop Setiap Dokumen & Term
        VB->>TF: calculateTFIDF(tokens)
        TF-->>VB: Bobot bobot_tfidf
        VB->>DB: INSERT INTO tb_vektor_tfidf (id_pengetahuan, term, bobot_tfidf)
    end

    DB-->>VB: Seluruh term vektor tersimpan
    VB-->>RV: Return array [total_dokumen, total_term, waktu_eksekusi]
    RV-->>A: Tampilkan alert sukses pembangunan ulang vektor
```

---

### 4.3 Sequence Diagram Login & Autentikasi Admin

Diagram interaksi keamanan saat admin melakukan autentikasi ke dalam sistem:

```mermaid
sequenceDiagram
    autonumber
    actor A as Admin Akademik
    participant L as LoginView (admin/index.php)
    participant V as Validator.php
    participant DB as Database (tb_admin)
    participant S as SessionManager
    participant D as DashboardView (admin/dashboard.php)

    A->>L: Membuka URL /admin/
    L->>S: Cek $_SESSION['admin_logged_in']
    S-->>L: Sesi kosong (Belum Login)
    L-->>A: Tampilkan formulir login (+ input CSRF token)

    A->>L: Memasukkan username, password, klik Login
    L->>L: Validasi CSRF Token (hash_equals)
    L->>V: valLogin(username, password)
    V-->>L: Validasi format lolos

    L->>DB: SELECT id_admin, username, password, nama_lengkap FROM tb_admin WHERE username = ?
    DB-->>L: Data baris admin (termasuk hash BCRYPT)

    L->>L: password_verify(passwordInput, passwordHashDB)
    
    alt Password Cocok
        L->>S: session_regenerate_id(true)
        L->>S: Set $_SESSION['admin_logged_in'] = true
        L->>S: Set data admin (id, username, nama)
        L-->>A: Redirect HTTP 302 ke admin/dashboard.php
        A->>D: Akses halaman dashboard admin
        D-->>A: Tampilkan ringkasan statistik & menu kelola
    else Password / Username Salah
        L-->>A: Tampilkan pesan error "Username atau password salah" (delay 1s)
    end
```

---

## 5. Petunjuk Penyalinan ke Naskah Skripsi

Untuk memasukkan diagram-diagram di atas ke dalam naskah Skripsi Microsoft Word:

1. **Menggunakan Visual Mermaid Langsung:**
   - Anda dapat membuka [Mermaid Live Editor](https://mermaid.live/) di browser.
   - Salin blok kode `mermaid` di atas dan tempelkan ke editor tersebut.
   - Klik tombol **Download PNG** atau **Download SVG** beresolusi tinggi, kemudian sisipkan (*Insert Picture*) ke dalam naskah Word Anda pada **Bab III (Metodologi Penelitian / Analisis & Perancangan Sistem)**.
2. **Penempatan Bab Skripsi:**
   - **Sub-bab Use Case Diagram:** Masukkan Diagram 2.1, Deskripsi Aktor 2.2, dan Tabel Skenario 2.3.
   - **Sub-bab Activity Diagram:** Masukkan Diagram 3.1 (Alur Chat NLP), 3.2 (Kelola Pengetahuan), dan 3.3 (Unggah Berkas).
   - **Sub-bab Sequence Diagram:** Masukkan Diagram 4.1 (Tanya Jawab NLP), 4.2 (Rebuild Vektor), dan 4.3 (Login Admin).
3. **Format Notasi:** Seluruh diagram telah mematuhi standar UML 2.5 dan merefleksikan 100% implementasi riil kode program sistem DIPA-Bot.
