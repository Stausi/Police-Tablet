<?php
include '../header.php';

if($_SESSION["afdeling"] == "Advokatledelse") {
    header("location: /police/pages/employed.php");
    exit;
}

$dato = $name = $sex = $phone = $height = "";
$dato_err = $name_err = $sex_err = $phone_err = $height_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["check"])){
    if(empty(trim($_POST["name"]))){
        $name_err = "Indtast venligst et fornavn.";
    } else{
        $name = trim($_POST["name"]);
    }

    if(empty(trim($_POST["dato"]))){
        $dato_err = "Indtast venligst et fødselsdato.";
    } else{
        $dato = trim($_POST["dato"]);
    }

    if(empty(trim($_POST["sex"]))){
        $sex_err = "Indtast venligst et køn.";
    } else{
        $sex = trim($_POST["sex"]);
    }

    if(empty(trim($_POST["phone"]))){
        $phone_err = "Indtast venligst et telefon nummer.";
    } else{
        $phone = trim($_POST["phone"]);
    }

    if(empty(trim($_POST["height"]))){
        $height_err = "Indtast venligst en højde.";
    } else{
        $height = trim($_POST["height"]);
    }
    
    if(empty($name_err) && empty($dato_err) && empty($sex_err) && empty($phone_err) && empty($height_err)) {
        $sql = "INSERT INTO population (name, dob, sex, height, phone_number, note) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssiis", $param_name, $param_dob, $param_sex, $param_height, $param_phone, $param_note);
            
            $param_name = $name;
            $param_dob = $dato;
            $param_sex = $sex;
            $param_height = $height;
            $param_phone = $phone;
            $param_note = "";
            
            if(mysqli_stmt_execute($stmt)) {
                $last_id = mysqli_insert_id($link);
                header("location: player.php?player=" . $last_id);
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($link);
}

$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;
?>

<main>
    <div class="krimi">
        <div class="search-text">
            <h2 align="center">Kriminalregister søgning</h2>
            <button class="no-users btn-custom" data-toggle="modal" data-target="#createUser">
                <h3>Kan du ikke finde brugeren? Tryk her for at oprette.</h3>
            </button>
        </div>

        <div class="krimi-search">
            <div class="form-group">
                <div class="input-group">
                    <input autocomplete="off" type="text" name="search_text" id="search_text" placeholder="Indtast Navn eller Telefon nummer" class="form-control" />
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
                            <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                                <label>Fulde navn</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                                <span class="help-block"><?php echo $name_err; ?></span>
                            </div>
                            <div class="form-group" id="afdeling">
                                <label>Køn</label>
                                <select name="sex" class="form-control">
                                    <option value="Mand">Mand</option>
                                    <option value="Kvinde">Kvinde</option>
                                    <option value="Andet">andet</option>
                                </select>
                            </div>
                            <div class="form-group <?php echo (!empty($phone_err)) ? 'has-error' : ''; ?>">
                                <label>Telefon nummer</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>">
                                <span class="help-block"><?php echo $phone_err; ?></span>
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
                    url:"../fetch.php",
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