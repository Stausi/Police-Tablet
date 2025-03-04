<?php
ob_start();

session_start();
header('Set-Cookie: PHPSESSID= ' . session_id() . '; SameSite=None; Secure');
header('content-type: text/html; charset=utf8mb4');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'name');
define('STEAM_API_KEY', 'key');

define('RCON_ENABLED', false);
define('RCON_ADDRESS', '');
define('RCON_PORT', '');
define('RCON_PASSWORD', '');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

if (!mysqli_set_charset($link, "utf8")) {
    printf("Error loading character set utf8: %s\n", mysqli_error($link));
    exit();
}

require_once 'vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/init.php';

$departments = [
    'ledelse' => 'Rigspolitiet',
    'east' => 'Østkredsen',
    'west' => 'Vestkredsen',
    'north' => 'Nordkredsen',
    'none' => 'Udenfor Kategori',
];
?>
