<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login']);
    $sifre = trim($_POST['sifre']);

    if ($login === "" || $sifre === "") {
        $error = "Login və şifrə boş ola bilməz.";
    } else {

        // 🔥 PREPARE YOXLA
        $stmt = $conn->prepare("SELECT id, login, sifre, rol FROM users WHERE login = ? LIMIT 1");

        if (!$stmt) {
            die("SQL xəta: " . $conn->error);
        }

        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if (password_verify($sifre, $user['sifre'])) {

                // Sessiya məlumatları
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login']   = $user['login'];
                $_SESSION['rol']     = $user['rol'];

                // 🔥 ROLE YÖNLƏNDİRMƏ (düzgün ardıcıllıq)
                switch ($user['rol']) {

                    case 'admin':
                        header("Location: admin.php");
                        exit;

                    case 'misar_kesimci':
                        header("Location: misar_kesimci_panel.php");
                        exit;

                    case 'pvs_usta':
                        header("Location: pvs_panel.php");
                        exit;

                    default:
                        header("Location: usta_panel.php");
                        exit;
                }

            } else {
                $error = "Şifrə səhvdir.";
            }

        } else {
            $error = "Belə istifadəçi tapılmadı.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Giriş</title>
<style>
    body { font-family: Arial; background:#f5f5f5; }
    .box {
        width: 330px; margin: 80px auto; background: #fff;
        padding: 20px; border-radius: 6px; box-shadow: 0 0 6px rgba(0,0,0,0.2);
    }
    input { width: 100%; padding: 8px; margin-top: 8px; }
    button { width: 100%; padding: 8px; margin-top: 10px; background:#007bff; color:white; border:none; cursor:pointer; }
    button:hover { opacity: .85; }
    .error {
        margin-top:10px; background:#ffcaca; padding:8px;
        border:1px solid #ff6d6d; border-radius:4px;
    }
</style>
</head>
<body>

<div class="box">
    <h2>Giriş</h2>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="login" placeholder="Login" required>
        <input type="password" name="sifre" placeholder="Şifrə" required>
        <button type="submit">Daxil ol</button>
    </form>
</div>

</body>
</html>
