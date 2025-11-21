<?php
session_start();
require 'db.php';

$cats = $conn->query("SELECT id, ad FROM kateqoriyalar WHERE tip='single' ORDER BY ad ASC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Kombo Kateqoriya Yarat</title>

<style>
body { font-family:Arial; padding:20px; background:#eef1f5; }
.box { background:white; padding:20px; border-radius:10px; max-width:500px; margin:auto; }
select, input { width:100%; padding:10px; margin-top:10px; }
button { width:100%; padding:12px; margin-top:15px; background:green; color:white; border:0; border-radius:6px; }
</style>
</head>
<body>

<div class="box">
<h2>➕ Yeni Kombo Kateqoriya</h2>

<form method="POST" action="kombo_yarat_proses.php">

    <label>Kombo Kateqoriya Adı:</label>
    <input type="text" name="ad" placeholder="Məs: Metal + Laminant" required>

    <label>Komboya daxil olacaq kateqoriyalar:</label>
    <select name="child[]" multiple size="6" required>
        <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= $c['ad'] ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">✔ Yarat</button>
</form>

</div>

</body>
</html>
