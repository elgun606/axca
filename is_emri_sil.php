<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tapılmadı.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM is_emri_novleri WHERE id = ?");
if (!$stmt) {
    die("SQL Prepare Xətası: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: is_emri.php?silindi=1");
exit;
?>
