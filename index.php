<?php
session_start();

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ./dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Welcome - Discussion Forum</title>
</head>

<body>

    <div class="container">


        <h1>Welcome to the Discussion Forum</h1>

        <a href="./login.php"><button>Login</button></a>
        <a href="./register.php"><button>Register</button></a>

    </div>

</body>

</html>