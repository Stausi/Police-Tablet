<?php
include '../header.php';

// Sikkerhed for at forhindre advokater og dommere at tilgå denne side.
if($_SESSION["afdeling"] == "Advokatledelse" || $_SESSION["afdeling"] == "Dommer") {
    header("location: /police/pages/employed.php");
    exit;
}

$sql = "SELECT * FROM population_vehicles ORDER BY dato DESC";
$result = $link->query($sql);

$plate = $reason = "";
$plate_err = $reason_err = "";

$owner = ""; // Tilføj ejer
$owner_err = $owner_err = ""; // Tilføj ejer_err

if(isset($_GET['action'])) {
    $id = $_GET['id'];

    if($_GET['action'] == 'toggle') {
        $sql = "UPDATE population_vehicles SET status = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_status, $param_id);

            $status = $_GET['status'];
            $status = ($status == 1) ? 0 : 1;

            $param_status = $status;
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: wanted_vehicles.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    } elseif($_GET['action'] == 'remove') {
        $sql = "DELETE FROM population_vehicles WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_id);

            $param_id = $id;
            $plate = $_GET['plate'];

            if(mysqli_stmt_execute($stmt)){
                header("location: wanted_vehicles.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["plate"]))){
        $plate_err = "Være venlig at indtaste Nummerpladen";
    } else{
        $plate = trim($_POST["plate"]);
    }

    if(empty(trim($_POST["reason"]))){
        $reason_err = "Være venlig at indtaste hvad efterlyste har gjort";
    } else{
        $reason = trim($_POST["reason"]);
    }

    if(empty(trim($_POST["owner"]))){ // Tilføj ejer tjek
        $owner_err = "Være venlig at indtaste ejeren af køretøjet";
    } else{
        $owner = trim($_POST["owner"]);
    }
    
    if(empty($plate_err) && empty($reason_err) && empty($owner_err)){ // Tilføj ejer_err
        $sql = "INSERT INTO population_vehicles (username, plate, reason, owner) VALUES (?, ?, ?, ?)"; // Tilføj ejer til SQL
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_plate, $param_reason, $param_owner); // Tilføj ejer til bind_param
            
            $username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];

            $param_username = $username;
            $param_plate = $plate;
            $param_reason = $reason;
            $param_owner = $owner; // Tilføj ejer parameter
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: wanted_vehicles.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($link);
}
?>

<main>
    <div class="wanted-wrapper">
        <div class="wanted-header" id="efterlysninger">
            <div class="wanted-text">
                <h2>Efterlysninger På Køretøjer</h2>
                <div class="wanted-button">
                    <button class="user-popup" data-toggle="modal" data-target="#wantedModal">Tilføj efterlysning</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="big-text-table" class="table table-striped table-responsive table-hover">
                    <tr>
                        <th>Oprettelse Tidspunkt</th>
                        <th>Badgenummer & navn</th>
                        <th>Nummerplade</th>
                        <th>Ejer</th> 
                        <th>Årsag</th>
                        <th>Status</th>
                        <th>Se mere</th> <!-- Tilføj Se mere kolonne -->
                        <?php if($_SESSION["websiteadmin"]) { ?>
                            <th>Handling</th>
                        <?php } ?>
                    </tr>
                        <?php 
                        $result = $link->query($sql); // Sørg for at $result er defineret korrekt
                        while($row = $result->fetch_assoc()) { 
                            $statusLabel = "Inaktiv";
                            if ($row["status"] == 1) $statusLabel = "Aktiv";
                        ?>
                        <tr>
                            <td><?php echo $row['dato'] ?></td>
                            <td><?php echo $row['username'] ?></td>
                            <td><?php echo $row['plate'] ?></td>
                            <td><?php echo $row['owner'] ?></td> <!-- Tilføj ejer til rækker -->
                            <td title="<?php echo str_replace('- ', '', $row['reason']) ?>"><?php echo str_replace('- ', '', $row['reason']) ?></td>
                            <td>
                                <a href="wanted_vehicles.php?action=toggle&id=<?php echo $row['id'] ?>&status=<?php echo $row['status'] ?>"><?php echo $statusLabel ?></a>
                            </td>
                            <td>
                                <button class="btn btn-secondary" data-toggle="modal" data-target="#detailsModal<?php echo $row['id']; ?>">Se mere</button>
                            </td>
                            <?php
                                if($_SESSION["websiteadmin"]) {
                                    echo '<td>';
                                        echo '<a href="wanted_vehicles.php?action=remove&id=' . $row["id"] . '&plate=' . $row["plate"] . '">Fjern efterlysning</a>';
                                    echo '</td>';
                                }
                            ?>
                        </tr>

                        <!-- Modal for Se mere -->
                        <div class="modal fade" id="detailsModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered custom-modal-size" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="detailsModalLabel<?php echo $row['id']; ?>">Detaljer for køretøj <?php echo $row['plate']; ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body text-white">
                                        <p><strong>Nummerplade:</strong> <?php echo $row['plate']; ?></p>
                                        <p><strong>Ejer:</strong> <?php echo $row['owner']; ?></p>
                                        <p><strong>Årsag:</strong> <?php echo htmlspecialchars($row['reason'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p><strong>Status:</strong> <?php echo $statusLabel; ?></p>
                                        <p><strong>Oprettelse Tidspunkt:</strong> <?php echo $row['dato']; ?></p>
                                        <p><strong>Badgenummer & navn:</strong> <?php echo $row['username']; ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </table>
            </div>

            <div class="modal fade" id="wantedModal" tabindex="-1" role="dialog" aria-labelledby="wantedModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                         <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Tilføj Efterlysning</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="form-group <?php echo (!empty($plate_err)) ? 'has-error' : ''; ?>">
                                    <label>Nummerpladen</label>
                                    <input type="text" name="plate" class="form-control" value="<?php echo $plate; ?>">
                                    <span class="help-block"><?php echo $plate_err; ?></span>
                                </div>
                                <div class="form-group <?php echo (!empty($owner_err)) ? 'has-error' : ''; ?>"> <!-- Tilføj ejer_err -->
                                    <label>Ejer</label>
                                    <input type="text" name="owner" class="form-control" value="<?php echo $owner; ?>"> <!-- Tilføj ejer input -->
                                    <span class="help-block"><?php echo $owner_err; ?></span> <!-- Tilføj ejer fejlmeddelelse -->
                                </div>
                                <div class="form-group <?php echo (!empty($reason_err)) ? 'has-error' : ''; ?>">
                                    <label>Årsag</label>
                                    <input type="text" name="reason" class="form-control" value="<?php echo $reason; ?>">
                                    <span class="help-block"><?php echo $reason_err; ?></span>
                                </div>
                                
                                <div class="form-group" id="submit">
                                    <input type="submit" class="btn btn-primary" value="Opret efterlysning">
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
    </div>
</main>

<?php
include '../footer.php';
?>

<style>
    .modal-body.text-white p {
        color: white;
    }
</style>
