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

// Başlat → status = icrada, baslama vaxtı yazılır
$conn->query("
    UPDATE is_emri_addimlari
    SET status='icrada', baslama=NOW()
    WHERE id = $id
");

// İş əmrinin ümumi statusu da dəyişsin
$conn->query("
    UPDATE is_emirleri
    SET status='icrada'
    WHERE id = $is_id
");

// Geri qayıt
header("Location: is_emri.php?id=$is_id");
exit;
?>
