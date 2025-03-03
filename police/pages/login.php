<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dailyreport.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
$afdeling_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err) && empty($afdeling_err)) {
        $sql = "SELECT id, username, password, firstname, lastname, job, role, afdeling, WebsiteAdmin, licenses, phone_number, steamid, hasGangAccess, hasPdfPrivilege FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $firstname, $lastname, $job, $role, $afdeling, $WebsiteAdmin, $licenses, $phone_number, $steamid, $hasGangAccess, $hasPdfPrivilege);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Initialize session and check login
                        if (password_verify($password, $hashed_password)) {
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["afdeling"] = $afdeling;
                            $_SESSION["websiteadmin"] = $WebsiteAdmin;
                            $_SESSION["firstname"] = $firstname;
                            $_SESSION["lastname"] = $lastname;
                            $_SESSION["licenses"] = $licenses;
                            $_SESSION["phone_number"] = $phone_number;
                            $_SESSION["steam_id"] = $steamid;
                            $_SESSION["hasGangAccess"] = $hasGangAccess;
                            $_SESSION["hasPdfPrivilege"] = $hasPdfPrivilege;
                            $_SESSION["job"] = 'police';

                            // Setup the webhook URL
                            $webhookurl = "";

                            // Prepare data
                            $full_name = "$username - $firstname $lastname";

                            // First payload with embed
                            $payload1 = json_encode([
                                "embeds" => [
                                    [
                                        "title" => "Login Registrering",
                                        "color" => 7506394,
                                        "fields" => [
                                            ["name" => "Bruger", "value" => $full_name, "inline" => false],
                                            ["name" => "Handling", "value" => "Logget ind på tabletten", "inline" => false],
                                            ["name" => "Tidspunkt", "value" => date("d/m/Y H:i:s"), "inline" => false]
                                        ],
                                        "footer" => [
                                            "text" => "Login fuldført"
                                        ]
                                    ]
                                ]
                            ]);



                            // cURL headers for sending JSON
                            $ch = curl_init($webhookurl);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            curl_setopt($ch, CURLOPT_POST, 1);

                            // First request: Send embed message
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload1);
                            curl_exec($ch);
                            if (curl_errno($ch)) {
                                // Error handling
                                echo 'Curl error: ' . curl_error($ch);
                            }

                            // Second request: Send plain text message
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload2);
                            curl_exec($ch);
                            if (curl_errno($ch)) {
                                // Error handling
                                echo 'Curl error: ' . curl_error($ch);
                            }

                            curl_close($ch);

                            // Redirect after successful login
                            header("location: dailyreport.php");
                            exit;

                        } else {
                            $password_err = "Det indtastede kodeord er ikke korrekt.";
                        }


                    }
                } else {
                    $username_err = "Ingen konto blev fundet med det nummer";
                }
            } else {
                echo "Oops! Der gik noget galt. Prøv venligst igen senere.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Close connection
    mysqli_close($link);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="shortcut icon" type="image/png" href="../../assets/img/logo.png" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="../../assets/css/police.css?v=3">
</head>

<body>
    <div class="login-form">
        <div class="wrapper">
            <img src="../../assets/img/logo_stort.png" alt="logo">
            <h2>Login</h2>
            <?php echo (!empty($afdeling_err)) ? '<p class="error">Du prøver at logge på den forkerte afdeling' : ''; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                    <label>Badge nummer</label>
                    <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                    <span class="help-block"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                    <label>Kodeord</label>
                    <input type="password" name="password" class="form-control">
                    <span class="help-block"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group" id="submit">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
            </form>
        </div>
        <div class="text-center mt-4">
            <a href="../../" class="btn btn-primary">Tilbage til start</a>
        </div>
    </div>

    <div class="front-page-footer">
        <h1>Provided and made alive by Stausi</h1>
        <h1>In collaboration with Riste</h1>
    </div>
</body>



</html>