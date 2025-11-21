<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

// Sifarişi götür
$sorgu = $conn->query("SELECT * FROM sifarisler WHERE id = $id");
if ($sorgu->num_rows == 0) {
    die("Sifariş tapılmadı.");
}
$ord = $sorgu->fetch_assoc();

// FORM GÖNDƏRİLDİSƏ
if (isset($_POST['update'])) {

    $tarix      = $_POST['tarix'];
    $aciqlama   = $_POST['aciqlama'];
    $kategoriya = intval($_POST['kategoriya']);
    $telefon    = $_POST['telefon'];
    $qiymet     = $_POST['qiymet'];
    $satis      = $_POST['satis'];
    $unvan      = $_POST['unvan'];

    $sql = "UPDATE sifarisler SET 
                tarix=?,
                aciqlama=?,
                kategoriya=?,
                telefon=?,
                qiymet=?,
                satis=?,
                unvan=?
            WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissssi", 
        $tarix, $aciqlama, $kategoriya, 
        $telefon, $qiymet, $satis, $unvan, $id
    );

    if ($stmt->execute()) {
        header("Location: orders.php");
        exit;
    } else {
        echo "Xəta baş verdi: " . $stmt->error;
    }
}

?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Sifarişi redaktə et</title>

<style>
    body { background:#eef1f5; font-family:Arial; padding:20px; }
    .box {
        max-width:600px; margin:auto; background:white; padding:20px;
        border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);
    }
    input, textarea, select {
        width:100%; padding:10px; margin-top:10px;
        border-radius:6px; border:1px solid #ccc; font-size:15px;
    }
    button {
        margin-top:15px; width:100%; padding:12px;
        background:#007bff; color:white; font-size:16px;
        border:none; border-radius:6px; cursor:pointer;
    }
    button:hover { background:#0056b3; }
    a { color:#007bff; text-decoration:none; }
</style>
</head>
<body>

<div class="box">
    <h2>✏ Sifariş redaktəsi</h2>

    <form method="POST">

        <label>Tarix</label>
        <input type="date" name="tarix" value="<?= $ord['tarix'] ?>" required>

        <label>Açıqlama</label>
        <textarea name="aciqlama" rows="3" required><?= htmlspecialchars($ord['aciqlama']) ?></textarea>

        <label>Kateqoriya</label>
        <select name="kategoriya" required>
            <?php
            $cats = $conn->query("SELECT * FROM kateqoriyalar ORDER BY ad ASC");
            while ($c = $cats->fetch_assoc()):
            ?>
                <option value="<?= $c['id'] ?>" 
                    <?= ($c['id'] == $ord['kategoriya']) ? 'selected' : '' ?>>
                    <?= $c['ad'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Telefon</label>
        <input type="text" name="telefon" value="<?= $ord['telefon'] ?>" required>

        <label>Qiymət</label>
        <input type="text" name="qiymet" value="<?= $ord['qiymet'] ?>" required>

        <label>Satış</label>
        <input type="text" name="satis" value="<?= $ord['satis'] ?>" required>

        <label>Ünvan</label>
        <textarea name="unvan" rows="2" required><?= htmlspecialchars($ord['unvan']) ?></textarea>

        <button type="submit" name="update">Yenilə</button>
    </form>

    <br>
    <a href="orders.php">⬅ Geri qayıt</a>
</div>

</body>
</html>
