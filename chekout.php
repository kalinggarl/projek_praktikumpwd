<?php
session_start();
include 'konek.php';

if (!isset($_SESSION['user']))             { header("Location: login.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: index.php");  exit; }

$user = $_SESSION['user'];

// Ongkir map (PHP murni, tidak ada JS)
$ongkir_map = [
    'JNE Reguler' => 15000,
    'JNE YES'     => 25000,
    'J&T Express' => 14000,
    'SiCepat'     => 13000,
    'Gosend'      => 20000,
];

// Data produk — bisa dari detail.php (pertama kali) atau dari reload checkout
$p = [
    'nama'     => htmlspecialchars($_POST['produk_nama']),
    'harga'    => (int)$_POST['produk_harga'],
    'gambar'   => htmlspecialchars($_POST['produk_gambar']),
    'kategori' => htmlspecialchars($_POST['produk_kategori']),
    'qty'      => max(1, (int)$_POST['qty']),
];
$subtotal = $p['harga'] * $p['qty'];

// Nilai yang sudah dipilih user (tersimpan di POST saat reload)
$kurir_pilihan = $_POST['kurir_pilihan'] ?? 'JNE Reguler';
$bayar_pilihan = $_POST['pembayaran']   ?? 'Transfer BCA';
$ongkir        = $ongkir_map[$kurir_pilihan] ?? 15000;
$total         = $subtotal + $ongkir;

// Nilai form yang sudah diisi (supaya tidak hilang saat reload pilih kurir)
$nama_penerima = $_POST['nama_penerima'] ?? $user['nama'];
$telepon       = $_POST['telepon']       ?? '';
$alamat        = $_POST['alamat']        ?? '';
$kota          = $_POST['kota']          ?? '';
$provinsi      = $_POST['provinsi']      ?? '';
$kode_pos      = $_POST['kode_pos']      ?? '';
$catatan       = $_POST['catatan']       ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Crut Parfumes</title>
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
        body::before { content:''; position:fixed; inset:0; background:rgba(8,6,3,0.78); z-index:0; }

        .navbar {
            background:rgba(0,0,0,0.65); backdrop-filter:blur(10px);
            border-bottom:1px solid rgba(255,255,255,0.08); position:relative; z-index:10;
        }
        .navbar-brand { font-family:'Libre Baskerville',serif; font-size:24px; letter-spacing:2px; color:#fff !important; }
        .nav-link { color:#ccc !important; transition:color .2s; }
        .nav-link:hover { color:#fff !important; }
        .user-greet { color:#e6c097 !important; font-size:13px; }
        .nav-logout { border:1px solid rgba(255,255,255,0.35); padding:5px 15px !important; border-radius:20px; }
        .nav-logout:hover { background:#fff; color:#000 !important; }

        .checkout-wrapper { position:relative; z-index:1; padding:100px 0 60px; }
        .back-link {
            display:inline-flex; align-items:center; gap:6px;
            color:rgba(255,255,255,0.4); font-size:13px; text-decoration:none;
            margin-bottom:24px; transition:color .2s;
        }
        .back-link:hover { color:#e6c097; }
        .page-title { font-family:'Libre Baskerville',serif; font-size:22px; font-weight:400; color:#f0e8d8; margin-bottom:4px; }
        .page-sub { font-size:12px; color:rgba(255,255,255,0.35); margin-bottom:26px; }

        /* STEPS */
        .steps { display:flex; align-items:center; margin-bottom:30px; }
        .step { display:flex; align-items:center; gap:8px; font-size:11px; letter-spacing:.1em; text-transform:uppercase; }
        .step-num { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; flex-shrink:0; }
        .step.done .step-num  { background:#e6c097; color:#1a1610; }
        .step.done .step-txt  { color:#e6c097; }
        .step.active .step-num { border:1px solid #e6c097; color:#e6c097; }
        .step.active .step-txt { color:#e6c097; }
        .step.idle .step-num  { border:1px solid rgba(255,255,255,0.18); color:rgba(255,255,255,0.28); }
        .step.idle .step-txt  { color:rgba(255,255,255,0.28); }
        .step-line { width:48px; height:1px; background:rgba(255,255,255,0.12); margin:0 10px; }

        /* GRID */
        .checkout-grid { display:grid; grid-template-columns:1fr 330px; gap:20px; align-items:start; }

        /* CARD */
        .co-card {
            background:rgba(255,255,255,0.04); backdrop-filter:blur(18px);
            border:1px solid rgba(255,255,255,0.1); border-radius:18px;
            padding:22px 20px; margin-bottom:16px;
        }
        .co-card:last-child { margin-bottom:0; }
        .co-title {
            font-family:'Libre Baskerville',serif; font-size:14px; font-weight:400;
            color:#e6c097; margin-bottom:16px; display:flex; align-items:center; gap:8px;
        }

        /* FIELDS */
        .fg { margin-bottom:13px; }
        .fg:last-child { margin-bottom:0; }
        .fg label { display:block; font-size:10px; letter-spacing:.16em; text-transform:uppercase; color:rgba(255,255,255,0.3); margin-bottom:6px; }
        .fg input, .fg textarea {
            width:100%; padding:10px 13px;
            background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.11);
            border-radius:9px; color:#fff; font-family:'Jost',sans-serif; font-size:13px;
            outline:none; transition:border-color .2s;
        }
        .fg input:focus, .fg textarea:focus { border-color:rgba(230,192,151,0.45); }
        .fg input::placeholder, .fg textarea::placeholder { color:rgba(255,255,255,0.18); }
        .fg textarea { resize:vertical; min-height:68px; }
        .fg-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .req { color:#e6c097; }

        /* KURIR */
        .kurir-list { display:flex; flex-direction:column; gap:8px; }
        .kurir-opt input[type="radio"] { display:none; }
        .kurir-lbl {
            display:flex; align-items:center; justify-content:space-between;
            padding:12px 14px; border:1px solid rgba(255,255,255,0.11);
            border-radius:10px; cursor:pointer; transition:all .2s;
        }
        .kurir-lbl:hover { border-color:rgba(230,192,151,0.3); }
        .kurir-opt input[type="radio"]:checked + .kurir-lbl {
            border-color:#e6c097; background:rgba(230,192,151,0.07);
        }
        .kurir-left { display:flex; flex-direction:column; gap:2px; }
        .kurir-nm { font-size:13px; color:rgba(255,255,255,0.75); }
        .kurir-opt input[type="radio"]:checked + .kurir-lbl .kurir-nm { color:#e6c097; }
        .kurir-est { font-size:11px; color:rgba(255,255,255,0.32); }
        .kurir-rp { font-size:13px; color:#e6c097; font-weight:500; }

        /* TOMBOL UPDATE KURIR */
        .btn-update {
            width:100%; margin-top:12px; padding:10px;
            background:transparent; color:rgba(255,255,255,0.45);
            font-family:'Jost',sans-serif; font-size:10px; letter-spacing:.16em; text-transform:uppercase;
            border:1px solid rgba(255,255,255,0.13); border-radius:9px; cursor:pointer; transition:all .2s;
        }
        .btn-update:hover { border-color:rgba(230,192,151,0.4); color:#e6c097; }

        /* PEMBAYARAN */
        .pay-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .pay-opt input[type="radio"] { display:none; }
        .pay-lbl {
            display:flex; align-items:center; gap:8px;
            padding:10px 12px; border:1px solid rgba(255,255,255,0.11);
            border-radius:10px; cursor:pointer; font-size:12px;
            color:rgba(255,255,255,0.55); transition:all .2s;
        }
        .pay-lbl:hover { border-color:rgba(230,192,151,0.3); }
        .pay-opt input[type="radio"]:checked + .pay-lbl {
            border-color:#e6c097; background:rgba(230,192,151,0.07); color:#e6c097;
        }
        .pay-icon { font-size:17px; }

        /* INFO BANK */
        .bank-info {
            margin-top:12px; padding:12px 14px;
            background:rgba(230,192,151,0.05); border:1px solid rgba(230,192,151,0.15);
            border-radius:10px; font-size:12px; color:rgba(255,255,255,0.58); line-height:1.9;
        }
        .bank-info strong { color:#e6c097; }

        /* SUMMARY */
        .summary-card {
            background:rgba(255,255,255,0.04); backdrop-filter:blur(18px);
            border:1px solid rgba(230,192,151,0.16); border-radius:18px; overflow:hidden;
            position:sticky; top:90px;
        }
        .sum-head { padding:15px 20px; border-bottom:1px solid rgba(255,255,255,0.07); }
        .sum-title { font-family:'Libre Baskerville',serif; font-size:14px; color:#e6c097; }

        .sum-prod { padding:15px 20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid rgba(255,255,255,0.07); }
        .sum-prod img { width:50px; height:62px; object-fit:contain; filter:drop-shadow(0 4px 10px rgba(0,0,0,0.4)); flex-shrink:0; }
        .sp-info { flex:1; }
        .sp-kat  { font-size:10px; letter-spacing:.1em; text-transform:uppercase; color:rgba(230,192,151,0.5); margin-bottom:3px; }
        .sp-nama { font-family:'Libre Baskerville',serif; font-size:13px; color:#f0e8d8; margin-bottom:2px; }
        .sp-qty  { font-size:11px; color:rgba(255,255,255,0.32); }
        .sp-price { font-size:14px; font-weight:500; color:#e6c097; align-self:center; flex-shrink:0; }

        .sum-rows { padding:6px 20px; }
        .sr { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid rgba(255,255,255,0.05); }
        .sr:last-child { border-bottom:none; }
        .sr-l { font-size:12px; color:rgba(255,255,255,0.3); }
        .sr-v { font-size:12px; color:rgba(255,255,255,0.68); }
        .sr-gold { font-size:12px; color:#e6c097; }

        .sum-total { padding:13px 20px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; }
        .st-l { font-size:10px; letter-spacing:.14em; text-transform:uppercase; color:rgba(255,255,255,0.28); }
        .st-v { font-family:'Libre Baskerville',serif; font-size:20px; color:#e6c097; }

        .sum-btn { padding:14px 20px; }
        .btn-konfirmasi {
            width:100%; padding:13px; background:transparent; color:#e6c097;
            font-family:'Jost',sans-serif; font-size:11px; letter-spacing:.16em; text-transform:uppercase;
            border:1px solid #e6c097; border-radius:11px; cursor:pointer; transition:all .25s;
        }
        .btn-konfirmasi:hover { background:#e6c097; color:#1a1610; transform:translateY(-2px); }

        @media(max-width:900px){
            .checkout-grid { grid-template-columns:1fr; }
            .summary-card { position:static; }
            .fg-row { grid-template-columns:1fr; }
        }
        @media(max-width:480px){ .pay-grid { grid-template-columns:1fr; } }
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

<div class="checkout-wrapper">
    <div class="container">
        <a href="javascript:history.back()" class="back-link">← Kembali</a>
        <h1 class="page-title">Checkout</h1>
        <p class="page-sub">Lengkapi informasi pembelian kamu</p>

        <div class="steps">
            <div class="step done"><div class="step-num">✓</div><div class="step-txt">Detail Produk</div></div>
            <div class="step-line"></div>
            <div class="step active"><div class="step-num">2</div><div class="step-txt">Checkout</div></div>
            <div class="step-line"></div>
            <div class="step idle"><div class="step-num">3</div><div class="step-txt">Konfirmasi</div></div>
        </div>

        <!-- FORM PILIH KURIR (reload ke checkout.php untuk update ongkir) -->
        <form method="POST" action="checkout.php" style="display:none" id="formKurir">
            <input type="hidden" name="produk_nama"     value="<?= $p['nama'] ?>">
            <input type="hidden" name="produk_harga"    value="<?= $p['harga'] ?>">
            <input type="hidden" name="produk_gambar"   value="<?= $p['gambar'] ?>">
            <input type="hidden" name="produk_kategori" value="<?= $p['kategori'] ?>">
            <input type="hidden" name="qty"             value="<?= $p['qty'] ?>">
            <input type="hidden" name="nama_penerima"   value="<?= htmlspecialchars($nama_penerima) ?>">
            <input type="hidden" name="telepon"         value="<?= htmlspecialchars($telepon) ?>">
            <input type="hidden" name="alamat"          value="<?= htmlspecialchars($alamat) ?>">
            <input type="hidden" name="kota"            value="<?= htmlspecialchars($kota) ?>">
            <input type="hidden" name="provinsi"        value="<?= htmlspecialchars($provinsi) ?>">
            <input type="hidden" name="kode_pos"        value="<?= htmlspecialchars($kode_pos) ?>">
            <input type="hidden" name="catatan"         value="<?= htmlspecialchars($catatan) ?>">
            <input type="hidden" name="pembayaran"      value="<?= htmlspecialchars($bayar_pilihan) ?>">
            <input type="hidden" name="kurir_pilihan"   id="kurirHidden" value="<?= htmlspecialchars($kurir_pilihan) ?>">
        </form>

        <!-- FORM UTAMA KE SUKSES -->
        <form method="POST" action="sukses.php">
            <input type="hidden" name="produk_nama"     value="<?= $p['nama'] ?>">
            <input type="hidden" name="produk_harga"    value="<?= $p['harga'] ?>">
            <input type="hidden" name="produk_gambar"   value="<?= $p['gambar'] ?>">
            <input type="hidden" name="produk_kategori" value="<?= $p['kategori'] ?>">
            <input type="hidden" name="qty"             value="<?= $p['qty'] ?>">
            <input type="hidden" name="ongkir"          value="<?= $ongkir ?>">
            <input type="hidden" name="total_bayar"     value="<?= $total ?>">
            <input type="hidden" name="kurir"           value="<?= htmlspecialchars($kurir_pilihan) ?>">

            <div class="checkout-grid">
                <!-- KIRI -->
                <div>

                    <!-- PENERIMA -->
                    <div class="co-card">
                        <div class="co-title">📦 Informasi Penerima</div>
                        <div class="fg-row">
                            <div class="fg">
                                <label>Nama Lengkap <span class="req">*</span></label>
                                <input type="text" name="nama_penerima" value="<?= htmlspecialchars($nama_penerima) ?>" placeholder="Nama penerima" required>
                            </div>
                            <div class="fg">
                                <label>No. Telepon <span class="req">*</span></label>
                                <input type="tel" name="telepon" value="<?= htmlspecialchars($telepon) ?>" placeholder="cth: 08123456789" required>
                            </div>
                        </div>
                        <div class="fg">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly style="opacity:.5;cursor:not-allowed;">
                        </div>
                    </div>

                    <!-- ALAMAT -->
                    <div class="co-card">
                        <div class="co-title">🏠 Alamat Pengiriman</div>
                        <div class="fg">
                            <label>Alamat Lengkap <span class="req">*</span></label>
                            <textarea name="alamat" placeholder="Jl. Contoh No. 12, RT/RW, Kelurahan" required><?= htmlspecialchars($alamat) ?></textarea>
                        </div>
                        <div class="fg-row">
                            <div class="fg">
                                <label>Kota <span class="req">*</span></label>
                                <input type="text" name="kota" value="<?= htmlspecialchars($kota) ?>" placeholder="cth: Yogyakarta" required>
                            </div>
                            <div class="fg">
                                <label>Provinsi <span class="req">*</span></label>
                                <input type="text" name="provinsi" value="<?= htmlspecialchars($provinsi) ?>" placeholder="cth: DI Yogyakarta" required>
                            </div>
                        </div>
                        <div class="fg">
                            <label>Kode Pos <span class="req">*</span></label>
                            <input type="text" name="kode_pos" value="<?= htmlspecialchars($kode_pos) ?>" placeholder="cth: 55281" required>
                        </div>
                        <div class="fg">
                            <label>Catatan (opsional)</label>
                            <textarea name="catatan" placeholder="cth: Titip ke satpam, jangan diketuk"><?= htmlspecialchars($catatan) ?></textarea>
                        </div>
                    </div>

                    <!-- KURIR -->
                    <div class="co-card">
                        <div class="co-title">🚚 Pilih Kurir</div>
                        <div class="kurir-list">
                            <?php foreach ($ongkir_map as $k => $hk): ?>
                            <div class="kurir-opt">
                                <input type="radio" name="kurir_pilihan_radio" id="k<?= md5($k) ?>"
                                       value="<?= htmlspecialchars($k) ?>"
                                       <?= $kurir_pilihan === $k ? 'checked' : '' ?>
                                       onchange="document.getElementById('kurirHidden').value=this.value; document.getElementById('formKurir').submit();">
                                <label class="kurir-lbl" for="k<?= md5($k) ?>">
                                    <div class="kurir-left">
                                        <span class="kurir-nm"><?= htmlspecialchars($k) ?></span>
                                        <span class="kurir-est">Estimasi <?= $k==='JNE YES'?'1 hari':($k==='SiCepat'?'1-2 hari':($k==='Gosend'?'Same Day':'2-3 hari')) ?></span>
                                    </div>
                                    <span class="kurir-rp">Rp <?= number_format($hk, 0, ',', '.') ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- PEMBAYARAN -->
                    <div class="co-card">
                        <div class="co-title">💳 Metode Pembayaran</div>
                        <div class="pay-grid">
                            <?php
                            $pay_list = [
                                'Transfer BCA'     => '🏦',
                                'Transfer Mandiri' => '🏦',
                                'Transfer BRI'     => '🏦',
                                'GoPay'            => '💚',
                                'OVO'              => '💜',
                                'DANA'             => '🔵',
                                'QRIS'             => '📱',
                                'COD'              => '💵',
                            ];
                            foreach ($pay_list as $pay => $icon):
                            ?>
                            <div class="pay-opt">
                                <input type="radio" name="pembayaran" id="p<?= md5($pay) ?>"
                                       value="<?= htmlspecialchars($pay) ?>"
                                       <?= $bayar_pilihan === $pay ? 'checked' : '' ?> required>
                                <label class="pay-lbl" for="p<?= md5($pay) ?>">
                                    <span class="pay-icon"><?= $icon ?></span>
                                    <?= htmlspecialchars($pay) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (in_array($bayar_pilihan, ['Transfer BCA','Transfer Mandiri','Transfer BRI'])): ?>
                        <div class="bank-info">
                            <strong>Info Rekening:</strong><br>
                            BCA &nbsp;&nbsp;→ <strong>1234567890</strong> a.n. Crut Parfumes<br>
                            Mandiri → <strong>0987654321</strong> a.n. Crut Parfumes<br>
                            BRI &nbsp;&nbsp;→ <strong>1122334455</strong> a.n. Crut Parfumes<br>
                            <span style="color:rgba(255,255,255,0.35);font-size:11px;">Transfer tepat sesuai total tagihan.</span>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- KANAN: SUMMARY -->
                <div>
                    <div class="summary-card">
                        <div class="sum-head"><div class="sum-title">Ringkasan Pesanan</div></div>
                        <div class="sum-prod">
                            <img src="<?= $p['gambar'] ?>" alt="<?= $p['nama'] ?>">
                            <div class="sp-info">
                                <p class="sp-kat"><?= $p['kategori'] ?></p>
                                <p class="sp-nama"><?= $p['nama'] ?></p>
                                <p class="sp-qty"><?= $p['qty'] ?> botol</p>
                            </div>
                            <div class="sp-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                        </div>
                        <div class="sum-rows">
                            <div class="sr">
                                <span class="sr-l">Subtotal</span>
                                <span class="sr-v">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                            </div>
                            <div class="sr">
                                <span class="sr-l">Kurir</span>
                                <span class="sr-v"><?= htmlspecialchars($kurir_pilihan) ?></span>
                            </div>
                            <div class="sr">
                                <span class="sr-l">Ongkos Kirim</span>
                                <span class="sr-v">Rp <?= number_format($ongkir, 0, ',', '.') ?></span>
                            </div>
                            <div class="sr">
                                <span class="sr-l">Pembayaran</span>
                                <span class="sr-gold"><?= htmlspecialchars($bayar_pilihan) ?></span>
                            </div>
                        </div>
                        <div class="sum-total">
                            <span class="st-l">Total</span>
                            <span class="st-v">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <div class="sum-btn">
                            <button type="submit" class="btn-konfirmasi">Konfirmasi Pesanan →</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>