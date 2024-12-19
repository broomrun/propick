<?php
// Bagian PHP
session_start();
$userName = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name']) && isset($_POST['age'])) {
        $_SESSION['name'] = htmlspecialchars($_POST['name']);
        $_SESSION['age'] = intval($_POST['age']);
        $userName = $_SESSION['name'];
        echo "<script>alert('Selamat datang, $userName!');</script>";
    }
}
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

<!-- Modal: Login -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="age">Umur</label>
                        <input type="number" class="form-control" id="age" name="age" min="14" max="25" required>
                    </div>
                    <div class="form-group">
                        <label for="loginSchoolType">Asal Sekolah</label>
                        <select class="form-control" id="loginSchoolType" required>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-custom btn-block">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Start Test -->
<div class="modal fade" id="testModal" tabindex="-1" role="dialog" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testModalLabel">Hi, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>! Mau tes prodi apa?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="quizMajor">
                    <option value="informatika">Informatika</option>
                    <option value="akuntansi">Akuntansi</option>
                    <option value="hukum">Hukum</option>
                    <option value="medis">Medis</option>
                    <option value="psikologi">Psikologi</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-custom" id="startQuiz">Mulai</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    document.getElementById('testButton').disabled = <?php echo isset($_SESSION['name']) ? 'false' : 'true'; ?>;
    document.getElementById('startQuiz').addEventListener('click', function() {
        const selectedMajor = document.getElementById('quizMajor').value;
        window.location.href = `quiz.php?major=${selectedMajor}`;
    });
</script>

</body>
</html>


