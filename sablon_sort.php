<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$ids = $data['ids'];

$sira = 1;
foreach ($ids as $id) {
    $conn->query("UPDATE is_emri_sablon_addimlari SET sira=$sira WHERE id=$id");
    $sira++;
}

echo "ok";
