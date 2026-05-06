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

    <!-- CSS langsung di sini biar ga ribet -->
    <style>
        body {
            background-color: #111;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial;
        }

        .auth-box {
            background-color: #1c1c1c;
            padding: 30px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            border: 1px solid #444;
        }

        .auth-box h2 {
            color: white;
        }

        .auth-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        .auth-box button {
            width: 100%;
            padding: 10px;
            background: white;
            border: none;
            cursor: pointer;
        }

        .auth-box p {
            color: white;
        }

        .auth-box a {
            color: #ccc;
        }
    </style>
</head>

<body>

<div class="auth-box">
    <h2>Login</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Register</a></p>
</div>

</body>
</html>