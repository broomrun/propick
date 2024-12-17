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
    <title>Pertanyaan untuk Jurusan <?= htmlspecialchars($jurusan) ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FAF7F0;
            color: #000000;
        }
        .navbar {
            background-color: #B17457;
        }
        .quiz-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .quiz-box {
            background-color: #D8D2C2;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }
        .question-box {
            background-color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: #B17457;
            color: #FFFFFF;
            border: none;
        }
        .btn-custom:hover {
            background-color: #8F5B40;
        }
        .btn-block {
            margin-bottom: 10px;
        }
        .question-box button:disabled {
            background-color: #D8D2C2;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">ProdiPicker</a>
</nav>

<!-- Quiz Section -->
<div class="quiz-container">
    <div class="quiz-box">
        <!-- Major Box -->
        <div class="question-box">
            <h3>Jurusan yang Dipilih: <?= htmlspecialchars($jurusan) ?></h3>
        </div>

        <!-- Questions -->
        <form id="quizForm" action="check_eligibility.php" method="POST">
            <input type="hidden" name="jurusan" value="<?= htmlspecialchars($jurusan) ?>">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">

            <?php foreach ($questions_array as $index => $question): ?>
                <div class="form-group question-box" id="question-<?= $index ?>" style="display: <?= $index == 0 ? 'block' : 'none' ?>;">
                    <h4><?= htmlspecialchars($question['pertanyaan']) ?></h4>
                    <button type="button" class="btn btn-custom btn-block" onclick="answerQuestion('ya', <?= $index ?>)">Ya</button>
                    <button type="button" class="btn btn-custom btn-block" onclick="answerQuestion('tidak', <?= $index ?>)">Tidak</button>
                </div>
                <!-- Hidden answer field for each question -->
                <input type="hidden" name="answer_<?= $question['id'] ?>" id="answer_<?= $index ?>">
            <?php endforeach; ?>

            <div class="d-flex justify-content-between">
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
        // Save the answer for this question in the hidden input field
        document.getElementById(`answer_${index}`).value = answer;

        // Show next question
        nextQuestion();
    }

    function nextQuestion() {
        if (currentQuestionIndex < totalQuestions - 1) {
            // Hide current question
            document.getElementById(`question-${currentQuestionIndex}`).style.display = 'none';

            // Show next question
            currentQuestionIndex++;
            document.getElementById(`question-${currentQuestionIndex}`).style.display = 'block';

            // Show/Hide buttons
            toggleButtons();
        } else {
            // Enable submit button only when all questions are answered
            document.getElementById('submitBtn').disabled = false;
        }
    }

    function prevQuestion() {
        if (currentQuestionIndex > 0) {
            // Hide current question
            document.getElementById(`question-${currentQuestionIndex}`).style.display = 'none';

            // Show previous question
            currentQuestionIndex--;
            document.getElementById(`question-${currentQuestionIndex}`).style.display = 'block';

            // Show/Hide buttons
            toggleButtons();
        }
    }

    function toggleButtons() {
        // Show/Hide previous and next buttons
        document.getElementById('prevButton').style.display = currentQuestionIndex > 0 ? 'inline-block' : 'none';
        document.getElementById('nextButton').style.display = currentQuestionIndex < totalQuestions - 1 ? 'inline-block' : 'none';
        document.getElementById('submitBtn').style.display = currentQuestionIndex === totalQuestions - 1 ? 'block' : 'none';
    }

    // Initialize button visibility
    toggleButtons();
</script>

</body>
</html>
