<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Giriş qadağandır.");
}

$id = intval($_POST['id']);
$roles = $_POST['rol'] ?? [];

if ($id > 0) {

    // Köhnə rolları sil
    $conn->query("DELETE FROM user_roles WHERE user_id = $id");

    // Yenilərini əlavə et
    $stmt = $conn->prepare("INSERT INTO user_roles (user_id, rol) VALUES (?, ?)");

    if (!$stmt) {
        die("SQL Xətası: " . $conn->error);
    }

    foreach ($roles as $r) {
        $stmt->bind_param("is", $id, $r);
        $stmt->execute();
    }
}

header("Location: users.php");
exit;
?>
