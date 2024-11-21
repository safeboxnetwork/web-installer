<?php
include "functions.php";

sleep(1);
switch ($_GET["op"]) {
	case "get_interface":
		echo $INTERFACE;
	break;
	case "directory":
		if (file_exists($SHARED_DIR)) {
			$test_file = $SHARED_DIR."/test";
			file_put_contents($test_file,"TEST");
			if (file_exists($test_file)) {
				echo "OK";
				unlink($test_file);
			}
			else echo "WRITE ERROR";
		}
		else echo "DIRECTORY DOESN'T EXISTS";
	break;
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

		if (set_output("system",$json)) echo "OK"; // TODO?
	break;
	case "check_system":
		$arr = check_response("system");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="system") {
					if ($data["INSTALL_STATUS"]==2) echo "NEW";
					elseif ($data["INSTALL_STATUS"]==1) {
						if ($_GET["services"]==1) {
							echo "<table><tr><td><b>Service/Container</b></td><td><b>Image</b></td><td><b>Status</b></td><td><b>Action</b></td></tr></table>";
							foreach ($data["INSTALLED_SERVICES"] as $service_name => $object) {
								//echo base64_decode($content);
								show_service($service_name, $object["running"]);
							}
							echo "<br>";
						}
						else echo "EXISTS";
					}
					remove_response("$key");
				}
			}
		}
		else echo "WAIT";
	break;
	case "services":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "services"; //"init:".date("YmdHis");
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_services":
		$arr = check_redis("web_out","services");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="services") {
					if ($data["INSTALL_STATUS"]==2) echo "NEW";
					elseif ($data["INSTALL_STATUS"]==1) {
						echo "<table><tr><td><b>Service/Container</b></td><td><b>Image</b></td><td><b>Status</b></td><td><b>Action</b></td></tr></table>";
						foreach ($data["INSTALLED_SERVICES"] as $service_name => $object) {
							//echo base64_decode($object["content"]);
							show_service($service_name, $object["running"]);
						}
						echo "<br>";
					}
					redis_remove("$key");
				}
			}
		}
		else echo "WAIT";
	break;
	case "updates":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "updates"; //"init:".date("YmdHis");
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_updates":
		$arr = check_redis("web_out","updates");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="updates") {
					if ($data["INSTALL_STATUS"]==1) {
						echo "<table><tr><td><b>Service/Container</b></td><td><b>Image</b></td><td><b>Status</b></td><td><b>Action</b></td></tr></table>";
						foreach ($data["INSTALLED_SERVICES"] as $service_name => $object) {
							show_service_update($service_name, trim($object["update"]), trim($object["uptodate"]));
						}
						echo "<br>";
					}
					redis_remove("$key");
				}
			}
		}
		else echo "WAIT";
	break;
	case "deployments":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "deployments";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_deployments":
		$arr = check_redis("web_out","deployments");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="deployments") {
					if (count($data["DEPLOYMENTS"])) {
						if ($data["DEPLOYMENTS"]["deployments"]=="NONE") echo "There are no deployments.<br>";
						else {
							foreach ($data["DEPLOYMENTS"] as $service_name => $content) {
								//echo base64_decode($content);
								if (array_key_exists($service_name,$data["INSTALLED_SERVICES"])) {
									echo '<div>'.$service_name.' - '.$content.' - INSTALLED - <a href="#" onclick="uninstall(\''.$service_name.'\')">UNINSTALL</a> - <a href="#" onclick="reinstall(\''.$service_name.'\')">REINSTALL</a></div>';
								}
								else echo '<div><a href="#" onclick="load_template(\''.$service_name.'\')">'.$service_name.'</a> - '.$content.'</div>';
								echo '<div id="'.$service_name.'"></div>';
							}
						}
					}
					else echo "There are no deployments.";
					echo "<br>";
/*
					if (count($data["INSTALLED_SERVICES"])) {
						echo "<br>Installed services:<br>";
						if ($data["INSTALLED_SERVICES"]["services"]=="NONE") echo "There are no installed services.<br>";
						else {
							foreach ($data["INSTALLED_SERVICES"] as $service_name => $content) {
								//echo base64_decode($content);
								echo $service_name."<br>";
							}
							echo "<br>";
						}
					}
					else echo "There are no installed services.<br>";
*/
					redis_remove("$key");
				}
			}
		}
		else echo "";
	break;
	case "deployment":
		$arr = array("NAME" => $_GET["additional"], "ACTION" => "ask");
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "deployment";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_reinstall":
		$reinstall = 1;
	case "check_deployment":
		$arr = check_redis("web_out","deployment");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="deployment") {
					if ($data["STATUS"]=="0") { // ask
						$template = json_decode(base64_decode($data["TEMPLATE"]));
						if ($reinstall) {
							var_dump($template);
						}
						echo "<fieldset><form action=\"#\" method=\"post\" id=\"deploy_form\"><br>";
						foreach ($template->fields as $field) {
							if (isset($field->generated)) {
								echo "<input type=\"hidden\" value=\"generated:{$field->generated}\" name=\"{$field->key}\" id=\"{$field->key}\" class=\"additional_field\">";
							}
							else {
							echo "<div class=\"row\"><div class=\"mb-3\"><label>".$field->description."</label>
								<input ".($field->required=="true" ? "required" : "")." type=\"".(!empty($field->type) ? $field->type : "text")."\" value=\"{$field->value}\" name=\"{$field->key}\" id=\"{$field->key}\" class=\"additional_field\">
							</div></div>";
							}
						}
						echo "
						<div class=\"row\">
						<div class=\"mb-3\">
						<input type=\"hidden\" value=\"{$template->name}\" id=\"additional\">
						<button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\" id=\"deploy_btn\">".($reinstall ? "Reinstall" : "Install")."</button>
						</div>
						</div>
						</form></fieldset>
<script>
	jQuery('#deploy_form').submit(function() {
		deploy(jQuery('#additional').val());
		return false;
	});
</script>
						";
					}
					elseif ($data["STATUS"]=="2") { // deploy
						echo "Install has finished.";
					}
					redis_remove("$key");
				}
			}
		}
		else {
			$arr = check_redis("web_in","deployment");
			if (!empty($arr)) { // deployment in progress
				foreach ($arr as $key=>$data) {
					if ($key=="deployment") {
						if ($data["STATUS"]=="1") { // TODO - curenct state message???
							echo "Install in progress... Please wait...";
						}
						elseif ($data["STATUS"]=="2") { 
							echo "Install has finished.";
						}
					}
				}
			}
			else echo ""; // no deployment, finished
		}
	break;
	case "deploy":
		if ($key=check_deploy()) { 
			$text="A deployment has already started.<br>Please wait and do not start a new one...";
		}
		else {
			$text="Install in progress... Please wait...";
			$fields = $_GET;
			unset($fields["op"]);
			unset($fields["additional"]);
			$algos = hash_algos();
			foreach ($fields as $field_key => $field_value) {
				$field_arr = explode(":",$field_value);
				if ($field_arr[0]=="generated") {
					if (intval($field_arr[3])==0) $len = 10; // default length
					else $len = $field_arr[3];

					if ($field_arr[1]=="random") $base = rand(100000,999999);
					elseif ($field_arr[1]=="time") $base = time();
					elseif ($field_arr[1]!="") $base = $field_arr[1]; // fix string
					else $base = rand(100000,999999); // default

					if (in_array($field_arr[2],$algos)) $base = hash($field_arr[2],$base);
					else $base = hash("md5",$base); // default alg

					$fields["$field_key"] = substr($base,0,$len);
				}
			}
			$payload = base64_encode(json_encode($fields, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
			$arr = array("NAME" => $_GET["additional"], "ACTION" => "deploy", "PAYLOAD" => $payload);
			$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
			$op = "deployment";
			redis_set($op,$json);
		}
		echo $text;
	break;
	case "reinstall":
		$arr = array("NAME" => $_GET["additional"], "ACTION" => "reinstall");
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "deployment";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "uninstall":
		if ($key=check_deploy()) { 
			$text="Deploy/uninstall process has already started.<br>Please wait and do not start a new one...";
		}
		else {
			$text="Uninstall in progress... Please wait...";
			$arr = array("NAME" => $_GET["additional"], "ACTION" => "uninstall");
			$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
			$op = "deployment";
			redis_set($op,$json);
		}
		echo $text;
	break;
	case "repositories":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "repositories";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "check_repositories":
		$arr = check_redis("web_out","repositories");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="repositories") {
					$repos = json_decode(base64_decode($data["REPOSITORIES"]));
					foreach ($repos->repositories as $repo) {
						echo $repo."<br>";
					}
					redis_remove("$key");
				}
			}
			redis_remove("add_repository");
		}
		else echo "";
	break;
	case "add_repository":
		redis_remove("add_repository");

		$arr = array("NEW_REPO" => $_GET["repo"]);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "add_repository";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
	case "containers":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		$op = "containers";
		redis_set($op,$json);
		echo "OK"; // TODO?
	break;
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
