<?php
require 'db.php';

$id = intval($_POST['id']);

// 1️⃣ Detal statusunu oxu
$q = $conn->query("SELECT sifaris_id, status FROM is_emri_detallar WHERE id=$id");
if(!$q){ die("SQL Xətası: ".$conn->error); }

$row = $q->fetch_assoc();
$sifaris_id = $row['sifaris_id'];
$current_status = $row['status'];

// ----------------------------------------------------------
// 2️⃣ Əgər GÖZLƏYİR idisə → TƏAMAMLANDI + PVS-ə göndər
// ----------------------------------------------------------
if ($current_status === 'gozleyir') {

    $upd = $conn->prepare("
        UPDATE is_emri_detallar 
        SET 
            status='tamamlandi',
            pvs_status='gozleyir_pvs',
            tamamlanma_tarixi = NOW()
        WHERE id=?
    ");
    $upd->bind_param("i", $id);
    $upd->execute();

    // 🔥 Kəsim başlama vaxtı yoxdursa → indi yaz
    $conn->query("
        UPDATE sifarisler
        SET kesim_baslama = IF(kesim_baslama IS NULL, NOW(), kesim_baslama)
        WHERE id = $sifaris_id
    ");
}

// ----------------------------------------------------------
// 3️⃣ Bütün detallar bitibsə → sifarişin kəsim bitmə vaxtını yaz
// ----------------------------------------------------------
$check = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(status='tamamlandi') AS done
    FROM is_emri_detallar
    WHERE sifaris_id = $sifaris_id
");

$info = $check->fetch_assoc();

// 🔥 Bütün kəsim bitibsə → bitmə vaxtı
if ($info['total'] == $info['done']) {
    $conn->query("
        UPDATE sifarisler
        SET kesim_bitme = NOW()
        WHERE id = $sifaris_id
    ");
}

echo "ok";
exit;
?>
