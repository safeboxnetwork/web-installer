<?php
include "functions.php";

if ($_POST["SMARTHOST_PROXY"]=="Y") {
	if ($_POST["DOMAIN"]=="") $_POST["DOMAIN"] = "localhost";
	# if not FQDN
	$arr = explode(".",$_POST["DOMAIN"]);
	if (count($arr)==1) {
		echo "Warning! It seems DOMAIN is not an FQDN. Self-signed certificate will be created only.";
		$_POST["SELF_SIGNED_CERTIFICATE"] = "true";
	}

}

/*
if ($_POST["VPN_PROXY"]=="Y") {
	$vpnkey_url = get_vpn_url($_POST["VPN_DOMAIN"],$_POST["VPN_PASS"]);
	// DEBUG
	echo $vpnkey_url;
}
*/

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

$json = json_encode($_POST, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
//echo $json;

// TODO preview about selected options?
// TODO - new install in progress? INSTALL_STATUS=0

if ($key=check_install()) { 
	$header_text="Install has already started.<br>Please wait and do not start a new one...";
}
else {
	$header_text="Installing in progress... Please wait...";
	//$key = "install:".date("YmdHis");
	$key = "install";
	if (set_output($key,$json)) echo "";
	else echo "ERROR";
}

/*
put_install_envs();

// check ENV variables
$output = shell_exec("set");
echo "<pre>".$output."</pre>";

//$output = shell_exec("sh install.sh");
//echo $output;
*/

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Safebox - INSTALLER TOOL</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Switzer:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="style.css?t=4" />
</head>
<body id="install" class="text-center">
  <div class="main">
    <div id="myAppsContainer">
	<div class="logo" style="margin:100px 0px 20px 0px;">
		<img src="/img/logo.svg" alt="Safebox"/>
		<span>Safebox</span>
	</div>
	<div class="progress-box">
		<div class="progress-title"><?php echo $header_text?></div>
		<div class="progress-description" id="info"></div>
		<div class="progress-container-shadow">
		</div>
		<div class="progress-container">
			<div class="progress-bar" id="progressBar"></div>
			<div class="progress-text" id="progressText">0%</div>
		</div>
	</div>
    </div>
  </div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.6/dist/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script src="install.js?t=7"></script>
<script>
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
let currentProgress = 0;
let progressInterval;
let install = 1;

// Initialize
updateProgress(0);
startProgress(90000);// 90 seconds

check_interface();
counter=0;
</script>
</body>
</html>
