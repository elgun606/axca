<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cədvəl mövcuddurmu?
$check = $conn->query("SHOW TABLES LIKE 'is_emirleri'");
if ($check->num_rows == 0) {
    die("<h3 style='color:red'>⚠ `is_emirleri` cədvəli mövcud deyil! SQL-i yaratmalısan.</h3>");
}

$emirler = $conn->query("
    SELECT is_emirleri.*, sifarisler.aciqlama 
    FROM is_emirleri
    LEFT JOIN sifarisler ON sifarisler.id = is_emirleri.sifaris_id
    ORDER BY is_emirleri.id DESC
");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>İş Əmrləri</title>

<style>
    body { background:#eef1f5; font-family:Arial; padding:20px; }
    table { width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; }
    th, td { padding:12px; border-bottom:1px solid #ccc; }
    th { background:#333; color:white; }
    .btn-add { background:#28a745; padding:10px 16px; color:white; border-radius:8px; text-decoration:none; }
    .view-btn { background:#007bff; padding:7px 10px; color:white; border-radius:6px; text-decoration:none; }
</style>

</head>
<body>

<h2>📄 İş Əmrləri</h2>

<a class="btn-add" href="is_emri_yarat.php">➕ Yeni İş Əmri Yarat</a>
<br><br>

<table>
<tr>
    <th>ID</th>
    <th>Sifariş</th>
    <th>Status</th>
    <th>Tarix</th>
    <th>Əməliyyat</th>
</tr>

<?php if ($emirler && $emirler->num_rows > 0): ?>
    <?php while ($e = $emirler->fetch_assoc()): ?>
        <tr>
            <td><?= $e['id'] ?></td>
            <td><?= htmlspecialchars($e['aciqlama']) ?></td>
            <td><?= $e['status'] ?></td>
            <td><?= $e['yaradildi'] ?></td>
            <td>
                <a class="view-btn" href="is_emri.php?id=<?= $e['id'] ?>">🔍 Bax</a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" style="text-align:center; padding:20px;">Hələ iş əmri yoxdur.</td>
    </tr>
<?php endif; ?>

</table>

</body>
</html>
