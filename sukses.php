<?php
session_start();
include 'konek.php';

if (!isset($_SESSION['user']))             { header("Location: login.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: index.php");  exit; }

$user      = $_SESSION['user'];
$nama      = htmlspecialchars($_POST['produk_nama']);
$harga     = (int)$_POST['produk_harga'];
$gambar    = htmlspecialchars($_POST['produk_gambar']);
$kategori  = htmlspecialchars($_POST['produk_kategori']);
$qty       = max(1, (int)$_POST['qty']);
$ongkir    = (int)$_POST['ongkir'];
$total     = (int)$_POST['total_bayar'];

// Data form checkout
$nama_penerima = htmlspecialchars($_POST['nama_penerima']);
$telepon       = htmlspecialchars($_POST['telepon']);
$alamat        = htmlspecialchars($_POST['alamat']);
$kota          = htmlspecialchars($_POST['kota']);
$provinsi      = htmlspecialchars($_POST['provinsi']);
$kode_pos      = htmlspecialchars($_POST['kode_pos']);
$kurir         = htmlspecialchars($_POST['kurir']);
$pembayaran    = htmlspecialchars($_POST['pembayaran']);
$catatan       = htmlspecialchars($_POST['catatan'] ?? '-');

$order_id = 'CRT-' . strtoupper(substr(md5(uniqid()), 0, 8));
$tanggal  = date('d F Y, H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembelian Berhasil - Crut Parfumes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'Jost',sans-serif;
            background-image:url('8.png');
            background-size:cover; background-position:center; background-attachment:fixed;
            min-height:100vh; display:flex; flex-direction:column;
        }
        body::before { content:''; position:fixed; inset:0; background:rgba(8,6,3,0.78); z-index:0; }

        .navbar {
            background:rgba(0,0,0,0.6); backdrop-filter:blur(10px);
            border-bottom:1px solid rgba(255,255,255,0.08); position:relative; z-index:10;
        }
        .navbar-brand { font-family:'Libre Baskerville',serif; font-size:24px; letter-spacing:2px; color:#fff !important; }
        .nav-link { color:#ccc !important; }
        .nav-link:hover { color:#fff !important; }
        .nav-logout { border:1px solid rgba(255,255,255,0.3); padding:5px 15px !important; border-radius:20px; }
        .nav-logout:hover { background:#fff; color:#000 !important; }

        .page-wrapper { position:relative; z-index:1; flex:1; padding:80px 0 60px; }

        /* STEP */
        .steps { display:flex; align-items:center; gap:0; margin-bottom:32px; }
        .step { display:flex; align-items:center; gap:8px; font-size:11px; letter-spacing:.12em; text-transform:uppercase; }
        .step-num { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; }
        .step.done .step-num  { background:#e6c097; color:#1a1610; }
        .step.done .step-txt  { color:#e6c097; }
        .step.active .step-num { border:1px solid #e6c097; color:#e6c097; }
        .step.active .step-txt { color:#e6c097; }
        .step-line { flex:1; height:1px; background:rgba(255,255,255,0.12); margin:0 12px; max-width:60px; }

        /* CHECK */
        .check-wrap { text-align:center; margin-bottom:28px; }
        .check-circle {
            width:68px; height:68px; border-radius:50%; border:1px solid #e6c097;
            display:inline-flex; align-items:center; justify-content:center;
            margin-bottom:14px; animation:popIn .5s cubic-bezier(.175,.885,.32,1.275) both;
        }
        @keyframes popIn { from{opacity:0;transform:scale(.5)} to{opacity:1;transform:scale(1)} }
        .check-circle svg {
            width:26px; height:26px; stroke:#e6c097; stroke-width:2;
            stroke-linecap:round; stroke-linejoin:round; fill:none;
            stroke-dasharray:50; stroke-dashoffset:50;
            animation:drawCheck .5s .4s ease forwards;
        }
        @keyframes drawCheck { to { stroke-dashoffset:0; } }
        .check-title { font-family:'Libre Baskerville',serif; font-size:24px; font-weight:400; color:#f0e8d8; margin-bottom:6px; }
        .check-sub   { font-size:13px; color:rgba(255,255,255,0.4); }

        /* GRID */
        .sukses-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:28px; }

        /* CARD */
        .s-card {
            background:rgba(255,255,255,0.04); backdrop-filter:blur(18px);
            border:1px solid rgba(230,192,151,0.15); border-radius:18px; overflow:hidden;
            animation:fadeUp .5s .2s ease both;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .s-head {
            padding:15px 22px; border-bottom:1px solid rgba(255,255,255,0.07);
            font-family:'Libre Baskerville',serif; font-size:13px; font-weight:400;
            color:#e6c097; display:flex; align-items:center; gap:8px;
        }

        /* ORDER HEADER */
        .order-meta { padding:16px 22px; border-bottom:1px solid rgba(255,255,255,0.07); display:flex; justify-content:space-between; }
        .om-lbl { font-size:10px; letter-spacing:.18em; text-transform:uppercase; color:rgba(255,255,255,0.28); margin-bottom:3px; }
        .om-val { font-size:13px; color:#e6c097; }
        .om-date { font-size:12px; color:rgba(255,255,255,0.42); }

        /* PRODUK */
        .prod-row { padding:16px 22px; display:flex; align-items:center; gap:14px; }
        .prod-row img { width:54px; height:68px; object-fit:contain; filter:drop-shadow(0 4px 10px rgba(0,0,0,0.4)); flex-shrink:0; }
        .pi { flex:1; }
        .pi-kat  { font-size:10px; letter-spacing:.12em; text-transform:uppercase; color:rgba(230,192,151,0.5); margin-bottom:3px; }
        .pi-nama { font-family:'Libre Baskerville',serif; font-size:14px; color:#f0e8d8; margin-bottom:3px; }
        .pi-qty  { font-size:12px; color:rgba(255,255,255,0.35); }
        .pi-price { font-size:15px; font-weight:500; color:#e6c097; flex-shrink:0; }

        /* INFO ROWS */
        .info-rows { padding:8px 22px; }
        .ir { display:flex; justify-content:space-between; align-items:flex-start; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.05); gap:12px; }
        .ir:last-child { border-bottom:none; }
        .ir-l { font-size:11px; color:rgba(255,255,255,0.32); min-width:110px; flex-shrink:0; }
        .ir-v { font-size:12px; color:rgba(255,255,255,0.72); text-align:right; }
        .ir-ok { font-size:12px; color:#a8d5a2; text-align:right; }
        .ir-gold { font-size:12px; color:#e6c097; text-align:right; }

        /* TOTAL ROW */
        .total-row {
            padding:14px 22px; border-top:1px solid rgba(255,255,255,0.08);
            display:flex; justify-content:space-between; align-items:center;
        }
        .tl-l { font-size:10px; letter-spacing:.16em; text-transform:uppercase; color:rgba(255,255,255,0.3); }
        .tl-v { font-family:'Libre Baskerville',serif; font-size:20px; color:#e6c097; }

        /* PAYMENT NOTICE */
        .pay-notice {
            margin:0 22px 18px; padding:13px 16px;
            background:rgba(230,192,151,0.06); border:1px solid rgba(230,192,151,0.2);
            border-radius:10px; font-size:12px; color:rgba(255,255,255,0.6); line-height:1.8;
        }
        .pay-notice strong { color:#e6c097; }

        /* BUTTONS */
        .btn-row { display:flex; gap:12px; margin-top:24px; }
        .btn-gold {
            flex:1; padding:13px; background:transparent; color:#e6c097;
            font-family:'Jost',sans-serif; font-size:11px; letter-spacing:.16em; text-transform:uppercase;
            border:1px solid #e6c097; border-radius:12px; text-decoration:none; text-align:center; transition:all .25s;
        }
        .btn-gold:hover { background:#e6c097; color:#1a1610; }
        .btn-ghost {
            flex:1; padding:13px; background:transparent; color:rgba(255,255,255,0.38);
            font-family:'Jost',sans-serif; font-size:11px; letter-spacing:.16em; text-transform:uppercase;
            border:1px solid rgba(255,255,255,0.14); border-radius:12px; text-decoration:none; text-align:center; transition:all .25s;
        }
        .btn-ghost:hover { border-color:rgba(255,255,255,0.4); color:#fff; }

        @media(max-width:768px){
            .sukses-grid { grid-template-columns:1fr; }
            .btn-row { flex-direction:column; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container position-relative">
        <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="index.php">Crut</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link" style="color:#e6c097!important;font-size:13px;">
                        Hi, <?= htmlspecialchars($user['nama']) ?>
                    </span>
                </li>
                <li class="nav-item"><a class="nav-link nav-logout" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="page-wrapper">
    <div class="container">

        <!-- STEP -->
        <div class="steps">
            <div class="step done"><div class="step-num">✓</div><div class="step-txt">Detail Produk</div></div>
            <div class="step-line"></div>
            <div class="step done"><div class="step-num">✓</div><div class="step-txt">Checkout</div></div>
            <div class="step-line"></div>
            <div class="step active"><div class="step-num">3</div><div class="step-txt">Konfirmasi</div></div>
        </div>

        <!-- CHECK ANIMATION -->
        <div class="check-wrap">
            <div class="check-circle">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h1 class="check-title">Pesanan Dikonfirmasi!</h1>
            <p class="check-sub">Terima kasih, <?= htmlspecialchars($user['nama']) ?>. Pesanan kamu sedang kami proses.</p>
        </div>

        <!-- GRID DETAIL -->
        <div class="sukses-grid">

            <!-- KIRI: DETAIL PESANAN -->
            <div class="s-card">
                <div class="s-head">📦 Detail Pesanan</div>
                <div class="order-meta">
                    <div>
                        <div class="om-lbl">Order ID</div>
                        <div class="om-val"><?= $order_id ?></div>
                    </div>
                    <div style="text-align:right">
                        <div class="om-lbl">Tanggal</div>
                        <div class="om-date"><?= $tanggal ?></div>
                    </div>
                </div>
                <div class="prod-row">
                    <img src="<?= $gambar ?>" alt="<?= $nama ?>">
                    <div class="pi">
                        <p class="pi-kat"><?= $kategori ?></p>
                        <p class="pi-nama"><?= $nama ?></p>
                        <p class="pi-qty"><?= $qty ?> botol</p>
                    </div>
                    <div class="pi-price">Rp <?= number_format($harga, 0, ',', '.') ?></div>
                </div>
                <div class="info-rows">
                    <div class="ir"><span class="ir-l">Subtotal</span><span class="ir-v">Rp <?= number_format($harga * $qty, 0, ',', '.') ?></span></div>
                    <div class="ir"><span class="ir-l">Ongkos Kirim</span><span class="ir-v">Rp <?= number_format($ongkir, 0, ',', '.') ?></span></div>
                    <div class="ir"><span class="ir-l">Kurir</span><span class="ir-v"><?= $kurir ?></span></div>
                    <div class="ir"><span class="ir-l">Pembayaran</span><span class="ir-gold"><?= $pembayaran ?></span></div>
                    <div class="ir"><span class="ir-l">Status</span><span class="ir-ok">● Menunggu Pembayaran</span></div>
                </div>
                <div class="total-row">
                    <span class="tl-l">Total Pembayaran</span>
                    <span class="tl-v">Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>

                <?php if (in_array($pembayaran, ['Transfer BCA','Transfer Mandiri','Transfer BRI'])): ?>
                <div class="pay-notice">
                    Silakan transfer ke rekening:<br>
                    <strong>BCA</strong> → 1234567890 &nbsp;|&nbsp;
                    <strong>Mandiri</strong> → 0987654321 &nbsp;|&nbsp;
                    <strong>BRI</strong> → 1122334455<br>
                    a.n. <strong>Crut Parfumes</strong><br>
                    <span style="color:rgba(255,255,255,0.4)">Transfer tepat <strong style="color:#e6c097">Rp <?= number_format($total, 0, ',', '.') ?></strong> dalam 24 jam.</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- KANAN: INFORMASI PENGIRIMAN -->
            <div class="s-card">
                <div class="s-head">🏠 Informasi Pengiriman</div>
                <div class="info-rows" style="padding-top:16px;">
                    <div class="ir"><span class="ir-l">Penerima</span><span class="ir-v"><?= $nama_penerima ?></span></div>
                    <div class="ir"><span class="ir-l">No. Telepon</span><span class="ir-v"><?= $telepon ?></span></div>
                    <div class="ir"><span class="ir-l">Email</span><span class="ir-v"><?= htmlspecialchars($user['email']) ?></span></div>
                    <div class="ir"><span class="ir-l">Alamat</span><span class="ir-v"><?= $alamat ?></span></div>
                    <div class="ir"><span class="ir-l">Kota</span><span class="ir-v"><?= $kota ?></span></div>
                    <div class="ir"><span class="ir-l">Provinsi</span><span class="ir-v"><?= $provinsi ?></span></div>
                    <div class="ir"><span class="ir-l">Kode Pos</span><span class="ir-v"><?= $kode_pos ?></span></div>
                    <div class="ir"><span class="ir-l">Catatan</span><span class="ir-v"><?= $catatan ?: '-' ?></span></div>
                </div>
            </div>

        </div>

        <!-- BUTTONS -->
        <div class="btn-row">
            <a href="index.php" class="btn-gold">Kembali ke Beranda</a>
            <a href="index.php#collection" class="btn-ghost">Belanja Lagi</a>
        </div>

    </div>
</div>

</body>
</html>