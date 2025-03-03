<?php
include '../header.php';
require ('../../q3query.class.php');

$type = $_GET['type'];
$player = $_GET['player'];

$erkender = isset($_GET['erkender']) ? $_GET['erkender'] : 0;

$conditional = isset($_GET['conditional']) ? $_GET['conditional'] : 0;

$sql = "SELECT * FROM population WHERE id='" . $player . "'";
$result = $link->query($sql);

$firstname = $dob = "";
$phone_number = 0;

while ($row = mysqli_fetch_array($result)) {
    $name = $row['name'];
    $dob = $row['dob'];
    $phone_number = $row['phone_number'];
}

$addToMessage = "";
if ($type == "krimi") {
    $addToMessage = "Tilføj til KR";
} elseif ($type == "efter") {
    $addToMessage = "Tilføj efterlysning";
} elseif ($type == "edit") {
    $addToMessage = "Godkend Redigering";
}

if (isset($_GET['cases']) && isset($_GET['klip']) && isset($_GET['ticket']) && isset($_GET['prison']) && isset($_GET['status']) && isset($_GET['comment'])) {

    if ($type == "krimi") {
        $cases = json_decode($_GET['cases'], true);

        $prison = $_GET['prison'];
        $ticket = $_GET['ticket'];
        $klip = $_GET['klip'];

        $status = $_GET['status'];
        $comment = $_GET['comment'];


        $player_id = $player;
        $username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];

        $sigtet = "";
        $sigtet_rcon = "";

        foreach ($cases as $key => $value) {
            if ($key == -1 || $key == -2 || $key == -3 || $key == -4 || $key == -5 || $key == -6 || $key == -7) {
                if ($key == -1) {
                    $zone = $_GET['zone'] ?? "Ukendt";
                    $speed = $_GET['speed'] ?? "Ukendt";
                    $sigtet .= " - Fartbøde for at køre " . $speed . " km/t i en " . $zone . "zone";
                }
                if ($key == -2) {

                    $sigtet = $sigtet .= " - Bøde nedsættelse";
                }
                if ($key == -3) {
                    $sigtet = $sigtet .= " - Straf nedsættelse";
                }
                if ($key == -4) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Bil kørekort";
                }
                if ($key == -5) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Motorcykel kørekort";
                }
                if ($key == -6) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Lastbil kørekort";
                }
                if ($key == -7) {
                    $sigtet = $sigtet .= " - Ubetinget frakendelse af kørekort";
                }
            } else {
                $sql = "SELECT * FROM tickets WHERE id = " . $key;
                $result = $link->query($sql);

                if (empty($value['customValues'])) {
                    $count = $value['count'];
                } else {
                    $count = 0;
                }

                foreach ($value['customValues'] as $customValue) {
                    $count += $customValue;
                }

                while ($row = $result->fetch_assoc()) {
                    if ($count <= 1) {
                        $sigtet .= " - " . $row['sigtelse'];
                        $sigtet_rcon .= " - " . $row['sigtelse'] . "<br>";
                    } else {
                        $sigtet .= " - x" . $count . " " . $row['sigtelse'];
                        $sigtet_rcon .= " - x" . $count . " " . $row['sigtelse'] . "<br>";
                    }
                }
            }
        }

        $sql = "INSERT INTO population_cases (pid, userid, username, sigtet, ticket, prison, klip, status, comment, cases, erkender, conditional) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssssii", $param_playerid, $param_userid, $param_username, $param_sigtet, $param_ticket, $param_prison, $param_klip, $param_status, $param_comment, $param_cases, $param_erkender, $param_conditional);

            // Tildel værdier til dine parametre her
            $param_playerid = $player_id;
            $param_userid = $_SESSION["id"];
            $param_username = $username;
            $param_sigtet = $sigtet;
            $param_ticket = $ticket;
            $param_prison = $prison;
            $param_klip = $klip;
            $param_status = $status;
            $param_comment = $comment;
            $param_cases = json_encode($cases);
            $param_erkender = $erkender;
            $param_conditional = $conditional; // Antag at denne variabel er korrekt tildelt baseret på brugerinput

            if (mysqli_stmt_execute($stmt)) {
                header("location: player.php?player=" . $player);

                if (RCON_ENABLED == true) {
                    $success = false;
                    $con = new q3query(RCON_ADDRESS, RCON_PORT, $success);

                    if ($success) {
                        $last_id = mysqli_insert_id($link);

                        $rcon_string = "<h2>Ny bøde fra Politiet</h2><br><p>Journal nummer: " . $last_id . "</p><br><br><p>Sigtet for:</p>";
                        $rcon_string .= $sigtet_rcon;
                        $rcon_string .= "<br>";

                        if ($param_ticket > 0)
                            $rcon_string .= "<p>Samlet bøde på: " . number_format($param_ticket, 0, ",", ".") . ",- DKK</p>";
                        if ($param_klip > 0)
                            $rcon_string .= "<p>Samlet antal klip: " . $param_klip . "</p>";
                        if ($param_prison > 0)
                            $rcon_string .= "<p>Samlet fængselstid på: " . $param_prison . " måneder</p>";

                        $rcon_string .= "<br><p>Mvh.</p><p>Politiet</p>";

                        $data = array(
                            'id' => $last_id,
                            'ticket' => $param_ticket,
                            'prison' => $param_prison,
                            'klip' => $param_klip,
                            'string' => $rcon_string,
                            'job' => 'police',
                        );

                        $jsonString = json_encode($data);
                        // $con->setRconpassword(RCON_PASSWORD);
                        // $con->rcon("rconadddbokspost " . $phone_number . " " . $jsonString);
                    }
                }
            } else {
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    } elseif ($type == "edit" && isset($_GET['case'])) {
        $case_id = $_GET['case'];
        $cases = json_decode($_GET['cases'], true);

        $prison = $_GET['prison'];
        $ticket = $_GET['ticket'];
        $klip = $_GET['klip'];

        $status = $_GET['status'];
        $comment = $_GET['comment'];

        $player_id = $player;
        $username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];
        $sigtet = "";

        foreach ($cases as $key => $value) {
            if ($key == -1 || $key == -2 || $key == -3 || $key == -4 || $key == -5 || $key == -6 || $key == -7) {
                if ($key == -1) {
                    $zone = $_GET['zone'] ?? "Ukendt";
                    $speed = $_GET['speed'] ?? "Ukendt";
                    $sigtet .= " - Fartbøde for at køre " . $speed . " km/t i en " . $zone . "zone";
                }
                if ($key == -2) {

                    $sigtet = $sigtet .= " - Bøde nedsættelse - " . $ticket . " DKK";
                }
                if ($key == -3) {
                    $sigtet = $sigtet .= " - Straf nedsættelse";
                }
                if ($key == -4) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Bil kørekort";
                }
                if ($key == -5) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Motorcykel kørekort";
                }
                if ($key == -6) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Lastbil kørekort";
                }
                if ($key == -7) {
                    $sigtet = $sigtet .= " - Ubetinget frakendelse af kørekort";
                }
            } else {
                $sql = "SELECT * FROM tickets WHERE id = " . $key;
                $result = $link->query($sql);

                if (empty($value['customValues'])) {
                    $count = $value['count'];
                } else {
                    $count = 0;
                }

                foreach ($value['customValues'] as $customValue) {
                    $count += $customValue;
                }

                while ($row = $result->fetch_assoc()) {
                    if ($count <= 1) {
                        $sigtet .= " - " . $row['sigtelse'];
                    } else {
                        $sigtet .= " - x" . $count . " " . $row['sigtelse'];
                    }
                }
            }
        }

        $sql = "UPDATE population_cases SET pid=?, userid=?, username=?, sigtet=?, ticket=?, prison=?, klip=?, status=?, comment=?, cases=?, erkender=?, conditional=? WHERE id=?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssssiii", $param_playerid, $param_userid, $param_username, $param_sigtet, $param_ticket, $param_prison, $param_klip, $param_status, $param_comment, $param_cases, $param_erkender, $param_conditional, $case_id);

            // Tildel værdier til dine parametre her
            $param_playerid = $player_id;
            $param_userid = $_SESSION["id"];
            $param_username = $username;
            $param_sigtet = $sigtet;
            $param_ticket = $ticket;
            $param_prison = $prison;
            $param_klip = $klip;
            $param_status = $status;
            $param_comment = $comment;
            $param_cases = json_encode($cases);
            $param_erkender = $erkender;
            $param_conditional = $conditional; // Antag at denne variabel er korrekt tildelt baseret på brugerinput

            if (mysqli_stmt_execute($stmt)) {
                header("location: player.php?player=" . $player);
            } else {
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }

    } elseif ($type == "efter") {
        $target_id = $player;
        $username = $_SESSION["username"] . ' - ' . $_SESSION["firstname"] . ' ' . $_SESSION["lastname"];

        $reason = "x";
        if (isset($_GET['comment'])) {
            $reason = $_GET['comment'];
        }

        $sigtet = "";
        $cases = json_decode($_GET['cases'], true);

        foreach ($cases as $key => $value) {
            if ($key == -1 || $key == -2 || $key == -3 || $key == -4 || $key == -5 || $key == -6 || $key == -7) {
                if ($key == -1) {
                    $zone = $_GET['zone'] ?? "Ukendt";
                    $speed = $_GET['speed'] ?? "Ukendt";
                    $sigtet .= " - Fartbøde for at køre " . $speed . " km/t i en " . $zone . "zone";
                }
                if ($key == -2) {
                    $sigtet = $sigtet .= " - Bøde nedsættelse";
                }
                if ($key == -3) {
                    $sigtet = $sigtet .= " - Straf nedsættelse";
                }
                if ($key == -4) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Bil kørekort";
                }
                if ($key == -5) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Motorcykel kørekort";
                }
                if ($key == -6) {
                    $sigtet = $sigtet .= " - Betinget frakendelse af Lastbil kørekort";
                }
                if ($key == -7) {
                    $sigtet = $sigtet .= " - Ubetinget frakendelse af kørekort";
                }
            } else {
                $sql = "SELECT * FROM tickets WHERE id = " . $key;
                $result = $link->query($sql);

                if (empty($value['customValues'])) {
                    $count = $value['count'];
                } else {
                    $count = 0;
                }

                foreach ($value['customValues'] as $customValue) {
                    $count += $customValue;
                }

                while ($row = $result->fetch_assoc()) {
                    if ($count <= 1) {
                        $sigtet .= " - " . $row['sigtelse'];
                    } else {
                        $sigtet .= " - x" . $count . " " . $row['sigtelse'];
                    }
                }
            }
        }

        $prison = $_GET['prison'];
        $ticket = $_GET['ticket'];
        $frakendelse = $_GET['status'];
        $klip = $_GET['klip'] ?? 0;

        $sql = "INSERT INTO population_wanted (username, target_id, sigtet, reason, ticket, prison, frakendelse, klip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sissiisi", $param_username, $param_target_id, $param_sigtet, $param_reason, $param_ticket, $param_prison, $param_frakendelse, $param_klip);

            $param_username = $username;
            $param_target_id = $target_id;
            $param_sigtet = $sigtet;
            $param_reason = $reason;
            $param_ticket = $ticket;
            $param_prison = $prison;
            $param_frakendelse = $frakendelse;
            $param_klip = $klip;


            if (mysqli_stmt_execute($stmt)) {
                header("location: wanted.php");
            } else {
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    }
}
?>

<main>
    <div class="tickes-wrapper">
        <div class="search-text">
            <h2 align="center">Straf søgning</h2>
        </div>
        <div class="krimi-search">
            <input autocomplete="off" type="text" name="search_text" id="search_text"
                placeholder="Indtast beskrivelse af bøden" class="form-control" />
        </div>
        <div id="result"></div>
        <div class="tickets-emner" style="margin-top: 2em;">
            <div class="tickets-header" id="Misc">
                <h2>Miscellaneous</h2>
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Sigtelse</th>
                        <th>Information</th>
                        <th>Tilføj</th>
                    </tr>
                    <tr>
                        <td>Fartbøde</td>
                        <td>Angiv Fartbøde</td>
                        <td><button class="addkrim-button" onclick="addMisc(-1)" title="Tilføj til straf"><i
                                    class="far fa-plus-square addkrim-button-text"></i></button></td>
                    </tr>
                    <tr>
                        <td>Formindsk Bøde</td>
                        <td>Nedsæt bøden</td>
                        <td><button class="addkrim-button" onclick="addMisc(-2)" title="Tilføj til straf"><i
                                    class="far fa-plus-square addkrim-button-text"></i></button></td>
                    </tr>
                    <tr>
                        <td>Formindsk Fængselsstraf</td>
                        <td>Nedsæt fængselsstraffen</td>
                        <td><button class="addkrim-button" onclick="addMisc(-3)" title="Tilføj til straf"><i
                                    class="far fa-plus-square addkrim-button-text"></i></button></td>
                    </tr>
                    <tr>
                        <td>Tilføj frakendelse</td>
                        <td>Tilføj en betinget eller ubetinget dom</td>
                        <td><button class="addkrim-button" onclick="addStatus()" title="Tilføj til straf"><i
                                    class="far fa-plus-square addkrim-button-text"></i></button></td>
                    </tr>
                    <tr>
                        <td>Kommentar</td>
                        <td>Tilføj en kommentar til en sag</td>
                        <td><button class="addkrim-button" onclick="addComment()" title="Tilføj kommentar"><i
                                    class="far fa-plus-square addkrim-button-text"></i></button></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="addedContent">
    <div class="content-wrapper">
        <div class="prison">
            <h3 class="title">Fængsel:</h3>
            <h3 id="prison">0</h3>
        </div>
        <div class="ticket">
            <h3 class="title">Bøde:</h3>
            <h3 id="ticket">0</h3>
        </div>
        <div class="klip">
            <h3 class="title">Klip:</h3>
            <h3 id="klip">0</h3>
        </div>
        <div class="addButton">
            <button onclick="prepareAdding()"><?php echo $addToMessage; ?></button>
        </div>
        <div class="listButton">
            <button data-toggle="modal" data-target="#krModal">Oversigt</button>
        </div>
    </div>
</div>

<div class="krModal">
    <div class="modal fade" id="krModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Oversigt over tilføjet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="cases">
                    </div>
                    <div id="commentDisplay" style="margin-top: 20px; padding: 10px; background-color: #f8f9fa;">
                        <!-- comment kommer her -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Erkender Modal -->
<div class="modal fade" id="erkenderModal" tabindex="-1" role="dialog" aria-labelledby="erkenderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="erkenderModalLabel">Erkender vedkommende?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="erkenderCheck">
                    <label class="form-check-label" for="erkenderCheck">
                        Ja, vedkommende erkender
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk</button>
                <button type="button" class="btn btn-primary" onclick="submitErkender()">Bekræft</button>
            </div>
        </div>
    </div>
</div>

<!-- Bekræft Handling Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Bekræft Handling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Hvis personen ikke erkender, så skriv en kommentar om hvilke sigtelser han/hun ikke erkender,
                og kontakt Anklagemyndigheden i forbindelse med en retssag.
                <br>
                Læs evt: #anklagesager-information
                <textarea id="commentBox" rows="4" cols="50"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="sendFormData(0)">OK</button>
            </div>
        </div>
    </div>
</div>


<!-- Betinget Dom Modal -->
<div class="modal fade" id="conditionalModal" tabindex="-1" role="dialog" aria-labelledby="conditionalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="conditionalModalLabel">Er denne dom betinget?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="conditionalCheck">
                    <label class="form-check-label" for="conditionalCheck">
                        Ja, dommen er betinget
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk</button>
                <button type="button" class="btn btn-primary" onclick="submitConditional()">Bekræft</button>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        var commentBox = document.getElementById('commentBox');
        if (commentBox) {
            commentBox.addEventListener('input', function () {
                comment = this.value;
            });
        }
    });
</script>


<script type="text/javascript">
    $(document).ready(function () {
        load_data();

        function load_data(query) {
            $.ajax({
                url: "../fetch_addKrimi.php",
                method: "POST",
                data: { query: query },
                success: function (data) {
                    $('#result').html(data);
                    updateKrimiDisplay(query); // update display
                }
            });
        }

        $('#search_text').keyup(function () {
            var search = $(this).val();
            load_data(search);
        });

        function updateKrimiDisplay(query) {
            if (query && query.length > 1) {
                expandRelevantKrimi(); // expander vores liste hvis der er mere end 2 karakterer i søgefeltet
            } else {
                initializeKrimiCollapse(); // collapser alting hvis der er mindre end 2 karakterer i søgefeltet
            }
            alwaysExpandMisc(); // sørger altid for misc er åben
        }

        function initializeKrimiCollapse() {
            $(".tickets-header").each(function () {
                if (this.id !== 'Misc') {
                    $(this).addClass("collapsed");
                }
            });
            attachHeaderClick();
        }

        function expandRelevantKrimi() {
            $(".tickets-header").each(function () {
                var rowCount = $(this).parent('.tickets-emner').find('.table tbody tr').length;
                if (rowCount > 0 && this.id !== 'Misc') {
                    $(this).removeClass("collapsed");
                } else if (this.id !== 'Misc') {
                    $(this).addClass("collapsed");
                }
            });
            attachHeaderClick();
        }

        function alwaysExpandMisc() {
            $("#Misc").removeClass("collapsed");
        }

        function attachHeaderClick() {
            $(".tickets-header h2").off("click").on("click", function () {
                var section = $(this).closest('.tickets-header');
                if (section.attr('id') !== 'Misc') {
                    var isVisible = section.toggleClass("collapsed").hasClass("collapsed");
                    localStorage.setItem(section.attr('id'), isVisible ? "collapsed" : "expanded");
                }
            });
        }
    });

    var addObj = {};
    var added = [];
    var addedCases = [];

    var prisonAmount = 0;
    var ticketAmount = 0;
    var klipAmount = 0;

    var caseZone;
    var caseSpeed;

    var status = "";
    var comment = "";

    function addToPenalty(id, prison, ticket, klip, sigtet, paragaraf, number) {
        added.push(id);

        if (!addedCases[id]) {
            addedCases[id] = { count: 0, customValues: [] };
        }

        addedCases[id].count++;

        var newIndex = -1;
        if (Number.isInteger(number)) {
            addedCases[id].customValues.push(number);
            newIndex = addedCases[id].customValues.length - 1;
        }

        const div = document.createElement('div');
        div.className = 'case';

        paragaraf = paragaraf == null ? '' : paragaraf + ' - ';

        div.innerHTML = `
            <h2>${paragaraf}${sigtet}</h2>
            <div class="info">
                <p>Fængsel: ${prison} Måneder</p>
                <p>Bøde: ${ticket},- DKK</p>
                <p>Klip: ${klip}</p>
                <button class="btn btn-success btn-sm" data-index="${newIndex}" onclick="removeFromPenalty(${id}, ${prison}, ${ticket}, ${klip}, this)">Fjern sigtelse</button>
            </div>
        `;

        document.getElementById('cases').appendChild(div);

        prisonAmount = prisonAmount + prison;
        ticketAmount = ticketAmount + ticket;
        klipAmount = klipAmount + klip;

        let formattedPrice = new Intl.NumberFormat('da-DK', { style: 'currency', currency: 'DKK' }).format(ticketAmount)

        document.getElementById("prison").innerHTML = prisonAmount;
        document.getElementById("ticket").innerHTML = formattedPrice;
        document.getElementById("klip").innerHTML = klipAmount;
    }

    function removeFromPenalty(id, prison, ticket, klip, input) {
        const index = added.indexOf(id);
        if (index > -1) {
            added.splice(index, 1);

            if (addedCases[id]) {
                if (input.getAttribute('data-index') > -1) {
                    const dataIndex = parseInt(input.getAttribute('data-index'));
                    if (dataIndex >= 0 && dataIndex < addedCases[id].customValues.length) {
                        addedCases[id].customValues.splice(dataIndex, 1);
                    }
                } else {
                    addedCases[id].count -= 1;
                    if (addedCases[id].count <= 0) {
                        delete addedCases[id];
                    }
                }
            }
        }

        document.getElementById('cases').removeChild(input.parentNode.parentNode);

        prisonAmount = prisonAmount - prison;
        ticketAmount = ticketAmount - ticket;
        klipAmount = klipAmount - klip;

        document.getElementById("prison").innerHTML = prisonAmount;
        document.getElementById("ticket").innerHTML = ticketAmount;
        document.getElementById("klip").innerHTML = klipAmount;
    }

    function addDrugs(id, ticket, prison, sigtet, paragraf) {
        var prisonFinal;
        var ticketFinal;

        (async () => {
            const { value: drugAmount } = await Swal.fire({
                title: 'Indtast antal',
                input: 'number',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!'
                    }
                }
            })

            if (drugAmount) {
                const prisonFinal = prison !== 0 ? Math.round(drugAmount / prison) : 0;
                ticketFinal = ticket * drugAmount;
                sigtet = sigtet + " x" + drugAmount;

                addToPenalty(id, prisonFinal, ticketFinal, 0, sigtet, paragraf, Number(drugAmount))
            }
        })()
    }

    const speedTicketData = {
        "by": [
            { "speed": 50, "ticket": "", "klip": "" },
            { "speed": 51, "ticket": "1000", "klip": "" },
            { "speed": 59, "ticket": "1000", "klip": "" },
            { "speed": 60, "ticket": "1800", "klip": "" },
            { "speed": 64, "ticket": "1800", "klip": "" },
            { "speed": 65, "ticket": "2800", "klip": "" },
            { "speed": 69, "ticket": "2800", "klip": "1" },
            { "speed": 70, "ticket": "3000", "klip": "1" },
            { "speed": 79, "ticket": "3000", "klip": "1" },
            { "speed": 80, "ticket": "3500", "klip": "3" },
            { "speed": 84, "ticket": "3500", "klip": "3" },
            { "speed": 85, "ticket": "4000", "klip": "3" },
            { "speed": 89, "ticket": "4000", "klip": "3" },
            { "speed": 90, "ticket": "4500", "klip": "3" },
            { "speed": 94, "ticket": "4500", "klip": "3" },
            { "speed": 95, "ticket": "5500", "klip": "3" },
            { "speed": 99, "ticket": "5500", "klip": "3" },
            { "speed": 100, "ticket": "6000", "klip": "3" },
            { "speed": 101, "ticket": "6000", "klip": "6" },
            { "speed": 139, "ticket": "6000", "klip": "6" },
            { "speed": 140, "ticket": "7000", "klip": "6" },
            { "speed": 149, "ticket": "7000", "klip": "6" },
            { "speed": 150, "ticket": "7500", "klip": "6" },
            { "speed": 159, "ticket": "7500", "klip": "6" },
            { "speed": 160, "ticket": "8000", "klip": "6" },
            { "speed": 169, "ticket": "8000", "klip": "6" },
            { "speed": 170, "ticket": "8500", "klip": "6" },
            { "speed": 179, "ticket": "8500", "klip": "6" },
            { "speed": 180, "ticket": "9000", "klip": "6" },
            { "speed": 189, "ticket": "9000", "klip": "6" },
            { "speed": 190, "ticket": "9500", "klip": "6" },
            { "speed": 199, "ticket": "9500", "klip": "6" },
            { "speed": 200, "ticket": "10000", "klip": "6" },
            { "speed": 209, "ticket": "10000", "klip": "6" },
            { "speed": 210, "ticket": "10500", "klip": "6" },
            { "speed": 219, "ticket": "10500", "klip": "6" },
            { "speed": 220, "ticket": "11000", "klip": "6" },
            { "speed": 229, "ticket": "11000", "klip": "6" },
            { "speed": 230, "ticket": "11500", "klip": "6" },
            { "speed": 239, "ticket": "11500", "klip": "6" },
            { "speed": 240, "ticket": "12000", "klip": "6" },
            { "speed": 249, "ticket": "12000", "klip": "6" },
            { "speed": 250, "ticket": "12500", "klip": "6" },
            { "speed": 259, "ticket": "12500", "klip": "6" },
            { "speed": 260, "ticket": "13000", "klip": "6" },
            { "speed": 269, "ticket": "13000", "klip": "6" },
            { "speed": 270, "ticket": "13500", "klip": "6" },
            { "speed": 279, "ticket": "13500", "klip": "6" },
            { "speed": 280, "ticket": "14000", "klip": "6" },
            { "speed": 289, "ticket": "14000", "klip": "6" },
            { "speed": 290, "ticket": "14500", "klip": "6" },
            { "speed": 299, "ticket": "14500", "klip": "6" },
            { "speed": 300, "ticket": "15000", "klip": "6" },

        ],
        "landevejs": [
            { "speed": 80, "ticket": "", "klip": "" },
            { "speed": 81, "ticket": "1000", "klip": "" },
            { "speed": 95, "ticket": "1000", "klip": "" },
            { "speed": 96, "ticket": "1800", "klip": "" },
            { "speed": 104, "ticket": "1800", "klip": "" },
            { "speed": 105, "ticket": "2800", "klip": "" },
            { "speed": 111, "ticket": "2800", "klip": "" },
            { "speed": 112, "ticket": "3000", "klip": "" },
            { "speed": 126, "ticket": "3000", "klip": "" },
            { "speed": 127, "ticket": "3500", "klip": "" },
            { "speed": 129, "ticket": "3500", "klip": "3" },
            { "speed": 135, "ticket": "3500", "klip": "3" },
            { "speed": 136, "ticket": "4000", "klip": "3" },
            { "speed": 139, "ticket": "4000", "klip": "3" },
            { "speed": 140, "ticket": "5000", "klip": "3" },
            { "speed": 145, "ticket": "5000", "klip": "3" },
            { "speed": 146, "ticket": "5500", "klip": "3" },
            { "speed": 149, "ticket": "5500", "klip": "3" },
            { "speed": 146, "ticket": "5500", "klip": "3" },
            { "speed": 150, "ticket": "6000", "klip": "6" },
            { "speed": 152, "ticket": "6000", "klip": "6" },
            { "speed": 153, "ticket": "7000", "klip": "6" },
            { "speed": 159, "ticket": "7000", "klip": "6" },
            { "speed": 160, "ticket": "8000", "klip": "6" },
            { "speed": 169, "ticket": "8000", "klip": "6" },
            { "speed": 170, "ticket": "8500", "klip": "6" },
            { "speed": 179, "ticket": "8500", "klip": "6" },
            { "speed": 180, "ticket": "9000", "klip": "6" },
            { "speed": 189, "ticket": "9000", "klip": "6" },
            { "speed": 190, "ticket": "9500", "klip": "6" },
            { "speed": 199, "ticket": "9500", "klip": "6" },
            { "speed": 200, "ticket": "10000", "klip": "6" },
            { "speed": 209, "ticket": "10000", "klip": "6" },
            { "speed": 210, "ticket": "10500", "klip": "6" },
            { "speed": 219, "ticket": "10500", "klip": "6" },
            { "speed": 220, "ticket": "11000", "klip": "6" },
            { "speed": 229, "ticket": "11000", "klip": "6" },
            { "speed": 230, "ticket": "11500", "klip": "6" },
            { "speed": 239, "ticket": "11500", "klip": "6" },
            { "speed": 240, "ticket": "12000", "klip": "6" },
            { "speed": 249, "ticket": "12000", "klip": "6" },
            { "speed": 250, "ticket": "12500", "klip": "6" },
            { "speed": 259, "ticket": "12500", "klip": "6" },
            { "speed": 260, "ticket": "13000", "klip": "6" },
            { "speed": 269, "ticket": "13000", "klip": "6" },
            { "speed": 270, "ticket": "13500", "klip": "6" },
            { "speed": 279, "ticket": "13500", "klip": "6" },
            { "speed": 280, "ticket": "14000", "klip": "6" },
            { "speed": 289, "ticket": "14000", "klip": "6" },
            { "speed": 290, "ticket": "14500", "klip": "6" },
            { "speed": 299, "ticket": "14500", "klip": "6" },
            { "speed": 300, "ticket": "15000", "klip": "6" },

        ],
        "motorvejs": [
            { "speed": 130, "ticket": "", "klip": "" },
            { "speed": 131, "ticket": "1000", "klip": "" },
            { "speed": 139, "ticket": "1000", "klip": "" },
            { "speed": 140, "ticket": "2000", "klip": "" },
            { "speed": 149, "ticket": "2000", "klip": "" },
            { "speed": 150, "ticket": "2500", "klip": "" },
            { "speed": 155, "ticket": "2500", "klip": "" },
            { "speed": 156, "ticket": "3300", "klip": "" },
            { "speed": 159, "ticket": "3300", "klip": "" },
            { "speed": 160, "ticket": "3800", "klip": "3" },
            { "speed": 168, "ticket": "3800", "klip": "3" },
            { "speed": 169, "ticket": "4000", "klip": "3" },
            { "speed": 170, "ticket": "4500", "klip": "3" },
            { "speed": 179, "ticket": "4500", "klip": "3" },
            { "speed": 180, "ticket": "5000", "klip": "3" },
            { "speed": 181, "ticket": "5000", "klip": "3" },
            { "speed": 182, "ticket": "5500", "klip": "3" },
            { "speed": 189, "ticket": "5500", "klip": "3" },
            { "speed": 190, "ticket": "6000", "klip": "3" },
            { "speed": 194, "ticket": "6000", "klip": "3" },
            { "speed": 195, "ticket": "6500", "klip": "3" },
            { "speed": 199, "ticket": "6500", "klip": "3" },
            { "speed": 200, "ticket": "7000", "klip": "6" },
            { "speed": 206, "ticket": "7000", "klip": "6" },
            { "speed": 207, "ticket": "7500", "klip": "6" },
            { "speed": 210, "ticket": "7500", "klip": "6" },
            { "speed": 211, "ticket": "8000", "klip": "6" },
            { "speed": 219, "ticket": "8000", "klip": "6" },
            { "speed": 220, "ticket": "9500", "klip": "6" },
            { "speed": 229, "ticket": "9500", "klip": "6" },
            { "speed": 230, "ticket": "10000", "klip": "6" },
            { "speed": 234, "ticket": "10000", "klip": "6" },
            { "speed": 235, "ticket": "10500", "klip": "6" },
            { "speed": 239, "ticket": "10500", "klip": "6" },
            { "speed": 240, "ticket": "11000", "klip": "6" },
            { "speed": 245, "ticket": "11000", "klip": "6" },
            { "speed": 246, "ticket": "12500", "klip": "6" },
            { "speed": 249, "ticket": "12500", "klip": "6" },
            { "speed": 250, "ticket": "13000", "klip": "6" },
            { "speed": 254, "ticket": "13000", "klip": "6" },
            { "speed": 255, "ticket": "13500", "klip": "6" },
            { "speed": 259, "ticket": "13500", "klip": "6" },
            { "speed": 260, "ticket": "14000", "klip": "6" },
            { "speed": 264, "ticket": "14000", "klip": "6" },
            { "speed": 265, "ticket": "14500", "klip": "6" },
            { "speed": 269, "ticket": "14500", "klip": "6" },
            { "speed": 270, "ticket": "15000", "klip": "6" },
            { "speed": 279, "ticket": "15000", "klip": "6" },
            { "speed": 280, "ticket": "15500", "klip": "6" },
            { "speed": 289, "ticket": "15500", "klip": "6" },
            { "speed": 290, "ticket": "16000", "klip": "6" },
            { "speed": 299, "ticket": "16000", "klip": "6" },
            { "speed": 300, "ticket": "16500", "klip": "6" },

        ]
    };

    function addMisc(id) {
        var prisonFinal = 0;
        var ticketFinal = 0;
        var klipFinal = 0;

        var sigtet;

        if (id == -1) {
            (async () => {
                const { value: formValues } = await Swal.fire({
                    title: 'Indtast Fart strafferamme',
                    html:
                        '<select id="swal-input-zone" class="swal2-input">' +
                        '<option value="by">Byzone</option>' +
                        '<option value="landevejs">Landevej</option>' +
                        '<option value="motorvejs">Motorvej</option>' +
                        '</select>' +
                        '<input id="swal-input-speed" type="number" placeholder="Fart" class="swal2-input">',
                    focusConfirm: false,
                    preConfirm: () => {
                        return {
                            zone: document.getElementById('swal-input-zone').value,
                            speed: document.getElementById('swal-input-speed').value
                        }
                    }
                });

                if (formValues) {
                    const { zone, speed } = formValues;
                    const zoneData = speedTicketData[zone];
                    let matchingEntry;

                    const limits = { "by": 300, "landevejs": 300, "motorvejs": 300 };
                    const speedLimit = limits[zone];

                    if (parseInt(speed) > speedLimit) {
                        matchingEntry = zoneData[zoneData.length - 1];
                    } else {
                        matchingEntry = zoneData.find(entry => parseInt(speed) <= entry.speed);
                    }

                    if (matchingEntry) {
                        ticketFinal = parseInt(matchingEntry.ticket) || 0;
                        klipFinal = parseInt(matchingEntry.klip) || 0;

                        caseZone = zone;
                        caseSpeed = speed;

                        sigtet = "Fartbøde: Bøde: " + ticketFinal + " Klip: " + klipFinal;
                        addToPenalty(id, prisonFinal, ticketFinal, klipFinal, sigtet);
                    } else {
                        console.error("Ingen matchende fartbøde fundet.(seriøs fejl)");
                    }
                }
            })();
        }

        if (id == -2) {
            (async () => {
                const { value: ticket } = await Swal.fire({
                    title: 'Indtast antal',
                    input: 'number',
                    showCancelButton: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to write something!'
                        }
                    }
                })

                if (ticket) {
                    ticketFinal = parseInt(ticket);
                    ticketFinal = -Math.abs(ticketFinal);

                    sigtet = "Nedsætning af bøde: " + ticketFinal;

                    addToPenalty(id, prisonFinal, ticketFinal, klipFinal, sigtet)
                }
            })()
        }

        if (id == -3) {
            (async () => {
                const { value: prison } = await Swal.fire({
                    title: 'Indtast antal',
                    input: 'number',
                    showCancelButton: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to write something!'
                        }
                    }
                })

                if (prison) {
                    prisonFinal = parseInt(prison);
                    prisonFinal = -Math.abs(prisonFinal);

                    sigtet = "Nedsætning af fængselsstraf: " + prisonFinal;

                    addToPenalty(id, prisonFinal, ticketFinal, klipFinal, sigtet)
                }
            })()
        }
    }


    function addStatus() {
        (async () => {

            const { value: fruit } = await Swal.fire({
                title: 'Select field validation',
                input: 'select',
                inputOptions: {
                    betingetbil: 'Betinget frakendelse bil',
                    betingetmc: 'Betinget frakendelse MC',
                    betingetlastbil: 'Betinget frakendelse Lastbil',
                    ubetinget: 'Ubetinget Frakendelse'
                },
                inputPlaceholder: 'Vælg en frakendelse',
                showCancelButton: true,
            })

            if (fruit) {
                var id;
                var sigtet;

                if (fruit == 'betingetbil') {
                    id = -4;
                    status = "Betinget frakendelse af Bil";
                } else if (fruit == 'betingetmc') {
                    id = -5;
                    status = "Betinget frakendelse af Motorcykel";
                } else if (fruit == 'betingetlastbil') {
                    id = -6;
                    status = "Betinget frakendelse af Lastbil";
                } else if (fruit == 'ubetinget') {
                    id = -7;
                    status = "Ubetinget frakendelse";
                }

                sigtet = status;

                addToPenalty(id, 0, 0, 0, sigtet)
            }

        })()
    }

    var comment = "";

    function addComment() {
        (async () => {
            const { value: text } = await Swal.fire({
                input: 'textarea',
                inputPlaceholder: 'Skriv en kommentar...',
                inputAttributes: {
                    'aria-label': 'Skriv en kommentar'
                },
                showCancelButton: true
            });

            if (text) {
                comment = text; // Gemmer den nye kommentar globalt
                updateCommentDisplay();
            }
        })();
    }

    function updateCommentDisplay() {
        const display = document.getElementById('commentDisplay');
        display.innerHTML = `<strong>Kommentar:</strong> ${comment}`;
    }

    // Opdatering af kommentarvisning når modal vises
    $('#krModal').on('show.bs.modal', function (event) {
        updateCommentDisplay();
    });

    function cleanObject(obj) {
        const cleanedObj = {};
        for (const key in obj) {
            if (obj[key] !== null && obj[key] !== undefined) {
                cleanedObj[key] = obj[key];
            }
        }
        return cleanedObj;
    }

    // I stedet for at omdirigere direkte, vis modalen
    function prepareAdding() {
        var type = "<?php echo $type; ?>"; // Antag at denne værdi er tilgængelig som en PHP-variabel
        // Tjekker om 'type' er "krimi" og om 'prisonAmount' er større end 0 og mindre end eller lig med 30
        if (type === "krimi" && prisonAmount > 0 && prisonAmount <= 30) {
            $('#conditionalModal').modal('show');
        } else if (prisonAmount > 30) {
            $('#erkenderModal').modal('show');
        } else if (prisonAmount === 0) {
            // Hvis prisonAmount er 0, vises erkender modalen direkte
            $('#erkenderModal').modal('show');
        } else {
            // Hvis ingen af de ovenstående betingelser er opfyldt, vises den relevante handling eller modal
            sendFormData(1); // Eller en anden handling der passer til konteksten
        }
    }


    function submitConditional() {
        var conditional = document.getElementById('conditionalCheck').checked ? 1 : 0;
        $('#conditionalModal').modal('hide');
        $('#erkenderModal').modal('show');
        document.getElementById('erkenderCheck').dataset.conditional = conditional;
    }

    function submitErkender() {
        var erkender = document.getElementById('erkenderCheck').checked ? 1 : 0;
        var conditional = document.getElementById('erkenderCheck').dataset.conditional || 0; // Henter betinget status

        // Opdaterer variablen 'comment' med værdien fra displayet, hvis den eksisterer
        const commentDisplay = document.getElementById('commentDisplay');
        if (commentDisplay) {
            comment = commentDisplay.textContent.replace('Kommentar:', '').trim();
        }

        // Tjekker om personen erkender
        if (!erkender) {
            if (comment.length > 0) {
                // Hvis der allerede er en kommentar og erkendelse er falsk, send data
                sendFormData(0, conditional);
            } else {
                // Hvis der ikke er nogen kommentar og erkendelse er falsk, vis "Bekræft Handling" modalen
                $('#confirmationModal').modal('show');
            }
        } else {
            // Hvis erkender er markeret som sand, send data
            sendFormData(1, conditional);
        }
    }


    function sendFormData(erkender, conditional) {
        var cleanedAddedCases = cleanObject(addedCases);
        var type = "<?php echo $type; ?>";
        var player = "<?php echo $player; ?>";

        var data = {
            player: player,
            type: type,
            cases: JSON.stringify(cleanedAddedCases),
            ticket: ticketAmount,
            prison: prisonAmount,
            klip: klipAmount,
            status: status,
            comment: comment,
            zone: caseZone,
            speed: caseSpeed,
            erkender: erkender,
            conditional: conditional
        };

        const url = `addKrimi.php?${Object.entries(data).map(([key, value]) => `${key}=${encodeURIComponent(value)}`).join("&")}`;
        window.location.href = url;
    }

</script>

<?php
if (isset($_GET['case'])) {
    $case_id = $_GET['case'];
    $sql = "SELECT * FROM population_cases WHERE id = " . $case_id;
    $result_case = $link->query($sql);

    if ($result_case->num_rows > 0) {
        $row = $result_case->fetch_assoc();
        $cases = json_decode($row['cases']);

        echo '<script>';

        foreach ($cases as $key => $value) {
            $sql = "SELECT * FROM tickets WHERE id = " . $key;
            $result = $link->query($sql);

            while ($row = $result->fetch_assoc()) {
                $ticket = ($row['ticket'] != NULL) ? $row['ticket'] : 0;
                $klip = ($row['klip'] != NULL) ? $row['klip'] : 0;
                $prison = ($row['prison'] != NULL) ? $row['prison'] : 0;

                for ($i = 0; $i < $value->count; $i++) {
                    $ticketToAdd = $ticket;
                    $prisonToAdd = $prison;

                    $customValue = (!empty($value->customValues) && isset($value->customValues[$i])) ? $value->customValues[$i] : null;

                    if (isset($customValue)) {
                        $ticketToAdd *= $customValue;
                        $prisonToAdd = round($customValue / $prison);
                        $sigtet = $row['sigtelse'] . " x" . $customValue;
                    } else {
                        $sigtet = $row['sigtelse'];
                    }

                    echo "addToPenalty(" . $row['id'] . "," . $prisonToAdd . "," . $ticketToAdd . "," . $klip . ",'" . $sigtet . "','" . $row['paragraf'] . "','" . $row['paragraf'] . "');";
                }
            }
        }

        echo '</script>';
    }
}

include '../footer.php';
?>