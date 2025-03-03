<?php
include '../header.php';

$type = null;
$player = null;

if (isset($_GET['type'])) {
    $type = $_GET['type'];
}

if (isset($_GET['player'])) {
    $player = $_GET['player'];
}

setlocale(LC_TIME, 'da_DK.utf8');
$arrival = strftime('%d-%m-%Y %H:%M', time());

$damage_report = $medicin_given = $damage_assessment = "";
$reason = $epikrise = $conversation = $medicin_treatment = $psykolog_assessment = "";

$sql = "SELECT * FROM population_ems WHERE id='" . $player . "'";
$result = $link->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $journalType = sanitize_input($_POST['journalType']);
    $playerId = sanitize_input($_POST['playerid']);

    if ($journalType == "normal") {
        $formData = [
            'arrival' => sanitize_input($_POST['arrival'] ?? null),
            'damage_report' => sanitize_input($_POST['damage_report'] ?? null),
            'medicin_given' => sanitize_input($_POST['medicin_given'] ?? null),
            'damage_assessment' => sanitize_input($_POST['damage_assessment'] ?? null),
            'treatment_before_arrival' => getAllSetInputsAsString('treatment_before_arrival_', 6),
            'condition_at_arrival_resp' => getFirstSetInput('condition_at_arrival_resp_', 6),
            'condition_at_arrival_cirk' => getFirstSetInput('condition_at_arrival_cirk_', 6),
            'condition_at_arrival_bleed' => getFirstSetInput('condition_at_arrival_bleed_', 6),
            'condition_at_arrival_pain' => getFirstSetInput('condition_at_arrival_pain_', 6),
            'follow_up_treatment' => getFirstSetInput('follow_up_treatment_', 6),
            'recept_given' => getFirstSetInput('recept_given_', 6)
        ];

        $nonNullFields = array_filter($formData, function ($value) { return $value !== null; });

        if (count($nonNullFields) === count($formData)) {
            $username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];
            $columns = implode(", ", array_keys($nonNullFields));
            $placeholders = implode(", ", array_fill(0, count($nonNullFields), "?"));
            $sql = "INSERT INTO population_journals (pid, userid, username, $columns) VALUES (?, ?, ?, $placeholders)";

            $stmt = mysqli_prepare($link, $sql);
            if ($stmt) {
                $types = str_repeat("s", count($nonNullFields) + 3);
                $values = array_merge([$playerId, $_SESSION["id"], $username], array_values($nonNullFields));
                mysqli_stmt_bind_param($stmt, $types, ...$values);

                if (mysqli_stmt_execute($stmt)) {
                    header("location: player.php?player=" . $playerId);
                    exit;
                } else {
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    if ($journalType == "psykolog") {
        $formData = [
            'reason' => sanitize_input($_POST['reason'] ?? null),
            'epikrise' => sanitize_input($_POST['epikrise'] ?? null),
            'conversation' => sanitize_input($_POST['conversation'] ?? null),
            'medicin_treatment' => sanitize_input($_POST['medicin_treatment'] ?? null),
            'psykolog_assessment' => sanitize_input($_POST['psykolog_assessment'] ?? null),
        ];

        $nonNullFields = array_filter($formData, function ($value) { return $value !== null; });

        if (count($nonNullFields) === count($formData)) {
            $username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];
            $columns = implode(", ", array_keys($nonNullFields));
            $placeholders = implode(", ", array_fill(0, count($nonNullFields), "?"));
            $sql = "INSERT INTO population_psykjournals (pid, userid, username, $columns) VALUES (?, ?, ?, $placeholders)";

            $stmt = mysqli_prepare($link, $sql);
            if ($stmt) {
                $types = str_repeat("s", count($nonNullFields) + 3);
                $values = array_merge([$playerId, $_SESSION["id"], $username], array_values($nonNullFields));
                mysqli_stmt_bind_param($stmt, $types, ...$values);

                if (mysqli_stmt_execute($stmt)) {
                    header("location: playerpsyk.php?player=" . $playerId);
                    exit;
                } else {
                    echo "Something went wrong. Please try again later. <br>";
                    printf("Error message: %s\n", $link->error);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

function getFirstSetInput($baseName, $count) {
    for ($i = 1; $i <= $count; $i++) {
        if (isset($_POST["$baseName$i"])) {
            return sanitize_input($_POST["$baseName$i"]);
        }
    }
    return null;
}

function getAllSetInputsAsString($baseName, $count) {
    $inputs = [];
    for ($i = 1; $i <= $count; $i++) {
        if (isset($_POST["$baseName$i"])) {
            $inputs[] = sanitize_input($_POST["$baseName$i"]);
        }
    }
    return implode(', ', $inputs);
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<main>
    <div class="journals-wrapper">
        <div class="journals-back-container">
            <a href="player.php?player=<?php echo $player; ?>"><i class="fa-solid fa-arrow-left"></i> Gå tilbage</a>
        </div>
        <div class="journals-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <?php if ($type == "normal") { ?>
                    <input type="hidden" name="journalType" value="<?php echo $type; ?>">
                    <input type="hidden" name="playerid" value="<?php echo $player; ?>">

                    <div class="form-groups">
                        <div class="form-group">
                            <h3>Ankomsttid</h3>
                            <input type="text" name="arrival" value="<?php echo $arrival; ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <h3>Skadesmelding</h3>
                            <input type="text" name="damage_report" value="<?php echo $damage_report; ?>" class="form-control">
                        </div>
                    </div>
                    <h3>Behandling før ankomst</h3> 
                    <div class="form-checkboxes multi-select">
                        <div class="form-checkbox">
                            <input type="checkbox" name="treatment_before_arrival_1" class="form-control" /><span>Ingen</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="treatment_before_arrival_2" class="form-control" /><span>Uoplyst</span>
                        </div>
                        <div class="form-checkbox">
                        <input type="checkbox" name="treatment_before_arrival_3" class="form-control" /><span>Fri luftveje</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="treatment_before_arrival_4" class="form-control" /><span>Ilt behandling</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="treatment_before_arrival_5" class="form-control" /><span>HLR</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="treatment_before_arrival_6" class="form-control" /><span>Kompresforbinding</span>
                        </div>
                    </div>
                    <h3>Tilstand ved ankomst</h3>
                    <p class="sub-header">Respiration</p>
                    <div class="form-checkboxes">
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_resp_1" class="form-control" /><span>Upåvirket</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_resp_2" class="form-control" /><span>Let påvirket</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_resp_3" class="form-control" /><span>Meget påvirket</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_resp_4" class="form-control" /><span>Resp. Stop.</span>
                        </div>
                    </div>
                    <p class="sub-header">Cirkulation</p>
                    <div class="form-checkboxes">
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_cirk_1" class="form-control" /><span>Upåvirket</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_cirk_2" class="form-control" /><span>Let påvirket</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_cirk_3" class="form-control" /><span>Meget påvirket</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_cirk_4" class="form-control" /><span>Hjertestop</span>
                        </div>
                    </div>
                    <p class="sub-header">Blødning</p>
                    <div class="form-checkboxes">
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_bleed_1" class="form-control" /><span>Ingen</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_bleed_2" class="form-control" /><span>Mindre ydre</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_bleed_3" class="form-control" /><span>Betydelig ydre</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_bleed_4" class="form-control" /><span>Obstruktiv indre</span>
                        </div>
                    </div>
                    <p class="sub-header">Smerter</p>
                    <div class="form-checkboxes">
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_pain_1" class="form-control" /><span>Ingen</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_pain_2" class="form-control" /><span>Lette</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_pain_3" class="form-control" /><span>Middel</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="condition_at_arrival_pain_4" class="form-control" /><span>Stærke</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <h3>Skadesvurdering</h3>
                        <textarea name="damage_assessment" value="<?php echo $damage_assessment; ?>" class="form-control"></textarea>
                    </div>
                    <h3>Opfølgning af behandling</h3>
                    <div class="form-checkboxes">
                        <div class="form-checkbox">
                            <input type="checkbox" name="follow_up_treatment_1" class="form-control" /><span>Ja</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="follow_up_treatment_2" class="form-control" /><span>Nej</span>
                        </div>
                    </div>
                    <h3>Recept Givet</h3>
                    <div class="form-checkboxes">
                        <div class="form-checkbox">
                            <input type="checkbox" name="recept_given_1" class="form-control" /><span>Nej</span>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="recept_given_2" class="form-control" /><span>Ja, alm håndkøbsmedicin, smertestillende, panodil, ibuprofen</span>
                        </div>
                        <div class="form-group">
                            <input type="text" name="recept_given_3" class="form-control" placeholder="Andet medicin">
                        </div>
                    </div>
                    <div class="form-group">
                        <h3>Medicin givet</h3>
                        <input type="text" name="medicin_given" class="form-control" value="<?php echo $medicin_given; ?>">
                    </div>
                    <div class="form-group" id="submit">
                        <input type="submit" class="btn btn-primary" value="Opret Journal">
                    </div>
                <?php } ?>
                <?php if ($type == "psykolog") { ?>
                    <input type="hidden" name="journalType" value="<?php echo $type; ?>">
                    <input type="hidden" name="playerid" value="<?php echo $player; ?>">

                    <div class="form-group">
                        <h3>Årsag</h3>
                        <textarea name="reason" value="<?php echo $reason; ?>" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <h3>Epikrise</h3>
                        <textarea name="epikrise" value="<?php echo $epikrise; ?>" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <h3>Samtale</h3>
                        <textarea name="conversation" value="<?php echo $conversation; ?>" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <h3>Medicinsk behandling</h3>
                        <textarea name="medicin_treatment" value="<?php echo $medicin_treatment; ?>" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <h3>Vurdering</h3>
                        <textarea name="psykolog_assessment" value="<?php echo $psykolog_assessment; ?>" class="form-control"></textarea>
                    </div>
                    <div class="form-group" id="submit">
                        <input type="submit" class="btn btn-primary" value="Opret til Psykolog Arktiv">
                    </div>
                <?php } ?>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('.form-checkboxes').forEach(group => {
                const isMultiSelect = group.classList.contains('multi-select');
                const checkboxes = group.querySelectorAll('input[type=checkbox].form-control');
                let isFirstCheckboxChecked = false;

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', (e) => {
                        if (!isMultiSelect) {
                            if (checkbox.checked) {
                                checkboxes.forEach(box => {
                                    if (box !== checkbox) box.checked = false;
                                });
                            }
                        }
                    });

                    if (!isMultiSelect && !isFirstCheckboxChecked && !checkbox.checked) {
                        checkbox.checked = true;
                        isFirstCheckboxChecked = true;
                    }
                });

                if (!isMultiSelect && !isFirstCheckboxChecked && checkboxes.length > 0) {
                    checkboxes[0].checked = true;
                }
            });

            document.querySelectorAll('.form-checkboxes .form-checkbox').forEach(function(checkboxDiv) {
                var spanText = checkboxDiv.querySelector('span').textContent;
                var checkbox = checkboxDiv.querySelector('input[type="checkbox"]');
                checkbox.value = spanText;
            });
        });
    </script>
</main>

<?php
include '../footer.php';
?>