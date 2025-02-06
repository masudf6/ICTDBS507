<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php"); // Redirect to home if not logged in
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dash.css">
    <title>Dashboard</title>
</head>

<body>

    <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>


    <nav>
        <a href="profile.php">Profile</a> |
        <a href="forum.php">Forum</a> |
        <a href="messages.php">Messages</a> |
        <a href="./logout.php"><button>Logout</button></a>
    </nav>

</body>

</html>