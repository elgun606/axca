<?php
session_start();
require 'db.php';

// Admin yoxlanır
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Bu səhifəyə yalnız admin daxil ola bilər.");
}

// Kateqoriyaların siyahısı
$kats = $conn->query("SELECT id, ad FROM kateqoriyalar ORDER BY ad ASC");

// Seçilmiş kateqoriya (filter)
$selected_kat = isset($_GET['kat']) ? intval($_GET['kat']) : 0;

// FILTER üzrə material siyahısı
if ($selected_kat > 0) {
    $stmt = $conn->prepare("
        SELECT m.id, m.ad, k.ad AS kat_ad
        FROM materiallar m
        JOIN kateqoriyalar k ON m.kateqoriya_id = k.id
        WHERE m.kateqoriya_id = ?
        ORDER BY m.id DESC
    ");
    $stmt->bind_param("i", $selected_kat);
    $stmt->execute();
    $materials = $stmt->get_result();
} else {
    $materials = false;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Materiallar</title>
<style>
body { font-family:Arial; background:#f3f3f3; padding:20px; }
.box { max-width:900px; margin:auto; background:white; padding:20px; border-radius:8px; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #ddd; padding:10px; text-align:left; }
th { background:#eee; }
input[type=text], select { width:100%; padding:10px; margin-top:5px; }
button { padding:10px 15px; margin-top:10px; cursor:pointer; }
.delete-btn { background:#ff4d4d; padding:6px 10px; color:white; border-radius:6px; text-decoration:none; }
</style>
</head>
<body>

<div class="box">
    <h2>📦 Materiallar</h2>

    <!-- ★ MATERIAL ƏLAVƏ -->
    <h3>Yeni material əlavə et</h3>

    <form action="material_add.php" method="POST">
        <label>Material adı:</label>
        <input type="text" name="ad" required>

        <label>Kateqoriya seç:</label>
        <select name="kategoriya" required>
            <option value="">-- Kateqoriya seçin --</option>
            <?php while($k = $kats->fetch_assoc()): ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['ad']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">➕ Əlavə et</button>
    </form>

    <hr>

    <!-- ★ FILTER -->
    <h3>Materialları kateqoriyaya görə göstər</h3>

    <form method="GET">
        <label>Kateqoriya seç:</label>
        <select name="kat" onchange="this.form.submit()">
            <option value="0">-- Kateqoriya seçin --</option>
            <?php
            $kats2 = $conn->query("SELECT id, ad FROM kateqoriyalar ORDER BY ad ASC");
            while($k = $kats2->fetch_assoc()):
            ?>
                <option value="<?= $k['id'] ?>" <?= ($selected_kat == $k['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['ad']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <hr>

    <?php if ($selected_kat == 0): ?>

        <p><i>Yuxarıdan kateqoriya seçin 👆</i></p>

    <?php else: ?>

        <?php
        // SEÇİLMİŞ KATEQORİYANIN ADINI AL
        $katadi = "—";
        $qk = $conn->prepare("SELECT ad FROM kateqoriyalar WHERE id=?");
        $qk->bind_param("i", $selected_kat);
        $qk->execute();
        $kat_res = $qk->get_result();
        if ($kat_res->num_rows > 0) {
            $katadi = $kat_res->fetch_assoc()['ad'];
        }
        ?>

        <h3>📌 <?= htmlspecialchars($katadi) ?> materialları</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Material</th>
                <th>Kateqoriya</th>
                <th>Əməliyyat</th>
            </tr>

            <?php while($m = $materials->fetch_assoc()): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['ad']) ?></td>
                    <td><b><?= htmlspecialchars($m['kat_ad']) ?></b></td>
                    <td>
                        <a class="delete-btn"
                           href="material_delete.php?id=<?= $m['id'] ?>"
                           onclick="return confirm('Silmək istədiyinizə əminsiniz?')">
                           Sil
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>

    <?php endif; ?>

</div>

</body>
</html>
