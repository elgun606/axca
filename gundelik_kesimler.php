<?php
session_start();
require 'db.php';

$bugun = date("Y-m-d");

$sql = "
SELECT d.*, s.aciqlama, m.ad AS material_adi
FROM is_emri_detallar d
LEFT JOIN sifarisler s ON d.sifaris_id = s.id
LEFT JOIN materiallar m ON m.id = d.material_id
WHERE d.tamamlanma_tarixi = ?
ORDER BY d.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bugun);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Bu günün kəsimləri</title>
<style>
body{font-family:Arial;padding:20px;background:#f3f3f3;}
.box{background:white;padding:20px;border-radius:10px;max-width:900px;margin:auto;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ccc;padding:10px;text-align:left;}
th{background:#eee;}
</style>
</head>
<body>

<div class="box">
<h2>📅 Bu günün kəsimləri — <?= date("d.m.Y") ?></h2>

<table>
<tr>
    <th>#</th>
    <th>Sifariş</th>
    <th>Material</th>
    <th>Ölçü</th>
    <th>Qalınlıq</th>
</tr>

<?php while($r = $res->fetch_assoc()): ?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= $r['sifaris_id'] ?> — <?= htmlspecialchars($r['aciqlama']) ?></td>
    <td><?= $r['material_adi'] ?></td>
    <td><?= $r['en'] ?> × <?= $r['uzunluq'] ?></td>
    <td><?= $r['qalinliq'] ?> mm</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</body>
</html>
