<?php

require __DIR__ . '/../../backend/exam.php';
require __DIR__ . '/../../backend/auth.php';
require_role('student', '../login.html');
require __DIR__ . '/../../backend/db.php';

$studentId = (int) $_SESSION['user_id'];
$quizId = (int) ($_GET['quiz'] ?? 0);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = submitQuiz($db, $quizId, $studentId, $_POST['answers'] ?? []);
    if ($error == '') {
        header('Location: dashboard.php?quiz=' . $quizId);
        exit;
    }
}

// Read quizzes with this student's score, if already submitted.
$result = mysqli_query($db, "SELECT q.*, JSON_LENGTH(q.questions) AS total, a.score
                            FROM quizzes q LEFT JOIN quiz_attempts a
                            ON a.quiz_id = q.id AND a.student_id = $studentId
                            ORDER BY q.opens_at DESC, q.id DESC");
$quizzes = [];
$activeQuiz = null;
$now = time();
while ($quiz = mysqli_fetch_assoc($result)) {
    $quizzes[] = $quiz;
    if ($quiz['id'] == $quizId && $quiz['score'] === null &&
        $now >= $quiz['opens_at'] && $now < $quiz['closes_at']) {
        $activeQuiz = $quiz;
    }
}
$questions = [];
if ($activeQuiz) {
    $questions = json_decode($activeQuiz['questions'], true);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student dashboard | IIIT Kota Exams</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/exam.css">
</head>
<body>
<main>
    <header>
        <div><p class="eyebrow">IIIT Kota Exams</p><h1>Student dashboard</h1></div>
        <form action="../../backend/logout.php" method="post"><button class="secondary">Log out</button></form>
    </header>
    <?php if ($error): ?><p class="error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($activeQuiz): ?>
        <section>
            <a href="dashboard.php">Back to quizzes</a>
            <h2><?= htmlspecialchars($activeQuiz['title']) ?></h2>
            <p>Submit by <?= date('d M Y, h:i A', $activeQuiz['closes_at']) ?> IST. Each correct answer is worth one point.</p>
            <p id="quiz-timer" role="timer"
               data-deadline="<?= (int) $activeQuiz['closes_at'] ?>"
               data-server-time="<?= microtime(true) ?>"></p>
            <p id="quiz-timer-status" class="error" role="status"></p>
            <form method="post">
                <?php foreach ($questions as $index => $question): ?>
                    <fieldset>
                        <legend><?= $index + 1 ?>. <?= htmlspecialchars($question['prompt']) ?></legend>
                        <?php foreach ($question['options'] as $option => $label): ?>
                            <label class="choice">
                                <input type="radio" name="answers[<?= $index ?>]" value="<?= $option ?>" required>
                                <?= htmlspecialchars($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
                <button id="submit-quiz">Submit quiz</button>
            </form>
        </section>
    <?php else: ?>
        <section>
            <h2>Quizzes</h2>
            <?php if (!$quizzes): ?><p>No quizzes scheduled yet.</p><?php endif; ?>
            <?php foreach ($quizzes as $quiz): ?>
                <article>
                    <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                    <p><?= date('d M Y, h:i A', $quiz['opens_at']) ?> to <?= date('d M Y, h:i A', $quiz['closes_at']) ?> IST · <?= $quiz['total'] ?> questions</p>
                    <?php if ($quiz['score'] !== null): ?>
                        <strong>Submitted · Score <?= (int) $quiz['score'] ?> / <?= (int) $quiz['total'] ?></strong>
                    <?php elseif ($now < $quiz['opens_at']): ?>
                        <strong>Upcoming</strong>
                    <?php elseif ($now >= $quiz['closes_at']): ?>
                        <strong>Closed</strong>
                    <?php else: ?>
                        <a class="button" href="?quiz=<?= (int) $quiz['id'] ?>">Take quiz</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php if ($activeQuiz): ?>
    <script src="../js/quiz-timer.js" defer></script>
<?php endif; ?>
</body>
</html>
