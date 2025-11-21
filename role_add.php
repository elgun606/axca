<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Giriş qadağandır.");
}

$error = "";
$ok = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rol = trim($_POST['rol']);

    if ($rol === "") {
        $error = "Rol adı boş ola bilməz.";
    } else {

        $stmt = $conn->prepare("INSERT INTO user_roles_list (rol) VALUES (?)");
        $stmt->bind_param("s", $rol);

        if ($stmt->execute()) {
            $ok = "Rol əlavə edildi!";
        } else {
            $error = "Xəta: eyni rol artıq var.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Yeni rol əlavə et</title>
</head>
<body style="padding:20px; font-family:Arial;">

<h2>Yeni rol əlavə et</h2>

<?php if ($error): ?>
    <div style="color:red;"><?= $error ?></div>
<?php endif; ?>

<?php if ($ok): ?>
    <div style="color:green;"><?= $ok ?></div>
<?php endif; ?>

<form method="post">
    <label>Rol adı:</label>
    <input type="text" name="rol" required>

    <button type="submit">Əlavə et</button>
</form>

<br>
<a href="roles.php">← Geri</a>

</body>
</html>
