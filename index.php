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
    .btn-custom {
        background-color: #B17457;
        color: #FFFFFF;
    }
    .btn-custom:hover {
        background-color: #8F5B40;
    }

    /* Modal and form styling */
    .modal-content {
        background-color: #FFF;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        margin: auto;
    }
    .modal-header h2 {
        font-size: 1.5rem;
    }
    .modal-content form input,
    .modal-content form select {
        width: 100%;
        padding: 8px;
        margin: 10px 0;
        border: 1px solid #CCC;
        border-radius: 5px;
    }
    .password-container {
        position: relative;
    }
    .password-container input {
        padding-right: 40px;
    }
    .password-container .toggle-password {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
    }
    .start-btn {
        background-color: #B17457;
        color: white;
        border: none;
        padding: 10px 20px;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
        width: auto;
    }
    .start-btn:hover {
        background-color: #8F5B40;
    }

    .text-center button {
        padding: 8px 16px;
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
        <!-- Login Modal Header -->
        <div id="loginHeader" class="modal-header">
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

        <!-- Sign Up Modal Header (Hidden Initially) -->
        <div id="signupHeader" class="modal-header" style="display: none;">
            <span class="close" id="closeModalSignup">&times;</span>
            <h2>Sign Up</h2>
        </div>

        <!-- Sign Up Form (Hidden Initially) -->
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
                <!-- Confirm Password Field -->
                <div class="password-container">
                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
                    <span class="toggle-password" id="toggleConfirmPassword">&#128065;</span>
                </div>
                <button type="submit" class="start-btn">Sign Up</button>
            </form>
            <p>Already have an account? <a href="#" id="switchToLogin">Login Now</a></p>
        </div>
    </div>
</div>

<!-- User Info Modal -->
<div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Masukkan Data Diri</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userInfoForm" action="process.php" method="POST">
                    <input type="text" name="nama" placeholder="Nama Lengkap" value="<?= $userData['nama'] ?? '' ?>" readonly>
                    <input type="text" name="umur" placeholder="Umur" value="<?= isset($_SESSION['umur']) ? $_SESSION['umur'] : '' ?>" readonly>
                    <input type="text" name="asal_sekolah" placeholder="Asal Sekolah" value="<?= isset($_SESSION['asal_sekolah']) ? $_SESSION['asal_sekolah'] : '' ?>" readonly>
                    <select name="jurusan" required>
                        <?php while ($major = $majors->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($major['nama_jurusan']) ?>">
                                <?= htmlspecialchars($major['nama_jurusan']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="start-btn">Mulai Quiz</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-5">
    <button class="start-btn" id="startButton" <?= $isLoggedIn ? '' : 'disabled' ?>>Mulai</button>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        // Show Modal for Start button
        $('#startButton').click(function () {
            if (<?= json_encode($isLoggedIn) ?>) {
                $('#userInfoModal').modal('show');
            } else {
                alert("Please log in first!");
            }
        });

        // Open Login/Signup Modal
        $('#openAuthModal').click(function () {
            $('#authModal').fadeIn();
        });

        // Switch to Signup Form
        $('#switchToSignup').click(function (e) {
            e.preventDefault();
            $('#loginForm').hide();
            $('#signupForm').fadeIn();
        });

        // Switch to Login Form
        $('#switchToLogin').click(function (e) {
            e.preventDefault();
            $('#signupForm').hide();
            $('#loginForm').fadeIn();
        });

        // Close Modal
        $('#closeModal').click(function () {
            $('#authModal').fadeOut();
        });

        // Close modal if clicked outside of it
        $(window).click(function (e) {
            if ($(e.target).is('#authModal')) {
                $('#authModal').fadeOut();
            }
        });

        document.getElementById('switchToSignup').addEventListener('click', function() {
            document.getElementById('loginHeader').style.display = 'none';
            document.getElementById('signupHeader').style.display = 'block';
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('signupForm').style.display = 'block';
        });

        document.getElementById('switchToLogin').addEventListener('click', function() {
            document.getElementById('loginHeader').style.display = 'block';
            document.getElementById('signupHeader').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('signupForm').style.display = 'none';
        });


        // Toggle visibility of password fields
        function togglePasswordVisibility(toggleId, inputId) {
            $('#' + toggleId).click(function () {
                const input = $('#' + inputId);
                const type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).text(type === 'password' ? '🙈' : '👁️');
            });
        }

        // Apply password visibility toggling for each input
        togglePasswordVisibility('togglePassword', 'password');
        togglePasswordVisibility('toggleConfirmPassword', 'confirmPassword');
        togglePasswordVisibility('toggleLoginPassword', 'loginPassword');
    });
            // Form login validation and submission
            $('#formLogin').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("index2.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.text())
            .then((message) => {
                alert(message); 
                if (message.includes("Login berhasil")) {
                    location.reload(); 
                }
            });
        });

        // Form signup validation and submission
        $('#formSignup').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            const umur = parseInt(formData.get("umur"), 10);
            if (umur < 15 || umur > 21) {
                alert("Umur tidak sesuai dengan usia SMA/SMK!");
                return;
            }

            fetch("index.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.text())
            .then((message) => {
                alert(message);
                if (message.includes("Pendaftaran berhasil")) {
                    location.reload(); 
                }
            });
        });
</script>
</body>
</html>
