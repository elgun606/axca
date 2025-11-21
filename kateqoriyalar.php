<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* -----------------------------------------
   1) Yeni tək kateqoriya əlavə
------------------------------------------ */
if (isset($_POST['add'])) {
    $ad = trim($_POST['ad']);
    if ($ad !== "") {
        $stmt = $conn->prepare("INSERT INTO kateqoriyalar (ad, tip) VALUES (?, 'single')");
        $stmt->bind_param("s", $ad);
        $stmt->execute();
    }
}

/* -----------------------------------------
   2) Silmə
------------------------------------------ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $conn->query("DELETE FROM kateqoriyalar WHERE id = $id");
    }
}

/* -----------------------------------------
   3) Redaktə
------------------------------------------ */
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $ad = trim($_POST['ad']);

    $stmt = $conn->prepare("UPDATE kateqoriyalar SET ad=? WHERE id=?");
    $stmt->bind_param("si", $ad, $id);
    $stmt->execute();
}

/* -----------------------------------------
   4) Siyahı
------------------------------------------ */
$cats = $conn->query("SELECT * FROM kateqoriyalar ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Kateqoriyalar</title>

<style>
    body { margin:0; font-family:Arial; background:#eef1f5; }
    .header { background:#222; color:white; padding:14px; font-size:20px; }
    .back { color:white; text-decoration:none; margin-right:14px; }
    .box { max-width:800px; margin:20px auto; background:white; padding:20px;
        border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,.1); }
    input { width:100%; padding:10px; margin-top:10px;
        border-radius:5px; border:1px solid #ccc; }
    table { width:100%; margin-top:20px; border-collapse:collapse; }
    th, td { padding:10px; border-bottom:1px solid #ddd; }
    th { background:#333; color:white; }
    a.btn-delete { background:#dc3545; color:white; padding:6px 10px; border-radius:5px; text-decoration:none; }
    a.btn-edit { background:#007bff; color:white; padding:6px 10px; border-radius:5px; text-decoration:none; }
    a.btn-orders { background:#28a745; color:white; padding:6px 10px; border-radius:5px; text-decoration:none; }
    a.btn-combo { background:#6f42c1; color:white; padding:6px 10px; border-radius:5px; text-decoration:none; }
</style>

</head>
<body>

<div class="header">
    <a class="back" href="admin.php">⬅ Geri</a>
    📂 Kateqoriyalar
</div>

<div class="box">

<h3>Yeni kateqoriya əlavə et</h3>

<form method="POST">
    <input type="text" name="ad" placeholder="Məs: Metal" required>
    <button name="add" style="margin-top:10px;padding:10px;width:100%;background:#28a745;color:white;border:none;border-radius:6px;">
        ➕ Əlavə et
    </button>
</form>

<!-- 🔗 KOMBO KATEQORİYA DÜYMƏSİ -->
<a href="kateqoriya_kombo_yeni.php"
   style="margin-top:10px;display:block;padding:10px;background:#6f42c1;color:white;
          text-align:center;border-radius:6px;text-decoration:none;">
   🔗 Yeni Kombo Kateqoriya
</a>

<h3>Mövcud kateqoriyalar</h3>

<table>
    <tr>
        <th>ID</th>
        <th>Ad</th>
        <th>Tip</th>
        <th>İş əmrləri</th>
        <th>Əməliyyatlar</th>
    </tr>

<?php while ($c = $cats->fetch_assoc()): ?>
    <?php 
        $cid = $c['id'];
        $tip = $c['tip'];

        // Sifariş sayı
        $count = 0;
        $saySor = $conn->query("SELECT COUNT(*) AS say FROM sifarisler WHERE kategoriya=$cid");
        if ($saySor) {
            $r = $saySor->fetch_assoc();
            $count = $r ? $r['say'] : 0;
        }

        // Kombo alt kateqoriyalar
        $combo_text = "";
        if ($tip === "combo" && !empty($c['combo_ids'])) {
            $ids = explode(",", $c['combo_ids']);
            $names = [];
            foreach ($ids as $id) {
                $q = $conn->query("SELECT ad FROM kateqoriyalar WHERE id=$id AND tip='single'");
                if ($q && $q->num_rows > 0) {
                    $names[] = $q->fetch_assoc()['ad'];
                }
            }
            if ($names) {
                $combo_text = "(" . implode(" + ", $names) . ")";
            }
        }
    ?>

    <tr>
        <td><?= $cid ?></td>

        <td>
            <?php if ($tip == "combo"): ?>
                <b><?= $c['ad'] ?></b> <span style="color:#6f42c1;"><?= $combo_text ?></span>
            <?php else: ?>
                <?= $c['ad'] ?>
            <?php endif; ?>
        </td>

        <td><?= ($tip == "combo" ? "🔗 Kombo" : "📁 Tək") ?></td>

        <td><?= $count ?> sifariş</td>

        <td>
            <a class="btn-edit" href="kateqoriya_edit.php?id=<?= $cid ?>">Dəyiş</a>
            <a class="btn-delete" href="?delete=<?= $cid ?>" onclick="return confirm('Silinsin?')">Sil</a>

            <?php if ($tip == "combo"): ?>
                <a class="btn-combo" href="sablon_gor.php?sablon_id=<?= $cid ?>">⚙ Şablon</a>
            <?php else: ?>
                <a class="btn-orders" href="orders.php?kategoriya=<?= $cid ?>">Bax</a>
            <?php endif; ?>
        </td>
    </tr>

<?php endwhile; ?>

</table>

</div>
</body>
</html>
