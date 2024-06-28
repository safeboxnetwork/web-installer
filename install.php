<?php

DOCKER_REGISTRY_URL=$_POST["registry"];

if ($_POST["smarthost"]=="Y") {
	SMARTHOST_PROXY="yes";
	DOMAIN=$_POST["domain"];
}
else {
	SMARTHOST_PROXY="no";
}
if ($_POST["smarthost"]=="Y") {
	LOCAL_PROXY="yes";
}
else {
	LOCAL_PROXY="no";
}


if [ "$SMARTHOST_PROXY" == "no" ]; then
	echo "Warning! Local proxy will not work without smarthost proxy service.";
fi;

if ($_POST["vpn"]=="Y") {
	VPN_PROXY="yes";
}
else {
	VPN_PROXY="no";
}


VPN_DOMAIN=$_POST["vpn_domain"];
VPN_KEY=$_POST["vpn_key"];
LETSENCRYPT_MAIL=$_POST["letsencrypt_mail"];
LETSENCRYPT_SERVERNAME=$_POST["letsencrypt_servername"];

CRON=$_POST["cron"];
DISCOVERY=$_POST["discovery"];


ADDITIONAL=$_POST["additional"]

?>
