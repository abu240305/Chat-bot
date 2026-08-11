<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../core/VectorBuilder.php';

$db = Database::getInstance()->getConnection();
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'rebuild') {
    requireCsrfToken();

    try {
        $start = microtime(true);

        $hasil = rebuildAllVectors($db);

        $elapsed = round(microtime(true) - $start, 3);
        $pesan = 'Rebuild selesai: <strong>' . $hasil['total_dokumen'] . '</strong> dokumen, <strong>' . $hasil['total_term'] . '</strong> term vektor tersimpan, dalam ' . $elapsed . ' detik.';
    } catch (Exception $e) {
        error_log("Rebuild Vektor Error: " . $e->getMessage());
        $pesan = 'Terjadi kesalahan saat rebuild: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rebuild Vektor - DIPA-Bot Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=5">
</head>
<body>
<div class="rebuild-wrap">
    <div class="rebuild-card">
        <div class="rebuild-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="1 4 1 10 7 10"></polyline>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
        </div>

        <h1 class="content-title" style="font-size:22px;">Backfill Vektor TF</h1>
        <p class="content-subtitle">Memproses ulang seluruh pertanyaan di <code>tb_pengetahuan</code> lalu menyimpannya ke <code>tb_vektor_tfidf</code>. Jalankan sekali setelah import database, atau setelah menambah banyak data Q&A sekaligus. Proses cepat dan aman (data vektor lama dihapus lalu ditulis ulang).</p>

        <form method="POST" action="rebuild_vektor.php" onsubmit="return confirm('Yakin ingin membangun ulang seluruh vektor TF?');">
            <?php echo csrfField(); ?>
            <input type="hidden" name="aksi" value="rebuild">
            <button type="submit" class="btn btn-gold btn-inline">Jalankan Rebuild Vektor</button>
            <a class="back-link" href="dashboard.php">Kembali ke Dashboard</a>
        </form>

        <?php if (!empty($pesan)): ?>
            <div class="msg"><?php echo $pesan; ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>