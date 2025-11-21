<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* ---------------------------------------
   1) Ustaları çəkirik
---------------------------------------- */
$ustalar = $conn->query("SELECT id, login FROM users ORDER BY login ASC");
if (!$ustalar) {
    die("Usta sorğusu xətası: " . $conn->error);
}

/* ---------------------------------------
   2) Form göndəriləndə işləyən hissə
---------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ad = trim($_POST['ad'] ?? '');
    $usta_id = intval($_POST['usta_id'] ?? 0);

    if ($ad === '') {
        die("İş əmrinin adı boş ola bilməz.");
    }

    // 🔥 DÜZGÜN CƏDVƏL: is_emri_novleri
    $sql = "INSERT INTO is_emri_novleri (ad, ustasi) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Prepare Xətası: " . $conn->error);
    }

    $stmt->bind_param("si", $ad, $usta_id);

    if (!$stmt->execute()) {
        die("İcra xətası: " . $stmt->error);
    }

    header("Location: is_emri.php?ok=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Yeni İş Əmri</title>

<style>
body {
    font-family: Arial;
    background:#f2f2f7;
    padding:30px;
}

.box {
    background:white;
    width:420px;
    margin:auto;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 12px rgba(0,0,0,0.15);
}

h2 {
    text-align:center;
    margin-bottom:20px;
}

label {
    font-weight:bold;
    margin-top:10px;
    display:block;
}

input, select {
    width:100%;
    padding:12px;
    margin-top:6px;
    border:1px solid #ccc;
    border-radius:6px;
    font-size:15px;
}

button {
    width:100%;
    margin-top:18px;
    background:#0077ff;
    border:none;
    color:white;
    padding:12px;
    font-size:16px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

button:hover {
    background:#005fd6;
}

.back-link {
    display:block;
    margin-top:20px;
    text-align:center;
    color:#0066cc;
    font-size:15px;
    text-decoration:none;
}
.back-link:hover {
    text-decoration:underline;
}
</style>

</head>
<body>

<div class="box">

<h2>📝 Yeni İş Əmri Yarat</h2>

<form method="POST">

    <label>İş əmrinin adı:</label>
    <input type="text" name="ad" placeholder="Məs: Laminant Kəsim" required>

    <label>Varsayılan usta seç:</label>
    <select name="usta_id" required>
        <option value="">-- Seçin --</option>
        <?php while($u = $ustalar->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['login']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">➕ Yarat</button>

</form>

<a class="back-link" href="is_emri.php">⬅ İş Əmrlərinə qayıt</a>

</div>

</body>
</html>
