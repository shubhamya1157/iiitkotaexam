<?php

require __DIR__ . '/../../backend/exam.php';
require_once __DIR__ . '/../../backend/auth.php';
require_role('student', '../login.html');
require __DIR__ . '/../../backend/db.php';

$studentId = (int) $_SESSION['user_id'];
$quizId = filter_input(INPUT_GET, 'quiz', FILTER_VALIDATE_INT);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    try {
        if ($quizId && submitQuiz($db, $quizId, $studentId, $_POST['answers'] ?? null)) {
            redirect('dashboard.php?quiz=' . $quizId);
        }
        $error = 'The quiz is outside its submission window.';
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    }
}

$quizzes = $db->execute_query(
    'SELECT q.id, q.title, q.opens_at, q.closes_at, JSON_LENGTH(q.questions) AS total,
            a.score, UNIX_TIMESTAMP() AS server_time
     FROM quizzes q LEFT JOIN quiz_attempts a ON a.quiz_id = q.id AND a.student_id = ?
     ORDER BY q.opens_at DESC, q.id DESC',
    [$studentId]
)->fetch_all(MYSQLI_ASSOC);

$activeQuiz = null;
foreach ($quizzes as $quiz) {
    if ((int) $quiz['id'] === $quizId && $quiz['score'] === null &&
        $quiz['opens_at'] <= $quiz['server_time'] && $quiz['closes_at'] > $quiz['server_time']) {
        $activeQuiz = $quiz;
        break;
    }
}
$questions = [];
if ($activeQuiz) {
    $row = $db->execute_query('SELECT questions FROM quizzes WHERE id = ?', [$quizId])->fetch_assoc();
    $questions = json_decode($row['questions'], true, 512, JSON_THROW_ON_ERROR);
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
        <form action="../../backend/logout.php" method="post"><?= csrfField() ?><button class="secondary">Log out</button></form>
    </header>
    <?php if ($error): ?><p class="error" role="alert"><?= escape($error) ?></p><?php endif; ?>
    <?php if ($activeQuiz): ?>
        <section>
            <a href="dashboard.php">Back to quizzes</a>
            <h2><?= escape($activeQuiz['title']) ?></h2>
            <p>Submit by <?= dateLabel($activeQuiz['closes_at']) ?>. Each correct answer is worth one point.</p>
            <p id="quiz-timer" role="timer"
               data-deadline="<?= (int) $activeQuiz['closes_at'] ?>"
               data-server-time="<?= microtime(true) ?>"></p>
            <p id="quiz-timer-status" class="error" role="status"></p>
            <form method="post">
                <?= csrfField() ?>
                <?php foreach ($questions as $index => $question): ?>
                    <fieldset>
                        <legend><?= $index + 1 ?>. <?= escape($question['prompt']) ?></legend>
                        <?php foreach ($question['options'] as $option => $label): ?>
                            <label class="choice">
                                <input type="radio" name="answers[<?= $index ?>]" value="<?= $option ?>" required>
                                <?= escape($label) ?>
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
                    <h3><?= escape($quiz['title']) ?></h3>
                    <p><?= dateLabel($quiz['opens_at']) ?> to <?= dateLabel($quiz['closes_at']) ?> · <?= (int) $quiz['total'] ?> questions</p>
                    <?php if ($quiz['score'] !== null): ?>
                        <strong>Submitted · Score <?= (int) $quiz['score'] ?> / <?= (int) $quiz['total'] ?></strong>
                    <?php elseif ($quiz['server_time'] < $quiz['opens_at']): ?>
                        <strong>Upcoming</strong>
                    <?php elseif ($quiz['server_time'] >= $quiz['closes_at']): ?>
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
