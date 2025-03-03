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

        if($isAdmin) $output .= '<th>Redigere</th>';
        $output .= "</tr>";

        while($ticketrow = $ticketresult->fetch_assoc()) {
            $output .= "<tr>";

            $output .= '<td>' . $ticketrow['paragraf'] . '</td>';
            $output .= '<td>' . $ticketrow['sigtelse'] . '</td>';
            $output .= '<td>' . $ticketrow['ticket'] . ',- DKK</td>';

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

            if($isAdmin) {
                $output .= '<td><a href="./admin/manageTicket.php?ticketid=' . $ticketrow['id'] . '" class="edit-ticket">Redigere Bøde</a></td>';
            }

            $output .= "</tr>";
        }

        $output .= "</table>";
        $output .= "</div>";
        $output .= "</div>";
    }
}

echo $output;
?>