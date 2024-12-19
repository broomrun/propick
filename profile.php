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

// Fetch the most recent test history for each major
$query_history = "
    SELECT * FROM test_history 
    WHERE user_id = $user_id
    AND date IN (SELECT MAX(date) FROM test_history WHERE user_id = $user_id GROUP BY major)
    ORDER BY date DESC
";
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
    <title>Propick</title>
    <link rel="stylesheet" href="navbar.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #FAF7F0;
            color: #333333;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }

        .profile-container {
            margin: 3rem auto;
            padding: 0;
            max-width: 800px;
        }

        .profile-header {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }

        .profile-info {
            background-color: #D8D2C2;
            padding: 2rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            transition: transform 0.3s ease;
        }

        .profile-info:hover {
            transform: translateY(-5px);
        }

        .profile-info p {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .profile-info strong {
            color: #B17457;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .btn-custom {
            background-color: #B17457;
            color: #FFFFFF;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #8F5B40;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(177, 116, 87, 0.2);
            color: #FFFFFF;
        }

        h2, h3 {
            color: #B17457;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .test-history-table {
            background-color: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #D8D2C2;
            border: none;
            color: #333333;
            font-weight: 600;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }

        .suitable {
            color: #28a745;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            background-color: rgba(40, 167, 69, 0.1);
            display: inline-block;
        }

        .not-suitable {
            color: #dc3545;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            background-color: rgba(220, 53, 69, 0.1);
            display: inline-block;
        }

        .empty-history {
            text-align: center;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            color: #666;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .profile-container {
                margin: 2rem 1rem;
            }
            
            .profile-header {
                padding: 1.5rem;
            }
            
            .table {
                font-size: 0.9rem;
            }
            
            .btn-custom {
                width: 100%;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php">Propick</a>
        <div class="ml-auto">
            <a href="index.php" class="btn btn-custom btn-sm">Beranda</a>
            <a href="profile.php" class="btn btn-custom btn-sm">Profil</a>
        </div>
    </nav>


    <div class="container profile-container">
        <div class="profile-header animate_animated animate_fadeIn">
            <h2>Profil Pengguna</h2>
            <div class="profile-info">
                <p><strong>Nama:</strong> <?= htmlspecialchars($user_data['nama']) ?></p>
                <p><strong>Umur:</strong> <?= htmlspecialchars($user_data['umur']) ?></p>
                <p><strong>Asal Sekolah:</strong> <?= htmlspecialchars($user_data['asal_sekolah']) ?></p>
            </div>
        </div>

        <div class="test-history-section animate_animated animatefadeIn animate_delay-1s">
            <h3>Riwayat Tes Jurusan</h3>
            <?php if ($test_history): ?>
                <div class="test-history-table">
                    <table class="table">
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
                                    <td class="font-weight-500"><?= $history['major'] ?></td>
                                    <td>
                                        <span class="<?= $history['suitable'] == 'COCOK' ? 'suitable' : 'not-suitable' ?>">
                                            <?= $history['suitable'] ?>
                                        </span>
                                    </td>
                                    <td><?= date("d-m-Y", strtotime($history['date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-history">
                    <p>Belum ada riwayat tes.</p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-custom">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>