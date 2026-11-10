<?php

date_default_timezone_set('Asia/Kolkata');

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect($path)
{
    header('Location: ' . $path, true, 303);
    exit;
}

function csrfField()
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . escape($_SESSION['csrf']) . '">';
}

function checkCsrf()
{
    $token = $_POST['csrf'] ?? null;
    if (!isset($_SESSION['csrf']) || !is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('Invalid form token. Reload the page and try again.');
    }
}

function dateLabel($timestamp)
{
    return date('d M Y, g:i A T', $timestamp);
}

function postedText($value)
{
    return is_string($value) ? trim($value) : '';
}

function parseSchedule($value)
{
    $text = postedText($value);
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $text);
    if (!$date || $date->format('Y-m-d\TH:i') !== $text) {
        throw new InvalidArgumentException('Enter valid opening and closing dates and times.');
    }
    return $date->getTimestamp();
}

function createQuiz($db, $form)
{
    $title = postedText($form['title'] ?? null);
    $opens = parseSchedule($form['opens'] ?? null);
    $closes = parseSchedule($form['closes'] ?? null);
    if ($title === '' || mb_strlen($title) > 150) {
        throw new InvalidArgumentException('Enter a title of up to 150 characters.');
    }
    if ($opens >= $closes || $closes <= time()) {
        throw new InvalidArgumentException('Closing time must be in the future and after opening time.');
    }

    $rows = $form['questions'] ?? null;
    if (!is_array($rows) || count($rows) < 1 || count($rows) > 30) {
        throw new InvalidArgumentException('Add between 1 and 30 questions.');
    }

    $questions = [];
    foreach ($rows as $row) {
        if (!is_array($row) || !is_array($row['options'] ?? null)) {
            throw new InvalidArgumentException('Each question needs four answer options.');
        }
        $prompt = postedText($row['prompt'] ?? null);
        $options = [];
        for ($index = 0; $index < 4; $index++) {
            $options[] = postedText($row['options'][$index] ?? null);
        }
        $correct = postedText($row['correct'] ?? null);
        if ($prompt === '' || mb_strlen($prompt) > 500 ||
            count(array_unique($options)) !== 4 || in_array('', $options, true) ||
            max(array_map('mb_strlen', $options)) > 200 ||
            !in_array($correct, ['0', '1', '2', '3'], true)) {
            throw new InvalidArgumentException('Each question needs a prompt, four distinct options, and a correct answer.');
        }
        $questions[] = ['prompt' => $prompt, 'options' => $options, 'correct' => (int) $correct];
    }

    $db->execute_query(
        'INSERT INTO quizzes (title, opens_at, closes_at, questions) VALUES (?, ?, ?, ?)',
        [$title, $opens, $closes, json_encode($questions, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)]
    );
}

function submitQuiz($db, $quizId, $studentId, $answers)
{
    $quiz = $db->execute_query('SELECT questions FROM quizzes WHERE id = ?', [$quizId])->fetch_assoc();
    if (!$quiz) {
        throw new InvalidArgumentException('Quiz unavailable.');
    }

    $score = 0;
    foreach (json_decode($quiz['questions'], true, 512, JSON_THROW_ON_ERROR) as $index => $question) {
        $answer = is_array($answers) ? ($answers[$index] ?? null) : null;
        if (!in_array($answer, ['0', '1', '2', '3'], true)) {
            throw new InvalidArgumentException('Choose one answer for every question.');
        }
        if ($answer === (string) $question['correct']) {
            $score++;
        }
    }

    try {
        $db->execute_query(
            'INSERT INTO quiz_attempts (quiz_id, student_id, score, submitted_at)
             SELECT id, ?, ?, UNIX_TIMESTAMP() FROM quizzes
             WHERE id = ? AND opens_at <= UNIX_TIMESTAMP() AND closes_at > UNIX_TIMESTAMP()',
            [$studentId, $score, $quizId]
        );
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() === 1062) {
            return true;
        }
        throw $error;
    }

    return $db->affected_rows === 1;
}
