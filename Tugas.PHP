<?php
session_start();
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pdf';

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_FILES['tugas_file'])) {
    $target_tab = $_POST['active_tab'];
    $file_name = $_FILES['tugas_file']['name'];
    $target_file = $upload_dir . time() . '_' . $file_name;
    if(move_uploaded_file($_FILES['tugas_file']['tmp_name'], $target_file)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=upload&tab=" . $target_tab);
        exit;
    }
}

if (isset($_GET['delete'])) {
    $target_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pdf';
    if (file_exists($_GET['delete'])) { unlink($_GET['delete']); }
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=upload&tab=" . $target_tab);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem File Tugas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2563eb; --danger: #dc2626; --bg: #f1f5f9; --gray: #6b7280; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); margin: 0; padding: 0; min-height: 100vh; display: flex; justify-content: center; align-items: center; }

        #progress-bar-container { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 5px; background: #e2e8f0; z-index: 10000; }
        #progress-bar { width: 0%; height: 100%; background: var(--gray); transition: width 0.1s linear; }

        .container { width: 95%; max-width: 900px; background: white; padding: 1.5rem; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; margin: 20px auto; }
        
        .tab-header { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .tab-btn { padding: 12px 20px; border: none; background: #e2e8f0; cursor: pointer; border-radius: 8px; font-weight: 600; color: #475569; transition: 0.3s; font-size: 14px; flex: 1; }
        .tab-btn.active { background: var(--primary); color: white; }
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .file-list { text-align: left; margin-top: 15px; border: 1px solid #f1f5f9; border-radius: 10px; overflow: hidden; }
        .file-item { padding: 15px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .file-info { display: flex; flex-direction: column; max-width: 65%; }
        .file-name { font-weight: 600; font-size: 14px; color: #1e293b; word-break: break-all; }
        .file-meta { font-size: 11px; color: #94a3b8; }

        .preview-wrapper { 
            width: 100%; 
            background: #1a1a1a; 
            border-radius: 10px; 
            overflow: hidden; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            margin-top: 15px;
        }
        .preview-wrapper img, .preview-wrapper video { 
            max-width: 100%; 
            height: auto; 
            display: block;
            object-fit: contain; 
        }
        .preview-wrapper iframe { width: 100%; height: 600px; border: none; }

        .upload-box { margin-bottom: 20px; padding: 20px; border: 2px dashed #cbd5e1; border-radius: 12px; background: #fafafa; }
        .btn-upload { color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 10px; }
        
        .logout-btn { color: var(--danger); text-decoration: none; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 6px; }

        @media (max-width: 600px) {
            .tab-btn { font-size: 12px; padding: 10px 5px; }
            .preview-wrapper iframe { height: 400px; }
        }
    </style>
</head>
<body>

    <div id="progress-bar-container"><div id="progress-bar"></div></div>

    <?php if (!isset($_GET['page'])): ?>
        <div class="container">
            <div style="border:3px solid #333; padding:20px; font-weight:800; font-size:1.5rem; margin-bottom:20px;">FILE TUGAS</div>
            <a onclick="startLoading()" style="color:var(--primary); font-weight:bold; cursor:pointer; text-decoration:none;">klik sini</a>
        </div>

    <?php elseif ($_GET['page'] == 'upload'): ?>
        <div class="container">
            <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0">Manajemen File</h3>
                <a href="?action=logout" class="logout-btn"><i class="fas fa-right-from-bracket"></i> Logout</a>
            </div>

            <div class="tab-header">
                <button class="tab-btn <?php echo ($current_tab == 'pdf') ? 'active' : ''; ?>" onclick="openTab(event, 'tab-pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
                <button class="tab-btn <?php echo ($current_tab == 'foto') ? 'active' : ''; ?>" onclick="openTab(event, 'tab-foto')"><i class="fas fa-image"></i> FOTO</button>
                <button class="tab-btn <?php echo ($current_tab == 'video') ? 'active' : ''; ?>" onclick="openTab(event, 'tab-video')"><i class="fas fa-video"></i> VIDEO</button>
            </div>

            <?php
            $all_files = array_diff(scandir($upload_dir), array('.', '..'));
            arsort($all_files);

            function renderTabContent($files, $allowed_exts, $type_label, $tab_id, $btn_color, $upload_dir) {
                $filtered = array_filter($files, function($f) use ($allowed_exts) {
                    return in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowed_exts);
                });
                ?>
                <div class="upload-box">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="active_tab" value="<?php echo $tab_id; ?>">
                        <input type="file" name="tugas_file" accept="<?php echo ($type_label == 'PDF' ? '.pdf' : ($type_label == 'FOTO' ? 'image/*' : 'video/*')); ?>" required>
                        <button type="submit" class="btn-upload" style="background:<?php echo $btn_color; ?>">Upload <?php echo $type_label; ?></button>
                    </form>
                </div>

                <div class="file-list">
                    <?php if (empty($filtered)): ?>
                        <div style="padding:40px; color:#94a3b8; font-size:14px;">Belum ada file <?php echo $type_label; ?>.</div>
                    <?php else: ?>
                        <?php foreach ($filtered as $file): 
                            $path = $upload_dir . $file;
                            $size = round(filesize($path) / (1024 * 1024), 2);
                            $display_name = substr($file, strpos($file, '_') + 1);
                        ?>
                            <div class="file-item">
                                <div class="file-info">
                                    <span class="file-name"><?php echo $display_name; ?></span>
                                    <span class="file-meta"><?php echo $size; ?> MB</span>
                                </div>
                                <div style="display:flex; gap:15px">
                                    <a href="?page=view&file=<?php echo $file; ?>&tab=<?php echo $tab_id; ?>" style="color:var(--primary); text-decoration:none; font-weight:600; font-size:14px;"><i class="fas fa-eye"></i> Lihat</a>
                                    <a href="#" onclick="konfirmasiHapus('<?php echo $path; ?>', '<?php echo $tab_id; ?>')" style="color:var(--danger);"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php
            }
            ?>

            <div id="tab-pdf" class="tab-content <?php echo ($current_tab == 'pdf') ? 'active' : ''; ?>">
                <?php renderTabContent($all_files, ['pdf'], 'PDF', 'pdf', '#2563eb', $upload_dir); ?>
            </div>
            <div id="tab-foto" class="tab-content <?php echo ($current_tab == 'foto') ? 'active' : ''; ?>">
                <?php renderTabContent($all_files, ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'FOTO', 'foto', '#059669', $upload_dir); ?>
            </div>
            <div id="tab-video" class="tab-content <?php echo ($current_tab == 'video') ? 'active' : ''; ?>">
                <?php renderTabContent($all_files, ['mp4', 'webm', 'ogg', 'mov'], 'VIDEO', 'video', '#7c3aed', $upload_dir); ?>
            </div>
        </div>
    <?php elseif ($_GET['page'] == 'view'): ?>
        <div class="container" style="max-width: 900px; padding: 1rem; margin-top: 10px;"> 
            <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 1.2rem;">Pratinjau File</h3>
            
            <div class="preview-wrapper" style="width: 100%; height: 70vh; background: #fcfcfcff; border-radius: 10px; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                <?php
                $file = $_GET['file']; $path = $upload_dir . $file;
                $prev_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pdf';
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if ($ext == 'pdf') {
                    echo "<iframe src='$path' style='width:100%; height:100%; border:none; background:white;'></iframe>";
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    echo "<img src='$path' style='max-width:100%; max-height:100%; object-fit: contain;'>";
                } elseif (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
                    echo "<video controls playsinline style='max-width:100%; max-height:100%;'><source src='$path' type='video/$ext'></video>";
                }
                ?>
            </div>
            
            <div style="display:flex; justify-content: center; gap:40px; margin-top:15px;">
                <a href="?page=upload&tab=<?php echo $prev_tab; ?>" style="text-decoration:none; color:var(--gray); font-weight:600; font-size: 14px;"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="<?php echo $path; ?>" download style="text-decoration:none; color:var(--primary); font-weight:600; font-size: 14px;"><i class="fas fa-download"></i> Unduh</a>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) { tabcontent[i].classList.remove("active"); }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) { tablinks[i].classList.remove("active"); }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        function startLoading() {
            const barContainer = document.getElementById('progress-bar-container');
            const bar = document.getElementById('progress-bar');
            barContainer.style.display = 'block';
            let width = 0;
            const interval = setInterval(() => {
                if (width >= 100) { clearInterval(interval); window.location.href = "?page=upload"; }
                else { width += 1; bar.style.width = width + '%'; }
            }, 20); 
        }

        function konfirmasiHapus(path, tab) {
            if (confirm("Hapus file ini?")) { window.location.href = "?delete=" + path + "&tab=" + tab; }
        }
    </script>
</body>
</html>