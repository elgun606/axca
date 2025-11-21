<?php
require 'db.php';

$id = intval($_POST['id']);

// 1️⃣ Detalın statusunu oxu
$q = $conn->query("SELECT sifaris_id, pvs_status FROM is_emri_detallar WHERE id=$id");
$row = $q->fetch_assoc();

if (!$row) {
    die("tapilmadi");
}

$sifaris_id = $row['sifaris_id'];
$current_status = $row['pvs_status'];

// 2️⃣ Əgər PVS gözləyir → tamam et
if ($current_status == 'gozleyir_pvs') {

    $upd = $conn->prepare("
        UPDATE is_emri_detallar
        SET pvs_status='tamamlandi_pvs',
            pvs_tarixi = NOW()
        WHERE id=?
    ");
    $upd->bind_param("i", $id);
    $upd->execute();

    // 🔥 Sifarişdə başlama vaxtı boşdursa → indi yaz
    $conn->query("
        UPDATE sifarisler
        SET pvs_baslama = IF(pvs_baslama IS NULL, NOW(), pvs_baslama)
        WHERE id=$sifaris_id
    ");
}

// 3️⃣ Sifariş tam bitibsə → bitmə vaxtını yaz
$q2 = $conn->query("
    SELECT COUNT(*) AS total,
           SUM(pvs_status='tamamlandi_pvs') AS done
    FROM is_emri_detallar
    WHERE sifaris_id = $sifaris_id
      AND pvs IS NOT NULL
");

$chk = $q2->fetch_assoc();

if ($chk['total'] == $chk['done'] && $chk['total'] > 0) {
    $conn->query("
        UPDATE sifarisler
        SET pvs_bitme = NOW()
        WHERE id = $sifaris_id
    ");
}

echo "ok";
?>
