<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../core/Preprocessing.php';

$db = Database::getInstance()->getConnection();
$pesan = '';
$editMode = false;
$editData = null;

function simpanVektorTfidf($db, $docId, $questionText) {
    try {
        $preprocessing = new Preprocessing($db);

        $tokens = $preprocessing->process($questionText, $db);

        $stmt = $db->prepare("DELETE FROM tb_vektor_tfidf WHERE id_pengetahuan = ?");
        $stmt->execute([$docId]);

        $stmtInsert = $db->prepare("INSERT INTO tb_vektor_tfidf (id_pengetahuan, term, bobot_tfidf) VALUES (?, ?, ?)");

        $tfCounts = array_count_values($tokens);
        $totalTerms = count($tokens);

        foreach ($tfCounts as $term => $count) {
            $bobot = $totalTerms > 0 ? $count / $totalTerms : 0;
            $stmtInsert->execute([$docId, $term, $bobot]);
        }
    } catch (Exception $e) {
        error_log("simpanVektorTfidf Error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'simpan') {
        requireCsrfToken();

        $id = isset($_POST['id_pengetahuan']) ? (int)$_POST['id_pengetahuan'] : 0;
        $pertanyaan = trim($_POST['pertanyaan']);
        $jawaban = trim($_POST['jawaban']);
        $kategori = trim($_POST['kategori']);
        $file_lampiran = trim($_POST['file_lampiran']);

        if ($pertanyaan === '' || $jawaban === '') {
            $pesan = 'Pertanyaan dan jawaban tidak boleh kosong.';
        } else {
            try {
                if ($id > 0) {
                    $sql = "UPDATE tb_pengetahuan 
                            SET pertanyaan = ?, jawaban = ?, kategori = ?, file_lampiran = ? 
                            WHERE id_pengetahuan = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$pertanyaan, $jawaban, $kategori, $file_lampiran, $id]);
                    $docId = $id;
                    $pesan = 'Data Q&A berhasil diperbarui.';
                } else {
                    $sql = "INSERT INTO tb_pengetahuan (pertanyaan, jawaban, kategori, file_lampiran) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$pertanyaan, $jawaban, $kategori, $file_lampiran]);
                    $docId = (int)$db->lastInsertId();
                    $pesan = 'Data Q&A baru berhasil ditambahkan.';
                }

                simpanVektorTfidf($db, $docId, $pertanyaan);
            } catch (PDOException $e) {
                error_log("QA Simpan Error: " . $e->getMessage());
                $pesan = 'Terjadi kesalahan saat menyimpan data.';
            }
        }
    }

    if ($aksi === 'hapus') {
        requireCsrfToken();

        $id = isset($_POST['id_pengetahuan']) ? (int)$_POST['id_pengetahuan'] : 0;

        if ($id > 0) {
            try {
                $sql = "DELETE FROM tb_pengetahuan WHERE id_pengetahuan = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id]);
                $pesan = 'Data Q&A berhasil dihapus.';
            } catch (PDOException $e) {
                error_log("QA Hapus Error: " . $e->getMessage());
                $pesan = 'Data tidak dapat dihapus karena masih digunakan.';
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $stmt = $db->prepare("SELECT * FROM tb_pengetahuan WHERE id_pengetahuan = ?");
        $stmt->execute([$editId]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($editData) {
            $editMode = true;
        }
    }
}

$listFiles = [];
try {
    $stmt = $db->query("SELECT file_lampiran FROM tb_pengetahuan WHERE file_lampiran IS NOT NULL AND file_lampiran != '' GROUP BY file_lampiran");
    $listFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('QA list files error: ' . $e->getMessage());
}

$dataQA = [];
try {
    $stmt = $db->query("SELECT id_pengetahuan, pertanyaan, jawaban, kategori, file_lampiran, created_at 
                        FROM tb_pengetahuan ORDER BY id_pengetahuan DESC");
    $dataQA = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('QA list error: ' . $e->getMessage());
    $pesan = 'Gagal memuat data Q&A.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Q&A - DIPA-Bot Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .table-wrap { overflow-x: auto; }
        .btn-sm { padding: 8px 12px; font-size: 12px; width: auto; border-radius: 6px; text-decoration: none; display: inline-block; }
        .btn-blue { background: var(--deep-blue); color: #fff; border: none; cursor: pointer; }
        .btn-red { background: var(--red-alert); color: #fff; border: none; cursor: pointer; }
        .btn-gold { background: var(--gold-accent); color: #fff; border: none; cursor: pointer; }
        .btn-sm:hover { opacity: 0.9; }
        table.data { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.data th, table.data td { padding: 12px; text-align: left; border-bottom: 1px solid var(--gray-border); vertical-align: top; }
        table.data th { background: var(--off-white); font-weight: 600; white-space: nowrap; }
        .badge-kategori { background: rgba(30,58,138,0.1); color: var(--deep-blue); padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .msg-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
        .form-row { margin-bottom: 16px; }
        .form-row textarea, .form-row select { width: 100%; padding: 12px 16px; border: 1px solid var(--gray-border); border-radius: 8px; font-family: inherit; font-size: 14px; }
        .form-row textarea { min-height: 120px; resize: vertical; }
        .form-row textarea:focus, .form-row select:focus { outline: none; border-color: var(--deep-blue); box-shadow: 0 0 0 3px rgba(30,58,138,0.1); }
        .btn-inline { width: auto; padding: 12px 24px; }
        .layout { display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }
        .cell-actions { white-space: nowrap; }
        .inline-form { display: inline; }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">DIPA-Bot Admin</h2>
            <p class="sidebar-subtitle">Panel Pengelola</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a></li>
            <li><a href="qa_manage.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Kelola Q&A</a></li>
            <li><a href="file_manage.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>Kelola File</a></li>
            <li><a href="logs.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Log Percakapan</a></li>
        </ul>
        <a href="logout.php" class="logout-btn"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1 class="content-title">Kelola Q&A</h1>
            <p class="content-subtitle">Sebagai admin, Anda dapat menambah, mengedit, dan menghapus data knowledge base. Perubahan langsung berdampak pada jawaban DIPA-Bot.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="msg-success"><?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="layout">

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?php echo $editMode ? 'Edit Q&A' : 'Tambah Q&A'; ?></h2>
                </div>

                <form method="POST" action="qa_manage.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="aksi" value="simpan">
                    <input type="hidden" name="id_pengetahuan" value="<?php echo $editMode ? (int)$editData['id_pengetahuan'] : 0; ?>">

                    <div class="form-row">
                        <label class="form-label">Pertanyaan</label>
                        <textarea name="pertanyaan" required><?php echo $editMode ? htmlspecialchars($editData['pertanyaan'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Jawaban</label>
                        <textarea name="jawaban" required><?php echo $editMode ? htmlspecialchars($editData['jawaban'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Kategori</label>
                        <select name="kategori">
                            <?php
                            $kategoriPilihan = ['KRS', 'Jadwal', 'UAS', 'Kalender', 'Skripsi', 'Kemahasiswaan', 'Umum'];
                            $kategoriAktif = $editMode ? $editData['kategori'] : 'Umum';
                            foreach ($kategoriPilihan as $k) {
                                $selected = ($k === $kategoriAktif) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">File Lampiran (opsional)</label>
                        <select name="file_lampiran">
                            <option value="">-- Tidak ada lampiran --</option>
                            <?php foreach ($listFiles as $file): ?>
                                <option value="<?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($editMode && $editData['file_lampiran'] === $file) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--gray-light);">Upload file terlebih dahulu di menu <strong>Kelola File</strong> agar muncul di daftar.</small>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary btn-inline" style="background: var(--deep-blue);"><?php echo $editMode ? 'Simpan Perubahan' : 'Simpan'; ?></button>
                        <?php if ($editMode): ?>
                            <a href="qa_manage.php" class="btn btn-sm btn-gold">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Knowledge Base</h2>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pertanyaan</th>
                                <th>Kategori</th>
                                <th>File</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dataQA)): ?>
                                <tr><td colspan="5" style="text-align:center; color:var(--gray-light);">Belum ada data.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($dataQA as $row): ?>
                                <tr>
                                    <td><?php echo (int)$row['id_pengetahuan']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['pertanyaan'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <br>
                                        <small style="color: var(--gray-light);"><?php echo htmlspecialchars(substr($row['jawaban'], 0, 120), ENT_QUOTES, 'UTF-8'); ?>...</small>
                                    </td>
                                    <td><span class="badge-kategori"><?php echo htmlspecialchars($row['kategori'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo !empty($row['file_lampiran']) ? htmlspecialchars($row['file_lampiran'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--gray-light)">-</span>'; ?></td>
                                    <td class="cell-actions">
                                        <a href="qa_manage.php?edit=<?php echo (int)$row['id_pengetahuan']; ?>" class="btn btn-sm btn-blue">Edit</a>
                                        <form method="POST" action="qa_manage.php" class="inline-form" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_pengetahuan" value="<?php echo (int)$row['id_pengetahuan']; ?>">
                                            <button type="submit" class="btn btn-sm btn-red">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>