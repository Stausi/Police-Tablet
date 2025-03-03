<?php
include '../header.php';

// Sikkerhed for at forhindre advokater at tilgå denne side.
if($_SESSION["afdeling"] == "Advokatledelse") {
    header("location: /police/pages/employed.php");
    exit;
}


$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;
?>

<main>
    <div class="krimi">
        <div class="search-text">
            <h2 align="center">Kriminalregister søgning</h2>

            <?php if($isWebsiteAdmin) { ?>
                <a style="text-align:center;" href="krimi_old.php">Gå til gamle database</a>
            <?php } ?>
        </div>

        <div class="krimi-search">
            <div class="form-group">
                <div class="input-group">
                    <input autocomplete="off" type="text" name="search_text" id="search_text" placeholder="Indtast Navn eller Telefon nummer" class="form-control" />
                </div>
            </div>
        </div>

        <div id="result"></div>
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
        });
    </script>
</main>

<?php
include '../footer.php';
?>