<?php
ini_set('session.save_path', '/tmp');

session_start();

unset($_SESSION["username"]);
session_destroy();

header('Location: signin.html');
exit();
?>