<?php
require 'db.php';

$kateqoriya_id = intval($_POST['kateqoriya_id']);
$is_emri_id    = intval($_POST['is_emri_id']);

$conn->query("
    INSERT INTO is_emri_sablon_addimlari (kateqoriya_id, is_emri_id, sira)
    VALUES ($kateqoriya_id, $is_emri_id,
        (SELECT IFNULL(MAX(sira),0)+1 FROM is_emri_sablon_addimlari WHERE kateqoriya_id=$kateqoriya_id)
    )
");

header("Location: kateqoriya_sablonlari.php?id=$kateqoriya_id");
