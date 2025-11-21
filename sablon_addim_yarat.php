<?php
session_start();
require 'db.php';

/* ----------------------------------------------------
   1) Giriş yoxlanışı
---------------------------------------------------- */
if (!isset($_SESSION['user_id'])) {
    die("❌ İcazə yoxdur!");
}

/* ----------------------------------------------------
   2) Sablon ID
---------------------------------------------------- */
$sablon_id = intval($_GET['sablon_id'] ?? 0);
if ($sablon_id <= 0) {
    die("❌ Xəta: sablon_id düzgün gəlmədi!");
}

/* ----------------------------------------------------
   3) POST məlumatları
---------------------------------------------------- */
$is_emri_id = intval($_POST['is_emri_id'] ?? 0);
$ustalar    = $_POST['usta'] ?? [];   // MULTI SELECT
$sira       = intval($_POST['sira'] ?? 0);
$tip        = $_POST['tip'] ?? "single";  // 🔥 YENİ SAHƏ

if ($is_emri_id <= 0) {
    die("<b style='color:red;'>❌ İş əmri seçilməyib!</b>");
}
if (!is_array($ustalar) || count($ustalar) == 0) {
    die("<b style='color:red;'>❌ Usta seçilməyib!</b>");
}
if ($sira <= 0) {
    die("<b style='color:red;'>❌ Sıra 0 ola bilməz!</b>");
}

/* ----------------------------------------------------
   4) Ustaları CSV formatına çevir (1,4,7)
---------------------------------------------------- */
$usta_csv = [];

foreach ($ustalar as $u) {
    $u = intval($u);
    if ($u > 0) {
        $usta_csv[] = $u;
    }
}

$usta_str = implode(",", $usta_csv);  // "1,4,7"

/* ----------------------------------------------------
   5) ADDIM ƏLAVƏ ET (INSERT)
---------------------------------------------------- */
$sql = "
INSERT INTO is_emri_sablon_addimlari 
(sablon_id, is_emri_id, usta, sira, tip)
VALUES (?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("❌ PREPARE ERROR: " . $conn->error);
}

$stmt->bind_param("iisis", $sablon_id, $is_emri_id, $usta_str, $sira, $tip);
//            i   i    s     i     s

if (!$stmt->execute()) {
    die("❌ EXECUTE ERROR: " . $stmt->error);
}

/* ----------------------------------------------------
   6) Redirect
---------------------------------------------------- */
header("Location: sablon_gor.php?sablon_id=" . $sablon_id . "&ok=1");
exit;

?>
