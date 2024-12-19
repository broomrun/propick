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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        .start-btn, .login-btn, .signup-btn {
            background-color: #B17457;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .start-btn:hover, .login-btn:hover, .signup-btn:hover {
            background-color: #8F5B40;
        }
        .modal-content {
            background-color: #D8D2C2;
            padding: 20px;
            border-radius: 10px;
        }
        .modal-header {
            border-bottom: 1px solid #8F5B40;
            background-color: #B17457;
            color: white;
        }
        .close {
            color: white;
            font-size: 1.5rem;
        }
        .btn-custom {
            background-color: #B17457;
            color: #FFFFFF;
            border: none;
        }
        .btn-custom:hover {
            background-color: #8F5B40;
        }
        
    </style>
</head>
<body>

<!-- Navbar -->
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

<!-- Buttons -->
<div class="d-flex justify-content-center mt-4">
    <button class="login-btn" data-toggle="modal" data-target="#loginModal">Daftar / Login</button>
    <button class="start-btn" id="testButton" disabled>Mulai Tes</button>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="loginForm" class="modal-body">
                <form method="POST" action="index.php" id="formLogin">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="toggleLoginPassword"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom btn-block">Login</button>
                </form>
                <p class="text-center mt-3">Belum punya akun? <a href="#" id="switchToSignup">Sign Up Sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Sign Up Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signupModalLabel">Sign Up</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php" id="formSignup">
                    <input type="hidden" name="action" value="signup">
                    <div class="form-group">
                        <label for="signupNama">Nama Lengkap</label>
                        <input type="text" class="form-control" id="signupNama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="signupUmur">Umur</label>
                        <input type="number" class="form-control" id="signupUmur" name="umur" required>
                    </div>
                    <div class="form-group">
                        <label for="signupAsalSekolah">Asal Sekolah</label>
                        <select class="form-control" id="signupAsalSekolah" name="asal_sekolah" required>
                            <option value="" disabled selected>Pilih Asal Sekolah</option>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="signupEmail">Email</label>
                        <input type="email" class="form-control" id="signupEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="signupPassword">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="signupPassword" name="password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="toggleSignupPassword"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="toggleConfirmPassword"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom btn-block">Sign Up</button>
                </form>
                <p class="text-center mt-3">Sudah punya akun? <a href="#" id="switchToLogin">Login Sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<!-- User Info Modal -->
<div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog" aria-labelledby="userInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userInfoModalLabel">Masukkan Data Diri</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="userInfoForm" action="process.php" method="POST">
                    <div class="form-group">
                        <label for="userName">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" id="userName" placeholder="Nama Lengkap" value="<?= $userData['nama'] ?? '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="userAge">Umur</label>
                        <input type="text" class="form-control" name="umur" id="userAge" placeholder="Umur" value="<?= isset($_SESSION['umur']) ? $_SESSION['umur'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="userSchool">Asal Sekolah</label>
                        <input type="text" class="form-control" name="asal_sekolah" id="userSchool" placeholder="Asal Sekolah" value="<?= isset($_SESSION['asal_sekolah']) ? $_SESSION['asal_sekolah'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="majorSelect">Pilih Jurusan</label>
                        <select class="form-control" name="jurusan" id="majorSelect" required>
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

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    // Assuming a boolean variable $isLoggedIn in PHP indicating the login status
    var isLoggedIn = <?= json_encode($isLoggedIn) ?>;
    
    // If user is logged in, enable the "Mulai Tes" button
    if (isLoggedIn) {
        $('#testButton').prop('disabled', false);
    }

    // Handle Start Test button click
    $('#testButton').click(function(e) {
        e.preventDefault();
        if (!isLoggedIn) { // Ensure the user is logged in before proceeding
            alert('Anda harus login atau sign up terlebih dahulu!');
            $('#loginModal').modal('show');
            return;
        }
        // If logged in, proceed with the test and show user info modal
        $('#userInfoModal').modal('show');
    });

    // Switch between login and signup modals
    $('#switchToSignup').click(function(e) {
        e.preventDefault();
        $('#loginModal').modal('hide');
        setTimeout(function() {
            $('#signupModal').modal('show');
        }, 500);
    });

    $('#switchToLogin').click(function(e) {
        e.preventDefault();
        $('#signupModal').modal('hide');
        setTimeout(function() {
            $('#loginModal').modal('show');
        }, 500);
    });

    // Toggle password visibility
    $('.toggle-password').click(function() {
        var inputField = $(this).closest('.input-group').find('input');
        var icon = $(this).find('i');
        if (inputField.attr('type') === 'password') {
            inputField.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            inputField.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    // Form validation for the signup modal
    $('#signupForm').submit(function(e) {
        e.preventDefault();
        var age = $('#signupUmur').val();
        if (age < 15 || age > 21) {
            alert('Usia anda tidak sesuai untuk jenjang SMA.');
        } else {
            // Proceed with form submission (handle sign up logic)
            $(this).unbind().submit();
        }
    });
});
</script>

</body>
</html>



