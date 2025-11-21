<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Yeni iş əmri növü əlavə
if (isset($_POST['add'])) {
    $ad = trim($_POST['ad']);
    if ($ad != "") {
        $stmt = $conn->prepare("INSERT INTO is_emri_novleri (ad) VALUES (?)");
        $stmt->bind_param("s", $ad);
        $stmt->execute();
    }
}

// Silmək
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM is_emri_novleri WHERE id = $id");
}

$novler = $conn->query("SELECT * FROM is_emri_novleri ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>İş Əmri Növləri</title>

<style>
    body { font-family: Arial; background:#eef1f5; margin:0; padding:20px; }
    .box { width:600px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }

    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th, td { padding:12px; border-bottom:1px solid #ccc; }
    th { background:#333; color:white; }

    input { width:100%; padding:10px; border-radius:6px; border:1px solid #aaa; margin-bottom:10px; }
    button { width:100%; padding:10px; border:none; background:#28a745; color:white; border-radius:6px; font-size:15px; }

    .del-btn { color:#c00; text-decoration:none; font-weight:bold; }
</style>

</head>
<body>

<div class="box">
    <h2>📌 İş Əmri Növləri</h2>
    <a href="admin.php">⬅ Geri</a>

    <h3>Yeni Növ Əlavə Et</h3>
    <form method="POST">
        <input type="text" name="ad" placeholder="Məs: Laminant Kəsim" required>
        <button name="add">➕ Əlavə Et</button>
    </form>

    <h3>Mövcud Növlər</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Ad</th>
            <th>Sil</th>
        </tr>

        <?php while($n = $novler->fetch_assoc()): ?>
        <tr>
            <td><?= $n['id'] ?></td>
            <td><?= $n['ad'] ?></td>
            <td><a class="del-btn" href="?del=<?= $n['id'] ?>" onclick="return confirm('Silinsin?')">🗑</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
