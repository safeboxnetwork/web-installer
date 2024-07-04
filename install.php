<?php

putenv('WEBINSTALL=true');

// TEMP
putenv('HOME=/home/hael');
putenv('USER=hael');

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



		# $NEXTCLOUD_DOMAIN" != "" ]; then
		# $NEXTCLOUD_USERNAME" != "" ]; then
		# $NEXTCLOUD_PASSWORD" != "" ]; then
		# $BITWARDEN_DOMAIN" != "" ]; then
		# $SMTP_SERVER" != "" ]; then
		# $SMTP HOST
		# $SMTP_PORT" != "" ]; then
		# $SMTP_SECURITY" != "" ]; then
		# $SMTP_FROM" != "" ]; then
		# $SMTP_USERNAME" != "" ]; then
		# $SMTP_PASSWORD" != "" ]; then
		# $DOMAINS_WHITELIST" != "" ]; then
/*
			if [ "$GUACAMOLE_DOMAIN" != "" ]; then
			if [ "$GUACAMOLE_ADMIN_NAME" != "" ]; then
			if [ "$GUACAMOLE_ADMIN_PASSWORD" != "" ]; then
                	TOTP_USE="true"; # if yes
			BAN_DURATION="5";

		$ROUNDCUBE_IMAP_HOST" != "" ]; then
		if [ "$ROUNDCUBE_IMAP_PORT" == "" ]; then
			ROUNDCUBE_IMAP_PORT="143";
		fi;
		if [ "$ROUNDCUBE_SMTP_HOST" != "" ]; then
		if [ "$ROUNDCUBE_SMTP_PORT" == "" ]; then
			ROUNDCUBE_SMTP_PORT="25";
		fi;
		if [ "$ROUNDCUBE_UPLOAD_MAX_FILESIZE" == "" ]; then
			ROUNDCUBE_UPLOAD_MAX_FILESIZE="50M";
		fi;
		$ROUNDCUBE_DOMAIN
 */

// check ENV variables
$output = shell_exec("set");
echo "<pre>".$output."</pre>";

$output = shell_exec("sh install.sh");
echo $output;

?>
