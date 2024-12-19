<?php
include 'config.php';

// Fetch major details and questions as before
$nama = htmlspecialchars(trim($_POST['nama']));
$umur = (int) $_POST['umur'];
$asal_sekolah = htmlspecialchars(trim($_POST['asal_sekolah']));
$jurusan = htmlspecialchars(trim($_POST['jurusan']));

// Validate inputs
if (empty($nama) || empty($umur) || empty($asal_sekolah) || empty($jurusan)) {
    die("Error: All fields are required.");
}

if ($umur < 14 || $umur > 25) {
    die("Error: Age must be between 14 and 25.");
}

// Save user data to the database
$stmt = $conn->prepare("INSERT INTO users (nama, umur, asal_sekolah, jurusan) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $nama, $umur, $asal_sekolah, $jurusan);
$stmt->execute();
$user_id = $conn->insert_id;

// Fetch major and questions as before
$query = "SELECT * FROM majors WHERE nama_jurusan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $jurusan);
$stmt->execute();
$result = $stmt->get_result();
$major = $result->fetch_assoc();

$query = "SELECT * FROM questions WHERE major_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $major['id']);
$stmt->execute();
$questions = $stmt->get_result();
$questions_array = $questions->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propick</title>
    <link rel="stylesheet" href="navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* Global Styles */
    body {
        background-color: #FAF7F0;
        color: #000000;
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        min-height: 100vh;
    }
    /* Quiz Container */
    .quiz-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 70px);
        padding: 2rem 1rem;
    }

    .quiz-box {
        background-color: #D8D2C2;
        padding: 2.5rem;
        border-radius: 16px;
        text-align: center;
        width: 100%;
        max-width: 600px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    /* Question Styling */
    .question-box {
        background-color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }

    .question-box:hover {
        transform: translateY(-2px);
    }

    .question-box h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: #333;
        line-height: 1.4;
    }

    /* Button Styling */
    .btn-custom {
        background-color: #B17457;
        color: #FFFFFF;
        border: none;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-custom:hover {
        background-color: #8F5B40;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(177, 116, 87, 0.2);
    }

    .btn-custom:active {
        transform: translateY(0);
    }

    .btn-block {
        margin-bottom: 1rem;
        width: 100%;
    }

    .btn-block:last-child {
        margin-bottom: 0;
    }

    /* Navigation Buttons */
    .d-flex {
        margin-top: 2rem;
        gap: 1rem;
    }

    #prevButton, #nextButton {
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    #prevButton {
        background-color: #D8D2C2;
        border: 1px solid #B17457;
        color: #B17457;
    }

    #prevButton:hover {
        background-color: #ccc6b6;
    }

    #nextButton {
        background-color: #B17457;
    }

    #nextButton:hover {
        background-color: #8F5B40;
    }

    /* Submit Button */
    #submitBtn {
        margin-top: 2rem;
        padding: 1rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        background-color: #B17457;
        border-radius: 8px;
    }

    #submitBtn:disabled {
        background-color: #D8D2C2;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Major Box Styling */
    .question-box h3 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #B17457;
        margin-bottom: 0;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .quiz-box {
            padding: 1.5rem;
        }
        
        .question-box {
            padding: 1.5rem;
        }
        
        .question-box h4 {
            font-size: 1.1rem;
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

<div class="quiz-container">
    <div class="quiz-box">
        <div class="question-box">
            <h3>Jurusan yang Dipilih: <?= htmlspecialchars($jurusan) ?></h3>
        </div>

        <form id="quizForm" action="check_eligibility.php" method="POST">
            <input type="hidden" name="jurusan" value="<?= htmlspecialchars($jurusan) ?>">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">

            <?php foreach ($questions_array as $index => $question): ?>
                <div class="form-group question-box" id="question-<?= $index ?>" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
                    <h4><?= htmlspecialchars($question['pertanyaan']) ?></h4>
                    <button type="button" class="btn btn-custom btn-block" onclick="answerQuestion('ya', <?= $index ?>)">Ya</button>
                    <button type="button" class="btn btn-custom btn-block" onclick="answerQuestion('tidak', <?= $index ?>)">Tidak</button>
                </div>
                <input type="hidden" name="answer_<?= $question['id'] ?>" id="answer_<?= $index ?>">
            <?php endforeach; ?>

            <div class="d-flex justify-content-between btn-navigation">
                <button type="button" id="prevButton" class="btn btn-secondary" onclick="prevQuestion()" style="display: none;">Previous</button>
                <button type="button" id="nextButton" class="btn btn-primary" onclick="nextQuestion()">Next</button>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="submitBtn" style="display: none;" disabled>Kirim Jawaban</button>
        </form>
    </div>
</div>

<script>
    let currentQuestionIndex = 0;
    const totalQuestions = <?= count($questions_array) ?>;

    function answerQuestion(answer, index) {
        document.getElementById(answer_${index}).value = answer;
        nextQuestion();
    }

    function nextQuestion() {
        if (currentQuestionIndex < totalQuestions - 1) {
            document.getElementById(question-${currentQuestionIndex}).style.display = 'none';
            currentQuestionIndex++;
            document.getElementById(question-${currentQuestionIndex}).style.display = 'block';
            toggleButtons();
        } else {
            document.getElementById('submitBtn').disabled = false;
        }
    }

    function prevQuestion() {
        if (currentQuestionIndex > 0) {
            document.getElementById(question-${currentQuestionIndex}).style.display = 'none';
            currentQuestionIndex--;
            document.getElementById(question-${currentQuestionIndex}).style.display = 'block';
            toggleButtons();
        }
    }

    function toggleButtons() {
        document.getElementById('prevButton').style.display = currentQuestionIndex > 0 ? 'inline-block' : 'none';
        document.getElementById('nextButton').style.display = currentQuestionIndex < totalQuestions - 1 ? 'inline-block' : 'none';
        document.getElementById('submitBtn').style.display = currentQuestionIndex === totalQuestions - 1 ? 'block' : 'none';
    }

    toggleButtons();
</script>

</body>
</html>