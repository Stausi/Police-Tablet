<?php 
include '../header.php';

$fleet_categories = [
    'hospital' => 'Hospital',
    'falck' => 'Falck',
    'hems' => 'HEMS',
    'tems' => 'TEMS',
    'training' => 'Træning',
];

if(isset($_GET['user'])) {
    if(isset($_GET['move'])) {
        $sql = "UPDATE users_ems SET patrol_category = ? WHERE id = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_category, $param_id);

            $param_category = $_GET['move'];
            $param_id = $_GET['user'];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: fleet.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }

    if(isset($_GET['remove'])) {
        $sql = "UPDATE users_ems SET patrol_category = ? WHERE id = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_category, $param_id);

            $param_category = NULL;
            $param_id = $_GET['user'];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: fleet.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }

    if(isset($_GET['comment']) && isset($_GET['option'])) {
        $task = $_GET['comment'];
        if ($_GET['comment'] == '') {
            $task = $_GET['option'];
        }

        $sql = "UPDATE users_ems SET patrol_task = ? WHERE id = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_comment, $param_id);

            $param_comment = $task;
            $param_id = $_GET['user'];
            
            if(mysqli_stmt_execute($stmt)) {
                header("location: fleet.php");
            } else{
                echo "Something went wrong. Please try again later. <br>";
                printf("Error message: %s\n", $link->error);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

function makeKeyFriendly($string) {
    $string = strtolower($string);
    $string = str_replace(' ', '_', $string);
    $string = preg_replace('/[^A-Za-z0-9_]/', '', $string);
    return $string;
}

function formatCategories($categories, $current) {
    $formattedCategories = array();

    foreach($categories as $name => $label) {
        if (!isset($current) || $name != $current) {
            $formattedCategories[$name] = $label;
        }
    }

    asort($formattedCategories);

    return $formattedCategories;
}

$stmt = $link->prepare("SELECT * FROM users_ems where isOnDuty = 1 ORDER BY (patrol_id IS NULL), patrol_id ASC, username ASC");
$stmt->execute();
$result = $stmt->get_result();

$users = array();
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$categorized = array();
foreach($fleet_categories as $key => $value) {
    $license_emne = makeKeyFriendly($key);
    $categorized[$license_emne] = array();
}

$uncategorized = array();
foreach($users as $user) {
    $patrol_category = $user['patrol_category'];
    if($patrol_category == NULL) {
        $uncategorized[] = $user;
    }

    if (isset($categorized[$patrol_category])) {
        $categorized[$patrol_category][] = $user;
    }
}

foreach($categorized as $patrol_category => &$users) {
    usort($users, function($a, $b) {
        if ($a['patrol_id'] === $b['patrol_id']) {
            return 0;
        }
        if ($a['patrol_id'] === NULL) {
            return 1;
        }
        if ($b['patrol_id'] === NULL) {
            return -1;
        }
        return ($a['patrol_id'] < $b['patrol_id']) ? -1 : 1;
    });
}
unset($users);

echo "<main>";
    echo "<div class='fleet-wrapper'>";
        echo "<div class='fleet-uncategorized'>";
            echo "<h1>Ukategoriseret (" . sizeof($uncategorized) . ")</h1>";
            echo "<div class='fleet-uncategorized-list'>";
                foreach($uncategorized as $user) {
                    $formated_categories = formatCategories($fleet_categories, NULL);

                    echo "<div class='fleet-user-container'>";
                        echo "<div class='fleet-user-informations'>";
                            echo "<div class='fleet-user-info'>";
                                echo '<i class="fa-solid fa-user"></i>';
                                echo $user["username"] . " " . $user["firstname"] . " " . $user["lastname"];
                            echo "</div>";

                            if ($user["patrol_task"] != NULL && $user["patrol_task"] != "") {
                                echo "<div class='fleet-user-task'>";
                                    echo '<i class="fa-solid fa-bars-progress"></i><p>';
                                    echo $user["patrol_task"];
                                echo "</p></div>";
                            }

                            if ($user["patrol_id"] != NULL) {
                                echo "<div class='fleet-user-patrol'>";
                                    echo '<i class="fa-solid fa-car"></i>';
                                    echo "Patrulje nummer: " . $user["patrol_id"];
                                echo "</div>";
                            }
                        echo "</div>";
                        echo "<div class='fleet-user-icons'>";
                            if ($user["id"] == $_SESSION["id"] || $_SESSION["websiteadmin"]) {
                                echo '<button class="hoverBtn">';
                                    echo '<i class="fa-solid fa-list"></i>';
                                    echo '<div class="fleet-user-icons-content">';
                                        foreach($fleet_categories as $name => $license) {
                                            echo "<a href='fleet.php?user=" . $user['id'] . "&move=" . $name . "'>" . $license . "</a>";
                                        }
                                    echo '</div>';
                                echo '</button>';
                                echo '<button onclick="addCustomPlayerTask(' . $user['id'] . ')"><i class="fa-solid fa-pen-to-square"></i></button>';
                            }
                        echo "</div>";
                    echo "</div>";
                }
            echo "</div>";
        echo "</div>";

        echo "<div class='fleet-categorized'>";
            foreach($categorized as $key => $category_users) {
                $category_label = $fleet_categories[$key];

                echo "<div class='fleet-category' id='" . $key . "'>";
                    echo "<h1>" . $category_label . " (" . sizeof($category_users) . ")</h1>";
                    echo "<div class='fleet-categorized-list'>";
                        foreach($category_users as $user) {
                            $formated_categories = formatCategories($fleet_categories, $key);

                            echo "<div class='fleet-user-container'>";
                                echo "<div class='fleet-user-informations'>";
                                    echo "<div class='fleet-user-info'>";
                                        echo '<i class="fa-solid fa-user"></i>';
                                        echo $user["username"] . " " . $user["firstname"] . " " . $user["lastname"];
                                    echo "</div>";

                                    if ($user["patrol_task"] != NULL && $user["patrol_task"] != "") {
                                        echo "<div class='fleet-user-task'><p>";
                                            echo '<i class="fa-solid fa-bars-progress"></i>';
                                            echo $user["patrol_task"];
                                        echo "</p></div>";
                                    }

                                    if ($user["patrol_id"] != NULL) {
                                        echo "<div class='fleet-user-patrol'>";
                                            echo '<i class="fa-solid fa-car"></i>';
                                            echo "Patrulje nummer: " . $user["patrol_id"];
                                        echo "</div>";
                                    }
                                echo "</div>";
                                echo "<div class='fleet-user-icons'>";
                                    if ($user["id"] == $_SESSION["id"] || $_SESSION["websiteadmin"]) {
                                        echo '<button class="hoverBtn">';
                                            echo '<i class="fa-solid fa-list"></i>';
                                            echo '<div class="fleet-user-icons-content">';
                                                foreach($formated_categories as $name => $license) {
                                                    echo "<a href='fleet.php?user=" . $user['id'] . "&move=" . $name . "'>" . $license . "</a>";
                                                }
                                            echo '</div>';
                                        echo '</button>';
                                        echo '<button onclick="addCustomPlayerTask(' . $user['id'] . ')"><i class="fa-solid fa-pen-to-square"></i></button>';
                                        echo '<a href="fleet.php?user=' . $user["id"] . '&remove=true"><i class="fa-solid fa-xmark"></i></a>';
                                    }
                                echo "</div>";
                            echo "</div>";
                        }
                    echo "</div>";
                echo "</div>";
            }
        echo "</div>";
    echo "</div>";
?>

<script type="text/javascript">
function addCustomPlayerTask(id) {
    (async () => {
        const { value: formValues } = await Swal.fire({
            title: 'Indtast opgave',
            html:
                '<input id="swal-input1" class="swal2-input" placeholder="Opgave">' +
                '<select id="swal-input2" class="swal2-input">' +
                    '<option value="">Eller vælg en opgave</option>' +
                '</select>',
            focusConfirm: false,
            preConfirm: () => {
                return [
                    document.getElementById('swal-input1').value,
                    document.getElementById('swal-input2').value
                ]
            },
            showCancelButton: true
        });

        if (formValues) {
            const [comment, option] = formValues;
            window.location.href = "fleet.php?user=" + id + "&comment=" + encodeURIComponent(comment) + "&option=" + encodeURIComponent(option);
        }
    })();
}
</script>

<?php
echo "</main>";

include '../footer.php'; 
?>