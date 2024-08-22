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
	case "system":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "system"; //"init:".date("YmdHis");
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_system":
		$arr = check_redis("web_out","system");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="system") {
					if ($data["INSTALL_STATUS"]==2) echo "NEW";
					elseif ($data["INSTALL_STATUS"]==1) {
						if ($_GET["services"]==1) {
							$deployments = "";
							foreach ($data["INSTALLED_SERVICES"] as $service_name => $content) {
								//echo base64_decode($content);
								echo $service_name."<br>";
							}
							echo $deployments."<br>";
						}
						else echo "EXISTS";
					}
					redis_remove("$key");
				}

			}
		}
		else echo "WAIT";
		$arr = check_redis("web_out","repositories");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
			}
		}
		else echo "";
	break;
	case "deployments":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "deployments";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "repositories":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "repositories";
		redis_set($op,$json);
		echo "OK"; // TODO?

	case "check_repositories":
		$arr = check_redis("web_out","repositories");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="repositories") {
					echo base64_decode($data["RESULT"]);
					redis_remove("$key");
				}
			}
		}
		else echo "";
	break;
	case "containers":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "containers";
		redis_set($op,$json);
		echo "OK"; // TODO?

	case "check_containers":
		$arr = check_redis("web_out","containers");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="containers") {
					echo base64_decode($data["RESULT"]);
					redis_remove("$key");
				}
			}
		}
		else echo "";
	break;
	case "docker":
		echo true;
	break;

}

?>
