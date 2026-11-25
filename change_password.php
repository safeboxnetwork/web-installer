<?php
include "functions.php";

// Ensure session files can be written (same workaround as signin.php)
ini_set('session.save_path', '/tmp');

session_start();

// Only for logged-in users
if (!isset($_SESSION["username"]) || $_SESSION["username"] === "") {
    echo "NOAUTH";
    exit;
}

$username = $_SESSION["username"];

$new = isset($_POST["AUTH_PASSWORD_NEW"]) ? $_POST["AUTH_PASSWORD_NEW"] : "";
$new2 = isset($_POST["AUTH_PASSWORD_NEW2"]) ? $_POST["AUTH_PASSWORD_NEW2"] : "";

if ($new === "") {
    echo "EMPTY";
    exit;
}

if ($new !== $new2) {
    echo "MISMATCH";
    exit;
}

// Send the new settings request to the scheduler, exactly like the install process does:
// it is written as /var/tmp/shared/input/settings.json
$arr = array(
    "USER_AUTH" => "yes",
    "AUTH_USERNAME" => $username,
    "AUTH_PASSWORD" => $new
);
$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

if (set_output("settings", $json)) {
    // Keep the web sign-in file in sync immediately, as install.php does
    $htpasswd = $username . ":" . password_hash($new, PASSWORD_DEFAULT);
    file_put_contents("/tmp/.htpasswd", $htpasswd);

    echo "OK";
}
else echo "ERROR";

?>