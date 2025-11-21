<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

// Mövcud addımı tapırıq
$stmt = $conn->prepare("SELECT * FROM is_emri_addimlari WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$addim = $result->fetch_assoc();

if (!$addim) {
    die("Addım tapılmadı!");
}

// İş əmrlərini çəkirik
$is_emri = $conn->query("SELECT id, ad FROM is_emri ORDER BY id DESC");

// Bütün istifadəçiləri çəkirik
$ustalar = $conn->query("SELECT id, login FROM users ORDER BY login ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $is_emri_id = intval($_POST['is_emri_id']);
    $usta_id    = intval($_POST['usta']);
    $sira       = intval($_POST['sira']);

    // seçilmiş iş əmərinin adını götürürük
    $get_ad = $conn->prepare("SELECT ad FROM is_emri WHERE id = ?");
    $get_ad->bind_param("i", $is_emri_id);
    $get_ad->execute();
    $ad_result = $get_ad->get_result();
    $ad = $ad_result->fetch_assoc()['ad'] ?? "";

    // YENİLƏMƏ — DÜZGÜN CƏDVƏL !!!
    $upd = $conn->prepare("UPDATE is_emri_addimlari 
                           SET is_emri_id=?, addim_adi=?, usta=?, sira=? 
                           WHERE id=?");
    $upd->bind_param("isiii", $is_emri_id, $ad, $usta_id, $sira, $id);
    $upd->execute();

    header("Location: sablonlar.php?updateok=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Şablon Addımını Dəyiş</title>
</head>
<body>

<h2>Şablon Addımını Dəyiş</h2>

<form method="POST">

    <label>İş əmri:</label><br>
    <select name="is_emri_id" required>
        <option value="">-- İş əmri seçin --</option>

        <?php while($row = $is_emri->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"
                <?= ($addim['is_emri_id'] == $row['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['ad']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>Usta:</label><br>
    <select name="usta" required>
        <option value="">-- Usta / istifadəçi seçin --</option>

        <?php while($u = $ustalar->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>"
                <?= ($addim['usta'] == $u['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['login']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>Sıra:</label><br>
    <input type="number" name="sira" value="<?= $addim['sira'] ?>" required>

    <br><br>

    <button type="submit" style="padding:10px 20px;">Yenilə</button>

</form>

<br>
<a href="sablonlar.php" style="color:blue; font-size:16px;">⬅ Şablonlara qayıt</a>

</body>
</html>
