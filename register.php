<?php
include 'konek.php';

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    mysqli_query($conn, "INSERT INTO users (nama, email, password, role) 
    VALUES ('$nama', '$email', '$password', 'user')");

    echo "<script>alert('Register berhasil! Silakan login'); window.location='login.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Register</h2>

<form method="POST">
    <input type="text" name="nama" placeholder="Nama" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit" name="register">Daftar</button>
</form>

<p>Sudah punya akun? <a href="login.php">Login</a></p>

</body>
</html>