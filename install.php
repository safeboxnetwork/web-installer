<?php
include "functions.php";

if ($_POST["SMARTHOST_PROXY"]=="Y") {
	if ($_POST["DOMAIN"]=="") $_POST["DOMAIN"] = "localhost";
	# if not FQDN
	$arr = explode(".",$_POST["DOMAIN"]);
	if (count($arr)==1) {
		echo "Warning! It seems DOMAAIN is not an FQDN. Self-signed certificate will be created only.";
		$_POST["SELF_SIGNED_CERTIFICATE"] = "true";
	}

}

if ($_POST["VPN_PROXY"]=="Y") {
	$vpnkey_url = get_vpn_url($_POST["VPN_DOMAIN"],$_POST["VPN_KEY"]);
	// DEBUG

	echo $vpnkey_url;
	echo "<br>";
	echo file_get_contents($vpnkey_url);
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

$json = json_encode($_POST, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
//echo $json;

// TODO preview about selected options?
// TODO - new install in progress? INSTALL_STATUS=0

if ($key=check_install()) { 
	$header_text="Install has already started.<br>Please wait and do not start a new one...";
}
else {
	$header_text="Installing in progress... Please wait...";
	$key = "install:".date("YmdHis");
	redis_set($key,$json);
	//$key = "install:20240816101849"; // DEBUG
}

/*
put_install_envs();

// check ENV variables
$output = shell_exec("set");
echo "<pre>".$output."</pre>";

//$output = shell_exec("sh install.sh");
//echo $output;
*/

?>
<!doctype html>
<html lang="en">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>INSTALLER TOOL</title>
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
<!-- Custom styles for this template -->
<link href="installer.css?t=1" rel="stylesheet">
</head>
<body id="install" class="text-center">
<div class="container-fluid">
<div class="col-md-12">
	<h1><?php echo $header_text?></h1>
	<div id="redis"></div>
	<div id="response"></div>
</div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.6/dist/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script>
$(function() {

function redirectToManage() {
    window.location.href = 'manage.html';
}

function check_install() {
  var url  = 'scan.php?op=check_install&key=<?php echo $key;?>';
  $.get(url, function(data){
    console.log(data);
    if (data=='INSTALLED') {
	redirectToManage();
    }
    else {
      counter+=1
      $("#response").html('Please wait... ' + counter);
      setTimeout(check_install, 1000);
    }
  });
}

  var url  = 'scan.php?op=redis';
  $.get(url, function(data){
    if (data=='OK') {
      $("#redis").html('Redis server - OK');
      check_install();
    }
    else {
      $("#redis").html('Redis server is not available...');
    }
  });

  counter=0;
});
</script>
</body>
</html>
