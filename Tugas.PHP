<?php
// --- LOGIKA PHP ---
session_start();

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$message = "";
if (isset($_POST['upload'])) {
    $file_name = $_FILES['file_tugas']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . basename($file_name);
    
    // Validasi: Hanya PDF
    if ($file_ext != "pdf") {
        $message = "Gagal! Hanya file PDF yang diperbolehkan.";
    } elseif ($_FILES['file_tugas']['size'] > 524288000) {
        $message = "Gagal! Ukuran file maksimal 500MB.";
    } else {
        if (move_uploaded_file($_FILES['file_tugas']['tmp_name'], $target_file)) {
            $message = "PDF Berhasil diunggah!";
        }
    }
}

if (isset($_GET['delete'])) {
    $file_to_delete = $target_dir . $_GET['delete'];
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=dashboard");
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'welcome';
$view_file = isset($_GET['view']) ? $_GET['view'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sistem Tugas PDF</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #2980b9; --danger: #c0392b; --dark: #2c3e50; --bg: #ecf0f1; }
        
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; font-family: sans-serif; background: var(--bg); }
        
        /* Container */
        .wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .box { background: white; width: 100%; max-width: 700px; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; }

        /* Welcome Page */
        .welcome-border { border: 4px solid var(--primary); padding: 50px 20px; border-radius: 15px; }
        
        /* Dashboard */
        .nav-top { display: flex; justify-content: flex-end; padding-bottom: 15px; }
        .menu-text { text-decoration: none; color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: none; background: none; transition: 0.2s; }
        .menu-text:hover { opacity: 0.7; }
        .menu-text.delete { color: var(--danger); }

        /* Upload area */
        .upload-container { margin-top: 40px; border: 2px solid #ddd; padding: 20px; border-radius: 10px; }
        .btn-blue { background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; margin-top: 15px; width: 100%; font-size: 16px; }

        /* List File */
        .file-row { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; text-align: left; }

        /* Fullscreen Viewer */
        .fullscreen-viewer { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 1000; display: flex; flex-direction: column; }
        .viewer-header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .pdf-frame { flex-grow: 1; border: none; width: 100%; height: 100%; }

        /* Loader */
        #loader { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 2000; flex-direction: column; justify-content: center; align-items: center; }
        .spin { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary); border-radius: 50%; animation: rotate 1s linear infinite; }
        @keyframes rotate { 100% { transform: rotate(360deg); } }

        @media (max-width: 600px) {
            .wrapper { padding: 10px; }
            .box { padding: 15px; }
            .file-row { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>
<body>

<div id="loader">
    <div class="spin"></div>
    <p>Loading...</p>
</div>

<div class="wrapper">
    <?php if ($page == 'welcome'): ?>
        <div class="box">
            <div class="welcome-border">
                <h1 style="margin:0; color:var(--dark);">FILE TUGAS</h1>
                <p style="color: #666;">Khusus Dokumen PDF</p>
                <button class="btn-blue" onclick="startApp()">Klik Sini</button>
            </div>
        </div>

    <?php elseif ($page == 'dashboard' && !$view_file): ?>
        <div class="box">
            <div class="nav-top">
                <a href="?action=logout" class="menu-text" style="color:var(--danger)">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <h2 style="text-align: left; color: var(--dark);"><i class="far fa-file-pdf"></i> Tugas Saya</h2>
            
            <?php if($message) echo "<p style='color:green; font-weight:bold;'>$message</p>"; ?>

            <div style="margin: 20px 0;">
                <?php
                $files = array_diff(scandir($target_dir), array('.', '..'));
                if (count($files) > 0) {
                    foreach ($files as $f) {
                        echo "<div class='file-row'>
                                <span style='word-break: break-all;'><i class='fas fa-file-pdf' style='color:red;'></i> $f</span>
                                <div style='white-space: nowrap;'>
                                    <a href='?page=dashboard&view=$f' class='menu-text'><i class='fas fa-eye'></i> Lihat</a>
                                    <a href='?delete=$f' class='menu-text delete' onclick='return confirm(\"Hapus?\")'><i class='fas fa-trash-alt'></i> Hapus</a>
                                </div>
                              </div>";
                    }
                } else { echo "<p>Belum ada file.</p>"; }
                ?>
            </div>

            <div class="upload-container">
                <form action="?page=dashboard" method="POST" enctype="multipart/form-data">
                    <p style="text-align: left; margin-bottom: 5px; font-weight: bold;">Pilih File PDF:</p>
                    <input type="file" name="file_tugas" accept=".pdf" required style="width: 100%;">
                    <button type="submit" name="upload" class="btn-blue">
                        <i class="fas fa-cloud-upload-alt"></i> Upload PDF
                    </button>
                </form>
            </div>
        </div>

    <?php elseif ($view_file): ?>
        <div class="fullscreen-viewer">
            <div class="viewer-header">
                <a href="?page=dashboard" class="menu-text" style="color: white;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <span style="font-size: 14px;"><?php echo $view_file; ?></span>
                <a href="uploads/<?php echo $view_file; ?>" download class="menu-text" style="color: white;">
                    <i class="fas fa-download"></i> Unduh
                </a>
            </div>
            <iframe src="uploads/<?php echo $view_file; ?>#toolbar=0" class="pdf-frame"></iframe>
        </div>
    <?php endif; ?>
</div>

<script>
    function startApp() {
        document.getElementById('loader').style.display = 'flex';
        setTimeout(() => {
            window.location.href = "?page=dashboard";
        }, 1000);
    }

    // Menghilangkan re-post saat refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>