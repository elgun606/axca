<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// KATEQORİYA FİLTRİ
$filter = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

// SİFARİŞLƏRİN ÇƏKİLMƏSİ
if ($filter > 0) {
    $orders = $conn->query("SELECT * FROM sifarisler WHERE kateqoriya_id = $filter ORDER BY id DESC");
} else {
    $orders = $conn->query("SELECT * FROM sifarisler ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Sifarişlər</title>
<style>
    body { margin:0; font-family: Arial; background:#eef1f5; }
    .header { background:#222; color:white; padding:14px; font-size:20px; display:flex; justify-content:space-between; align-items:center; }
    .back { color:white; text-decoration:none; }

    .add-btn {
        background:#28a745; color:white; padding:10px 16px;
        border-radius:6px; text-decoration:none; font-size:14px;
    }

    .content { padding:20px; }

    table {
        width:100%; border-collapse:collapse; background:white;
        border-radius:8px; overflow:hidden;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
    }
    th {
        background:#333; color:white; padding:12px;
        font-size:15px; text-align:left;
    }
    td { padding:12px; border-bottom:1px solid #ddd; font-size:14px; }
    tr:nth-child(even) td { background:#f9f9f9; }
    tr:hover td { background:#f1f1f1; }

    .filter-select {
        padding:8px; 
        border-radius:6px; 
        font-size:14px; 
        margin-bottom:15px;
    }

    .edit-btn { color:#007bff; text-decoration:none; }
    .delete-btn { color:#cc0000; text-decoration:none; }
    .work-btn {
        background:#0066ff;
        color:white;
        padding:6px 10px;
        border-radius:5px;
        text-decoration:none;
    }
</style>
</head>
<body>

<div class="header">
    <a class="back" href="admin.php">⬅ Geri</a>
    📦 Sifarişlər
    <a class="add-btn" href="sifaris_add.php">➕ Yeni Sifariş Əlavə Et</a>
</div>

<div class="content">

<!-- KATEQORİYA FİLTRİ -->
<form method="GET">
    <select name="cat" class="filter-select" onchange="this.form.submit()">
        <option value="0">📂 Bütün kateqoriyalar</option>

        <?php
        $cats = $conn->query("SELECT * FROM kateqoriyalar ORDER BY ad ASC");
        while ($c = $cats->fetch_assoc()):
        ?>
            <option value="<?= $c['id'] ?>" <?= ($filter == $c['id']) ? 'selected' : '' ?>>
                <?= $c['ad'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php if ($orders->num_rows == 0): ?>
    <div>Hələ sifariş yoxdur.</div>

<?php else: ?>
<table>
    <tr>
        <th>ID</th>
        <th>Tarix</th>
        <th>Müştəri</th>
        <th>Açıqlama</th>
        <th>Kateqoriya</th>
        <th>Telefon</th>
        <th>Qiymət</th>
        <th>Ünvan</th>
        <th>İş Əmri</th>
        <th>Əməliyyat</th>
    </tr>

    <?php while($o = $orders->fetch_assoc()): ?>

        <?php
        // KATEQORİYA ADI
        $cat_name = "—";
        $cat_id = intval($o['kateqoriya_id']);
        $cat_query = $conn->query("SELECT ad FROM kateqoriyalar WHERE id = $cat_id");

        if ($cat_query && $cat_query->num_rows > 0) {
            $cat_name = $cat_query->fetch_assoc()['ad'];
        }
        ?>

        <tr>
            <td><?= $o['id'] ?></td>
            <td><?= $o['tarix'] ?></td>
            <td><?= htmlspecialchars($o['musteri_adi']) ?></td>
            <td><?= htmlspecialchars($o['aciqlama']) ?></td>
            <td><?= $cat_name ?></td>
            <td><?= $o['telefon'] ?></td>
            <td><?= $o['qiymet'] ?></td>
            <td><?= htmlspecialchars($o['unvan']) ?></td>

            <td>
                <a class="work-btn" href="is_emri_yarat.php?sifaris=<?= $o['id'] ?>">
                    ➕ Yarat
                </a>
            </td>

            <td>
                <a class="edit-btn" href="edit_order.php?id=<?= $o['id'] ?>">✏ Dəyiş</a> | 
                <a class="delete-btn" href="delete_order.php?id=<?= $o['id'] ?>" onclick="return confirm('Silmək istəyirsiniz?')">🗑 Sil</a>
            </td>
        </tr>

    <?php endwhile; ?>

</table>
<?php endif; ?>

</div>

</body>
</html>
