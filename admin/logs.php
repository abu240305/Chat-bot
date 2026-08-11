<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/validator.php';

$db = Database::getInstance()->getConnection();

$dataLogs = [];
$totalBerhasil = 0;
$totalFallback = 0;

$keyword = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$statusFilter = isset($_GET['status']) ? (string)$_GET['status'] : '';
$tanggal = isset($_GET['tanggal']) ? trim((string)$_GET['tanggal']) : '';

$filterErr = valLogFilter($keyword, $statusFilter, $tanggal);

$where = [];
$params = [];

if ($filterErr === '') {
    if ($keyword !== '') {
        $where[] = "pertanyaan_user LIKE ?";
        $params[] = '%' . $keyword . '%';
    }
    if ($statusFilter === 'berhasil') {
        $where[] = "skor_similarity >= 0.25";
    } elseif ($statusFilter === 'fallback') {
        $where[] = "skor_similarity < 0.25";
    }
    if ($tanggal !== '') {
        $where[] = "DATE(created_at) = ?";
        $params[] = $tanggal;
    }
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
$perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalLogs = 0;
$totalPages = 1;
$dataLogs = [];

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM tb_log_chat" . $whereSql);
    $stmt->execute($params);
    $totalLogs = (int)$stmt->fetchColumn();

    $totalPages = max(1, (int)ceil($totalLogs / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT id_log, pertanyaan_user, skor_similarity, ip_address, created_at 
            FROM tb_log_chat" . $whereSql . " 
            ORDER BY created_at DESC, id_log DESC 
            LIMIT ? OFFSET ?";

    $stmt = $db->prepare($sql);
    $i = 1;
    foreach ($params as $p) {
        $stmt->bindValue($i++, $p, PDO::PARAM_STR);
    }
    $stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($i++, $offset, PDO::PARAM_INT);
    $stmt->execute();
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
            <li><a href="logs.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>Log Percakapan</a></li>
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
                <span class="current">Log Percakapan</span>
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
            <h1 class="content-title">Log Percakapan</h1>
            <p class="content-subtitle">Riwayat interaksi mahasiswa dengan DIPA-Bot. Skor Cosine Similarity >= 0.25 dianggap berhasil, di bawah 0.25 masuk ke Fallback.</p>
        </div>

        <div class="card" style="padding: 18px 20px; margin-bottom: 18px;">
            <form method="GET" action="logs.php" class="filter-form">
                <input type="search" name="q" placeholder="Cari pertanyaan mahasiswa..." value="<?php echo e($keyword); ?>">
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="berhasil" <?php echo $statusFilter === 'berhasil' ? 'selected' : ''; ?>>Berhasil (&gt;= 0.25)</option>
                    <option value="fallback" <?php echo $statusFilter === 'fallback' ? 'selected' : ''; ?>>Fallback (&lt; 0.25)</option>
                </select>
                <input type="date" name="tanggal" value="<?php echo e($tanggal); ?>">
                <button type="submit" class="btn btn-primary btn-inline">Filter</button>
                <a href="logs.php" class="btn btn-sm btn-red">Reset</a>
            </form>
            <?php if ($filterErr !== ''): ?>
                <p class="error-text" style="margin-top:10px;"><?php echo e($filterErr); ?></p>
            <?php endif; ?>
        </div>

        <div class="stats-mini">
            <span class="stats-mini-item"><span class="dot dot-gray"></span> Total: <?php echo number_format($totalLogs); ?></span>
            <span class="stats-mini-item"><span class="dot dot-green"></span> Berhasil: <?php echo number_format($totalBerhasil); ?></span>
            <span class="stats-mini-item"><span class="dot dot-red"></span> Fallback: <?php echo number_format($totalFallback); ?></span>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Riwayat Percakapan Chatbot</h2>
                <label class="page-size-wrap">Per halaman
                    <select class="page-size" onchange="location.href='logs.php?<?php echo htmlspecialchars(http_build_query(array_filter(['q' => $keyword, 'status' => $statusFilter, 'tanggal' => $tanggal])), ENT_QUOTES, 'UTF-8'); ?>&per='+this.value">
                        <?php foreach ([10, 25, 50] as $n): ?>
                            <option value="<?php echo $n; ?>" <?php echo $perPage === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pertanyaan Mahasiswa</th>
                            <th>Skor Cosine Similarity</th>
                            <th>IP Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dataLogs)): ?>
                            <tr><td colspan="5" style="text-align:center; color:var(--gray-light); padding:32px;">Belum ada percakapan tercatat.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($dataLogs as $row): ?>
                            <?php
                                $skor = (float)$row['skor_similarity'];
                                $berhasil = $skor >= 0.25;
                                $statusClass = $berhasil ? 'badge-success' : 'badge-fallback';
                                $statusText = $berhasil ? 'Berhasil Matched' : 'Fallback Response';
                                $waktu = date('d M Y, H:i:s', strtotime($row['created_at']));
                                $ip = isset($row['ip_address']) ? $row['ip_address'] : '-';
                            ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo htmlspecialchars($waktu, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="cell-main">
                                        <div class="cell-truncate"><?php echo htmlspecialchars($row['pertanyaan_user'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <button type="button" class="toggle-more">Lihat Selengkapnya</button>
                                    </div>
                                </td>
                                <td class="skor-cell <?php echo $berhasil ? 'skor-ok' : 'skor-low'; ?>" data-tip="Skor Cosine: <?php echo number_format($skor, 6); ?>"><?php echo number_format($skor, 4); ?></td>
                                <td style="white-space:nowrap;" data-tip="IP Address"><?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="badge-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <?php
                $filterQuery = array_filter(['q' => $keyword, 'status' => $statusFilter, 'tanggal' => $tanggal, 'per' => $perPage]);
                $pageUrl = function ($p) use ($filterQuery) {
                    return 'logs.php?' . http_build_query(array_merge($filterQuery, ['page' => $p]));
                };
                ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a class="page-link" href="<?php echo $pageUrl($page - 1); ?>">&laquo; Sebelumnya</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    if ($start > 1) {
                        echo '<a class="page-link" href="' . $pageUrl(1) . '">1</a>';
                        if ($start > 2) {
                            echo '<span class="page-ellipsis">…</span>';
                        }
                    }
                    for ($i = $start; $i <= $end; $i++) {
                        $cls = ($i === $page) ? 'page-link active' : 'page-link';
                        echo '<a class="' . $cls . '" href="' . $pageUrl($i) . '">' . $i . '</a>';
                    }
                    if ($end < $totalPages) {
                        if ($end < $totalPages - 1) {
                            echo '<span class="page-ellipsis">…</span>';
                        }
                        echo '<a class="page-link" href="' . $pageUrl($totalPages) . '">' . $totalPages . '</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a class="page-link" href="<?php echo $pageUrl($page + 1); ?>">Berikutnya &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

</div>
<script src="../assets/js/admin.js"></script>

</body>
</html>