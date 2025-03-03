<?php
include '../header.php';

$player = $_GET['player'];
$sql = "SELECT * FROM population_ems WHERE id='" . $player . "'";
$result = $link->query($sql);

$player_err = $note_err = "";
$name = $dob = $height = $sex = $phone = $note = "";

while($row = mysqli_fetch_array($result)) {
    $name = $row['name'];
    $dob = $row['dob'];
    $height = $row['height'];
    $sex = $row['sex'];
    $phone = $row['phone_number'];
    $note = $row['note'];

    $dobSlit = explode(" ", $dob);
    $dob = $dobSlit[0];
    $dob = str_replace(".", "/", $dob);
}

$imgURL = "";
$file_pointer = '../../assets/emsPlayersIMG/' . $player . '.png'; 

if (file_exists($file_pointer)) {
    $imgURL = $file_pointer;
} else {
    $imgURL = '../../assets/emsPlayersIMG/unknown.png';
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'RemovePicture') {
        if (file_exists($file_pointer)) {
            if (!unlink($file_pointer)) {  
                echo ("$file_pointer cannot be deleted due to an error");  
            } else {
                header("location: playerpsyk.php?player=" . $player); 
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $sql = "DELETE FROM population_psykjournals WHERE id = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        $param_id = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: playerpsyk.php?player=" . $player);
        } else{
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["playerid"]))) {
        $playerid_err = "Vælg venligst et playerid";
    } else {
        $playerid = trim($_POST["playerid"]);
    }

    if (isset($_POST["note"])) {
        $new_note = "";
        if (!empty($_POST["note"])) {
            $new_note = trim($_POST["note"]);
        }

        if (!empty($new_note)) {
            $sql = "UPDATE population_ems SET note = ? WHERE id = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $param_note, $param_id);

                $param_note = $new_note;
                $param_id = $playerid;

                if (mysqli_stmt_execute($stmt)) {
                    header("location: playerpsyk.php?player=" . $playerid);
                } else {
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<main>
    <div class="player">
        <div class="player-header">
            <div class="player-image">
                <img src="<?php echo $imgURL ?>" alt="player">
                <button class="change-image" data-toggle="modal" data-target="#profileModal">Ændre billede til KR</button>
            </div>
            <div class="player-info">
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Navn: </h2>
                        <h2 class="title"><?php echo "$name" ?></h2>
                    </div>
                    <div class="info-text">
                        <h2 class="title">Fødselsdag: </h2>
                        <h2 class="title"><?php echo "$dob" ?></h2>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Højde: </h2>
                        <h2 class="title"><?php echo $height ?></h2>
                    </div>
                    <div class="info-text">
                        <h2 class="title">Køn: </h2>
                        <h2 class="title"><?php echo $sex ?></h2>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Telefon: </h2>
                        <h2 class="title"><?php echo $phone ?></h2>
                    </div>
                    <div class="info-text">
                        <h2 class="title">Note: </h2>
                        <h2 class="title"><?php echo $note ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="mid-line"></div>
        <div class="buttons">
            <a class="Delete" href="addJournal.php?player=<?php echo $player ?>&type=psykolog">Tilføj til Psykolog Arktiv</a>
            <button class="Delete" data-toggle="modal" data-target="#noteModal">Sæt note</button>
        </div>
        <div class="mid-line"></div>
        <div class="krim-header">
            <div class="wanted-text">
                <h2>Journalregister</h2>
            </div>
            <div class="table-responsive">
                <table id="big-text-table" class="table table-striped table-hover">
                    <tr>
                        <th>Journal Nr.</th>
                        <th>Dato</th>
                        <th>Ansat</th>
                        <th>Årsag</th>
                        <th>Se Raport</th>
                        <?php 
                            if($_SESSION["websiteadmin"]) {
                                echo "<th>Redigere</th>";
                            }
                        ?>
                    </tr>
                        <?php
                        $sql = "SELECT * FROM population_psykjournals WHERE pid ='" . $player . "'";
                        $result = $link->query($sql);
                        while($row = $result->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td><?php echo $row['id'] ?></td>
                            <td><?php echo $row['dato'] ?></td>
                            <td><?php echo $row['username'] ?></td>
                            <td><?php echo $row['reason'] ?></td>
                            <td>
                                <button onclick="openJournal(<?php echo $row['id'] ?>)" title="Open" class="edit"><i class="fas fa-list"></i></button>
                            </td>
                            <?php if($_SESSION["websiteadmin"]) { ?>
                                <td class="kr_action">
                                    <button onclick="deleteJournal(<?php echo $row['id'] ?>)" title="Slet" class="delete"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
    <div class="profile-note">
        <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="noteModalLabel">Edit KR</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group <?php echo (!empty($player_err)) ? 'has-error' : ''; ?>">
                                <label>ID</label>
                                <input type="text" name="playerid" class="form-control" value="<?php echo $player; ?>" readonly>
                                <span class="help-block"><?php echo $player_err; ?></span>
                            </div>
                            <div class="form-group <?php echo (!empty($note_err)) ? 'has-error' : ''; ?>">
                                <label>Note</label>
                                <textarea name="note" class="form-control" value="<?php $note; ?>"><?php echo $note; ?></textarea>
                                <span class="help-block"><?php echo $note_err; ?></span>
                            </div>
                            <div class="form-group update" id="submit">
                                <input type="submit" class="btn btn-primary" value="Opdatere Note">
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
    <div class="profile-add">
        <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profileModalLabel">Tilføj billede</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo "../upload.php?player=" . $player ?>" method="post" enctype="multipart/form-data">
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
    <script type="text/javascript">
        $('#customFile').on("change",function() {
            var i = $(this).prev('label').clone();
            var file = $('#customFile')[0].files[0].name;
            $(this).next('label').text(file);
        });

        function deleteJournal(id) {
            window.location.href = "playerpsyk.php?player=" + <?php echo $player; ?> + "&delete=" + id;
        }

        function openJournal(journalId) {
            var pdfUrl = 'generate_psykjournal.php?id=' + journalId;
            window.open(pdfUrl, '_blank');
        }
    </script>
</main>

<?php
include '../footer.php';
?>