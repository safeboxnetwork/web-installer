<?php

putenv('DOCKER_REGISTRY_URL='.$_POST["registry"]);

if ($_POST["smarthost"]=="Y") {
	putenv('SMARTHOST_PROXY=yes');
	putenv('DOMAIN='.$_POST["domain"]);
}
else {
	putenv('SMARTHOST_PROXY=no');
}

if ($_POST["smarthost"]=="Y") {
	putenv('LOCAL_PROXY=yes');
}
else {
	putenv('LOCAL_PROXY=no');
}



// TODO - js warning
//if [ "$SMARTHOST_PROXY" == "no" ]; then
//	echo "Warning! Local proxy will not work without smarthost proxy service.";
//fi;

if ($_POST["vpn"]=="Y") {
	putenv('VPN_PROXY=yes');
}
else {
	putenv('VPN_PROXY=no');
}

putenv('VPN_DOMAIN='.$_POST["vpn_domain"]);
putenv('VPN_KEY='.$_POST["vpn_key"]);

putenv('LETSENCRYPT_MAIL='.$_POST["letsencrypt_mail"]);
putenv('LETSENCRYPT_SERVERNAME='.$_POST["letsencrypt_servername"]);

putenv('CRON='.$_POST["cron"]);
putenv('DISCOVERY='.$_POST["discovery"]);

putenv('ADDITIONAL='.$_POST["additional"]);

?>
