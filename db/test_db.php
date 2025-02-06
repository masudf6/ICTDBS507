<?php
include "../models/database.php"; // Include the database connection

$query = "SELECT 'Database connection successful!' AS message";
$result = pg_query($pdo, $query);

if ($result) {
    $row = pg_fetch_assoc($result);
    echo $row['message']; // Should print: Database connection successful!
} else {
    echo "Database query failed.";
}
