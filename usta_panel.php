<?php
session_start();
require 'db.php';

// User login yoxlanır
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usta_id = intval($_SESSION['user_id']);
$usta_login = $_SESSION['login'] ?? 'Usta';

/* ======================================================
   ✅ 1) Ustanın real addımları (is_emri_real_addimlari)
   ====================================================== */

$sql = "
    SELECT 
        r.id,
        r.aciqlama AS addim_aciqlama,
        r.baslama,
        r.bitme,

        e.id AS is_emri_id,
        e.sifaris_id,

        s.aciqlama AS sifaris_aciqlama,
        s.kateqoriya_id
    FROM is_emri_real_addimlari r
    JOIN is_emri e ON r.is_emri_addim_id = e.id
    JOIN sifarisler s ON e.sifaris_id = s.id
    WHERE r.user_id = ?
    ORDER BY r.id DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL xətası: " . $conn->error);
}

$stmt->bind_param("i", $usta_id);
$stmt->execute();
$tapshiriqlar = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Usta paneli</title>
    <style>
        body {
            font-family: Arial;
            background:#f3f3f3;
            padding:20px;
        }
        .box {
            max-width:1100px;
            margin:0 auto;
            background:#fff;
            padding:20px;
            border-radius:10px;
        }
        table {
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }
        th, td {
            border:1px solid #ddd;
            padding:10px;
            text-align:center;
        }
        th {
            background:#e7e7e7;
        }
        .header {
            text-align:center;
        }
    </style>
</head>
<body>

<div class="box">
    <h1 class="header">👷‍♂️ Usta paneli – <?= htmlspecialchars($usta_login) ?></h1>
    <p class="header">Bu səhifədə yalnız sənə yönləndirilmiş tapşırıqlar görünür.</p>

    <?php if ($tapshiriqlar->num_rows === 0): ?>
        <p>Hazırda tapşırıq yoxdur.</p>

    <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Sifariş №</th>
                <th>Sifariş Açıqlaması</th>
                <th>Kateqoriya</th>
                <th>Tapşırıq Açıqlaması</th>
                <th>Başlama</th>
                <th>Bitmə</th>
            </tr>

            <?php while ($row = $tapshiriqlar->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['sifaris_id'] ?></td>
                    <td><?= htmlspecialchars($row['sifaris_aciqlama']) ?></td>
                    <td><?= htmlspecialchars($row['kateqoriya_id']) ?></td>
                    <td><?= htmlspecialchars($row['addim_aciqlama']) ?></td>
                    <td><?= $row['baslama'] ?></td>
                    <td><?= $row['bitme'] ?></td>
                </tr>
            <?php endwhile; ?>

        </table>
    <?php endif; ?>

</div>

</body>
</html>
