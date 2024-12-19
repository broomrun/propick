<?php
include 'config.php';

// Fetch data from database
$query = "SELECT * FROM majors";
$majors = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProdiPicker</title>
    <!-- Bootstrap CSS -->
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
        }
        .circle-logo {
            border-radius: 50%;
            margin: 10px;
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .logo-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .start-btn {
            background-color: #B17457;
            color: #FFF;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .start-btn:hover {
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

<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">ProdiPicker</a>
</nav>

<div id="carouselExample" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="image1.jpg" class="d-block w-100" alt="carousel image 1">
        </div>
        <div class="carousel-item">
            <img src="image2.jpg" class="d-block w-100" alt="carousel image 2">
        </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </a>
    <a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </a>
</div>

<div class="logo-container">
    <img src="logo1.png" class="circle-logo" alt="logo1">
    <img src="logo2.png" class="circle-logo" alt="logo2">
    <img src="logo3.png" class="circle-logo" alt="logo3">
</div>

<div class="text-center mt-5">
    <button class="start-btn" id="startButton">Mulai</button>
</div>

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
                    <!-- Input Nama -->
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" name="nama" id="nama" required>
                    </div>
                    
                    <!-- Input Umur -->
                    <div class="form-group">
                        <label for="umur">Umur</label>
                        <input type="number" class="form-control" name="umur" id="umur" min="14" max="25" required>
                    </div>
                    
                    <!-- Pilihan Asal Sekolah -->
                    <div class="form-group">
                        <label for="asal_sekolah">Asal Sekolah</label>
                        <select class="form-control" name="asal_sekolah" id="asal_sekolah" required>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>
                    </div>
                    
                    <!-- Pilihan Jurusan -->
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
                    
                    <!-- Tombol Submit -->
                    <button type="submit" class="btn btn-custom btn-block">Mulai Quiz</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#startButton').on('click', function() {
            $('#userInfoModal').modal('show');
        });
    });
</script>

</body>
</html>
