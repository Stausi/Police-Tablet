<?php
include '../../header.php';

$stmt = $link->prepare("SELECT * FROM afdelinger_ems ORDER BY order_number ASC");
$stmt->execute();
$result = $stmt->get_result();

$afdeling = $edit = "";
$afdeling_err = $edit_err = "";

if(isset($_GET['delete'])) {
    $sql = "DELETE FROM afdelinger_ems WHERE afdelingID = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_userid);
        
        $param_userid = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: manageAfdeling.php");
        } else{
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["afdelingsnavn"])){
        if(empty(trim($_POST["afdelingsnavn"]))){
            $afdeling_err = "Indtast et navn på den nye afdeling";
        } else{
            $sql = "SELECT afdelingID FROM afdelinger_ems WHERE afdeling = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_afdeling);
                
                $param_afdeling = trim($_POST["afdelingsnavn"]);
                
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $afdeling_err = "Denne afdeling eksistere allerede";
                    } else{
                        $afdeling = trim($_POST["afdelingsnavn"]);
                    }
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        if(empty($afdeling_err)){
            $sql = "INSERT INTO afdelinger_ems (afdeling) VALUES (?)";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_afdeling);
                
                $param_afdeling = $afdeling;
                
                if(mysqli_stmt_execute($stmt)){
                    header("location: manageAfdeling.php");
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        mysqli_close($link);
    } elseif(isset($_POST["order_number"])) {
        $sql = "UPDATE afdelinger_ems SET order_number = ? WHERE afdeling = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "is", $param_order_number, $param_afdeling);
            
            $param_order_number = $_POST["order_number"];
            $param_afdeling = $_POST["afdeling"];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: manageAfdeling.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<main class="licenseEmne">
    <div class="create-afdeling">
        <h2>Opret en ny Afdeling</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($afdeling_err)) ? 'has-error' : ''; ?>">
                <label>Afdelingens navn</label>
                <input type="text" name="afdelingsnavn" class="form-control" value="<?php echo $afdeling; ?>">
                <span class="help-block"><?php echo $afdeling_err; ?></span>
            </div>    
            <div class="form-group" id="submit">
                <input type="submit" class="btn btn-primary" value="Opret">
            </div>
        </form>
    </div>
    <div class="mid-line" style="margin-bottom:20px;"></div>
    <div class="manage-afdelinger">
        <?php while($row = $result->fetch_assoc()) { ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="manage-afdeling">
                    <input type="text" name="afdeling" value="<?php echo $row['afdeling']; ?>" readonly>
                    <input type="tel" name="order_number" value="<?php echo $row['order_number']; ?>">
                    <input type="submit" class="btn btn-primary" value="Sæt Nummer">
                    <a class="btn btn-danger" href="<?php echo 'manageAfdeling.php?delete=' . $row['afdelingID']; ?>"> Slet Afdeling</a>
                </div>
            </form>
        <?php } ?>
    </div>
</main>

<?php
include '../../footer.php';
?>