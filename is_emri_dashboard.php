<?php
session_start();
require 'db.php';

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
.box  { background:white; padding:20px; border-radius:8px; max-width:850px; margin:auto; }
select, button, input { padding:10px; margin-top:10px; width:100%; box-sizing:border-box; }
h2 { text-align:center; }
label{ font-weight:bold; display:block; margin-top:10px; }
.info{ background:#eefbe8; padding:12px; border-radius:5px; margin-top:10px; border:1px solid #a7dd98; }
.side-boxes { display:flex; gap:10px; margin-top:10px; }
.side-item { flex:1; background:#f7f7f7; padding:10px; border-radius:6px; border:1px solid #ccc; text-align:center; cursor:pointer; }
.side-item input { pointer-events:none; }
ul li { background:#f1f1f1; padding:6px; border-radius:6px; margin-bottom:5px; }
canvas{ background:white; border:1px solid #ccc; margin-top:15px; display:none; }
</style>
</head>
<body>

<h2>🛠 İş Əmrini Yarat & Yönləndir</h2>
<div class="box">

<?php if (!$selected): ?>

<!-- ================== SİFARİŞ SEÇİMİ ================== -->
<form method="POST">
    <label>Hesabatı verilməmiş sifarişi seçin:</label>
    <select name="sifaris_id" required onchange="this.form.submit()">
        <option value="">-- Seçin --</option>

        <?php
        // yalnız hələ iş əmri olmayan sifarişlər
        $qe = $conn->query("
            SELECT id, aciqlama, kateqoriya_id
            FROM sifarisler
            WHERE id NOT IN (SELECT sifaris_id FROM is_emri)
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
// ================== SEÇİLMİŞ SİFARİŞ GET ==================

$sid = intval($_POST['sifaris_id']);

$q = $conn->prepare("
   SELECT s.aciqlama, s.kateqoriya_id, k.ad AS kat_adi
   FROM sifarisler s
   JOIN kateqoriyalar k ON s.kateqoriya_id = k.id
   WHERE s.id=?
");
$q->bind_param("i", $sid);
$q->execute();
$row = $q->get_result()->fetch_assoc();

$aciq     = $row['aciqlama'];
$kat_id   = $row['kateqoriya_id'];
$kat_adi  = $row['kat_adi'];
$lower    = mb_strtolower($kat_adi, 'UTF-8');

// avtomatik misar yönləndirmə
$misar_id = 17;
$auto = in_array($lower, ["laminant", "mdf", "mdf kəsim"]);

function mats($conn, $id){
    $m = $conn->prepare("SELECT id, ad FROM materiallar WHERE kateqoriya_id=?");
    $m->bind_param("i", $id);
    $m->execute();
    return $m->get_result();
}
?>

<div class="info">
    <b>Sifariş №<?= $sid ?></b><br>
    Açıqlama: <?= htmlspecialchars($aciq) ?><br>
    Kateqoriya: <b><?= $kat_adi ?></b>
</div>

<!-- ================== FORM BAŞLAYIR ================== -->

<form method="POST" action="is_emri_yarat_auto.php">
    <input type="hidden" name="sifaris_id" value="<?= $sid ?>">

<?php if ($auto): ?>
    <div class="info">Bu sifariş avtomatik <b>misar_kesimci</b>-yə yönləndiriləcək.</div>
    <input type="hidden" name="usta_id" value="<?= $misar_id ?>">

<?php else: ?>

    <label>Usta seç:</label>
    <select name="usta_id" required>
        <option value="">-- Usta seç --</option>
        <?php
        $us = $conn->query("SELECT id, login FROM users ORDER BY login ASC");
        while ($u = $us->fetch_assoc()):
        ?>
            <option value="<?= $u['id'] ?>"><?= $u['login'] ?></option>
        <?php endwhile; ?>
    </select>

<?php endif; ?>

<?php if (in_array($lower, ["laminant","mdf","mdf kəsim"])): ?>

<h3>🔧 <?= ($lower === 'laminant') ? 'Laminant' : 'MDF'; ?> Kəsim</h3>

<label>Material seç:</label>
<select id="lam_mat">
    <option value="">-- Seçin --</option>
    <?php $mm = mats($conn, $kat_id); while ($m = $mm->fetch_assoc()): ?>
        <option value="<?= $m['id'] ?>"><?= $m['ad'] ?></option>
    <?php endwhile; ?>
</select>

<label>Eni (mm):</label>
<input type="number" id="lam_eni">

<label>Uzunluq (mm):</label>
<input type="number" id="lam_uzun">

<div class="side-boxes">
    <div class="side-item"><label>Uzun 1</label><br><input type="checkbox" class="lam_pvs" value="uzun1"></div>
    <div class="side-item"><label>Uzun 2</label><br><input type="checkbox" class="lam_pvs" value="uzun2"></div>
    <div class="side-item"><label>Qısa 1</label><br><input type="checkbox" class="lam_pvs" value="qisa1"></div>
    <div class="side-item"><label>Qısa 2</label><br><input type="checkbox" class="lam_pvs" value="qisa2"></div>
</div>

<button type="button" onclick="addLam()">➕ Əlavə et</button>

<ul id="lam_list"></ul>

<input type="hidden" name="laminant_json" id="laminant_json">

<?php endif; ?>

<!-- ================= PANEL/Rastonovka ================= -->

<h3>📐 Panel ölçüləri</h3>

<label>Panel eni (mm):</label>
<input type="number" id="panel_eni" placeholder="2440">

<label>Panel uzunluğu (mm):</label>
<input type="number" id="panel_uzun" placeholder="1830">

<label>Kəsim istiqaməti:</label>
<select id="cut_dir">
    <option value="uzununa">Uzununa</option>
    <option value="enine">Eninə</option>
</select>

<button type="button" onclick="rastonovka()" style="background:#0066ff;color:white;">
    🧩 Rastonovka et
</button>

<div id="rasto_result" style="display:none; margin-top:10px;"></div>

<canvas id="rasto_canvas" width="800" height="600"></canvas>

<br><br>
<button type="submit" style="background:#222; color:#fff;">Tapşırıq Yarat →</button>
</form>

<?php endif; ?>

</div>

<script>
// ====================== LAMİNANT/MDF DETALLARI ======================
let lam = [];

function addLam(){
    let mat = document.getElementById('lam_mat').value;
    let eni = document.getElementById('lam_eni').value;
    let uz  = document.getElementById('lam_uzun').value;

    if(!mat || !eni || !uz){
        alert("Boş sahə var!");
        return;
    }

    let pvs = [];
    document.querySelectorAll(".lam_pvs:checked").forEach(x => pvs.push(x.value));

    lam.push({material_id:mat, eni:parseInt(eni), uzunluq:parseInt(uz), pvs:pvs});
    document.getElementById('laminant_json').value = JSON.stringify(lam);

    let li = document.createElement("li");
    li.innerHTML = "["+mat+"] "+eni+"×"+uz+" PVS: "+pvs.join(",");
    document.getElementById('lam_list').appendChild(li);

    document.getElementById('lam_eni').value="";
    document.getElementById('lam_uzun').value="";
    document.querySelectorAll(".lam_pvs").forEach(x=>x.checked=false);
}

// CheckBox klik düzəlişi
document.querySelectorAll(".side-item").forEach(box=>{
    box.addEventListener("click", (e)=>{
        if(e.target.tagName.toLowerCase()==="input") return;
        let cb = box.querySelector("input");
        cb.checked = !cb.checked;
    });
});

// ====================== RASTONOVKA + ÇİZMƏ ======================
const KERF_MM = 4;
const MARGIN_MM = 10;

function layoutShelves(panelW, panelH, pieces){
    const usableW = panelW - 2*MARGIN_MM;
    const usableH = panelH - 2*MARGIN_MM;

    let remaining = pieces.map(p => ({...p}));
    remaining.sort((a,b)=> b.h - a.h || b.w - a.w);

    let placed = [];
    let y = 0;

    while(remaining.length > 0){
        let rowH = remaining[0].h;
        let x = 0;

        for(let i=0; i<remaining.length; ){
            let p = remaining[i];
            if(p.h <= rowH && x + p.w <= usableW){
                placed.push({
                    ...p,
                    x: MARGIN_MM + x,
                    y: MARGIN_MM + y
                });
                x += p.w + KERF_MM;
                remaining.splice(i,1);
            }else{
                i++;
            }
        }

        y += rowH + KERF_MM;
        if(y > usableH) break;
    }

    return placed;
}

function drawRasto(panelW, panelH, placed){
    const c = document.getElementById("rasto_canvas");
    const ctx = c.getContext("2d");
    c.style.display="block";

    ctx.clearRect(0,0,c.width,c.height);

    const scale = Math.min(700/panelW, 450/panelH);
    const offsetX = 40;
    const offsetY = 30;

    const boardW = panelW * scale;
    const boardH = panelH * scale;

    ctx.strokeStyle = "#000";
    ctx.lineWidth = 2;
    ctx.strokeRect(offsetX, offsetY, boardW, boardH);

    ctx.font = "13px Arial";
    ctx.textAlign = "center";
    ctx.fillText(panelW + " mm", offsetX + boardW/2, offsetY + boardH + 18);

    ctx.save();
    ctx.translate(offsetX - 20, offsetY + boardH/2);
    ctx.rotate(-Math.PI/2);
    ctx.fillText(panelH + " mm", 0, 0);
    ctx.restore();

    placed.forEach(p=>{
        const x = offsetX + p.x*scale;
        const y = offsetY + p.y*scale;
        const w = p.w*scale;
        const h = p.h*scale;

        ctx.fillStyle = "hsl("+(Math.random()*360)+",70%,70%)";
        ctx.fillRect(x, y, w, h);
        ctx.strokeStyle = "#000";
        ctx.strokeRect(x, y, w, h);

        ctx.fillStyle = "#000";
        ctx.font = "11px Arial";
        ctx.fillText(p.w + "×" + p.h, x + 4, y + 14);
    });
}

function rastonovka(){
    const panelEn = parseInt(document.getElementById('panel_eni').value);
    const panelUz = parseInt(document.getElementById('panel_uzun').value);
    const dir = document.getElementById('cut_dir').value;

    if(!panelEn || !panelUz){
        alert("Panel ölçülərini daxil edin!");
        return;
    }
    if(lam.length === 0){
        alert("Kəsiləcək detal yoxdur!");
        return;
    }

    let boardW = panelEn;
    let boardH = panelUz;

    if(dir === "enine"){
        [boardW, boardH] = [boardH, boardW];
    }

    const pieces = lam.map(d=>({
        w: d.eni,
        h: d.uzunluq
    }));

    const placed = layoutShelves(boardW, boardH, pieces);
    drawRasto(boardW, boardH, placed);

    document.getElementById('rasto_result').innerHTML =
        "<h3>Vizual rastonovka hazırlanmışdır 👇</h3>";
    document.getElementById('rasto_result').style.display="block";
}
</script>

</body>
</html>
