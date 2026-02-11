<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['barcode_file'])) {
    $targetDir = "uploads/";
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES["barcode_file"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'zip', '7z');

    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($_FILES["barcode_file"]["tmp_name"], $targetFilePath)) {
            header("Location: files.php?upload=success");
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "Sorry, only JPG, PNG, PDF, ZIP, & 7Z files are allowed.";
    }
}
?>