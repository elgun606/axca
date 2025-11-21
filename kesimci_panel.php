<?php
session_start();
require 'db.php';

// Ustanın rolu uyğun deyil
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'misar_kesimci') {
    die("Bu səhifəyə yalnız kəsimçi daxil ola bilər.");
}

/*
  Kategroiyalar:
  1 = Laminant
  2 = MDF
*/

$sql = "
SELECT d.id, d.sifaris_id, d.kategoriya, d.en, d.uzunluq, d.qalinliq, d.status,
       s.aciklama
FROM is_emri_detallar d
LEFT JOIN sifarisler s ON d.sifaris_id = s.id
WHERE d.kategoriya IN (1,2)
ORDER BY d.id DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang='az'>
<head>
<meta charset='UTF-8'>
<title>Kəsimçi Paneli</title>
<style>
body{
    font-family:Arial;
    padding:20px;
    background:#f5f5f5;
}
h2{
    text-align:center;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:white;
}
th,td{
    padding:12px;
    border:1px solid #ccc;
    text-align:center;
}
.status{
    color:orange;
    font-weight:bold;
}
thead{
    background:#eee;
}
</style>
</head>
<body>

<h2>🪚 Kəsimçi paneli – misar_kesimci</h2>
<p style="text-align:center; color:#666;">Burada yalnız Laminant və MDF kəsim detallarınız görünür.</p>

<table>
<thead>
<tr>
    <th>#</th>
    <th>Sifariş №</th>
    <th>Açıqlama</th>
    <th>Kateqoriya</th>
    <th>En (mm)</th>
    <th>Uzunluq (mm)</th>
    <th>Qalınlıq</th>
    <th>Status</th>
</tr>
</thead>

<tbody>
<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['sifaris_id'] ?></td>
    <td><?= $row['aciklama'] ?></td>
    <td>
        <?= ($row['kategoriya'] == 1 ? "Laminant" : "MDF") ?>
    </td>
    <td><?= $row['en'] ?></td>
    <td><?= $row['uzunluq'] ?></td>
    <td><?= $row['qalinliq'] ?></td>
    <td class="status"><?= $row['status'] ?></td>
</tr>
<?php } ?>
</tbody>

</table>

</body>
</html>
