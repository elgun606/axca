$id = intval($_GET['id']);
$next_usta = intval($_POST['usta']);

// Cari addımı bağla
$conn->query("UPDATE is_emri_real_addimlari SET status='hazir' WHERE id=$id");

// Sifarişi tap
$q = $conn->query("SELECT sifaris_id, sira FROM is_emri_real_addimlari WHERE id=$id")->fetch_assoc();
$sifaris_id = $q['sifaris_id'];
$cari_sira  = $q['sira'];

// Növbəti addımı yaradırıq
$add = $conn->prepare("
    INSERT INTO is_emri_real_addimlari (sifaris_id, addim_adi, usta, sira, status)
    VALUES (?, 'Kəsim', ?, ?, 'gozleyir')
");
$novbeti_sira = $cari_sira + 1;
$add->bind_param("iii", $sifaris_id, $next_usta, $novbeti_sira);
$add->execute();

header("Location: usta_panel.php");
exit;
