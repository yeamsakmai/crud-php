<?php
$host = 'sql205.infinityfree.com';
$user = 'if0_38988916';
$password = 'esozPC9cKYVi';
$dbname = 'if0_38988916_phpcrud';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
