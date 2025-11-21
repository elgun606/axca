<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 🟢 Tək kateqoriyaları götür
$cats = $conn->query("SELECT id, ad FROM kateqoriyalar WHERE tip='single' ORDER BY ad ASC");

$ok = "";
$error = "";

// 🟢 Form göndərilibsə
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ad = trim($_POST['ad']);
    $kombo = $_POST['combo'] ?? [];

    if ($ad == "" || count($kombo) < 2) {
        $error = "❌ Kombo yaratmaq üçün minimum 2 kateqoriya seçilməlidir!";
    } else {

        $combo_ids = implode(",", array_map("intval", $kombo));

        // SQL HAZIRLANIR
        $stmt = $conn->prepare("INSERT INTO kateqoriyalar (ad, tip, combo_ids) VALUES (?, 'combo', ?)");

        if (!$stmt) {
            die("SQL ERROR: " . $conn->error);
        }

        $stmt->bind_param("ss", $ad, $combo_ids);

        if ($stmt->execute()) {
            $ok = "✅ Kombo kateqoriya yaradıldı!";
        } else {
            $error = "Xəta baş verdi: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Yeni Kombo Kateqoriya</title>
<style>
body { background:#eef1f5; font-family:Arial; padding:20px; }
.box { max-width:600px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
input, select { width:100%; padding:10px; margin-top:10px; }
button { padding:10px; background:#6f42c1; color:white; border:none; border-radius:6px; width:100%; margin-top:10px; }
.success { background:#d4edda; padding:10px; border-radius:6px; margin-bottom:10px; }
.error { background:#f8d7da; padding:10px; border-radius:6px; margin-bottom:10px; }
</style>
</head>
<body>

<div class="box">
<h2>🔗 Yeni Kombo Kateqoriya</h2>

<?php if ($ok): ?><div class="success"><?= $ok ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

<form method="POST">

    <label>Kombo adı:</label>
    <input type="text" name="ad" placeholder="Məs: Metal + Laminant" required>

    <label>Alt kateqoriyalar (çoxlu seçim):</label>
    <select name="combo[]" multiple size="6" required>
        <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= $c['ad'] ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">➕ Yarat</button>
</form>

<br>
<a href="kateqoriyalar.php">⬅ Geri</a>

</div>

</body>
</html>
