<?php
ini_set('session.save_path', '/tmp');

session_start();

if (file_exists("/tmp/.htpasswd")) {
        if (isset($_SESSION["username"])) echo $_SESSION["username"];
        else echo "";
}
else echo "NOAUTH";

?>
