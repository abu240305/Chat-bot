<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

$totalQA = 0;
$totalFiles = 0;
$totalLogs = 0;

try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_pengetahuan");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalQA = $result['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_pengetahuan WHERE file_lampiran IS NOT NULL AND file_lampiran != ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalFiles = $result['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_log_chat");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalLogs = $result['total'];
    
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}

$adminNama = htmlspecialchars($_SESSION['admin_nama'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - DIPA-Bot</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">DIPA-Bot Admin</h2>
                <p class="sidebar-subtitle">Panel Pengelola</p>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="qa_manage.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Kelola Q&A
                    </a>
                </li>
                <li>
                    <a href="file_manage.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        Kelola File
                    </a>
                </li>
                <li>
                    <a href="logs.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Log Percakapan
                    </a>
                </li>
            </ul>

            <a href="logout.php" class="logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1 class="content-title">Dashboard</h1>
                <p class="content-subtitle">Selamat datang, <?php echo $adminNama; ?></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalQA); ?></div>
                    <div class="stat-label">Total Data Q&A</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon gold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                <polyline points="13 2 13 9 20 9"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalFiles); ?></div>
                    <div class="stat-label">File Tersedia</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon red">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalLogs); ?></div>
                    <div class="stat-label">Total Percakapan</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informasi Sistem</h2>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid var(--gray-border);">
                        <td style="padding: 12px 0; font-weight: 600;">Admin Login</td>
                        <td style="padding: 12px 0;"><?php echo $adminNama; ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-border);">
                        <td style="padding: 12px 0; font-weight: 600;">Username</td>
                        <td style="padding: 12px 0;"><?php echo htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--gray-border);">
                        <td style="padding: 12px 0; font-weight: 600;">Waktu Login</td>
                        <td style="padding: 12px 0;"><?php echo date('d M Y, H:i:s', $_SESSION['login_time']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; font-weight: 600;">Status Sistem</td>
                        <td style="padding: 12px 0; color: #10B981; font-weight: 600;">● Online</td>
                    </tr>
                </table>
            </div>
        </main>

    </div>
</body>
</html>
