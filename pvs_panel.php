<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'pvs_usta') {
    die("Bu səhifəyə yalnız PVS ustası daxil ola bilər.");
}

/* -------------------------------------------------
   🔥 Arxiv tarixləri – PVS-ə düşən detalların tarixi
------------------------------------------------- */
$datesQ = $conn->query("
    SELECT DISTINCT DATE(d.tamamlanma_tarixi) AS tar
    FROM is_emri_detallar d
    WHERE d.pvs_status IS NOT NULL
      AND d.tamamlanma_tarixi IS NOT NULL
    ORDER BY tar DESC
");
if (!$datesQ) {
    die("SQL Xətası (arxiv tarixləri): " . $conn->error);
}

$tarixler = [];
while ($d = $datesQ->fetch_assoc()) {
    $tarixler[] = $d['tar'];
}

/* -------------------------------------------------
   🔥 Tarix filtr – default: son 10 gün
------------------------------------------------- */
$gun_filter = "";
if (isset($_GET['gun']) && $_GET['gun'] !== '') {
    $gun = $conn->real_escape_string($_GET['gun']);
    $gun_filter = " AND DATE(d.tamamlanma_tarixi) = '$gun' ";
} else {
    // son 10 gün
    $gun_filter = " AND d.tamamlanma_tarixi >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) ";
}

/* -------------------------------------------------
   🔥 PVS detallarını çək
------------------------------------------------- */
$sql = "
SELECT 
    d.id,
    d.sifaris_id,
    d.material_id,
    d.en,
    d.uzunluq,
    d.qalinliq,
    d.pvs_status,
    d.pvs,
    d.tamamlanma_tarixi,
    s.aciqlama,
    s.pvs_baslama,
    s.pvs_bitme,
    m.ad AS material_adi
FROM is_emri_detallar d
LEFT JOIN sifarisler  s ON d.sifaris_id  = s.id
LEFT JOIN materiallar m ON d.material_id = m.id
WHERE d.pvs_status IS NOT NULL
  $gun_filter
ORDER BY d.sifaris_id DESC, d.id ASC
";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Xətası (PVS siyahısı): " . $conn->error . "<br><br><pre>$sql</pre>");
}

/* -------------------------------------------------
   🔥 Sifarişə görə qrupla
------------------------------------------------- */
$groups = [];
while ($row = $result->fetch_assoc()) {
    $groups[$row['sifaris_id']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>PVS Usta Paneli</title>

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
    margin:6px 0;
    color:#0066cc;
    text-decoration:none;
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

.time-area{
    text-align:center;
    font-size:17px;
    margin:10px 0;
}

table{
    width:100%;
    border-collapse:collapse;
}
th{
    text-align:left;
    padding:12px;
    border-bottom:2px solid #444;
    font-size:18px;
}
td{
    padding:12px;
    border-bottom:1px solid #ccc;
    font-size:18px;
}

tr:hover{
    background:#f5f5f5;
    cursor:pointer;
}

.completed{
    background:#d7ffd7 !important;
}

.status-pvs{
    font-size:18px;
    font-weight:bold;
}

.wait { color:#d67a00; }
.done { color:#0b8a1e; }

.pvs-tag{
    padding:4px 7px;
    border-radius:5px;
    font-weight:bold;
    margin-right:4px;
}
.u-tag{ background:#cfe3ff; color:#004cbd; }
.q-tag{ background:#ffe1c4; color:#c45a00; }
</style>

<script>
function pvsDone(id){
    fetch("update_pvs.php", {
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:"id="+id
    }).then(r=>r.text()).then(t=>{
        if(t === "ok"){
            let el = document.getElementById("pvs_"+id);
            el.innerHTML = "✓ Tamamlandı";
            el.classList.remove("wait");
            el.classList.add("done");

            document.getElementById("row_"+id).classList.add("completed");
        }
    });
}
</script>

</head>
<body>

<h2 style="text-align:center; font-size:30px; margin-bottom:25px;">
🔧 PVS Usta Paneli
</h2>

<!-- 🔥 ARXİV -->
<div class="archive-box">
    <h3>📁 Gündəlik Arxiv</h3>

    <a class="archive-link" href="pvs_panel.php">📅 Bugün (<?= date("d.m.Y") ?>)</a>

    <?php foreach($tarixler as $t): ?>
        <a class="archive-link" href="pvs_panel.php?gun=<?= $t ?>">
            📅 <?= date("d.m.Y", strtotime($t)) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if(empty($groups)): ?>
<h3 style="text-align:center; margin-top:50px;">Bu gün PVS işi yoxdur 😊</h3>
<?php endif; ?>

<?php foreach($groups as $sifaris_id => $items): ?>
<div class="order-box">

    <div class="order-title">
        📦 Sifariş №<?= $sifaris_id ?> — <?= htmlspecialchars($items[0]['aciqlama']) ?>
    </div>

    <div class="time-area">
        <div><b>Başlama vaxtı:</b> <?= $items[0]['pvs_baslama'] ?: "—" ?></div>
        <div style="margin-top:5px;"><b>Bitmə vaxtı:</b> <?= $items[0]['pvs_bitme'] ?: "—" ?></div>
    </div>

    <table>
        <tr>
            <th>#</th>
            <th>Material</th>
            <th>Ölçü</th>
            <th>PVS</th>
            <th>Status</th>
        </tr>

        <?php foreach($items as $r): 
            $pvs = json_decode($r['pvs'], true);
        ?>
        <tr id="row_<?= $r['id'] ?>" onclick="pvsDone(<?= $r['id'] ?>)"
            class="<?= $r['pvs_status']=='tamamlandi_pvs' ? 'completed' : '' ?>">

            <td><?= $r['id'] ?></td>

            <td><?= htmlspecialchars($r['material_adi'] ?: "—") ?></td>

            <td><b><?= $r['en'] ?> × <?= $r['uzunluq'] ?> mm</b></td>

            <td>
                <?php if ($pvs): ?>
                    <?php foreach($pvs as $side): ?>
                        <?php if($side=="uzun1"): ?><span class="pvs-tag u-tag">U1</span><?php endif; ?>
                        <?php if($side=="uzun2"): ?><span class="pvs-tag u-tag">U2</span><?php endif; ?>
                        <?php if($side=="qisa1"): ?><span class="pvs-tag q-tag">Q1</span><?php endif; ?>
                        <?php if($side=="qisa2"): ?><span class="pvs-tag q-tag">Q2</span><?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>

            <td id="pvs_<?= $r['id'] ?>" 
                class="status-pvs <?= $r['pvs_status']=='gozleyir_pvs'?'wait':'done' ?>">
                <?= $r['pvs_status']=='gozleyir_pvs' ? 'Gözləyir' : '✓ Tamamlandı' ?>
            </td>

        </tr>
        <?php endforeach; ?>
    </table>

</div>
<?php endforeach; ?>

</body>
</html>
