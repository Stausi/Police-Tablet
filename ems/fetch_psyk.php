<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$output = '';
if (isset($_POST["query"])) {
    $search = mysqli_real_escape_string($link, $_POST["query"]);
    $query = "SELECT * FROM population_ems WHERE name LIKE '%" . $search . "%' LIMIT 10";
} else {
    $query = "SELECT * FROM population_ems ORDER BY name LIMIT 10";
}

$result = mysqli_query($link, $query);
if(mysqli_num_rows($result) > 0) {
    $output .= '
        <div class="table-responsive">
            <table class="table table-striped table-hover">
            <tr>
                <th>Navn</th>
                <th>Køn</th>
                <th>Fødselsdato</th>
                <th>Køn</th>
                <th>Journalregister</th>
            </tr>
    ';

    while($row = mysqli_fetch_array($result)) {
        $output .= '
            <tr>
                <td>'.$row["name"].'</td>
                <td>'.$row["sex"].'</td>
                <td>'.$row["dob"].'</td>
                <td>'.$row["sex"].'</td>
                <td><a href="playerpsyk.php?player=' . $row["id"] . '"><i class="fas fa-info"></i> Psykolog Arkiv</a></td>
            </tr>
        ';
    }

    echo $output;
} else {
    echo "<h3>Ingen Resultater fundet.</h3>";
}
?>