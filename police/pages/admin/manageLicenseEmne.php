<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


$sql = "SELECT * FROM licenses_subjects ORDER BY order_number ASC";
$result = $link->query($sql);

$emne = "";

if(isset($_GET['delete'])) {
    $sql = "DELETE FROM licenses_subjects WHERE id = ?";
         
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

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["emne"])){
        if(trim($_POST["emne"])) {
            $emne = trim($_POST["emne"]);
        }

        $sql = "INSERT INTO licenses_subjects (license_emne) VALUES (?)";
            
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_licenseemne);
            $param_licenseemne = $emne;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: syncAllLicenses.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($link);
    } elseif(isset($_POST["order_number"])) {
        $sql = "UPDATE licenses_subjects SET order_number = ?, license_emne = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "isi", $param_order_number, $param_licenseemne, $param_licenseemneid);
            
            $param_order_number = $_POST["order_number"];
            $param_licenseemne = $_POST["licenseemne"];
            $param_licenseemneid = $_POST["licenseemneid"];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: syncAllLicenses.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

echo '<main class="licenseEmne">';
    echo '<div class="create-afdeling">';
        echo '<h2>Opret et nyt License Emne</h2>';
        echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
            echo '<div class="form-group">';
                echo '<label>License Emnets navn</label>';
                echo '<input type="text" name="emne" class="form-control" value="' . $emne . '">';
            echo '</div>';
            echo '<div class="form-group" id="submit">';
                echo '<input type="submit" class="btn btn-primary" value="Opret">';
            echo'</div>';
        echo '</form>';
    echo '</div>';
    echo '<div class="mid-line" style="margin-bottom:20px;"></div>';
    echo '<div class="manage-afdelinger">';
        while($row = $result->fetch_assoc()) {
            echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
                echo '<div class="manage-afdeling">';
                    echo '<input type="text" class="id" name="licenseemneid" value="' . $row['id'] . '" readonly>';
                    echo '<input type="text" name="licenseemne" value="' . $row['license_emne'] . '">';
                    echo '<div class="order-number">';
                        echo '<input type="tel" name="order_number" value="' . $row['order_number'] . '">';
                        echo '<input type="submit" class="btn btn-primary" value="Opdatere License Emne">';
                    echo '</div>';
                    echo '<a class="btn btn-danger" href="manageLicenseEmne.php?delete=' . $row['id'] . '"> Slet License Emne</a>';
                echo '</div>';
            echo '</form>';
        }
    echo '</div>';
echo '</main>';

include '../../footer.php';
?>