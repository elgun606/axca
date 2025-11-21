<?php
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "mebel";
$port       = 3307;   // <<< BURDA SƏNİN PORTUN

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Verilənlər bazası bağlantı xətası: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
