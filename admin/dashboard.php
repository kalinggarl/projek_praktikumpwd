<?php
session_start();
include '../konek.php';

// ===== ADMIN GUARD =====
// Hanya role 'admin' yang bisa akses, selain itu redirect
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$user  = $_SESSION['user'];
$pesan = '';
$tipe  = '';

// ===== TAMBAH PRODUK =====
if (isset($_POST['tambah'])) {
    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $harga     = (int)$_POST['harga'];
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
    $gambar    = mysqli_real_escape_string($conn, trim($_POST['gambar']));

    if ($nama && $harga && $gambar) {
        mysqli_query($conn, "INSERT INTO produk (nama, harga, deskripsi, gambar, kategori) 
                             VALUES ('$nama', $harga, '$deskripsi', '$gambar', '$kategori')");
        $pesan = 'Produk <strong>' . htmlspecialchars($nama) . '</strong> berhasil ditambahkan!';
        $tipe  = 'sukses';
    } else {
        $pesan = 'Harap isi semua field yang wajib (Nama, Harga, Gambar).';
        $tipe  = 'error';
    }
}

// ===== HAPUS PRODUK =====
if (isset($_GET['hapus'])) {
    $id     = (int)$_GET['hapus'];
    $cek    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM produk WHERE id = $id"));
    if ($cek) {
        mysqli_query($conn, "DELETE FROM produk WHERE id = $id");
        $pesan = 'Produk <strong>' . htmlspecialchars($cek['nama']) . '</strong> berhasil dihapus.';
        $tipe  = 'hapus';
    }
}

// Ambil semua produk
$produk_list = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
$total_produk = mysqli_num_rows($produk_list);
$produk_list = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crut Parfumes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Jost',sans-serif; background:#0d0b08; min-height:100vh; color:#fff; display:flex; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width:220px; flex-shrink:0;
            background:rgba(255,255,255,0.03);
            border-right:1px solid rgba(255,255,255,0.07);
            display:flex; flex-direction:column;
            padding:32px 0; position:fixed; top:0; left:0; bottom:0; z-index:100;
        }
        .sidebar-logo {
            font-family:'Libre Baskerville',serif; font-size:20px;
            letter-spacing:3px; color:#e6c097; text-align:center; margin-bottom:4px;
        }
        .sidebar-badge {
            text-align:center; font-size:10px; letter-spacing:.2em;
            text-transform:uppercase; color:rgba(255,255,255,0.25); margin-bottom:36px;
        }
        .sidebar-nav { padding:0 14px; flex:1; }
        .sidebar-nav a {
            display:flex; align-items:center; gap:10px;
            padding:10px 14px; border-radius:10px;
            color:rgba(255,255,255,0.45); text-decoration:none;
            font-size:13px; margin-bottom:4px; transition:all .2s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background:rgba(230,192,151,0.1); color:#e6c097;
        }
        .sidebar-nav .icon { font-size:16px; }
        .sidebar-footer { padding:0 14px; }
        .sidebar-footer a {
            display:block; padding:10px 14px; border-radius:10px;
            border:1px solid rgba(255,255,255,0.09);
            color:rgba(255,255,255,0.35); text-decoration:none;
            font-size:12px; text-align:center; letter-spacing:.06em; transition:all .2s;
        }
        .sidebar-footer a:hover { border-color:#e6c097; color:#e6c097; }

        /* ===== MAIN ===== */
        .main { margin-left:220px; flex:1; padding:36px 32px; }

        /* TOP BAR */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:32px;
        }
        .topbar h1 {
            font-family:'Libre Baskerville',serif; font-size:21px;
            font-weight:400; color:#f0e8d8;
        }
        .topbar p { font-size:12px; color:rgba(255,255,255,0.3); margin-top:2px; }
        .admin-chip {
            display:flex; align-items:center; gap:8px;
            background:rgba(230,192,151,0.08); border:1px solid rgba(230,192,151,0.2);
            border-radius:20px; padding:7px 16px;
            font-size:12px; color:#e6c097;
        }

        /* STAT CARD */
        .stat-card {
            background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08);
            border-radius:14px; padding:20px 22px; margin-bottom:28px;
            display:inline-flex; align-items:center; gap:16px;
        }
        .stat-icon { font-size:28px; }
        .stat-num { font-family:'Libre Baskerville',serif; font-size:28px; color:#e6c097; }
        .stat-lbl { font-size:11px; color:rgba(255,255,255,0.35); letter-spacing:.1em; text-transform:uppercase; }

        /* ALERT */
        .alert-box {
            padding:12px 18px; border-radius:10px; font-size:13px;
            margin-bottom:22px; display:flex; align-items:center; gap:10px;
            animation:slideDown .3s ease;
        }
        @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .alert-sukses { background:rgba(168,213,162,0.08); border:1px solid rgba(168,213,162,0.25); color:#a8d5a2; }
        .alert-hapus  { background:rgba(255,100,100,0.06); border:1px solid rgba(255,100,100,0.2);  color:#ff9999; }
        .alert-error  { background:rgba(255,180,50,0.07);  border:1px solid rgba(255,180,50,0.2);   color:#ffc266; }

        /* GRID */
        .content-grid { display:grid; grid-template-columns:360px 1fr; gap:24px; align-items:start; }

        /* FORM CARD */
        .form-card {
            background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09);
            border-radius:18px; padding:26px 24px;
        }
        .card-title {
            font-family:'Libre Baskerville',serif; font-size:15px; font-weight:400;
            color:#e6c097; margin-bottom:20px; letter-spacing:.06em;
            display:flex; align-items:center; gap:8px;
        }
        .fg { margin-bottom:15px; }
        .fg label {
            display:block; font-size:10px; letter-spacing:.18em;
            text-transform:uppercase; color:rgba(255,255,255,0.32); margin-bottom:6px;
        }
        .fg input, .fg textarea, .fg select {
            width:100%; padding:10px 13px;
            background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
            border-radius:9px; color:#fff; font-family:'Jost',sans-serif; font-size:13px;
            outline:none; transition:border-color .2s;
        }
        .fg input:focus, .fg textarea:focus, .fg select:focus {
            border-color:rgba(230,192,151,0.45);
        }
        .fg textarea { resize:vertical; min-height:75px; }
        .fg select option { background:#1a1610; }
        .fg input::placeholder, .fg textarea::placeholder { color:rgba(255,255,255,0.18); }
        .req { color:#e6c097; }

        .btn-tambah {
            width:100%; padding:13px;
            background:transparent; color:#e6c097;
            font-family:'Jost',sans-serif; font-size:11px;
            letter-spacing:.18em; text-transform:uppercase;
            border:1px solid #e6c097; border-radius:10px;
            cursor:pointer; margin-top:4px; transition:all .25s;
        }
        .btn-tambah:hover { background:#e6c097; color:#1a1610; }

        /* TABLE CARD */
        .table-card {
            background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09);
            border-radius:18px; overflow:hidden;
        }
        .table-head {
            padding:18px 22px; border-bottom:1px solid rgba(255,255,255,0.07);
            display:flex; justify-content:space-between; align-items:center;
        }
        .tbl { width:100%; border-collapse:collapse; }
        .tbl th {
            padding:11px 18px; font-size:10px; letter-spacing:.18em;
            text-transform:uppercase; color:rgba(255,255,255,0.28); font-weight:400;
            text-align:left; border-bottom:1px solid rgba(255,255,255,0.06);
        }
        .tbl td {
            padding:12px 18px; font-size:13px;
            color:rgba(255,255,255,0.72); border-bottom:1px solid rgba(255,255,255,0.05);
            vertical-align:middle;
        }
        .tbl tr:last-child td { border-bottom:none; }
        .tbl tr:hover td { background:rgba(255,255,255,0.02); }

        .tbl-img { width:40px; height:50px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(0,0,0,0.4)); }
        .tbl-nama { color:#f0e8d8; font-size:13px; }
        .tbl-kat {
            display:inline-block; font-size:10px; letter-spacing:.1em;
            color:rgba(230,192,151,0.6); border:1px solid rgba(230,192,151,0.2);
            padding:2px 9px; border-radius:20px;
        }
        .tbl-harga { color:#e6c097; font-weight:500; }

        /* HAPUS BTN */
        .btn-hapus {
            padding:5px 13px; background:transparent;
            color:rgba(255,100,100,0.65); font-size:11px; letter-spacing:.1em;
            text-transform:uppercase; border:1px solid rgba(255,100,100,0.25);
            border-radius:8px; cursor:pointer; transition:all .2s;
            font-family:'Jost',sans-serif;
        }
        .btn-hapus:hover {
            background:rgba(255,100,100,0.1); border-color:rgba(255,100,100,0.55);
            color:#ff7070;
        }

        /* KONFIRMASI MODAL */
        .modal-bg {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.78); z-index:9999;
            justify-content:center; align-items:center;
        }
        .modal-bg.active { display:flex; }
        .modal-box {
            background:#1a1610; border:1px solid rgba(255,255,255,0.1);
            border-radius:18px; width:310px; padding:36px 28px;
            text-align:center; animation:popIn .3s ease;
        }
        @keyframes popIn { from{opacity:0;transform:scale(.88)} to{opacity:1;transform:scale(1)} }
        .modal-box .m-icon { font-size:34px; margin-bottom:14px; }
        .modal-box h4 {
            font-family:'Libre Baskerville',serif; font-size:17px;
            font-weight:400; color:#f0e8d8; margin-bottom:8px;
        }
        .modal-box p { font-size:12px; color:rgba(255,255,255,0.38); margin-bottom:24px; line-height:1.6; }
        .modal-btns { display:flex; gap:10px; }
        .mbtn-cancel {
            flex:1; padding:11px; background:transparent;
            color:rgba(255,255,255,0.4); font-family:'Jost',sans-serif;
            font-size:11px; letter-spacing:.12em; text-transform:uppercase;
            border:1px solid rgba(255,255,255,0.14); border-radius:10px;
            cursor:pointer; transition:all .2s;
        }
        .mbtn-cancel:hover { border-color:rgba(255,255,255,0.35); color:#fff; }
        .mbtn-hapus {
            flex:1; padding:11px; background:transparent;
            color:#ff7070; font-family:'Jost',sans-serif;
            font-size:11px; letter-spacing:.12em; text-transform:uppercase;
            border:1px solid rgba(255,100,100,0.3); border-radius:10px;
            text-decoration:none; display:flex; align-items:center; justify-content:center;
            transition:all .2s;
        }
        .mbtn-hapus:hover { background:rgba(255,100,100,0.12); border-color:rgba(255,100,100,0.6); color:#ff9090; }
    </style>
</head>
<body>

<!-- KONFIRMASI HAPUS MODAL -->
<div class="modal-bg" id="hapusModal">
    <div class="modal-box">
        <div class="m-icon">🗑️</div>
        <h4>Hapus Produk?</h4>
        <p>Tindakan ini tidak bisa dibatalkan. Produk akan dihapus permanen dari database.</p>
        <div class="modal-btns">
            <button class="mbtn-cancel" onclick="tutupModal()">Batal</button>
            <a class="mbtn-hapus" id="hapusLink" href="#">Ya, Hapus</a>
        </div>
    </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">CRUT</div>
    <div class="sidebar-badge">Admin Panel</div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="active">
            <span class="icon">📦</span> Kelola Produk
        </a>
        <a href="../index.php">
            <span class="icon">🏠</span> Lihat Website
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../logout.php">Logout</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <h1>Kelola Produk</h1>
            <p>Tambah atau hapus produk dari koleksi Crut Parfumes</p>
        </div>
        <div class="admin-chip">
            👤 <?= htmlspecialchars($user['nama']) ?>
        </div>
    </div>

    <!-- STAT -->
    <div class="stat-card">
        <div class="stat-icon">🧴</div>
        <div>
            <div class="stat-num"><?= $total_produk ?></div>
            <div class="stat-lbl">Total Produk</div>
        </div>
    </div>

    <!-- ALERT -->
    <?php if ($pesan): ?>
    <div class="alert-box alert-<?= $tipe ?>">
        <?= $tipe === 'sukses' ? '✓' : ($tipe === 'hapus' ? '🗑' : '⚠') ?>
        <?= $pesan ?>
    </div>
    <?php endif; ?>

    <!-- GRID: FORM + TABLE -->
    <div class="content-grid">

        <!-- FORM TAMBAH PRODUK -->
        <div class="form-card">
            <div class="card-title">➕ Tambah Produk Baru</div>
            <form method="POST" action="dashboard.php">
                <div class="fg">
                    <label>Nama Produk <span class="req">*</span></label>
                    <input type="text" name="nama" placeholder="cth: Wonderful Blue Sea" required>
                </div>
                <div class="fg">
                    <label>Harga (Rp) <span class="req">*</span></label>
                    <input type="number" name="harga" placeholder="cth: 299000" min="1" required>
                </div>
                <div class="fg">
                    <label>Kategori</label>
                    <select name="kategori">
                        <option value="Eau De Parfume">Eau De Parfume</option>
                        <option value="Extrait">Extrait</option>
                        <option value="Parfum">Parfum</option>
                        <option value="Eau de Cologne">Eau de Cologne</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Nama File Gambar <span class="req">*</span></label>
                    <input type="text" name="gambar" placeholder="cth: produk.png">
                </div>
                <div class="fg">
                    <label>Deskripsi / Fragrance Notes</label>
                    <textarea name="deskripsi" placeholder="cth: Top Notes: Bergamot&#10;Middle Notes: Rose&#10;Base Notes: Musk"></textarea>
                </div>
                <button type="submit" name="tambah" class="btn-tambah">+ Tambah Produk</button>
            </form>
        </div>

        <!-- TABEL PRODUK -->
        <div class="table-card">
            <div class="table-head">
                <div class="card-title" style="margin-bottom:0">📋 Daftar Produk</div>
                <span style="font-size:12px;color:rgba(255,255,255,0.3);"><?= $total_produk ?> produk</span>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($produk_list)): ?>
                    <tr>
                        <td><img class="tbl-img" src="../<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>"></td>
                        <td class="tbl-nama"><?= htmlspecialchars($p['nama']) ?></td>
                        <td><span class="tbl-kat"><?= htmlspecialchars($p['kategori']) ?></span></td>
                        <td class="tbl-harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                        <td>
                            <button class="btn-hapus"
                                onclick="konfirmasiHapus(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>')">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    function konfirmasiHapus(id, nama) {
        document.getElementById('hapusLink').href = 'dashboard.php?hapus=' + id;
        document.getElementById('hapusModal').classList.add('active');
    }
    function tutupModal() {
        document.getElementById('hapusModal').classList.remove('active');
    }
    document.getElementById('hapusModal').addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });
</script>

</body>
</html>