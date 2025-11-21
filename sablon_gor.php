<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* -----------------------------------------------------
   Sablon ID
----------------------------------------------------- */
$sablon_id = intval($_GET['sablon_id'] ?? ($_GET['id'] ?? 0));
if ($sablon_id <= 0) die("Sablon tapılmadı!");

/* -----------------------------------------------------
   Kateqoriya məlumatı
----------------------------------------------------- */
$catQ = $conn->query("SELECT * FROM kateqoriyalar WHERE id = $sablon_id");
if ($catQ->num_rows == 0) die("Kateqoriya tapılmadı!");
$cat = $catQ->fetch_assoc();

/* -----------------------------------------------------
   İş əmrləri
----------------------------------------------------- */
$is_emirleri = $conn->query("SELECT id, rol FROM user_roles_list ORDER BY rol ASC");

/* -----------------------------------------------------
   Ustalar
----------------------------------------------------- */
$ustalar = $conn->query("SELECT id, login FROM users ORDER BY login ASC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>Şablon Gör – <?= htmlspecialchars($cat['ad']) ?></title>

<style>
body { font-family: Arial; background:#eef1f5; padding:20px; }
.container { display:flex; gap:20px; }

/* Sol panel */
.left-box {
    width:30%;
    background:white; padding:20px; border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

/* Sağ panel */
.right-box {
    width:70%;
    background:white; padding:20px; border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

/* Inputlar */
input, select {
    width:100%; padding:8px; margin-top:5px;
    border:1px solid #ccc; border-radius:6px;
}

/* Button */
button {
    padding:10px 18px;
    background:#007bff; color:white;
    border:none; border-radius:6px;
    cursor:pointer; margin-top:10px;
}

.step-box {
    max-width: 300px;       /* ← qutu 300px-dən geniş olmayacaq */
    width: 100%;
}




/* Addım kartları grid */
.step-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;                        /* daha az boşluq */
}


.step-grid {
    grid-template-columns: repeat(1, 1fr);
}


/* Addım kartı (5 dəfə kiçik versiya) */
.step-box {
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 6px 8px;               /* çox kiçik padding */
    min-height: 35px;               /* əvvəl 80px idi → 35px */
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
}

/* Yazılar da kiçilsin */
.step-box b {
    font-size: 12px;
    font-weight: 600;
}

.step-box small {
    font-size: 11px;
    color: #444;
}

/* Kiçik sil düyməsi */
.delete-small {
    position: absolute;
    right: 6px;
    top: 6px;
    background: #dc3545;
    padding: 3px 5px;
    font-size: 10px;
    border-radius: 4px;
    text-decoration: none;
}


.combo-title {
    margin-top:20px;
    font-size:18px;
    font-weight:bold;
}

.add-btn {
    display:inline-block;
    background:#007bff;
    padding:8px 15px;
    color:white;
    border-radius:6px;
    margin:10px 0;
    text-decoration:none;
}

</style>

</head>
<body>

<h2>🔧 Şablon: <?= htmlspecialchars($cat['ad']) ?></h2>
<a href="kateqoriya_sablonlari.php">⬅ Geri</a>

<div class="container">

    <!-- SOL BLOK -->
    <div class="left-box">

        <h3>Yeni Addım Əlavə Et</h3>

        <form method="POST" action="sablon_addim_yarat.php?sablon_id=<?= $sablon_id ?>">

            <label>İş Əmri:</label>
            <select name="is_emri_id" required>
                <option value="">-- Seçin --</option>
                <?php while ($r = $is_emirleri->fetch_assoc()): ?>
                    <option value="<?= $r['id'] ?>"><?= $r['rol'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Ustalar:</label>
            <select name="usta[]" multiple size="6" required>
                <?php while ($u = $ustalar->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>"><?= $u['login'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Sıra:</label>
            <input type="number" name="sira" min="1" required>

            <button type="submit">➕ Əlavə Et</button>
        </form>

    </div>


    <!-- =============================
         SAĞ BLOK – ŞABLON ADDIMLARI
    ============================= -->
    <div class="right-box">

<?php
/* ==========================================================
   KOMBO KATEQORİYA ÜÇÜN — HƏR ALT KATEQORİYA AYRI GÖSTƏRİLİR
========================================================== */
if ($cat['tip'] === 'combo' && !empty($cat['combo_ids'])) {

    $combo_ids = explode(",", $cat['combo_ids']);

    foreach ($combo_ids as $cid) {

        $cid = intval($cid);
        if ($cid <= 0) continue;

        // Alt kateqoriya adı
        $alt = $conn->query("SELECT ad FROM kateqoriyalar WHERE id=$cid")->fetch_assoc()['ad'];

        echo "<div class='combo-title'>🔹 $alt üçün addımlar</div>";
        echo "<a class='add-btn' href='sablon_addim_yeni.php?sablon_id=$cid'>➕ Addım əlavə et</a>";
        echo "<div class='step-grid'>";

        $steps = $conn->query("
            SELECT id, sira, is_emri_id, usta
            FROM is_emri_sablon_addimlari
            WHERE sablon_id = $cid
            ORDER BY sira ASC
        ");

        while ($a = $steps->fetch_assoc()) {

            /* İş əmri */
            $rol = $conn->query(
                "SELECT rol FROM user_roles_list WHERE id=".$a['is_emri_id']
            )->fetch_assoc()['rol'];

            /* Ustalar */
            $usta = "—";
            $arr = [];
            if (!empty($a['usta'])) {
                foreach (explode(",", $a['usta']) as $uid) {
                    $name = $conn->query("SELECT login FROM users WHERE id=$uid")->fetch_assoc()['login'];
                    if ($name) $arr[] = $name;
                }
                if (!empty($arr)) $usta = implode(", ", $arr);
            }

            echo "
            <div class='step-box'>
                <b>$rol</b>
                <small>$usta</small>
                <a class='delete-small'
                href='sablon_addim_sil.php?id={$a['id']}&sablon_id=$cid'>X</a>
            </div>";
        }

        echo "</div><hr>";
    }

} else {

    /* ==========================================
       TƏK KATEQORİYA ÜÇÜN ADDIMLAR – GRID FORMATI
    ========================================== */

    echo "<h3>Mövcud Addımlar</h3>";
    echo "<div class='step-grid'>";

    $steps = $conn->query("
        SELECT id, sira, is_emri_id, usta
        FROM is_emri_sablon_addimlari
        WHERE sablon_id = $sablon_id
        ORDER BY sira ASC
    ");

    while ($a = $steps->fetch_assoc()) {

        $rol = $conn->query(
            "SELECT rol FROM user_roles_list WHERE id=".$a['is_emri_id']
        )->fetch_assoc()['rol'];

        $usta = "—";
        $arr = [];
        if (!empty($a['usta'])) {
            foreach (explode(",", $a['usta']) as $uid) {
                $u = $conn->query("SELECT login FROM users WHERE id=$uid")->fetch_assoc()['login'];
                if ($u) $arr[] = $u;
            }
            if (!empty($arr)) $usta = implode(", ", $arr);
        }

        echo "
        <div class='step-box'>
            <b>$rol</b>
            <small>$usta</small>
            <a class='delete-small'
            href='sablon_addim_sil.php?id={$a['id']}&sablon_id=$sablon_id'>X</a>
        </div>";
    }

    echo "</div>";
}
?>

    </div>
</div>

</body>
</html> 