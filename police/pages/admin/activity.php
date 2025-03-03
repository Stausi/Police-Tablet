<?php
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}



$username = $_GET['user'] ?? '';
$link->real_escape_string($username);

$sql = "SELECT pc.*, p.name FROM population_cases pc LEFT JOIN population p ON pc.pid = p.id WHERE pc.userid = ?";

$stmt = $link->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$username_string = "";
$cases = [];

while ($row = $result->fetch_assoc()) {
    if (empty($username_string)) {
        $username_string = $row['username'];
    }
    $cases[] = $row;
}

$stmt->close();
?>

<main>
    <div class="activity">
        <h2 class="header"><?php echo htmlspecialchars($username_string); ?></h2>
        <table class="table table-striped table-hover">
            <tr>
                <th>Sagsnummer</th>
                <th>Dato</th>
                <th>Sigtet Person</th>
                <th>Sigtet for</th>
                <th>Bødestørrelse</th>
                <th>Fængselsstraf</th>
                <th>Klip</th>
            </tr>
            <?php foreach ($cases as $row): ?>
                <tr>
                    <td>Nr. <?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['dato']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['sigtet']); ?></td>
                    <td><?php echo htmlspecialchars($row['ticket']); ?>,- DKK</td>
                    <td><?php echo htmlspecialchars($row['prison']); ?> Måneder</td>
                    <td><?php echo htmlspecialchars($row['klip']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>

<?php
include '../../footer.php';
?>