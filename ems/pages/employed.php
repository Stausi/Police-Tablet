<?php
include '../header.php';

$sql = "SELECT * FROM afdelinger_ems ORDER BY order_number ASC";
$result = $link->query($sql);

$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;
?>

<main>
    <div class="employed">
        <?php if($isWebsiteAdmin) { ?>
            <div class="afdeling-content">
                <h1>Ansatte</h1>
                <div class="buttons">
                    <a href="/ems/pages/admin/opretBruger.php"><i class="fas fa-plus-square"></i> Opret en ny ansat</a>
                    <a href="/ems/pages/admin/manageAfdeling.php"><i class="fas fa-edit"></i> Håndtere afdelinger</a>
                </div>
            </div>
            <div class="mid-line" style="margin-bottom: 20px"></div>
        <?php } ?>
        <?php while($row = $result->fetch_assoc()) { ?>
            <div class="afdelinger">
                <div class="afdeling" id="<?php echo $row['afdeling'] ?>">
                    <h2><?php echo $row['afdeling'] ?></h2>
                    <?php
                        $usersql = "SELECT * FROM users_ems WHERE afdeling = '" . $row['afdeling'] . "' AND only_admin = 0 ORDER BY username ASC";
                        $userresult = $link->query($usersql);
                    ?>
                    <div class="users">
                        <?php while($userrow = $userresult->fetch_assoc()) { ?>
                            <?php if($_SESSION["websiteadmin"]) { ?>
                                <a class="user-popup" href="<?php echo "/ems/pages/admin/user.php?user=" . $userrow['id'] ?>">
                                    <i class="fas fa-plus"></i><h3><?php echo $userrow['username'] . ' - ' . $userrow['firstname'] . " " . $userrow['lastname'] ?></h3>
                            </a>
                            <?php } else { ?>
                                <button class="user-popup">
                                    <i class="fas fa-plus"></i><h3><?php echo $userrow['username'] . ' - ' . $userrow['firstname'] . " " . $userrow['lastname'] ?></h3>
                                </button>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</main>

<?php
include '../footer.php';
?>