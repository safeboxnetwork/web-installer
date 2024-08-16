<?php

$REDIS_HOST='redis-server';

function ping_redis() {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) return true;
	else return false;
}

function check_install() {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) {
		$members = $redis->sMembers("web_in"); // redis-cli -h redis-server smembers $group

		$in_progress=0;
		foreach ($members as $member) {
			if (substr($member,0,7)=="install") {
				$in_progress=$member;
				break;
			}
		}
		return $in_progress;
	}
}

function check_redis($group="scheduler_in", $key="") {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) {
		$members = $redis->sMembers($group); // redis-cli -h redis-server smembers $group
		//print_r($members);

		foreach ($members as $member) {
			if ($key!="" && $member!=$key) continue; // check a specific key in a group

			$value = $redis->get($member);
			$json_data = base64_decode($value);
			$data = json_decode($json_data,true);
			if ($data === null) {
				echo "JSON read error...";
				// TODO json error
			}
			else {
				return array("$member" => $data);
			}
		}
	}
}

function redis_get($key) {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
	if ($redis->ping()) {
		//$arList = $redis->keys("*"); // ? redis-cli -h redis-server keys "*" 
		//echo "Stored keys in redis:";
		//print_r($arList);
		if ($redis->exists($key)) {
			$value = $redis->get($key);
			//redis-cli -h redis-server get $key
			return base64_decode($value);
		} else {
			echo "Key does not exist: $key";
			// TODO
		}
	}
}

function redis_set($key, $value) {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
//	$redis->auth('password');
	if ($redis->ping()) {
		if (!$redis->exists($key)) {
			//redis-cli -h redis set $key "$value"
			//redis-cli -h redis sadd web_in $key
			//redis-cli -h redis smembers web_in
			$redis->set($key, base64_encode($value));
			$redis->sAdd('web_in', $key);
		} else {
			echo "Key already exist: $key";
		}
	}
}

function redis_remove($key) {

	global $REDIS_HOST;

	$redis = new Redis();
	$redis->connect($REDIS_HOST);
//	$redis->auth('password');
	if ($redis->ping()) {
		//redis-cli -h redis srem web_out $key
		//redis-cli -h redis del $key
		$redis->srem("web_out", $key);
		$redis->del($key);
	}
}

// not in use
function put_install_envs() {

	// TEMP
	putenv('HOME=/home/hael');
	putenv('USER=hael');

	putenv('DOCKER_REGISTRY_URL='.$_POST["registry"]);

	putenv('SMARTHOST_PROXY='.$_POST["smarthost"]);
	if ($_POST["smarthost"]=="Y") {
		if ($_POST["domain"]=="") $_POST["domain"] = "localhost";
		putenv('DOMAIN='.$_POST["domain"]);
		# if not FQDN
		$arr = explode(".",$_POST["DOMAIN"]);
		if (count($arr)==1) {
			echo "Warning! It seems DOMAAIN is not an FQDN. Self-signed certificate will be created only.";
			putenv('SELF_SIGNED_CERTIFICATE=true');
		}

	}

	putenv('LOCAL_PROXY='.$_POST["localproxy"]);
	putenv('VPN_PROXY='.$_POST["vpn"]);
	if ($_POST["vpn"]=="yes") {
		putenv('VPN_DOMAIN='.$_POST["vpn_domain"]);
		putenv('VPN_KEY='.$_POST["vpn_key"]);

		putenv('LETSENCRYPT_MAIL='.$_POST["letsencrypt_mail"]);
		putenv('LETSENCRYPT_SERVERNAME='.$_POST["letsencrypt_servername"]);
	}
	putenv('CRON='.$_POST["cron"]);
	putenv('DISCOVERY='.$_POST["discovery"]);

	if ($_POST["discovery"]=="yes") {
		if ($_POST["DISCOVERY_DIR"] == "" ) $_POST["DISCOVERY_DIR"]="/usr/local/bin/";
		if (substr($_POST["DISCOVERY_DIR"],0,1)!="/") {
			echo "The path must be absolute, for example /usr/local/bin/. Please type it again.";
			exit;
		}
		if ($_POST["DISCOVERY_CONFIG_FILE"] == "" ) $_POST["DISCOVERY_CONFIG_FILE"]="discovery.conf";
		putenv('DISCOVERY_DIR='.$_POST["discovery_dir"]);
		putenv('DISCOVERY_CONFIG_FILE='.$_POST["discovery_config_file"]);
	}


	putenv('ADDITIONALS='.$_POST["additionals"]);
	if ($_POST["additionals"]=="yes") {
	      if ($_POST["SERVICE_DIR"] == "" ) $_POST["SERVICE_DIR"]="/etc/user/config/services";
	      putenv('SERVICE_DIR='.$_POST["service_dir"]);

	      putenv('NEXTCLOUD='.$_POST["nextcloud"]);
	      putenv('BITWARDEN='.$_POST["bitwarden"]);
	      putenv('GUACAMOLE='.$_POST["guacamole"]);
	      putenv('SMTP='.$_POST["smtp_server"]);
	      putenv('ROUNDCUBE='.$_POST["roundcube"]);

	      if ($_POST["nextcloud"]=="yes") {
		      putenv('NEXTCLOUD_DOMAIN='.$_POST["nextcloud_domain"]);
		      putenv('NEXTCLOUD_USERNAME='.$_POST["nextcloud_username"]);
		      putenv('NEXTCLOUD_PASSWORD='.$_POST["nextcloud_password"]);
	      }
	      if ($_POST["bitwarden"]=="yes") {
		      putenv('BITWARDEN_DOMAIN='.$_POST["bitwarden_domain"]);
		      putenv('SMTP_SERVER='.$_POST["bitwarden_smtp_server"]);
		      putenv('SMTP_HOST='.$_POST["bitwarden_smtp_host"]);
		      putenv('SMTP_PORT='.$_POST["bitwarden_smtp_port"]);
		      putenv('SMTP_SECURITY='.$_POST["bitwarden_smtp_security"]);
		      putenv('SMTP_FROM='.$_POST["bitwarden_smtp_from"]);
		      putenv('SMTP_USERNAME='.$_POST["bitwarden_smtp_username"]);
		      putenv('SMTP_PASSWORD='.$_POST["bitwarden_smtp_password"]);
		      putenv('DOMAINS_WHITELIST='.$_POST["bitwarden_domains_whitelist"]);
	      }
	      if ($_POST["guacamole"]=="yes") {
		      putenv('GUACAMOLE_DOMAIN='.$_POST["bitwarden_domain"]);
		      putenv('GUACAMOLE_ADMIN_NAME='.$_POST["bitwarden_smtp_username"]);
		      putenv('GUACAMOLE_ADMIN_PASSWORD='.$_POST["bitwarden_smtp_password"]);
		      if ($_POST["bitwarden_totp"]=="yes") putenv('TOTP_USE=true');
		      if ($_POST["bitwarden_ban_duration"]=="") $_POST["bitwarden_ban_duration"]="5";
		      putenv('BAN_DURATION='.$_POST["bitwarden_ban_duration"]);
	      }
	      if ($_POST["roundcube"]=="yes") {
		      if ($_POST["roundcube_imap_port"]=="") $_POST["roundcube_imap_port"]="143";
		      if ($_POST["roundcube_smtp_port"]=="") $_POST["roundcube_smtp_port"]="25";
		      if ($_POST["roundcube_upload"]=="") $_POST["roundcube_smtp_port"]="50M";
		      putenv('ROUNDCUBE_IMAP_HOST='.$_POST["roundcube_imap_host"]);
		      putenv('ROUNDCUBE_IMAP_PORT='.$_POST["roundcube_imap_port"]);
		      putenv('ROUNDCUBE_SMTP_HOST='.$_POST["roundcube_smtp_host"]);
		      putenv('ROUNDCUBE_SMTP_PORT='.$_POST["roundcube_smtp_port"]);
		      putenv('ROUNDCUBE_UPLOAD_MAX_FILESIZE='.$_POST["roundcube_upload"]);
		      putenv('ROUNDCUBE_DOMAIN='.$_POST["roundcube_domain"]);
	      }
	}
}

?>
