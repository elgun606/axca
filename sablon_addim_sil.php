<?php
session_start();
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$sablon_id = intval($_GET['sablon_id'] ?? 0);

if ($id <= 0 || $sablon_id <= 0) {
    die("Xəta: məlumat düzgün gəlmir.");
}

// addımı sil
$conn->query("DELETE FROM is_emri_sablon_addimlari WHERE id = $id");

// DÜZGÜN geri qayıt
header("Location: sablon_gor.php?sablon_id=$sablon_id");
exit;
