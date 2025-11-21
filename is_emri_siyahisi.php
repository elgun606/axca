<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$result = $conn->query("SELECT * FROM is_emri_sablonlari ORDER BY ad ASC");

if (!$result) {
    die("Sorğu xətası: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>İş əmri şablonları</title>
</head>
<body>
    <h1>İş əmri şablonları</h1>

    <?php if (isset($_GET['ok'])): ?>
        <p style="color:green;">Yeni iş əmri şablonu əlavə olundu.</p>
    <?php endif; ?>

    <p><a href="is_emri_yarat.php">Yeni iş əmri şablonu yarat</a></p>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Ad</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['ad']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
