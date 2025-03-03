<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$stmt = $link->prepare("SELECT * FROM punishment ORDER BY order_number ASC");
$stmt->execute();
$result = $stmt->get_result();

$output = '';
$search_query = '';
$isAdmin = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["query"])) {
    $search_query = $_POST["query"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["isAdmin"])) {
    $isAdmin = $_POST["isAdmin"];
}

$search_term = "%" . $search_query . "%";

while($row = $result->fetch_assoc()) {
    $stmt = $link->prepare("SELECT * FROM tickets WHERE emne = ? AND sigtelse LIKE ? ORDER BY paragraf ASC");
    $stmt->bind_param("ss", $row['ticketemne'], $search_term);
    $stmt->execute();
    $ticketresult = $stmt->get_result();

    if ($ticketresult->num_rows > 0) {
        $output .= '
        <div class="tickets-emner">
            <div class="tickets-header" id="' . $row["ticketemne"] . '">
                <h2>' . $row['ticketemne'] . '</h2>
                <table class="table table-striped table-hover">
        ';

        $output .= "<tr>";
        $output .= '<th>Paragraf</th>';
        $output .= '<th>Sigtelse</th>';
        $output .= '<th>Bøde</th>';

        if($row['hasVehicle']) {
            $output .= '<th>Klip</th>';
            $output .= '<th>Frakendelse</th>';
            $output .= '<th>Information</th>';
        } else if($row['hasPrison']) {
            $output .= '<th>Fængselstraf</th>';
            $output .= '<th>Information</th>';
        } else {
            $output .= '<th>Fængselstraf</th>';
            $output .= '<th>Information</th>';
        }

        $output .= '<th>Tilføj</th>';
        $output .= "</tr>";

        while($ticketrow = $ticketresult->fetch_assoc()) {
            $output .= "<tr>";

            $output .= '<td>' . $ticketrow['paragraf'] . '</td>';
            $output .= '<td>' . $ticketrow['sigtelse'] . '</td>';
            $output .= '<td>' . $ticketrow['ticket'] . ',- DKK</td>';

            if ($ticketrow['prison'] == "") {
                $ticketrow['prison'] = 0;
            }

            if ($ticketrow['klip'] == null) {
                $ticketrow['klip'] = 0;
            }

            if($row['hasVehicle']) {
                $output .= '<td>' . $ticketrow['klip'] . '</td>';
                $output .= '<td>' . $ticketrow['frakendelse'] . '</td>';
            } else if($row['hasPrison']) {
                if($row['hasStoffer']) {
                    $output .= '<td>1 måned pr. ' . $ticketrow['prison'] . ' stk.</td>';
                } else {
                    $output .= '<td>' . $ticketrow['prison'] . '</td>';
                }
            } else {
                $output .= '<td>' . $ticketrow['prison'] . '</td>';
            }

            $output .= '<td>' . $ticketrow['information'] . '</td>';

            $addPenalty = "";
            if ($row['hasStoffer'] || $ticketrow['sigtelse'] == 'Besiddelse af Ammunition') {
                $addPenalty = "addDrugs(" . $ticketrow['id'] . "," . $ticketrow['ticket'] . "," . $ticketrow['prison'] . ",'" . $ticketrow['sigtelse'] . "','" . $ticketrow['paragraf'] . "')";
            } else {
                $addPenalty = "addToPenalty(" . $ticketrow['id'] . "," . $ticketrow['prison'] . "," . $ticketrow['ticket'] . "," . $ticketrow['klip'] . ",'" . $ticketrow['sigtelse'] . "','" . $ticketrow['paragraf'] . "')";
            }

            $output .= '<td><button class="addkrim-button" title="Tilføj til straf" onclick="' . $addPenalty . '"><i class="far fa-plus-square addkrim-button-text"></button></td>';

            $output .= "</tr>";
        }

        $output .= "</table>";
        $output .= "</div>";
        $output .= "</div>";
    }
}

echo $output;
?>