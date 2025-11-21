<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("İcazə yoxdur!");
}

$adim_id = intval($_GET['id'] ?? 0);
$sablon_id = intval($_GET['sablon_id'] ?? 0);

if ($adim_id <= 0 || $sablon_id <= 0) {
    die("Xəta: məlumat düzgün deyil.");
}

// ❗ Yalnız NÜSXƏ cədvəlindən silirik
$conn->query("DELETE FROM sablon_isemri_nusxe WHERE id = $adim_id");

// Şablona geri qaytar
header("Location: sablon_gor.php?sablon_id=$sablon_id");
exit;
