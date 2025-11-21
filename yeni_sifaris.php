<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    function uploadImage($name){
        if(isset($_FILES[$name]) && $_FILES[$name]["name"] !== ""){
            if(!is_dir("uploads")){
                mkdir("uploads");
            }
            $file = time() . "_" . $_FILES[$name]["name"];
            move_uploaded_file($_FILES[$name]["tmp_name"], "uploads/" . $file);
            return $file;
        }
        return "";
    }

    $tarix = $_POST["tarix"];
    $notlar = $_POST["notlar"];
    $telefon = $_POST["telefon"];
    $qiymet = $_POST["qiymet"];
    $satis = $_POST["satis"];
    $unvan = $_POST["unvan"];

    $sh1 = uploadImage("shekil1");
    $sh2 = uploadImage("shekil2");
    $sh3 = uploadImage("shekil3");
    $sh4 = uploadImage("shekil_elave");

    $sql = "INSERT INTO sifarisler (tarix, notlar, shekil1, shekil2, shekil3, shekil_elave, telefon, qiymet, satis, unvan)
            VALUES ('$tarix','$notlar','$sh1','$sh2','$sh3','$sh4','$telefon','$qiymet','$satis','$unvan')";

    if($conn->query($sql)){
        header("Location: admin.php");
        exit();
    } else {
        echo "Xəta: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Yeni Sifariş</title>
<style>
body{font-family:Arial; background:#f5f5f5; padding:20px;}
form{
    background:white;
    padding:20px;
    width:400px;
    border-radius:8px;
    margin:auto;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
input,textarea{
    width:100%;
    margin:6px 0;
    padding:8px;
    border:1px solid #ccc;
    border-radius:4px;
}
button{
    background:#4285F4;
    color:white;
    padding:12px;
    border:none;
    width:100%;
    margin-top:10px;
    border-radius:6px;
}
</style>
</head>
<body>

<h2 style="text-align:center">Yeni Sifariş Əlavə Et</h2>

<form method="POST" enctype="multipart/form-data">

Tarix:
<input type="date" name="tarix" required>

Notlar:
<textarea name="notlar" required></textarea>

Şəkil1: <input type="file" name="shekil1">
Şəkil2: <input type="file" name="shekil2">
Şəkil3: <input type="file" name="shekil3">
Əlavə Şəkil: <input type="file" name="shekil_elave">

Telefon:
<input type="text" name="telefon" required>

Qiymət:
<input type="number" name="qiymet" required>

Satış:
<input type="number" name="satis" required>

Ünvan:
<textarea name="unvan" required></textarea>

<button type="submit">Yadda saxla</button>

</form>

</body>
</html>
