-- ====================================================================
-- MIGRASI PENAMBAHAN DATASET KNOWLEDGE BASE & KAMUS NORMALISASI
-- Diselaraskan dengan Data Resmi Universitas Dipa Makassar (UNDIPA)
-- (BAAK, SISKA siska.undipa.ac.id, Tracer Study site.undipa.ac.id, Hotline 0812-2822-1994)
-- Mahasiswa: Mohammad Ali Riedza (NIM: 221084) - Sistem Informasi S1
-- ====================================================================

-- 1. TAMBAH KAMUS NORMALISASI KATA KUNCI (tb_kata_kunci)
INSERT INTO tb_kata_kunci (kata_asli, kata_baku, jenis) VALUES
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
('bukuwisuda', 'buku alumni', 'sinonim'),
('tracerstudy', 'pelacakan alumni', 'sinonim'),
('baak', 'biro administrasi akademik dan kemahasiswaan', 'singkatan'),
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
('bpp', 'biaya penyelenggaraan pendidikan', 'singkatan')
ON DUPLICATE KEY UPDATE kata_baku = VALUES(kata_baku);

-- 2. TAMBAH & SELARASKAN DATA KNOWLEDGE BASE 10 TOPIK AKADEMIK RESMI (tb_pengetahuan)
INSERT INTO tb_pengetahuan (pertanyaan, jawaban, file_lampiran, kategori) VALUES

-- [TOPIK 1: KRS & LAYANAN AKADEMIK SISKA]
('Bagaimana alur dan prosedur pengisian KRS online?', 'Alur pengisian KRS di Universitas Dipa Makassar: 1) Mahasiswa melunasi SPP semester berjalan di bank/rekening resmi universitas. 2) Masuk ke portal SISKA (https://siska.undipa.ac.id/) untuk mengunduh daftar kode mata kuliah program studi (SI, TI, BD, RPL, MI, KWU). 3) Membuka menu KRS Reguler di SIAKA dan menginput kode mata kuliah. 4) Mengajukan persetujuan (approval) ke Dosen Pembimbing Akademik (PA). Jika mengalami kendala, hubungi BAAK via Telp 0411-587194 atau WA 0812-2822-1994.', 'panduan-krs_2b49c.pdf', 'KRS'),
('Berapa batas maksimal SKS yang dapat diprogramkan dalam KRS?', 'Batas SKS yang boleh diambil ditentukan oleh Indeks Prestasi Semester (IPS) sebelumnya: IPS >= 3.00 dapat memprogramkan maksimal 24 SKS, IPS 2.50 - 2.99 maksimal 21 SKS, IPS 2.00 - 2.49 maksimal 18 SKS, dan IPS < 2.00 maksimal 15 SKS sesuai pedoman akademik UNDIPA.', NULL, 'KRS'),
('Apa yang harus dilakukan jika KRS terkunci atau belum di-ACC oleh Dosen PA?', 'Jika status KRS terkunci atau belum disetujui, segera hubungi Dosen Pembimbing Akademik (PA) Anda melalui kontak resmi atau temui di ruang dosen kampus UNDIPA (Jl. Perintis Kemerdekaan Km. 9) saat jam kerja, atau konsultasikan ke bagian BAAK UNDIPA.', NULL, 'KRS'),
('Bagaimana cara perbaikan atau batal tambah mata kuliah KRS?', 'Perbaikan atau batal-tambah mata kuliah dilayani selama masa revisi KRS (biasanya pada minggu ke-1 hingga ke-2 awal perkuliahan) melalui portal akademik SIAKA setelah berkonsultasi dan mendapatkan izin tertulis/digital dari Dosen Pembimbing Akademik.', NULL, 'KRS'),

-- [TOPIK 2: EKSTRAKURIKULER & UKM KAMPUS]
('Apa saja daftar UKM dan kegiatan ekstrakurikuler yang aktif di UNDIPA?', 'Unit Kegiatan Mahasiswa (UKM) aktif di Universitas Dipa Makassar terbagi dalam beberapa bidang: 1) Penalaran & Teknologi: UKM Robotika, English Club, IT Developer Club. 2) Olahraga: Basket, Futsal, Badminton, Catur. 3) Kesenian: Musik, Seni Tari, Teater. 4) Kerohanian: Lembaga Dakwah Kampus (LDK), Persekutuan Mahasiswa Kristen (PMK). 5) Kepanduan & Sosial: Pramuka, KSR-PMI, Mahasiswa Pecinta Alam (MAPALA).', NULL, 'Ekstrakurikuler'),
('Bagaimana alur pendaftaran menjadi anggota kegiatan ekstrakurikuler atau UKM?', 'Alur pendaftaran UKM: 1) Mengambil form pendaftaran di Ruang Kemahasiswaan Kampus UNDIPA atau mengunduh formulir resmi. 2) Melengkapi persyaratan (KTM aktif, pas foto, surat kesediaan). 3) Mengikuti Masa Penerimaan Anggota Baru (MPAB) yang diselenggarakan oleh pengurus UKM bersangkutan.', 'form-ekstrakurikuler_9d14b.pdf', 'Ekstrakurikuler'),
('Apakah kegiatan ekstrakurikuler mendapatkan poin keaktifan atau sertifikat SKPI?', 'Ya, keaktifan dalam organisasi kemahasiswaan, kepanitiaan, dan kejuaraan perlombaan akan dikonversi menjadi poin Surat Keterangan Pendamping Ijazah (SKPI) yang diterbitkan resmi oleh Universitas Dipa Makassar bersamaan dengan ijazah kelulusan.', NULL, 'Ekstrakurikuler'),

-- [TOPIK 3: MBKM (MERDEKA BELAJAR KAMPUS MERDEKA)]
('Apa syarat dan ketentuan untuk mengikuti program MBKM di UNDIPA?', 'Syarat mengikuti program MBKM di Universitas Dipa Makassar: 1) Mahasiswa aktif terdaftar pada PDDikti. 2) Minimal telah menempuh semester 5 dengan jumlah SKS lulus minimal 80-90 SKS. 3) Memiliki IPK minimal 3.00. 4) Tidak memiliki tunggakan akademik atau mata kuliah prasyarat yang belum tuntas. 5) Memperoleh surat rekomendasi resmi dari Ketua Program Studi.', NULL, 'MBKM'),
('Program MBKM apa saja yang dapat diikuti oleh mahasiswa?', 'Mahasiswa UNDIPA dapat mengikuti program MBKM nasional maupun mandiri: 1) Magang dan Studi Independen Bersertifikat (MSIB) bersama mitra industri/teknologi, 2) Kampus Mengajar (KM), 3) Pertukaran Mahasiswa Merdeka (PMM), 4) Wirausaha Merdeka (WMK), dan 5) Proyek Kemanusiaan / Riset Mandiri.', NULL, 'MBKM'),
('Bagaimana prosedur konversi nilai mata kuliah dari kegiatan program MBKM?', 'Prosedur konversi MBKM di UNDIPA: 1) Mahasiswa menyusun Learning Agreement (rencana penyetaraan mata kuliah) yang disetujui Kaprodi sebelum kegiatan dimulai. 2) Menjalani program dan menyusun laporan akhir serta menyertakan sertifikat resmi. 3) Melakukan seminar evaluasi/pertanggungjawaban di prodi. 4) Hasil kegiatan dikonversi hingga setara 20 SKS ke dalam transkrip nilai SIAKA.', NULL, 'MBKM'),

-- [TOPIK 4: BERKAS & BEBAS SKRIPSI / PROPOSAL]
('Apa saja syarat berkas pendaftaran seminar proposal skripsi?', 'Syarat pendaftaran Seminar Proposal di UNDIPA: 1) Telah melulusi minimal 116 SKS (semester genap) atau 126 SKS (semester ganjil) dengan IPK >= 2.00. 2) Lulus mata kuliah konsentrasi/Start-Up I. 3) Kartu Konsultasi Bimbingan (FR-DP-04-22) bertandatangan Pembimbing I dan II minimal 3 kali pertemuan per pembimbing. 4) Bukti pembayaran seminar proposal di bank/bagian keuangan. 5) Naskah proposal disetujui pembimbing.', 'Template PROPOSAL_Infokom_Undipa_2025 (1).docx', 'Skripsi'),
('Apa saja syarat berkas pendaftaran ujian sidang skripsi?', 'Syarat pendaftaran Sidang Ujian Skripsi di UNDIPA: 1) Bebas nilai E dan nilai D maksimal 10%. 2) Kartu kontrol bimbingan skripsi lengkap minimal 3 kali bimbingan per dosen pembimbing. 3) Surat keterangan bebas plagiasi Turnitin sesuai batas toleransi prodi. 4) Surat Bebas Pustaka dari Perpustakaan UNDIPA. 5) Surat Bebas Keuangan dari Bagian Keuangan. 6) Naskah skripsi lengkap dijilid rapi.', 'FR-DP-04-22 Lembar konsultasi pembimbing KKLP Skripsi.pdf', 'Skripsi'),
('Bagaimana prosedur mengurus Surat Bebas Pustaka Perpustakaan?', 'Prosedur Bebas Pustaka di Perpustakaan UNDIPA: 1) Membawa Kartu Anggota Perpustakaan / KTM. 2) Memastikan tidak ada tanggungan peminjaman buku yang belum dikembalikan atau denda keterlambatan. 3) Menyerahkan softcopy skripsi/karya ilmiah lengkap untuk diunggah ke repositori perpustakaan kampus. 4) Petugas perpustakaan akan menerbitkan cap/Surat Bebas Pustaka.', NULL, 'Skripsi'),
('Bagaimana prosedur mengurus Surat Keterangan Bebas Keuangan?', 'Surat Bebas Keuangan diurus langsung di loket Bagian Keuangan Kampus UNDIPA (Jl. Perintis Kemerdekaan Km. 9). Mahasiswa wajib melunasi seluruh kewajiban pembayaran SPP/BPP, biaya skripsi, dan administrasi lainnya hingga sistem keuangan mencatat status lunas.', NULL, 'Skripsi'),

-- [TOPIK 5: JALUR PUBLIKASI (KONVERSI SKRIPSI)]
('Apakah skripsi dapat digantikan dengan publikasi artikel ilmiah atau jurnal?', 'Ya, Universitas Dipa Makassar memfasilitasi kelulusan melalui jalur publikasi karya ilmiah. Mahasiswa yang berhasil mempublikasikan artikel ilmiah pada jurnal nasional terakreditasi (SINTA sesuai aturan prodi) atau jurnal internasional bereputasi dapat mengajukan konversi naskah artikel sebagai pengganti skripsi konvensional.', NULL, 'Publikasi'),
('Apa saja syarat mengajukan kelulusan jalur publikasi jurnal ilmiah?', 'Syarat kelulusan jalur publikasi di UNDIPA: 1) Mahasiswa tercatat sebagai first author (penulis pertama) dengan afiliasi Universitas Dipa Makassar. 2) Dosen pembimbing tercatat sebagai co-author. 3) Artikel telah berstatus Diterima (Letter of Acceptance / LoA) atau Terbit (Published) pada jurnal terakreditasi SINTA yang diakui prodi. 4) Menyerahkan naskah artikel, LoA resmi, dan bukti korespondensi ke Program Studi.', NULL, 'Publikasi'),
('Bagaimana alur konversi artikel jurnal ilmiah menjadi nilai skripsi?', 'Alur konversi publikasi: Mahasiswa mengajukan permohonan tertulis kepada Ketua Program Studi dengan melampirkan berkas naskah dan bukti publikasi/LoA. Tim penilai prodi akan memverifikasi keabsahan jurnal dan kesesuaian topik. Jika disetujui, nilai karya ilmiah dikonversi langsung menjadi nilai ujian skripsi tanpa sidang skripsi reguler.', NULL, 'Publikasi'),

-- [TOPIK 6: PENGAJUAN CUTI AKADEMIK (BAAK UNDIPA)]
('Bagaimana prosedur dan alur mengajukan cuti akademik?', 'Prosedur pengajuan cuti akademik di Universitas Dipa Makassar: 1) Mahasiswa mengambil formulir permohonan cuti di loket BAAK (Biro Administrasi Akademik dan Kemahasiswaan). 2) Meminta persetujuan Dosen Pembimbing Akademik (PA) dan Ketua Program Studi. 3) Mengurus surat bebas tanggungan keuangan di Bagian Keuangan dan bebas perpustakaan. 4) Menyerahkan berkas ke BAAK untuk diterbitkan Surat Keputusan Cuti resmi agar status di PDDikti/SIAKAD tidak tercatat mangkir.', NULL, 'Cuti'),
('Kapan batas waktu pengajuan cuti akademik dapat dilakukan?', 'Permohonan cuti akademik wajib diajukan sesuai jadwal registrasi cuti yang tertera pada Kalender Akademik resmi UNDIPA (umumnya selambat-lambatnya 2 minggu sebelum perkuliahan semester baru dimulai). Pengajuan di tengah semester yang sedang berjalan tidak diperkenankan.', NULL, 'Cuti'),
('Berapa lama maksimal mahasiswa diperbolehkan mengambil cuti kuliah?', 'Sesuai peraturan akademik UNDIPA, cuti akademik diberikan paling banyak 2 (dua) semester, baik secara berturut-turut maupun tidak berturut-turut selama masa studi. Masa cuti akademik resmi tidak dihitung ke dalam batas maksimal masa studi mahasiswa.', NULL, 'Cuti'),
('Bagaimana cara aktif kembali setelah menjalani masa cuti kuliah?', 'Untuk aktif kembali kuliah, mahasiswa wajib melapor ke BAAK dan Program Studi sebelum batas waktu pembayaran semester baru, membawa Surat Izin Cuti sebelumnya, melakukan pembayaran SPP aktif, dan memprogramkan mata kuliah di portal KRS SIAKA.', NULL, 'Cuti'),

-- [TOPIK 7: SURAT KETERANGAN LULUS (SKL)]
('Apa itu Surat Keterangan Lulus (SKL) dan apa kegunaannya?', 'Surat Keterangan Lulus (SKL) adalah surat keterangan resmi yang diterbitkan oleh BAAK Universitas Dipa Makassar bagi mahasiswa yang telah dinyatakan lulus yudisium skripsi. SKL memuat pernyataan kelulusan, IPK, dan gelar sarjana, yang sah digunakan untuk keperluan melamar pekerjaan atau melanjutkan studi sebelum ijazah asli diterbitkan saat wisuda.', NULL, 'SKL'),
('Bagaimana prosedur mengurus Surat Keterangan Lulus (SKL)?', 'Prosedur pengurusan SKL di UNDIPA: 1) Telah dinyatakan lulus ujian skripsi dan menyelesaikan seluruh revisi penguji. 2) Menyerahkan lembar pengesahan yang telah ditandatangani lengkap oleh dosen penguji dan dekan/kaprodi. 3) Melampirkan bukti Bebas Pustaka dan Bebas Keuangan. 4) Mengajukan permohonan ke loket BAAK kampus. SKL biasanya selesai dalam 2-3 hari kerja.', NULL, 'SKL'),
('Berapa lama masa berlaku Surat Keterangan Lulus (SKL)?', 'Masa berlaku SKL UNDIPA umumnya adalah 6 (enam) bulan sejak tanggal diterbitkan atau sampai ijazah dan transkrip nilai asli resmi diserahkan pada saat upacara wisuda.', NULL, 'SKL'),

-- [TOPIK 8: MULAI PERKULIAHAN & JADWAL]
('Kapan perkuliahan semester baru dimulai dan bagaimana melihat jadwalnya?', 'Jadwal perkuliahan mengacu pada Kalender Akademik resmi UNDIPA: Semester Ganjil umumnya dimulai awal September dan Semester Genap dimulai akhir Februari/awal Maret. Jadwal lengkap perkuliahan (hari, jam, ruang kelas, dan dosen pengampu) dapat dilihat langsung melalui portal SISKA di https://siska.undipa.ac.id/ atau pengumuman resmi kampus.', 'Kalender Akademik 2026_2027 Universitas Dipa Makassar.pdf', 'Jadwal'),
('Kapan batas akhir pembayaran SPP dan pendaftaran ulang perkuliahan?', 'Batas akhir pembayaran SPP/BPP dan pendaftaran ulang semester baru biasanya ditutup 1 pekan sebelum perkuliahan hari pertama dimulai. Mahasiswa dapat memantau tanggal pasti pada Kalender Akademik di portal siska.undipa.ac.id untuk menghindari denda atau sanksi cuti otomatis.', NULL, 'Jadwal'),
('Apa sanksi jika mahasiswa tidak melakukan KRS atau terlambat masuk kuliah?', 'Mahasiswa yang tidak melakukan registrasi SPP dan pengisian KRS hingga batas akhir jadwal kalender akademik akan berstatus Non-Aktif (mangkir) pada semester berjalan. Status non-aktif tanpa izin cuti tetap diperhitungkan dalam masa studi aktif.', NULL, 'Jadwal'),

-- [TOPIK 9: WISUDA]
('Apa saja syarat dan ketentuan pendaftaran wisuda sarjana di UNDIPA?', 'Syarat pendaftaran wisuda sarjana di Universitas Dipa Makassar: 1) Lulus ujian skripsi dan yudisium dengan nilai lengkap. 2) Menyerahkan naskah skripsi hard cover resmi yang telah disahkan. 3) Mengunggah file skripsi ke repositori perpustakaan kampus. 4) Memiliki bukti Bebas Pustaka, Bebas Keuangan, dan Bebas Laboratorium. 5) Melakukan verifikasi data diri (NIK, tempat/tanggal lahir) sesuai ijazah terakhir agar sinkron dengan PDDikti/NINA. 6) Mendaftar melalui portal wisuda resmi UNDIPA.', NULL, 'Wisuda'),
('Kapan jadwal penyelenggaraan wisuda dan bagaimana alurnya?', 'Universitas Dipa Makassar menyelenggarakan upacara wisuda secara berkala (umumnya 1-2 kali setahun). Jadwal pendaftaran, batas pengumpulan berkas, dan tanggal gladi bersih diumumkan melalui website resmi https://undipa.ac.id/ serta papan pengumuman kampus.', NULL, 'Wisuda'),
('Bagaimana prosedur pengambilan baju toga dan gladi bersih wisuda?', 'Baju toga dan atribut wisuda dapat diambil di bagian perlengkapan/kemahasiswaan kampus UNDIPA (Jl. Perintis Kemerdekaan Km. 9) dengan menunjukkan bukti pembayaran wisuda. Mahasiswa wajib mengikuti gladi bersih yang diadakan H-1 sebelum prosesi wisuda senat terbuka berlangsung.', NULL, 'Wisuda'),

-- [TOPIK 10: BUKU ALUMNI / BUKU WISUDA & TRACER STUDY]
('Apa itu Buku Alumni dan apa fungsinya bagi calon wisudawan?', 'Buku Alumni (Buku Wisuda) adalah buku dokumentasi profil dan rekam jejak kelulusan resmi yang diterbitkan oleh Universitas Dipa Makassar pada setiap periode wisuda. Buku ini memuat data wisudawan, pas foto resmi, judul skripsi, serta biodata lulusan untuk dokumentasi dan jaringan Ikatan Alumni (IKA UNDIPA).', NULL, 'Alumni'),
('Apa syarat pengumpulan pas foto dan data diri untuk Buku Alumni?', 'Syarat data Buku Alumni/Wisuda UNDIPA: 1) Pas foto formal terbaru mengenakan kemeja putih berdasi dan jas/blazer almamater (latar belakang merah/biru sesuai ketentuan). 2) Mengisi formulir pelacakan alumni / Tracer Study melalui portal resmi UNDIPA di https://site.undipa.ac.id/. 3) Menyerahkan softfile foto beresolusi tinggi dan cetak foto ukuran 3x4 dan 4x6 ke panitia wisuda.', NULL, 'Alumni'),
('Kapan batas waktu pengumpulan data Buku Alumni dan formulir tracer study?', 'Pengisian data Buku Wisuda dan formulir Tracer Study di site.undipa.ac.id wajib diselesaikan selambat-lambatnya bersamaan dengan batas penutupan pendaftaran wisuda. Keterlambatan penyerahan foto berisiko menyebabkan data wisudawan tidak termuat dalam cetakan Buku Wisuda.', NULL, 'Alumni');
