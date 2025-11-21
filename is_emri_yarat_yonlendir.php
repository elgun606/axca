<?php
session_start();
require 'db.php';

// Yalnız admin görə bilər
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Bu səhifəyə yalnız admin daxil ola bilər.");
}

$selected = isset($_POST['sifaris_id']);
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>İş Əmrini Yarat & Yönləndir</title>
<style>
body { font-family:Arial; padding:20px; background:#f3f3f3; }
.box  { background:white; padding:20px; border-radius:8px; max-width:880px; margin:auto; }
select, button, input { padding:10px; margin-top:10px; width:100%; box-sizing:border-box; }
h2   { text-align:center; }
label{ font-weight:bold; display:block; margin-top:10px; }
.info{ background:#eefbe8; padding:12px; border-radius:5px; margin-top:10px; border:1px solid #a7dd98; }
.side-boxes { display:flex; gap:10px; margin-top:10px; }
.side-item { flex:1; background:#f7f7f7; padding:10px; border-radius:6px; border:1px solid #ccc; text-align:center; }
ul li { background:#f1f1f1; padding:6px; border-radius:6px; margin-bottom:5px; }
</style>
</head>
<body>

<h2>🛠 İş Əmrini Yarat & Yönləndir</h2>
<div class="box">

<?php if (!$selected): ?>

    <form method="POST">
        <label>Hesabatı verilməmiş sifarişi seçin:</label>
        <select name="sifaris_id" required onchange="this.form.submit()">
            <option value="">-- Seçin --</option>
            <?php
            $qe = $conn->query("
                SELECT id, aciqlama, kategoriya 
                FROM sifarisler 
                WHERE id NOT IN (SELECT sifaris_id FROM is_emri_real_addimlari)
                ORDER BY id DESC
            ");
            while ($s = $qe->fetch_assoc()):
            ?>
                <option value="<?= $s['id'] ?>">
                    <?= $s['id'] ?> — <?= htmlspecialchars($s['aciqlama']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

<?php else: ?>

<?php
$sid = intval($_POST['sifaris_id']);

$q = $conn->prepare("
   SELECT s.aciqlama, s.kategoriya, k.ad AS kat_adi
   FROM sifarisler s
   JOIN kateqoriyalar k ON s.kategoriya = k.id
   WHERE s.id=?
");
$q->bind_param("i", $sid);
$q->execute();
$row = $q->get_result()->fetch_assoc();

$aciq  = $row['aciqlama'];
$kat_id = $row['kategoriya'];
$kat_adi = $row['kat_adi'];
$kat_lower = mb_strtolower($kat_adi, 'UTF-8');

// Auto misar kəsimçi
$misar_id = 17;
$auto = in_array($kat_lower, ["laminant","mdf","mdf kəsim"]);

function mats($conn,$id){
    $m = $conn->prepare("SELECT id, ad FROM materiallar WHERE kategoriya_id = ?");
    $m->bind_param("i",$id);
    $m->execute();
    return $m->get_result();
}
?>

<div class="info">
    <b>Sifariş №<?= $sid ?></b><br>
    Açıqlama: <?= $aciq ?><br>
    Kateqoriya: <b><?= $kat_adi ?></b>
</div>

<form method="POST" action="is_emri_yarat_auto.php">
    <input type="hidden" name="sifaris_id" value="<?= $sid ?>">

<?php if ($auto): ?>
    <div class="info">Avtomatik olaraq <b>misar_kesimci</b>-yə yönləndiriləcək.</div>
    <input type="hidden" name="usta_id" value="<?= $misar_id ?>">
<?php else: ?>
    <label>Usta seç:</label>
    <select name="usta_id">
        <option value="">-- Usta seç --</option>
        <?php
        $us = $conn->query("SELECT id, login FROM users ORDER BY login ASC");
        while($u=$us->fetch_assoc()):
        ?>
            <option value="<?= $u['id'] ?>"><?= $u['login'] ?></option>
        <?php endwhile; ?>
    </select>
<?php endif; ?>



<!-- ==================== LAMİNANT ==================== -->
<?php if ($kat_lower==="laminant"): ?>

<h3>🔧 Laminant Kəsim</h3>

<label>Material seç:</label>
<select id="lam_mat">
    <option value="">-- Seçin --</option>
    <?php $mm=mats($conn,$kat_id); while($m=$mm->fetch_assoc()): ?>
    <option value="<?= $m['id'] ?>"><?= $m['ad'] ?></option>
    <?php endwhile; ?>
</select>

<label>Eni (mm):</label>
<input type="number" id="lam_eni">

<label>Uzunluq (mm):</label>
<input type="number" id="lam_uzun">

<h3>PVS (kənar bant)</h3>
<div class="side-boxes">
    <div class="side-item"><label>Uzun 1</label><br><input type="checkbox" class="lam_pvs" value="uzun1"></div>
    <div class="side-item"><label>Uzun 2</label><br><input type="checkbox" class="lam_pvs" value="uzun2"></div>
    <div class="side-item"><label>Qısa 1</label><br><input type="checkbox" class="lam_pvs" value="qisa1"></div>
    <div class="side-item"><label>Qısa 2</label><br><input type="checkbox" class="lam_pvs" value="qisa2"></div>
</div>

<button type="button" onclick="addLam()">➕ Kəsim əlavə et</button>

<ul id="lam_list"></ul>
<input type="hidden" name="laminant_json" id="laminant_json">

<script>
let lam=[];
function addLam(){
    let mat=document.getElementById("lam_mat").value;
    let eni=document.getElementById("lam_eni").value;
    let uzun=document.getElementById("lam_uzun").value;

    if(!mat||!eni||!uzun){ alert("Boş sahə var!"); return; }

    let pvs=[];
    document.querySelectorAll(".lam_pvs:checked").forEach(x=>pvs.push(x.value));

    lam.push({material_id:mat, eni:eni, uzunluq:uzun, pvs:pvs});
    laminant_json.value=JSON.stringify(lam);

    let li=document.createElement("li");
    li.innerHTML="["+mat+"] — "+eni+"×"+uzun+" PVS: "+pvs.join(",");
    lam_list.appendChild(li);
}
</script>

<?php endif; ?>



<!-- ==================== MDF ==================== -->
<?php if ($kat_lower==="mdf"): ?>

<h3>🟫 MDF Kəsim</h3>

<label>Material seç:</label>
<select id="mdf_mat">
    <option value="">-- Seçin --</option>
    <?php $mm=mats($conn,$kat_id); while($m=$mm->fetch_assoc()): ?>
    <option value="<?= $m['id'] ?>"><?= $m['ad'] ?></option>
    <?php endwhile; ?>
</select>

<label>Eni:</label>
<input type="number" id="mdf_eni">

<label>Uzunluq:</label>
<input type="number" id="mdf_uzun">

<button type="button" onclick="addMDF()">➕ Əlavə et</button>

<ul id="mdf_list"></ul>
<input type="hidden" name="mdf_json" id="mdf_json">

<script>
let mdf=[];
function addMDF(){
    let mat=mdf_mat.value, eni=mdf_eni.value, uz=mdf_uzun.value;
    if(!mat||!eni||!uz){ alert("Boş sahə!"); return; }
    mdf.push({material_id:mat, eni:eni, uzunluq:uz});
    mdf_json.value=JSON.stringify(mdf);

    let li=document.createElement("li");
    li.innerHTML="MDF ["+mat+"] — "+eni+"×"+uz;
    mdf_list.appendChild(li);
}
</script>

<?php endif; ?>



<!-- ==================== METAL ==================== -->
<?php if ($kat_lower==="metal"): ?>

<h3>⚙️ Metal İşləri</h3>

<label>Material seç:</label>
<select id="met_mat">
    <option value="">-- Seçin --</option>
    <?php $mm=mats($conn,$kat_id); while($m=$mm->fetch_assoc()): ?>
    <option value="<?= $m['id'] ?>"><?= $m['ad'] ?></option>
    <?php endwhile; ?>
</select>

<label>Açıqlama:</label>
<input type="text" id="met_info">

<button type="button" onclick="addMet()">➕ Əlavə et</button>

<ul id="met_list"></ul>
<input type="hidden" name="metal_json" id="metal_json">

<script>
let metal=[];
function addMet(){
    let mat=met_mat.value, inf=met_info.value;
    if(!mat||!inf){ alert("Boş sahə!"); return; }
    metal.push({material_id:mat, info:inf});
    metal_json.value=JSON.stringify(metal);

    let li=document.createElement("li");
    li.innerHTML="["+mat+"] — "+inf;
    met_list.appendChild(li);
}
</script>

<?php endif; ?>



<br><br>
<button type="submit" style="background:#222; color:#fff;">Tapşırıq Yarat →</button>

</form>

<?php endif; ?>
</div>

</body>
</html>
