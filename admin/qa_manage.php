<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/validator.php';
require_once __DIR__ . '/../core/VectorBuilder.php';

$db = Database::getInstance()->getConnection();
$pesan = '';
$editMode = false;
$editData = null;
$adminNama = isset($_SESSION['admin_nama']) ? htmlspecialchars($_SESSION['admin_nama'], ENT_QUOTES, 'UTF-8') : 'Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'simpan') {
        requireCsrfToken();

        $id = isset($_POST['id_pengetahuan']) ? (int)$_POST['id_pengetahuan'] : 0;
        $pertanyaan = trim($_POST['pertanyaan']);
        $jawaban = trim($_POST['jawaban']);
        $kategori = trim($_POST['kategori']);
        $file_lampiran = trim($_POST['file_lampiran']);

        $valErr = valQA($pertanyaan, $jawaban);

        if ($valErr !== '') {
            $pesan = $valErr;
        } else {
            try {
                if ($id > 0) {
                    $sql = "UPDATE tb_pengetahuan 
                            SET pertanyaan = ?, jawaban = ?, kategori = ?, file_lampiran = ? 
                            WHERE id_pengetahuan = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$pertanyaan, $jawaban, $kategori, $file_lampiran, $id]);
                    $pesan = 'Data Q&A berhasil diperbarui.';
                } else {
                    $sql = "INSERT INTO tb_pengetahuan (pertanyaan, jawaban, kategori, file_lampiran) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$pertanyaan, $jawaban, $kategori, $file_lampiran]);
                    $pesan = 'Data Q&A baru berhasil ditambahkan.';
                }

                rebuildAllVectors($db);
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

$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
$perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalQA = 0;
$totalPages = 1;
$dataQA = [];
try {
    $totalQA = (int)$db->query("SELECT COUNT(*) FROM tb_pengetahuan")->fetchColumn();
    $totalPages = max(1, (int)ceil($totalQA / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $stmt = $db->prepare("SELECT id_pengetahuan, pertanyaan, jawaban, kategori, file_lampiran, created_at 
                          FROM tb_pengetahuan 
                          ORDER BY id_pengetahuan DESC 
                          LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
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
    <link rel="stylesheet" href="../assets/css/admin.css?v=9">
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
            <li><a href="qa_manage.php" class="active"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>Kelola Q&A</a></li>
            <li><a href="file_manage.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>Kelola File</a></li>
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
                <span class="current">Kelola Q&A</span>
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
            <h1 class="content-title">Kelola Q&A</h1>
            <p class="content-subtitle">Sebagai admin, Anda dapat menambah, mengedit, dan menghapus data knowledge base. Perubahan langsung berdampak pada jawaban DIPA-Bot.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="msg-success"><?php echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Knowledge Base</h2>
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <label class="page-size-wrap">Per halaman
                            <select class="page-size" onchange="location.href='qa_manage.php?per='+this.value">
                                <?php foreach ([10, 25, 50] as $n): ?>
                                    <option value="<?php echo $n; ?>" <?php echo $perPage === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button type="button" class="btn btn-primary btn-inline" onclick="openAddModal()">+ Tambah Q&A</button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pertanyaan</th>
                                <th>Kategori</th>
                                <th>File</th>
                                <th class="sticky-col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dataQA)): ?>
                                <tr><td colspan="5" style="text-align:center; color:var(--gray-light);">Belum ada data.</td></tr>
                            <?php endif; ?>
                            <?php $no = 1; foreach ($dataQA as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="cell-main">
                                            <div class="cell-truncate">
                                                <strong><?php echo htmlspecialchars($row['pertanyaan'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <br>
                                                <small style="color: var(--gray-light);"><?php echo htmlspecialchars(substr($row['jawaban'], 0, 160), ENT_QUOTES, 'UTF-8'); ?>...</small>
                                            </div>
                                            <button type="button" class="toggle-more">Lihat Selengkapnya</button>
                                        </div>
                                    </td>
                                    <td><span class="badge-kategori"><?php echo htmlspecialchars($row['kategori'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo !empty($row['file_lampiran']) ? htmlspecialchars($row['file_lampiran'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--gray-light)">-</span>'; ?></td>
                                    <td class="cell-actions sticky-col">
                                        <a href="qa_manage.php?edit=<?php echo (int)$row['id_pengetahuan']; ?>" class="btn btn-sm btn-blue" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                                        <form method="POST" action="qa_manage.php" class="inline-form" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_pengetahuan" value="<?php echo (int)$row['id_pengetahuan']; ?>">
                                            <button type="submit" class="btn btn-sm btn-red" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a class="page-link" href="qa_manage.php?per=<?php echo $perPage; ?>&page=<?php echo $page - 1; ?>">&laquo; Sebelumnya</a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1) {
                            echo '<a class="page-link" href="qa_manage.php?per=' . $perPage . '&page=1">1</a>';
                            if ($start > 2) {
                                echo '<span class="page-ellipsis">…</span>';
                            }
                        }
                        for ($i = $start; $i <= $end; $i++) {
                            $cls = ($i === $page) ? 'page-link active' : 'page-link';
                            echo '<a class="' . $cls . '" href="qa_manage.php?per=' . $perPage . '&page=' . $i . '">' . $i . '</a>';
                        }
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) {
                                echo '<span class="page-ellipsis">…</span>';
                            }
                            echo '<a class="page-link" href="qa_manage.php?per=' . $perPage . '&page=' . $totalPages . '">' . $totalPages . '</a>';
                        }
                        ?>

                        <?php if ($page < $totalPages): ?>
                            <a class="page-link" href="qa_manage.php?per=<?php echo $perPage; ?>&page=<?php echo $page + 1; ?>">Berikutnya &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
    </main>
</div>

<!-- MODAL FORM Q&A -->
<div class="modal-overlay" id="qaModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="card-title"><?php echo $editMode ? 'Edit Q&A' : 'Tambah Q&A'; ?></h2>
            <button type="button" class="modal-close" onclick="closeModal('qaModal')" aria-label="Tutup">&times;</button>
        </div>

        <form method="POST" action="qa_manage.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id_pengetahuan" id="id_pengetahuan" value="<?php echo $editMode ? (int)$editData['id_pengetahuan'] : 0; ?>">

            <div class="form-row">
                <label class="form-label">Pertanyaan</label>
<textarea name="pertanyaan" id="f_pertanyaan" required><?php echo $editMode ? htmlspecialchars($editData['pertanyaan'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        <small class="field-error">Pertanyaan wajib diisi.</small>
                    </div>

            <div class="form-row">
                <label class="form-label">Jawaban</label>
<textarea name="jawaban" id="f_jawaban" required><?php echo $editMode ? htmlspecialchars($editData['jawaban'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        <small class="field-error">Jawaban wajib diisi.</small>
                    </div>

            <div class="form-row">
                <label class="form-label">Kategori</label>
                <select name="kategori">
                    <?php
                    $kategoriList = [];
                    try {
                        $stmt = $db->query("SELECT DISTINCT kategori FROM tb_pengetahuan WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
                        $kategoriList = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    } catch (PDOException $e) {
                        error_log("Kategori list error: " . $e->getMessage());
                    }
                    if (!in_array('Umum', $kategoriList, true)) {
                        $kategoriList[] = 'Umum';
                    }

                    $kategoriAktif = $editMode ? $editData['kategori'] : 'Umum';
                    foreach ($kategoriList as $k) {
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

            <div style="display: flex; gap: 12px; margin-top: 4px;">
                <button type="submit" class="btn btn-primary btn-inline"><?php echo $editMode ? 'Simpan Perubahan' : 'Simpan'; ?></button>
                <?php if ($editMode): ?>
                    <a href="qa_manage.php" class="btn btn-sm btn-red">Batal Edit</a>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-red" onclick="closeModal('qaModal')">Batal</button>
                <?php endif; ?>
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

function openAddModal() {
    document.getElementById('id_pengetahuan').value = 0;
    document.getElementById('f_pertanyaan').value = '';
    document.getElementById('f_jawaban').value = '';
    document.getElementById('qaModal').classList.remove('dirty');
    openModal('qaModal');
}

document.getElementById('qaModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal('qaModal');
    }
});

<?php if ($editMode): ?>
    openModal('qaModal');
<?php endif; ?>
</script>
<script src="../assets/js/admin.js"></script>

</body>
</html>