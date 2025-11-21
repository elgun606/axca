<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Düzgün cədvəl: is_emri_novleri
$sql = "
    SELECT n.id, n.ad, n.ustasi, u.login AS usta_adi
    FROM is_emri_novleri n
    LEFT JOIN users u ON u.id = n.ustasi
    ORDER BY n.id DESC
";
$result = $conn->query($sql);

if (!$result) {
    die("SQL xətası: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>İş Əmrləri</title>
<style>
table { border-collapse: collapse; width: 100%; }
table, th, td { border: 1px solid #ccc; padding: 10px; }
thead { background: #333; color: #fff; }
button { padding:6px 12px; border:none; border-radius:4px; cursor:pointer; }
.edit { background:#007bff; color:white; }
.delete { background:#dc3545; color:white; }
</style>
</head>
<body>

<a href="admin.php" style="color:blue; text-decoration:none; font-size:16px;">
    ⬅ Geri
</a>
<br><br>

<h2>İş Əmrləri</h2>

<a href="is_emri_yarat.php">
    <button style="padding:10px 20px; background:green; color:white;">+ Yeni İş Əmri Yarat</button>
</a>

<?php if (isset($_GET['ok'])): ?>
    <p style="color:green;">Yeni iş əmri yaradıldı!</p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Ad</th>
            <th>Ustası</th>
            <th>Əməliyyat</th>
        </tr>
    </thead>

    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['ad']) ?></td>
            <td><?= $row['usta_adi'] ?: "-" ?></td>

            <td>
                <a href="is_emri_edit.php?id=<?= $row['id'] ?>">
                    <button class="edit">Dəyiş</button>
                </a>

                <a href="is_emri_sil.php?id=<?= $row['id'] ?>" onclick="return confirm('Silmək istəyirsən?')">
                    <button class="delete">Sil</button>
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
