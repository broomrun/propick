<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User is not logged in.");
}

$user_id = $_SESSION['user_id']; // Get the user ID from session

// Get POST data and validate inputs
$jurusan = htmlspecialchars(trim($_POST['jurusan'] ?? ''));
if (empty($jurusan)) {
    die("Error: Jurusan tidak valid.");
}

// Fetch user data from the database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    die("Error: User data not found.");
}

// Get the user's answers from the form
$total_questions = 7;  // Assume there are 7 questions for each major
$correct_answers = 0;  // To count "Yes" answers

// Count "Yes" answers
foreach ($_POST as $key => $value) {
    if (strpos($key, 'answer_') === 0) {  // Check if the key starts with 'answer_'
        if ($value == 'ya') {
            $correct_answers++;
        }
    }
}

if ($correct_answers === 0) {
    // If no answers are selected, show an error message
    die("Error: Tidak ada jawaban yang dipilih.");
}

// Set the threshold to half of the total questions
$threshold = ceil($total_questions / 2);  // Half of the total questions

// Determine if the major is suitable or not
$is_suitable = $correct_answers >= $threshold;

// Insert the test result into the test_history table
$query_insert = "INSERT INTO test_history (user_id, major, suitable, date) 
                 VALUES ($user_id, '$jurusan', " . ($is_suitable ? 1 : 0) . ", NOW())";
if ($conn->query($query_insert) === TRUE) {
    // Set the result message based on suitability
// Set the result message based on suitability
if ($is_suitable) {
    $resultMessage = "Selamat! Berdasarkan jawaban kamu, jurusan ini <span class='suitable'><strong>COCOK</strong></span> untuk kamu.<br>
                      <strong>Kamu sangat cocok dengan jurusan yang kamu pilih!</strong><br>
                      <div class='divider'></div>
                      <strong>Nama:</strong> " . htmlspecialchars($user_data['nama']) . "<br>
                      <strong>Umur:</strong> " . htmlspecialchars($user_data['umur']) . "<br>
                      <strong>Asal Sekolah:</strong> " . htmlspecialchars($user_data['asal_sekolah']) . "<br>
                      <div class='divider'></div>
                      Semangat untuk langkah selanjutnya!<br>
                      <div class='divider'></div>
                      Apakah kamu ingin mencoba jurusan lain?";
} else {
    $resultMessage = "Sayangnya, jawaban kamu menunjukkan bahwa jurusan ini <span class='not-suitable'><strong>TIDAK COCOK</strong></span> untuk kamu.<br>
                      <strong>Silakan coba jurusan lain yang lebih sesuai!</strong><br>
                      <div class='divider'></div>
                      <strong>Nama:</strong> " . htmlspecialchars($user_data['nama']) . "<br>
                      <strong>Umur:</strong> " . htmlspecialchars($user_data['umur']) . "<br>
                      <strong>Asal Sekolah:</strong> " . htmlspecialchars($user_data['asal_sekolah']) . "<br>
                      <div class='divider'></div>
                      Mungkin jurusan ini bukan yang terbaik untuk kamu, coba pertimbangkan pilihan lain.<br>
                      <div class='divider'></div>
                      Coba cari jurusan yang lebih sesuai dengan minat dan keahlian kamu.";
}
} else {
    $resultMessage = "Terjadi kesalahan saat menyimpan hasil tes.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProdiPicker - Hasil</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body { background-color: #FAF7F0; color: #000000; }
        .navbar { background-color: #B17457; }
        .result-container {
            text-align: center;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #D8D2C2;
            border-radius: 10px;
            max-width: 600px;
        }
        .btn-custom {
            background-color: #B17457;
            color: #FFFFFF;
            border: none;
        }
        .divider {
            border-top: 2px solid #B17457;
            margin: 20px 0;
        }

        .btn-custom:hover { background-color: #8F5B40; }
        .bold { font-weight: bold; }
        .not-suitable { color: red; }
        .suitable { color: green; font-weight: bold; }

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">ProdiPicker</a>
    </nav>

    <!-- Result Section -->
    <div class="container result-container">
        <h2>Hasil Tes</h2>
        <div class='divider'></div>
        <p id="resultMessage"><?= $resultMessage ?></p>
        <h3 id="resultJurusan" class="font-weight-bold"><?= htmlspecialchars($jurusan) ?></h3>
        <a href="index.php" class="btn btn-custom mt-4">Akhiri</a>
        <a href="profile.php" class="btn btn-custom mt-4">Lihat Riwayat</a>
        <button class="btn btn-custom mt-4 ml-3" data-toggle="modal" data-target="#jurusanModal">Lihat Semua Jurusan</button>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="jurusanModal" tabindex="-1" role="dialog" aria-labelledby="jurusanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jurusanModalLabel">Daftar Semua Jurusan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Jurusan</th>
                                <th>Kriteria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM majors";
                            $result = $conn->query($query);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_jurusan']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kriteria']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>Data tidak ditemukan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
