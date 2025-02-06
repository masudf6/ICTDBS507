<?php
// forum.php - Forum Page
session_start();
include "./database.php"; // Ensure database connection

// Handle question submission BEFORE outputting HTML
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['question'])) {
    $question = pg_escape_string($db, $_POST['question']);
    $user_id = $_SESSION['user_id'];

    $query = "INSERT INTO questions (question, user_id) VALUES ('$question', $user_id)";
    $result = pg_query($db, $query);

    if ($result) {
        header("Location: forum.php"); // Refresh the page to show the new question
        exit;
    } else {
        echo "Failed to post question.";
    }
}

// Fetch all questions with user info
$query = "SELECT q.q_id, q.question, u.username 
          FROM questions q 
          JOIN users u ON q.user_id = u.id 
          ORDER BY q.created_at DESC";
$result = pg_query($db, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
</head>

<body>
    <h2>Forum</h2>

    <!-- Display questions -->
    <?php while ($row = pg_fetch_assoc($result)): ?>
        <p><strong><?php echo htmlspecialchars($row['username']); ?>:</strong>
            <?php echo htmlspecialchars($row['question']); ?>
            <a href='answers.php?q_id=<?php echo $row['q_id']; ?>'>View Answers</a>
        </p>
    <?php endwhile; ?>

    <!-- Ask a question -->
    <form method="POST">
        <input type="text" name="question" required>
        <button type="submit">Ask Question</button>
    </form>
</body>

</html>