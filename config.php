<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sispakar';

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $password, $database);

// Cek apakah koneksi berhasil
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
