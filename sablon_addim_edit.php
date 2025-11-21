<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID tapılmadı!");
}

// BURASI DÜZGÜN CƏDVƏLDİR !!!
$stmt = $conn->prepare("SELECT * FROM is_emri_sablon_addimlari WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$addim = $res->fetch_assoc();

if (!$addim) {
    die("Addım tapılmadı!");
}

$sablon_id = $addim['sablon_id'];

// İş emri siyahısı
$is_emri = $conn->query("SELECT id, ad FROM is_emri ORDER BY ad ASC");
// Usta siyahısı
$ustalar = $conn->query("SELECT id, login FROM users ORDER BY login ASC");


// FORM GƏLİBSƏ — YENİLƏMƏ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $is_emri_id = intval($_POST['is_emri_id']);
    $usta       = intval($_POST['usta']);
    $sira       = intval($_POST['sira']);

    $upd = $conn->prepare("
        UPDATE is_emri_sablon_addimlari
           SET is_emri_id = ?, usta = ?, sira = ?
         WHERE id = ?
    ");
    $upd->bind_param("iiii", $is_emri_id, $usta, $sira, $id);
    $upd->execute();

    header("Location: sablon_gor.php?id=".$sablon_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Addımı Redaktə Et</title>
</head>
<body>

<h2>Addımı Redaktə Et</h2>

<form method="POST">

    <label>İş əmri:</label><br>
    <select name="is_emri_id" required>
        <?php while($row = $is_emri->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"
                <?= ($row['id'] == $addim['is_emri_id']) ? "selected" : "" ?>>
                <?= htmlspecialchars($row['ad']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label>Usta:</label><br>
    <select name="usta" required>
        <?php while($u = $ustalar->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>"
                <?= ($u['id'] == $addim['usta']) ? "selected" : "" ?>>
                <?= htmlspecialchars($u['login']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label>Sıra:</label><br>
    <input type="number" name="sira" value="<?= $addim['sira'] ?>" required>
    <br><br>

    <button type="submit">Yenilə</button>

</form>

<br>
<a href="sablon_gor.php?id=<?= $sablon_id ?>">⬅ Geri</a>

</body>
</html>
