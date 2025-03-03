<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$output = '';
if(isset($_POST["query"])) {
    $search = mysqli_real_escape_string($link, $_POST["query"]);
    $query = "SELECT * FROM krimi WHERE CONCAT(firstname, ' ', lastname) LIKE '%".$search."%' LIMIT 14";
} else {
    $query = "SELECT * FROM krimi ORDER BY firstname LIMIT 10";
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
                <th>Kriminalregister</th>
            </tr>
    ';
    while($row = mysqli_fetch_array($result)) {
        if($row["sex"] == '') $row["sex"] = "N/A";
        if($row["dateofbirth"] == '') $row["dateofbirth"] = "N/A";
        $output .= '
            <tr>
                <td>'.$row["firstname"]." ".$row["lastname"].'</td>
                <td>'.$row["sex"].'</td>
                <td>'.$row["dateofbirth"].'</td>
                <td><a href="player_old.php?player=' . $row["id"] . '"><i class="fas fa-info"></i> Kriminalregister</a></td>
            </tr>
        ';
    }
    echo $output;
} else {
    echo "<h3>Ingen Resultater fundet.</h3>";
}
?>