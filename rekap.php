<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") { header("location:login.php"); exit; }

$query = mysqli_query($koneksi, "SELECT * FROM absensi_magang ORDER BY id DESC");
$total = mysqli_num_rows($query);

$q_aktif  = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang WHERE JAM_PULANG='BELUM' OR JAM_PULANG='00:00:00' OR JAM_PULANG IS NULL OR JAM_PULANG=''");
$m_aktif  = mysqli_fetch_assoc($q_aktif)['n'] ?? 0;
$q_pulang = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang WHERE JAM_PULANG!='BELUM' AND JAM_PULANG!='00:00:00' AND JAM_PULANG IS NOT NULL AND JAM_PULANG!=''");
$m_pulang = mysqli_fetch_assoc($q_pulang)['n'] ?? 0;
$q_hari   = mysqli_query($koneksi, "SELECT COUNT(*) as n FROM absensi_magang WHERE TANGGAL=CURDATE()");
$m_hari   = mysqli_fetch_assoc($q_hari)['n'] ?? 0;

$rows = [];
mysqli_data_seek($query, 0);
$no = 1;
while ($d = mysqli_fetch_assoc($query)) {
    $jam_p = $d['JAM_PULANG'] ?? '';
    $status = ($jam_p == 'BELUM' || $jam_p == '00:00:00' || empty($jam_p)) ? 'Di Ruangan' : 'Pulang';
    $rows[] = [
        'no'       => $no++,
        'nama'     => $d['NAMA'] ?? '',
        'kegiatan' => $d['KEGIATAN'] ?? '',
        'tanggal'  => $d['TANGGAL'] ?? '',
        'jam_m'    => $d['JAM_MASUK'] ?? '',
        'jam_p'    => ($status == 'Pulang') ? $jam_p : '-',
        'status'   => $status,
        'id'       => $d['id'] ?? 0,
    ];
}

$teks_rekap = "📋 *REKAP ABSENSI LAB TIMUR*\n";
$teks_rekap .= "📅 " . date('d F Y, H:i') . " WIB\n";
$teks_rekap .= "━━━━━━━━━━━━━━━━\n";
foreach ($rows as $r) {
    $teks_rekap .= "\n{$r['no']}. *{$r['nama']}*\n";
    $teks_rekap .= "   📌 {$r['kegiatan']}\n";
    $teks_rekap .= "   🕐 Masuk: {$r['jam_m']} | Pulang: {$r['jam_p']}\n";
    $teks_rekap .= "   Status: {$r['status']}\n";
}
$teks_rekap .= "\n━━━━━━━━━━━━━━━━\n";
$teks_rekap .= "Total: {$total} data | Aktif: {$m_aktif} | Pulang: {$m_pulang}";
$wa_text = urlencode($teks_rekap);
$email_subject = urlencode("Rekap Absensi Lab Timur - " . date('d F Y'));
$email_body = urlencode(str_replace('*', '', $teks_rekap));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAP DATA ABSENSI</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&family=JetBrains+Mono&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    :root {
        --bg: #020617;
        --card: #0f172a;
        --border: #1e293b;
        --accent: #00d4ff;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: var(--bg);
        color: #fff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        min-height: 100vh;
        padding: 24px;
    }

    .glass {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 20px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px 20px;
        text-align: center;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    /* ===== TABEL DENGAN HEADER STICKY + TBODY SCROLL ===== */
    .table-container {
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    /* Lebar kolom - HARUS SAMA antara thead dan tbody */
    .col-no {
        width: 50px;
    }

    .col-nama {
        width: 250px;
    }

    .col-tgl {
        width: 110px;
    }

    .col-masuk {
        width: 110px;
    }

    .col-pulang {
        width: 110px;
    }

    .col-status {
        width: 130px;
    }

    .col-aksi {
        width: 120px;
    }

    /* HEADER - tidak scroll */
    .thead-fixed {
        display: block;
        width: 100%;
        overflow: hidden;
    }

    .thead-fixed table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .thead-fixed th {
        background: #071020 !important;
        border-bottom: 2px solid var(--accent) !important;
        color: var(--accent) !important;
        padding: 13px 14px !important;
        font-size: 0.73rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /* TBODY - yang scroll */
    .tbody-scroll {
        display: block;
        max-height: 320px;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: var(--accent) transparent;
    }

    .tbody-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .tbody-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .tbody-scroll::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 10px;
    }

    .tbody-scroll table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .tbody-scroll td {
        border-bottom: 1px solid var(--border);
        padding: 13px 14px;
        vertical-align: middle;
    }

    .tbody-scroll tr:hover td {
        background: rgba(0, 212, 255, 0.04) !important;
    }

    .tbody-scroll tr.row-aktif td {
        background: rgba(34, 197, 94, 0.03) !important;
    }

    .tbody-scroll tr.row-pulang td {
        background: rgba(100, 116, 139, 0.03) !important;
    }

    .name-tag {
        color: #fff;
        font-weight: 800;
        font-size: 0.92rem;
    }

    .task-tag {
        color: var(--accent);
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.76rem;
    }

    .badge-aktif {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-pulang {
        background: rgba(148, 163, 184, 0.1);
        color: #64748b;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .btn-wa {
        background: #25D366;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-wa:hover {
        background: #1da851;
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-email {
        background: #ea4335;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-email:hover {
        background: #c23321;
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-copy {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-copy:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .search-box {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 10px !important;
        font-size: 0.83rem;
    }

    .search-box::placeholder {
        color: #4b5563;
    }

    .empty-state {
        text-align: center;
        padding: 60px;
        color: #334155;
    }

    .modal-content {
        background: #0f172a;
        border: 1px solid var(--accent);
        border-radius: 20px;
        color: white;
    }

    .share-preview {
        background: rgba(0, 0, 0, 0.4);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: #94a3b8;
        max-height: 250px;
        overflow-y: auto;
        white-space: pre-wrap;
        scrollbar-width: thin;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white;
            color: black;
            padding: 0;
        }

        .glass {
            border: 1px solid #ccc;
            border-radius: 0;
        }

        .thead-fixed th {
            background: #f0f0f0 !important;
            color: #000 !important;
        }

        .tbody-scroll {
            max-height: none !important;
            overflow: visible !important;
        }
    }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print">
        <div>
            <h3 class="fw-800 m-0"><i class="bi bi-archive-fill text-info me-2"></i>REKAP <span
                    class="text-info">DATA</span></h3>
            <div class="small text-secondary fw-bold mt-1"><i
                    class="bi bi-calendar-fill me-1"></i><?= date('d F Y, H:i') ?> WIB</div>
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-bold no-print">
            <i class="bi bi-arrow-left me-1"></i>DASHBOARD
        </a>
    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4 no-print">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="small text-secondary fw-bold mb-1" style="font-size:0.65rem;"><i
                        class="bi bi-database-fill text-info me-1"></i>TOTAL DATA</div>
                <div class="h4 m-0 text-info fw-800"><?= $total ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="small text-secondary fw-bold mb-1" style="font-size:0.65rem;"><i
                        class="bi bi-person-fill text-warning me-1"></i>DI RUANGAN</div>
                <div class="h4 m-0 text-warning fw-800"><?= $m_aktif ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="small text-secondary fw-bold mb-1" style="font-size:0.65rem;"><i
                        class="bi bi-box-arrow-right text-success me-1"></i>SUDAH PULANG</div>
                <div class="h4 m-0 text-success fw-800"><?= $m_pulang ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="small text-secondary fw-bold mb-1" style="font-size:0.65rem;"><i
                        class="bi bi-calendar-check me-1" style="color:#a78bfa;"></i>HARI INI</div>
                <div class="h4 m-0 fw-800" style="color:#a78bfa;"><?= $m_hari ?></div>
            </div>
        </div>
    </div>

    <!-- TABEL CARD -->
    <div class="glass p-4">
        <!-- TOOLBAR -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3 no-print">
            <h6 class="fw-bold m-0"><i class="bi bi-table text-info me-2"></i>DAFTAR ABSENSI LENGKAP</h6>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <input type="text" id="searchInput" class="form-control search-box form-control-sm"
                    placeholder="🔍 Cari nama..." style="width:180px;" oninput="filterTable()">
                <button class="btn-copy" onclick="copyRekap()"><i class="bi bi-clipboard"></i> Copy</button>
                <button class="btn-wa" data-bs-toggle="modal" data-bs-target="#modalShare" onclick="setShare('wa')"><i
                        class="bi bi-whatsapp"></i> WhatsApp</button>
                <button class="btn-email" data-bs-toggle="modal" data-bs-target="#modalShare"
                    onclick="setShare('email')"><i class="bi bi-envelope-fill"></i> Email</button>
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" onclick="window.print()"><i
                        class="bi bi-printer me-1"></i>Print</button>
            </div>
        </div>

        <!-- TABEL -->
        <div class="table-container">

            <!-- HEADER TETAP (tidak ikut scroll) -->
            <div class="thead-fixed">
                <table>
                    <colgroup>
                        <col class="col-no">
                        <col class="col-nama">
                        <col class="col-tgl">
                        <col class="col-masuk">
                        <col class="col-pulang">
                        <col class="col-status">
                        <col class="col-aksi">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="col-no">#</th>
                            <th class="col-nama"><i class="bi bi-person-fill me-1"></i>NAMA & KEGIATAN</th>
                            <th class="col-tgl"><i class="bi bi-calendar me-1"></i>TANGGAL</th>
                            <th class="col-masuk"><i class="bi bi-clock me-1"></i>JAM MASUK</th>
                            <th class="col-pulang"><i class="bi bi-clock-history me-1"></i>JAM PULANG</th>
                            <th class="col-status">STATUS</th>
                            <th class="col-aksi no-print">AKSI</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- TBODY SCROLL -->
            <div class="tbody-scroll">
                <table>
                    <colgroup>
                        <col class="col-no">
                        <col class="col-nama">
                        <col class="col-tgl">
                        <col class="col-masuk">
                        <col class="col-pulang">
                        <col class="col-status">
                        <col class="col-aksi">
                    </colgroup>
                    <tbody id="tableBody">
                        <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $r): ?>
                        <?php $isAktif = ($r['status'] == 'Di Ruangan'); ?>
                        <tr class="<?= $isAktif ? 'row-aktif' : 'row-pulang' ?>">
                            <td class="col-no text-secondary small"><?= $r['no'] ?></td>
                            <td class="col-nama">
                                <div class="name-tag"><?= htmlspecialchars($r['nama']) ?></div>
                                <div class="task-tag"><i class="bi bi-chevron-right" style="font-size:0.65rem;"></i>
                                    <?= htmlspecialchars($r['kegiatan']) ?></div>
                            </td>
                            <td class="col-tgl small text-secondary" style="white-space:nowrap;">
                                <?= $r['tanggal'] ? date('d/m/Y', strtotime($r['tanggal'])) : '-' ?>
                            </td>
                            <td class="col-masuk">
                                <span
                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fw-bold"
                                    style="font-size:0.72rem;white-space:nowrap;"><?= htmlspecialchars($r['jam_m']) ?></span>
                            </td>
                            <td class="col-pulang">
                                <?php if ($isAktif): ?>
                                <span class="text-warning small fw-bold">—</span>
                                <?php else: ?>
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 fw-bold"
                                    style="font-size:0.72rem;white-space:nowrap;"><?= htmlspecialchars($r['jam_p']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="col-status">
                                <?php if ($isAktif): ?>
                                <span class="badge-aktif"><i class="bi bi-record-fill me-1"
                                        style="font-size:0.5rem;color:#22c55e;"></i>DI RUANGAN</span>
                                <?php else: ?>
                                <span class="badge-pulang"><i class="bi bi-record me-1"
                                        style="font-size:0.5rem;"></i>PULANG</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-aksi no-print">
                                <a href="hapus.php?id=<?= $r['id'] ?>"
                                    onclick="return confirm('Hapus data <?= addslashes(htmlspecialchars($r['nama'])) ?>?')"
                                    class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold"
                                    style="font-size:0.72rem;">
                                    <i class="bi bi-trash3-fill me-1"></i>HAPUS
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state"><i class="bi bi-inbox d-block mb-2"
                                        style="font-size:2.5rem;"></i>Belum ada data absensi</div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- FOOTER -->
        <div class="d-flex justify-content-between align-items-center pt-3 mt-2 border-top border-secondary no-print">
            <small class="text-secondary">Total <strong class="text-white"><?= $total ?></strong> data absensi</small>
            <small class="text-secondary"><i class="bi bi-info-circle me-1"></i>Scroll atas/bawah untuk melihat semua
                data</small>
        </div>
    </div>

    <!-- MODAL SHARE -->
    <div class="modal fade" id="modalShare" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-secondary">
                    <h6 class="modal-title fw-bold" id="modalShareTitle"><i class="bi bi-share-fill me-2"></i>KIRIM
                        REKAP</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Preview rekap yang akan dikirim:</p>
                    <div class="share-preview"><?= htmlspecialchars($teks_rekap) ?></div>
                    <div class="mt-3" id="waSection">
                        <label class="small text-secondary fw-bold mb-2">NOMOR WHATSAPP TUJUAN</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="waNumber" class="form-control" placeholder="Contoh: 628123456789"
                                style="background:#1e293b;border-color:#334155;color:white;border-radius:10px;">
                            <button class="btn btn-success fw-bold px-4 rounded-pill" onclick="kirimWA()"><i
                                    class="bi bi-whatsapp me-1"></i>Kirim</button>
                        </div>
                        <small class="text-secondary mt-1 d-block">Format: 62 + nomor HP (tanpa 0 di depan)</small>
                    </div>
                    <div class="mt-3 d-none" id="emailSection">
                        <label class="small text-secondary fw-bold mb-2">EMAIL TUJUAN</label>
                        <div class="d-flex gap-2">
                            <input type="email" id="emailAddress" class="form-control" placeholder="contoh@email.com"
                                style="background:#1e293b;border-color:#334155;color:white;border-radius:10px;">
                            <button class="btn btn-danger fw-bold px-4 rounded-pill" onclick="kirimEmail()"><i
                                    class="bi bi-envelope-fill me-1"></i>Kirim</button>
                        </div>
                        <small class="text-secondary mt-1 d-block">Akan membuka aplikasi email kamu</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const rekapText = <?= json_encode($teks_rekap) ?>;
    const waText = <?= json_encode($wa_text) ?>;
    const emailSubj = <?= json_encode($email_subject) ?>;
    const emailBody = <?= json_encode($email_body) ?>;

    function setShare(mode) {
        document.getElementById('waSection').classList.toggle('d-none', mode !== 'wa');
        document.getElementById('emailSection').classList.toggle('d-none', mode !== 'email');
        document.getElementById('modalShareTitle').innerHTML = mode === 'wa' ?
            '<i class="bi bi-whatsapp me-2 text-success"></i>KIRIM VIA WHATSAPP' :
            '<i class="bi bi-envelope-fill me-2 text-danger"></i>KIRIM VIA EMAIL';
    }

    function kirimWA() {
        let nomor = document.getElementById('waNumber').value.trim().replace(/\D/g, '');
        if (!nomor) {
            alert('Masukkan nomor WhatsApp dulu!');
            return;
        }
        if (nomor.startsWith('0')) nomor = '62' + nomor.slice(1);
        window.open('https://wa.me/' + nomor + '?text=' + waText, '_blank');
    }

    function kirimEmail() {
        const email = document.getElementById('emailAddress').value.trim();
        if (!email) {
            alert('Masukkan alamat email dulu!');
            return;
        }
        window.open('mailto:' + email + '?subject=' + emailSubj + '&body=' + emailBody, '_blank');
    }

    function copyRekap() {
        navigator.clipboard.writeText(rekapText).then(() => {
            const btn = document.querySelector('.btn-copy');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.style.background = 'rgba(34,197,94,0.2)';
            btn.style.borderColor = '#22c55e';
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.style.background = '';
                btn.style.borderColor = '';
            }, 2000);
        });
    }

    function filterTable() {
        const keyword = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
        });
    }
    </script>
</body>

</html>