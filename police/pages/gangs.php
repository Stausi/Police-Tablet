<?php
include '../header.php';

// Sikkerhed for at sikre, at kun admins eller brugere med gang adgang kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;
$hasGangAccess = $_SESSION["hasGangAccess"] ?? false;

if (!$isWebsiteAdmin && !$hasGangAccess) {
    header("location: /police/pages/employed.php");
    exit;
}


$sql = "SELECT * FROM gangs ORDER BY order_number ASC";
$result = $link->query($sql);
?>

<main>
    <div class="gangs">
        <div class="gangs-content">
            <h1>Bander</h1>
            <div class="buttons">
                <a href="/police/pages/admin/manageGangs.php"><i class="fas fa-edit"></i> Håndtere Bander</a>
            </div>
        </div>
        <div class="mid-line" style="margin-bottom: 20px"></div>
        <?php
            while($row = $result->fetch_assoc()) {
                echo '<div class="afdelinger">';
                    echo '<div class="afdeling" id="' . $row["gang_name"] . '">';
                        echo '<div class="gang-header">';
                            echo '<h2>' . $row["gang_name"] . '</h2>';
                            echo '<h2 class="created">Oprettet af: ' . $row["created_by"] . '</h2>';
                        echo '</div>';

                        $usersql = "SELECT * FROM population WHERE gang = '" . $row['id'] . "' ORDER BY name ASC";
                        $userresult = $link->query($usersql);

                        echo '<div class="gang-users">';
                            while($userrow = $userresult->fetch_assoc()) {
                                echo '<a href="player.php?player=' . $userrow['id'] . '">';
                                    echo '<i class="fas fa-plus"></i><h3>' . $userrow['name'] . ' - ' . $userrow['dob'] . ' - ' . $userrow['phone_number'] . '</h3>';
                                echo '</a>';
                            }
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }
        ?>
    </div>
</main>

<?php
include '../footer.php';
?>