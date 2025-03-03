<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /ems/pages/login.php");
    exit;
}

if($_SESSION["job"] != 'ems'){
    header("location: ../index.html");
    exit;
}
?>

<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Stausi Database</title>
    <meta name="description" content="Stausi-Data">
    <meta name="author" content="Stausi">

    <link rel="shortcut icon" type="image/png" href="/assets/img/logo.png"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/slate/bootstrap.min.css" integrity="sha256-9+U4iiMDdq/mEURZxvY8e7AA/e0/iQWTh1tSDjgHNH8=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />

    <link rel="stylesheet" href="/assets/css/ems_dark.css?v=1.03">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/da.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.10.8/dist/sweetalert2.all.min.js"></script>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">

    <script>(function(e,t,n){var r=e.querySelectorAll("html")[0];r.className=r.className.replace(/(^|s)no-js(s|$)/,"$1js$2")})(document,window,0);</script>
</head>

<body>

    <header>
        <div class="header-wrapper">
            <div class="logo-container">
                <a href="/ems/pages/employed.php"><img src="/assets/img/ems-logo.png" alt="logo"></a>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a class="nav-link" href="/ems/pages/employed.php"><i class="fa fa-users"></i> Ansatte</a></li>
                    <li><a class="nav-link" href="/ems/pages/journal.php"><i class="fa fa-child"></i> Journal Arkiv</a></li>

                    <?php
                        if ($_SESSION["hasPsykologAccess"] || $_SESSION["websiteadmin"]) {
                            echo '<li><a class="nav-link" href="/ems/pages/journal_psyk.php"><i class="fas fa-guitar"></i> Psykolog Arkiv</a></li>';
                        }
                    ?>

                    <li><a class="nav-link" href="/ems/pages/fleet.php"><i class="fa fa-road"></i> Flådestyring</a></li>
                </ul>
            </nav>
            <div class="login">
                <ul class="login-links">
                    <li><a class="login-link" href="/ems/pages/profile.php"><i class="fas fa-user"></i> Min Profil</a></li>
                    <li><a class="login-link" href="/ems/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Log ud</a></li>
                </ul>
            </div>
        </div>
    </header>