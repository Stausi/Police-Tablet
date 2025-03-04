<?php
include '../header.php';

$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;
$hasGangAccess = $_SESSION["hasGangAccess"] ?? false;

if (!$isWebsiteAdmin && !$hasGangAccess) {
    header("location: /police/pages/employed.php");
    exit;
}

$stmt = $link->prepare("SELECT id, gang_name, created_by FROM gangs ORDER BY order_number ASC");
$stmt->execute();
$result = $stmt->get_result();
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
        while ($row = $result->fetch_assoc()) {
            echo '<div class="afdelinger">';
                echo '<div class="afdeling" id="' . htmlspecialchars($row["gang_name"]) . '">';
                    echo '<div class="gang-header">';
                        echo '<h2>' . htmlspecialchars($row["gang_name"]) . '</h2>';
                        echo '<h2 class="created">Oprettet af: ' . htmlspecialchars($row["created_by"]) . '</h2>';
                    echo '</div>';

                    $usersql = "SELECT id, name, dob, phone_number FROM population WHERE gang = ? ORDER BY name ASC";
                    $userstmt = $link->prepare($usersql);
                    $userstmt->bind_param("s", $row['id']);
                    $userstmt->execute();
                    $userresult = $userstmt->get_result();

                    echo '<div class="gang-users">';
                        while ($userrow = $userresult->fetch_assoc()) {
                            echo '<a href="player.php?player=' . htmlspecialchars($userrow['id']) . '">';
                                echo '<i class="fas fa-plus"></i><h3>' . htmlspecialchars($userrow['name']) . ' - ' . htmlspecialchars($userrow['dob']) . ' - ' . htmlspecialchars($userrow['phone_number']) . '</h3>';
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