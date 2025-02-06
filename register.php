<?php

// register.php - User Registration
session_start();
include "database.php";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (name, username, email, password) VALUES ('$name', '$username', '$email', '$password')";
    $result = pg_query($db, $query);

    if ($result) {
        header("Location: ./login.php");
        exit;
    } else {
        echo "Registration failed.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/form.css">
</head>

<body>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
</body>

</html>