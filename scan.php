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
	case "system":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("system",$json)) echo "OK";
		else echo "ERROR";
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
	case "check_install": // called in install.php - check if install process has finished
		$arr = check_response($_GET["key"]); // TODO - replace key with "install", key can be "install*"
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				//echo $key."-".$_GET["key"];
				if ($key==$_GET["key"]) { // if install key moved to web_out
					if ($data["INSTALL_STATUS"]>0) {
						remove_response("$key");
						echo "INSTALLED";
					}
				}
			}
		}
		else echo "NOT EXISTS"; // TODO - check if in progress or just not exists ???
	break;
	case "services":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("services",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "check_services":
		$arr = check_response("services");
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
					remove_response("$key");
				}
			}
		}
		else echo "WAIT";
	break;
	case "updates":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("updates",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "check_updates":
		$arr = check_response("updates");
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
					remove_response("$key");
				}
			}
		}
		else echo "WAIT";
	break;
	case "deployments":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("deployments",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "check_deployments":
		$arr = check_response("deployments");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="deployments") {
					if (count($data["DEPLOYMENTS"])) {
						if ($data["DEPLOYMENTS"]["deployments"]=="NONE") echo "There are no deployments.<br>";
						else {
							foreach ($data["DEPLOYMENTS"] as $service_name => $content) {
                                                                $orig_service_name = $service_name;
                                                                $service_name = strtolower($service_name);
                                                                //echo base64_decode($content);
								if (array_key_exists($service_name,$data["INSTALLED_SERVICES"])) {
                                                                        echo '<div><a href="#" onclick="reinstall(\''.$service_name.'\')">'.$orig_service_name.'</a> - '.$content.' - INSTALLED</div>';
								}
								else echo '<div><a href="#" onclick="load_template(\''.$service_name.'\')">'.$orig_service_name.'</a> - '.$content.'</div>';
								echo '<div id="'.$service_name.'" class="deployment"></div>';
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
					remove_response("$key");
				}
			}
		}
		else echo "WAIT";
	break;
	case "deployment":
		$arr = array("NAME" => $_GET["additional"], "ACTION" => "ask");
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("deployment",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "check_reinstall":
		$reinstall = 1;
	case "check_deployment":
		$arr = check_response("deployment");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="deployment") {
					if ($data["STATUS"]=="0") { // ask
						$template = json_decode(base64_decode($data["TEMPLATE"]));
						echo "<fieldset><form action=\"#\" method=\"post\" id=\"deploy_{$template->name}_form\"><br>";
						if ($reinstall) {
							//var_dump($template);
                                                        //var_dump($template);
                                                        $letsencrypt = check_letsencrypt();
							if (empty($letsencrypt)) echo "LETSENCRYPT in progress...";
							else {
								foreach ($template->fields as $field) {
									if ($field->key=="DOMAIN") {
										if (!empty($letsencrypt[$field->value])) {
											echo "LETSENCRYPT: ".$letsencrypt[$field->value]["status"]." - ".$letsencrypt[$field->value]["date"];
											echo " - <a href=\"letsencrypt_log.php?domain={$field->value}\" target=\"_blank\">LOG</a><br><br>";
										}
										else echo "LETSENCRYPT in progress for {$field->value}.";
									}
								}
							}
						}
						foreach ($template->fields as $field) {
							if (isset($field->generated)) {
								echo "<input type=\"hidden\" value=\"generated:{$field->generated}\" name=\"{$field->key}\" id=\"{$template->name}_{$field->key}\" class=\"additional_{$template->name}\">";
							}
							else {
							echo "<div class=\"row\"><div class=\"mb-3\"><label>".$field->description."</label>
								<input ".($field->required=="true" ? "required" : "")." type=\"".(!empty($field->type) ? $field->type : "text")."\" value=\"{$field->value}\" name=\"{$field->key}\" id=\"{$template->name}_{$field->key}\" class=\"additional_{$template->name}\">
							</div></div>";
							}
						}


                                                echo "
                                                <div class=\"row\">
                                                <div class=\"mb-3\">
                                                <button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\" id=\"deploy_{$template->name}_btn\">".($reinstall ? "Reinstall" : "Install")."</button>
                                                </div>";
						if ($reinstall) {
							echo "
							<div class=\"mb-3\" style=\"margin-left:30px;\">
							<button class=\"btn btn-lg btn-primary btn-block\" type=\"button\" id=\"uninstall_{$template->name}_btn\" onclick=\"uninstall('{$template->name}')\">Uninstall</button>
							</div>";
						}
						echo "<div class=\"mb-3\" style=\"margin-left:30px;\">
						<button class=\"btn btn-lg btn-primary btn-block\" type=\"button\" id=\"cancel_{$template->name}_btn\">Cancel</button>
						</div>";
                                                echo "
                                                </div>
                                                </form></fieldset>
<script>
	jQuery('#deploy_{$template->name}_form').submit(function() {
		".($reinstall ? "redeploy" : "deploy")."('{$template->name}');
		return false;
	});
        jQuery('#cancel_{$template->name}_btn').click(function() {
                $('div#{$template->name}').html('');
        });
</script>
						";
					}
					elseif ($data["STATUS"]=="2") { // deploy
						echo "Install has finished.";
						echo "<script>get_deployments();</script>";
					}
					remove_response("$key");
				}
			}
		}
		else {
			$arr = check_deploy($_GET["additional"]);
			if (!empty($arr)) { // deployment in progress
				foreach ($arr as $key=>$data) {
					if ($key=="deploy-".$_GET["additional"]) {
						if ($data["STATUS"]=="1") {
							//echo "Install in progress... Please wait...";
							echo "";
						}
						elseif ($data["STATUS"]=="2") { 
							echo "Install has finished.";
							echo "<script>get_deployments();</script>";
							remove_response("$key"); // remove from output if finished so reinstall can start
						}
					}
				}
			}
			else echo ""; // no deployment, finished
		}
	break;
	case "redeploy":
	case "deploy":
		if ($key=check_deploy($_GET["additional"])) { 
			$text="A deployment ({$_GET["additional"]}) has already started.<br>Please wait and do not start a new one...";
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

					if ($field_arr[1]=="openssl") {
						if ($field_arr[2]=="hex") $command = "openssl rand -hex $len";
						elseif ($field_arr[2]=="base64") $command = "openssl rand -base64 $len";
						else $command = "openssl rand $len"; // raw
						$output = shell_exec($command);
						if ($output === null) $output = "OPENSSL_ERROR";
					}
					else {
						if ($field_arr[1]=="random") $base = rand(100000,999999);
						elseif ($field_arr[1]=="time") $base = time();
						elseif ($field_arr[1]!="") $base = $field_arr[1]; // fix string
						else $base = rand(100000,999999); // default

						if (in_array($field_arr[2],$algos)) $base = hash($field_arr[2],$base);
						else $base = hash("md5",$base); // default alg

						$output = substr($base,0,$len);
					}
					$fields["$field_key"] = $output;
				}
			}
			$payload = base64_encode(json_encode($fields, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
			$arr = array("NAME" => $_GET["additional"], "ACTION" => $_GET["op"], "PAYLOAD" => $payload);
			$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
			if (set_output("deployment",$json)) echo "OK";
			else echo "ERROR";
		}
		echo $text;
	break;
	case "reinstall":
		$arr = array("NAME" => $_GET["additional"], "ACTION" => "reinstall");
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("deployment",$json)) echo "OK";
		else echo "ERROR";
	break;
        case "check_uninstall":
                $arr = check_deploy($_GET["additional"]);
                if (!empty($arr)) { // deployment in progress
                        foreach ($arr as $key=>$data) {
                                if ($key=="deploy-".$_GET["additional"]) {
                                        if ($data["STATUS"]=="1") {
                                                echo "Install in progress... You can't uninstall while in progress...";
                                        }
                                        elseif ($data["STATUS"]=="2") {
                                                echo "Install has finished...";
						echo "<script>get_deployments();</script>";
                                                remove_response("$key");
                                        }
                                }
                        }
                }
                else { // no deployment in progress -> uninstall
                        $key = "uninstall-".$_GET["additional"];
                        $arr = check_response($key);
                        if (!empty($arr)) {
                                $data = $arr[$key];
                                if ($data["STATUS"]=="1") {
                                        echo "Uninstall in progress... Please wait... ".date("Y-m-d H:i:s");
                                }
                                elseif ($data["STATUS"]=="2") {
                                        echo "OK";
                                        remove_response("$key");
                                }
                        }
                        else echo "Uninstall in progress... Please wait...";
                }
        break;
        case "uninstall":
                if ($key=check_deploy($_GET["additional"])) {
                        $text="Deploy/uninstall process has already started.<br>Please wait and do not start a new one...";
                }
                else {
                        $text="Uninstall in progress... Please wait...";
                        $arr = array("NAME" => $_GET["additional"], "ACTION" => "uninstall");
                        $json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

                        if (set_output("deployment",$json)) echo "OK";
                        else echo "ERROR";
                }
                echo $text;
        break;

	case "repositories":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("repositories",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "check_repositories":
		$arr = check_response("repositories");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="repositories") {
					$repos = json_decode(base64_decode($data["REPOSITORIES"]));
					foreach ($repos->repositories as $repo) {
						echo $repo."<br>";
					}
					remove_response("$key");
				}
			}
			remove_response("add_repository");
		}
		else echo "WAIT";
	break;
	case "add_repository":
		remove_response("add_repository");

		$arr = array("NEW_REPO" => $_GET["repo"]);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("add_repository",$json)) echo "OK";
		else echo "ERROR";
	break;
        case "check_vpn":
                $key = "check_vpn";
                $arr = array("STATUS" => 0);
                $json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
                set_output($key,$json);
                sleep(1);
                $arr = check_response($key);
                if (!empty($arr)) {
                        $data = $arr[$key];
                        echo $data["STATUS"];
                        remove_response("$key");
                }
                else echo "NO";
        break;
	case "save_vpn":
		remove_response("save_repository");

		$arr = array(
			"VPN_DOMAIN" => $_GET["vpn_domain"],
			"VPN_PASS" => $_GET["vpn_pass"],
			"LETSENCRYPT_MAIL" => $_GET["letsencrypt_mail"],
			"LETSENCRYPT_SERVERNAME" => $_GET["letsencrypt_servername"]
		);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("save_vpn",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "containers":
		$arr = array("STATUS" => 0);
		$json = json_encode($arr, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

		if (set_output("containers",$json)) echo "OK";
		else echo "ERROR";
	break;
	case "check_containers":
		$arr = check_response("containers");
		if (!empty($arr)) {
			foreach ($arr as $key=>$data) {
				if ($key=="containers") {
					echo base64_decode($data["RESULT"]);
					remove_response("$key");
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
