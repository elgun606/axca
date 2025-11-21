<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sablonlar = $conn->query("
    SELECT id, ad
    FROM kateqoriyalar
    ORDER BY id DESC



");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Şablonlar</title>

<style>
    body { font-family: Arial; background: #f4f4f4; padding: 20px; }
    table { width: 100%; border-collapse: collapse; background: #fff; }
    th { background: #007bff; color: #fff; padding: 10px; }
    td { padding: 10px; border-bottom: 1px solid #ccc; }
    .btn { padding: 6px 12px; color:#fff; text-decoration:none; border-radius:4px; }
    .blue{background:#007bff;} .orange{background:#ff9800;} .red{background:#e91e63;}
</style>

</head>
<body>

<h2>Şablonlar</h2>

<a href="sablon_yarat.php" class="btn" style="background:#4caf50;">+ Yeni Şablon</a>

<br><br>

<table>
<tr>
    <th>ID</th>
    <th>Şablon adı</th>
    <th>Əməliyyat</th>
</tr>

<?php while ($s = $sablonlar->fetch_assoc()): ?>
<tr>
    <td><?= $s['id'] ?></td>
    <td><?= htmlspecialchars($s['ad']) ?></td>

    <td>
        <a href="sablon_gor.php?id=<?= $s['id'] ?>" class="btn blue">Bax</a>
        <a href="sablon_sil.php?id=<?= $s['id'] ?>"
           onclick="return confirm('Bu şablon silinsin?')"
           class="btn red">
            Sil
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
