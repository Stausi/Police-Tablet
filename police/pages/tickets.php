<?php
include '../header.php';

$stmt = $link->prepare("SELECT * FROM punishment ORDER BY order_number ASC");
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
    <div class="tickes-wrapper">
        <?php if($_SESSION["websiteadmin"]) { ?>
            <div class="afdeling-content">
                <h1>Bødetakster</h1>
                <div class="buttons">
                    <a href="/police/pages/admin/manageTicketEmne.php"><i class="fas fa-edit"></i> Håndtere Bødeemner</a>
                    <a href="/police/pages/admin/opretTicket.php"><i class="fas fa-plus-square"></i> Opret en ny Bøde</a>
                </div>
            </div>
            <div class="mid-line" style="margin-bottom: 20px"></div>
        <?php } ?>
        <div class="search-text">
            <h2 align="center">Straf søgning</h2>
        </div>
        <div class="krimi-search">
            <input autocomplete="off" type="text" name="search_text" id="search_text" placeholder="Indtast beskrivelse af bøden" class="form-control" />
        </div>
        <div id="result"></div>
    </div>
</main>

<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(function() {
            load_data();
        }, 500);

        function load_data(query) {
            var isAdmin = <?php echo $_SESSION["websiteadmin"]; ?>;

            $.ajax({
                url: "../fetch_tickets.php",
                method: "POST",
                data: { query:query, isAdmin: isAdmin },
                success: function(data) {
                    $('#result').html(data);
                    initializeTicketsCollapse();
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

        function initializeTicketsCollapse() {
            $(".tickets-header").each(function() {
                var sectionId = $(this).attr('id');
                var rowCount = $(this).parent('.tickets-emner').find('.table tbody tr').length;

                if (rowCount >= 1 && rowCount <= 5) {
                    $(this).removeClass("collapsed");
                } else {
                    var state = localStorage.getItem(sectionId);
                    if (state === "expanded") {
                        $(this).removeClass("collapsed");
                    } else {
                        $(this).addClass("collapsed");
                    }
                }
            });

            $(".tickets-header h2").off("click").on("click", function() {
                var section = $(this).closest('.tickets-header');
                var sectionId = section.attr('id');
                
                var isVisible = section.toggleClass("collapsed").hasClass("collapsed");
                localStorage.setItem(sectionId, isVisible ? "collapsed" : "expanded");
            });
        }
    });
</script>

<?php
include '../footer.php';
?>