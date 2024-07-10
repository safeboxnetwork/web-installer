<?php

switch ($_GET["op"]) {
	"redis":
		echo ping_redis();
	"docker":
		echo true;
	break;

}

?>
