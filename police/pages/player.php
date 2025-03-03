<?php
include '../header.php';

// Sikkerhed for at forhindre advokater og dommere at tilgå denne side.
if ($_SESSION["afdeling"] == "Advokatledelse") {
    header("location: /police/pages/employed.php");
    exit;
}

$player = $_GET['player'] ?? '';
$name = '';
$username = $_SESSION['username'] ?? '';

if (!empty($player)) {
    $sql = "SELECT * FROM population WHERE id=?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("s", $player);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $name = $row['name'];
        }
        $stmt->close();
    }

    if (!empty($username) && !empty($name)) {
        logPlayerVisit($username, $name);
    }
}


$player = $_GET['player'];
$note_err = $playerid_err = "";

$sql = "SELECT * FROM population WHERE id='" . $player . "' ";
$result = $link->query($sql);

$sql = "SELECT klip, status FROM population_cases WHERE pid='" . $player . "' AND dato >= DATE(NOW()) - INTERVAL 3 DAY";
$result2 = $link->query($sql);

$sql = "SELECT * FROM population_wanted WHERE target_id='" . $player . "'";
$result4 = $link->query($sql);

$sql = "SELECT conditional FROM population_cases WHERE pid='" . $player . "' AND dato >= DATE(NOW()) - INTERVAL 3 DAY";
$result5 = $link->query($sql);


$klip = $height = $number = 0;
$status = "Ingen aktiv frakendelse";
$name = $dob = $hasFingerprint = $note = $gang = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_name"])) {
    $new_name = $_POST["new_name"];
    $player_id = $_POST["player_id"];

    $sql = "UPDATE population SET name = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_name, $player_id);
        if (mysqli_stmt_execute($stmt)) {
            // Brug session til at sende en succesmeddelelse
            $_SESSION['message'] = 'Navnet er opdateret!';
            header("location: player.php?player=" . $player_id);
            exit;
        } else {
            echo "Noget gik galt. Prøv igen senere.";
        }
        mysqli_stmt_close($stmt);
    }
}
if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']); // Slet meddelelsen efter visning for at undgå at den vises igen
}




function sendDiscordWebhook($title, $description, $footer, $timestamp)
{
    $webhookurl = "";

    $json_data = json_encode([
        "embeds" => [
            [
                "title" => $title,
                "type" => "rich",
                "description" => $description,
                "timestamp" => $timestamp,
                "color" => hexdec("3366ff"),
                "footer" => [
                    "text" => $footer
                ]
            ]
        ]
    ]);

    // Initiate cURL
    $ch = curl_init($webhookurl);
    // Set cURL options
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the POST request
    $response = curl_exec($ch);
    // Close cURL session
    curl_close($ch);
}

function logPlayerVisit($username, $playerName) {
    global $link;  // Sørg for at kunne bruge $link i funktionen

    // Hent fornavn og efternavn fra databasen
    $firstname = '';
    $lastname = '';

    $sql = "SELECT firstname, lastname FROM users WHERE username = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $firstname, $lastname);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    $webhookUrl = ""; // Ensure this is correct
    $timestamp = date("c");
    $title = "Tjek af KR";
    $description = "Betjent **{$username} - {$firstname} {$lastname}** har tjekket KR for **{$playerName}**.";
    $footer = "Logger adgang til KR";

    $json_data = json_encode([
        "embeds" => [
            [
                "title" => $title,
                "description" => $description,
                "timestamp" => $timestamp,
                "color" => hexdec("3366ff"),
                "footer" => ["text" => $footer]
            ]
        ]
    ]);

    $ch = curl_init($webhookUrl);
    if ($ch === false) {
        error_log("Failed to initialize cURL session");
        return;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    if ($response === false) {
        error_log("cURL error: " . curl_error($ch));
    } else {
        error_log("cURL response: " . $response);
    }
    curl_close($ch);
}



while ($row = mysqli_fetch_array($result)) {
    $name = $row['name'];
    $dob = $row['dob'];
    $gang = $row['gang'];

    $dobSlit = explode(" ", $dob);
    $dob = $dobSlit[0];
    $dob = str_replace(".", "/", $dob);

    $height = $row['height'];
    $number = $row['phone_number'];
    $hasFingerprint = ($row['fingerprint'] == 1) ? "JA" : "NEJ";
    $note = ($row['note'] == NULL) ? "Ingen note" : $row['note'];
}

while ($row = mysqli_fetch_array($result2)) {
    $klip = $klip + $row['klip'];

    if ($row['status'] == "Ubetinget frakendelse") {
        $status = $row['status'];
    }

    if ($row['status'] == "Betinget frakendelse af Bil" || $row['status'] == "Betinget frakendelse af Motorcykel" || $row['status'] == "Betinget frakendelse af Lastbil") {
        if ($status != "Ubetinget frakendelse") {
            $status = $row['status'];
        }
    }
}

$isWanted = false;
$WantedSigtelser = "";

while ($row = mysqli_fetch_array($result4)) {
    if (isset($row['id']) && $row['status'] == 1) {
        $isWanted = true;
        $WantedSigtelser = $row['sigtet'];
    }
}

$isBetinget = false;
while ($row = mysqli_fetch_array($result5)) {
    if ($row['conditional'] == 1) {
        $isBetinget = true;
    }
}

$imgURL = "";
$file_pointer = '../../assets/playersIMG/' . $player . '.png';

if (file_exists($file_pointer)) {
    $modTime = filemtime($file_pointer);
    $imgURL = $file_pointer . '?v=' . $modTime;
} else {
    $imgURL = '../../assets/playersIMG/unknown.png';
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'RemovePicture') {
        if (file_exists($file_pointer)) {
            if (!unlink($file_pointer)) {
                echo ("$file_pointer cannot be deleted due to an error");
            } else {
                header("location: player.php?player=" . $player);
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $caseId = $_GET['delete'];

    // Lavet primært fra inspiration fra wanted.php, samt ChatGPT og Copilot.
    $sql = "SELECT username, sigtet, ticket, prison, erkender, comment FROM population_cases WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $caseId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $username, $sigtet, $ticket, $prison, $erkender, $comment);
        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt); // Close the first statement

            // Now delete the case
            $sqlDelete = "DELETE FROM population_cases WHERE id = ?";
            if ($stmtDelete = mysqli_prepare($link, $sqlDelete)) {
                mysqli_stmt_bind_param($stmtDelete, "i", $caseId);
                if (mysqli_stmt_execute($stmtDelete)) {
                    mysqli_stmt_close($stmtDelete); // Close the second statement

                    // Successful deletion, send a Discord notification
                    $user = $_SESSION['username'];
                    $erkenderText = ($erkender == 1) ? "Ja" : "Nej";
                    $title = "Sigtelse slettet";
                    $description = "Betjent **{$user}** har slettet en sigtelse fra **{$name}**.\n\n**Sigtelser:** {$sigtet}\n**Bøde:** {$ticket} DKK\n**Fængselsstraf:** {$prison} måneder\n**Erkender:** {$erkenderText}\n**Kommentar:** {$comment}\n**Betjenten der har sigtet:** {$username}";
                    $footer = "Handling udført";
                    $timestamp = date("c");
                    sendDiscordWebhook($title, $description, $footer, $timestamp);

                    header("location: player.php?player=" . $player);
                    exit;
                } else {
                    echo "Something went wrong. Please try again later.<br>";
                    printf("Error message: %s\n", $link->error);
                }
            } else {
                echo "Could not prepare the SQL statement for deletion.<br>";
                printf("Error message: %s\n", $link->error);
            }
        } else {
            mysqli_stmt_close($stmt); // Close the statement if fetch fails
            echo "Could not retrieve case details.<br>";
            printf("Error message: %s\n", $link->error);
        }
    } else {
        echo "Could not prepare the SQL statement.<br>";
        printf("Error message: %s\n", $link->error);
    }
}





if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["playerid"]))) {
        $playerid_err = "Vælg venligst et playerid";
    } else {
        $playerid = trim($_POST["playerid"]);
    }

    if (isset($_POST["Gang"])) {
        $new_gang = 0;
        if (!empty($_POST["Gang"])) {
            $new_gang = $_POST["Gang"];
        }

        if (!empty($new_gang)) {
            $sql = "UPDATE population SET gang = ? WHERE id = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $param_gang, $param_id);

                $param_gang = $new_gang;
                $param_id = $playerid;

                if (mysqli_stmt_execute($stmt)) {
                    header("location: player.php?player=" . $playerid);
                } else {
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }
            }

            mysqli_stmt_close($stmt);
        }
    } else {
        $new_note = "";
        if (!empty($_POST["note"])) {
            $new_note = trim($_POST["note"]);
        }

        if (!empty($new_note)) {
            $sql = "UPDATE population SET note = ? WHERE id = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $param_note, $param_id);

                $param_note = $new_note;
                $param_id = $playerid;

                if (mysqli_stmt_execute($stmt)) {
                    header("location: player.php?player=" . $playerid);
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
                <button class="change-image" data-toggle="modal" data-target="#profileModal">Ændre billede til
                    KR</button>
            </div>
            <div class="player-info">
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Navn: </h2>
                        <h2 class="title"><?php echo "$name" ?></h2>
                        <?php if ($_SESSION["username"] == "199"): ?>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#nameChangeModal">Skift
                                navn</button>
                        <?php endif; ?>
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
                        <h2 class="title">Tlf. nummer: </h2>
                        <h2 class="title"><?php echo $number ?></h2>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Antal Klip: </h2>
                        <h2 class="title"><?php echo $klip ?></h2>
                    </div>
                    <div class="info-text">
                        <h2 class="title">Kørekort Status: </h2>
                        <h2 class="title"><?php echo $status ?></h2>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Fingeraftryk: </h2>
                        <h2 class="title"><?php echo $hasFingerprint ?></h2>
                    </div>

                    <?php if ($_SESSION["afdeling"] != "Advokatledelse" && $_SESSION["afdeling"] != "Dommer"): ?>
                        <div class="info-text">
                            <h2 class="title">Note:</h2>
                            <h2 class="title"><?php echo $note ?></h2>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php if ($isWanted) { ?>
            <div class="wanted-line">
                <h1><?php echo "Efterlyst: " . $name; ?></h1>
                <p><?php echo "Sigtet for: " . $WantedSigtelser; ?></p>
            </div>
        <?php } ?>
        <?php if ($isBetinget) { ?>
            <div class="wanted-line">
                <h1><?php echo "Betinget dømt"; ?></h1>
            </div>
        <?php } ?>
        <div class="mid-line"></div>
        <div class="buttons">
            <a class="Delete" href="/police/pages/addKrimi.php?player=<?php echo $player ?>&type=efter">Tilføj til
                Efterlysning</a>
            <a class="Delete" href="/police/pages/addKrimi.php?player=<?php echo $player ?>&type=krimi">Tilføj til
                Kriminalregistreret</a>
            <button class="Delete" data-toggle="modal" data-target="#noteModal">Sæt note</button>
            <?php
            if ($_SESSION["hasGangAccess"]) {
                echo '<button class="Delete" data-toggle="modal" data-target="#gangModal">Tilføj til bande</button>';
            }
            ?>
        </div>
        <div class="mid-line"></div>
        <div class="krim-header">
            <div class="wanted-text">
                <h2>Kriminalregister</h2>
            </div>
            <div class="table-responsive">
                <table id="big-text-table" class="table table-striped table-hover">
                    <tr>
                        <th>Journal Nr.</th>
                        <th>Dato</th>
                        <th>Betjent</th>
                        <th>Sigtet for</th>
                        <th>Bødestørrelse</th>
                        <th>Fængselsstraf</th>
                        <th>Klip</th>
                        <th>Kommentar</th>
                        <th>Erkender</th>
                        <th>Betinget</th>
                        <?php
                        if ($_SESSION["websiteadmin"]) {
                            echo "<th>Redigere</th>";
                        }
                        ?>
                    </tr>
                    <?php
                    $sql = "SELECT * FROM population_cases WHERE pid ='" . $player . "' ORDER BY dato DESC, id DESC";
                    $result = $link->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo $row['id'] ?></td>
                            <td><?php echo date('d-m-y H:i', strtotime($row['dato'])); ?></td>
                            <td><?php echo $row['username'] ?></td>
                            <td><?php echo $row['sigtet'] ?></td>
                            <td><?php echo number_format($row['ticket'], 0, ",", ".") ?>,- DKK</td>
                            <td><?php echo $row['prison'] ?> Måneder</td>
                            <td><?php echo $row['klip'] ?></td>
                            <td><?php echo $row['comment'] ?></td>
                            <td><?php echo ($row['erkender'] == 1) ? "Ja" : "Nej"; ?></td>
                            <td><?php echo ($row['conditional'] == 1) ? "Ja" : "Nej"; ?></td>
                            <?php if ($_SESSION["websiteadmin"]) { ?>
                                <td class="kr_action">
                                    <button onclick="editKR(<?php echo $row['id'] ?>)" title="Redigere" class="edit"><i
                                            class="fas fa-edit"></i></button>
                                    <button onclick="deleteKR(<?php echo $row['id'] ?>)" title="Slet" class="delete"><i
                                            class="fas fa-trash-alt"></i></button>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
    <div class="profile-note">
        <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel"
            aria-hidden="true">
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
                            <div class="form-group <?php echo (!empty($playerid_err)) ? 'has-error' : ''; ?>">
                                <label>ID</label>
                                <input type="text" name="playerid" class="form-control" value="<?php echo $player; ?>"
                                    readonly>
                                <span class="help-block"><?php echo $playerid_err; ?></span>
                            </div>
                            <div class="form-group <?php echo (!empty($note_err)) ? 'has-error' : ''; ?>">
                                <label>Note</label>
                                <textarea name="note" class="form-control"
                                    value="<?php $note; ?>"><?php echo $note; ?></textarea>
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
    <div class="profile-gang">
        <div class="modal fade" id="gangModal" tabindex="-1" role="dialog" aria-labelledby="gangModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="gangModalLabel">Edit KR</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group <?php echo (!empty($playerid_err)) ? 'has-error' : ''; ?>">
                                <label>ID</label>
                                <input type="text" name="playerid" class="form-control" value="<?php echo $player; ?>"
                                    readonly>
                                <span class="help-block"><?php echo $playerid_err; ?></span>
                            </div>
                            <div class="form-group" id="afdeling">
                                <label>Sæt bande</label>
                                <select name="Gang" class="form-control">
                                    <option value="-1" selected>Ingen bande</option>';

                                    <?php
                                    $sql = "SELECT * FROM gangs";
                                    $result = $link->query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        if ($row["id"] == $gang) {
                                            echo '<option value="' . $row["id"] . '" selected>' . $row["gang_name"] . '</option>';
                                        } else {
                                            echo '<option value="' . $row["id"] . '">' . $row["gang_name"] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group update" id="submit">
                                <input type="submit" class="btn btn-primary" value="Opdatere Personens Bande">
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
        <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profileModalLabel">Tilføj billede</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo "../upload.php?player=" . $player ?>" method="post"
                            enctype="multipart/form-data">
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

    <div class="modal fade" id="nameChangeModal" tabindex="-1" role="dialog" aria-labelledby="nameChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nameChangeModalLabel">Skift Navn</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Nyt navn</label>
                        <input type="text" name="new_name" class="form-control" required>
                        <input type="hidden" name="player_id" value="<?php echo $player; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Opdater Navn</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <script type="text/javascript">
        $('#customFile').on("change", function () {
            var i = $(this).prev('label').clone();
            var file = $('#customFile')[0].files[0].name;
            $(this).next('label').text(file);
        });

        function editKR(id) {
            window.location.href = "addKrimi.php?player=" + <?php echo $player; ?> + "&case=" + id + "&type=edit";
        }

        function deleteKR(id) {
            window.location.href = "player.php?player=" + <?php echo $player; ?> + "&delete=" + id;
        }
    </script>


</main>

<?php
include '../footer.php';
?>