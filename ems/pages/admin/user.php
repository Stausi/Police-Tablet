<?php
include '../../header.php';

$username = 0;
if(isset($_GET['user'])) {
    $username = $_GET['user'];
}
        
$sql = "SELECT * FROM users_ems WHERE id = '" . $username . "'";
$result = $link->query($sql);

$badge = $role = $afdeling = $firstname = $lastname = $created = "";
$webadmin = false;

$afdeling = $action = "";
$username_err = $badge_err = $afdelinger_err = $role_err = "";

while($row = $result->fetch_assoc()) {
    $badge = $row['username'];
    $role = $row['role'];
    $afdeling = $row['afdeling'];
    $firstname = $row['firstname'];
    $lastname = $row['lastname'];
    $created = $row['created_at'];
    $webadmin = $row['WebsiteAdmin'];
}

function mergeObjectsRecursively($obj1, $obj2) {
    $baseObject = (array) $obj1;
    $mergeObject = (array) $obj2;
    $merged = array_merge_recursive($baseObject, $mergeObject);
    return (object) $merged;
}

if(isset($_GET['action'])) {
    if($_GET['action'] == "password") {
        $password = randomPassword();

        $sql = "UPDATE users_ems SET password = ? WHERE id = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_username);

            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)) {
                $action = "Kodeord er blevet resat til: " . $password;
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["brugernavn"]))){
        $username_err = "Vælg venligst et brugernavn";     
    } else{
        $username = trim($_POST["brugernavn"]);
    }

    if(empty(trim($_POST["badge"]))){
        $badge_err = "Vælg venligst et badge nummer";     
    } else{
        $badge = trim($_POST["badge"]);
    }

    if(empty(trim($_POST["role"]))){
        $role_err = "Vælg venligst en rolle";     
    } else{
        $role = trim($_POST["role"]);
    }

    if(empty(trim($_POST["Afdelinger"]))){
        $afdelinger_err = "Vælg venligst en afdeling";     
    } else{
        $afdelinger = trim($_POST["Afdelinger"]);
    }

    if(isset($_POST['admin'])){
        $checkbox = "1";    
    } else{
        $checkbox = "0";
    }
    
    if(empty($badge_err) && empty($role_err) && empty($afdelinger_err)){
        $sql = "UPDATE users_ems SET username = ?, role = ?, afdeling = ?, WebsiteAdmin = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $param_badge, $param_role, $param_afdelinger, $param_checkbox, $param_username);
            
            $param_badge = $badge;
            $param_role = $role;
            $param_afdelinger = $afdelinger;
            $param_checkbox = $checkbox;
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: user.php?user=" . $param_username);
                exit();
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    }
}

if(isset($_GET['action'])) {
    if($_GET['action'] == 'delete') {
        $sql = "DELETE FROM users_ems WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: ../employed.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    }
}
?>

<main>
    <?php if($action != "") {  ?>
        <div class="action">
            <div class="action-column">
                <h1><?php echo $action ?></h1>
            </div>
        </div>
    <?php } ?>
    <div class="user">
        <div class="user-header">
            <div class="user-header-text">
                <h2><?php echo $firstname . " " . $lastname ?></h2>
                <h2><?php echo "(Badge nr: " . $badge . ")" ?></h2>
            </div>
            <?php if($_SESSION["websiteadmin"]) { ?>
                <div class="user-header-buttons">
                    <a href="user.php?user=<?php echo $username; ?>&action=password" class="Reset"">Reset Kodeord</a>
                    <button class="Delete" data-toggle="modal" data-target="#confirmModal">Slet Brugeren</button>
                </div>
            <?php } ?>
        </div>
        <span class="sexy_line"></span>
        <div class="user-form-content">
            <div class="user-form-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                        <label>Bruger ID</label>
                        <input type="text" name="brugernavn" class="form-control" value="<?php echo $username; ?>" readonly>
                        <span class="help-block"><?php echo $username_err; ?></span>
                    </div>
                    <div class="form-group <?php echo (!empty($badge_err)) ? 'has-error' : ''; ?>">
                        <label>Badge nummer</label>
                        <input type="text" name="badge" class="form-control" value="<?php echo $badge; ?>">
                        <span class="help-block"><?php echo $badge_err; ?></span>
                    </div>
                    <div class="form-group <?php echo (!empty($role_err)) ? 'has-error' : ''; ?>">
                        <label>Rolle</label>
                        <input type="text" name="role" class="form-control" value="<?php echo $role; ?>">
                        <span class="help-block"><?php echo $role_err; ?></span>
                    </div>
                    <div class="form-group" id="afdeling">
                        <label>Ny Afdeling</label>
                        <select name="Afdelinger" class="form-control">
                            <?php
                            $sql = "SELECT * FROM afdelinger_ems";
                            $result = $link->query($sql);

                            while($row = $result->fetch_assoc()) {
                                if($row["afdeling"] == $afdeling) {
                                    echo '<option value="' . $row["afdeling"] .'" selected>' . $row["afdeling"] . '</option>';
                                } else {
                                    echo '<option value="' . $row["afdeling"] .'">' . $row["afdeling"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <span class="help-block"><?php echo $afdelinger_err; ?></span>
                    </div>
                    <div class="form-group" id="admin">
                        <?php if($webadmin == '1') { ?>
                            <input class="checkbox" type="checkbox" name="admin" value="true" checked> Brugeren skal have Admin Permissions
                        <?php } else { ?>
                            <input class="checkbox" type="checkbox" name="admin" value="true"> Brugeren skal have Admin Permissions
                        <?php } ?>
                    </div>
                    <div class="form-group update" id="submit">
                        <input type="submit" class="btn btn-primary" value="Opdatere Bruger">
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo "Er du sikker på at du vil slette: " . $username?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-primary" href="<?php echo "user.php?user=" . $username . "&action=delete" ?>">Bekræft sletning</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Afbryd</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include '../../footer.php';
?>