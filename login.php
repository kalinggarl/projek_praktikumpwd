<?php
include 'konek.php';
session_start();

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users 
    WHERE email='$email' AND password='$password'");

    $user = mysqli_fetch_assoc($query);

    if ($user) {
        $_SESSION['user'] = $user;

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
    } else {
        echo "<script>alert('Akun belum terdaftar!'); window.location='register.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <!-- Panggil CSS -->
    <link rel="stylesheet" href="login.css">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>

<div class="auth-box">

    <div class="logo">CRUT PARFUM</div>
    <div class="tagline">FEEL THE ESSENCE</div>

    <h2>Welcome Back</h2>
    <div class="subtitle">Please login to continue</div>

    <form method="POST">
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="options">
            <label><input type="checkbox"> Remember</label>
            <a href="#">Forgot?</a>
        </div>

        <button type="submit" name="login" class="login-btn">SIGN IN</button>
    </form>

    <div class="divider">OR</div>

    <button class="google-btn">Continue with Google</button>

    <div class="register">
        Belum punya akun? <a href="register.php">Register</a>
    </div>

</div>

</body>
</html>