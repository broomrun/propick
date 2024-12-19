<?php
include 'config.php';

// Get POST data and validate inputs
$jurusan = htmlspecialchars(trim($_POST['jurusan'] ?? ''));
$user_id = (int) ($_POST['user_id'] ?? 0);

// Validate essential inputs
if (empty($jurusan) || empty($user_id)) {
    die("Error: Input tidak valid.");
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
$total_questions = 7;  // Asumsi jumlah pertanyaan untuk setiap jurusan adalah 7
$correct_answers = 0;  // Untuk menghitung jawaban "Ya"

// Menghitung jawaban "Ya"
foreach ($_POST as $key => $value) {
    if (strpos($key, 'answer_') === 0) {  // Mengambil hanya jawaban
        if ($value == 'ya') {
            $correct_answers++;
        }
    }
}

if ($correct_answers === 0) {
    // If no answers are selected, show a message
    die("Error: Tidak ada jawaban yang dipilih.");
}

// Tentukan ambang batas (threshold) sebagai setengah dari jumlah pertanyaan
$threshold = ceil($total_questions / 2);  // Ambang batas setengah jumlah pertanyaan

// Tentukan apakah jurusan cocok atau tidak
$is_suitable = $correct_answers >= $threshold;

// Prepare the result message
if ($is_suitable) {
    $resultMessage = "Selamat! Berdasarkan jawaban kamu, jurusan ini <span class='suitable'>COCOK</span> untuk kamu.<br>
                      Kamu sangat cocok dengan jurusan yang kamu pilih!<br><br>
                      <strong>Nama:</strong> " . htmlspecialchars($user_data['nama']) . "<br>
                      <strong>Umur:</strong> " . htmlspecialchars($user_data['umur']) . "<br>
                      <strong>Asal Sekolah:</strong> " . htmlspecialchars($user_data['asal_sekolah']) . "<br><br>
                      Semangat untuk langkah selanjutnya!<br>
                      Apakah kamu ingin mencoba jurusan lain?";
} else {
    $resultMessage = "Sayangnya, jawaban kamu menunjukkan bahwa jurusan ini <span class='not-suitable'>TIDAK COCOK</span> untuk kamu.<br>
                      Silakan coba jurusan lain yang lebih sesuai!<br><br>
                      <strong>Nama:</strong> " . htmlspecialchars($user_data['nama']) . "<br>
                      <strong>Umur:</strong> " . htmlspecialchars($user_data['umur']) . "<br>
                      <strong>Asal Sekolah:</strong> " . htmlspecialchars($user_data['asal_sekolah']) . "<br><br>
                      Mungkin jurusan ini bukan yang terbaik untuk kamu, coba pertimbangkan pilihan lain.<br>
                      Coba cari jurusan yang lebih sesuai dengan minat dan keahlian kamu.";
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
        <h2>Hasil Rekomendasi Jurusan</h2>
        <p id="resultMessage"><?= $resultMessage ?></p>
        <h3 id="resultJurusan" class="font-weight-bold"><?= htmlspecialchars($jurusan) ?></h3>
        <a href="index.php" class="btn btn-custom mt-4">Kembali ke Beranda</a>
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
