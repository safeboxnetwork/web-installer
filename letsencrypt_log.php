<?php
include "functions.php";

$letsencrypt = check_letsencrypt();
if (!empty($letsencrypt[$_GET["domain"]])) {
        echo "<pre>";
        echo base64_decode($letsencrypt[$_GET["domain"]]["log"]);
        echo "</pre>";
}
?>
