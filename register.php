<?php
include 'konek.php';

if (isset($_POST['register'])) {
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
    } else {
        mysqli_query($conn, "INSERT INTO users (nama, email, password, role) 
        VALUES ('$nama', '$email', '$password', 'user')");
        echo "<script>alert('Register berhasil! Silakan login'); window.location='login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="auth-box">
    <h2 class="logo">CRUT</h2>
    <p class="tagline">Create Your Signature Scent</p>
    <p class="subtitle">Register</p>

    <form method="POST">
        <div class="input-group">
            <input type="text" name="nama" placeholder="Nama" required>
        </div>
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" name="register" class="login-btn">Register</button>
    </form>

    <p class="register">
        Sudah punya akun? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>