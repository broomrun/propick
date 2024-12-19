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
        .carousel-inner img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .carousel-caption {
            position: absolute;
            bottom: 20%;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 90%;
        }
        .carousel-caption h5 {
            font-size: 2rem;
            font-weight: bold;
        }
        .carousel-caption p {
            font-size: 1.2rem;
        }
        .circle-logo {
            border-radius: 50%;
            margin: 10px;
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .start-btn, .btn-custom {
            background-color: #B17457;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: auto;
            font-size: 1rem; /* Menjaga ukuran font seragam */
        }
        .start-btn:hover, .btn-custom:hover {
            background-color: #8F5B40;
        }
        /* Styling Modal yang Dirapikan */
        .modal-content {
        background-color: #FAEBD7;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        padding: 20px;
    }

    .modal-header {
        background-color: #8B4513;
        color: #FFF;
        font-size: 18px;
        font-weight: bold;
        border-bottom: 2px solid #DEB887;
        border-radius: 15px 15px 0 0;
        padding: 15px;
    }

    .modal-body {
        color: #5C4033;
        font-size: 16px;
        padding: 15px;
    }

    .form-control {
        margin-bottom: 15px;
        border: 1px solid #8B4513;
        border-radius: 10px;
        padding: 10px;
        font-size: 14px;
    }

    .modal-footer {
        text-align: right;
        border-top: 1px solid #DEB887;
        padding: 10px;
    }

    .close {
        font-size: 1.5rem;
        color: #FFF;
        opacity: 0.8;
        cursor: pointer;
    }

    .close:hover {
        opacity: 1;
    }

    .start-btn {
        background-color: #8B4513;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        margin-top: 10px;
    }

    .start-btn:hover {
        background-color: #5C4033;
    }
    
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">ProdiPicker</a>
</nav>

<!-- Carousel -->
<div id="carouselExample" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="assets/image1.jpg" alt="Slide 1">
            <div class="carousel-caption d-none d-md-block">
                <h5>Selamat Datang di ProdiPicker</h5>
                <p>Temukan program studi yang cocok untukmu!</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="assets/image2.jpg" alt="Slide 2">
            <div class="carousel-caption d-none d-md-block">
                <h5>Pilih Jalur Karirmu</h5>
                <p>Eksplorasi program terbaik untuk masa depanmu!</p>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </a>
    <a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </a>
</div>

<!-- University logos -->
<div class="d-flex justify-content-center flex-wrap mt-4">
    <img src="logo1.png" class="circle-logo" alt="Logo 1">
    <img src="logo2.png" class="circle-logo" alt="Logo 2">
    <img src="logo3.png" class="circle-logo" alt="Logo 3">
</div>

<!-- Button Mulai and Login/Sign Up Button (next to each other) -->
<div class="text-center mt-5">
    <div class="d-flex justify-content-center">
        <button class="start-btn" id="startButton" <?= $isLoggedIn ? '' : 'disabled' ?>>Mulai</button>
        <div class="auth-btns">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="btn btn-custom ml-2">Logout</a>
            <?php else: ?>
                <button id="openAuthModal" class="btn btn-custom ml-2">Login / Sign Up</button>
            <?php endif; ?>
        </div>
    </div>
</div>

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