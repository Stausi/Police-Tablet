<?php
include '../header.php';

$player = $_GET['player'];

$sigtet = "";
$playerid = $id = $ticket = $prison = $klip = 0;
$playerid_err = $id_err = $sigtet_err = $ticket_err = $prison_err = $klip_err = "";

$sql = "SELECT * FROM krimi WHERE id='" . $player . "'";
$result = $link->query($sql);

$sql = "SELECT klip FROM players WHERE player_id='" . $player . "' AND dato >= DATE(NOW()) - INTERVAL 3 DAY";
$result2 = $link->query($sql);

$sql = "SELECT status FROM players WHERE player_id='" . $player . "' AND dato >= DATE(NOW()) - INTERVAL 3 DAY";
$result3 = $link->query($sql);

$klip = $height = $number = 0;
$status = "Ingen aktiv frakendelse";
$firstname = $lastname = $dob = "";

while($row = mysqli_fetch_array($result)) {
    $firstname = $row['firstname'];
    $lastname = $row['lastname'];
    $dob = $row['dateofbirth'];

    $dobSlit = explode(" ", $dob);
    $dob = $dobSlit[0];
    $dob = str_replace(".", "/", $dob);

    if ($row['height'] != null) {
        $height = $row['height'];
    }

    if ($row['phone_number'] != null) {
        $number = $row['phone_number'];
    }
}

while($row = mysqli_fetch_array($result2)) {
    $klip = $klip + $row['klip'];
}

while($row = mysqli_fetch_array($result3)) {
    if ($row['status'] == "Ubetinget frakendelse") {
        $status = $row['status'];
    }
    if ($row['status'] == "Betinget frakendelse af Bil" || $row['status'] == "Betinget frakendelse af Motorcykel" || $row['status'] == "Betinget frakendelse af Lastbil") {
        if ($status != "Ubetinget frakendelse") {
            $status = $row['status'];
        }
    }
}

$imgURL = "";
$file_pointer = '../../assets/playersIMG/' . $player . '.png'; 

if (file_exists($file_pointer)) {
    $imgURL = $file_pointer;
} else {
    $imgURL = '../../assets/playersIMG/unknown.png';
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'RemoveKrim') {
        $sql = "DELETE FROM players WHERE player_id = ?";
            
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $_GET['player'];

            if(!mysqli_stmt_execute($stmt)){
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }

        if (file_exists($file_pointer)) {
            if (!unlink($file_pointer)) {  
                echo ("$file_pointer cannot be deleted due to an error");
            }  
        }

        $sql = "DELETE FROM krimi WHERE id = ?";
            
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $_GET['player'];

            if(mysqli_stmt_execute($stmt)){
                header("location: krimi.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
    } elseif ($action == 'RemovePicture') {
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
    $sql = "DELETE FROM players WHERE id = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        $param_id = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: player.php?player=" . $player);
        } else{
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["playerid"]))){
        $playerid_err = "Vælg venligst et playerid";     
    } else{
        $playerid = trim($_POST["playerid"]);
    }

    if(empty(trim($_POST["id"]))){
        $id_err = "Vælg venligst et ID";     
    } else{
        $id = trim($_POST["id"]);
    }

    if(empty(trim($_POST["sigtet"]))){
        $sigtet_err = "Vælg venligst en sigtelse";     
    } else{
        $sigtet = trim($_POST["sigtet"]);
    }

    if(empty($_POST["ticket"])){
        $ticket_err = "Vælg venligst en Bøde";     
    } else{
        $ticket = trim($_POST["ticket"]);
    }

    if(!empty($_POST["prison"])){
        $prison = trim($_POST["prison"]);
    }

    if(!empty($_POST["klip"])){
        $klip = trim($_POST["klip"]);
    }

    if(empty($id_err) && empty($sigtet_err) && empty($ticket_err) && empty($prison_err)){
        $sql = "UPDATE players SET sigtet = ?, ticket = ?, prison = ?, klip = ? WHERE id = ?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "siiii", $param_siget, $param_ticket, $param_prison, $param_klip, $param_id);
            
            $param_siget = $sigtet;
            $param_ticket = $ticket;
            $param_prison = $prison;
            $param_klip = $klip;
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: player.php?player=" . $playerid);
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<main>
    <div class="player">
        <div class="player-header">
            <div class="player-image">
                <img src="<?php echo $imgURL ?>" alt="player">
                <button class="change-image" data-toggle="modal" data-target="#profileModal">Ændre billede til KR</button>
            </div>
            <div class="player-info">
                <div class="info-column">
                    <div class="info-text">
                        <h2 class="title">Navn: </h2>
                        <h2 class="title"><?php echo "$firstname $lastname" ?></h2>
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
            </div>
        </div>
        <div class="mid-line"></div>
        <h2 class="header">Kriminalregister</h2>
        <table class="table table-striped table-hover">
            <tr>
                <th>Journal Nr.</th>
                <th>Dato</th>
                <th>Betjent</th>
                <th>Sigtet for</th>
                <th>Bødestørrelse</th>
                <th>Fængselsstraf</th>
                <th>Klip</th>
                <th>Kommentar</th>
            </tr>
                <?php
                $sql = "SELECT * FROM players WHERE player_id ='" . $player . "'";
                $result = $link->query($sql);
                while($row = $result->fetch_assoc()) { 
                ?>
                <tr>
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo $row['dato'] ?></td>
                    <td><?php echo $row['username'] ?></td>
                    <td><?php echo $row['sigtet'] ?></td>
                    <td><?php echo number_format($row['ticket'], 0, ",", ".") ?>,- DKK</td>
                    <td><?php echo $row['prison'] ?> Måneder</td>
                    <td><?php echo $row['klip'] ?></td>
                    <td><?php echo $row['comment'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <script type="text/javascript">
        $('#customFile').on("change",function() {
            var i = $(this).prev('label').clone();
            var file = $('#customFile')[0].files[0].name;
            $(this).next('label').text(file);
        });
    </script>
</main>

<?php
include '../footer.php';
?>