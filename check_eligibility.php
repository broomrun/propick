<?php
include 'config.php';

// Ambil data yang dikirimkan dari form
$jurusan = $_POST['jurusan'];
$user_id = $_POST['user_id'];

// Ambil jawaban dari setiap pertanyaan
$query = "SELECT * FROM questions WHERE major_id = (SELECT id FROM majors WHERE nama_jurusan = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $jurusan);
$stmt->execute();
$questions = $stmt->get_result();

if (!$questions) {
    die('Query Error: ' . $conn->error);  // Menambahkan pengecekan error pada query
}

$total_questions = 0;
$correct_answers = 0;  // Untuk menghitung jawaban "Ya"

while ($question = $questions->fetch_assoc()) {
    $answer = $_POST['answer_' . $question['id']];
    
    // Hitung jawaban "Ya"
    if ($answer == "Ya") {
        $correct_answers++;
    }
    $total_questions++;
}

// Tentukan apakah cocok berdasarkan jumlah jawaban "Ya"
if ($correct_answers >= ($total_questions / 2)) {
    echo "Selamat! Anda cocok dengan jurusan " . $jurusan;
} else {
    echo "Maaf, Anda tidak cocok dengan jurusan " . $jurusan;
}
?>
