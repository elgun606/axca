<?php
require 'db.php';

$id = intval($_POST['id']);

$upd = $conn->prepare("UPDATE is_emri_real_addimlari SET status='gedir' WHERE id=?");
$upd->bind_param("i", $id);
$upd->execute();

header("Location: usta_panel.php");
exit;
