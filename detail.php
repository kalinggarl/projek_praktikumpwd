<?php
session_start();
include 'konek.php';

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q  = mysqli_query($conn, "SELECT * FROM produk WHERE id = $id");
$p  = mysqli_fetch_assoc($q);
if (!$p) { header("Location: index.php"); exit; }

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['nama']) ?> - Crut</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'Jost',sans-serif;
            background-image:url('8.png');
            background-size:cover; background-position:center; background-attachment:fixed;
            min-height:100vh;
        }
        body::before { content:''; position:fixed; inset:0; background:rgba(8,6,3,0.76); z-index:0; }
        .navbar {
            background:rgba(0,0,0,0.65); backdrop-filter:blur(10px);
            border-bottom:1px solid rgba(255,255,255,0.08);
            position:relative; z-index:10;
        }
        .navbar-brand { font-family:'Libre Baskerville',serif; font-size:24px; letter-spacing:2px; color:#fff !important; }
        .nav-link { color:#ccc !important; transition:color .2s; }
        .nav-link:hover { color:#fff !important; }
        .user-greet { color:#e6c097 !important; font-size:13px; }
        .nav-logout { border:1px solid rgba(255,255,255,0.35); padding:5px 15px !important; border-radius:20px; }
        .nav-logout:hover { background:#fff; color:#000 !important; }

        .detail-wrapper { position:relative; z-index:1; padding:100px 0 60px; }
        .back-link {
            display:inline-flex; align-items:center; gap:6px;
            color:rgba(255,255,255,0.4); font-size:13px; letter-spacing:.08em;
            text-decoration:none; margin-bottom:26px; transition:color .2s;
        }
        .back-link:hover { color:#e6c097; }

        .detail-card {
            background:rgba(255,255,255,0.04); backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.11); border-radius:24px;
            overflow:hidden; display:grid; grid-template-columns:1fr 1fr; min-height:520px;
        }
        .img-side {
            background:rgba(255,255,255,0.02);
            border-right:1px solid rgba(255,255,255,0.08);
            display:flex; align-items:center; justify-content:center; padding:48px 32px;
        }
        .img-side img {
            max-width:100%; max-height:400px; object-fit:contain;
            filter:drop-shadow(0 20px 40px rgba(0,0,0,0.5)); transition:transform .4s;
        }
        .img-side img:hover { transform:scale(1.04); }
        .info-side { padding:48px 44px; display:flex; flex-direction:column; justify-content:center; color:#fff; }
        .badge-kat {
            display:inline-block; border:1px solid rgba(230,192,151,0.4); color:#e6c097;
            font-size:10px; letter-spacing:.18em; text-transform:uppercase;
            padding:5px 14px; border-radius:20px; margin-bottom:16px; width:fit-content;
        }
        .prod-nama { font-family:'Libre Baskerville',serif; font-size:clamp(20px,3vw,30px); font-weight:400; color:#f0e8d8; margin-bottom:12px; line-height:1.3; }
        .prod-harga { font-size:24px; font-weight:500; color:#e6c097; margin-bottom:20px; }
        .divider { border:none; border-top:1px solid rgba(255,255,255,0.09); margin:0 0 20px; }
        .sec-label { font-size:10px; letter-spacing:.2em; text-transform:uppercase; color:rgba(255,255,255,0.35); margin-bottom:8px; }
        .notes-text { font-size:13px; color:rgba(255,255,255,0.68); line-height:1.8; margin-bottom:24px; }

        .qty-wrap { display:flex; align-items:center; margin-bottom:26px; width:fit-content; }
        .qty-btn {
            width:36px; height:36px; background:transparent;
            border:1px solid rgba(255,255,255,0.2); color:#fff; font-size:17px;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:border-color .2s, color .2s;
        }
        .qty-btn:first-child { border-radius:8px 0 0 8px; }
        .qty-btn:last-child  { border-radius:0 8px 8px 0; }
        .qty-btn:hover { border-color:#e6c097; color:#e6c097; }
        .qty-num {
            width:50px; height:36px; background:transparent;
            border:1px solid rgba(255,255,255,0.2); border-left:none; border-right:none;
            color:#fff; font-size:14px; text-align:center; outline:none;
        }
        .btn-beli {
            width:100%; padding:14px; background:transparent; color:#e6c097;
            font-family:'Jost',sans-serif; font-weight:500; font-size:12px;
            letter-spacing:.18em; text-transform:uppercase;
            border:1px solid #e6c097; border-radius:12px; cursor:pointer; transition:all .25s;
        }
        .btn-beli:hover { background:#e6c097; color:#1a1610; transform:translateY(-2px); }

        @media(max-width:768px){
            .detail-card { grid-template-columns:1fr; }
            .img-side { border-right:none; border-bottom:1px solid rgba(255,255,255,0.08); padding:28px; }
            .info-side { padding:28px 20px; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container position-relative">
        <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="index.php">Crut</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#collection">Shop</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><span class="nav-link user-greet">Hi, <?= htmlspecialchars($user['nama']) ?></span></li>
                <li class="nav-item"><a class="nav-link nav-logout" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="detail-wrapper">
    <div class="container">
        <a href="index.php#collection" class="back-link">← Kembali ke Koleksi</a>
        <div class="detail-card">
            <div class="img-side">
                <img src="<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>">
            </div>
            <div class="info-side">
                <span class="badge-kat"><?= htmlspecialchars($p['kategori']) ?></span>
                <h1 class="prod-nama"><?= htmlspecialchars($p['nama']) ?></h1>
                <div class="prod-harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                <hr class="divider">
                <p class="sec-label">Fragrance Notes</p>
                <p class="notes-text"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
                <p class="sec-label">Jumlah</p>
                <div class="qty-wrap">
                    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                    <input class="qty-num" type="number" id="qty" value="1" min="1" max="99" readonly>
                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                </div>
                <!-- Ke checkout, bukan langsung sukses -->
                <form method="POST" action="checkout.php">
                    <input type="hidden" name="produk_id"       value="<?= $p['id'] ?>">
                    <input type="hidden" name="produk_nama"     value="<?= htmlspecialchars($p['nama']) ?>">
                    <input type="hidden" name="produk_harga"    value="<?= $p['harga'] ?>">
                    <input type="hidden" name="produk_gambar"   value="<?= htmlspecialchars($p['gambar']) ?>">
                    <input type="hidden" name="produk_kategori" value="<?= htmlspecialchars($p['kategori']) ?>">
                    <input type="hidden" name="qty" id="qtyForm" value="1">
                    <button type="submit" class="btn-beli">Beli Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function changeQty(d) {
        const el = document.getElementById('qty');
        let v = parseInt(el.value) + d;
        if (v < 1) v = 1; if (v > 99) v = 99;
        el.value = v;
        document.getElementById('qtyForm').value = v;
    }
</script>
</body>
</html>