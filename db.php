<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'movie_dashboard';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
