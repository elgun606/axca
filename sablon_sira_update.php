<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied");
}

$sablon_id = intval($_POST['sablon_id'] ?? 0);
$order = $_POST['order'] ?? [];

if ($sablon_id <= 0) {
    die("Sablon ID error");
}

if (!is_array($order) || count($order) == 0) {
    die("No order received");
}

/*
   order array belə gəlir:
   [41, 39, 38, 37, 36]

   Biz bunu DB-də sira = 1,2,3,4 kimi yeniləməliyik.
*/

$sira = 1;

foreach ($order as $addim_id) {

    $addim_id = intval($addim_id);
    if ($addim_id > 0) {

        $conn->query("
            UPDATE is_emri_sablon_addimlari
            SET sira = $sira
            WHERE id = $addim_id
        ");

        $sira++;
    }
}

echo "OK";
?>
