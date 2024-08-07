<?php

if ($_POST["smarthost"]=="Y") {
	if ($_POST["domain"]=="") $_POST["domain"] = "localhost";
	# if not FQDN
	$arr = explode(".",$_POST["DOMAIN"]);
	if (count($arr)==1) {
		echo "Warning! It seems DOMAAIN is not an FQDN. Self-signed certificate will be created only.";
		$_POST["self_signed"] = "true";
	}

}

create_install_json($_POST);

/*
put_install_envs();

// check ENV variables
$output = shell_exec("set");
echo "<pre>".$output."</pre>";

//$output = shell_exec("sh install.sh");
//echo $output;
*/

?>
