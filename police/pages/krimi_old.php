<?php
include '../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


$dato = $firstname = $lastname = $sex = $height = "";
$dato_err = $firstname_err = $lastname_err = $sex_err = $height_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["check"])){
    if(empty(trim($_POST["firstname"]))){
        $firstname_err = "Indtast venligst et fornavn.";
    } else{
        $firstname = trim($_POST["firstname"]);
    }

    if(empty(trim($_POST["lastname"]))){
        $lastname_err = "Indtast venligst et efternavn.";
    } else{
        $lastname = trim($_POST["lastname"]);
    }

    if(empty(trim($_POST["height"]))){
        $height_err = "Indtast venligst en højde.";
    } else{
        $height = trim($_POST["height"]);
    }

    if(empty(trim($_POST["sex"]))){
        $sex_err = "Indtast venligst et køn.";
    } else{
        $sex = trim($_POST["sex"]);
    }

    if(empty(trim($_POST["dato"]))){
        $dato_err = "Indtast venligst et fødselsdato.";
    } else{
        $dato = trim($_POST["dato"]);
    }
    
    // Check input errors before inserting in database
    if(empty($firstname_err) && empty($lastname_err) && empty($height_err) && empty($dato_err)) {
        $sql = "INSERT INTO krimi (firstname, lastname, dateofbirth, sex, height) VALUES (?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $param_firstname, $param_lastname, $param_dateofbirth, $param_sex, $param_height);
            
            $param_firstname = $firstname;
            $param_lastname = $lastname;
            $param_dateofbirth = $dato;
            $param_sex = $sex;
            $param_height = $height;
            
            if(mysqli_stmt_execute($stmt)) {
                $last_id = mysqli_insert_id($link);
                header("location: player.php?player=" . $last_id);
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
    <div class="krimi">
        <div class="search-text">
            <h2 align="center">Kriminalregister søgning</h2>
        </div>
        <div class="krimi-search">
            <div class="form-group">
                <div class="input-group">
                    <input autocomplete="off" type="text" name="search_text" id="search_text" placeholder="Indtast Fornavn eller Efternavn" class="form-control" />
                </div>
            </div>
        </div>
        <div id="result"></div>

        <div class="modal fade" id="createUser" tabindex="-1" role="dialog" aria-labelledby="createUserLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Opret Bruger</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h2>Opret en ny person</h2>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="form-group <?php echo (!empty($dato_err)) ? 'has-error' : ''; ?>">
                                <label>Fødselsdato</label>
                                <input autocomplete="off" type="text" name="dato" class="form-control datetimepicker-input" id="dato" data-toggle="datetimepicker" data-target="#dato"/>
                            </div>
                            <div class="form-group <?php echo (!empty($firstname_err)) ? 'has-error' : ''; ?>">
                                <label>Fornavn</label>
                                <input type="text" name="firstname" class="form-control" value="<?php echo $firstname; ?>">
                                <span class="help-block"><?php echo $firstname_err; ?></span>
                            </div>
                            <div class="form-group <?php echo (!empty($lastname_err)) ? 'has-error' : ''; ?>">
                                <label>Efternavn</label>
                                <input type="text" name="lastname" class="form-control" value="<?php echo $lastname; ?>">
                                <span class="help-block"><?php echo $lastname_err; ?></span>
                            </div>
                            <div class="form-group" id="afdeling">
                                <label>Køn</label>
                                <select name="sex" class="form-control">
                                    <option value="Mand">Mand</option>
                                    <option value="Kvinde">Kvinde</option>
                                    <option value="Andet">andet</option>
                                </select>
                            </div>
                            <div class="form-group <?php echo (!empty($height_err)) ? 'has-error' : ''; ?>">
                                <label>Højde</label>
                                <input type="text" name="height" class="form-control" value="<?php echo $height; ?>">
                                <span class="help-block"><?php echo $height_err; ?></span>
                            </div>
                            <div class="form-group" id="submit">
                                <input type="hidden" name="check" value="createNew">
                                <input type="submit" class="btn btn-primary" value="Opret">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk menuen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            load_data();

            function load_data(query) {
                $.ajax({
                    url:"../fetch_old.php",
                    method:"POST",
                    data:{
                        query:query
                    },
                    success:function(data) {
                        $('#result').html(data);
                    }
                });
            }

            $('#search_text').keyup(function(){
                var search = $(this).val();
                if(search != '') {
                    load_data(search);
                } else {
                    load_data();
                }
            });

            $('#dato').datetimepicker({
                locale: 'da',
                icons: {
                    time: "fas fa-clock",
                    date: "fa fa-calendar",
                    up: "fa fa-arrow-up",
                    down: "fa fa-arrow-down"
                }
            });
        });
    </script>
</main>

<?php
include '../footer.php';
?>