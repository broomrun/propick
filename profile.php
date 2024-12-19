<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to view your profile.");
}

// Include the configuration file
include 'config.php';

// Get the current user's ID from the session
$user_id = (int) $_SESSION['user_id'];  // Assumes user_id is stored in session

// Fetch user data from the database
$query_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = $conn->query($query_user);

if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
} else {
    die("Error: User data not found.");
}

// Fetch the user's test history from the test_history table
$query_history = "SELECT * FROM test_history WHERE user_id = $user_id ORDER BY date DESC";
$result_history = $conn->query($query_history);

// Prepare the history display message
$test_history = [];
if ($result_history->num_rows > 0) {
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
    <title>Profil - ProdiPicker</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body { background-color: #FAF7F0; color: #000000; }
        .navbar { background-color: #B17457; }
        .profile-container { text-align: center; margin: 3rem auto; padding: 2rem; background-color: #D8D2C2; border-radius: 10px; max-width: 800px; }
        .btn-custom { background-color: #B17457; color: #FFFFFF; border: none; }
        .btn-custom:hover { background-color: #8F5B40; }
        .bold { font-weight: bold; }
        .test-history-table { margin-top: 2rem; }
        .suitable { color: green; font-weight: bold; }
        .not-suitable { color: red; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">ProdiPicker</a>
    </nav>

    <!-- Profile Section -->
    <div class="container profile-container">
        <h2>Profil Pengguna</h2>
        <p><strong>Nama:</strong> <?= htmlspecialchars($user_data['nama']) ?></p>
        <p><strong>Umur:</strong> <?= htmlspecialchars($user_data['umur']) ?></p>
        <p><strong>Asal Sekolah:</strong> <?= htmlspecialchars($user_data['asal_sekolah']) ?></p>

        <!-- Test History Section -->
        <h3>Riwayat Tes Jurusan</h3>
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

        <a href="index.php" class="btn btn-custom mt-4">Kembali ke Beranda</a>
    </div>
</body>
</html>
