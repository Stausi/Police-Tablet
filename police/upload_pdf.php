<?php

$uploadOk = 1;

$target_dir = "../assets/pdf_files/";
$newfilename = basename($_FILES["fileToUpload"]["name"]);
$target_file = $target_dir . $newfilename;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$location = "";

if(isset($_GET['user'])) {
    $location = "pages/profile.php?action=document&name=" . $newfilename;
} else {
    echo "Noget gik galt.";
    $uploadOk = 0;
}

if ($_FILES["fileToUpload"]["size"] > 5000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

if($fileType != "pdf") {
    echo "Sorry, only PDF files are allowed.";
    $uploadOk = 0;
}

if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        header("location: " . $location);
    } else {
        echo "Sorry, there was an error uploading your file.";
        if (isset($_FILES["fileToUpload"]["error"])) {
            echo "File upload error: " . $_FILES["fileToUpload"]["error"];
        }
    }
}
?>

