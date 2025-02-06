<?php
// answers.php - Displays answers for a specific question
session_start();
include "./database.php"; // Ensure database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Get the question ID from the URL
if (!isset($_GET['q_id']) || empty($_GET['q_id'])) {
    echo "Invalid question.";
    exit;
}

$q_id = (int)$_GET['q_id'];

// Fetch question details
$question_query = "SELECT q.question, u.username, g.name AS group_name, q.group_id
                   FROM questions q
                   JOIN users u ON q.user_id = u.id
                   JOIN groups g ON q.group_id = g.id
                   WHERE q.q_id = $q_id";
$question_result = pg_query($db, $question_query);
$question = pg_fetch_assoc($question_result);

if (!$question) {
    echo "Question not found.";
    exit;
}

// Handle new answer submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['answer'])) {
    $answer = pg_escape_string($db, $_POST['answer']);
    $user_id = $_SESSION['user_id'];

    $query = "INSERT INTO answers (q_id, answer, user_id) VALUES ($q_id, '$answer', $user_id)";
    $result = pg_query($db, $query);

    if ($result) {
        header("Location: answers.php?q_id=$q_id"); // Refresh to show the new answer
        exit;
    } else {
        echo "Failed to post answer.";
    }
}

// Fetch all answers for this question
$answer_query = "SELECT a.answer, u.username, a.created_at 
                 FROM answers a
                 JOIN users u ON a.user_id = u.id
                 WHERE a.q_id = $q_id
                 ORDER BY a.created_at ASC";
$answer_result = pg_query($db, $answer_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/answers.css">
    <title>Answers</title>
</head>

<body>
    <h2>Group: <?php echo htmlspecialchars($question['group_name']); ?></h2>
    <div class="question-container">
        <h3>Question:</h3>
        <p><strong><?php echo htmlspecialchars($question['username']); ?>:</strong>
            <?php echo htmlspecialchars($question['question']); ?>
        </p>
    </div>

    <div class="answers-container">
        <h3>Answers</h3>
        <?php while ($row = pg_fetch_assoc($answer_result)): ?>
            <p>
                <strong><?php echo htmlspecialchars($row['username']); ?>:</strong>
                <?php echo htmlspecialchars($row['answer']); ?>
            </p>
        <?php endwhile; ?>
    </div>

    <div class="your-answer-container">
        <!-- Submit a new answer -->
        <h3>Your Answer</h3>
        <form method="POST">
            <textarea name="answer" placeholder="Write your answer..." required></textarea>
            <button type="submit">Submit Answer</button>
        </form>
    </div>

    <br>
    <a href="group.php?group_id=<?php echo $question['group_id']; ?>">Back to Questions</a>
</body>

</html>