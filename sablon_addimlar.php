<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// URL-dən kateqoriya ID gəlməlidir: sablon_addimlar.php?kat_id=5 kimi
$kat_id = intval($_GET['kat_id'] ?? 0);
if ($kat_id <= 0) {
    die("Kateqoriya seçilməyib (kat_id yoxdur).");
}

// Kateqoriyanı tapırıq
$stmt = $conn->prepare("SELECT * FROM kateqoriyalar WHERE id = ?");
$stmt->bind_param("i", $kat_id);
$stmt->execute();
$res = $stmt->get_result();
$kateqoriya = $res->fetch_assoc();

if (!$kateqoriya) {
    die("Kateqoriya tapılmadı!");
}

// FORM GƏLİBSƏ – ƏMƏLİYYATLAR (yeni əlavə / update / silmə)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // YENİ ADDIM ƏLAVƏ ET
    if (isset($_POST['yeni_addim'])) {
        $is_emri_id = intval($_POST['is_emri_id'] ?? 0);
        $usta_id    = intval($_POST['usta_id'] ?? 0);
        $sira       = intval($_POST['sira'] ?? 0);

        if ($is_emri_id > 0 && $usta_id > 0 && $sira > 0) {
            $ins = $conn->prepare("
                INSERT INTO sablon_addimlar (kateqoriya_id, is_emri_id, usta_id, sira)
                VALUES (?, ?, ?, ?)
            ");
            $ins->bind_param("iiii", $kat_id, $is_emri_id, $usta_id, $sira);
            $ins->execute();
        }

        header("Location: sablon_addimlar.php?kat_id=" . $kat_id);
        exit;
    }

    // MÖVCUD ADDIMI YENİLƏ
    if (isset($_POST['update_addim'])) {
        $id         = intval($_POST['id'] ?? 0);
        $is_emri_id = intval($_POST['is_emri_id'] ?? 0);
        $usta_id    = intval($_POST['usta_id'] ?? 0);
        $sira       = intval($_POST['sira'] ?? 0);

        if ($id > 0 && $is_emri_id > 0 && $usta_id > 0 && $sira > 0) {
            $upd = $conn->prepare("
                UPDATE sablon_addimlar
                   SET is_emri_id = ?, usta_id = ?, sira = ?
                 WHERE id = ? AND kateqoriya_id = ?
            ");
            $upd->bind_param("iiiii", $is_emri_id, $usta_id, $sira, $id, $kat_id);
            $upd->execute();
        }

        header("Location: sablon_addimlar.php?kat_id=" . $kat_id);
        exit;
    }

    // ADDIMI SİL
    if (isset($_POST['sil_id'])) {
        $sil_id = intval($_POST['sil_id'] ?? 0);

        if ($sil_id > 0) {
            $del = $conn->prepare("DELETE FROM sablon_addimlar WHERE id = ? AND kateqoriya_id = ?");
            $del->bind_param("ii", $sil_id, $kat_id);
            $del->execute();
        }

        header("Location: sablon_addimlar.php?kat_id=" . $kat_id);
        exit;
    }
}

// DROPDOWN-lar üçün siyahılar
$is_emrleri = $conn->query("SELECT id, ad FROM is_emri ORDER BY ad ASC");
$ustalar    = $conn->query("SELECT id, login FROM users ORDER BY login ASC");

// Mövcud şablon addımları
$add_stmt = $conn->prepare("
    SELECT s.*, i.ad AS is_emri_adi, u.login AS usta_login
      FROM sablon_addimlar s
 LEFT JOIN is_emri i ON s.is_emri_id = i.id
 LEFT JOIN users  u ON s.usta_id    = u.id
     WHERE s.kateqoriya_id = ?
  ORDER BY s.sira ASC
");
$add_stmt->bind_param("i", $kat_id);
$add_stmt->execute();
$addimlar = $add_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Şablon addımları - <?= htmlspecialchars($kateqoriya['ad']) ?></title>
</head>
<body>

<h2>Kateqoriya üçün şablon addımları: 
    <strong><?= htmlspecialchars($kateqoriya['ad']) ?></strong>
</h2>

<a href="kateqoriyalar.php">⬅ Kateqoriyalara qayıt</a>
<br><br>

<!-- MÖVCUD ADDIMLAR CƏDVƏLİ -->
<?php if ($addimlar->num_rows > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Sıra</th>
            <th>İş əmri</th>
            <th>Usta</th>
            <th>Əməliyyat</th>
        </tr>

        <?php while ($row = $addimlar->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td>
                        <input type="number" name="sira" value="<?= (int)$row['sira'] ?>" style="width:60px;">
                    </td>

                    <td>
                        <select name="is_emri_id">
                            <?php
                            // is_emrleri siyahısını yenidən çəkək (cursor sonuna gedib)
                            $is_emrleri2 = $conn->query("SELECT id, ad FROM is_emri ORDER BY ad ASC");
                            while($ie = $is_emrleri2->fetch_assoc()):
                            ?>
                                <option value="<?= $ie['id'] ?>"
                                    <?= ($row['is_emri_id'] == $ie['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ie['ad']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>

                    <td>
                        <select name="usta_id">
                            <?php
                            $ustalar2 = $conn->query("SELECT id, login FROM users ORDER BY login ASC");
                            while($u = $ustalar2->fetch_assoc()):
                            ?>
                                <option value="<?= $u['id'] ?>"
                                    <?= ($row['usta_id'] == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['login']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>

                    <td>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                        <button type="submit" name="update_addim">Yenilə</button>
                </form>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="sil_id" value="<?= $row['id'] ?>">
                    <button type="submit" onclick="return confirm('Silinsin?')">Sil</button>
                </form>
                    </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Bu kateqoriya üçün hələ şablon addımı yoxdur.</p>
<?php endif; ?>

<br><hr><br>

<!-- YENİ ADDIM ƏLAVƏ FORMU -->
<h3>Yeni şablon addımı əlavə et</h3>

<form method="POST">

    <label>İş əmri:</label><br>
    <select name="is_emri_id" required>
        <option value="">-- İş əmri seçin --</option>
        <?php
        // yenidən çəkirik
        $is_emrleri3 = $conn->query("SELECT id, ad FROM is_emri ORDER BY ad ASC");
        while($ie = $is_emrleri3->fetch_assoc()):
        ?>
            <option value="<?= $ie['id'] ?>"><?= htmlspecialchars($ie['ad']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label>Varsayılan usta / istifadəçi:</label><br>
    <select name="usta_id" required>
        <option value="">-- Usta / istifadəçi seçin --</option>
        <?php
        $ustalar3 = $conn->query("SELECT id, login FROM users ORDER BY login ASC");
        while($u = $ustalar3->fetch_assoc()):
        ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['login']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label>Sıra:</label><br>
    <input type="number" name="sira" required style="width:80px;">
    <br><br>

    <button type="submit" name="yeni_addim">Addım əlavə et</button>

</form>

</body>
</html>
