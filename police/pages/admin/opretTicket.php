<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


// Define variables and initialize with empty values
$paragraf = $sigtelse = $ticket = $klip = $frakendelse = $information = $prison = $emne = "";
$paragraf_err = $sigtelse_err = $ticket_err = $klip_err = $frakendelse_err = $information_err = $prison_err = $emne_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(empty(trim($_POST["paragraf"]))){
        $paragraf_err = "Indtast venligst en paragraf.";
    } else{
        $paragraf = trim($_POST["paragraf"]);
    }

    if(empty(trim($_POST["sigtelse"]))){
        $sigtelse_err = "Indtast venligst en sigtelse.";
    } else{
        $sigtelse = trim($_POST["sigtelse"]);
    }

    if(empty(trim($_POST["ticket"]))){
        $ticket_err = "Indtast venligst en bøde.";
    } else{
        $ticket = trim($_POST["ticket"]);
    }

    if(empty(trim($_POST["information"]))){
        $information = "Indtast venligst informationer.";
    } else{
        $information = trim($_POST["information"]);
    }

    if(!empty(trim($_POST["klip"]))){
        $klip = trim($_POST["klip"]);
    }

    if(!empty(trim($_POST["frakendelse"]))){
        $frakendelse = trim($_POST["frakendelse"]);
    }

    if(!empty(trim($_POST["prison"]))){
        $prison = trim($_POST["prison"]);
    }

    if(empty(trim($_POST["emner"]))){
        $emne_err = "Vælg venligst et emne";     
    } else{
        $emne = trim($_POST["emner"]);
    }

    // Check input errors before inserting in database
    if(empty($afdeling_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO tickets (emne, paragraf, sigtelse, ticket, klip, frakendelse, information, prison) VALUES (?,?,?,?,?,?,?,?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssissss", $param_emne, $param_paragraf, $param_sigtelse, $param_ticket, $param_klip, $param_frakendelse, $param_information, $param_prison);
            
            // Set parameters
            $param_emne = $emne;
            $param_paragraf = $paragraf;
            $param_sigtelse = $sigtelse;
            $param_ticket = $ticket;
            $param_klip = $klip;
            $param_frakendelse = $frakendelse;
            $param_information = $information;
            $param_prison = $prison;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: ../tickets.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<main>
    <div class="create-afdeling">
        <h2>Opret en ny Straf</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($paragraf_err)) ? 'has-error' : ''; ?>">
                <label>Paragraf</label>
                <input type="text" name="paragraf" class="form-control" value="<?php echo $paragraf; ?>">
                <span class="help-block"><?php echo $paragraf_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($sigtelse_err)) ? 'has-error' : ''; ?>">
                <label>Sigtelse</label>
                <input type="text" name="sigtelse" class="form-control" value="<?php echo $sigtelse; ?>">
                <span class="help-block"><?php echo $sigtelse_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($ticket_err)) ? 'has-error' : ''; ?>">
                <label>Bøde</label>
                <input type="number" name="ticket" class="form-control" value="<?php echo $ticket; ?>">
                <span class="help-block"><?php echo $ticket_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($klip_err)) ? 'has-error' : ''; ?>">
                <label>Klip</label>
                <input type="text" name="klip" class="form-control" value="<?php echo $klip; ?>">
                <span class="help-block"><?php echo $klip_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($frakendelse_err)) ? 'has-error' : ''; ?>">
                <label>Frakendelse</label>
                <input type="text" name="frakendelse" class="form-control" value="<?php echo $frakendelse; ?>">
                <span class="help-block"><?php echo $frakendelse_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($information_err)) ? 'has-error' : ''; ?>">
                <label>Information</label>
                <textarea name="information" class="form-control" value="<?php echo $information; ?>"></textarea>
                <span class="help-block"><?php echo $information_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($prison_err)) ? 'has-error' : ''; ?>">
                <label>Fængselstraf</label>
                <input type="text" name="prison" class="form-control" value="<?php echo $prison; ?>">
                <span class="help-block"><?php echo $prison_err; ?></span>
            </div>
            <div class="form-group" id="afdeling">
                <label>Bødetasktemne</label>
                <select name="emner" class="form-control">
                    <?php
                    $sql = "SELECT * FROM punishment";
                    $result = $link->query($sql);

                    while($row = $result->fetch_assoc()) {
                    ?>
                        <option value="<?php echo $row['ticketemne'] ?>"><?php echo $row['ticketemne'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group" id="submit">
                <input type="submit" class="btn btn-primary" value="Opret">
            </div>
        </form>
    </div>
</main>

<?php
include '../../footer.php';
?>