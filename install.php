<?php

if ($_POST["SMARTHOST_PROXY"]=="Y") {
	if ($_POST["DOMAIN"]=="") $_POST["DOMAIN"] = "localhost";
	# if not FQDN
	$arr = explode(".",$_POST["DOMAIN"]);
	if (count($arr)==1) {
		echo "Warning! It seems DOMAAIN is not an FQDN. Self-signed certificate will be created only.";
		$_POST["SELF_SIGNED_CERTIFICATE"] = "true";
	}

}

if ($_POST["DISCOVERY"]=="yes") {
	if ($_POST["DISCOVERY_DIR"] == "" ) $_POST["DISCOVERY_DIR"]="/usr/local/bin/";
	if (substr($_POST["DISCOVERY_DIR"],0,1)!="/") {
		echo "The path must be absolute, for example /usr/local/bin/. Please type it again.";
		exit;
	}
	if ($_POST["DISCOVERY_CONFIG_FILE"] == "" ) $_POST["DISCOVERY_CONFIG_FILE"]="discovery.conf";
}

if ($_POST["ADDITIONALS"]=="yes") {
      if ($_POST["SERVICE_DIR"] == "" ) $_POST["SERVICE_DIR"]="/etc/user/config/services";
}

$json = json_encode($_POST);
echo $json;

// TODO redis

/*
put_install_envs();

// check ENV variables
$output = shell_exec("set");
echo "<pre>".$output."</pre>";

//$output = shell_exec("sh install.sh");
//echo $output;
*/

?>
