<?php

$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root';
$dbname = getenv('DB_NAME') ?: 'projectdb';

// Establish a connection using object-oriented MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");