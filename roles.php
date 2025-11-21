<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Giriş qadağandır.");
}

$roles = $conn->query("SELECT * FROM user_roles_list ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Rollar</title>
<style>
    body { margin:0; font-family: Arial; background:#f2f2f2; }
    .header { background:#222; color:white; padding:14px; font-size:20px; display:flex; justify-content:space-between; align-items:center; }
    .back { color:white; text-decoration:none; }
    .add-btn { background:#28a745; padding:8px 12px; color:white; border-radius:5px; text-decoration:none; }
    table { width:100%; background:white; border-collapse:collapse; margin-top:20px; }
    th, td { padding:10px; border-bottom:1px solid #ddd; text-align:center; }
    th { background:#333; color:white; }
    .delete { background:#dc3545; color:white; padding:5px 8px; border-radius:4px; text-decoration:none; }
</style>
</head>
<body>

<div class="header">
    <a class="back" href="admin.php">⬅ Geri</a>
    Rollar
    <a class="add-btn" href="role_add.php">+ Yeni rol</a>
</div>

<table>
<tr>
    <th>ID</th>
    <th>Rol</th>
    <th>Əməliyyat</th>
</tr>

<?php while($r = $roles->fetch_assoc()): ?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= $r['rol'] ?></td>
    <td>
        <a class="delete" onclick="return confirm('Silinsin?')"
           href="role_delete.php?id=<?= $r['id'] ?>">
           Sil
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
