<?php
session_start();  // Make sure session is started at the very beginning
include 'config.php';

// Fetch data from the database for majors
$query = "SELECT * FROM majors";
$majors = $conn->query($query);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userData = $isLoggedIn ? [
    'nama' => $_SESSION['nama'],
    'umur' => $_SESSION['umur'],
    'asal_sekolah' => $_SESSION['asal_sekolah'],
] : null;

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'signup') {
        // Process the signup form
        $nama = sanitizeInput($_POST['nama']);
        $umur = (int)sanitizeInput($_POST['umur']);
        $asal_sekolah = sanitizeInput($_POST['asal_sekolah']);
        $email = sanitizeInput($_POST['email']);
        $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_BCRYPT);

        // Check if email already exists
        $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Email sudah terdaftar!";
        } else {
            // Insert new user data into database
            $query = "INSERT INTO users (nama, umur, asal_sekolah, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sisss", $nama, $umur, $asal_sekolah, $email, $password);

            if ($stmt->execute()) {
                // Store user data in session after successful sign-up
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['nama'] = $nama;
                $_SESSION['umur'] = $umur;
                $_SESSION['asal_sekolah'] = $asal_sekolah;
                echo "Pendaftaran berhasil!";
            } else {
                echo "Terjadi kesalahan: " . $stmt->error;
            }
        }
    } elseif ($action === 'login') {
        // Process the login form
        $email = sanitizeInput($_POST['email']);
        $password = sanitizeInput($_POST['password']);
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Store user data in session after successful login
                if ($user['umur'] < 15 || $user['umur'] > 21) {
                    echo "Login gagal! Umur Anda tidak sesuai dengan kriteria.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['umur'] = $user['umur'];
                    $_SESSION['asal_sekolah'] = $user['asal_sekolah'];
                    header("Location: index.php");
                    exit();
                }
            } else {
                echo "Password salah!";
            }
        } else {
            echo "Email tidak ditemukan!";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProdiPicker</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FAF7F0;
            color: #000000;
        }
        .navbar {
            background-color: #B17457;
        }
        .circle-logo {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .start-btn {
            background-color: #B17457;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .start-btn:disabled {
            background-color: #CCC;
            cursor: not-allowed;
        }
        .btn-custom {
            background-color: #B17457;
            color: #FFFFFF;
        }
        .btn-custom:hover {
            background-color: #8F5B40;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">ProdiPicker</a>
    <div class="ml-auto">
        <?php if ($isLoggedIn): ?>
            <span class="navbar-text">Welcome, <?= htmlspecialchars($userData['nama']) ?>!</span>
            <a href="logout.php" class="btn btn-custom ml-2">Logout</a>
        <?php else: ?>
            <button id="openAuthModal" class="btn btn-custom">Login / Sign Up</button>
        <?php endif; ?>
    </div>
</nav>

<!-- Login Modal -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" id="closeModal">&times;</span>
            <h2>Login</h2>
        </div>
        <div id="loginForm" class="modal-body">
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                    <span class="toggle-password" id="toggleLoginPassword">&#128065;</span>
                </div>
                <button type="submit" class="start-btn">Login</button>
            </form>
            <p>Don't have an account? <a href="#" id="switchToSignup">Sign Up Now</a></p>
        </div>

        <div id="signupForm" class="modal-body" style="display: none;">
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="signup">
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
                <input type="number" name="umur" placeholder="Umur" required>
                <select name="asal_sekolah" required>
                    <option value="" disabled selected>Pilih Asal Sekolah</option>
                    <option value="SMA">SMA</option>
                    <option value="SMK">SMK</option>
                </select>
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <span class="toggle-password" id="togglePassword">&#128065;</span>
                </div>
                <button type="submit" class="start-btn">Sign Up</button>
            </form>
            <p>Already have an account? <a href="#" id="switchToLogin">Login Now</a></p>
        </div>
    </div>
</div>

<!-- Mulai Modal -->
<div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Masukkan Data Diri</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userInfoForm" action="process.php" method="POST">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" name="nama" id="nama" value="<?= $userData['nama'] ?? '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="umur">Umur</label>
                        <input type="text" name="umur" value="<?= isset($_SESSION['umur']) ? $_SESSION['umur'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="asal_sekolah">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah" value="<?= isset($_SESSION['asal_sekolah']) ? $_SESSION['asal_sekolah'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="jurusan">Jurusan Pilihan</label>
                        <select class="form-control" name="jurusan" id="jurusan" required>
                            <?php while ($major = $majors->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($major['nama_jurusan']) ?>">
                                    <?= htmlspecialchars($major['nama_jurusan']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-custom btn-block">Mulai Quiz</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Button Mulai (disabled until logged in) -->
<div class="text-center mt-5">
    <button class="start-btn" id="startButton" <?= $isLoggedIn ? '' : 'disabled' ?>>Mulai</button>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        // Show Modal for Start button
        $('#startButton').click(function () {
            if (<?= $isLoggedIn ? 'true' : 'false' ?>) {
                $('#userInfoModal').modal('show');
            } else {
                alert("Please log in first!");
            }
        });

        // Show Login Modal
        $('#openAuthModal').click(function () {
            $('#authModal').show();
        });

        // Switch to Signup Form
        $('#switchToSignup').click(function () {
            $('#loginForm').hide();
            $('#signupForm').show();
        });

        // Switch to Login Form
        $('#switchToLogin').click(function () {
            $('#signupForm').hide();
            $('#loginForm').show();
        });

        // Close Modal
        $('#closeModal').click(function () {
            $('#authModal').hide();
        });
    });
</script>
</body>
</html>
