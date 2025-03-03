<?php 
include '../../header.php';

// Sikkerhed for at sikre, at kun admins kan tilgå siden
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;

if (!$isWebsiteAdmin) {
    header("location: /police/pages/employed.php");
    exit;
}


function makeKeyFriendly($string) {
    $string = strtolower($string);
    $string = str_replace(' ', '_', $string);
    $string = preg_replace('/[^A-Za-z0-9_]/', '', $string);
    return $string;
}

function mergeObjectsRecursively($obj1, $obj2) {
    $baseObject = (array) $obj1;
    $mergeObject = (array) $obj2;
    $merged = array_merge_recursive($baseObject, $mergeObject);
    return (object) $merged;
}

$subjects = array();
$subjectSQL = "SELECT * FROM licenses_subjects";
$subjectResult = $link->query($subjectSQL);

while($subjectRow = $subjectResult->fetch_assoc()) {
    $subjects[] = $subjectRow;
}

$licenses = array();
$licensesSQL = "SELECT * FROM licenses";
$licensesResult = $link->query($licensesSQL);

while($licensesRow = $licensesResult->fetch_assoc()) {
    $licenses[] = $licensesRow;
}

$formated_licenses = [];
foreach($licenses as $license) {
    $name = makeKeyFriendly($license['license_name']);
    
    foreach($subjects as $subject) {
        if ($subject['license_emne'] == $license['subject']) {
            $formated_licenses[$name] = $subject['license_emne'];
        }
    }
}

$users = array();
$usersSQL = "SELECT id, licenses FROM users";
$usersResult = $link->query($usersSQL);

while($usersRow = $usersResult->fetch_assoc()) {
    $users[] = $usersRow;
}

$stmt = mysqli_prepare($link, "UPDATE users SET licenses = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $licenses_json, $user_id);

foreach($users as $user) {
    $licenses = json_decode($user['licenses'], true);

    if(isset($licenses)) {
        foreach($licenses as $emne => $emner) {
            foreach($emner as $key => $name) {
                $formated_name = makeKeyFriendly($name);
                if (isset($formated_licenses[$formated_name])) {
                    $license_emne = $formated_licenses[$formated_name];
                    if ($emne != $license_emne) {
                        unset($licenses[$emne][$key]);

                        if (!isset($licenses[$license_emne])) {
                            $licenses[$license_emne] = array();
                        }

                        $licenses[$license_emne][] = $name;
                    }
                } else {
                    unset($licenses[$emne][$key]);
                }

                if (empty($licenses[$emne])) {
                    unset($licenses[$emne]);
                }
            }

            if (isset($licenses[$emne])) {
                $licenses[$emne] = array_values($licenses[$emne]);
            }
        }

        $user_id = $user['id'];
        $licenses_json = json_encode($licenses, true | JSON_UNESCAPED_UNICODE);;
        
        if (!mysqli_stmt_execute($stmt)) {
            printf("Error updating user: %s\n", mysqli_stmt_error($stmt));
        }
    }
}

header("Location: ../employed.php"); 
exit();
?>