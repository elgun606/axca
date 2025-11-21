<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

// Addımı tap
$step = $conn->query("SELECT * FROM is_emri_addimlari WHERE id = $id")->fetch_assoc();
if (!$step) die("Addım tapılmadı!");

// Addımın iş əmri ID-si
$is_id = intval($step['is_emri_id']);

// Bitir → status = bitdi, bitmə vaxtı yazılır
$conn->query("
    UPDATE is_emri_addimlari
    SET status='bitdi', bitme=NOW()
    WHERE id = $id
");

// Yoxla bütün addımlar bitibsə, iş əmrinin statusu da bitdi olsun
$check = $conn->query("
    SELECT COUNT(*) AS qalan 
    FROM is_emri_addimlari 
    WHERE is_emri_id=$is_id AND status!='bitdi'
")->fetch_assoc()['qalan'];

if ($check == 0) {
    $conn->query("UPDATE is_emirleri SET status='bitdi' WHERE id=$is_id");
}

header("Location: is_emri.php?id=$is_id");
exit;
?>
