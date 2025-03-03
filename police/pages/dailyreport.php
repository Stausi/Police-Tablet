<?php
include '../header.php';

// Sikkerhed for at forhindre advokater og dommere at tilgå denne side.
if($_SESSION["afdeling"] == "Advokatledelse" || $_SESSION["afdeling"] == "Dommer") {
    header("location: /police/pages/employed.php");
    exit;
}

$sql = "SELECT * FROM dailyreport ORDER BY dato DESC";
$result = $link->query($sql);

$title = $kommentar = "";
$title_err = $kommentar_err = "";

if (isset($_GET['action'])) {
    $id = $_GET['id'];

    if ($_GET['action'] == 'toggle') {
        $sql = "UPDATE dailyreport SET status = NOT status, updated_by = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $_SESSION['username'], $id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: dailyreport.php");
                exit;
            }
        }
    } elseif ($_GET['action'] == 'remove') {
        $sql = "DELETE FROM dailyreport WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                header("location: dailyreport.php");
                exit;
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["title"]))) {
        $title_err = "Indtast venligst en titel";
    } else {
        $title = trim($_POST["title"]);
    }

    if (empty(trim($_POST["kommentar"]))) {
        $kommentar_err = "Indtast venligst en kommentar";
    } else {
        $kommentar = trim($_POST["kommentar"]);
    }

    if (empty($title_err) && empty($kommentar_err)) {
        $sql = "INSERT INTO dailyreport (username, titel, kommentar, status, updated_by) VALUES (?, ?, ?, 1, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $_SESSION["username"], $title, $kommentar, $_SESSION["username"]);
            if (mysqli_stmt_execute($stmt)) {
                header("location: dailyreport.php");
            } else {
                echo "Something went wrong. Please try again later.<br>";
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
        <div class="daily-header" id="dailyreport">
            <div class="daily-text">
                <h2>Opslagstavle</h2>
            </div>
            <div class="daily-text daily-button">
                <button class="" data-toggle="modal" data-target="#dailyModal">Tilføj rapport</button>
            </div>
        </div>

        <div class="reports-grid">
            <?php while ($row = $result->fetch_assoc()) {
                    $words = explode(' ', $row['kommentar']);
                    $short_comment = implode(' ', array_slice($words, 0, 20));
                    if (count($words) > 20) {
                        $short_comment .= '...';
                    }
                    $statusLabel = $row["status"] == 1 ? "Aktiv" : "Inaktiv";
                    $updatedBy = ($row["status"] == 0 && !empty($row["updated_by"])) ? " (Opdateret af: " . htmlspecialchars($row["updated_by"], ENT_QUOTES, 'UTF-8') . ")" : "";
                
                ?>
                <div class="report-box" style="position: relative;">
                <?php if ($_SESSION["websiteadmin"]) { ?>
                    <div style="position: absolute; top: 5px; right: 5px; cursor: pointer;">
                        <a href="javascript:void(0);" onclick="confirmRemoval(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['titel'], ENT_QUOTES, 'UTF-8'); ?>')">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 6L6 18" stroke="#FF0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 6L18 18" stroke="#FF0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                    <?php } ?>
                    <h3><?php echo htmlspecialchars($row['titel'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><strong>Dato:</strong> <em><?php echo date('d-m-Y H:i', strtotime($row['dato'])); ?></em></p>
                    <p><strong>Betjent:</strong> <?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p title="<?php echo htmlspecialchars($row['kommentar'], ENT_QUOTES, 'UTF-8'); ?>"><strong>Rapport:</strong> <?php echo $short_comment; ?></p>
                    <p><strong>Status:</strong> <a href="dailyreport.php?action=toggle&id=<?php echo $row['id']; ?>&status=<?php echo $row['status']; ?>"
                        style="color: <?php echo $row['status'] == 1 ? '#28a745' : '#dc3545'; ?>">
                        <?php echo $statusLabel . $updatedBy; ?></a></p>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#detailsModal<?php echo $row['id']; ?>">Se mere</button>
                    
                    <div class="modal fade" id="detailsModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailsModalLabel<?php echo $row['id']; ?>">Detaljer for <?php echo htmlspecialchars($row['titel'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Rapport:</strong> <?php echo htmlspecialchars($row['kommentar'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="modal fade" id="dailyModal" tabindex="-1" role="dialog" aria-labelledby="dailyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Tilføj Rapport</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>


                <div class="modal-body">
                    <form action="<?php echo ($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group <?php echo (!empty($title_err)) ? 'has-error' : ''; ?>">
                            <label>Titel</label>
                            <input type="text" name="title" class="form-control" value="<?php echo $title; ?>">
                            <span class="help-block">
                                <?php echo $title_err; ?>
                            </span>
                        </div>
                        <div class="form-group <?php echo (!empty($kommentar_err)) ? 'has-error' : ''; ?>">
                            <label for="kommentar">Kommentar</label>
                            <textarea id="kommentar" name="kommentar" class="form-control"
                                rows="4"><?php echo ($kommentar); ?></textarea>
                            <span class="help-block">
                                <?php echo $kommentar_err; ?>
                            </span>
                        </div>
                        <div class="form-group" id="submit">
                            <input type="submit" class="btn btn-primary" value="Opret rapport">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
function confirmRemoval(id, title) {
    Swal.fire({
        title: "Er du sikker på du vil fjerne rapporten '" + title + "'?",
        text: 'Du kan ikke fortryde denne handling.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ja, fjern den!',
        cancelButtonText: 'Nej, annuller!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "dailyreport.php?action=remove&id=" + id;
        }
    });
}
</script>

<?php
include '../footer.php';
?>