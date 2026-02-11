<?php
if (isset($_GET['file'])) {
    $dir = "uploads/";
    $fileName = basename($_GET['file']);
    $filePath = $dir . $fileName;

    if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
            header("Location: files.php?delete=success");
            exit;
        } else {
            echo "Error: Could not delete the file.";
        }
    } else {
        echo "Error: File not found.";
    }
} else {
    header("Location: files.php");
    exit;
}
?>