<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* ---------------------------------------------------------
   1) SINGLE kateqoriyaların siyahısı (kombo yaratmaq üçün)
---------------------------------------------------------- */
$cats = $conn->query("SELECT id, ad FROM kateqoriyalar WHERE tip='single' ORDER BY ad ASC");

$message = "";

/* ---------------------------------------------------------
   2) POST gəlirsə işləyək
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ad = trim($_POST['ad']);
    $tip = $_POST['tip'];

    if ($ad === "") {
        $message = "⚠ Kateqoriya adı boş ola bilməz!";
    } else {

        /* ---------------------------------------------------
           SINGLE kateqoriya yaradılır
        ---------------------------------------------------- */
        if ($tip === "single") {

            $stmt = $conn->prepare("INSERT INTO kateqoriyalar (ad, tip) VALUES (?, 'single')");
            $stmt->bind_param("s", $ad);
            $stmt->execute();

            $message = "✔ Tək kateqoriya əlavə olundu!";
        }

        /* ---------------------------------------------------
           COMBO kateqoriya yaradılır + ŞABLON NÜSXƏSİ yaradır
        ---------------------------------------------------- */
        elseif ($tip === "combo") {

            $combo = $_POST['combo_ids'] ?? [];

            if (count($combo) < 2) {
                $message = "⚠ Kombo üçün ən az 2 kateqoriya seçilməlidir.";
            } else {

                // Combo string
                $combo_str = implode(",", $combo);

                // 1) Combo kateqoriya yaradırıq
                $stmt = $conn->prepare("
                    INSERT INTO kateqoriyalar (ad, tip, combo_ids) 
                    VALUES (?, 'combo', ?)
                ");
                $stmt->bind_param("ss", $ad, $combo_str);
                $stmt->execute();

                // Yeni kombonun ID-si
                $new_combo_id = $conn->insert_id;

                /* ---------------------------------------------
                   2) ŞABLON QRUPU YARADIRIQ (müstəqil nüsxə)
                ---------------------------------------------- */
                $conn->query("
                    INSERT INTO sablon_qruplar (ad, kateqoriya_id)
                    VALUES ('$ad', $new_combo_id)
                ");
                $new_group_id = $conn->insert_id;

                /* ---------------------------------------------
                   3) Hər seçilən kateqoriyanın MASTER şablonunu kopyalayırıq
                ---------------------------------------------- */
                foreach ($combo as $kid) {

                    $master = $conn->query("
                        SELECT * FROM kateqoriya_sablonlar 
                        WHERE kateqoriya_id = $kid 
                        ORDER BY sira ASC
                    ");

                    while ($m = $master->fetch_assoc()) {

                        $txt  = $conn->real_escape_string($m['adim_text']);
                        $sira = intval($m['sira']);

                        // Nüsxə yaradılır
                        $conn->query("
                            INSERT INTO sablon_isemri_nusxe 
                                (sablon_qrup_id, kateqoriya_id, adim_text, sira)
                            VALUES 
                                ($new_group_id, $kid, '$txt', $sira)
                        ");
                    }
                }

                $message = "✔ Kombo kateqoriya və şablon nüsxəsi uğurla yaradıldı!";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Yeni Şablon</title>
<style>
body { font-family:Arial;background:#eef1f5;padding:20px; }
.box { background:white;padding:20px;border-radius:10px;max-width:600px;margin:auto; }
input,select { width:100%;padding:10px;margin-top:10px;border-radius:6px;border:1px solid #ccc; }
button { padding:10px;width:100%;background:#28a745;color:white;border:none;border-radius:6px;margin-top:10px; }
</style>
</head>
<body>

<div class="box">
    <h2>Yeni Kateqoriya və Şablon</h2>
    <a href="kateqoriyalar.php">⬅ Geri</a>

    <?php if ($message): ?>
        <div style="padding:10px;background:#e6ffe6;margin-top:10px;border:1px solid #7ad67a;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Kateqoriya adı:</label>
        <input type="text" name="ad" required>

        <label>Tip seç:</label>
        <select name="tip" id="tip">
            <option value="single">Tək Kateqoriya</option>
            <option value="combo">Kombinasiya (2 və daha çox)</option>
        </select>

        <div id="combo_box" style="display:none;">
            <label>Birləşdiriləcək kateqoriyalar:</label>
            <select name="combo_ids[]" multiple size="5" style="height:150px;">
                <?php while ($c = $cats->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['ad'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit">➕ Yarat</button>
    </form>
</div>

<script>
document.getElementById('tip').addEventListener('change', function() {
    document.getElementById('combo_box').style.display = 
        (this.value === 'combo') ? 'block' : 'none';
});
</script>

</body>
</html>
