<?php
include '../header.php';

$sql = "SELECT * FROM afdelinger ORDER BY order_number ASC";
$result = $link->query($sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

$isAdvocateOrJudge = in_array($_SESSION["afdeling"], ["Advokatledelse", "Dommer"]);


$usersql = "SELECT * FROM users WHERE only_admin = 0 ORDER BY username ASC";
$userresult = $link->query($usersql);

$users = [];
while ($userrow = $userresult->fetch_assoc()) {
    $users[$userrow['department']][$userrow['afdeling']][] = $userrow;
}
?>

<main>
    <div class="employed">
        <?php if ($isWebsiteAdmin) : ?>
            <div class="afdeling-content">
                <h1>Ansatte</h1>
                <div class="buttons">
                    <a href="/police/pages/admin/opretBruger.php"><i class="fas fa-plus-square"></i> Opret en ny betjent</a>
                    <a href="/police/pages/admin/manageAfdeling.php"><i class="fas fa-edit"></i> Håndtere afdelinger</a>
                </div>
            </div>
            <div class="mid-line" style="margin-bottom: 20px"></div>
        <?php endif; ?>

        <?php foreach ($departments as $department_key => $department_value) : ?>
            <div class="department-header">
                <h2><?php echo htmlspecialchars($department_value); ?></h2>
            </div>

            <?php if (isset($users[$department_key])) : ?>
                <?php foreach ($rows as $row) : ?>
                    <?php if (isset($users[$department_key][$row['afdeling']])) : ?>
                        <div class="afdelinger">
                            <div class="afdeling" id="<?php echo $row['afdeling']; ?>">
                                <h2><?php echo $row['afdeling']; ?></h2>
                                <div class="users">
                                    <?php foreach ($users[$department_key][$row['afdeling']] as $user) : ?>
                                        
                                            <button class="user-popup" data-toggle="modal" data-target="<?php echo "#userModal" . $user['username']; ?>">
                                            
                                                <i class="fas fa-plus"></i>
                                                <h3><?php echo $user['username'] . ' - ' . $user['firstname'] . " " . $user['lastname']; ?></h3>
                                            </button>
                                            <?php if (!$isAdvocateOrJudge) : ?>
                                        
                                           
                                        <?php endif; ?>

                                        <div class="modal fade" id="<?php echo "userModal" . $user['username']; ?>" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                <?php if (!$isAdvocateOrJudge) : ?>
                                                        <div class="modal-header">
                                                        
                                                            <h5 class="modal-title" id="exampleModalLabel"><?php echo $user['firstname'] . " " . $user['lastname'] . " - " . $user['role']; ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <?php
                                                            echo "<h2 class='number'>Badge nummer: " . $user['username'] . "</h2>";
                                                            $licenses = json_decode($user['licenses'], true);

                                                            if (is_array($licenses)) {
                                                                foreach ($licenses as $key => $values) {
                                                                    sort($values);
                                                                    echo "<h2>$key: </h2>";
                                                                    foreach ($values as $value) {
                                                                        echo "<p> - " . $value . "</p>";
                                                                    }
                                                                }
                                                            } else {
                                                                echo "<p>Betjenten har ingen licenser</p>";
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <?php if ($isWebsiteAdmin) : ?>
                                                                <a class="btn btn-success" href="/police/pages/admin/activity.php?user=<?php echo $user['id']; ?>">Tjek aktivitet</a>
                                                                <a class="btn btn-danger" href="/police/pages/admin/user.php?user=<?php echo $user['id']; ?>">Rediger Bruger</a>
                                                            <?php endif; ?>
                                                            <a class="btn btn-secondary" href="/police/pages/profile.php?user=<?php echo $user['id']; ?>">Tjek Profil</a>
                                                        </div>
                                                    <?php else : ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><?php echo $user['firstname'] . " " . $user['lastname']; ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</main>


<?php
include '../footer.php';
?>