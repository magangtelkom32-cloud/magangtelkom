<?php
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['nama'])) { header("location:login.php"); exit; }

$nama_user = $_SESSION['nama'];
$today = date('Y-m-d');

// Logika Cek Jeda 2 Detik
$cek = mysqli_query($koneksi, "SELECT JAM_MASUK FROM absensi_magang WHERE NAMA = '$nama_user' AND TANGGAL = '$today' ORDER BY ID DESC LIMIT 1");
$sisa_detik = 0;
if (mysqli_num_rows($cek) > 0) {
    $data = mysqli_fetch_assoc($cek);
    $last_time = strtotime($today . " " . $data['JAM_MASUK']);
    $diff = time() - $last_time;
    if ($diff < 2) { $sisa_detik = 2 - $diff; }
}

// Ambil inisial nama untuk avatar
$nama_parts = explode(' ', $nama_user);
$inisial = strtoupper(substr($nama_parts[0], 0, 1));
if (isset($nama_parts[1])) $inisial .= strtoupper(substr($nama_parts[1], 0, 1));

// Salam berdasarkan jam
$jam = (int)date('H');
if ($jam >= 5 && $jam < 12) $salam = "Selamat Pagi";
elseif ($jam >= 12 && $jam < 15) $salam = "Selamat Siang";
elseif ($jam >= 15 && $jam < 18) $salam = "Selamat Sore";
else $salam = "Selamat Malam";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Lab Timur</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    :root {
        --accent: #00d4ff;
        --purple: #6366f1;
        --bg: #020617;
        --card: #0f172a;
        --border: #1e293b;
    }

    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: var(--bg);
        background-image:
            radial-gradient(ellipse at 0% 0%, rgba(99, 102, 241, 0.25) 0%, transparent 55%),
            radial-gradient(ellipse at 100% 0%, rgba(168, 85, 247, 0.2) 0%, transparent 55%),
            radial-gradient(ellipse at 50% 100%, rgba(0, 212, 255, 0.1) 0%, transparent 55%);
        padding: 20px;
    }

    /* FLOATING PARTICLES */
    .particles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        border-radius: 50%;
        animation: floatUp linear infinite;
        opacity: 0;
    }

    @keyframes floatUp {
        0% {
            transform: translateY(100vh) scale(0);
            opacity: 0;
        }

        10% {
            opacity: 0.4;
        }

        90% {
            opacity: 0.2;
        }

        100% {
            transform: translateY(-10vh) scale(1);
            opacity: 0;
        }
    }

    /* CARD */
    .main-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
        position: relative;
        z-index: 1;
        animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-width: 420px;
        width: 100%;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(40px) scale(0.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* HEADER */
    .profile-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
        padding: 36px 24px 48px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
    }

    .profile-header::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: -30px;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    /* AVATAR INISIAL */
    .avatar-ring {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: 3px solid rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 1.8rem;
        font-weight: 800;
        color: white;
        letter-spacing: 1px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        position: relative;
        z-index: 1;
    }

    .online-dot {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 16px;
        height: 16px;
        background: #22c55e;
        border-radius: 50%;
        border: 2px solid white;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
        }
    }

    .greeting-text {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .nama-text {
        color: white;
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }

    /* INFO BAR */
    .info-bar {
        background: rgba(0, 212, 255, 0.06);
        border-bottom: 1px solid var(--border);
        padding: 12px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
    }

    .info-item i {
        color: var(--accent);
    }

    .info-item strong {
        color: #94a3b8;
    }

    /* BODY */
    .card-body-custom {
        padding: 28px 24px;
    }

    /* LABEL */
    .field-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .field-label i {
        color: var(--accent);
        font-size: 0.85rem;
    }

    /* TEXTAREA */
    .kegiatan-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        border-radius: 14px;
        color: #e2e8f0;
        padding: 14px 16px;
        font-size: 0.88rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        resize: none;
        outline: none;
        transition: all 0.2s;
        min-height: 110px;
    }

    .kegiatan-input::placeholder {
        color: #334155;
    }

    .kegiatan-input:focus {
        border-color: var(--accent);
        background: rgba(0, 212, 255, 0.04);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    }

    /* TOMBOL KIRIM */
    .btn-kirim {
        width: 100%;
        background: linear-gradient(135deg, #4f46e5, #00d4ff);
        border: none;
        border-radius: 14px;
        color: white;
        font-weight: 800;
        font-size: 0.9rem;
        letter-spacing: 1.5px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.25s;
        margin-top: 16px;
        box-shadow: 0 6px 24px rgba(79, 70, 229, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-kirim:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 212, 255, 0.35);
        filter: brightness(1.08);
    }

    .btn-kirim:active {
        transform: translateY(0);
    }

    /* TIMER */
    .timer-box {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.2);
        border-radius: 20px;
        padding: 28px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .timer-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 50% 0%, rgba(239, 68, 68, 0.08), transparent 70%);
    }

    .timer-icon-wrap {
        width: 64px;
        height: 64px;
        background: rgba(239, 68, 68, 0.12);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 1.8rem;
        animation: spin-slow 3s linear infinite;
    }

    @keyframes spin-slow {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .timer-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 2px;
        color: #f87171;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    #countdown-val {
        font-size: 3.5rem;
        font-weight: 800;
        color: #f87171;
        line-height: 1;
        margin-bottom: 6px;
        font-variant-numeric: tabular-nums;
    }

    .timer-sub {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }

    /* Progress bar timer */
    .timer-progress {
        height: 4px;
        background: rgba(239, 68, 68, 0.15);
        border-radius: 10px;
        margin-top: 16px;
        overflow: hidden;
    }

    .timer-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #f87171, #ef4444);
        border-radius: 10px;
        transition: width 1s linear;
    }

    /* LOGOUT */
    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        color: #334155;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.8rem;
        letter-spacing: 1px;
        margin-top: 20px;
        padding: 10px;
        border-radius: 10px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .logout-btn:hover {
        color: #f87171;
        background: rgba(248, 113, 113, 0.08);
        border-color: rgba(248, 113, 113, 0.2);
    }

    /* FOOTER */
    .page-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.2);
        font-weight: 600;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }
    </style>
</head>

<body>

    <!-- PARTICLES -->
    <div class="particles" id="particles"></div>

    <div style="position:relative; z-index:1; width:100%; display:flex; flex-direction:column; align-items:center;">
        <div class="main-card">

            <!-- PROFILE HEADER -->
            <div class="profile-header">
                <div class="avatar-ring" style="position:relative; display:inline-flex;">
                    <?= $inisial ?>
                    <div class="online-dot"></div>
                </div>
                <div class="greeting-text">👋 <?= $salam ?></div>
                <div class="nama-text"><?= strtoupper($nama_user) ?></div>
                <span class="role-badge">
                    <i class="bi bi-mortarboard-fill"></i>
                    Mahasiswa Magang Lab Timur
                </span>
            </div>

            <!-- INFO BAR -->
            <div class="info-bar">
                <div class="info-item">
                    <i class="bi bi-calendar3"></i>
                    <strong><?= date('d M Y') ?></strong>
                </div>
                <div class="info-item">
                    <i class="bi bi-clock-fill"></i>
                    <strong id="live-clock"><?= date('H:i:s') ?></strong>
                </div>
                <div class="info-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <strong>Lab Timur</strong>
                </div>
            </div>

            <!-- BODY -->
            <div class="card-body-custom">

                <?php if ($sisa_detik > 0): ?>
                <!-- TIMER -->
                <div class="timer-box">
                    <div class="timer-icon-wrap">⏳</div>
                    <div class="timer-label">Harap Tunggu</div>
                    <div id="countdown-val"><?= $sisa_detik ?></div>
                    <div class="timer-sub">detik sebelum absen berikutnya</div>
                    <div class="timer-progress">
                        <div class="timer-progress-bar" id="progressBar" style="width:<?= ($sisa_detik/2)*100 ?>%">
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- FORM ABSEN -->
                <form action="simpan_absen.php" method="POST">
                    <input type="hidden" name="nama" value="<?= $nama_user ?>">

                    <div class="field-label">
                        <i class="bi bi-journal-text"></i>
                        Laporan Kegiatan
                    </div>
                    <textarea name="kegiatan" class="kegiatan-input" rows="4" required
                        placeholder="Ceritakan kegiatan kamu hari ini..."></textarea>

                    <button type="submit" name="kirim" class="btn-kirim">
                        <i class="bi bi-send-check-fill" style="font-size:1.1rem;"></i>
                        KIRIM ABSENSI
                    </button>
                </form>
                <?php endif; ?>

                <!-- LOGOUT -->
                <a href="logout.php" class="logout-btn">
                    <i class="bi bi-power"></i>
                    KELUAR DARI SISTEM
                </a>
            </div>
        </div>

        <div class="page-footer">✦ 2026 Lab Timur Digital ✦</div>
    </div>

    <script>
    // ===== LIVE CLOCK =====
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = h + ':' + m + ':' + s;
    }
    setInterval(updateClock, 1000);

    // ===== COUNTDOWN (2 DETIK) =====
    let timeLeft = <?= $sisa_detik ?>;
    const totalTime = 2;
    if (timeLeft > 0) {
        const countEl = document.getElementById('countdown-val');
        const barEl = document.getElementById('progressBar');
        const interval = setInterval(() => {
            timeLeft--;
            if (countEl) countEl.textContent = timeLeft;
            if (barEl) barEl.style.width = ((timeLeft / totalTime) * 100) + '%';
            if (timeLeft <= 0) {
                clearInterval(interval);
                window.location.reload();
            }
        }, 1000);
    }

    // ===== PARTICLES =====
    const container = document.getElementById('particles');
    const colors = ['#6366f1', '#a855f7', '#00d4ff', '#ffffff'];
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 5 + 2;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            left:${Math.random()*100}%;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            animation-duration:${Math.random()*12+8}s;
            animation-delay:${Math.random()*8}s;
        `;
        container.appendChild(p);
    }
    </script>
</body>

</html>