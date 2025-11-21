<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="az">
<head>
<meta charset="UTF-8">
<title>İstifadəçilər</title>
<style>
    body { margin:0; font-family: Arial; background:#f2f2f2; }
    .header { background:#222; color:white; padding:14px; font-size:20px;
              display:flex; justify-content:space-between; align-items:center; }
    .back { color:white; text-decoration:none; margin-right:14px; }
    .add-btn { background:#28a745; padding:8px 12px; color:white; border-radius:5px; text-decoration:none; }
    .role-btn { background:#007bff; padding:8px 12px; color:white; border-radius:5px; text-decoration:none; margin-left:10px; }

    table { width:100%; background:white; border-collapse:collapse; margin-top:20px; }
    th, td { padding:10px; border-bottom:1px solid #ddd; text-align:center; }
    th { background:#333; color:white; }
    tr:hover { background:#f5f5f5; }

    .tag { padding:3px 7px; border-radius:4px; background:#6c757d; color:white; margin:2px; display:inline-block; }

    .btn { padding:5px 8px; border-radius:4px; color:white; text-decoration:none; margin-right:5px; font-size:13px; }
    .edit { background:#6c757d; }
    .delete { background:#dc3545; }
</style>
</head>
<body>

<div class="header">
    <div>
        <a class="back" href="admin.php">⬅ Geri</a>
        İstifadəçilər
    </div>

    <div>
        <a class="add-btn" href="istifadeci_elave_et.php">+ Yeni istifadəçi</a>
        <a class="role-btn" href="role_add.php">+ Yeni rol</a>
        <a class="role-btn" href="roleS.php">+ Rollar</a>
    </div>
</div>

<table>
<tr>
    <th>ID</th>
    <th>Login</th>
    <th>Rol(lar)</th>
    <th>Əməliyyat</th>
</tr>

<?php while($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= $u['login'] ?></td>

    <td>
        <?php
        // bu istifadəçinin rolları
        $urs = $conn->query("SELECT rol FROM user_roles WHERE user_id=" . $u['id']);
        if ($urs && $urs->num_rows > 0) {
            while ($r = $urs->fetch_assoc()) {
                echo "<span class='tag'>{$r['rol']}</span>";
            }
        } else {
            echo "<span style='color:#aaa;'>Rol yoxdur</span>";
        }
        ?>
    </td>

    <td>

        <!-- MULTI SELECT ROL DƏYİŞDİRMƏ -->
        <form action="rol_deyis.php" method="post" style="display:inline-block;">
            <input type="hidden" name="id" value="<?= $u['id'] ?>">

            <select name="rol[]" multiple size="8" style="width:280px; padding:6px; font-size:15px;">

                <?php
                // bütün rollar
                $all = $conn->query("SELECT rol FROM user_roles_list ORDER BY rol ASC");

                // bu ustanın mövcud rolları
                $myRoles = [];
                $my = $conn->query("SELECT rol FROM user_roles WHERE user_id=" . $u['id']);
                while ($mr = $my->fetch_assoc()) {
                    $myRoles[] = $mr['rol'];
                }

                while($r = $all->fetch_assoc()):
                    $sel = in_array($r['rol'], $myRoles) ? "selected" : "";
                ?>
                    <option value="<?= $r['rol'] ?>" <?= $sel ?>><?= $r['rol'] ?></option>
                <?php endwhile; ?>

            </select>

            <button class="btn edit" type="submit">Dəyiş</button>
        </form>

        <!-- İSTİFADƏÇİ SİL -->
        <a class="btn delete" onclick="return confirm('Bu istifadəçi silinsin?')" href="delete_user.php?id=<?= $u['id'] ?>">
            Sil
        </a>

    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
