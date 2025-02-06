<?php

// database.php - Database Connection
$host = "aws-0-ap-southeast-2.pooler.supabase.com";
$dbname = "postgres";
$user = "postgres.mvpweszhbkmjqkgudisz";
$password = "ICTDBS507@Abbey";

$db = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$db) {
    die("Database connection failed");
}
