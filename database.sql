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
('Bagaimana cara mengisi KRS online SIAKAD?', 'Alur dan prosedur pengisian KRS online di Universitas Dipa Makassar:\n1) Mahasiswa melunasi SPP semester berjalan di rekening/bank resmi universitas.\n2) Mengakses portal SISKA (https://siska.undipa.ac.id/) untuk mengunduh daftar kode mata kuliah program studi (SI, TI, BD, RPL, MI, KWU).\n3) Membuka menu KRS Reguler di SIAKA dan menginput kode mata kuliah yang diprogramkan.\n4) Mengajukan persetujuan (approval) ke Dosen Pembimbing Akademik (PA).\nJika mengalami kendala, hubungi BAAK via Telp 0411-587194 atau WA 0812-2822-1994.', 'panduan-krs_2b49c.pdf', 'KRS'),

('Apa syarat mengisi KRS?', 'Syarat mengisi KRS adalah mahasiswa harus aktif (sudah melakukan registrasi pembayaran semester berjalan), tidak memiliki tunggakan administrasi, dan memiliki Kartu Rencana Studi yang telah disetujui Dosen Pembimbing Akademik (PA).', 'panduan-krs_2b49c.pdf', 'KRS'),

('Kapan batas waktu pengisian KRS?', 'Batas waktu pengisian KRS adalah 2 minggu setelah perkuliahan semester baru dimulai. Untuk semester genap biasanya di akhir Februari, sedangkan semester ganjil di akhir Agustus. Cek kalender akademik untuk tanggal pasti.', NULL, 'KRS'),

-- 2. JADWAL KULIAH
('Bagaimana cara melihat jadwal kuliah?', 'Jadwal kuliah dapat dilihat melalui portal akademik SISKA di website https://siska.undipa.ac.id/ pada menu Jadwal Perkuliahan atau melalui papan pengumuman di gedung fakultas. Jadwal mencakup hari, jam, ruangan, dan nama dosen pengampu.', NULL, 'Jadwal'),

('Apakah jadwal kuliah bisa berubah?', 'Ya, jadwal kuliah dapat berubah sewaktu-waktu karena keperluan dosen atau perubahan ruangan. Mahasiswa diharapkan selalu mengecek portal akademik dan grup kelas untuk informasi terbaru.', NULL, 'Jadwal'),

-- 3. UJIAN AKHIR SEMESTER (UAS)
('Kapan jadwal UAS semester ini?', 'Jadwal UAS akan diumumkan melalui portal akademik dan papan pengumuman 2 minggu sebelum pelaksanaan. Biasanya UAS dilaksanakan pada minggu ke-16 hingga ke-18 setiap semester. Pastikan memeriksa jadwal secara berkala.', NULL, 'UAS'),

('Apa syarat mengikuti UAS?', 'Untuk mengikuti UAS, mahasiswa wajib membawa Kartu Ujian yang dapat diunduh atau diperoleh melalui SISKA. Kartu Ujian memuat foto mahasiswa serta kode atau mata kuliah yang akan diikuti. Mahasiswa juga wajib membawa KTM, KTP, atau SIM, datang paling lambat 15 menit sebelum ujian, serta berpakaian sopan dan rapi.', NULL, 'UAS'),

('Bagaimana jika tidak bisa hadir saat UAS?', 'Jika berhalangan hadir saat UAS karena sakit atau keperluan mendesak, mahasiswa harus mengajukan surat keterangan resmi (seperti surat dokter) ke bagian akademik maksimal 3 hari setelah jadwal UAS. Ujian susulan akan dijadwalkan kemudian.', NULL, 'UAS'),

-- 4. KALENDER AKADEMIK
('Bagaimana cara melihat kalender akademik?', 'Kalender akademik dapat diunduh di website resmi Universitas Dipa Makassar atau dilihat di portal mahasiswa. Kalender ini memuat jadwal penting seperti awal semester, registrasi, KRS, UTS, UAS, dan libur akademik.', 'Kalender Akademik 2026_2027 Universitas Dipa Makassar.pdf', 'Kalender'),

('Kapan libur semester?', 'Libur semester biasanya berlangsung 2-3 minggu setelah pelaksanaan UAS dan sebelum semester baru dimulai. Untuk informasi detail silakan cek kalender akademik tahun berjalan.', NULL, 'Kalender'),

-- 5. PANDUAN SKRIPSI
('Apa syarat mengambil skripsi?', 'Mahasiswa dapat memprogramkan Seminar Proposal dan Skripsi pada semester ganjil maupun genap dengan ketentuan telah melulusi minimal 126 SKS untuk semester ganjil atau 116 SKS untuk semester genap, serta memiliki IPK minimal 2,0. Mahasiswa juga harus memenuhi prasyarat mata kuliah yang ditentukan, termasuk telah melulusi Program Pembuatan Aplikasi Konsentrasi atau Start-Up I sesuai program studi. Untuk mahasiswa angkatan 2026, terdapat ketentuan tambahan terkait MKWK dengan nilai minimal B dan jumlah nilai D tidak lebih dari 10%.', 'panduan-skripsi_8a23d.pdf', 'Skripsi'),

('Bagaimana prosedur pengajuan judul skripsi?', 'Prosedur pengajuan judul skripsi dimulai dengan menentukan topik dan judul sesuai ranah penelitian program studi, kemudian mengajukan judul kepada Ketua Program Studi untuk diverifikasi. Setelah judul disetujui, mahasiswa mendapatkan dosen pembimbing dan dapat memulai proses bimbingan hingga skripsi selesai. Setelah memenuhi persyaratan dan mendapatkan persetujuan pembimbing, mahasiswa dapat mengajukan diri untuk mengikuti sidang skripsi sesuai jadwal dan ketentuan yang berlaku.', NULL, 'Skripsi'),

('Berapa lama waktu pengerjaan skripsi?', 'Waktu standar pengerjaan skripsi adalah 6 bulan (1 semester). Mahasiswa dapat mengajukan perpanjangan maksimal 2 semester dengan persetujuan dosen pembimbing dan Ketua Prodi.', NULL, 'Skripsi'),

-- 6. TEMPLATE SKRIPSI
('Dimana saya bisa download template skripsi?', 'Template skripsi resmi Universitas Dipa Makassar dapat diunduh melalui link berikut. Template ini mencakup format cover, abstrak, BAB I-V, dan daftar pustaka sesuai pedoman penulisan ilmiah.', 'Template SKRIPSI_Infokom_Undipa_2025 (3).docx', 'Skripsi'),

('Apakah ada template proposal skripsi?', 'Ya, template proposal skripsi juga sudah tersedia dan terpisah dari template skripsi. Anda dapat mengunduhnya secara langsung melalui link dokumen berikut.', 'Template PROPOSAL_Infokom_Undipa_2025 (1).docx', 'Skripsi'),

-- 7. KARTU KONTROL BIMBINGAN
('Apa itu kartu kontrol bimbingan?', 'Kartu kontrol bimbingan adalah dokumen wajib yang digunakan mahasiswa untuk mencatat setiap pertemuan bimbingan skripsi dengan dosen pembimbing. Kartu ini harus ditandatangani dosen setiap kali bimbingan dan diserahkan saat pendaftaran sidang.', 'FR-DP-04-22 Lembar konsultasi pembimbing KKLP Skripsi.pdf', 'Skripsi'),

('Berapa kali minimal bimbingan skripsi?', 'Mahasiswa wajib melakukan bimbingan dengan dua dosen pembimbing, yaitu Pembimbing I dan Pembimbing II. Bimbingan dilakukan minimal 3 kali dengan masing-masing pembimbing untuk Proposal dan 3 kali untuk Skripsi. Setiap hasil konsultasi dan koreksi dari dosen pembimbing harus dicatat pada kartu atau lembar kontrol/konsultasi bimbingan sebagai bukti pelaksanaan bimbingan.', NULL, 'Skripsi'),

-- 8. FORM EKSTRAKURIKULER
('Bagaimana cara mendaftar kegiatan ekstrakurikuler?', 'Mahasiswa dapat mendaftar kegiatan ekstrakurikuler dengan mengisi form pendaftaran yang tersedia di bagian kemahasiswaan atau mengunduh form berikut, kemudian diserahkan ke koordinator UKM yang dituju.', 'form-ekstrakurikuler_9d14b.pdf', 'Kemahasiswaan'),

('Apa saja jenis UKM di UNDIPA?', 'Universitas Dipa Makassar memiliki berbagai UKM seperti UKM Olahraga (Basket, Futsal, Badminton), UKM Seni (Musik, Teater), UKM Keilmuan (Robotika, English Club), dan UKM Kerohanian.', NULL, 'Kemahasiswaan'),

-- 9. PENGISIAN KRS & PERUBAHAN
('Bagaimana alur dan prosedur pengisian KRS online?', 'Alur pengisian KRS di Universitas Dipa Makassar: 1) Mahasiswa melunasi SPP semester berjalan di bank/rekening resmi universitas. 2) Masuk ke portal SISKA (https://siska.undipa.ac.id/) untuk mengunduh daftar kode mata kuliah program studi (SI, TI, BD, RPL, MI, KWU). 3) Membuka menu KRS Reguler di SIAKA dan menginput kode mata kuliah. 4) Mengajukan persetujuan (approval) ke Dosen Pembimbing Akademik (PA). Jika mengalami kendala, hubungi BAAK via Telp 0411-587194 atau WA 0812-2822-1994.', 'panduan-krs_2b49c.pdf', 'KRS'),
('Berapa batas maksimal SKS yang dapat diprogramkan dalam KRS?', 'Batas SKS yang boleh diambil ditentukan oleh Indeks Prestasi Semester (IPS) sebelumnya: IPS >= 3.00 dapat memprogramkan maksimal 24 SKS, IPS 2.50 - 2.99 maksimal 21 SKS, IPS 2.00 - 2.49 maksimal 18 SKS, dan IPS < 2.00 maksimal 15 SKS sesuai pedoman akademik UNDIPA.', NULL, 'KRS'),
('Apa yang harus dilakukan jika KRS terkunci atau belum di-ACC oleh Dosen PA?', 'Jika status KRS terkunci atau belum disetujui, segera hubungi Dosen Pembimbing Akademik (PA) Anda melalui kontak resmi atau temui di ruang dosen kampus UNDIPA (Jl. Perintis Kemerdekaan Km. 9) saat jam kerja, atau konsultasikan ke bagian BAAK UNDIPA.', NULL, 'KRS'),
('Bagaimana cara perbaikan atau batal tambah mata kuliah KRS?', 'Perbaikan atau batal-tambah mata kuliah dilayani selama masa revisi KRS (biasanya pada minggu ke-1 hingga ke-2 awal perkuliahan) melalui portal akademik SIAKA setelah berkonsultasi dan mendapatkan izin tertulis/digital dari Dosen Pembimbing Akademik.', NULL, 'KRS'),

-- 10. EKSTRAKURIKULER & UKM
('Apa saja daftar UKM dan kegiatan ekstrakurikuler yang aktif di UNDIPA?', 'Unit Kegiatan Mahasiswa (UKM) aktif di Universitas Dipa Makassar terbagi dalam beberapa bidang: 1) Penalaran & Teknologi: UKM Robotika, English Club, IT Developer Club. 2) Olahraga: Basket, Futsal, Badminton, Catur. 3) Kesenian: Musik, Seni Tari, Teater. 4) Kerohanian: Lembaga Dakwah Kampus (LDK), Persekutuan Mahasiswa Kristen (PMK). 5) Kepanduan & Sosial: Pramuka, KSR-PMI, Mahasiswa Pecinta Alam (MAPALA).', NULL, 'Ekstrakurikuler'),
('Apa syarat, formulir penilaian, dan alur pendaftaran ekstrakurikuler UKM?', 'Syarat dan alur pendaftaran ekstrakurikuler atau UKM di UNDIPA:\n1) Mengunduh dan mengisi formulir pendaftaran UKM / penilaian keaktifan resmi.\n2) Melengkapi persyaratan berkas (KTM aktif, pas foto, dan surat kesediaan anggota).\n3) Mengikuti Masa Penerimaan Anggota Baru (MPAB) yang diselenggarakan pengurus UKM bersangkutan.\nCatatan: Keaktifan dalam UKM dan perlombaan akan dikonversi menjadi poin Surat Keterangan Pendamping Ijazah (SKPI).', 'form-ekstrakurikuler_9d14b.pdf', 'Ekstrakurikuler'),
('Apakah kegiatan ekstrakurikuler mendapatkan poin keaktifan atau sertifikat SKPI?', 'Ya, keaktifan dalam organisasi kemahasiswaan, kepanitiaan, dan kejuaraan perlombaan akan dikonversi menjadi poin Surat Keterangan Pendamping Ijazah (SKPI) yang diterbitkan resmi oleh Universitas Dipa Makassar bersamaan dengan ijazah kelulusan.', NULL, 'Ekstrakurikuler'),

-- 11. MBKM (MERDEKA BELAJAR KAMPUS MERDEKA)
('Apa syarat dan panduan untuk ikut program magang MBKM MSIB di UNDIPA?', 'Syarat dan ketentuan mengikuti program Magang MBKM / MSIB di UNDIPA:\n1) Mahasiswa aktif terdaftar pada PDDikti.\n2) Minimal telah menempuh semester 5 dengan jumlah SKS lulus minimal 80–90 SKS.\n3) Memiliki Indeks Prestasi Kumulatif (IPK) minimal 3.00.\n4) Bebas tunggakan akademik dan tidak ada mata kuliah prasyarat yang belum tuntas.\n5) Memperoleh surat rekomendasi resmi dari Ketua Program Studi.\nCatatan: Panduan teknis pelaksanaan dan konversi SKS program MBKM dapat diunduh pada tombol di bawah.', 'panduan-mbkm_undipa.pdf', 'MBKM'),
('Program MBKM apa saja yang dapat diikuti oleh mahasiswa?', 'Mahasiswa UNDIPA dapat mengikuti program MBKM nasional maupun mandiri: 1) Magang dan Studi Independen Bersertifikat (MSIB) bersama mitra industri/teknologi, 2) Kampus Mengajar (KM), 3) Pertukaran Mahasiswa Merdeka (PMM), 4) Wirausaha Merdeka (WMK), dan 5) Proyek Kemanusiaan / Riset Mandiri.', NULL, 'MBKM'),
('Bagaimana prosedur konversi nilai mata kuliah dari kegiatan program MBKM?', 'Prosedur konversi MBKM di UNDIPA: 1) Mahasiswa menyusun Learning Agreement (rencana penyetaraan mata kuliah) yang disetujui Kaprodi sebelum kegiatan dimulai. 2) Menjalani program dan menyusun laporan akhir serta menyertakan sertifikat resmi. 3) Melakukan seminar evaluasi/pertanggungjawaban di prodi. 4) Hasil kegiatan dikonversi hingga setara 20 SKS ke dalam transkrip nilai SIAKA.', NULL, 'MBKM'),

-- 12. BERKAS & BEBAS SKRIPSI / PROPOSAL
('Apa saja syarat berkas pendaftaran seminar proposal skripsi?', 'Syarat berkas pendaftaran Seminar Proposal Skripsi di UNDIPA:\n1) Telah melulusi minimal 116 SKS (semester genap) atau 126 SKS (semester ganjil) dengan IPK >= 2.00.\n2) Telah melulusi mata kuliah konsentrasi / Start-Up I sesuai program studi.\n3) Kartu Konsultasi Bimbingan (FR-DP-04-22) bertandatangan Pembimbing I dan II minimal 3 kali pertemuan per pembimbing.\n4) Bukti pembayaran seminar proposal dari bank atau bagian keuangan.\n5) Naskah proposal lengkap yang telah disetujui oleh kedua dosen pembimbing.\nCatatan: Template resmi penulisan proposal skripsi dapat diunduh langsung melalui tombol di bawah.', 'Template PROPOSAL_Infokom_Undipa_2025 (1).docx', 'Skripsi'),
('Apa saja syarat berkas pendaftaran ujian sidang skripsi?', 'Syarat pendaftaran Sidang Ujian Skripsi di UNDIPA: 1) Bebas nilai E dan nilai D maksimal 10%. 2) Kartu kontrol bimbingan skripsi lengkap minimal 3 kali bimbingan per dosen pembimbing. 3) Surat keterangan bebas plagiasi Turnitin sesuai batas toleransi prodi. 4) Surat Bebas Pustaka dari Perpustakaan UNDIPA. 5) Surat Bebas Keuangan dari Bagian Keuangan. 6) Naskah skripsi lengkap dijilid rapi.', 'FR-DP-04-22 Lembar konsultasi pembimbing KKLP Skripsi.pdf', 'Skripsi'),
('Bagaimana prosedur mengurus Surat Bebas Pustaka Perpustakaan?', 'Prosedur Bebas Pustaka di Perpustakaan UNDIPA: 1) Membawa Kartu Anggota Perpustakaan / KTM. 2) Memastikan tidak ada tanggungan peminjaman buku yang belum dikembalikan atau denda keterlambatan. 3) Menyerahkan softcopy skripsi/karya ilmiah lengkap untuk diunggah ke repositori perpustakaan kampus. 4) Petugas perpustakaan akan menerbitkan cap/Surat Bebas Pustaka.', NULL, 'Skripsi'),
('Bagaimana prosedur mengurus Surat Keterangan Bebas Keuangan?', 'Surat Bebas Keuangan diurus langsung di loket Bagian Keuangan Kampus UNDIPA (Jl. Perintis Kemerdekaan Km. 9). Mahasiswa wajib melunasi seluruh kewajiban pembayaran SPP/BPP, biaya skripsi, dan administrasi lainnya hingga sistem keuangan mencatat status lunas.', NULL, 'Skripsi'),

-- 13. JALUR PUBLIKASI (KONVERSI SKRIPSI)
('Apakah skripsi dapat digantikan dengan publikasi artikel ilmiah atau jurnal?', 'Ya, Universitas Dipa Makassar memfasilitasi kelulusan melalui jalur publikasi karya ilmiah. Mahasiswa yang berhasil mempublikasikan artikel ilmiah pada jurnal nasional terakreditasi (SINTA sesuai aturan prodi) atau jurnal internasional bereputasi dapat mengajukan konversi naskah artikel sebagai pengganti skripsi konvensional.', NULL, 'Publikasi'),
('Apa saja syarat mengajukan kelulusan jalur publikasi jurnal ilmiah?', 'Syarat kelulusan jalur publikasi di UNDIPA: 1) Mahasiswa tercatat sebagai first author (penulis pertama) dengan afiliasi Universitas Dipa Makassar. 2) Dosen pembimbing tercatat sebagai co-author. 3) Artikel telah berstatus Diterima (Letter of Acceptance / LoA) atau Terbit (Published) pada jurnal terakreditasi SINTA yang diakui prodi. 4) Menyerahkan naskah artikel, LoA resmi, dan bukti korespondensi ke Program Studi.', NULL, 'Publikasi'),
('Bagaimana alur konversi artikel jurnal ilmiah menjadi nilai skripsi?', 'Alur konversi publikasi: Mahasiswa mengajukan permohonan tertulis kepada Ketua Program Studi dengan melampirkan berkas naskah dan bukti publikasi/LoA. Tim penilai prodi akan memverifikasi keabsahan jurnal dan kesesuaian topik. Jika disetujui, nilai karya ilmiah dikonversi langsung menjadi nilai ujian skripsi tanpa sidang skripsi reguler.', NULL, 'Publikasi'),

-- 14. PENGAJUAN CUTI AKADEMIK
('Bagaimana alur dan prosedur permohonan cuti kuliah akademik?', 'Prosedur dan alur pengajuan permohonan cuti akademik di UNDIPA:\n1) Mengambil formulir permohonan cuti di loket BAAK atau mengunduh formulir di bawah.\n2) Meminta persetujuan Dosen Pembimbing Akademik (PA) dan Ketua Program Studi.\n3) Mengurus surat bebas tanggungan keuangan di Bagian Keuangan dan bebas perpustakaan.\n4) Menyerahkan seluruh berkas ke BAAK untuk diterbitkan Surat Keputusan (SK) Cuti resmi.\nCatatan: Pengajuan cuti wajib diproses sebelum semester berjalan dimulai agar status di PDDikti/SIAKAD tidak tercatat mangkir.', 'form-permohonan-cuti_undipa.pdf', 'Cuti'),
('Kapan batas waktu pengajuan cuti akademik dapat dilakukan?', 'Permohonan cuti akademik wajib diajukan sesuai jadwal registrasi cuti yang tertera pada Kalender Akademik resmi UNDIPA (umumnya selambat-lambatnya 2 minggu sebelum perkuliahan semester baru dimulai). Pengajuan di tengah semester yang sedang berjalan tidak diperkenankan.', NULL, 'Cuti'),
('Berapa lama maksimal mahasiswa diperbolehkan mengambil cuti kuliah?', 'Sesuai peraturan akademik UNDIPA, cuti akademik diberikan paling banyak 2 (dua) semester, baik secara berturut-turut maupun tidak berturut-turut selama masa studi. Masa cuti akademik resmi tidak dihitung ke dalam batas maksimal masa studi mahasiswa.', NULL, 'Cuti'),
('Bagaimana cara aktif kembali setelah menjalani masa cuti kuliah?', 'Untuk aktif kembali kuliah, mahasiswa wajib melapor ke BAAK dan Program Studi sebelum batas waktu pembayaran semester baru, membawa Surat Izin Cuti sebelumnya, melakukan pembayaran SPP aktif, dan memprogramkan mata kuliah di portal KRS SIAKA.', NULL, 'Cuti'),

-- 15. SURAT KETERANGAN LULUS (SKL)
('Apa itu Surat Keterangan Lulus (SKL) dan apa kegunaannya?', 'Surat Keterangan Lulus (SKL) adalah surat keterangan resmi yang diterbitkan oleh BAAK Universitas Dipa Makassar bagi mahasiswa yang telah dinyatakan lulus yudisium skripsi. SKL memuat pernyataan kelulusan, IPK, dan gelar sarjana, yang sah digunakan untuk keperluan melamar pekerjaan atau melanjutkan studi sebelum ijazah asli diterbitkan saat wisuda.', NULL, 'SKL'),
('Bagaimana prosedur mengurus Surat Keterangan Lulus (SKL)?', 'Prosedur pengurusan SKL di UNDIPA: 1) Telah dinyatakan lulus ujian skripsi dan menyelesaikan seluruh revisi penguji. 2) Menyerahkan lembar pengesahan yang telah ditandatangani lengkap oleh dosen penguji dan dekan/kaprodi. 3) Melampirkan bukti Bebas Pustaka dan Bebas Keuangan. 4) Mengajukan permohonan ke loket BAAK kampus. SKL biasanya selesai dalam 2-3 hari kerja.', NULL, 'SKL'),
('Berapa lama masa berlaku Surat Keterangan Lulus (SKL)?', 'Masa berlaku SKL UNDIPA umumnya adalah 6 (enam) bulan sejak tanggal diterbitkan atau sampai ijazah dan transkrip nilai asli resmi diserahkan pada saat upacara wisuda.', NULL, 'SKL'),

-- 16. MULAI PERKULIAHAN & JADWAL
('Kapan perkuliahan semester baru dimulai dan bagaimana melihat jadwalnya?', 'Jadwal perkuliahan mengacu pada Kalender Akademik resmi Universitas Dipa Makassar:\n1) Semester Ganjil: Perkuliahan umumnya dimulai pada awal September.\n2) Semester Genap: Perkuliahan dimulai pada akhir Februari hingga awal Maret.\n3) Jadwal Detail: Hari, jam, ruang kelas, dan dosen pengampu dapat dilihat melalui portal SISKA di https://siska.undipa.ac.id/\nCatatan: Kalender Akademik lengkap tahun akademik 2026/2027 dapat diunduh pada tombol di bawah.', 'Kalender Akademik 2026_2027 Universitas Dipa Makassar.pdf', 'Jadwal'),
('Kapan batas akhir pembayaran SPP dan pendaftaran ulang perkuliahan?', 'Batas akhir pembayaran SPP/BPP dan pendaftaran ulang semester baru biasanya ditutup 1 pekan sebelum perkuliahan hari pertama dimulai. Mahasiswa dapat memantau tanggal pasti pada Kalender Akademik di portal siska.undipa.ac.id untuk menghindari denda atau sanksi cuti otomatis.', NULL, 'Jadwal'),
('Apa sanksi jika mahasiswa tidak melakukan KRS atau terlambat masuk kuliah?', 'Mahasiswa yang tidak melakukan registrasi SPP dan pengisian KRS hingga batas akhir jadwal kalender akademik akan berstatus Non-Aktif (mangkir) pada semester berjalan. Status non-aktif tanpa izin cuti tetap diperhitungkan dalam masa studi aktif.', NULL, 'Jadwal'),

-- 17. WISUDA
('Apa saja syarat dan ketentuan pendaftaran wisuda sarjana di UNDIPA?', 'Syarat pendaftaran wisuda sarjana di Universitas Dipa Makassar: 1) Lulus ujian skripsi dan yudisium dengan nilai lengkap. 2) Menyerahkan naskah skripsi hard cover resmi yang telah disahkan. 3) Mengunggah file skripsi ke repositori perpustakaan kampus. 4) Memiliki bukti Bebas Pustaka, Bebas Keuangan, dan Bebas Laboratorium. 5) Melakukan verifikasi data diri (NIK, tempat/tanggal lahir) sesuai ijazah terakhir agar sinkron dengan PDDikti/NINA. 6) Mendaftar melalui portal wisuda resmi UNDIPA.', NULL, 'Wisuda'),
('Kapan jadwal penyelenggaraan wisuda dan bagaimana alurnya?', 'Universitas Dipa Makassar menyelenggarakan upacara wisuda secara berkala (umumnya 1-2 kali setahun). Jadwal pendaftaran, batas pengumpulan berkas, dan tanggal gladi bersih diumumkan melalui website resmi https://undipa.ac.id/ serta papan pengumuman kampus.', NULL, 'Wisuda'),
('Bagaimana prosedur pengambilan baju toga dan gladi bersih wisuda?', 'Baju toga dan atribut wisuda dapat diambil di bagian perlengkapan/kemahasiswaan kampus UNDIPA (Jl. Perintis Kemerdekaan Km. 9) dengan menunjukkan bukti pembayaran wisuda. Mahasiswa wajib mengikuti gladi bersih yang diadakan H-1 sebelum prosesi wisuda senat terbuka berlangsung.', NULL, 'Wisuda'),

-- 18. BUKU ALUMNI
('Apa itu Buku Alumni dan apa fungsinya bagi calon wisudawan?', 'Buku Alumni (Buku Wisuda) adalah buku dokumentasi profil dan rekam jejak kelulusan resmi yang diterbitkan oleh Universitas Dipa Makassar pada setiap periode wisuda. Buku ini memuat data wisudawan, pas foto resmi, judul skripsi, serta biodata lulusan untuk dokumentasi dan jaringan Ikatan Alumni (IKA UNDIPA).', NULL, 'Alumni'),
('Apa syarat pengumpulan pas foto dan data diri untuk Buku Alumni?', 'Syarat data Buku Alumni/Wisuda UNDIPA: 1) Pas foto formal terbaru mengenakan kemeja putih berdasi dan jas/blazer almamater (latar belakang merah/biru sesuai ketentuan). 2) Mengisi formulir pelacakan alumni / Tracer Study melalui portal resmi UNDIPA di https://site.undipa.ac.id/. 3) Menyerahkan softfile foto beresolusi tinggi dan cetak foto ukuran 3x4 dan 4x6 ke panitia wisuda.', NULL, 'Alumni'),
('Kapan batas waktu pengumpulan data Buku Alumni dan formulir tracer study?', 'Pengisian data Buku Wisuda dan formulir Tracer Study di site.undipa.ac.id wajib diselesaikan selambat-lambatnya bersamaan dengan batas penutupan pendaftaran wisuda. Keterlambatan penyerahan foto berisiko menyebabkan data wisudawan tidak termuat dalam cetakan Buku Wisuda.', NULL, 'Alumni');

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
('ukm', 'unit kegiatan mahasiswa', 'singkatan'),
('mbkm', 'merdeka belajar kampus merdeka', 'singkatan'),
('msib', 'magang studi independen bersertifikat', 'singkatan'),
('pmm', 'pertukaran mahasiswa merdeka', 'singkatan'),
('kampusmerdeka', 'merdeka belajar kampus merdeka', 'sinonim'),
('magang', 'magang', 'sinonim'),
('skl', 'surat keterangan lulus', 'singkatan'),
('skck', 'surat keterangan catatan kepolisian', 'singkatan'),
('suratlulus', 'surat keterangan lulus', 'sinonim'),
('cuti', 'cuti akademik', 'sinonim'),
('cutikampus', 'cuti akademik', 'sinonim'),
('cutikuliah', 'cuti akademik', 'sinonim'),
('berhentisementara', 'cuti akademik', 'sinonim'),
('wisuda', 'wisuda', 'sinonim'),
('yudisium', 'yudisium', 'sinonim'),
('wisudawan', 'wisuda', 'sinonim'),
('toga', 'perlengkapan wisuda', 'sinonim'),
('publikasi', 'publikasi ilmiah', 'sinonim'),
('jurnal', 'jurnal ilmiah', 'sinonim'),
('sinta', 'akreditasi sinta', 'singkatan'),
('scopus', 'indeks scopus', 'sinonim'),
('bebaspustaka', 'bebas pustaka perpustakaan', 'sinonim'),
('perpus', 'perpustakaan', 'singkatan'),
('perpustakaan', 'perpustakaan', 'sinonim'),
('alumni', 'alumni', 'sinonim'),
('bukualumni', 'buku alumni', 'sinonim'),
('tracerstudy', 'pelacakan alumni', 'sinonim'),
('bimbingan', 'bimbingan', 'sinonim'),
('kartukontrol', 'kartu kontrol bimbingan', 'sinonim'),
('sempro', 'seminar proposal', 'singkatan'),
('sidang', 'sidang skripsi', 'sinonim'),
('ujianproposal', 'seminar proposal', 'sinonim'),
('mulaikuliah', 'awal perkuliahan', 'sinonim'),
('awalkuliah', 'awal perkuliahan', 'sinonim'),
('masukkuliah', 'awal perkuliahan', 'sinonim'),
('biayakursus', 'biaya pendidikan', 'sinonim'),
('spp', 'sumbangan pembinaan pendidikan', 'singkatan'),
('bpp', 'biaya penyelenggaraan pendidikan', 'singkatan');

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
-- TABEL 6: tb_pengaturan (Pengaturan Teks Sistem — anti hardcode)
-- ====================================================================
CREATE TABLE tb_pengaturan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(50) NOT NULL UNIQUE,
    nilai TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tb_pengaturan (nama, nilai) VALUES
('greeting', 'Halo! Saya DIPA-Bot, asisten virtual layanan akademik Universitas Dipa Makassar.\nAda yang bisa saya bantu? Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.'),
('fallback', 'Maaf, DIPA-Bot belum memahami pertanyaan tersebut. Silakan tanyakan seputar KRS, Jadwal Kuliah, UAS, Skripsi, atau topik akademik lainnya.');

-- ====================================================================
-- SELESAI
-- ====================================================================
