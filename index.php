<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") { header("location:login.php"); exit; }

function isAktif($jam_pulang) {
    return empty($jam_pulang) || $jam_pulang == 'BELUM' || $jam_pulang == '00:00:00';
}

function hitungDurasi($jam_masuk, $jam_pulang) {
    if (isAktif($jam_pulang)) {
        $mulai = strtotime($jam_masuk);
        $selesai = time();
        $diff = $selesai - $mulai;
        if ($diff < 0) return '<span class="text-warning small">Sedang berlangsung</span>';
        $jam = floor($diff / 3600);
        $menit = floor(($diff % 3600) / 60);
        return '<span class="text-warning small fw-bold">' . $jam . 'j ' . $menit . 'm ⏱</span>';
    } else {
        $mulai = strtotime($jam_masuk);
        $selesai = strtotime($jam_pulang);
        $diff = $selesai - $mulai;
        if ($diff <= 0) return '<span class="text-secondary small">-</span>';
        $jam = floor($diff / 3600);
        $menit = floor(($diff % 3600) / 60);
        return '<span class="text-success small fw-bold">' . $jam . 'j ' . $menit . 'm</span>';
    }
}

// STATISTIK
$q_aktif  = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang WHERE JAM_PULANG = 'BELUM' OR JAM_PULANG = '00:00:00' OR JAM_PULANG IS NULL OR JAM_PULANG = ''");
$m_aktif  = mysqli_fetch_assoc($q_aktif)['n'] ?? 0;
$q_total  = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang");
$m_total  = mysqli_fetch_assoc($q_total)['n'] ?? 0;
$q_pulang = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang WHERE JAM_PULANG != 'BELUM' AND JAM_PULANG != '00:00:00' AND JAM_PULANG IS NOT NULL AND JAM_PULANG != '' AND TANGGAL = CURDATE()");
$m_pulang = mysqli_fetch_assoc($q_pulang)['n'] ?? 0;
$q_hari   = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang WHERE TANGGAL = CURDATE()");
$m_hari   = mysqli_fetch_assoc($q_hari)['n'] ?? 0;

// ===== DETEKSI ABSEN DOBEL HARI INI =====
$q_dobel = mysqli_query($koneksi, "
    SELECT NAMA, COUNT(*) as jumlah, MIN(JAM_MASUK) as pertama, MAX(JAM_MASUK) as terakhir
    FROM absensi_magang
    WHERE TANGGAL = CURDATE()
    GROUP BY NAMA
    HAVING COUNT(*) > 1
    ORDER BY jumlah DESC
");
$dobel_list = [];
while ($d = mysqli_fetch_assoc($q_dobel)) {
    $dobel_list[] = $d;
}

// FILTER & SEARCH
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'aktif';

$where = "WHERE 1=1";
if ($filter == 'aktif') {
    $where .= " AND (JAM_PULANG = 'BELUM' OR JAM_PULANG = '00:00:00' OR JAM_PULANG IS NULL OR JAM_PULANG = '')";
} elseif ($filter == 'pulang') {
    $where .= " AND JAM_PULANG != 'BELUM' AND JAM_PULANG != '00:00:00' AND JAM_PULANG IS NOT NULL AND JAM_PULANG != ''";
} elseif ($filter == 'hari') {
    $where .= " AND TANGGAL = CURDATE()";
}
if (!empty($search)) {
    $s = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND (NAMA LIKE '%$s%' OR KEGIATAN LIKE '%$s%')";
}

$res = mysqli_query($koneksi, "SELECT * FROM absensi_magang $where ORDER BY id DESC");
$total_tampil = $res ? mysqli_num_rows($res) : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>ADMIN COMMAND CENTER</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&family=JetBrains+Mono&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    :root {
        --bg: #060b18;
        --accent: #00d4ff;
        --card: rgba(17, 25, 40, 0.9);
        --border: rgba(255, 255, 255, 0.1);
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: var(--bg);
        color: #fff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        height: 100vh;
        overflow: hidden;
        padding: 18px 22px;
    }

    .glass {
        background: var(--card);
        backdrop-filter: blur(15px);
        border: 1px solid var(--border);
        border-radius: 18px;
    }

    .stat-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 10px 16px;
        transition: transform 0.2s;
        cursor: default;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .main-layout {
        display: flex;
        gap: 16px;
        height: calc(100vh - 110px);
    }

    .sidebar {
        width: 260px;
        min-width: 260px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .sidebar::-webkit-scrollbar {
        display: none;
    }

    .content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .table-wrapper {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--accent) transparent;
        border-radius: 0 0 14px 14px;
    }

    .table-wrapper::-webkit-scrollbar {
        width: 5px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 10px;
    }

    .table {
        margin: 0;
    }

    .table thead th {
        position: sticky;
        top: 0;
        background: #0a1628 !important;
        z-index: 10;
        border-bottom: 2px solid var(--accent) !important;
        color: var(--accent) !important;
        padding: 11px 10px !important;
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .table td {
        border-bottom: 1px solid var(--border) !important;
        padding: 11px 10px !important;
        background: transparent !important;
        vertical-align: middle;
    }

    tr.row-aktif td {
        background: rgba(34, 197, 94, 0.04) !important;
    }

    tr.row-pulang td {
        background: rgba(100, 116, 139, 0.04) !important;
    }

    tr:hover td {
        background: rgba(0, 212, 255, 0.06) !important;
    }

    /* HIGHLIGHT baris dobel */
    tr.row-dobel td {
        background: rgba(251, 191, 36, 0.06) !important;
    }

    tr.row-dobel:hover td {
        background: rgba(251, 191, 36, 0.12) !important;
    }

    .name-tag {
        color: #fff !important;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .task-tag {
        color: var(--accent) !important;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
    }

    .modal-content {
        background: #0f172a;
        border: 1px solid var(--accent);
        border-radius: 18px;
        color: white;
    }

    .form-control,
    .form-select {
        background: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #fff !important;
        border-radius: 8px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.15) !important;
    }

    .filter-btn {
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 5px 14px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: transparent;
        color: #888;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-block;
    }

    .filter-btn:hover {
        color: #fff;
        border-color: #fff;
    }

    .filter-btn.active {
        background: var(--accent);
        color: #000 !important;
        border-color: var(--accent);
    }

    .badge-aktif {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 20px;
        padding: 3px 9px;
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-pulang {
        background: rgba(148, 163, 184, 0.1);
        color: #64748b;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 20px;
        padding: 3px 9px;
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-dobel {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.35);
        border-radius: 20px;
        padding: 2px 8px;
        font-size: 0.65rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .search-box {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 10px !important;
        font-size: 0.82rem;
    }

    .search-box::placeholder {
        color: #555;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #334155;
    }

    .toast-container {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 9999;
    }

    .sidebar-btn {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #aaa;
        border-radius: 12px;
        padding: 8px 12px;
        text-align: left;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        display: block;
        width: 100%;
    }

    .sidebar-btn:hover {
        background: rgba(0, 212, 255, 0.1);
        border-color: var(--accent);
        color: var(--accent);
    }

    .sidebar-btn.danger:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        color: #ef4444;
    }

    /* ===== NOTIF DOBEL ===== */
    .notif-dobel-bar {
        background: rgba(251, 191, 36, 0.08);
        border: 1px solid rgba(251, 191, 36, 0.3);
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 10px;
        animation: pulse-border 2s infinite;
    }

    @keyframes pulse-border {

        0%,
        100% {
            border-color: rgba(251, 191, 36, 0.3);
            box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.1);
        }

        50% {
            border-color: rgba(251, 191, 36, 0.7);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.05);
        }
    }

    .notif-dobel-title {
        font-size: 0.72rem;
        font-weight: 800;
        color: #fbbf24;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notif-dobel-item {
        background: rgba(251, 191, 36, 0.07);
        border: 1px solid rgba(251, 191, 36, 0.2);
        border-radius: 8px;
        padding: 7px 12px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .notif-dobel-item:last-child {
        margin-bottom: 0;
    }

    .notif-nama {
        font-size: 0.78rem;
        font-weight: 800;
        color: #fff;
    }

    .notif-detail {
        font-size: 0.68rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .notif-count {
        background: #fbbf24;
        color: #000;
        border-radius: 20px;
        padding: 2px 9px;
        font-size: 0.68rem;
        font-weight: 800;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .dismiss-notif {
        background: none;
        border: none;
        color: #475569;
        font-size: 0.7rem;
        cursor: pointer;
        padding: 0 4px;
        transition: color 0.2s;
    }

    .dismiss-notif:hover {
        color: #fbbf24;
    }
    </style>
</head>

<body>

    <?php if (isset($_GET['msg'])): ?>
    <div class="toast-container">
        <div class="toast show align-items-center text-white border-0"
            style="background:<?= $_GET['msg']=='ok'?'#14532d':'#7f1d1d' ?>;border-radius:12px;padding:2px;">
            <div class="d-flex">
                <div class="toast-body fw-bold"><i
                        class="bi bi-<?= $_GET['msg']=='ok'?'check-circle':'x-circle' ?>-fill me-2"></i><?= $_GET['msg']=='ok'?'✅ Berhasil disimpan!':'❌ Terjadi kesalahan!' ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="fw-800 m-0" style="font-size:1.5rem;"><i class="bi bi-shield-lock-fill text-info me-2"></i>ADMIN
                <span class="text-info">PANEL</span>
            </h1>
            <div style="font-size:0.7rem;" class="text-secondary fw-bold"><i
                    class="bi bi-broadcast text-danger me-1"></i>MONITORING MAHASISWA &nbsp;|&nbsp;
                <?= date('d F Y, H:i') ?> WIB</div>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <div class="stat-card text-center">
                <div style="font-size:0.55rem;" class="text-warning fw-bold"><i class="bi bi-person-walking me-1"></i>DI
                    LOKASI</div>
                <div class="h6 m-0 text-warning fw-800"><?= $m_aktif ?> <span style="font-size:0.75rem;"
                        class="text-white fw-normal">org</span></div>
            </div>
            <div class="stat-card text-center">
                <div style="font-size:0.55rem;" class="text-info fw-bold"><i class="bi bi-calendar-check me-1"></i>HARI
                    INI</div>
                <div class="h6 m-0 text-info fw-800"><?= $m_hari ?> <span style="font-size:0.75rem;"
                        class="text-white fw-normal">org</span></div>
            </div>
            <div class="stat-card text-center">
                <div style="font-size:0.55rem;" class="text-success fw-bold"><i
                        class="bi bi-box-arrow-right me-1"></i>PULANG</div>
                <div class="h6 m-0 text-success fw-800"><?= $m_pulang ?> <span style="font-size:0.75rem;"
                        class="text-white fw-normal">org</span></div>
            </div>
            <div class="stat-card text-center">
                <div style="font-size:0.55rem;" class="text-secondary fw-bold"><i class="bi bi-database me-1"></i>TOTAL
                    DB</div>
                <div class="h6 m-0 fw-800"><?= $m_total ?> <span style="font-size:0.75rem;"
                        class="text-white fw-normal">data</span></div>
            </div>
            <!-- Indikator dobel di header -->

            <?php if (!empty($dobel_list)): ?>
            <div class="stat-card text-center" style="border-color:rgba(251,191,36,0.4);cursor:pointer;"
                onclick="document.getElementById('notifDobelBar').scrollIntoView({behavior:'smooth'})"
                title="Ada absen dobel!">
                <div style="font-size:0.55rem;" class="text-warning fw-bold"><i
                        class="bi bi-exclamation-triangle-fill me-1"></i>DOBEL</div>
                <div class="h6 m-0 fw-800" style="color:#fbbf24;"><?= count($dobel_list) ?> <span
                        style="font-size:0.75rem;" class="text-white fw-normal">org</span></div>
            </div>
            <?php endif; ?>
            <a href="rekap.php" class="btn btn-info btn-sm rounded-pill px-3 fw-bold"><i
                    class="bi bi-archive-fill me-1"></i>REKAP</a>
            <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold"><i class="bi bi-power"></i></a>
        </div>
    </div>

    <!-- MAIN LAYOUT -->
    <div class="main-layout">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <!-- FORM INPUT -->
            <div class="glass p-3">
                <h6 class="fw-bold mb-3 text-info" style="font-size:0.82rem;"><i
                        class="bi bi-person-plus-fill me-2"></i>INPUT ABSENSI BARU</h6>
                <form action="proses_admin.php?act=tambah" method="POST">
                    <div class="mb-2">
                        <label class="small text-secondary fw-bold mb-1" style="font-size:0.7rem;">NAMA
                            MAHASISWA</label>
                        <input type="text" name="nama" class="form-control form-control-sm"
                            placeholder="Nama lengkap..." required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary fw-bold mb-1" style="font-size:0.7rem;">TUGAS /
                            KEGIATAN</label>
                        <textarea name="kegiatan" class="form-control form-control-sm" rows="3"
                            placeholder="Deskripsi kegiatan..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-info btn-sm w-100 fw-bold text-white"
                        style="border-radius:10px;">
                        <i class="bi bi-send-fill me-1"></i>MASUKKAN DATA
                    </button>
                </form>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="glass p-3">
                <h6 class="fw-bold mb-2 text-warning" style="font-size:0.8rem;"><i
                        class="bi bi-lightning-fill me-1"></i>QUICK ACTIONS</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="rekap.php" class="sidebar-btn"><i
                            class="bi bi-file-earmark-spreadsheet me-2 text-info"></i>Lihat Rekap Data</a>
                    <a href="?filter=hari" class="sidebar-btn"><i class="bi bi-calendar-day me-2 text-success"></i>Data
                        Hari Ini</a>
                    <a href="?filter=aktif" class="sidebar-btn"><i
                            class="bi bi-person-check me-2 text-warning"></i>Mahasiswa Aktif</a>
                    <a href="?filter=semua" class="sidebar-btn"><i class="bi bi-database me-2 text-info"></i>Semua
                        Data</a>
                    <button class="sidebar-btn danger" data-bs-toggle="modal" data-bs-target="#modalPulangSemua">
                        <i class="bi bi-box-arrow-right me-2 text-danger"></i>Pulangkan Semua
                    </button>
                </div>
            </div>

            <!-- INFO -->
            <div class="glass p-3">
                <h6 class="fw-bold mb-2 text-secondary" style="font-size:0.78rem;"><i
                        class="bi bi-info-circle me-1"></i>INFO</h6>
                <div style="font-size:0.72rem;" class="text-secondary">
                    <div class="mb-1"><span class="badge-aktif">DI RUANGAN</span> = Belum pulang</div>
                    <div class="mb-1"><span class="badge-pulang">PULANG</span> = Sudah pulang</div>
                    <div class="mb-1"><span class="badge-dobel">⚠ DOBEL</span> = Absen &gt;1x hari ini</div>
                    <div class="mt-2 text-info"><i class="bi bi-arrow-clockwise me-1"></i>Auto-refresh: 60 detik</div>
                </div>
            </div>
        </div>

        <!-- KONTEN TABEL -->
        <div class="content glass p-3">
            <!-- TOOLBAR -->
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <h6 class="fw-bold m-0" style="font-size:0.85rem;"><i
                        class="bi bi-person-badge-fill text-danger me-2"></i>DATA MAHASISWA</h6>
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        class="form-control search-box form-control-sm" placeholder="🔍 Cari nama / kegiatan..."
                        style="width:200px;">
                    <button type="submit" class="btn btn-info btn-sm rounded-pill px-3 fw-bold">Cari</button>
                    <?php if (!empty($search)): ?>
                    <a href="?filter=<?= $filter ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">✕</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- FILTER TABS -->
            <div class="d-flex gap-2 mb-2 flex-wrap">
                <a href="?filter=aktif<?= !empty($search)?'&search='.urlencode($search):'' ?>"
                    class="filter-btn <?= $filter=='aktif'?'active':'' ?>"><i class="bi bi-circle-fill me-1"
                        style="color:#22c55e;font-size:0.45rem;vertical-align:middle;"></i>Aktif (<?= $m_aktif ?>)</a>
                <a href="?filter=pulang<?= !empty($search)?'&search='.urlencode($search):'' ?>"
                    class="filter-btn <?= $filter=='pulang'?'active':'' ?>"><i
                        class="bi bi-box-arrow-right me-1"></i>Pulang (<?= $m_pulang ?>)</a>
                <a href="?filter=hari<?= !empty($search)?'&search='.urlencode($search):'' ?>"
                    class="filter-btn <?= $filter=='hari'?'active':'' ?>"><i class="bi bi-calendar me-1"></i>Hari Ini
                    (<?= $m_hari ?>)</a>
                <a href="?filter=semua<?= !empty($search)?'&search='.urlencode($search):'' ?>"
                    class="filter-btn <?= $filter=='semua'?'active':'' ?>"><i class="bi bi-database me-1"></i>Semua
                    (<?= $m_total ?>)</a>
            </div>

            <!-- ===== NOTIF DOBEL BAR ===== -->
            <?php if (!empty($dobel_list)): ?>
            <div class="notif-dobel-bar" id="notifDobelBar">
                <div class="notif-dobel-title">
                    <span>⚠️</span>
                    PERINGATAN — ABSEN DOBEL HARI INI
                    <span
                        style="background:rgba(251,191,36,0.2);border-radius:20px;padding:1px 8px;font-size:0.65rem;"><?= count($dobel_list) ?>
                        mahasiswa</span>
                    <button class="dismiss-notif ms-auto"
                        onclick="document.getElementById('notifDobelBar').style.display='none'" title="Tutup"><i
                            class="bi bi-x-lg"></i></button>
                </div>
                <?php foreach ($dobel_list as $d): ?>
                <div class="notif-dobel-item">
                    <div>
                        <div class="notif-nama">
                            <i class="bi bi-person-fill me-1" style="color:#fbbf24;"></i>
                            <?= htmlspecialchars($d['NAMA']) ?>
                        </div>
                        <div class="notif-detail">
                            Absen pertama: <strong style="color:#94a3b8;"><?= $d['pertama'] ?></strong>
                            &nbsp;·&nbsp;
                            Terakhir: <strong style="color:#94a3b8;"><?= $d['terakhir'] ?></strong>
                            &nbsp;·&nbsp;
                            Tanggal: <strong style="color:#94a3b8;"><?= date('d/m/Y') ?></strong>
                        </div>
                    </div>
                    <div class="notif-count">
                        <?= $d['jumlah'] ?>× absen
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <!-- ===== END NOTIF DOBEL ===== -->

            <!-- TABEL SCROLL -->
            <div class="table-wrapper">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th width="4%">#</th>
                            <th width="22%"><i class="bi bi-person-fill me-1"></i>NAMA & TUGAS</th>
                            <th width="10%"><i class="bi bi-calendar me-1"></i>TANGGAL</th>
                            <th width="9%"><i class="bi bi-clock me-1"></i>MASUK</th>
                            <th width="9%"><i class="bi bi-clock-history me-1"></i>PULANG</th>
                            <th width="12%"><i class="bi bi-hourglass-split me-1"></i>DURASI</th>
                            <th width="12%">STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Buat set nama yang dobel untuk highlight
                        $nama_dobel = array_column($dobel_list, 'NAMA');

                        if ($res && mysqli_num_rows($res) > 0):
                            $no = 1;
                            while($row = mysqli_fetch_assoc($res)):
                                $id       = $row['id'] ?? 0;
                                $nama     = $row['NAMA'] ?? '';
                                $kegiatan = $row['KEGIATAN'] ?? '';
                                $jam_m    = $row['JAM_MASUK'] ?? '';
                                $jam_p    = $row['JAM_PULANG'] ?? '';
                                $tgl      = $row['TANGGAL'] ?? '';
                                $aktif    = isAktif($jam_p);
                                $isDobel  = in_array($nama, $nama_dobel) && $tgl == date('Y-m-d');
                                $rowClass = $isDobel ? 'row-dobel' : ($aktif ? 'row-aktif' : 'row-pulang');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-secondary" style="font-size:0.75rem;"><?= $no++ ?></td>
                            <td>
                                <div class="name-tag">
                                    <?= htmlspecialchars($nama) ?>
                                    <?php if ($isDobel): ?>
                                    <span class="badge-dobel ms-1">⚠ DOBEL</span>
                                    <?php endif; ?>
                                </div>
                                <div class="task-tag"><i class="bi bi-chevron-right" style="font-size:0.65rem;"></i>
                                    <?= htmlspecialchars($kegiatan) ?></div>
                            </td>
                            <td style="font-size:0.78rem;" class="text-secondary">
                                <?= $tgl ? date('d/m/Y', strtotime($tgl)) : '-' ?></td>
                            <td>
                                <span
                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fw-bold"
                                    style="font-size:0.72rem;"><?= htmlspecialchars($jam_m) ?></span>
                            </td>
                            <td>
                                <?php if ($aktif): ?>
                                <span class="text-warning" style="font-size:0.8rem;">—</span>
                                <?php else: ?>
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 fw-bold"
                                    style="font-size:0.72rem;"><?= htmlspecialchars($jam_p) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= hitungDurasi($jam_m, $jam_p) ?></td>
                            <td>
                                <?php if ($aktif): ?>
                                <span class="badge-aktif"><i class="bi bi-record-fill me-1"
                                        style="font-size:0.5rem;color:#22c55e;"></i>DI RUANGAN</span>
                                <?php else: ?>
                                <span class="badge-pulang"><i class="bi bi-record me-1"
                                        style="font-size:0.5rem;"></i>PULANG</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <?php if ($aktif): ?>
                                    <a href="proses_admin.php?act=pulang&id=<?= $id ?>"
                                        class="btn btn-warning btn-sm rounded-pill px-2 fw-bold text-dark"
                                        style="font-size:0.7rem;" title="Set Pulang">
                                        <i class="bi bi-box-arrow-right"></i> Pulang
                                    </a>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-info btn-sm rounded-pill px-2" data-bs-toggle="modal"
                                        data-bs-target="#edit<?= $id ?>" title="Edit">
                                        <i class="bi bi-pencil-fill" style="font-size:0.75rem;"></i>
                                    </button>
                                    <a href="proses_admin.php?act=hapus&id=<?= $id ?>"
                                        onclick="return confirm('Hapus data <?= addslashes(htmlspecialchars($nama)) ?>?')"
                                        class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Hapus">
                                        <i class="bi bi-trash3-fill" style="font-size:0.75rem;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="edit<?= $id ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="proses_admin.php?act=edit" method="POST">
                                        <div class="modal-header border-secondary">
                                            <h6 class="modal-title fw-bold text-info"><i
                                                    class="bi bi-pencil-square me-2"></i>EDIT DATA —
                                                <?= htmlspecialchars($nama) ?></h6>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <div class="mb-3">
                                                <label class="small text-secondary fw-bold mb-1">NAMA LENGKAP</label>
                                                <input type="text" name="nama" class="form-control"
                                                    value="<?= htmlspecialchars($nama) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small text-secondary fw-bold mb-1">KEGIATAN</label>
                                                <textarea name="kegiatan" class="form-control" rows="3"
                                                    required><?= htmlspecialchars($kegiatan) ?></textarea>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="small text-secondary fw-bold mb-1">JAM MASUK</label>
                                                    <input type="time" name="jam_masuk" class="form-control"
                                                        value="<?= $jam_m ?>" step="1">
                                                </div>
                                                <div class="col-6">
                                                    <label class="small text-secondary fw-bold mb-1">JAM PULANG</label>
                                                    <input type="time" name="jam_pulang" class="form-control"
                                                        value="<?= (!$aktif ? $jam_p : '') ?>" step="1">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-secondary">
                                            <button type="button" class="btn btn-secondary rounded-pill px-3"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit"
                                                class="btn btn-info text-white rounded-pill px-4 fw-bold"><i
                                                    class="bi bi-check2-circle me-1"></i>SIMPAN</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state"><i class="bi bi-inbox d-block mb-2"
                                        style="font-size:2.5rem;"></i><?= !empty($search) ? "Tidak ada hasil untuk \"<strong>".htmlspecialchars($search)."</strong>\"" : "Tidak ada data ditemukan" ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- FOOTER -->
            <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top border-secondary">
                <small class="text-secondary" style="font-size:0.72rem;">Menampilkan <strong
                        class="text-white"><?= $total_tampil ?></strong> data
                    <?= !empty($search) ? '— pencarian: "<strong class="text-info">'.htmlspecialchars($search).'</strong>"' : '' ?></small>
                <small class="text-secondary" style="font-size:0.72rem;"><i
                        class="bi bi-arrow-clockwise me-1"></i><?= date('H:i:s') ?></small>
            </div>
        </div>
    </div>

    <!-- MODAL PULANGKAN SEMUA -->
    <div class="modal fade" id="modalPulangSemua" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header border-secondary justify-content-center">
                    <h6 class="modal-title fw-bold text-warning"><i
                            class="bi bi-exclamation-triangle-fill me-2"></i>KONFIRMASI PULANG SEMUA</h6>
                </div>
                <div class="modal-body py-4">
                    <i class="bi bi-box-arrow-right text-warning d-block mb-3" style="font-size:3rem;"></i>
                    <p class="fw-bold mb-1">Pulangkan semua mahasiswa sekarang?</p>
                    <p class="text-secondary small">Semua yang berstatus "DI RUANGAN" akan di-set jam pulang = waktu
                        sekarang.</p>
                    <div
                        class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning small mt-2">
                        <i class="bi bi-exclamation-circle me-1"></i>Tindakan ini tidak bisa dibatalkan!
                    </div>
                </div>
                <div class="modal-footer border-secondary justify-content-center">
                    <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <a href="proses_admin.php?act=pulang_semua"
                        class="btn btn-warning fw-bold rounded-pill px-4 text-dark"><i
                            class="bi bi-box-arrow-right me-1"></i>Ya, Pulangkan Semua</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(t => {
            try {
                bootstrap.Toast.getOrCreateInstance(t).hide();
            } catch (e) {}
        });
    }, 3000);
    setTimeout(() => location.reload(), 60000);
    </script>
</body>

</html>