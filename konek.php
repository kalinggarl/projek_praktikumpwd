<?php
$conn = mysqli_connect("localhost", "root", "", "crut_parfum");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>