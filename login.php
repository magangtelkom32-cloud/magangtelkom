<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    * {
        font-family: 'Plus Jakarta Sans', sans-serif;
        box-sizing: border-box;
    }

    /* VIDEO BG */
    .video-bg {
        position: fixed;
        right: 0;
        bottom: 0;
        min-width: 100%;
        min-height: 100%;
        z-index: -1;
        object-fit: cover;
    }

    /* OVERLAY GRADIENT */
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(2, 6, 23, 0.82) 0%, rgba(15, 23, 42, 0.75) 100%);
        z-index: 0;
    }

    body {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        overflow: hidden;
    }

    /* CARD */
    .login-card {
        width: 420px;
        padding: 44px 40px;
        border-radius: 24px;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(0, 212, 255, 0.18);
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.04);
        z-index: 1;
        animation: fadeUp 0.5s ease;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(24px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* LOGO / ICON ATAS */
    .login-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #00d4ff, #4f46e5);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        box-shadow: 0 8px 24px rgba(0, 212, 255, 0.3);
        font-size: 1.6rem;
        color: white;
    }

    .login-title {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: 2px;
        margin-bottom: 4px;
    }

    .login-sub {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 500;
    }

    /* INPUT */
    .input-group-custom {
        position: relative;
        margin-bottom: 18px;
    }

    .input-label {
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 7px;
        display: block;
    }

    .input-wrap {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #475569;
        font-size: 1rem;
        pointer-events: none;
        transition: color 0.2s;
    }

    .form-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        padding: 12px 44px 12px 42px;
        font-size: 0.9rem;
        font-weight: 500;
        outline: none;
        transition: all 0.2s;
    }

    .form-input::placeholder {
        color: #334155;
    }

    .form-input:focus {
        border-color: #00d4ff;
        background: rgba(0, 212, 255, 0.05);
        box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.12);
    }

    .form-input:focus+.input-icon,
    .input-wrap:focus-within .input-icon {
        color: #00d4ff;
    }

    /* Toggle password */
    .toggle-pass {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #475569;
        cursor: pointer;
        font-size: 1rem;
        transition: color 0.2s;
        z-index: 2;
    }

    .toggle-pass:hover {
        color: #00d4ff;
    }

    /* BUTTON */
    .btn-login {
        width: 100%;
        background: linear-gradient(135deg, #4f46e5, #00d4ff);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-weight: 800;
        font-size: 0.88rem;
        letter-spacing: 1.5px;
        padding: 13px;
        cursor: pointer;
        transition: all 0.25s;
        margin-top: 8px;
        box-shadow: 0 6px 24px rgba(79, 70, 229, 0.35);
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 212, 255, 0.35);
        filter: brightness(1.08);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    /* ALERT */
    .alert-custom {
        border-radius: 10px;
        font-size: 0.8rem;
        padding: 10px 14px;
        margin-bottom: 18px;
        font-weight: 600;
        text-align: center;
    }

    .alert-danger-custom {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #f87171;
    }

    .alert-warning-custom {
        background: rgba(234, 179, 8, 0.12);
        border: 1px solid rgba(234, 179, 8, 0.3);
        color: #fbbf24;
    }

    /* DIVIDER / FOOTER */
    .login-footer {
        margin-top: 20px;
        text-align: center;
        color: #1e293b;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .accent-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #00d4ff;
        border-radius: 50%;
        margin: 0 6px;
        vertical-align: middle;
    }
    </style>
</head>

<body>

    <!-- VIDEO BACKGROUND -->
    <video autoplay muted loop class="video-bg">
        <source src="bg.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <!-- LOGIN CARD -->
    <div class="login-card">

        <!-- ICON & JUDUL -->
        <div class="text-center mb-4">
            <div class="login-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div class="login-title">LOG IN</div>
            <div class="login-sub">Silakan masuk ke sistem absensi</div>
        </div>

        <!-- ALERT -->
        <?php 
        if(isset($_GET['pesan'])){
            if($_GET['pesan'] == "gagal"){
                echo "<div class='alert-custom alert-danger-custom'><i class='bi bi-x-circle-fill me-1'></i>Login Gagal! Akun tidak ditemukan.</div>";
            } else if($_GET['pesan'] == "belum_login"){
                echo "<div class='alert-custom alert-warning-custom'><i class='bi bi-exclamation-triangle-fill me-1'></i>Anda harus login dulu!</div>";
            }
        }
        ?>

        <!-- FORM - autocomplete off agar tidak simpan otomatis -->
        <form action="cek_login.php" method="POST" autocomplete="off">

            <!-- USERNAME -->
            <div class="input-group-custom">
                <label class="input-label">Username</label>
                <div class="input-wrap">
                    <input type="text" name="username" class="form-input" placeholder="Masukkan username"
                        autocomplete="off" required>
                    <i class="bi bi-person-fill input-icon"></i>
                </div>
            </div>

            <!-- PASSWORD -->
            <div class="input-group-custom">
                <label class="input-label">Password</label>
                <div class="input-wrap">
                    <input type="password" name="password" id="passwordInput" class="form-input"
                        placeholder="Masukkan password" autocomplete="new-password" required>
                    <i class="bi bi-lock-fill input-icon"></i>
                    <i class="bi bi-eye-slash toggle-pass" id="togglePass" onclick="togglePassword()"></i>
                </div>
            </div>

            <!-- TOMBOL -->
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>MASUK SEKARANG
            </button>

        </form>

        <!-- FOOTER -->
        <div class="login-footer">
            <span class="accent-dot"></span>
            Sistem Absensi Lab Timur
            <span class="accent-dot"></span>
        </div>

    </div>

    <script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('togglePass');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    }
    </script>

</body>

</html>