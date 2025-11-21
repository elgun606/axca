<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cats = $conn->query("SELECT * FROM kateqoriyalar ORDER BY ad ASC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Şablonlar</title>

<style>
    body {
        font-family: Arial;
        background: #eef1f5;
        margin: 0;
        padding: 20px;
    }

    h2 {
        margin-bottom: 15px;
        display: inline-block;
    }

    .back-btn {
        background: #444;
        color: #fff;
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
        margin-right: 10px;
    }

    /* Yeni Şablon Butonu */
    .add-btn {
        background: #28a745;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        float: right;
        margin-top: -5px;
        font-weight: bold;
    }
    .add-btn:hover {
        background: #1f8a38;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        transition: 0.2s;
    }

    .card:hover {
        transform: scale(1.03);
    }

    .card h3 {
        margin: 0;
        font-size: 20px;
        margin-bottom: 10px;
    }

    .btn {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        background: #007bff;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
    }

    .btn:hover {
        background: #0066d3;
    }
</style>

</head>
<body>

<h2>🔧 İş Əmri Şablonları</h2>

<a class="back-btn" href="admin.php">⬅ Geri</a>

<!-- ⚡ YENİ ŞABLON DÜYMƏSİ -->
<a class="add-btn" href="sablon_yeni.php">+ Yeni Şablon Əlavə Et</a>

<div style="clear: both;"></div>

<div class="grid">

<?php while($c = $cats->fetch_assoc()): ?>
    <div class="card">
        <h3><?= htmlspecialchars($c['ad']) ?></h3>
        <p>ID: <?= $c['id'] ?></p>

        <!-- Şablonlara yönləndirmə -->
        <a class="btn" href="sablon_gor.php?sablon_id=<?= $c['id'] ?>">
            ⚙ Şablonu Aç
        </a>

    </div>
<?php endwhile; ?>

</div>

</body>
</html>
