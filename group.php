<?php
// group.php - Displays questions in a specific group
session_start();
include "./database.php"; // Ensure database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the group ID from the URL
if (!isset($_GET['group_id']) || empty($_GET['group_id'])) {
    echo "Invalid group.";
    exit;
}

$group_id = (int)$_GET['group_id'];

// Fetch group details
$group_query = "SELECT name, description FROM groups WHERE id = $group_id";
$group_result = pg_query($db, $group_query);
$group = pg_fetch_assoc($group_result);

if (!$group) {
    echo "Group not found.";
    exit;
}

// Handle question submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) {
    $question = pg_escape_string($db, $_POST['question']);
    $query = "INSERT INTO questions (question, user_id, group_id) VALUES ('$question', $user_id, $group_id)";
    pg_query($db, $query);
    header("Location: group.php?group_id=$group_id");
    exit;
}

// Handle question deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_qid'])) {
    $delete_qid = (int)$_POST['delete_qid'];

    // Ensure the user is deleting their own question
    $delete_query = "DELETE FROM questions WHERE q_id = $delete_qid AND user_id = $user_id";
    pg_query($db, $delete_query);

    header("Location: group.php?group_id=$group_id");
    exit;
}

// Fetch all questions in this group
$query = "SELECT q.q_id, q.question, u.username, q.user_id, q.created_at 
          FROM questions q 
          JOIN users u ON q.user_id = u.id 
          WHERE q.group_id = $group_id 
          ORDER BY q.created_at DESC";
$result = pg_query($db, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/group.css">
    <title><?php echo htmlspecialchars($group['name']); ?> - Questions</title>
</head>

<body>

    <div class="questions-container">
        <h2><?php echo htmlspecialchars($group['name']); ?></h2>
        <p><?php echo htmlspecialchars($group['description']); ?></p>

        <h3>Questions in this group</h3>
        <?php while ($row = pg_fetch_assoc($result)): ?>
            <p>
                <strong><?php echo htmlspecialchars($row['username']); ?>:</strong>
                <?php echo htmlspecialchars($row['question']); ?>
                <br><a href='answers.php?q_id=<?php echo $row['q_id']; ?>'>View Answers</a>

                <?php if ($row['user_id'] == $user_id): ?>
                    <!-- Show delete button only if the current user is the asker -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_qid" value="<?php echo $row['q_id']; ?>">
                <button type="submit" style="color:red;">Delete</button>
            </form>
        <?php endif; ?>

    <?php endwhile; ?>
    </div>

    <div class="ask-container">
        <h3>Ask a Question</h3>
        <form method="POST">
            <input type="text" name="question" placeholder="Ask your question..." required>
            <button type="submit">Submit</button>
        </form>
    </div>

    <br>
    <a href="forum.php">Back to Groups</a>

</body>

</html>