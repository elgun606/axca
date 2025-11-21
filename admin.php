<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

// Login yoxlanışı
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Rol yoxlanışı
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'superadmin') {
    echo "Bu səhifəyə giriş icazən yoxdur!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial; background:#f2f2f2; margin:0; padding:0; }
        .header { background:#222; color:#fff; padding:14px; font-size:20px; }
        .logout { float:right; color:#fff; text-decoration:none; }
        .menu { background:#333; padding:12px; }
        .menu a {
            color:white; margin-right:20px; text-decoration:none;
            padding:6px 12px; border-radius:4px; font-weight:bold;
        }
        .menu a:hover { background:#555; }
        .container { padding:20px; }
    </style>
</head>
<body>

<div class="header">


<style>
.user-menu {
    position: absolute;
    right: 20px;
    top: 10px;
    color: white;
    cursor: pointer;
    font-size: 16px;
    user-select: none;
}

.user-menu .menu-box {
    display: none;
    position: absolute;
    right: 0;
    top: 35px;
    background: white;
    border: 1px solid #ccc;
    border-radius: 6px;
    min-width: 170px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    z-index: 9999;
}

.user-menu .menu-box a {
    display: block;
    padding: 10px;
    color: #444;
    text-decoration: none;
    border-bottom: 1px solid #eee;
}

.user-menu .menu-box a:hover {
    background: #f5f5f5;
}

.user-menu-expanded .menu-box {
    display: block;
}
</style>

<div class="user-menu" id="userMenu">
    👤 <?php echo $_SESSION['login'] ?? 'Admin'; ?> ▼
    
    <div class="menu-box">
        <a href="profil.php">👤 Profil</a>
        <a href="parol_deyis.php">🔑 Parol Dəyiş</a>
        <a href="logout.php" style="color:#c00;font-weight:bold">🚪 Çıxış</a>
    </div>
</div>

<script>
document.getElementById("userMenu").onclick = function() {
    this.classList.toggle("user-menu-expanded");
}
</script>








</div>

<div class="menu">
    <a href="users.php">👤 İstifadəçilər</a>
    <a href="orders.php">📦 Sifarişlər</a>
<a href="kateqoriyalar.php">📂 Kateqoriyalar</a>
<a href="is_emri.php">🛠 İş Əmrləri</a>
<a href="kateqoriya_sablonlari.php">🔧 Şablonlar</a>
<a href="materiallar.php">
    <i class="fa fa-box"></i> Materiallar
</a>





</div>

<div class="container">
    <h2>Dashboard</h2>
    <p>Yuxarıdan İstifadəçilər və ya Sifarişlər bölməsinə keçə bilərsiniz.</p>
</div>


<li class="nav-item">
    <a href="is_emri_dashboard.php" class="nav-link">
        🛠 İş Əmrini Yönləndir
    </a>
</li>




</body>
</html>
