<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Giriş qadağandır.");
}

$id = intval($_GET['id']);

if ($id > 0) {
    $conn->query("DELETE FROM user_roles WHERE id = $id");
}

header("Location: roles.php");
exit;
