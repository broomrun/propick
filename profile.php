<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User is not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user data from the database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    die("Error: User data not found.");
}

// Fetch the user's test history from the database
$query_history = "SELECT * FROM test_history WHERE user_id = $user_id ORDER BY date DESC";
$result_history = $conn->query($query_history);

if ($result_history->num_rows > 0) {
    $test_history = [];
    while ($row = $result_history->fetch_assoc()) {
        $test_history[] = [
            'major' => htmlspecialchars($row['major']),
            'suitable' => $row['suitable'] ? 'COCOK' : 'TIDAK COCOK',
            'date' => $row['date']
        ];
    }
} else {
    $test_history = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body { background-color: #FAF7F0; color: #000000; }
        .navbar { background-color: #B17457; }
        .test-history-table { margin-top: 2rem; }
        .btn-custom { background-color: #B17457; color: #FFFFFF; border: none; }
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

    <!-- Profile Section -->
    <div class="container">
        <h2 class="my-4">Profil Pengguna</h2>
        <div class="card">
            <div class="card-header">
                <strong>Informasi Pengguna</strong>
            </div>
            <div class="card-body">
                <p><strong>Nama:</strong> <?= htmlspecialchars($user_data['nama']) ?></p>
                <p><strong>Umur:</strong> <?= htmlspecialchars($user_data['umur']) ?></p>
                <p><strong>Asal Sekolah:</strong> <?= htmlspecialchars($user_data['asal_sekolah']) ?></p>
            </div>
        </div>

        <!-- Test History Section -->
        <h3 class="my-4">Riwayat Tes Jurusan</h3>
        <?php if ($test_history): ?>
            <table class="table table-striped test-history-table">
                <thead>
                    <tr>
                        <th>Jurusan</th>
                        <th>Hasil</th>
                        <th>Tanggal Tes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($test_history as $history): ?>
                        <tr>
                            <td><?= $history['major'] ?></td>
                            <td class="<?= $history['suitable'] == 'COCOK' ? 'suitable' : 'not-suitable' ?>"><?= $history['suitable'] ?></td>
                            <td><?= date("d-m-Y", strtotime($history['date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Belum ada riwayat tes.</p>
        <?php endif; ?>

        <!-- Profile Links -->
        <div class="mt-4">
            <a href="index.php" class="btn btn-custom">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
