<?php
include 'config.php';

// Fungsi untuk membersihkan data input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Cek apakah form yang dikirim adalah login atau sign-up
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'signup') {
        // Data sign-up
        $nama = sanitizeInput($_POST['nama']);
        $umur = (int)sanitizeInput($_POST['umur']);
        $asal_sekolah = sanitizeInput($_POST['asal_sekolah']);
        $email = sanitizeInput($_POST['email']);
        $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_BCRYPT); // Enkripsi password

        // Periksa apakah email sudah digunakan
        $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Email sudah terdaftar!";
        } else {
            // Masukkan data ke database
            $query = "INSERT INTO users (nama, umur, asal_sekolah, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sisss", $nama, $umur, $asal_sekolah, $email, $password);

            if ($stmt->execute()) {
                echo "Pendaftaran berhasil!";
            } else {
                echo "Terjadi kesalahan: " . $stmt->error;
            }
        }
    } elseif ($action === 'login') {
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
                if ($user['umur'] < 15 || $user['umur'] > 21) {
                    echo "Login gagal! Umur Anda tidak sesuai dengan kriteria SMA/SMK.";
                } else {
                    echo "Login berhasil! Selamat datang, " . $user['nama'];
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];

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

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Sign-Up Modal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Button untuk membuka modal -->
    <button id="openModal">Login / Sign Up</button>

    <!-- Modal -->
    <div id="authModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>

            <!-- Login Form -->
            <div id="loginForm">
                <h2>Login</h2>
                <form id="formLogin">
                    <input type="email" name="email" placeholder="Email" required>
                    <div class="password-container">
                        <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                        <span class="toggle-password" id="toggleLoginPassword">&#128065;</span>
                    </div>
                    <button type="submit">Login</button>
                </form>
                <p>Don't have an account? <a href="#" id="switchToSignup">Sign Up Now</a></p>
            </div>

            <!-- Sign-Up Form -->
            <div id="signupForm" style="display: none;">
                <h2>Sign Up</h2>
                <form id="formSignup">
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
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
                        <span class="toggle-password" id="toggleConfirmPassword">&#128065;</span>
                    </div>
                    <button type="submit">Sign Up</button>
                </form>
                <p>Already have an account? <a href="#" id="switchToLogin">Login Now</a></p>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>

