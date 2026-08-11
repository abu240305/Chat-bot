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

$pesan = '';
$pesanTipe = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'ganti') {
    requireCsrfToken();

    $passLama = isset($_POST['password_lama']) ? $_POST['password_lama'] : '';
    $passBaru = isset($_POST['password_baru']) ? $_POST['password_baru'] : '';
    $passKonfirmasi = isset($_POST['password_konfirmasi']) ? $_POST['password_konfirmasi'] : '';

    $valErr = valPasswordChange($passLama, $passBaru, $passKonfirmasi);

    if ($valErr !== '') {
        $pesan = $valErr;
        $pesanTipe = 'error';
    } else {
        try {
            $adminId = (int)$_SESSION['admin_id'];

            $stmt = $db->prepare("SELECT password FROM tb_admin WHERE id_admin = ? LIMIT 1");
            $stmt->execute([$adminId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($passLama, $row['password'])) {
                $pesan = 'Password lama salah.';
                $pesanTipe = 'error';
            } elseif ($passLama === $passBaru) {
                $pesan = 'Password baru tidak boleh sama dengan password lama.';
                $pesanTipe = 'error';
            } else {
                $newHash = password_hash($passBaru, PASSWORD_BCRYPT);

                $upd = $db->prepare("UPDATE tb_admin SET password = ? WHERE id_admin = ?");
                $upd->execute([$newHash, $adminId]);

                $pesan = 'Password berhasil diubah. Gunakan password baru saat login berikutnya.';
            }
        } catch (PDOException $e) {
            error_log("Ganti Password Error: " . $e->getMessage());
            $pesan = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            $pesanTipe = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - DIPA-Bot Admin</title>
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
            <li><a href="file_manage.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>Kelola File</a></li>
            <li><a href="logs.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Log Percakapan</a></li>
            <li><a href="change_password.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>Ubah Password</a></li>
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
                <span class="current">Ubah Password</span>
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
            <h1 class="content-title">Ubah Password</h1>
            <p class="content-subtitle">Perbarui kata sandi akun admin untuk menjaga keamanan panel. Password baru minimal 8 karakter.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="<?php echo $pesanTipe === 'error' ? 'msg-error' : 'msg-success'; ?>">
                <?php echo e($pesan); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 480px;">
            <div class="card-header">
                <h2 class="card-title">Form Ganti Password</h2>
            </div>

            <form method="POST" action="change_password.php">
                <?php echo csrfField(); ?>
                <input type="hidden" name="aksi" value="ganti">

                <div class="form-row">
                    <label class="form-label">Password Lama</label>
                    <input type="password" name="password_lama" class="form-input" placeholder="Masukkan password lama" required autocomplete="current-password">
                </div>

                <div class="form-row">
                    <label class="form-label">Password Baru (min. 8 karakter)</label>
                    <input type="password" name="password_baru" class="form-input" minlength="8" placeholder="Masukkan password baru" required autocomplete="new-password">
                </div>

                <div class="form-row">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_konfirmasi" class="form-input" minlength="8" placeholder="Ulangi password baru" required autocomplete="new-password">
                </div>

                <div style="display: flex; gap: 12px; margin-top: 4px;">
                    <button type="submit" class="btn btn-primary btn-inline">Simpan Password</button>
                    <a href="dashboard.php" class="btn btn-sm btn-red">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>