-- ====================================================================
-- DATABASE CHATBOT AKADEMIK UNIVERSITAS DIPA MAKASSAR
-- Skripsi: Pengembangan Chatbot Cerdas Berbasis Web Menggunakan NLP
-- Peneliti: Mohammad Ali Riedza (NIM: 221084)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS chatbot_undipa;
USE chatbot_undipa;

-- ====================================================================
-- TABEL 1: tb_admin (Pengelola Sistem)
-- ====================================================================
CREATE TABLE tb_admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password: admin123 (BCRYPT Hash)
INSERT INTO tb_admin (username, password, nama_lengkap, email) VALUES
('admin', '$2y$12$ADG.vo3Gt7fA9yyMx6ybXuuVOI0UnCTBBKU9G6H87Rw/NTykwxdQ6', 'Administrator UNDIPA', 'admin@undipa.ac.id'),
('layanan_akademik', '$2y$12$ADG.vo3Gt7fA9yyMx6ybXuuVOI0UnCTBBKU9G6H87Rw/NTykwxdQ6', 'Staf Layanan Akademik', 'akademik@undipa.ac.id');

-- ====================================================================
-- TABEL 2: tb_pengetahuan (Knowledge Base Q&A)
-- ====================================================================
CREATE TABLE tb_pengetahuan (
    id_pengetahuan INT PRIMARY KEY AUTO_INCREMENT,
    pertanyaan TEXT NOT NULL,
    jawaban TEXT NOT NULL,
    file_lampiran VARCHAR(255) DEFAULT NULL,
    kategori VARCHAR(50) DEFAULT 'Umum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Dummy Akademik (8 Topik Utama)
INSERT INTO tb_pengetahuan (pertanyaan, jawaban, file_lampiran, kategori) VALUES

-- 1. PROSEDUR KRS
('Bagaimana cara mengisi KRS online?', 'Untuk mengisi KRS online, mahasiswa harus login ke portal akademik menggunakan NIM dan password, kemudian pilih menu KRS, pilih mata kuliah yang tersedia sesuai jadwal, lalu klik Simpan. Pastikan KRS diisi sebelum batas waktu yang ditentukan.', NULL, 'KRS'),

('Apa syarat mengisi KRS?', 'Syarat mengisi KRS adalah mahasiswa harus aktif (sudah melakukan registrasi pembayaran semester berjalan), tidak memiliki tunggakan administrasi, dan memiliki Kartu Rencana Studi yang telah disetujui Dosen Pembimbing Akademik (PA).', 'panduan-krs_2b49c.pdf', 'KRS'),

('Kapan batas waktu pengisian KRS?', 'Batas waktu pengisian KRS adalah 2 minggu setelah perkuliahan semester baru dimulai. Untuk semester genap biasanya di akhir Februari, sedangkan semester ganjil di akhir Agustus. Cek kalender akademik untuk tanggal pasti.', NULL, 'KRS'),

-- 2. JADWAL KULIAH
('Bagaimana cara melihat jadwal kuliah?', 'Jadwal kuliah dapat dilihat melalui portal akademik di menu Jadwal Perkuliahan atau melalui papan pengumuman di gedung fakultas. Jadwal mencakup hari, jam, ruangan, dan nama dosen pengampu.', NULL, 'Jadwal'),

('Apakah jadwal kuliah bisa berubah?', 'Ya, jadwal kuliah dapat berubah sewaktu-waktu karena keperluan dosen atau perubahan ruangan. Mahasiswa diharapkan selalu mengecek portal akademik dan grup kelas untuk informasi terbaru.', NULL, 'Jadwal'),

-- 3. UJIAN AKHIR SEMESTER (UAS)
('Kapan jadwal UAS semester ini?', 'Jadwal UAS akan diumumkan melalui portal akademik dan papan pengumuman 2 minggu sebelum pelaksanaan. Biasanya UAS dilaksanakan pada minggu ke-16 hingga ke-18 setiap semester. Pastikan memeriksa jadwal secara berkala.', NULL, 'UAS'),

('Apa syarat mengikuti UAS?', 'Syarat mengikuti UAS adalah kehadiran minimal 75% dari total pertemuan, tidak memiliki tunggakan pembayaran, dan telah mengisi KRS dengan benar. Mahasiswa yang tidak memenuhi syarat tidak diperkenankan mengikuti ujian.', NULL, 'UAS'),

('Bagaimana jika tidak bisa hadir saat UAS?', 'Jika berhalangan hadir saat UAS karena sakit atau keperluan mendesak, mahasiswa harus mengajukan surat keterangan resmi (seperti surat dokter) ke bagian akademik maksimal 3 hari setelah jadwal UAS. Ujian susulan akan dijadwalkan kemudian.', NULL, 'UAS'),

-- 4. KALENDER AKADEMIK
('Bagaimana cara melihat kalender akademik?', 'Kalender akademik dapat diunduh di website resmi Universitas Dipa Makassar atau dilihat di portal mahasiswa. Kalender ini memuat jadwal penting seperti awal semester, registrasi, KRS, UTS, UAS, dan libur akademik.', 'kalender-akademik-2024.pdf', 'Kalender'),

('Kapan libur semester?', 'Libur semester biasanya berlangsung 2-3 minggu setelah pelaksanaan UAS dan sebelum semester baru dimulai. Untuk informasi detail silakan cek kalender akademik tahun berjalan.', NULL, 'Kalender'),

-- 5. PANDUAN SKRIPSI
('Apa syarat mengambil skripsi?', 'Syarat mengambil skripsi adalah telah menempuh minimal 120 SKS dengan IPK minimal 2.00, telah lulus mata kuliah prasyarat (Metodologi Penelitian), tidak memiliki tunggakan administrasi, dan mendapat persetujuan dari Ketua Program Studi.', 'panduan-skripsi_8a23d.pdf', 'Skripsi'),

('Bagaimana prosedur pengajuan judul skripsi?', 'Mahasiswa mengajukan 3 judul skripsi melalui form pengajuan judul, kemudian form diserahkan ke Ketua Prodi untuk review. Setelah disetujui, mahasiswa akan mendapat surat penunjukan pembimbing skripsi.', NULL, 'Skripsi'),

('Berapa lama waktu pengerjaan skripsi?', 'Waktu standar pengerjaan skripsi adalah 6 bulan (1 semester). Mahasiswa dapat mengajukan perpanjangan maksimal 2 semester dengan persetujuan dosen pembimbing dan Ketua Prodi.', NULL, 'Skripsi'),

-- 6. TEMPLATE SKRIPSI
('Dimana saya bisa download template skripsi?', 'Template skripsi resmi Universitas Dipa Makassar dapat diunduh melalui link berikut. Template ini mencakup format cover, abstrak, BAB I-V, dan daftar pustaka sesuai pedoman penulisan ilmiah.', 'template-skripsi_8f91a.docx', 'Skripsi'),

('Apakah ada template proposal skripsi?', 'Ya, template proposal skripsi tersedia dalam satu paket dengan template skripsi lengkap. Anda dapat mengunduhnya melalui portal atau bertanya ke bagian akademik.', 'template-skripsi_8f91a.docx', 'Skripsi'),

-- 7. KARTU KONTROL BIMBINGAN
('Apa itu kartu kontrol bimbingan?', 'Kartu kontrol bimbingan adalah dokumen wajib yang digunakan mahasiswa untuk mencatat setiap pertemuan bimbingan skripsi dengan dosen pembimbing. Kartu ini harus ditandatangani dosen setiap kali bimbingan dan diserahkan saat pendaftaran sidang.', 'kartu-kontrol-bimbingan_4c72e.pdf', 'Skripsi'),

('Berapa kali minimal bimbingan skripsi?', 'Minimal bimbingan skripsi adalah 8 kali pertemuan dengan setiap pembimbing (Pembimbing I dan Pembimbing II). Total minimal 16 kali bimbingan yang tercatat di kartu kontrol.', NULL, 'Skripsi'),

-- 8. FORM EKSTRAKURIKULER
('Bagaimana cara mendaftar kegiatan ekstrakurikuler?', 'Mahasiswa dapat mendaftar kegiatan ekstrakurikuler dengan mengisi form pendaftaran yang tersedia di bagian kemahasiswaan atau mengunduh form berikut, kemudian diserahkan ke koordinator UKM yang dituju.', 'form-ekstrakurikuler_9d14b.pdf', 'Kemahasiswaan'),

('Apa saja jenis UKM di UNDIPA?', 'Universitas Dipa Makassar memiliki berbagai UKM seperti UKM Olahraga (Basket, Futsal, Badminton), UKM Seni (Musik, Teater), UKM Keilmuan (Robotika, English Club), dan UKM Kerohanian.', NULL, 'Kemahasiswaan');

-- ====================================================================
-- TABEL 3: tb_kata_kunci (Normalisasi & Sinonim untuk NLP)
-- ====================================================================
CREATE TABLE tb_kata_kunci (
    id_kata INT PRIMARY KEY AUTO_INCREMENT,
    kata_asli VARCHAR(100) NOT NULL,
    kata_baku VARCHAR(100) NOT NULL,
    jenis ENUM('singkatan', 'typo', 'sinonim') DEFAULT 'sinonim'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Normalisasi (Typo, Singkatan, Kata Gaul)
INSERT INTO tb_kata_kunci (kata_asli, kata_baku, jenis) VALUES
('gimana', 'bagaimana', 'sinonim'),
('gmn', 'bagaimana', 'singkatan'),
('krsan', 'krs', 'typo'),
('krrs', 'krs', 'typo'),
('uas', 'ujian akhir semester', 'singkatan'),
('uts', 'ujian tengah semester', 'singkatan'),
('dospem', 'dosen pembimbing', 'singkatan'),
('pa', 'pembimbing akademik', 'singkatan'),
('prodi', 'program studi', 'singkatan'),
('skripsweet', 'skripsi', 'typo'),
('skripsih', 'skripsi', 'typo'),
('template', 'template', 'sinonim'),
('templat', 'template', 'typo'),
('jadwal', 'jadwal', 'sinonim'),
('jadwa', 'jadwal', 'typo'),
('jadwall', 'jadwal', 'typo'),
('donlot', 'unduh', 'sinonim'),
('download', 'unduh', 'sinonim'),
('ekstrakurikuler', 'ekstrakurikuler', 'sinonim'),
('ekskul', 'ekstrakurikuler', 'singkatan'),
('ukm', 'unit kegiatan mahasiswa', 'singkatan');

-- ====================================================================
-- TABEL 4: tb_log_chat (Riwayat Percakapan & Monitoring)
-- ====================================================================
CREATE TABLE tb_log_chat (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    pertanyaan_user TEXT NOT NULL,
    jawaban_bot TEXT NOT NULL,
    skor_similarity DECIMAL(5,4) DEFAULT 0.0000,
    id_pengetahuan_matched INT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengetahuan_matched) REFERENCES tb_pengetahuan(id_pengetahuan) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk performa query log
CREATE INDEX idx_created_at ON tb_log_chat(created_at);
CREATE INDEX idx_skor ON tb_log_chat(skor_similarity);

-- ====================================================================
-- TABEL 5: tb_vektor_tfidf (Cache Vektor untuk Percepatan Chat)
-- Menyimpan hasil preprocessing + bobot (TF) per dokumen Q&A sehingga
-- engine chat tidak perlu menghitung ulang seluruh knowledge base.
-- ====================================================================
CREATE TABLE tb_vektor_tfidf (
    id_vektor INT PRIMARY KEY AUTO_INCREMENT,
    id_pengetahuan INT NOT NULL,
    term VARCHAR(100) NOT NULL,
    bobot_tfidf DECIMAL(8,6) NOT NULL,
    FOREIGN KEY (id_pengetahuan) REFERENCES tb_pengetahuan(id_pengetahuan) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk mempercepat pencarian pencocokan kata saat chatting
CREATE INDEX idx_term ON tb_vektor_tfidf(term);
CREATE INDEX idx_id_pengetahuan ON tb_vektor_tfidf(id_pengetahuan);

-- ====================================================================
-- SELESAI
-- ====================================================================
