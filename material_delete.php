<?php
session_start();
require 'db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("İcazə yoxdur");
}

$id = intval($_GET['id']);

$conn->query("DELETE FROM materiallar WHERE id=$id");

header("Location: materiallar.php?silindi=1");
exit;
