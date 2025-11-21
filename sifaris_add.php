<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $musteri_adi  = $_POST['musteri_adi'];
    $telefon      = $_POST['telefon'];
    $unvan        = $_POST['unvan'];
    $tarix        = $_POST['tarix'];
    $kategoriya   = intval($_POST['kategoriya']);
    $qiymet       = floatval($_POST['qiymet']);
    $aciqlama     = $_POST['aciqlama'];
    $status       = 0;

    $sql = "INSERT INTO sifarisler 
            (musteri_adi, telefon, unvan, tarix, kateqoriya_id, qiymet, aciqlama, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Xətası (prepare): " . $conn->error);
    }

    $stmt->bind_param(
        "ssssidsi",
        $musteri_adi,
        $telefon,
        $unvan,
        $tarix,
        $kategoriya,
        $qiymet,
        $aciqlama,
        $status
    );

    if ($stmt->execute()) {
        $mesaj = "Sifariş uğurla əlavə edildi! 🎉";
    } else {
        $mesaj = "Xəta baş verdi: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Sifariş əlavə et</title>
<style>
    body { font-family: Arial; background:#eef1f5; padding:20px; }
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
        background:#28a745; color:white; font-size:16px;
        border:none; border-radius:6px; cursor:pointer;
    }
    button:hover { background:#218838; }
    .msg { margin-top:10px; padding:10px; background:#d4edda; border-left:4px solid #28a745; }
    a { text-decoration:none; color:#007bff; }
</style>
</head>
<body>

<div class="box">
    <h2>📦 Yeni Sifariş Əlavə Et</h2>

    <?php if ($mesaj != ""): ?>
        <div class="msg"><?= $mesaj ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Müştəri adı</label>
        <input type="text" name="musteri_adi" required>

        <label>Telefon</label>
        <input type="text" name="telefon" required>

        <label>Ünvan</label>
        <textarea name="unvan" required></textarea>

        <label>Tarix</label>
        <input type="date" name="tarix" required>

        <label>Açıqlama</label>
        <textarea name="aciqlama" required></textarea>

        <label>Kateqoriya</label>
        <select name="kategoriya" required>
            <option value="">Seçin</option>
            <?php
            $cats = $conn->query("SELECT * FROM kateqoriyalar ORDER BY ad ASC");
            while($c = $cats->fetch_assoc()):
            ?>
                <option value="<?= $c['id'] ?>"><?= $c['ad'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Qiymət</label>
        <input type="number" step="0.01" name="qiymet" required>

        <button type="submit">Sifarişi əlavə et</button>

    </form>

    <br>
    <a href="orders.php">⬅ Sifarişlərə qayıt</a>
</div>

</body>
</html>
