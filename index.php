<?php
session_start();
include 'konek.php';

$isLoggedIn = isset($_SESSION['user']);
$result = mysqli_query($conn, "SELECT * FROM produk");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crut Parfumes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="o.css">
    <style>
        .guard-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.72);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .guard-overlay.active { display: flex; }
        .guard-box {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 0 40px rgba(230,192,151,0.12);
            border-radius: 20px;
            width: 340px;
            padding: 44px 36px;
            text-align: center;
            color: #fff;
            animation: popUp .3s ease;
        }
        @keyframes popUp {
            from { opacity:0; transform: scale(.93) translateY(14px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }
        .guard-icon { font-size: 36px; margin-bottom: 14px; }
        .guard-box h4 {
            font-family: 'Libre Baskerville', serif;
            font-size: 19px; font-weight: 400;
            color: #e6c097; margin-bottom: 10px;
        }
        .guard-box p {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 26px;
            line-height: 1.7;
        }
        .guard-btn-login {
            display: block; width: 100%; padding: 13px;
            background: transparent; color: #e6c097;
            font-size: 12px; letter-spacing: .16em; text-transform: uppercase;
            border: 1px solid #e6c097; border-radius: 10px;
            text-decoration: none; margin-bottom: 11px;
            transition: all .25s;
        }
        .guard-btn-login:hover { background: #e6c097; color: #1a1610; }
        .guard-btn-cancel {
            background: none; border: none;
            color: rgba(255,255,255,0.3); font-size: 12px;
            cursor: pointer; text-decoration: underline;
        }
        .guard-btn-cancel:hover { color: rgba(255,255,255,0.6); }

        .parfum-card {
            cursor: pointer;
            transition: transform .2s, box-shadow .25s;
            position: relative;
        }
        .parfum-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(184,151,74,0.2);
        }
        .lock-badge {
            position: absolute; top: 10px; right: 10px;
            background: rgba(0,0,0,0.5); color: #e6c097;
            font-size: 13px; width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity .2s;
        }
        <?php if (!$isLoggedIn): ?>
        .parfum-card:hover .lock-badge { opacity: 1; }
        <?php endif; ?>
    </style>
</head>
<body>

<!-- GUARD MODAL -->
<div class="guard-overlay" id="guardModal">
    <div class="guard-box">
        <div class="guard-icon">🔒</div>
        <h4>Login Diperlukan</h4>
        <p>Kamu harus login terlebih dahulu untuk melihat detail produk dan melakukan pembelian.</p>
        <a href="login.php" class="guard-btn-login">Login Sekarang</a>
        <button class="guard-btn-cancel" onclick="closeGuard()">Kembali</button>
    </div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container position-relative">
        <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="#">  CRUT </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#collection">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="#"></a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <span class="nav-link" style="color:#e6c097 !important; font-size:13px;">
                            Hi, <?= htmlspecialchars($_SESSION['user']['nama']) ?>
                        </span>
                    </li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php" style="color:#e6c097 !important;">Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link login-btn" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link login-btn" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="tengahgede">
    <div class="tengahgede-content">
        <h1>CRUT PARFUMES</h1>
        <a href="<?= $isLoggedIn ? '#collection' : 'login.php' ?>" class="btn-discover">
            Discover our Fragrances
        </a>
    </div>
</section>

<!-- COLLECTION -->
<section class="collection" id="collection">
    <p class="subtitle">OUR COLLECTION</p>
    <div class="parfum-carousel">
        <?php while ($p = mysqli_fetch_assoc($result)): ?>
        <div class="parfum-card" onclick="handleCardClick(<?= $p['id'] ?>)">
            <span class="lock-badge">🔒</span>
            <img class="parfum-img" src="<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>">
            <p class="parfum-name"><?= htmlspecialchars($p['nama']) ?></p>
            <p class="parfum-sub"><?= htmlspecialchars($p['kategori']) ?></p>
        </div>
        <?php endwhile; ?>
    </div>
    <p class="hint">← geser →</p>
    <h2>Myths Reborn Through Scent</h2>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    function handleCardClick(id) {
        if (!isLoggedIn) {
            document.getElementById('guardModal').classList.add('active');
        } else {
            window.location.href = 'detail.php?id=' + id;
        }
    }
    function closeGuard() {
        document.getElementById('guardModal').classList.remove('active');
    }
    document.getElementById('guardModal').addEventListener('click', function(e) {
        if (e.target === this) closeGuard();
    });
</script>
</body>
</html>