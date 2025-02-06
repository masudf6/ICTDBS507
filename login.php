<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/form.css">
    <title>Welcome - Discussion Forum</title>
</head>

<body>

    <form action="./login.php" method="POST">
        <h1>Log In</h1>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>


</body>

</html>


<?php

session_start(); // Start session for login tracking
include "./database.php";

// Login User
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Check if the email exists
        $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = pg_query($db, $query);
        $user = pg_fetch_assoc($result);

        if ($user) {
            $_SESSION['user_id'] = $user['id']; // Save user ID in session
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "Please fill in all fields.";
    }
}
