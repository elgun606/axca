<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

// Şablonu tapırıq
$stmt = $conn->prepare("SELECT * FROM sablon_addimlar WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$addim = $result->fetch_assoc();

if (!$addim) {
    die("Addım tapılmadı!");
}

// İş əmri adlarını çəkirik (DROP-DOWN üçün)
$is_emri_list = $conn->query("SELECT id, ad FROM is_emri ORDER BY ad ASC");

// Bütün istifadəçiləri çəkirik (usta drop-down)
$ustalar = $conn->query("SELECT id, login FROM users ORDER BY login ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_emri_id = intval($_POST['is_emri_id']);
    $usta_id = intval($_POST['usta_id']);
    $sira = intval($_POST['sira']);

    $update = $conn->prepare("UPDATE sablon_addimlar SET is_emri_id=?, usta_id=?, sira=? WHERE id=?");
    $update->bind_param("iiii", $is_emri_id, $usta_id, $sira, $id);
    $update->execute();

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

    <!-- Addım adı -->
    <label>Addım adı (iş əmri):</label><br>
    <select name="is_emri_id" required>
        <option value="">-- İş əmri seçin --</option>
        <?php while($row = $is_emri_list->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" 
                <?= ($addim['is_emri_id'] == $row['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['ad']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <!-- Usta -->
    <label>Usta:</label><br>
    <select name="usta_id" required>
        <option value="">-- İstifadəçi seçin --</option>
        <?php while($u = $ustalar->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>" 
                <?= ($addim['usta_id'] == $u['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['login']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <!-- Sıra -->
    <label>Sıra:</label><br>
    <input type="number" name="sira" value="<?= $addim['sira'] ?>" required>
    <br><br>

    <button type="submit" style="padding:10px 20px;">Yenilə</button>

</form>

<br>
<a href="sablonlar.php" style="color:blue; font-size:16px; text-decoration:none;">
    ⬅ Şablonlara qayıt
</a>

</body>
</html>
