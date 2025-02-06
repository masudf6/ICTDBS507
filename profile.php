<?php
session_start();
include "./database.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php");
    exit;
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, name FROM users WHERE id = $user_id";
$result = pg_query($db, $query);
$user = pg_fetch_assoc($result);


// Handle update profile
if (isset($_POST['update_profile'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $new_name = $_POST['new_name'];

    if (!empty($new_username) || !empty($new_password) || !empty($new_name)) {
        if (!empty($new_username)) {
            $new_username = pg_escape_string($db, $new_username);
            pg_query($db, "UPDATE users SET username = '$new_username' WHERE id = $user_id");
            $_SESSION['username'] = $new_username; // Update session
        }

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            pg_query($db, "UPDATE users SET password = '$hashed_password' WHERE id = $user_id");
        }

        if (!empty($new_name)) {
            $new_name = pg_escape_string($db, $new_name);
            pg_query($db, "UPDATE users SET name = '$new_name' WHERE id = $user_id");
            $_SESSION['username'] = $new_username; // Update session
        }

        echo "Profile updated!";
        header("Location: profile.php");
    } else {
        echo "Please fill at least one field.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/profile.css">
    <title>Profile</title>
</head>

<body>

    <div class="profile-container">
        <h1>Your Profile</h1>
        <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
        <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>

    </div>

    <div class="profile-update-container">
        <h2>Edit Profile</h2>
        <form action="profile.php" method="POST">
            <input type="text" name="new_name" placeholder="New Name">
            <input type="text" name="new_username" placeholder="New Username">
            <input type="password" name="new_password" placeholder="New Password">
            <button type="submit" name="update_profile">Update</button>
        </form>

    </div>

    <br>
    <a href="./dashboard.php">Back to Dashboard</a>

</body>

</html>