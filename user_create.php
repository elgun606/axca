
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // redirect üçün buffer açıram

include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $sifre = trim($_POST['sifre'] ?? '');
    $rol   = $_POST['rol'] ?? 'istifadeci';

    if ($login === '' || $sifre === '') {
        die("Login və şifrə boş ola bilməz");
    }

    $hash = password_hash($sifre, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (login, sifre, rol) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL prepare xətası: " . $conn->error);
    }

    $stmt->bind_param("sss", $login, $hash, $rol);

    if ($stmt->execute()) {
        ob_end_clean(); // buffer təmizlə
   header("Location: /mebel/index.php?user_ok=1");
        exit;
    } else {
        die("Yazma xətası: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Yeni istifadəçi yarat</title>
</head>
<body>

<form method="post">
    <input type="text" name="login" placeholder="Login" required>
    <input type="password" name="sifre" placeholder="Şifrə" required>
    <select name="rol">
        <option value="istifadeci">İstifadəçi</option>
        <option value="admin">Admin</option>
    </select>
    <button type="submit">Yarat</button>
</form>

</body>
</html>
