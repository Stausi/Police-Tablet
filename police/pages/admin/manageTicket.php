<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


$ticketid = 0;
if(isset($_GET['ticketid'])) {
    $ticketid = $_GET['ticketid'];
}

$stmt = $link->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->bind_param("i", $ticketid);
$stmt->execute();
$result = $stmt->get_result();

$ticketSql = "SELECT * FROM punishment";
$ticketResult = $link->query($ticketSql);

$id = $paragraf = $sigtelse = $ticket = $klip = $frakendelse = $information = $prison = $emne = "";

while($row = mysqli_fetch_array($result)) {
    $paragraf = $row['paragraf'];
    $sigtelse = $row['sigtelse'];
    $ticket = $row['ticket'];
    $klip = $row['klip'];
    $frakendelse = $row['frakendelse'];
    $information = $row['information'];
    $prison = $row['prison'];
    $emne = $row['emne'];
}

if(isset($_GET['delete'])) {
    $sql = "DELETE FROM tickets WHERE id = ?";
         
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_userid);
        
        $param_userid = $_GET['delete'];
        
        if(mysqli_stmt_execute($stmt)){
            header("location: ../tickets.php");
        } else{
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(trim($_POST["id"])) {
        $id = trim($_POST["id"]);
    }

    if(trim($_POST["paragraf"])) {
        $paragraf = trim($_POST["paragraf"]);
    }

    if(trim($_POST["sigtelse"])) {
        $sigtelse = trim($_POST["sigtelse"]);
    }

    if(trim($_POST["ticket"])) {
        $ticket = trim($_POST["ticket"]);
    }

    if(trim($_POST["information"])) {
        $information = trim($_POST["information"]);
    }

    if(trim($_POST["klip"])) {
        $klip = trim($_POST["klip"]);
    }

    if(trim($_POST["frakendelse"])){
        $frakendelse = trim($_POST["frakendelse"]);
    }

    if(trim($_POST["prison"])) {
        $prison = trim($_POST["prison"]);
    }

    if(trim($_POST["emner"])) {
        $emne = trim($_POST["emner"]);
    }

    $updateSql = "UPDATE tickets SET paragraf = ?, sigtelse = ?, ticket = ?, klip = ?, frakendelse = ?, information = ?, prison = ?, emne = ? WHERE id = ?";

    if($stmt = mysqli_prepare($link, $updateSql)) {
        mysqli_stmt_bind_param($stmt, "sssissssi", $param_paragraf, $param_sigtelse, $param_ticket, $param_klip, $param_frakendelse, $param_information, $param_prison, $param_emne, $param_id);
    
        $param_paragraf = $paragraf;
        $param_sigtelse = $sigtelse;
        $param_ticket = $ticket;
        $param_klip = $klip;
        $param_frakendelse = $frakendelse;
        $param_information = $information;
        $param_prison = $prison;
        $param_emne = $emne;
        $param_id = $id;
        
        if(mysqli_stmt_execute($stmt)) {
            header("location: manageTicket.php?ticketid=" . $param_id);
        } else {
            echo "Something went wrong. Please try again later. <br>";
            printf("Error message: %s\n", $link->error);
        }
    }
}

echo '<main>';
    echo '<div class="create-afdeling">';
        echo '<h2>Redigere ' . $paragraf . '</h2>';
        echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
            echo '<div class="form-group">';
                echo '<label>ID</label>';
                echo '<input type="text" name="id" class="form-control" value="' . $ticketid . '" readonly>';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Paragraf</label>';
                echo '<input type="text" name="paragraf" class="form-control" value="' . $paragraf . '">';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Sigtelse</label>';
                echo '<input type="text" name="sigtelse" class="form-control" value="' . $sigtelse . '">';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Bøde</label>';
                echo '<input type="text" name="ticket" class="form-control" value="' . $ticket . '">';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Klip</label>';
                echo '<input type="text" name="klip" class="form-control" value="' . $klip . '">';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Frakendelse</label>';
                echo '<input type="text" name="frakendelse" class="form-control" value="' . $frakendelse . '">';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Information</label>';
                echo '<input type="text" name="information" class="form-control" value="' . $information . '">';
            echo '</div>';
            echo '<div class="form-group">';
                echo '<label>Fængselstraf</label>';
                echo '<input type="text" name="prison" class="form-control" value="' . $prison . '">';
            echo '</div>';
            echo '<div class="form-group" id="afdeling">';
                echo '<label>Bødetasktemne</label>';
                echo '<select name="emner" class="form-control">';
                    while($row = $ticketResult->fetch_assoc()) {
                        if($row["ticketemne"] == $emne) {
                            echo '<option value="' . $row["ticketemne"] .'" selected>' . $row["ticketemne"] . '</option>';
                        } else {
                            echo '<option value="' . $row["ticketemne"] .'">' . $row["ticketemne"] . '</option>';
                        }
                    }
                echo '</select>';
            echo '</div>';
            echo '<input type="submit" style="margin-right: 10px; margin-bottom: 20px;" class="btn btn-primary" value="Opdatere">';
            echo '<a class="btn btn-danger" style="margin-left: 10px; margin-bottom: 20px;" href="manageTicket.php?ticketid=' . $ticketid . '&delete=' . $ticketid . '"> Slet Bøde</a>';
        echo '</form>';
    echo '</div>';
echo '</main>';

include '../../footer.php';
?>
