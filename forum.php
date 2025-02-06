<?php
// forum.php - Forum Page
session_start();
include "./database.php"; // Ensure database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle group creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_name'])) {
    $group_name = pg_escape_string($db, $_POST['group_name']);
    $description = pg_escape_string($db, $_POST['description']);

    $query = "INSERT INTO groups (name, description, user_id) VALUES ('$group_name', '$description', $user_id)";
    pg_query($db, $query);
    header("Location: forum.php");
    exit;
}

// Handle group deletion (only by creator)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_group_id'])) {
    $delete_group_id = (int)$_POST['delete_group_id'];

    // Ensure the user is the creator of the group before deleting
    $delete_query = "DELETE FROM groups WHERE id = $delete_group_id AND user_id = $user_id";
    pg_query($db, $delete_query);

    header("Location: forum.php");
    exit;
}

// Fetch all groups
$query = "SELECT g.id, g.name, g.description, g.user_id, u.username 
          FROM groups g 
          JOIN users u ON g.user_id = u.id 
          ORDER BY g.id DESC";
$result = pg_query($db, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/forum.css">
    <title>Discussion Groups</title>
</head>

<body>

    <div class="groups-container">
        <h2>Discussion Groups</h2>

        <!-- Display groups -->
        <?php while ($row = pg_fetch_assoc($result)): ?>
        <p>
            <strong><?php echo htmlspecialchars($row['name']); ?></strong> (Created by:
            <?php echo htmlspecialchars($row['username']); ?>)
            <br> <?php echo htmlspecialchars($row['description']); ?>
            <br><a href='group.php?group_id=<?php echo $row['id']; ?>'>Enter Group</a>

            <?php if ($row['user_id'] == $user_id): ?>
        </p>
        <!-- Show delete button only if the current user created the group -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="delete_group_id" value="<?php echo $row['id']; ?>">
            <button type="submit" style="color:red;">Delete</button>
        </form>
        <?php endif; ?>

        <?php endwhile; ?>
    </div>

    <!-- Create a new group -->
    <div class="new-group">
        <h3>Create a New Group</h3>
        <form method="POST">
            <input type="text" name="group_name" placeholder="Group Name" required>
            <input type="text" name="description" placeholder="Description (Optional)">
            <button type="submit">Create Group</button>
        </form>
    </div>

    <br>
    <a href="./dashboard.php">Back to Dashboard</a>
</body>

</html>