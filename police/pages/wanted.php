<?php
include '../header.php';

// Sikkerhed for at forhindre advokater og dommere at tilgå denne side.
// Men hvis man har id 4 må man godt komme ind selvom man er dommer
if ($_SESSION["afdeling"] == "Advokatledelse" || $_SESSION["afdeling"] == "Dommer" && $_SESSION["username"] != 199) {
    header("location: /police/pages/employed.php");
    exit;
}

$sql = "SELECT * FROM population_wanted ORDER BY dato DESC";
$result = $link->query($sql);

function sendDiscordWebhook($title, $description, $footer, $timestamp)
{
    $webhookurl = "https://discord.com/api/webhooks/1227749379507486730/jJxu8kUUgP4zmW1ErRnjLycfyN83RRa3mh1slJZzUGmxwAKYiNNdbhvBcmV3w4_iHJVL";

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

    $ch = curl_init($webhookurl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);
}

if (isset($_GET['action'])) {
    $id = $_GET['id'];

    $sql = "SELECT name FROM population WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $id;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $target_name);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Could not prepare statement to fetch name.<br>";
        printf("Error message: %s\n", $link->error);
    }

    if ($_GET['action'] == 'toggle') {
        $sql = "UPDATE population_wanted SET status = ?, updated_by = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssi", $param_status, $param_updated_by, $param_id);

            $status = $_GET['status'];
            $status = ($status == 1) ? 0 : 1;
            $param_status = $status;

            $param_updated_by = $_SESSION['username'];
            $param_id = $id;

            if (mysqli_stmt_execute($stmt)) {
                header("location: wanted.php");
                exit;
            } else {
                echo "Something went wrong. Please try again later.<br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    } elseif ($_GET['action'] == 'remove') {
        $sql = "SELECT p.name, w.sigtet, w.reason, w.username FROM population p JOIN population_wanted w ON p.id = w.target_id WHERE w.id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id;
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $target_name, $sigtelser, $reason, $username);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            $sql = "DELETE FROM population_wanted WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $param_id);
                $param_id = $id;

                if (mysqli_stmt_execute($stmt)) {
                    $user = $_SESSION['username'];
                    $title = "Efterlysning fjernet";
                    $description = "Bruger **{$user}** har fjernet efterlysningen på **{$target_name}**.\n\n**Sigtet for:**\n{$sigtelser}\n\n**Årsag:**\n{$reason}";
                    $footer = "Handling udført";
                    $timestamp = date("c");

                    sendDiscordWebhook($title, $description, $footer, $timestamp);

                    header("location: wanted.php");
                    exit;
                } else {
                    echo "Something went wrong. Please try again later.<br>";
                    printf("Error message: %s\n", $link->error);
                }
            }
        } else {
            echo "Could not prepare statement to fetch name, charges, reason, and username.<br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}
?>


<main>
    <div class="wanted-wrapper">
        <div class="wanted-header" id="efterlysninger">
            <div class="wanted-text">
                <h2>Efterlysninger</h2>
            </div>
        </div>

        <div class="wanted-grid">
            <?php while ($row = $result->fetch_assoc()) {

                $sigtelser = explode('- ', $row['sigtet']); // Antager hver sigtelse starter med "- "
                $max_sigtelser = 4;
                if (count($sigtelser) > $max_sigtelser) {
                    // Samler de første tre sigtelser og tilføjer '...'
                    $vis_sigtelser = implode('- ', array_slice($sigtelser, 0, $max_sigtelser)) . '...';
                } else {
                    // Ingen behov for at ændre noget, hvis der er 3 eller færre sigtelser
                    $vis_sigtelser = implode('- ', $sigtelser);
                }

                $sql_player = "SELECT * FROM population WHERE id='" . $row['target_id'] . "'";
                $result_player = $link->query($sql_player);
                $target_name = "";
                $image_path = "";

                while ($row_player = mysqli_fetch_array($result_player)) {
                    $target_name = $row_player['name'];

                    $image_path = "../../assets/playersIMG/" . $row['target_id'] . ".png";

                    // standard billede vises hvis ikke billedet findes
                    if (!file_exists($image_path)) {
                        $image_path = "../../assets/profilesIMG/unknown.png";
                    }
                }
                $escaped_name = addslashes($target_name); // Escape navnet med PHP
            

                $statusLabel = $row["status"] == 1 ? "Aktiv" : "Inaktiv";
                $updatedBy = !empty($row["updated_by"]) ? htmlspecialchars($row["updated_by"], ENT_QUOTES, 'UTF-8') : "N/A";




                ?>

                <div class="wanted-box">
                    <div class="wanted-img">
                        <a href="/police/pages/player.php?player=<?php echo $row['target_id']; ?>">

                            <img src="<?php echo $image_path; ?>"
                                alt="<?php echo htmlspecialchars($target_name, ENT_QUOTES, 'UTF-8'); ?>"
                                style="width: 120px; max-height: 210px;">
                        </a>
                    </div>

                    <div class="wanted-info">
                        <div class="wanted-content">
                            <h3><?php echo $target_name; ?></h3>
                            <p><strong>Dato:</strong> <em><?php echo $row['dato']; ?></em></p>
                            <p><strong>Betjent:</strong> <?php echo $row['username']; ?></p>

                            <p title="<?php echo htmlspecialchars($row['sigtet'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="short-text">
                                <strong>Sigtet for:</strong>
                                <?php echo htmlspecialchars($vis_sigtelser, ENT_QUOTES, 'UTF-8'); ?>
                            </p>


                            <p><strong>Status:</strong> <a
                                    href="wanted.php?action=toggle&id=<?php echo $row['id']; ?>&status=<?php echo $row['status']; ?>"
                                    style="color: <?php echo $row['status'] == 1 ? '#28a745' : '#dc3545'; ?>">
                                    <?php echo $statusLabel; ?>
                                    <?php if ($row["status"] == 0) { ?>
                                        (Opdateret af: <?php echo $updatedBy; ?>)
                                    <?php } ?>
                                </a></p>
                        </div>


                        <div style="position: absolute; top: 10px; right: 10px;">
                            <?php if ($_SESSION["websiteadmin"]) { ?>
                                <a href="javascript:void(0);"
                                    onclick="confirmRemoval(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($target_name), ENT_QUOTES, 'UTF-8'); ?>')"
                                    title="Fjern efterlysning" style="margin-right: 10px;">
                                    <i class="fas fa-times"></i>
                                </a>


                            <?php } ?>
                            <a href="#detailsModal<?php echo $row['id']; ?>" data-toggle="modal" title="Se mere">
                                <i class="far fa-eye"></i>
                            </a>
                        </div>



                        <div class="modal fade" id="detailsModal<?php echo $row['id']; ?>" tabindex="-1"
                            aria-labelledby="detailsModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered custom-modal-size" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="detailsModalLabel<?php echo $row['id']; ?>">Detaljer for
                                            <?php echo $target_name; ?>
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p style="margin-bottom: 60px;"><strong>Sigtet for:</strong>
                                            <?php echo htmlspecialchars($row['sigtet'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p style="margin-bottom: 60px;"><strong>Årsag:</strong>
                                            <?php echo htmlspecialchars($row['reason'], ENT_QUOTES, 'UTF-8'); ?></p>

                                        <table class="table" style="margin-top: 40px;">
                                            <thead>
                                                <tr>
                                                    <th>Bøde</th>
                                                    <th>Fængsel</th>
                                                    <th>Klip</th>
                                                    <th>Frakendelse</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['ticket'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $prisonMonths = htmlspecialchars($row['prison'], ENT_QUOTES, 'UTF-8');
                                                        echo $prisonMonths > 0 ? $prisonMonths . ' måneder' : 'Ingen'; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['klip'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['frakendelse'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- sweetalert -->

                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                        <script type="text/javascript">
                            function escapeHtml(unsafe) {
                                return unsafe
                                    .replace(/&/g, "&amp;")
                                    .replace(/</g, "&lt;")
                                    .replace(/>/g, "&gt;")
                                    .replace(/"/g, "&quot;")
                                    .replace(/'/g, "&#039;")
                                    .replace(/\\/g, '\\\\'); // Escape backslashes
                            }

                            function confirmRemoval(id, name) {
                                const safeName = escapeHtml(name);
                                Swal.fire({
                                    title: 'Er du sikker på du vil fjerne efterlysningen på ' + safeName + '?',
                                    text: 'Du kan ikke fortryde denne handling.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Ja, fjern den!',
                                    cancelButtonText: 'Nej, annuller!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "wanted.php?action=remove&id=" + id;
                                    }
                                });
                            }
                        </script>


                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</main>




<?php
include '../footer.php';
?>