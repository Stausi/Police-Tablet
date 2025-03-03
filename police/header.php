<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /police/pages/login.php");
    exit;
}


if ($_SESSION["job"] != 'police') {
    header("location: ../index.html");
    exit;
}

function hasLicense($link, $username, $license)
{
    $sql = "SELECT * FROM users WHERE id='" . $username . "'";
    $result = $link->query($sql);

    $licenses = "";

    while ($row = $result->fetch_assoc()) {
        $licenses = $row['licenses'];
    }

    $licenseArray = json_decode($licenses, true);

    if (is_array($licenseArray)) {
        foreach ($licenseArray as $k => $v) {
            foreach ($v as $value) {
                if ($value == $license) {
                    return true;
                }
            }
        }

        return false;
    }
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title>Stausi Database</title>
    <meta name="description" content="Stausi-Data">
    <meta name="author" content="Stausi">

    <link rel="shortcut icon" type="image/png" href="/assets/img/logo.png" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/slate/bootstrap.min.css"
        integrity="sha256-9+U4iiMDdq/mEURZxvY8e7AA/e0/iQWTh1tSDjgHNH8=" crossorigin="anonymous">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />

    <link rel="stylesheet" href="/assets/css/police_dark.css?v=5.3">
    <link rel="stylesheet" href="/assets/css/users.css?v=1.06">
    <link rel="stylesheet" href="/assets/css/player.css?v=1.08">

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/da.js"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.10.8/dist/sweetalert2.all.min.js"></script>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">

    <script>(function (e, t, n) { var r = e.querySelectorAll("html")[0]; r.className = r.className.replace(/(^|s)no-js(s|$)/, "$1js$2") })(document, window, 0);</script>
</head>

<body>

    <header>
        <div class="header-wrapper">
            <div class="logo-container">
                <a href="/police/pages/employed.php"><img src="/assets/img/police-logo.png" alt="logo"></a>
            </div>
            <nav>
                <ul class="nav-links">
                    <!-- Altid synlige links -->

                    <li><a class="nav-link" href="/police/pages/employed.php"><i class="fa fa-users"></i> Ansatte</a>
                    </li>

                    <li>
                        <div class="dropdown">
                            <button class="dropbtn" onmouseover="openMenuDropdown()"
                                onclick="window.location.href='/police/pages/tickets.php'">
                                <i class="fa fa-credit-card"></i> Bødetakster
                            </button>
                            <div class="dropdown-content" id="menuDropdown">
                                <a href="/police/pages/statistics.php"><i class="fa fa-chart-bar"></i> Statistikker</a>
                            </div>
                        </div>
                    </li>



                    <!-- Vis 'Kriminalregister' for alle undtagen 'Advokatledelse' -->
                    <?php if (!isset($_SESSION['afdeling']) || $_SESSION['afdeling'] != "Advokatledelse"): ?>
                        <li><a class="nav-link" href="/police/pages/krimi.php"><i class="fa fa-child"></i>
                                Kriminalregister</a></li>
                    <?php endif; ?>

                    <!-- Vis for alle undtagen 'Advokatledelse' og 'Dommer' medmindre man er dommer og har id 4 -->
                    <?php if (!isset($_SESSION['afdeling']) || ($_SESSION['afdeling'] != "Advokatledelse" && $_SESSION['afdeling'] != "Dommer") || $_SESSION['username'] == 199): ?>
                        <li><a class="nav-link" href="/police/pages/dailyreport.php"><i class="fa fa-book"></i>
                                Opslagstavle</a></li>
                        <li>
                            <div class="dropdown">
                                <button class="dropbtn" onclick="window.location.href='/police/pages/wanted.php'">
                                    <i class="fa fa-ban"></i> Efterlysninger
                                </button>
                                <div class="dropdown-content" id="wantedDropdown">
                                    <a href="/police/pages/wanted_vehicles.php"><i class="fa fa-car-side"></i> Efterlyste
                                        køretøjer</a>
                                </div>
                            </div>
                        </li>


                        <?php
                        if ($_SESSION["hasGangAccess"]) {
                            echo '<li><a class="nav-link" href="/police/pages/gangs.php"><i class="fas fa-cannabis"></i> Bander</a></li>';
                        }
                        ?>

                    <?php endif; ?>
                            <!-- Vis for alle undtagen 'Advokatledelse' -->
                    <?php if (!isset($_SESSION['afdeling']) || $_SESSION['afdeling'] != "Advokatledelse"): ?>
                    <li><a class="nav-link" href="/police/pages/fleet.php"><i class="fas fa-road"></i> Flådestyring</a>
                        </li>

                    <?php endif; ?>
                </ul>
            </nav>
            <div class="login">
                <ul class="login-links">
                    <li><a class="login-link" href="/police/pages/profile.php"><i class="fas fa-user"></i> Min
                            Profil</a></li>
                    <li><a class="login-link" href="/police/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Log
                            ud</a></li>
                </ul>
            </div>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var menuButton = document.querySelector('#menuDropdown').parentNode.querySelector('.dropbtn');
        var menuDropdown = document.getElementById("menuDropdown");
        var policeButton = document.querySelector('#wantedDropdown').parentNode.querySelector('.dropbtn');
        var wantedDropdown = document.getElementById("wantedDropdown");

        function toggleDropdown(dropdown) {
            dropdown.classList.toggle("show");
        }

        function hideDropdown(dropdown) {
            dropdown.classList.remove("show");
        }

        menuButton.onmouseover = function () {
            toggleDropdown(menuDropdown);
        };
        menuButton.onmouseleave = function (event) {
            setTimeout(function () {  
                if (!menuDropdown.contains(event.relatedTarget)) {
                    hideDropdown(menuDropdown);
                }
            }, 300);
        };
        menuDropdown.onmouseleave = function (event) {
            setTimeout(function () {
                if (!menuButton.contains(event.relatedTarget)) {
                    hideDropdown(menuDropdown);
                }
            }, 300);
        };

        policeButton.onclick = function () {
            window.location.href = '/police/pages/wanted.php';  
        };
        policeButton.onmouseover = function () {
            toggleDropdown(wantedDropdown); 
        };
        policeButton.onmouseleave = function (event) {
            setTimeout(function () {
                if (!wantedDropdown.contains(event.relatedTarget)) {
                    hideDropdown(wantedDropdown);
                }
            }, 300);
        };
        wantedDropdown.onmouseleave = function (event) {
            setTimeout(function () {
                if (!policeButton.contains(event.relatedTarget)) {
                    hideDropdown(wantedDropdown);
                }
            }, 300);
        };

        window.onclick = function (event) {
            if (!event.target.matches('.dropbtn')) {
                hideDropdown(menuDropdown);
                hideDropdown(wantedDropdown);
            }
        };
    });
</script>
