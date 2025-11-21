<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("İcazə yoxdur");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ad        = trim($_POST['ad'] ?? '');
    $kategoriya = intval($_POST['kategoriya'] ?? 0); // bu əslində kateqoriya_id-dir

    if ($ad === '' || $kategoriya <= 0) {
        die("Material adı və kateqoriya boş ola bilməz");
    }

    // ⚠ CƏDVƏLDƏKİ SÜTUN ADI: kateqoriya_id
    $sql = "INSERT INTO materiallar (ad, kateqoriya_id) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL xətası (prepare): " . $conn->error);
    }

    $stmt->bind_param("si", $ad, $kategoriya);

    if (!$stmt->execute()) {
        die("SQL xətası (execute): " . $stmt->error);
    }

    header("Location: materiallar.php?ok=1");
    exit;
}

// POST deyilsə, sadəcə siyahıya qaytar
header("Location: materiallar.php");
exit;
