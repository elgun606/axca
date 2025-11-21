<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Yanlış müraciət.");
}

$sifaris_id = intval($_POST['sifaris_id']);
$usta_id    = intval($_POST['usta_id']);

/******************************************************
 🔥 DEBUG – JSON gəlirmi?
******************************************************/
if (!isset($_POST['laminant_json'])) {
    die("laminant_json POST gəlmir ❌");
}

/******************************************************
 🔥 1) DETALLAR — is_emri_detallar
******************************************************/

$lam_json = $_POST['laminant_json'];
$detallar = json_decode($lam_json, true);

if (!is_array($detallar)) {
    die("JSON Xətası: Gələn JSON array deyil ❌");
}

$stmt = $conn->prepare("
    INSERT INTO is_emri_detallar
        (sifaris_id, is_emri_id, ad, en, uzunluq, say, material, pvs, pvs_status, material_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'gozleyir', ?)
");

if (!$stmt) {
    die("PREPARE ERROR: " . $conn->error);
}

foreach ($detallar as $d) {

    $is_emri_id = 0;
    $ad = "Laminant Detalı";

    $en  = intval($d['eni']);
    $uz  = intval($d['uzunluq']);
    $say = 1;

    $material_text = $d['material_text'] ?? "Laminant";
    $material_id   = intval($d['material_id'] ?? 0);

    $pvs_json = json_encode($d['pvs'], JSON_UNESCAPED_UNICODE);

    $stmt->bind_param(
        "iisiiissi",
        $sifaris_id,
        $is_emri_id,
        $ad,
        $en,
        $uz,
        $say,
        $material_text,
        $pvs_json,
        $material_id
    );

    $stmt->execute();

    if ($stmt->error) {
        die("INSERT ERROR: " . $stmt->error);
    }
}

/******************************************************
 🔥 2) REAL ADDIM
******************************************************/
$aciqlama = "Hesabat yaradıldı";
$ins = $conn->prepare("
    INSERT INTO is_emri_real_addimlari (is_emri_addim_id, user_id, aciqlama)
    VALUES (?, ?, ?)
");
$ins->bind_param("iis", $sifaris_id, $usta_id, $aciqlama);
$ins->execute();

/******************************************************
 🔥 3) sifaris → hesabat_status + kesim_baslama
******************************************************/
$conn->query("UPDATE sifarisler SET kesim_baslama = NOW(), hesabat_status = 1 WHERE id = $sifaris_id");

/******************************************************
 🔥 4) Redirect
******************************************************/
header("Location: is_emri_dashboard.php?ok=1");
exit;

?>
