<?php
session_start();
require 'db.php';

$ad = trim($_POST['ad'] ?? '');
$child = $_POST['child'] ?? [];

if ($ad == '' || count($child) == 0) {
    die("Xəta: ad və seçim lazımdır.");
}

/* 1) kateqoriyalar cədvəlinə kombo əlavə edirik */
$stmt = $conn->prepare("INSERT INTO kateqoriyalar (ad, tip) VALUES (?, 'combo')");
$stmt->bind_param("s", $ad);
$stmt->execute();

$combo_id = $stmt->insert_id;

/* 2) seçilmiş child-ları map-ə yazırıq */
foreach ($child as $cid) {
    $cid = intval($cid);
    if ($cid > 0) {
        $conn->query("INSERT INTO kateqoriya_combo_map (combo_id, child_kateqoriya_id) VALUES ($combo_id, $cid)");
    }
}

header("Location: kateqoriyalar.php?ok=1");
exit;
?>
