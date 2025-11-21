<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$kategoriya_id = intval($_GET['id']);
$cat = $conn->query("SELECT * FROM kateqoriyalar WHERE id = $kategoriya_id")->fetch_assoc();

if (!$cat) die("Kateqoriya tapılmadı");

// Addım əlavə
if (isset($_POST['add'])) {
    $addim = $_POST['addim'];
    $usta = $_POST['usta'];
    $sira = intval($_POST['sira']);

    $stmt = $conn->prepare("INSERT INTO kateqoriya_addimlari (kategoriya_id, addim_adi, usta, sira) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $kategoriya_id, $addim, $usta, $sira);
    $stmt->execute();
}

// Addım silmə
if (isset($_GET['del'])) {
    $del = intval($_GET['del']);
    $conn->query("DELETE FROM kateqoriya_addimlari WHERE id = $del");
}

$addimlar = $conn->query("SELECT * FROM kateqoriya_addimlari WHERE kategoriya_id = $kategoriya_id ORDER BY sira ASC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Şablon</title>
<style>
    body { font-family:Arial; background:#eef1f5; padding:20px; }
    .box { width:700px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.1); }
    table { width:100%; border-collapse:collapse; margin-top:15px; }
    th, td { padding:10px; border-bottom:1px solid #ccc; }
    th { background:#333; color:white; }
    input { width:100%; padding:10px; margin-top:10px; border-radius:6px; border:1px solid #aaa; }
    button { padding:10px; width:100%; margin-top:10px; background:#28a745; border:none; border-radius:6px; color:white; }
    a.del { color:#c00; text-decoration:none; }
</style>
</head>
<body>

<div class="box">
    <h2>⚙ <?= $cat['ad'] ?> – Şablon Addımları</h2>
    <a href="kateqoriya_sablonlari.php">⬅ Geri</a>

    <h3>Yeni Addım Əlavə et</h3>
    <form method="POST">
        <label>Addım adı</label>
        <input type="text" name="addim" required>

        <label>Usta adı</label>
        <input type="text" name="usta" required>

        <label>Sıra</label>
        <input type="number" name="sira" required>

        <button name="add">➕ Əlavə et</button>
    </form>

    <h3>Mövcud Şablon Addımları</h3>
    <table>
        <tr>
            <th>Sıra</th>
            <th>Addım</th>
            <th>Usta</th>
            <th>Sil</th>
        </tr>

        <?php while ($a = $addimlar->fetch_assoc()): ?>
        <tr>
            <td><?= $a['sira'] ?></td>
            <td><?= $a['addim_adi'] ?></td>
            <td><?= $a['usta'] ?></td>
            <td><a class="del" href="?id=<?= $kategoriya_id ?>&del=<?= $a['id'] ?>" onclick="return confirm('Silinsin?')">🗑</a></td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

</body>
</html>
