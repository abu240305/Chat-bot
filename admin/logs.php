<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

$dataLogs = [];
$totalBerhasil = 0;
$totalFallback = 0;

try {
    $stmt = $db->query("SELECT id_log, pertanyaan_user, skor_similarity, created_at 
                        FROM tb_log_chat 
                        ORDER BY created_at DESC, id_log DESC");
    $dataLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT 
                            SUM(CASE WHEN skor_similarity >= 0.25 THEN 1 ELSE 0 END) AS berhasil,
                            SUM(CASE WHEN skor_similarity < 0.25 THEN 1 ELSE 0 END) AS fallback
                        FROM tb_log_chat");
    $statistik = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($statistik) {
        $totalBerhasil = (int)$statistik['berhasil'];
        $totalFallback = (int)$statistik['fallback'];
    }
} catch (PDOException $e) {
    error_log("Logs Error: " . $e->getMessage());
}

$adminNama = htmlspecialchars($_SESSION['admin_nama'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Percakapan - DIPA-Bot Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .table-wrap { overflow-x: auto; }
        table.data { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.data th, table.data td { padding: 12px; text-align: left; border-bottom: 1px solid var(--gray-border); vertical-align: top; }
        table.data th { background: var(--off-white); font-weight: 600; white-space: nowrap; color: var(--deep-blue); }
        table.data tr:hover td { background: #F1F5F9; }
        .badge-status { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .badge-fallback { background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; }
        .skor-cell { font-variant-numeric: tabular-nums; font-weight: 600; }
        .skor-ok { color: var(--deep-blue); }
        .skor-low { color: var(--red-alert); }
        .stats-mini { display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap; }
        .stats-mini-item { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .dot-green { background: #10B981; }
        .dot-red { background: var(--red-alert); }
        .dot-gray { background: var(--gray-light); }
        @media (max-width: 768px) {
            table.data th, table.data td { white-space: normal; }
        }
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
            <li><a href="file_manage.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>Kelola File</a></li>
            <li><a href="logs.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Log Percakapan</a></li>
        </ul>

        <a href="logout.php" class="logout-btn"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1 class="content-title">Log Percakapan</h1>
            <p class="content-subtitle">Riwayat interaksi mahasiswa dengan DIPA-Bot. Skor Cosine Similarity >= 0.25 dianggap berhasil, di bawah 0.25 masuk ke Fallback.</p>
        </div>

        <div class="stats-mini">
            <span class="stats-mini-item"><span class="dot dot-gray"></span> Total: <?php echo number_format(count($dataLogs)); ?></span>
            <span class="stats-mini-item"><span class="dot dot-green"></span> Berhasil: <?php echo number_format($totalBerhasil); ?></span>
            <span class="stats-mini-item"><span class="dot dot-red"></span> Fallback: <?php echo number_format($totalFallback); ?></span>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Riwayat Percakapan Chatbot</h2>
            </div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pertanyaan Mahasiswa</th>
                            <th>Skor Cosine Similarity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dataLogs)): ?>
                            <tr><td colspan="4" style="text-align:center; color:var(--gray-light); padding:32px;">Belum ada percakapan tercatat.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($dataLogs as $row): ?>
                            <?php
                                $skor = (float)$row['skor_similarity'];
                                $berhasil = $skor >= 0.25;
                                $statusClass = $berhasil ? 'badge-success' : 'badge-fallback';
                                $statusText = $berhasil ? 'Berhasil' : 'Fallback';
                                $waktu = date('d M Y, H:i:s', strtotime($row['created_at']));
                            ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo htmlspecialchars($waktu, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['pertanyaan_user'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="skor-cell <?php echo $berhasil ? 'skor-ok' : 'skor-low'; ?>"><?php echo number_format($skor, 4); ?></td>
                                <td><span class="badge-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
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