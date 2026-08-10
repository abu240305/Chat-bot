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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'rebuild') {
    requireCsrfToken();

    try {
        $start = microtime(true);

        $semua = $db->query("SELECT id_pengetahuan, pertanyaan FROM tb_pengetahuan")->fetchAll(PDO::FETCH_ASSOC);

        $totalTerm = 0;

        foreach ($semua as $row) {
            $tokens = (new Preprocessing($db))->process($row['pertanyaan'], $db);

            $del = $db->prepare("DELETE FROM tb_vektor_tfidf WHERE id_pengetahuan = ?");
            $del->execute([$row['id_pengetahuan']]);

            $ins = $db->prepare("INSERT INTO tb_vektor_tfidf (id_pengetahuan, term, bobot_tfidf) VALUES (?, ?, ?)");

            $tfCounts = array_count_values($tokens);
            $total = count($tokens);

            foreach ($tfCounts as $term => $count) {
                $ins->execute([$row['id_pengetahuan'], $term, $total > 0 ? $count / $total : 0]);
                $totalTerm++;
            }
        }

        $elapsed = round(microtime(true) - $start, 3);
        $pesan = 'Rebuild selesai: <strong>' . count($semua) . '</strong> dokumen, <strong>' . $totalTerm . '</strong> term vektor tersimpan, dalam ' . $elapsed . ' detik.';
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .rebuild-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .rebuild-card { background: var(--white); border-radius: 12px; box-shadow: var(--shadow-lg); max-width: 560px; width: 100%; padding: 32px; border: 1px solid var(--gray-border); }
        .rebuild-icon { width: 56px; height: 56px; border-radius: 12px; background: rgba(30,58,138,0.1); color: var(--deep-blue); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .btn-inline { width: auto; padding: 12px 24px; }
        .msg { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; padding: 12px 16px; border-radius: 8px; margin-top: 16px; font-size: 13px; }
        .back-link { display: inline-block; margin-top: 16px; font-size: 13px; color: var(--deep-blue); text-decoration: none; }
    </style>
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

        <h1 class="content-title" style="font-size:22px; margin-bottom:8px;">Backfill Vektor TF</h1>
        <p class="content-subtitle" style="margin-bottom:20px;">
            Memproses ulang seluruh pertanyaan di <code>tb_pengetahuan</code> lalu menyimpannya ke
            <code>tb_vektor_tfidf</code>. Jalankan sekali setelah import database, atau setelah menambah banyak data
            Q&A sekaligus. Proses cepat dan aman (data vektor lama dihapus lalu ditulis ulang).
        </p>

        <form method="POST" action="rebuild_vektor.php" onsubmit="return confirm('Yakin ingin membangun ulang seluruh vektor TF?');">
            <?php echo csrfField(); ?>
            <input type="hidden" name="aksi" value="rebuild">
            <button type="submit" class="btn btn-primary btn-inline" style="background: var(--gold-accent);">Jalankan Rebuild Vektor</button>
            <a class="back-link" href="dashboard.php">Kembali ke Dashboard</a>
        </form>

        <?php if (!empty($pesan)): ?>
            <div class="msg"><?php echo $pesan; ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>