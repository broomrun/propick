<?php
include 'config.php';
function getJurusan() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM jurusan");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ambil semua pertanyaan untuk jurusan tertentu
function getPertanyaan($jurusan_sebelumnya) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE jurusan_id = :jurusan_id");
    $stmt->bindParam(':jurusan_id', $jurusan_sebelumnya);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
