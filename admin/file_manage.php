<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/validator.php';

$db = Database::getInstance()->getConnection();

$adminNama = isset($_SESSION['admin_nama']) ? htmlspecialchars($_SESSION['admin_nama'], ENT_QUOTES, 'UTF-8') : 'Admin';

$uploadDir = __DIR__ . '/../assets/downloads/';
$maxFileSize = 10 * 1024 * 1024;

$allowedExt = ['pdf', 'docx'];

$allowedMime = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$pesan = '';
$pesanTipe = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'upload') {
        requireCsrfToken();

        $valErr = valFileUpload(isset($_FILES['berkas']) ? $_FILES['berkas'] : null, $maxFileSize, $allowedExt, $allowedMime);

        if ($valErr !== '') {
            $pesan = $valErr;
            $pesanTipe = 'error';
        } else {
            $file = $_FILES['berkas'];

            $originalName = basename($file['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $newName = md5(uniqid(rand(), true)) . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                tapisFile($targetPath);
                $pesan = 'File berhasil diunggah. <strong>Nama file: ' . htmlspecialchars($newName, ENT_QUOTES, 'UTF-8') . '</strong>. Gunakan nama ini pada menu Kelola Q&A.';
            } else {
                $pesan = 'Gagal memindahkan file ke server.';
                $pesanTipe = 'error';
            }
        }
    }

    if ($aksi === 'hapus') {
        requireCsrfToken();

        $namaFile = isset($_POST['nama_file']) ? basename($_POST['nama_file']) : '';

        if ($namaFile !== '') {
            $filePath = $uploadDir . $namaFile;

            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $stmt = $db->prepare("UPDATE tb_pengetahuan SET file_lampiran = NULL WHERE file_lampiran = ?");
                    $stmt->execute([$namaFile]);
                    $pesan = 'File berhasil dihapus.';
                } else {
                    $pesan = 'Gagal menghapus file.';
                    $pesanTipe = 'error';
                }
            } else {
                $pesan = 'File tidak ditemukan di server.';
                $pesanTipe = 'error';
            }
        }
    }
}

$filesInDir = [];
if (is_dir($uploadDir)) {
    $filesInDir = array_filter(scandir($uploadDir), function ($item) use ($allowedExt) {
        if ($item === '.' || $item === '..' || $item === '.htaccess') {
            return false;
        }
        $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExt);
    });
    natcasesort($filesInDir);
}

function tapisFile($filePath) {
    $lines = file($filePath);
    $tapis = ['<?php', '<?=', '<?', 'eval(', 'system(', 'shell_exec(', 'passthru(', 'exec('];
    foreach ($lines as $line) {
        foreach ($tapis as $kata) {
            if (stripos($line, $kata) !== false) {
                error_log('File mencurigakan terdeteksi: ' . $filePath);
                return;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola File - DIPA-Bot Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5">
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <div class="brand-text">
                <h2 class="sidebar-title">DIPA-Bot</h2>
                <p class="sidebar-subtitle">Admin UNDIPA</p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a></li>
            <li><a href="qa_manage.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Kelola Q&A</a></li>
            <li><a href="file_manage.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>Kelola File</a></li>
            <li><a href="logs.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Log Percakapan</a></li>
            <li><a href="change_password.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>Ubah Password</a></li>
        
        </ul>
        <div class="sidebar-user">
            <div class="user-avatar"><?php echo strtoupper(substr($adminNama, 0, 1)); ?></div>
            <div class="user-meta">
                <span class="user-name"><?php echo $adminNama; ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a>
    </aside>

    <div class="sidebar-backdrop"></div>

    <main class="main-content">
        <div class="topbar">
            <button type="button" class="hamburger" aria-label="Buka menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <nav class="breadcrumb">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <span class="current">Kelola File</span>
            </nav>
            <div class="user-chip">
                <div class="chip-avatar"><?php echo strtoupper(substr($adminNama, 0, 1)); ?></div>
                <div class="chip-meta">
                    <span class="chip-name"><?php echo $adminNama; ?></span>
                    <span class="chip-role">Administrator</span>
                </div>
            </div>
        </div>

        <div class="content-header">
            <h1 class="content-title">Kelola File</h1>
            <p class="content-subtitle">Unggah dokumen akademik (.pdf / .docx) untuk dilampirkan pada jawaban DIPA-Bot.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="<?php echo $pesanTipe === 'error' ? 'msg-error' : 'msg-success'; ?>">
                <?php echo $pesan; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Daftar File Tersimpan</h2>
                <button type="button" class="btn btn-gold btn-inline" onclick="openModal('uploadModal')">+ Upload Dokumen</button>
            </div>
            <p class="content-subtitle" style="margin-bottom:14px;">Folder: <code>assets/downloads/</code></p>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filesInDir)): ?>
                            <tr><td colspan="3" style="text-align:center; color:var(--gray-light);">Belum ada file.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($filesInDir as $fileName): ?>
                            <?php
                            $filePath = $uploadDir . $fileName;
                            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                            $sizeLabel = number_format($fileSize / 1024, 1) . ' KB';
                            if ($fileSize >= 1024 * 1024) {
                                $sizeLabel = number_format($fileSize / (1024 * 1024), 2) . ' MB';
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="file-size"><?php echo htmlspecialchars($sizeLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="../assets/downloads/<?php echo htmlspecialchars(rawurlencode($fileName), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-sm btn-blue">Lihat</a>
                                    <form method="POST" action="file_manage.php" class="inline-form" onsubmit="return confirm('Yakin ingin menghapus file ini?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="nama_file" value="<?php echo htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-red">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL UPLOAD FILE -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h2 class="card-title">Upload Dokumen Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('uploadModal')" aria-label="Tutup">&times;</button>
        </div>

        <form method="POST" action="file_manage.php" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <input type="hidden" name="aksi" value="upload">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxFileSize; ?>">

            <div class="upload-box">
                <p style="margin-bottom: 12px; color: var(--dark-slate);">Pilih file dari komputer Anda</p>
                <input type="file" name="berkas" id="berkas" accept=".pdf,.docx" required>
                <p class="upload-note">
                    Format yang diizinkan: <strong>.pdf, .docx</strong><br>
                    Ukuran maksimal: <strong>10 MB</strong><br>
                    File akan di-rename otomatis dengan nama acak (hash) demi keamanan.
                </p>
            </div>

            <div class="file-preview">
                <span class="fp-icon">&#128196;</span>
                <span class="fp-name"></span>
                <span class="fp-size"></span>
                <button type="button" class="fp-remove" title="Hapus/Ganti file">&times;</button>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 16px;">
                <button type="submit" class="btn btn-gold btn-inline">Upload Sekarang</button>
                <button type="button" class="btn btn-sm btn-red" onclick="closeModal('uploadModal')">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
}

function closeModal(id) {
    var m = document.getElementById(id);
    if (m && m.classList.contains('dirty')) {
        if (!confirm('Ada perubahan yang belum disimpan. Tetap tutup?')) {
            return;
        }
    }
    if (m) {
        m.classList.remove('open');
        m.classList.remove('dirty');
    }
}

document.getElementById('uploadModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal('uploadModal');
    }
});
</script>
<script src="../assets/js/admin.js"></script>

</body>
</html>