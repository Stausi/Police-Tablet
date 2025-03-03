<?php

$uploadOk = 1;

$player = "";
$target_dir = "";
$location = "";

if(isset($_GET['player'])) {
    $player = $_GET['player'];
    $target_dir = "../assets/playersIMG/";
    $location = "pages/player.php?player=" . $player;
} elseif(isset($_GET['user'])) {
    $player = $_GET['user'];
    $target_dir = "../assets/profilesIMG/";
    $location = "pages/profile.php?action=picture";
} else {
    echo "Noget gik galt.";
    $uploadOk = 0;
}

$temp = explode(".", $_FILES["fileToUpload"]["name"]);
$newfilename = $player . '.png';
$target_file = $target_dir . $newfilename;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}

if ($_FILES["fileToUpload"]["size"] > 5000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}


if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
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