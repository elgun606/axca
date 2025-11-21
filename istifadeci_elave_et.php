<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Bu səhifəyə giriş qadağandır.");
}

$error = "";
$ok = "";

/* --- ROL SİYAHINI CƏDVƏLDƏN GƏTİR --- */
$roleList = $conn->query("SELECT rol FROM user_roles_list ORDER BY rol ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login']);
    $sifre = trim($_POST['sifre']);
    $rol   = trim($_POST['rol']);

    if ($login === "" || $sifre === "") {
        $error = "Login və şifrə boş ola bilməz";
    } else {
        $hash = password_hash($sifre, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (login, sifre, rol) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $login, $hash, $rol);

        if ($stmt->execute()) {
            $ok = "İstifadəçi əlavə olundu!";
        } else {
            $error = "Xəta: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>İstifadəçi əlavə et</title>
</head>
<body style="padding:20px; font-family:Arial;">

<h2>Yeni istifadəçi əlavə et</h2>

<?php if ($error): ?>
    <div style="color:red;"><?= $error ?></div>
<?php endif; ?>

<?php if ($ok): ?>
    <div style="color:green;"><?= $ok ?></div>
<?php endif; ?>

<form method="post">
    <label>Login:</label>
    <input type="text" name="login" required>

    <label>Şifrə:</label>
    <input type="password" name="sifre" required>

    <label>Rol:</label>
    <select name="rol" required>
        <?php while($r = $roleList->fetch_assoc()): ?>
            <option value="<?= $r['rol'] ?>"><?= $r['rol'] ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Əlavə et</button>
</form>

<br>
<a href="users.php">← Geri</a>

</body>
</html>
