<?php
include '../../header.php';

// Define variables and initialize with empty values
$username = $password = $confirm_password = $firstname = $lastname = "";
$username_err = $password_err = $confirm_password_err = $firstname_err = $lastname_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Indtast venligst et badge nummer.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users_ems WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "Dette badge nummer er allerede taget";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Indtast venligst et kodeord.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Kodeordet skal mindst være 6 tegn langt";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Indtast venligst et kodeord.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Kodeordene stemmer ikke overens.";
        }
    }

    if(empty(trim($_POST["firstname"]))){
        $firstname_err = "Indtast venligst et fornavn.";
    } else{
        $firstname = trim($_POST["firstname"]);
    }

    if(empty(trim($_POST["lastname"]))){
        $lastname_err = "Indtast venligst et efternavn.";
    } else{
        $lastname = trim($_POST["lastname"]);
    }

    if(empty(trim($_POST["Afdelinger"]))){
        $afdelinger_err = "Vælg venligst en afdeling";     
    } else{
        $afdelinger = trim($_POST["Afdelinger"]);
    }

    if(isset($_POST['admin'])){
        $checkbox = 1;    
    } else{
        $checkbox = 0;
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($firstname_err) && empty($lastname_err) && empty($afdelinger_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users_ems (username, password, firstname, lastname, job, role, afdeling, WebsiteAdmin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssssi", $param_username, $param_password, $param_firstname, $param_lastname, $param_job, $param_role, $param_afdelinger, $param_checkbox);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_firstname = $firstname;
            $param_lastname = $lastname;
            $param_job = 'ems';
            $param_role = 'Elev';
            $param_afdelinger = $afdelinger;
            $param_checkbox = $checkbox;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: ../employed.php");
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
    <div class="create-user">
        <h2>Opret en ny Betjent</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Badge nummer</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Kodeord</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Bekræft Kodeord</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($firstname_err)) ? 'has-error' : ''; ?>">
                <label>Fornavn</label>
                <input type="text" name="firstname" class="form-control" value="<?php echo $firstname; ?>">
                <span class="help-block"><?php echo $firstname_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($lastname_err)) ? 'has-error' : ''; ?>">
                <label>Efternavn</label>
                <input type="text" name="lastname" class="form-control" value="<?php echo $lastname; ?>">
                <span class="help-block"><?php echo $lastname_err; ?></span>
            </div>
            <div class="form-group" id="afdeling">
                <label>Afdeling</label>
                <select name="Afdelinger" class="form-control">
                    <?php
                    $sql = "SELECT * FROM afdelinger_ems";
                    $result = $link->query($sql);

                    while($row = $result->fetch_assoc()) {
                    ?>
                        <option value="<?php echo $row['afdeling'] ?>"><?php echo $row['afdeling'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group" id="admin">
                <input type="checkbox" name="admin" value="true"> Brugeren skal have Admin Permissions
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