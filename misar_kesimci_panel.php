<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'misar_kesimci') {
    die("Bu səhifəyə yalnız kəsimçi misar daxil ola bilər.");
}

/* ------------------------------------------
   🔥 1) ARXİV – kəsimə başlamış sifarişlərin tarixləri
------------------------------------------ */
$datesQ = $conn->query("
    SELECT DISTINCT DATE(s.kesim_baslama) AS tar
    FROM sifarisler s
    WHERE s.kesim_baslama IS NOT NULL
    ORDER BY tar DESC
");

$tarixler = [];
if ($datesQ) {
    while ($d = $datesQ->fetch_assoc()) {
        $tarixler[] = $d['tar'];
    }
}

/* ------------------------------------------
   🔥 2) TARİX FİLTRİ
------------------------------------------ */
$gun_filter = "";
if (isset($_GET['gun']) && $_GET['gun'] !== "") {
    $gun = $conn->real_escape_string($_GET['gun']);
    $gun_filter = " AND DATE(s.kesim_baslama) = '$gun' ";
}

/* ------------------------------------------
   🔥 3) SON 10 GÜN FİLTRİ
------------------------------------------ */
$date_limit = "";
if (!isset($_GET['gun']) || $_GET['gun'] === "") {
    $date_limit = " AND s.kesim_baslama >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) ";
}

/* ------------------------------------------
   🔥 4) DETALLARI ÇƏK — tam cədvələ uyğunlaşdırıldı
------------------------------------------ */
$sql = "
SELECT 
    d.id,
    d.sifaris_id,
    d.en,
    d.uzunluq,
    d.say,
    d.material AS material_adi,
    d.pvs,
    d.pvs_status,
    d.tamamlama_tarixi,
    s.aciqlama,
    s.kesim_baslama,
    s.kesim_bitme
FROM is_emri_detallar d
LEFT JOIN sifarisler s ON d.sifaris_id = s.id
WHERE 1 
      $gun_filter
      $date_limit
ORDER BY d.sifaris_id DESC, d.id ASC
";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Xətası: " . $conn->error);
}

/* ------------------------------------------
   🔥 5) Sifariş üzrə qruplaşdırma
------------------------------------------ */
$groups = [];
while ($row = $result->fetch_assoc()) {
    $groups[$row['sifaris_id']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Kəsimçi Paneli – misar_kesimci</title>

<style>
body{
    font-family:Arial;
    background:#f2f2f2;
    padding:20px;
}

.archive-box{
    background:white;
    padding:15px;
    margin-bottom:25px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,0.15);
}

.archive-link{
    display:block;
    font-size:18px;
    margin:8px 0;
    text-decoration:none;
    color:#0066cc;
}

.order-box{
    background:white;
    padding:20px;
    margin-bottom:30px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,0.15);
}

.order-title{
    font-size:22px;
    font-weight:bold;
    margin-bottom:10px;
    border-left:6px solid #444;
    padding-left:12px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    padding:12px;
    border-bottom:2px solid #444;
    font-size:18px;
}

td{
    padding:14px;
    border-bottom:1px solid #ccc;
    font-size:20px;
}

.wait { color:#d67a00; font-weight:bold; cursor:pointer; }
.done { color:#0b8a1e; font-weight:bold; cursor:pointer; }
.completed{
    background:#e2ffe2 !important;
}
</style>

<script>
function changeStatus(id){
    fetch("update_status.php", {
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:"id="+id
    }).then(r=>r.text()).then(t=>{
        if(t === "ok"){
            let el = document.getElementById("st_"+id);
            el.innerHTML = "✓ Kesildi";
            el.classList.remove("wait");
            el.classList.add("done");

            document.getElementById("row_"+id).classList.add("completed");
        } else {
            alert("Status yenilənmədi: " + t);
        }
    });
}
</script>

</head>
<body>

<h2 style="text-align:center; font-size:30px; margin-bottom:25px;">
🪚 Kəsimçi Paneli — misar_kesimci
</h2>

<!-- 🔥 ARXİV -->
<div class="archive-box">
    <h3>📁 Gündəlik Kəsim Arxivi</h3>

    <a class="archive-link" href="misar_kesimci_panel.php">📅 Bugün</a>

    <?php foreach($tarixler as $t): ?>
        <a class="archive-link" href="?gun=<?= $t ?>">📅 <?= date("d.m.Y", strtotime($t)) ?></a>
    <?php endforeach; ?>
</div>

<!-- 🔥 SİFARİŞ GRUPLARI -->
<?php foreach($groups as $sifaris_id => $items): ?>
<div class="order-box">

    <div class="order-title">
        📦 Sifariş №<?= $sifaris_id ?> — <?= htmlspecialchars($items[0]['aciqlama']) ?>
    </div>

    <table>
        <tr>
            <th>#</th>
            <th>Material</th>
            <th>Ölçü (En × Uzun)</th>
            <th>Say</th>
            <th>Status</th>
        </tr>

        <?php foreach($items as $r): ?>
        <tr id="row_<?= $r['id'] ?>"
            onclick="changeStatus(<?= $r['id'] ?>)"
            class="<?= $r['pvs_status']=='tamamlandi' ? 'completed' : '' ?>">

            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['material_adi']) ?></td>
            <td><?= $r['en'] ?> × <?= $r['uzunluq'] ?> mm</td>
            <td><?= $r['say'] ?></td>

            <td id="st_<?= $r['id'] ?>" 
                class="<?= $r['pvs_status']=='gozleyir'?'wait':'done' ?>">
                <?= $r['pvs_status']=='gozleyir' ? 'Gözləyir' : '✓ Kesildi' ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>
<?php endforeach; ?>

</body>
</html>
