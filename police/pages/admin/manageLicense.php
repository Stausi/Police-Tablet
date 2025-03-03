<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


error_reporting(E_ALL); 
ini_set('display_errors', 1);

$licenseid = 0;
if(isset($_GET['licenseid'])) {
    $licenseid = $_GET['licenseid'];
}

$sql = "SELECT * FROM licenses WHERE id='" . $licenseid . "'";
$result = $link->query($sql);

$subjectSql = "SELECT * FROM licenses_subjects ORDER BY order_number ASC";
$subjectResult = $link->query($subjectSql);

$id = $license_name = $emne = "";

while($row = mysqli_fetch_array($result)) {
    $license_name = $row['license_name'];
    $emne = $row['subject'];
}

if(isset($_GET['delete'])) {
    $sql = "DELETE FROM licenses WHERE id = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: syncAllLicenses.php");
        } else{
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(trim($_POST["id"])) {
        $id = trim($_POST["id"]);
    }

    if(trim($_POST["license_name"])) {
        $license_name = trim($_POST["license_name"]);
    }

    if(trim($_POST["emner"])) {
        $emne = trim($_POST["emner"]);
    }

    $updateSql = "UPDATE licenses SET subject = ?, license_name = ? WHERE id = ?";

    if($stmt = mysqli_prepare($link, $updateSql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $param_emne, $param_license_name, $param_id);
    
        $param_license_name = $license_name;
        $param_emne = $emne;
        $param_id = $id;
        
        if(mysqli_stmt_execute($stmt)) {
            header("location: syncAllLicenses.php");
        } else {
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

echo '<main>';
    echo '<div class="create-afdeling">';
        echo '<h2>Redigere ' . $license_name . '</h2>';
        echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
            echo '<div class="form-group">';
                echo '<label>ID</label>';
                echo '<input type="text" name="id" class="form-control" value="' . $licenseid . '" readonly>';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Navn</label>';
                echo '<input type="text" name="license_name" class="form-control" value="' . $license_name . '">';
            echo '</div>';
            echo '<div class="form-group" id="afdeling">';
                echo '<label>Bødetasktemne</label>';
                echo '<select name="emner" class="form-control">';
                    while($row = $subjectResult->fetch_assoc()) {
                        if($row["license_emne"] == $emne) {
                            echo '<option value="' . $row["license_emne"] .'" selected>' . $row["license_emne"] . '</option>';
                        } else {
                            echo '<option value="' . $row["license_emne"] .'">' . $row["license_emne"] . '</option>';
                        }
                    }
                echo '</select>';
            echo '</div>';
            echo '<input type="submit" style="margin-right: 10px; margin-bottom: 20px;" class="btn btn-primary" value="Opdatere">';
            echo '<a class="btn btn-danger" style="margin-left: 10px; margin-bottom: 20px;" href="manageLicense.php?licenseid=' . $licenseid . '&delete=' . $licenseid . '"> Slet License</a>';
        echo '</form>';
    echo '</div>';
echo '</main>';

include '../../footer.php';
?>
