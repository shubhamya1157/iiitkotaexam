<?php

date_default_timezone_set('Asia/Kolkata');

// Save the quiz. An empty error message means it was saved.
function createQuiz($db, $form)
{
    $title = $form['title'] ?? '';
    $opens = strtotime($form['opens'] ?? '');
    $closes = strtotime($form['closes'] ?? '');
    $questions = $form['questions'] ?? [];

    if ($title == '' || !$opens || !$closes) {
        return 'Enter a title and valid opening and closing times.';
    }
    if ($opens >= $closes || $closes <= time()) {
        return 'Closing time must be in the future and after opening time.';
    }
    if (!is_array($questions) || count($questions) < 1 || count($questions) > 30) {
        return 'Add between 1 and 30 questions.';
    }
    foreach ($questions as $index => $question) {
        $options = $question['options'] ?? [];
        if (($question['prompt'] ?? '') == '' || !is_array($options) || count($options) != 4 ||
            in_array('', $options, true) || !in_array($question['correct'] ?? '', ['0', '1', '2', '3'], true)) {
            return 'Complete each question, its four options, and its correct answer.';
        }
        $questions[$index] = [
            'prompt' => $question['prompt'],
            'options' => array_values($options),
            'correct' => (int) $question['correct'],
        ];
    }

    // JSON stores the list of questions in one database column.
    // Escape text before putting it inside an SQL query.
    $title = mysqli_real_escape_string($db, $title);
    $questions = mysqli_real_escape_string($db, json_encode(array_values($questions)));
    mysqli_query($db, "INSERT INTO quizzes (title, opens_at, closes_at, questions)
                      VALUES ('$title', $opens, $closes, '$questions')");
    return '';
}

// Check the student's answers and save the score.
function submitQuiz($db, $quizId, $studentId, $answers)
{
    $quizId = (int) $quizId;
    $studentId = (int) $studentId;
    $result = mysqli_query($db, "SELECT * FROM quizzes WHERE id = $quizId");
    $quiz = mysqli_fetch_assoc($result);
    if (!$quiz) {
        return 'Quiz unavailable.';
    }

    $result = mysqli_query($db, "SELECT score FROM quiz_attempts
                               WHERE quiz_id = $quizId AND student_id = $studentId");
    if (mysqli_num_rows($result) > 0) {
        return '';
    }

    $questions = json_decode($quiz['questions'], true);
    $score = 0;
    foreach ($questions as $index => $question) {
        $answer = $answers[$index] ?? '';
        if (!in_array($answer, ['0', '1', '2', '3'], true)) {
            return 'Choose one answer for every question.';
        }
        if ($answer == $question['correct']) {
            $score++;
        }
    }

    $now = time();
    if ($now < $quiz['opens_at'] || $now >= $quiz['closes_at']) {
        return 'The quiz is outside its submission window.';
    }

    // IGNORE prevents a double click from saving the same attempt twice.
    mysqli_query($db, "INSERT IGNORE INTO quiz_attempts (quiz_id, student_id, score, submitted_at)
                      VALUES ($quizId, $studentId, $score, $now)");
    return '';
}
