<?php

require __DIR__ . '/../../backend/exam.php';
require __DIR__ . '/../../backend/auth.php';
require_role('admin', '../login.html');
require __DIR__ . '/../../backend/db.php';

$error = '';
$draft = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = createQuiz($db, $_POST);
    if ($error == '') {
        header('Location: dashboard.php?created=1');
        exit;
    }
    $draft = $_POST;
}

// Read the quizzes and the selected quiz's results.
$quizzes = mysqli_query($db, 'SELECT id, title, opens_at, closes_at, JSON_LENGTH(questions) AS total
                             FROM quizzes ORDER BY opens_at DESC, id DESC');
$selectedId = (int) ($_GET['results'] ?? 0);
if ($selectedId > 0) {
    $results = mysqli_query($db, "SELECT a.student_id, u.email, a.score, a.submitted_at
                                 FROM quiz_attempts a LEFT JOIN users u ON u.id = a.student_id
                                 WHERE a.quiz_id = $selectedId ORDER BY a.score DESC, a.submitted_at");
}
$rows = $draft['questions'] ?? [[]];
if (!is_array($rows) || count($rows) == 0) {
    $rows = [[]];
}
$rows = array_values($rows);

function questionFields($index, $row = [])
{
    $name = "questions[$index]";
    ?>
    <fieldset>
        <legend>Question <?= $index + 1 ?></legend>
        <label>Prompt <textarea name="<?= $name ?>[prompt]" maxlength="500" required><?= htmlspecialchars($row['prompt'] ?? '') ?></textarea></label>
        <div class="grid">
            <?php for ($option = 0; $option < 4; $option++): ?>
                <label>Option <?= $option + 1 ?>
                    <input name="<?= $name ?>[options][<?= $option ?>]" maxlength="200" required value="<?= htmlspecialchars($row['options'][$option] ?? '') ?>">
                </label>
            <?php endfor; ?>
        </div>
        <label>Correct answer
            <select name="<?= $name ?>[correct]" required>
                <option value="">Choose an option</option>
                <?php for ($option = 0; $option < 4; $option++): ?>
                    <option value="<?= $option ?>" <?= (string) $option === ($row['correct'] ?? null) ? 'selected' : '' ?>>Option <?= $option + 1 ?></option>
                <?php endfor; ?>
            </select>
        </label>
    </fieldset>
    <?php
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard | IIIT Kota Exams</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/exam.css">
</head>
<body>
<main>
    <header>
        <div><p class="eyebrow">IIIT Kota Exams</p><h1>Admin dashboard</h1></div>
        <form action="../../backend/logout.php" method="post"><button class="secondary">Log out</button></form>
    </header>
    <section>
        <h2>Create a quiz</h2>
        <p>Students can take the quiz once during its scheduled window. All times are in India Standard Time (IST).</p>
        <?php if ($error): ?><p class="error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if (isset($_GET['created'])): ?><p class="success" role="status">Quiz scheduled successfully.</p><?php endif; ?>
        <form method="post">
            <label>Quiz title <input name="title" maxlength="150" required value="<?= htmlspecialchars($draft['title'] ?? '') ?>"></label>
            <div class="grid">
                <label>Opens at <input type="datetime-local" name="opens" required value="<?= htmlspecialchars($draft['opens'] ?? '') ?>"></label>
                <label>Closes at <input type="datetime-local" name="closes" required value="<?= htmlspecialchars($draft['closes'] ?? '') ?>"></label>
            </div>
            <div id="questions">
                <?php foreach ($rows as $index => $row): ?>
                    <?php questionFields($index, is_array($row) ? $row : []); ?>
                <?php endforeach; ?>
            </div>
            <div class="actions">
                <button type="button" class="secondary" id="add">Add question</button>
                <button type="button" class="secondary" id="remove">Remove last question</button>
                <button>Schedule quiz</button>
            </div>
        </form>
        <template id="question-template"><?php questionFields(0); ?></template>
    </section>
    <section>
        <h2>Scheduled quizzes and results</h2>
        <?php if (mysqli_num_rows($quizzes) == 0): ?><p>No quizzes scheduled yet.</p><?php endif; ?>
        <?php while ($quiz = mysqli_fetch_assoc($quizzes)): ?>
            <article>
                <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                <p><?= date('d M Y, h:i A', $quiz['opens_at']) ?> to <?= date('d M Y, h:i A', $quiz['closes_at']) ?> · <?= $quiz['total'] ?> questions</p>
                <a href="?results=<?= (int) $quiz['id'] ?>#quiz-<?= (int) $quiz['id'] ?>">View results</a>
                <?php if ((int) $quiz['id'] === $selectedId): ?>
                    <div id="quiz-<?= (int) $quiz['id'] ?>" class="table-wrap">
                        <h4>Submissions (<?= mysqli_num_rows($results) ?>)</h4>
                        <?php if (mysqli_num_rows($results) == 0): ?>
                            <p>No submissions yet.</p>
                        <?php else: ?>
                            <table>
                                <thead><tr><th>Student</th><th>Score</th><th>Submitted</th></tr></thead>
                                <tbody>
                                <?php while ($result = mysqli_fetch_assoc($results)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($result['email'] ?? ('Student #' . $result['student_id'])) ?></td>
                                        <td><?= (int) $result['score'] ?> / <?= (int) $quiz['total'] ?></td>
                                        <td><?= date('d M Y, h:i A', $result['submitted_at']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>
    </section>
</main>
<script src="../js/quiz-editor.js" defer></script>
</body>
</html>
