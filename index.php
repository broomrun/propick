<?php
include 'config.php';

// Ambil daftar jurusan dari database
$query = "SELECT * FROM majors";
$majors = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Pemilihan Jurusan</title>
</head>
<body>
    <h1>Form Pemilihan Jurusan</h1>
    <form action="process.php" method="POST">
        <label for="nama">Nama:</label>
        <input type="text" name="nama" id="nama" required><br>

        <label for="umur">Umur:</label>
        <input type="number" name="umur" id="umur" required><br>

        <label for="asal_sekolah">Asal Sekolah:</label>
        <select name="asal_sekolah" id="asal_sekolah" required>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select><br>

        <label for="jurusan">Pilih Jurusan:</label>
        <select name="jurusan" id="jurusan" required>
            <?php while ($major = $majors->fetch_assoc()): ?>
                <option value="<?= $major['nama_jurusan'] ?>"><?= $major['nama_jurusan'] ?></option>
            <?php endwhile; ?>
        </select><br>

        <input type="submit" value="Lanjutkan">
    </form>
</body>
</html>
