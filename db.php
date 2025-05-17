<?php
$host = 'fdb1028.awardspace.net';
$user = '4635349_moviecrud';
$password = 'nQEc])wW28usry!3';
$dbname = '4635349_moviecrud';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
