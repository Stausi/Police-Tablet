<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


$username = 0;
if(isset($_GET['user'])) {
    $username = $_GET['user'];
}
        
$stmt = $link->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $player);
$stmt->execute();
$result = $stmt->get_result();

$emneql = "SELECT * FROM licenses_subjects";
$emneresult = $link->query($emneql);

$emneData = [];
while ($emnerow = $emneresult->fetch_assoc()) {
    $emneData[$emnerow['license_emne']] = [];
}

$licensesql = "SELECT * FROM licenses ORDER BY subject, license_name ASC";
$licenseresult = $link->query($licensesql);

while ($licenserow = $licenseresult->fetch_assoc()) {
    $emne = $licenserow['subject'];
    if (array_key_exists($emne, $emneData)) {
        $emneData[$emne][] = $licenserow;
    }
}

$licenses = (array) null;
$badge = $role = $afdeling = $firstname = $lastname = $created = $nickname = $department = "";

$hasGangAccess = false;
$hasPdfPrivilege = false;
$webadmin = false;

$afdeling = $action = "";
$username_err = $badge_err = $afdelinger_err = $department_err = $role_err = $nickname_err = "";

while($row = $result->fetch_assoc()) {
    $badge = $row['username'];
    $role = $row['role'];
    $licenses = $row['licenses'];
    $afdeling = $row['afdeling'];
    $firstname = $row['firstname'];
    $lastname = $row['lastname'];
    $created = $row['created_at'];
    $webadmin = $row['WebsiteAdmin'];
    $hasGangAccess = $row['hasGangAccess'];
    $hasPdfPrivilege = $row['hasPdfPrivilege'];
    $nickname = $row['nickname'];
    $department = $row['department'];
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

        $sql = "UPDATE users SET password = ? WHERE id = ?";

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

    if (isset($_POST["nickname"])) {
        if ($_POST["nickname"] === '') {
            $nickname = '';
        } else {
            $nickname = trim($_POST["nickname"]);
        }
    } else {
        $nickname = '';
    }

    if(empty(trim($_POST["Afdelinger"]))){
        $afdelinger_err = "Vælg venligst en afdeling";     
    } else{
        $afdelinger = trim($_POST["Afdelinger"]);
    }

    if(empty(trim($_POST["Kreds"]))){
        $department_err = "Vælg venligst en kreds";     
    } else{
        $department = trim($_POST["Kreds"]);
    }

    if(isset($_POST['gang'])){
        $gang_checkbox = "1";    
    } else{
        $gang_checkbox = "0";
    }

    if(isset($_POST['pdf'])){
        $pdf_checkbox = "1";    
    } else{
        $pdf_checkbox = "0";
    }

    if(isset($_POST['admin'])){
        $checkbox = "1";    
    } else{
        $checkbox = "0";
    }
    
    if(empty($badge_err) && empty($role_err) && empty($afdelinger_err)){
        $sql = "UPDATE users SET username = ?, role = ?, afdeling = ?, nickname = ?, hasGangAccess = ?, hasPdfPrivilege = ?, WebsiteAdmin = ?, department = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssi", $param_badge, $param_role, $param_afdelinger, $param_nickname, $param_gang_checkbox, $param_pdf_checkbox, $param_checkbox, $param_department, $param_username);
            
            $param_badge = $badge;
            $param_role = $role;
            $param_afdelinger = $afdelinger;
            $param_nickname = $nickname;
            $param_gang_checkbox = $gang_checkbox;
            $param_pdf_checkbox = $pdf_checkbox;
            $param_checkbox = $checkbox;
            $param_department = $department;
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
    if(!empty($_GET['subject']) && !empty($_GET['license'])) {
        if($_GET['action'] == 'add') {
            $subject = $_GET['subject'];
            $license = $_GET['license'];

            $newObj = (object) [
                $subject => array($license)
            ];

            $jsonDecode = json_decode($licenses);

            $resultOjb = mergeObjectsRecursively($jsonDecode, $newObj);
            $resultJson = json_encode($resultOjb, true | JSON_UNESCAPED_UNICODE);

            $sql = "UPDATE users SET licenses = ? WHERE id = ?";
         
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $param_licenses, $param_username);

                $param_licenses = $resultJson;
                $param_username = $username;
                
                if(mysqli_stmt_execute($stmt)) {
                    header("location: user.php?user=" . $username);
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            
            mysqli_stmt_close($stmt);
        } 

        if($_GET['action'] == 'remove') {
            $subject = $_GET['subject'];
            $license = $_GET['license'];

            $jsonDecode = json_decode($licenses, true);

            foreach($jsonDecode as $k => $v) {
                if($k == $subject) {
                    if(!is_array($v)) {
                        $v = array($v);
                    }
    
                    $v = \array_diff($v, [$license]);
                    $jsonDecode[$subject] = $v;
                }
            }
            $resultJson = json_encode($jsonDecode, true | JSON_UNESCAPED_UNICODE);

            $sql = "UPDATE users SET licenses = ? WHERE id = ?";
         
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "si", $param_licenses, $param_username);
                
                $param_licenses = $resultJson;
                $param_username = $username;
                
                if(mysqli_stmt_execute($stmt)) {
                    header("location: user.php?user=" . $username);
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
        } 
    } else {
        if($_GET['action'] == 'delete') {
            $sql = "DELETE FROM users WHERE id = ?";
         
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
                    <button class="Edit" data-toggle="modal" data-target="<?php echo "#userModal" . $username ?>">Rediger Oplysninger</button>
                    <button class="Delete" data-toggle="modal" data-target="#confirmModal">Slet Brugeren</button>
                </div>
            <?php } ?>
        </div>
        <span class="sexy_line"></span>
        <div class="license-buttons">
            <a href="/police/pages/admin/opretLicense.php"><i class="fas fa-plus-square"></i> Opret nyt license</a>
            <a href="/police/pages/admin/manageLicenseEmne.php"><i class="fas fa-edit"></i> Håndtere license Emner</a>
        </div>
        <span class="sexy_line"></span>
        <div class="user-license">
            <h2>Nuværende Licenser</h2>
            <div class="license-subject">
                <?php
                foreach ($emneData as $emne => $licenses) { ?>
                    <div class="subject-header">
                    <h3><?php echo $emne; ?>:</h3>
                        <?php foreach ($licenses as $licenserow) {  ?>
                            <?php if (hasLicense($link, $username, $licenserow['license_name'])) { ?>
                                <div class='license'>
                                    <div class="license-text">
                                        <h4><?php echo $licenserow['license_name'] ?></h4>
                                    </div>
                                    <div class="license-button">
                                        <?php echo "<a href='manageLicense.php?licenseid=".$licenserow['id']."'>Håndtere License</a>"; ?>
                                        <?php echo "<a href='user.php?user=" . $username . "&action=remove&subject=" . $emne . "&license=".$licenserow['license_name']."'>Fjern License</a>"; ?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="user-license">
            <h2>Tilføj Licenser</h2>
            <div class="license-subject">
                <?php foreach ($emneData as $emne => $licenses) { ?>
                    <div class="subject-header">
                        <h3><?php echo $emne ?>: </h3>
                        <?php foreach ($licenses as $licenserow) {
                            if (!hasLicense($link, $username, $licenserow['license_name'])) { ?>
                                <div class='license'>
                                    <div class="license-text">
                                        <h4><?php echo $licenserow['license_name'] ?></h4>
                                    </div>
                                    <div class="license-button">
                                    <?php echo "<a href='manageLicense.php?licenseid=".$licenserow['id']."'>Håndtere License</a>"; ?>
                                        <?php echo "<a href='user.php?user=" . $username . "&action=add&subject=" . $emne . "&license=".$licenserow['license_name']."'>Tilføj License</a>"; ?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="modal fade" id="<?php echo "userModal" . $username ?>" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo $firstname . " " . $lastname . " (Badge nr: " . $badge . ")" ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
                                    $sql = "SELECT * FROM afdelinger";
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
                            <div class="form-group" id="afdeling">
                                <label>Ny Kreds <?php echo $department; ?></label>
                                <select name="Kreds" class="form-control">
                                    <?php
                                    foreach ($departments as $department_key => $department_value) {
                                        if($department == $department_key) {
                                            echo '<option value="' . $department_key .'" selected>' . $department_value . '</option>';
                                        } else {
                                            echo '<option value="' . $department_key .'">' . $department_value . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="help-block"><?php echo $department_err; ?></span>
                            </div>
                            <div class="form-group <?php echo (!empty($nickname_err)) ? 'has-error' : ''; ?>">
                                <label>Nickname</label>
                                <input type="text" name="nickname" class="form-control" value="<?php echo $nickname; ?>">
                                <span class="help-block"><?php echo $nickname_err; ?></span>
                            </div>
                            <div class="form-group" id="gang">
                                <?php if($hasGangAccess == '1') { ?>
                                    <input class="checkbox" type="checkbox" name="gang" value="true" checked> Brugeren skal have Gang Permissions
                                <?php } else { ?>
                                    <input class="checkbox" type="checkbox" name="gang" value="true"> Brugeren skal have Gang Permissions
                                <?php } ?>
                            </div>
                            <div class="form-group" id="pdf">
                                <?php if($hasPdfPrivilege == '1') { ?>
                                    <input class="checkbox" type="checkbox" name="gang" value="true" checked> Brugeren skal have Pdf Permissions
                                <?php } else { ?>
                                    <input class="checkbox" type="checkbox" name="gang" value="true"> Brugeren skal have Pdf Permissions
                                <?php } ?>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                    </div>
                </div>
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