<?php
include 'config.php';

// Ambil data dari form
$nama = $_POST['nama'];
$umur = $_POST['umur'];
$asal_sekolah = $_POST['asal_sekolah'];
$jurusan = $_POST['jurusan'];

// Simpan data pengguna ke database
$stmt = $conn->prepare("INSERT INTO users (nama, umur, asal_sekolah, jurusan) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $umur, $asal_sekolah, $jurusan);
$stmt->execute();
$stmt->close();

// Ambil kriteria untuk jurusan yang dipilih
$query = "SELECT * FROM majors WHERE nama_jurusan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $jurusan);
$stmt->execute();
$result = $stmt->get_result();
$major = $result->fetch_assoc();

// Ambil pertanyaan yang relevan dengan jurusan
$query = "SELECT * FROM questions WHERE major_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $major['id']);
$stmt->execute();
$questions = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pertanyaan untuk Jurusan <?= $jurusan ?></title>
</head>
<body>
    <h1>Pertanyaan untuk Menentukan Kesesuaian Jurusan</h1>
    <form action="check_eligibility.php" method="POST">
        <h2>Jurusan yang dipilih: <?= $jurusan ?></h2>
        <input type="hidden" name="jurusan" value="<?= $jurusan ?>">
        <input type="hidden" name="user_id" value="<?= $conn->insert_id ?>">

        <?php while ($question = $questions->fetch_assoc()): ?>
            <label for="question_<?= $question['id'] ?>"><?= $question['pertanyaan'] ?></label><br>
            <input type="radio" name="answer_<?= $question['id'] ?>" value="Ya" required> Ya
            <input type="radio" name="answer_<?= $question['id'] ?>" value="Tidak"> Tidak<br><br>
        <?php endwhile; ?>

        <input type="submit" value="Kirim Jawaban">
    </form>
</body>
</html>
