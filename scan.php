<?php
include "functions.php";
sleep(1);
switch ($_GET["op"]) {
	case "redis":
		try {
			$ret = ping_redis();
			if ($ret===false) {
				echo "Can't ping redis-server";
			}
			else echo "OK";
		} catch (RedisException $e) {
			echo "RedisException caught: " . $e->getMessage();
		}
	break;
	case "init":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "init:".date("YmdHis");
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_init":
		$arr = check_redis("web_out");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($data["INSTALL_STATUS"]==2) echo "NEW";
				elseif ($data["INSTALL_STATUS"]==1) echo "EXISTS";
				redis_remove("$key");
			}
		}
		else echo "WAIT";
	break;
	case "check_install":
		$arr = check_redis("web_out",$_GET["key"]);
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				//echo $key."-".$_GET["key"];
				if ($key==$_GET["key"]) { // if install key moved to web_out
					if ($data["INSTALL_STATUS"]>0) {
						redis_remove("$key");
						echo "INSTALLED";
					}
				}
			}
		}
		else echo "NOT EXISTS";
	break;
	case "docker":
		echo true;
	break;

}

?>
