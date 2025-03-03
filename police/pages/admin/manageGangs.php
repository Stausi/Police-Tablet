<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


$sql = "SELECT * FROM gangs ORDER BY order_number ASC";
$result = $link->query($sql);

$username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];

$gang = $edit = "";
$gang_err = $edit_err = "";

if(isset($_GET['delete'])) {
    $sql = "DELETE FROM gangs WHERE id = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_userid);
        
        $param_userid = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: manageGangs.php");
        } else{
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["gang_name"])){
        if(empty(trim($_POST["gang_name"]))){
            $gang_err = "Indtast et navn på den nye bande";
        } else{
            $sql = "SELECT id FROM gangs WHERE gang_name = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_gang);
                
                $param_gang = trim($_POST["gang_name"]);
                
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $gang_err = "Denne bande eksistere allerede";
                    } else{
                        $gang = trim($_POST["gang_name"]);
                    }
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        if(empty($gang_err)){
            $sql = "INSERT INTO gangs (gang_name, created_by) VALUES (?, ?)";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ss", $param_gang, $param_username);
                
                $param_gang = $gang;
                $param_username = $username;
                
                if(mysqli_stmt_execute($stmt)){
                    header("location: manageGangs.php");
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        mysqli_close($link);
    } elseif(isset($_POST["order_number"])) {
        $sql = "UPDATE gangs SET order_number = ? WHERE gang_name = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "is", $param_order_number, $param_gang);
            
            $param_order_number = $_POST["order_number"];
            $param_gang = $_POST["gang"];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: manageGangs.php");
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
        <h2>Opret en ny Bande</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($gang_err)) ? 'has-error' : ''; ?>">
                <label>Bandens navn</label>
                <input type="text" name="gang_name" class="form-control" value="<?php echo $gang; ?>">
                <span class="help-block"><?php echo $gang_err; ?></span>
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
                    <input type="text" name="gang" value="<?php echo $row['gang_name']; ?>" readonly>
                    <input type="tel" name="order_number" value="<?php echo $row['order_number']; ?>">
                    <input type="submit" class="btn btn-primary" value="Sæt Nummer">
                    <a class="btn btn-danger" href="<?php echo 'manageGangs.php?delete=' . $row['id']; ?>"> Slet Bande</a>
                </div>
            </form>
        <?php } ?>
    </div>
</main>

<?php
include '../../footer.php';
?>