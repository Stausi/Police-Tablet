<?php
include '../header.php';

$action = "";
if(isset($_GET['action'])) {
    if($_GET['action'] == "password") {
        $action = "Dit Kodeord er ændret.";
    } elseif($_GET['action'] == "picture") {
        $action = "Dit Profilbillede er ændret.";
    } elseif($_GET['action'] == "number") {
        $action = "Dit Telefon Nummer er ændret.";
    } elseif($_GET['action'] == "document" && isset($_GET['name'])) {
        $action = "Fil uploaded. Fil navn & Link: <a href='../../assets/pdf_files/" . $_GET['name'] . "'>" . $_GET['name'] . "</a>";
    }
}

$user = $_SESSION["id"];
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$role = $_SESSION['role'];
$afdeling = $_SESSION['afdeling'];
$phone_number = $_SESSION['phone_number'];

$password = $confirm_password = $number_err = "";
$password_err = $confirm_password_err = $number = "";

if(isset($_GET['user'])) {
    $target_user = $_GET['user'];
    $sql = "SELECT * FROM users WHERE id='" . $target_user . "'";
    $result = $link->query($sql);

    while($row = mysqli_fetch_array($result)) {
        $user = $row['id'];
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
        $role = $row['role'];
        $afdeling = $row['afdeling'];
        
        if ($row['phone_number'] == null) {
            $phone_number = "Ukendt";
        }

        if ($row['phone_number'] != null) {
            $phone_number = $row['phone_number'];
        }
    }
}

if(isset($_GET['steamid'])) {
    $sql = "UPDATE users SET steamid = ? WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $param_steamid, $param_id);
    
        $param_steamid = $_GET['steamid'];
        $param_id = $_SESSION["id"];
        
        if(mysqli_stmt_execute($stmt)) {
            header("location: profile.php");
        } else {
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }

        $_SESSION["steam_id"] = $_GET['steamid'];
    }
}

$imgURL = "";
$file_pointer = '../../assets/profilesIMG/' . $user . '.png'; 

if (file_exists($file_pointer)) {
    $imgURL = $file_pointer;
} else {
    $imgURL = '../../assets/profilesIMG/unknown.png';
}

$steam_message = "Connect din Steam";
if (isset($_SESSION["steam_id"])) {
    $steam_message = "Reconnect din Steam";
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["password"])) {
        if(empty(trim($_POST["password"]))) {
            $password_err = "Indtast venligst et kodeord.";     
        } elseif(strlen(trim($_POST["password"])) < 6) {
            $password_err = "Kodeordet skal mindst være 6 tegn langt";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if(empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Indtast venligst et kodeord.";     
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($password_err) && ($password != $confirm_password)){
                $confirm_password_err = "Kodeordene stemmer ikke overens.";
            }
        }

        if(empty($password_err) && empty($confirm_password_err)) {
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_id);

                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_id = $user;
                
                if(mysqli_stmt_execute($stmt)) {
                    header("location: profile.php?action=password");
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            
            mysqli_stmt_close($stmt);
        }

        mysqli_close($link);
    } elseif(isset($_POST["number"])) {
        if(empty(trim($_POST["number"]))) {
            $number_err = "Indtast venligst et Nummer.";
        } else {
            $number = trim($_POST["number"]);
        }

        if(empty($number_err)) {
            $sql = "UPDATE users SET phone_number = ? WHERE id = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ii", $param_number, $param_id);

                $param_number = $number;
                $param_id = $user;
                
                if(mysqli_stmt_execute($stmt)) {
                    $_SESSION['phone_number'] = $number;
                    header("location: profile.php?action=number");
                } else{
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
            
            mysqli_stmt_close($stmt);
        }

        mysqli_close($link);
    }
}
?>

<main>
    <div class="myprofile">
        <h1>Min Profil</h1>
        <div class="mid-line"></div>
        <div class="profile">
            <div class="profile-pictures">
                <img src="<?php echo $imgURL ?>?<?php echo filemtime($imgURL); ?>" alt="user">
            </div>
            <div class="profile-informations">
                <div class="profile-information">
                    <p>Navn: <span><?php echo $firstname . ' ' . $lastname ?></span></p>
                    <p>Afdeling: <span><?php echo $afdeling ?></span></p>
                </div>
                <div class="profile-information">
                    <p>Stilling: <span><?php echo $role ?></span></p>
                    <?php
                        if($phone_number == null) {
                            echo "<p>Telefon Nummer: <span>Ukendt</span></p>";
                        } else {
                            echo "<p>Telefon Nummer: <span>" . $phone_number . "</span></p>";
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="mid-line"></div>
        <?php if($_SESSION["id"] == $user) { ?>
            <div class="profile-actions">
                <div class="profile-action">
                    <button data-toggle="modal" data-target="#passwordModal">Ændre Kodeord</button>
                </div>
                <div class="profile-action">
                    <button data-toggle="modal" data-target="#pictureModal">Ændre Profilbillede</button>
                </div>
                <?php
                    if ($_SESSION["hasPdfPrivilege"]) {
                        echo '<div class="profile-action">';
                        echo '<button data-toggle="modal" data-target="#pdfModal">Upload PDF</button>';
                        echo '</div>';
                    }
                ?>
                <div class="profile-action">
                    <button data-toggle="modal" data-target="#numberModal">Ændre Telefon Nummer</button>
                </div>
                <div class="profile-action">
                    <a href="/steam/init-openId.php">
                        <i class="fa-brands fa-steam text-2xl"></i>
                        <span><?php echo $steam_message; ?></span>
                    </a>
                </div>
            </div>
            <div class="mid-line"></div>
        <?php } ?>
    </div>
    <div class="profile-add">
        <div class="modal fade" id="pictureModal" tabindex="-1" role="dialog" aria-labelledby="pictureModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pictureModalLabel">Tilføj billede</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo "../upload.php?user=" . $user ?>" method="post" enctype="multipart/form-data">
                            <div class="custom-file">
                                <input type="file" name="fileToUpload" class="custom-file-input" id="customFile">
                                <label class="custom-file-label" for="customFile">Vælg fil</label>
                            </div>
                            <div class="form-group" id="submit">
                                <input type="submit" class="btn btn-primary" value="Upload billede">
                            </div>
                            <p>Maks 5 MB på billedet.</p>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="profile-pdf">
        <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pdfModalLabel">Tilføj PDF</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo "../upload_pdf.php?user=" . $user ?>" method="post" enctype="multipart/form-data">
                            <div class="custom-file">
                                <input type="file" name="fileToUpload" class="custom-file-input" id="customFile">
                                <label class="custom-file-label" for="customFile">Vælg fil</label>
                            </div>
                            <div class="form-group" id="submit">
                                <input type="submit" class="btn btn-primary" value="Upload PDF">
                            </div>
                            <p>Maks 5 MB på billedet.</p>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="profile-password">
        <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordModalLabel">Ændre Kodeordet</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                            <div class="form-group" id="submit">
                                <input type="submit" class="btn btn-primary" value="Ændre Kodeord">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="profile-number">
        <div class="modal fade" id="numberModal" tabindex="-1" role="dialog" aria-labelledby="numberModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="numberModalLabel">Ændre Telefon Nummer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group <?php echo (!empty($number_err)) ? 'has-error' : ''; ?>">
                                <label>Kodeord</label>
                                <input type="number" name="number" class="form-control" value="<?php echo $number; ?>">
                                <span class="help-block"><?php echo $number_err; ?></span>
                            </div>
                            <div class="form-group" id="submit">
                                <input type="submit" class="btn btn-primary" value="Ændre Telefon Nummer">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if($action != "") {  ?>
    <div class="action">
        <div class="action-column">
            <h1><?php echo $action ?></h1>
        </div>
    </div>
<?php } ?>

<?php
include '../footer.php';
?>