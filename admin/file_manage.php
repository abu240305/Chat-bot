<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';

$db = Database::getInstance()->getConnection();

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

        if (!isset($_FILES['berkas']) || $_FILES['berkas']['error'] === UPLOAD_ERR_NO_FILE) {
            $pesan = 'Silakan pilih file terlebih dahulu.';
            $pesanTipe = 'error';
        } else {
            $file = $_FILES['berkas'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $pesan = 'Terjadi kesalahan saat upload file.';
                $pesanTipe = 'error';
            } elseif ($file['size'] > $maxFileSize) {
                $pesan = 'Ukuran file melebihi batas maksimal 10MB.';
                $pesanTipe = 'error';
            } elseif ($file['size'] <= 0) {
                $pesan = 'File kosong.';
                $pesanTipe = 'error';
            } else {
                $originalName = basename($file['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) {
                    $pesan = 'Format file tidak diizinkan. Hanya .pdf dan .docx.';
                    $pesanTipe = 'error';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);

                    if (!in_array($mimeType, $allowedMime)) {
                        $pesan = 'Isi file tidak valid (MIME: ' . htmlspecialchars($mimeType, ENT_QUOTES, 'UTF-8') . '). Hanya PDF dan DOCX yang diizinkan.';
                        $pesanTipe = 'error';
                    } else {
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .table-wrap { overflow-x: auto; }
        .btn-sm { padding: 8px 12px; font-size: 12px; width: auto; border-radius: 6px; text-decoration: none; display: inline-block; }
        .btn-blue { background: var(--deep-blue); color: #fff; }
        .btn-red { background: var(--red-alert); color: #fff; border: none; cursor: pointer; }
        .btn-gold { background: var(--gold-accent); color: #fff; border: none; cursor: pointer; font-size: 14px; }
        .btn-sm:hover { opacity: 0.9; }
        table.data { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.data th, table.data td { padding: 12px; text-align: left; border-bottom: 1px solid var(--gray-border); vertical-align: middle; }
        table.data th { background: var(--off-white); font-weight: 600; white-space: nowrap; }
        .file-size { color: var(--gray-light); font-size: 12px; }
        .msg-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
        .msg-error { background: #FEE2E2; color: var(--red-alert); border: 1px solid #FECACA; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
        .upload-box { border: 2px dashed var(--gray-border); border-radius: 12px; padding: 32px 24px; text-align: center; }
        .upload-note { font-size: 12px; color: var(--gray-light); margin-top: 8px; line-height: 1.6; }
        .error-text { color: var(--red-alert); }
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
            <li><a href="qa_manage.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Kelola Q&A</a></li>
            <li><a href="file_manage.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>Kelola File</a></li>
            <li><a href="logs.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Log Percakapan</a></li>
        </ul>
        <a href="logout.php" class="logout-btn"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1 class="content-title">Kelola File</h1>
            <p class="content-subtitle">Unggah dokumen akademik (.pdf / .docx) untuk dilampirkan pada jawaban DIPA-Bot.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="<?php echo $pesanTipe === 'error' ? 'msg-error' : 'msg-success'; ?>">
                <?php echo $pesan; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width:640px;">
            <div class="card-header">
                <h2 class="card-title">Upload Dokumen Baru</h2>
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

                <button type="submit" class="btn btn-primary btn-gold btn-sm" style="margin-top:16px; padding: 12px 32px; font-size: 14px;">Upload Sekarang</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Daftar File Tersimpan</h2>
                <p class="content-subtitle">Folder: <code>assets/downloads/</code></p>
            </div>

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
</body>
</html>