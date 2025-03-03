<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


$sql = "SELECT * FROM punishment ORDER BY order_number ASC";
$result = $link->query($sql);

$emne = "";

if(isset($_GET['delete'])) {
    $sql = "DELETE FROM punishment WHERE id = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_userid);
        
        $param_userid = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: manageTicketEmne.php");
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

        $prisoncheckbox = false;
        if(isset($_POST['prison'])) {
            $prisoncheckbox = true;
        }
        
        $vehiclecheckbox = false;
        if(isset($_POST['vehicle'])) {
            $vehiclecheckbox = true;
        }
        
        $drugscheckbox = false;
        if(isset($_POST['drugs'])) {
            $drugscheckbox = true;
        }

        $sql = "INSERT INTO punishment (ticketemne, hasPrison, hasVehicle, hasStoffer) VALUES (?, ?, ?, ?)";
            
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "siii", $param_ticketemne, $param_prison, $param_drugs, $param_vehicle);
            
            $param_ticketemne = $emne;
            $param_prison = $prisoncheckbox;
            $param_vehicle = $vehiclecheckbox;
            $param_drugs = $drugscheckbox;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: manageTicketEmne.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($link);
    } elseif(isset($_POST["order_number"])) {
        $sql = "UPDATE punishment SET order_number = ?, ticketemne = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "isi", $param_order_number, $param_ticketemne, $param_ticketemneid);
            
            $param_order_number = $_POST["order_number"];
            $param_ticketemne = $_POST["ticketemne"];
            $param_ticketemneid = $_POST["ticketemneid"];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: manageTicketEmne.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

echo '<main class="ticketEmne">';
    echo '<div class="create-afdeling">';
        echo '<h2>Opret en ny Bødeemne</h2>';
        echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
            echo '<div class="form-group">';
                echo '<label>Bødeemnets navn</label>';
                echo '<input type="text" name="emne" class="form-control" value="' . $emne . '">';
            echo '</div>';
            echo '<div class="form-group" id="prison">';
                echo '<input type="checkbox" name="prison" value="true"> Kan loven give fængselstraf';
            echo '</div>';
            echo '<div class="form-group" id="vehicle">';
                echo '<input type="checkbox" name="vehicle" value="true"> Er det en del af færdselsloven';
            echo '</div>';
            echo '<div class="form-group" id="drugs">';
                echo '<input type="checkbox" name="drugs" value="true"> Er det om stoffer';
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
                    echo '<input type="text" class="id" name="ticketemneid" value="' . $row['id'] . '" readonly>';
                    echo '<input type="text" name="ticketemne" value="' . $row['ticketemne'] . '">';
                    echo '<div class="order-number">';
                        echo '<input type="tel" name="order_number" value="' . $row['order_number'] . '">';
                        echo '<input type="submit" class="btn btn-primary" value="Opdatere Bødeemne">';
                    echo '</div>';
                    echo '<a class="btn btn-danger" href="manageTicketEmne.php?delete=' . $row['id'] . '"> Slet Bødeemne</a>';
                echo '</div>';
            echo '</form>';
        }
    echo '</div>';
echo '</main>';

include '../../footer.php';
?>
