<?php
include '../../header.php';
 
// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


// Define variables and initialize with empty values
$license = $emne ="";
$license_err = $emne_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["license"]))){
        $license_err = "Indtast et navn på det nye license";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM licenses WHERE license_name = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_license);
            
            // Set parameters
            $param_license = trim($_POST["license"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $license_err = "Dette license eksistere allerede";
                } else{
                    $license = trim($_POST["license"]);
                }
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }

    if(empty(trim($_POST["emner"]))){
        $emne_err = "Vælg venligst et emne";     
    } else{
        $emne = trim($_POST["emner"]);
    }
    
    // Check input errors before inserting in database
    if(empty($license_err) && empty($emne_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO licenses (subject, license_name) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_subject, $param_license);
            
            // Set parameters
            $param_subject = $emne;
            $param_license = $license;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: syncAllLicenses.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<main>
    <div class="create-afdeling">
        <h2>Opret et nyt License</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group" id="license">
                <label>Bødetasktemne</label>
                <select name="emner" class="form-control">
                    <?php
                    $sql = "SELECT * FROM licenses_subjects";
                    $result = $link->query($sql);

                    while($row = $result->fetch_assoc()) {
                    ?>
                        <option value="<?php echo $row['license_emne'] ?>"><?php echo $row['license_emne'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group <?php echo (!empty($license_err)) ? 'has-error' : ''; ?>">
                <label>License navn</label>
                <input type="text" name="license" class="form-control" value="<?php echo $license; ?>">
                <span class="help-block"><?php echo $license_err; ?></span>
            </div>    
            <div class="form-group" id="submit">
                <input type="submit" class="btn btn-primary" value="Opret">
            </div>
        </form>
    </div>
</main>

<?php
include '../../footer.php';
?>