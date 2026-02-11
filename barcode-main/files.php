<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Download & Upload Files</title>
        <link rel="stylesheet" href="index.css?v=1.1" />

</head>
<body>
    <nav class="nav">
        <a href="index.php">Barcode Generator</a>
    </nav>

    <div class="card file-card">
       
        <h1>ISBN Barcodes Zip File</h1>
        <div class="isbn-container">
            <a href="isbn barcodes.7z" class="master-download">Download Master .7z</a>
        </div>
    </div>

    <div class="card upload-card">
        <h3>Upload New Assets</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                <input type="file" name="barcode_file" id="fileInput" hidden onchange="this.form.submit()" />
                <div class="upload-label">
                    <strong>Click to upload</strong> or select a file
                </div>
            </div>
        </form>
        
        <div id="fileList" class="file-list">
            <?php
            $dir = "uploads/";
            if (is_dir($dir)) {
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file) {
                    $filePath = $dir . $file;
                    $fileSize = round(filesize($filePath) / 1024, 1);
                 echo '
            <div class="uploaded-item">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">📄</span>
                    <div style="text-align: left;">
                        <div style="font-weight: 600; font-size: 0.9rem;">' . htmlspecialchars($file) . '</div>
                        <div style="font-size: 0.75rem; color: #666;">' . $fileSize . ' KB</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="' . htmlspecialchars($filePath) . '" download class="mini-download">Download</a>
                    <a href="delete.php?file=' . urlencode($file) . '" 
                       class="delete-btn" 
                       onclick="return confirm(\'Are you sure you want to delete this file?\')">Delete</a>
                </div>
            </div>';
        }
    }
    ?>
</div>
    </div>
</body>
</html>