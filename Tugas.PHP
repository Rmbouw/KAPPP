<?php
// --- LOGIKA BACKEND PHP ---

// Mengatur batas upload agar bisa sampai 600MB
ini_set('upload_max_filesize', '600M');
ini_set('post_max_size', '600M');
ini_set('memory_limit', '1024M');

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Logika Hapus File
if (isset($_GET['hapus'])) {
    $fileHapus = basename($_GET['hapus']);
    if (file_exists($uploadDir . $fileHapus)) {
        unlink($uploadDir . $fileHapus);
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=dashboard");
    exit;
}

// Logika Upload File
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['tugas'])) {
    $fileName = basename($_FILES['tugas']['name']);
    $targetPath = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['tugas']['tmp_name'], $targetPath)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=dashboard");
        exit;
    }
}

// Menentukan halaman yang sedang dibuka
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Tugas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS STYLE --- */
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        
        body { 
            background: #f0f2f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            color: #333;
        }

        /* Loading Overlay */
        #loader {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: white;
            z-index: 9999;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Card Container */
        .card {
            background: white;
            width: 90%;
            max-width: 600px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
        }

        /* Halaman Pertama */
        .landing-box { padding: 40px 20px; }
        .title-box { 
            font-size: 24px; 
            font-weight: bold; 
            letter-spacing: 2px; 
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .btn-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-link:hover { color: #2980b9; }

        /* Halaman Kedua (Dashboard) */
        .header-dash {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .logout { color: #e74c3c; text-decoration: none; font-size: 14px; font-weight: bold; }

        /* List File Tanpa Kotak */
        .file-list { width: 100%; text-align: left; margin-bottom: 30px; }
        .file-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f9f9f9;
        }
        .file-name { font-size: 14px; color: #555; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 60%; }
        .actions a {
            text-decoration: none;
            font-size: 13px;
            margin-left: 10px;
            color: #7f8c8d;
        }
        .actions a:hover { color: #2c3e50; }

        /* Bagian Upload (Dengan Kotak) */
        .upload-area {
            margin-top: 20px;
            padding: 20px;
            border: 2px dashed #dcdde1;
            border-radius: 15px;
            background: #fbfbfb;
        }
        input[type="file"] { margin-bottom: 15px; width: 100%; }
        .btn-upload {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }

        /* View Mode */
        .preview-box { margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .preview-content { max-width: 100%; height: auto; border-radius: 10px; }

        /* Responsive HP */
        @media (max-width: 480px) {
            .card { padding: 20px; width: 95%; }
            .file-name { max-width: 50%; }
        }
    </style>
</head>
<body>

    <div id="loader">
        <i class="fas fa-circle-notch fa-spin fa-3x" style="color: #3498db;"></i>
        <p style="margin-top: 15px; color: #7f8c8d;">Memproses...</p>
    </div>

    <?php if ($page == 'home'): ?>
    <div class="card landing-box">
        <div class="title-box">FILE TUGAS</div>
        <a href="javascript:void(0)" class="btn-link" onclick="goToDashboard()">
            <i class="fas fa-hand-pointer"></i> Klik Sini
        </a>
    </div>

    <?php elseif ($page == 'dashboard'): ?>
    <div class="card">
        <div class="header-dash">
            <h3 style="font-size: 18px;">Daftar File</h3>
            <a href="?page=home" class="logout">
                <i class="fas fa-power-off"></i> LOGOUT
            </a>
        </div>

        <div class="file-list">
            <?php
            $files = array_diff(scandir($uploadDir), array('.', '..'));
            if (empty($files)) echo "<p style='color:#ccc; text-align:center;'>Belum ada file.</p>";
            foreach ($files as $f) :
            ?>
                <div class="file-item">
                    <span class="file-name"><i class="far fa-file-alt"></i> <?php echo $f; ?></span>
                    <div class="actions">
                        <a href="?page=view&file=<?php echo urlencode($f); ?>"><i class="fas fa-eye"></i> Lihat</a>
                        <a href="?hapus=<?php echo urlencode($f); ?>" onclick="return confirm('Hapus file ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="upload-area">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="file" name="tugas" required>
                <button type="submit" class="btn-upload">
                    <i class="fas fa-cloud-upload-alt"></i> UPLOAD SEKARANG
                </button>
            </form>
            <p style="font-size: 10px; color: #999; margin-top: 10px;">Max size: 600MB (PDF, Foto, Video)</p>
        </div>
    </div>

    <?php elseif ($page == 'view'): 
        $fileView = $_GET['file'];
        $pathView = $uploadDir . $fileView;
    ?>
    <div class="card">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="?page=dashboard" class="btn-link" style="margin-right: 20px;">
                <i class="fas fa-chevron-left"></i> Kembali
            </a>
            <a href="<?php echo $pathView; ?>" download class="btn-link" style="color: #2ecc71;">
                <i class="fas fa-download"></i> Unduh
            </a>
        </div>

        <div class="preview-box">
            <p style="margin-bottom: 15px; font-weight: bold;"><?php echo $fileView; ?></p>
            <?php
            $ext = strtolower(pathinfo($fileView, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="<?php echo $pathView; ?>" class="preview-content">
            <?php elseif ($ext == 'pdf'): ?>
                <iframe src="<?php echo $pathView; ?>" width="100%" height="400px" style="border:none;"></iframe>
            <?php elseif (in_array($ext, ['mp4', 'webm'])): ?>
                <video controls class="preview-content">
                    <source src="<?php echo $pathView; ?>" type="video/mp4">
                </video>
            <?php else: ?>
                <p>Preview tidak tersedia untuk tipe file ini.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // --- JAVASCRIPT LOGIC ---
        function goToDashboard() {
            // Tampilkan loading
            document.getElementById('loader').style.display = 'flex';
            
            // Delay 1 detik
            setTimeout(function() {
                window.location.href = "?page=dashboard";
            }, 1000);
        }
    </script>
</body>
</html>