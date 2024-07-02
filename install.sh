#!/bin/bash

ask_envs() {

	echo "Smarthost proxy? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "n" ] || [ "$ANSWER" == "N" ]; then
		SMARTHOST_PROXY="no";
	else
		SMARTHOST_PROXY="yes";
		echo "Please fill in the domain name: (localhost)";
		read -r DOMAIN;
		if [ "$DOMAIN" == "" ]; then
			DOMAIN="localhost";
		fi
		A=$(echo $DOMAIN | cut -d '.' -f1)
		B=$(echo $DOMAIN | cut -d '.' -f2)
		# if not FQDN
		if [ "$A" == "$B" ]; then
			echo "Warning! It seems it's not a FQDN. Self-signed certificate will be created only.";
			SELF_SIGNED_CERTIFICATE="true";
		fi;
	fi

	echo "Local proxy? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "n" ] || [ "$ANSWER" == "N" ]; then
		LOCAL_PROXY="no";
	else
		LOCAL_PROXY="yes";
		if [ "$SMARTHOST_PROXY" == "no" ]; then
			echo "Warning! Local proxy will not work without smarthost proxy service.";
		fi;
	fi

	echo "VPN proxy? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "n" ] || [ "$ANSWER" == "N" ]; then
		VPN_PROXY="no";
	else
		VPN_PROXY="yes";

		while true; do

			echo "Please add domain url to download the VPN hash from (default: https://demo.format.hu): ";
			read -r VPN_DOMAIN;
			if [ "$VPN_DOMAIN" == "" ]; then
				VPN_DOMAIN="https://demo.format.hu";
			fi;

			VPN_KEY="";
			echo "Please type in the generated VPN passkey (8 digits):";
			while read -r VPN_PASS; do
				if [ "$VPN_PASS" != "" ]; then
					dateFromServer=$(curl -v --silent https://demo.format.hu/ 2>&1 | grep -i '< date' | sed -e 's/< date: //gi')
					VPN_DATE=$(date +"%Y%m%d" -d "$dateFromServer");
					VPN_HASH=$(echo -n $(( $VPN_PASS * $VPN_DATE )) | sha256sum | cut -d " " -f1);
					VPN_URL="$VPN_DOMAIN/$VPN_HASH/secret";
					echo "DEBUG: $VPN_DATE";
					echo "DEBUG: $VPN_URL";
					HTTP_CODE=$(curl -s -I -w "%{http_code}" $VPN_URL -o /dev/null);
					break;
				fi;
			done

			echo "DEBUG: $HTTP_CODE";
			if [ "$HTTP_CODE" == "200" ]; then
				# download VPN key
				VPN_KEY=$(curl -s $VPN_URL);
				echo $VPN_KEY;

				$SUDO_CMD mkdir -p /etc/user/secret/vpn-proxy;
				echo $VPN_KEY | base64 -d > /tmp/wg0.conf;
				$SUDO_CMD mv /tmp/wg0.conf /etc/user/secret/vpn-proxy/;
				break;
			else
				echo "Download of VPN KEY was unsuccessful from URL: $VPN_URL";

				echo "Do you want to retry? (Y/n)";
				read -r VPN_RETRY;
				if [ "$VPN_RETRY" == "n" ] || [ "$VPN_RETRY" == "N" ]; then
					VPN_PROXY="no";
					echo "VPN proxy was skipped.";
					break;
				fi
			fi;
		done;

		if [ "$VPN_PROXY" == "yes" ]; then
			echo "Please add the letsencrypt mail address:";
			while read -r LETSENCRYPT_MAIL; do
				if [ "$LETSENCRYPT_MAIL" != "" ]; then
					if [ "$(echo "$LETSENCRYPT_MAIL" | grep '@')" != "" ]; then
						if [ "$(echo "$LETSENCRYPT_MAIL" | grep '\.')" != "" ]; then
							break;
						fi;
					fi;
				fi;
				echo "Invalid email address.";
			done

			echo "Please add letsencrypt server name (default is letsencrypt but you can add zerossl too):";
			read -r LETSENCRYPT_SERVERNAME;
			if [ "$LETSENCRYPT_SERVERNAME" = "" ]; then
				LETSENCRYPT_SERVERNAME="letsencrypt";
			fi;
		fi;
	fi

	echo "Cron? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "n" ] || [ "$ANSWER" == "N" ]; then
		CRON="no";
	else
		CRON="yes";
	fi

}

discover_services() {
	echo "Would you like to discover services? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "n" ] || [ "$ANSWER" == "N" ]; then
		DISCOVERY="no";
	else
		DISCOVERY="yes";
		echo "Path of service discovery scripts: (/usr/local/bin/)";
		read -r DISCOVERY_DIR;
		if [ "$DISCOVERY_DIR" == "" ] ; then
			DISCOVERY_DIR="/usr/local/bin/"
		else
			# while not an absolute path
			while [ "${DISCOVERY_DIR:0:1}" != "/" ]; do
				echo "The path must be absolute, for example /usr/local/bin/. Please type it again."
				read -r DISCOVERY_DIR;
			done

		fi

		echo "Path of the discovery config file: (discovery.conf)";
		read -r DISCOVERY_CONFIG_FILE;
		if [ "$DISCOVERY_CONFIG_FILE" == "" ] ; then
			DISCOVERY_CONFIG_FILE=$PWD"/discovery.conf";
			if [ ! -f $DISCOVERY_CONFIG_FILE ]; then
				USE_SUDO=$(whoami);
				if [ "$USE_SUDO" == "root" ]; then
					USE_SUDO=0;
				else
					USE_SUDO=1;
				fi

				{
					echo '#!/bin/bash';
					echo 'SOURCE_DIRS="/etc/user/data/ /etc/user/config/"; # separator space or |';
					echo 'DIRNAME="services misc"; # separator space or |';
					echo 'FILENAME="service healthcheck"; # separator space or |';
					echo 'KEYS="START_ON_BOOT"; # separator space or |';
					echo 'DEST_FILE="results.txt";';
					echo 'USE_SUDO='$USE_SUDO';';

				} >> $DISCOVERY_CONFIG_FILE;
			fi
		fi
		DISCOVERY_CONFIG_DIR=$(dirname $DISCOVERY_CONFIG_FILE)
		 if [ "$DISCOVERY_CONFIG_DIR" == "/root" ]; then
		 	DISCOVERY_CONFIG_DIR="";
		 fi
		 	
	fi
}

check_dirs_and_files() { # TODO?

	if [ ! -f "$HOME/.ssh/installer" ]; then
		echo "No ssh key files found. Please paste base64 content of the installer private key: ";
		while read -r INSTALLER; do
			if [ "$INSTALLER" != "" ]; then
				break;
			fi;
		done
		echo $INSTALLER > $HOME/.ssh/installer;
	fi;
	chmod 0600 $HOME/.ssh/installer;

	if [ ! -d "/etc/user/config" ]; then
		$SUDO_CMD mkdir -p "/etc/user/config"
	fi;
	if [ ! -d "/etc/system" ]; then
		$SUDO_CMD mkdir "/etc/system"
	fi;
	if [ ! -d "/etc/user/secret" ]; then
		$SUDO_CMD mkdir -p "/etc/user/secret"
	fi;

	if [ ! -f "/etc/user/config/system.json" ]; then
		{
			echo '
{
	"NETWORK": {
		"IP_POOL_START": "172.19.0.0",
		"IP_POOL_END": "172.19.254.0",
		"IP_SUBNET": "24"
	}
}
';
		} > /tmp/system.json

		$SUDO_CMD mv /tmp/system.json /etc/user/config/system.json
	fi;

	{
		echo "alias service-debian='$SUDO_CMD docker run --rm \
 -w /services/ \
 -e DOCKER_REGISTRY_URL=$DOCKER_REGISTRY_URL \
 -e USER_INIT_PATH=/etc/user/config \
 -e CA_PATH=/etc/ssl/certs \
 -e DNS_DIR=/etc/system/data/dns \
 -e HOST_FILE=/etc/dns/hosts.local \
 -v /etc/system/data/dns:/etc/dns:rw \
 -v /etc/ssl/certs:/etc/ssl/certs:ro \
 -v /etc/user/config/user.json:/etc/user/config/user.json:ro \
 -v /etc/user/config/system.json:/etc/user/config/system.json:ro \
 -v /etc/user/config/services/:/services/:ro \
 -v /etc/user/config/services/tmp:/services/tmp:rw \
 -v /var/run/docker.sock:/var/run/docker.sock \
 -v /usr/bin/docker:/usr/bin/docker:ro \
 $DOCKER_REGISTRY_URL/setup'";
	} > $HOME/.bash_aliases

}

check_running() {

	DOCKERD_STATUS="0";

	which systemctl 2> /dev/null;

	if [ "$?" == "0" ]; then
		DOCKERD_STATUS=$($SUDO_CMD systemctl status docker | grep running | wc -l)
		if [ "$DOCKERD_STATUS" == "0" ]; then
			$SUDO_CMD systemctl start docker

			# wait for docker start, check in every seconds, run for max. 60 sec
			WAIT_COUNT=0;
			while [ "$DOCKERD_STATUS" == "0" ]; do
				sleep 1;
				WAIT_COUNT=$((WAIT_COUNT+1))
				DOCKERD_STATUS=$($SUDO_CMD systemctl status docker | grep running | wc -l)

				if [ $WAIT_COUNT -gt 60 ]; then
					break; # docker hasn't started in 60 seconds
				fi;
			done;

			if [ "$DOCKERD_STATUS" == "0" ]; then 
				echo "Docker daemon not running, please check and execute again the install script";
				exit;
			fi
		fi
		DEBIAN="true";
	else
		echo "systemctl was not found";
		if [ "$WEBINSTALL" == "" ]; then # TODO?
			echo "Do you want to continue? (Y/n)";
			read -r ANSWER;
			if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
				# Custom gentoo docker status check
				DOCKERD_STATUS=$($SUDO_CMD rc-status 2>/dev/null | grep docker | grep started | wc -l);
				if [ "$DOCKERD_STATUS" == "0" ]; then
					$SUDO_CMD /etc/init.d/docker start
					sleep 5;
					DOCKERD_STATUS=$($SUDO_CMD rc-status 2>/dev/null | grep docker | grep started | wc -l);
					if [ "$DOCKERD_STATUS" == "0" ]; then 
						echo "Docker daemon not running, please check and execute again the install script";
						exit;
					fi
				fi;
				GENTOO="true";
			else
				exit;
			fi;
		else
			exit; # webinstall
		fi;
	fi

	if [ "$WEBINSTALL" == "" ]; then # TODO?
		# bridge check
		BRIDGE_NUM=$($SUDO_CMD docker network ls | grep bridge | awk '{print $2":"$3}' | sort | uniq | wc -l);

		CONTAINER_NUM=$($SUDO_CMD docker ps -a | wc -l);

		if [ "$BRIDGE_NUM" != "1" ] && [ "$CONTAINER_NUM" != "1" ]; then

			echo "There are existing containers and/or networks.";
			echo "Please select from the following options (1/2/3):";

			echo "1 - Delete all existing containers and networks before installation";
			echo "2 - Stop the installation process";
			echo "3 - Just continue on my own risk";
			
			read -r ANSWER;

			if [ "$ANSWER" == "1" ]; then
				echo "1 - Removing exising containers and networks";
				# delete and continue
				$SUDO_CMD docker stop $($SUDO_CMD docker ps |grep Up | awk '{print $1}')
				$SUDO_CMD docker system prune -a

			elif [ "$ANSWER" == "3" ]; then
				echo "3 - You have chosen to continue installation process."

			else # default: 2 - stop installastion
				echo "2 - Installation process was stopped";
				exit;
			fi;

		fi;
	fi;
}


install_docker_apt() {
	#echo exit 101 > /usr/sbin/policy-rc.d
	echo exit 101 > /tmp/p-rc; $SUDO_CMD mv /tmp/p-rc /usr/sbin/policy-rc.d
	$SUDO_CMD chmod +x /usr/sbin/policy-rc.d

	$SUDO_CMD apt-get update -y
	$SUDO_CMD apt-get install ca-certificates curl gnupg -y
	$SUDO_CMD install -m 0755 -d /etc/apt/keyrings
	$SUDO_CMD curl -fsSL https://download.docker.com/linux/debian/gpg | $SUDO_CMD gpg --dearmor -o /etc/apt/keyrings/docker.gpg
	$SUDO_CMD chmod a+r /etc/apt/keyrings/docker.gpg

	. /etc/os-release; # set variable VERSION_CODENAME

	DOCKER_SOURCE=$($SUDO_CMD cat /etc/apt/sources.list.d/docker.list | grep 'bullseye stable' | wc -l)
	if [ "$DOCKER_SOURCE" == "0" ]; then 
		# add docker source to the source list
		$SUDO_CMD echo "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian "$VERSION_CODENAME" stable" | $SUDO_CMD tee /etc/apt/sources.list.d/docker.list > /dev/null
		$SUDO_CMD apt-get update -y
	fi

	$SUDO_CMD apt-get install --no-install-recommends docker-ce docker-ce-cli containerd.io -y
}

install_docker_deb() {

	# set variables to install docker from debian packages
	DOCKER_URL="https://download.docker.com/linux/debian/dists/bullseye/pool/stable/amd64/";
	CONTAINERD_VERSION="1.6.20-1";
	DOCKER_VERSION="23.0.5-1~debian.11~bullseye";
	DOCKER_ARCH="amd64";
	PKG_DIR="/tmp"

	# set package names
	CONTAINERD="containerd.io_"$CONTAINERD_VERSION"_"$DOCKER_ARCH".deb";
	DOCKER_CE="docker-ce_"$DOCKER_VERSION"_"$DOCKER_ARCH".deb";
	DOCKER_CE_CLI="docker-ce-cli_"$DOCKER_VERSION"_"$DOCKER_ARCH".deb";
	#DOCKER_BUILDX="docker-buildx-plugin_"$DOCKER_VERSION"_"$DOCKER_ARCH".deb";
	#DOCKER_COMPOSE="docker-compose-plugin_"$DOCKER_VERSION"_"$DOCKER_ARCH".deb";

	CONTAINERD_INSTALLED=$(dpkg -s containerd.io | wc -l);
	if [ "$CONTAINERD_INSTALLED" == "0" ]; then
		# Download debian package
		echo "Download package from: " $DOCKER_URL$CONTAINERD;
		wget -O $PKG_DIR/$CONTAINERD $DOCKER_URL$CONTAINERD;

		# Install package
		dpkg -i $PKG_DIR/$CONTAINERD;
	fi;

	DOCKERCE_INSTALLED=$(dpkg -s docker-ce | wc -l);
	if [ "$DOCKERCE_INSTALLED" == "0" ]; then
		# Download debian package
		echo "Download package from: " $DOCKER_URL$DOCKER_CE;
		wget -O $PKG_DIR/$DOCKER_CE $DOCKER_URL$DOCKER_CE;

		# Install package
		dpkg -i $PKG_DIR/$DOCKER_CE;
	fi;

	DOCKERCECLI_INSTALLED=$(dpkg -s docker-ce-cli | wc -l);
	if [ "$DOCKERCECLI_INSTALLED" == "0" ]; then
		# Download debian package
		echo "Download package from: " $DOCKER_URL$DOCKER_CE_CLI;
		wget -O $PKG_DIR/$DOCKER_CE_CLI $DOCKER_URL$DOCKER_CE_CLI;

		# Install package
		dpkg -i $PKG_DIR/$DOCKER_CE_CLI;
	fi;

	# verify ???
	systemctl start docker

	# remove downloaded packages ???
	# rm $PKG_DIR/$CONTAINERD $PKG_DIR/$DOCKER_CE $PKG_DIR/$DOCKER_CE_CLI $PKG_DIR/$DOCKER_BUILDX $PKG_DIR/$DOCKER_COMPOSE

}

ask_additionals() {

	echo "Please add directory path of service files: (/etc/user/config/services/)";
	read -r SERVICE_DIR;
	if [ "$SERVICE_DIR" == "" ] ; then
		SERVICE_DIR="/etc/user/config/services";
	else
		# while not an absolute path
		while [ ${SERVICE_DIR:0:1} != "/" ]; do
			echo "The path must be absolute, for example /etc/user/config/services/. Please type it again."
			read -r SERVICE_DIR;
		done

	fi

	echo "Do you want to install Nextcloud? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ]; then
		NEXTCLOUD="yes";

		echo "Please add Nextcloud domain: ";
		while read -r NEXTCLOUD_DOMAIN; do
			if [ "$NEXTCLOUD_DOMAIN" != "" ]; then
				break;
			fi;
		done
		echo "Please add Nextcloud username: ";
		while read -r NEXTCLOUD_USERNAME; do
			if [ "$NEXTCLOUD_USERNAME" != "" ]; then
				break;
			fi;
		done
		echo "Please add Nextcloud password: ";
		while read -r -s NEXTCLOUD_PASSWORD; do
			if [ "$NEXTCLOUD_PASSWORD" != "" ]; then
				break;
			fi;
		done
	fi

	echo "Do you want to install Bitwarden? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ]; then
		BITWARDEN="yes";

		echo "Please add Bitwarden domain: ";
		while read -r BITWARDEN_DOMAIN; do
			if [ "$BITWARDEN_DOMAIN" != "" ]; then
				break;
			fi;
		done

		echo "Please choose an SMTP server (1/2/3): ";
		echo "1 - Gmail";
		echo "2 - Microsoft Outlook/Hotmail";
		echo "3 - Other";
		while read -r SMTP_SERVER; do
			if [ "$SMTP_SERVER" != "" ]; then
				break;
			fi;
		done

		if [ "$SMTP_SERVER" == "3" ]; then
			echo "Please add SMTP HOST: ";
			while read -r SMTP_HOST; do
				if [ "$SMTP_HOST" != "" ]; then
					break;
				fi;
			done

			echo "Please add SMTP PORT (587, 465, 25, etc.): ";
			while read -r SMTP_PORT; do
				if [ "$SMTP_PORT" != "" ]; then
					break;
				fi;
			done

			echo "Please add SMTP SECURITY (starttls, force_tls, off, etc.): ";
			while read -r SMTP_SECURITY; do
				if [ "$SMTP_SECURITY" != "" ]; then
					break;
				fi;
			done
		fi

		echo "Please add SMTP FROM (mail address from): ";
		while read -r SMTP_FROM; do
			if [ "$SMTP_FROM" != "" ]; then
				break;
			fi;
		done

		echo "Please add SMTP USERNAME: ";
		while read -r SMTP_USERNAME; do
			if [ "$SMTP_USERNAME" != "" ]; then
				break;
			fi;
		done

		echo "Please add SMTP PASSWORD: ";
		while read -r -s SMTP_PASSWORD; do
			if [ "$SMTP_PASSWORD" != "" ]; then
				break;
			fi;
		done

		echo "Please add Domains Whitelist (list of domains): ";
		while read -r DOMAINS_WHITELIST; do
			if [ "$DOMAINS_WHITELIST" != "" ]; then
				break;
			fi;
		done

	fi

	echo "Do you want to install Guacamole? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ]; then
		GUACAMOLE="yes";

		echo "Please add Guacamole domain: ";
		while read -r GUACAMOLE_DOMAIN; do
			if [ "$GUACAMOLE_DOMAIN" != "" ]; then
				break;
			fi;
		done

		echo "Please add Guacamole admin username: ";
		while read -r GUACAMOLE_ADMIN_NAME; do
			if [ "$GUACAMOLE_ADMIN_NAME" != "" ]; then
				break;
			fi;
		done

		echo "Please add Guacamole admin password: ";
		while read -r -s GUACAMOLE_ADMIN_PASSWORD; do
			if [ "$GUACAMOLE_ADMIN_PASSWORD" != "" ]; then
				break;
			fi;
		done

		echo "Do you want TOTP via login? (Y/n)";
		read -r TOTP_USE;
		if [ "$TOTP_USE" == "" ] || [ "$TOTP_USE" == "y" ] || [ "$TOTP_USE" == "Y" ] ; then
                	TOTP_USE="true";
        	fi;

		echo "Do you want limitation in case invalid login or password? Please add a number how many minutes for deny retry. If you add 0 means it will disabled. If just press enter means limitation will be set 5 minutes by default";
		read -r BAN_DURATION;
		if [ "$BAN_DURATION" == "" ] ; then
			BAN_DURATION="5";
		fi;
	fi

	echo "Do you want to install SMTP server? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ]; then
		SMTP="yes";


	fi;

	echo "Do you want to install roundcube? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ]; then
		ROUNDCUBE="yes";

		echo "Please add IMAP HOST: ";
		while read -r ROUNDCUBE_IMAP_HOST; do
			if [ "$ROUNDCUBE_IMAP_HOST" != "" ]; then
				break;
			fi;
		done

		echo "Please add IMAP PORT (default: 143): ";
		read -r ROUNDCUBE_IMAP_PORT;
		if [ "$ROUNDCUBE_IMAP_PORT" == "" ]; then
			ROUNDCUBE_IMAP_PORT="143";
		fi;

		echo "Please add SMTP HOST: ";
		while read -r ROUNDCUBE_SMTP_HOST; do
			if [ "$ROUNDCUBE_SMTP_HOST" != "" ]; then
				break;
			fi;
		done

		echo "Please add SMTP PORT (587, 465, 25, etc., default: 25): ";
		read -r ROUNDCUBE_SMTP_PORT;
		if [ "$ROUNDCUBE_SMTP_PORT" == "" ]; then
			ROUNDCUBE_SMTP_PORT="25";
		fi;

		echo "Please add UPLOAD_MAX_FILESIZE (default: 50M): ";
		read -r ROUNDCUBE_UPLOAD_MAX_FILESIZE;
		if [ "$ROUNDCUBE_UPLOAD_MAX_FILESIZE" == "" ]; then
			ROUNDCUBE_UPLOAD_MAX_FILESIZE="50M";
		fi;

		echo "Please add Roundcube DOMAIN: ";
		while read -r ROUNDCUBE_DOMAIN; do
			if [ "$ROUNDCUBE_DOMAIN" != "" ]; then
				break;
			fi;
		done

	fi;

}

uninstall() {

	sed '/service-debian/d' $HOME/.bash_aliases

	#$SUDO_CMD rm -rf /etc/user;
	#$SUDO_CMD rm -rf /etc/system;

	# $SUDO_CMD docker stop $($SUDO_CMD docker ps |grep Up | awk '{print $1}')
	# $SUDO_CMD docker system prune -a
	# $SUDO_CMD docker containers prune --force
	$SUDO_CMD docker ps -a

	# $SUDO_CMD /sbin/iptables -D DOCKER-USER -F

	if [ "$APT" == "1" ]; then
		echo "Would you like to remove docker? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ]; then
			#$SUDO_CMD apt-get purge -y docker-ce docker-ce-cli containerd.io docker-engine docker docker.io docker-compose-plugin
			#$SUDO_CMD rm -rf /var/lib/docker /etc/docker
			#$SUDO_CMD rm /etc/apparmor.d/docker
			#$SUDO_CMD groupdel docker
			#$SUDO_CMD rm -rf /var/run/docker.sock

			echo "x";
		fi
	fi;

	echo "x";
}

SUDO_CMD="";
APT=$($SUDO_CMD type apt 2>/dev/null | grep 'apt is' | wc -l);

if [ "$1" == "remove" ]; then
	ACTION="uninstall";
else
	ACTION="install";
fi;

if [ "$WEBINSTALL" == "" ]; then
	if [ "$USER" != "root" ] ; then
		echo "You are not logged in as root."
		echo "Do you want to continue and run $ACTION script as "$USER" user using sudo? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "n" ] || [ "$ANSWER" == "N" ]; then
			echo "Bye."
			exit;
		else
			SUDO_CMD="sudo ";
		fi;
	fi;
fi;

if [ "$1" == "remove" ]; then
#	uninstall;
	exit;
fi; # else run install


# running on WSL
if [ -n "$WSL_DISTRO_NAME" ]; then
	if [ ! -f /etc/wsl.conf ]; then
		$SUDO_CMD touch /etc/wsl.conf;
	fi
		
	#SYSTEM_SETTINGS="$(grep -Pzow '\[boot\]\nsystemd\=true' /etc/wsl.conf 2> /dev/null)";
	SYSTEM_SETTINGS=$(grep -w "systemd=true" /etc/wsl.conf);
	if [ "$SYSTEM_SETTINGS" == "" ]; then
		echo -e "[boot]\nsystemd=true" | $SUDO_CMD tee -a /etc/wsl.conf;
		echo "Not a corresponding wsl configuration has found, conf was modified and need a WSL system restart from Windows terminal";

		echo "Do you want to restart the $WSL_DISTRO_NAME system now? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER"  == "" ] ; then
			echo "Exiting. Please join again using wsl command."
			$SUDO_CMD poweroff -f;
		else
			echo "Bye.";
		fi;
		exit;
	fi
fi;

if [ "$APT" == "1" ]; then
	echo "Would you like to install/update docker? (y/N)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ]; then

		if [ -f "/etc/apt/keyrings/docker.gpg" ]; then
			rm -f /etc/apt/keyrings/docker.gpg
		fi;

		#install_docker_deb;
		# install docker using apt-get
		install_docker_apt

		echo "Wait..."
		sleep 5
	fi
fi;

if [ "$WEBINSTALL" == "" ]; then
	echo "Please fill in registry url (registry.format.hu): ";
	read -r DOCKER_REGISTRY_URL;
	if [ "$DOCKER_REGISTRY_URL" == "" ]; then
		DOCKER_REGISTRY_URL="registry.format.hu";
	fi
fi;

# first install
if [ ! -f "/etc/user/config/system.json" ]; then

	INIT="true";

	check_running;

	check_dirs_and_files;

	ask_envs;

	discover_services;

	# Validating previously created vaiables

	if [ "$DOCKER_REGISTRY_URL" != "" ]; then
		VAR_DOCKER_REGISTRY_URL="--env DOCKER_REGISTRY_URL=$DOCKER_REGISTRY_URL";
	fi

	if [ "$SMARTHOST_PROXY" != "" ]; then
		VAR_SMARTHOST_PROXY="--env SMARTHOST_PROXY=$SMARTHOST_PROXY";
	fi

	if [ "$LOCAL_PROXY" != "" ]; then
		VAR_LOCAL_PROXY="--env LOCAL_PROXY=$LOCAL_PROXY";
	fi

	if [ "$VPN_PROXY" != "" ]; then
		VAR_VPN_PROXY="--env VPN_PROXY=$VPN_PROXY";
	fi

	if [ "$DOMAIN" != "" ]; then
		VAR_DOMAIN="--env DOMAIN=$DOMAIN";
	fi

	if [ "$CRON" != "" ]; then
		VAR_CRON="--env CRON=$CRON";
	fi

	if [ "$DISCOVERY" != "" ]; then
		VAR_DISCOVERY="--env DISCOVERY=$DISCOVERY";
	fi

	if [ "$DISCOVERY_DIR" != "" ]; then
		VAR_DISCOVERY_DIR="--env DISCOVERY_DIR=$DISCOVERY_DIR";
		VAR_DISCOVERY_DIRECTORY="--volume $DISCOVERY_DIR/:$DISCOVERY_DIR/";
	fi

	if [ "$DISCOVERY_CONFIG_FILE" != "" ]; then
		VAR_DISCOVERY_CONFIG_FILE="--env DISCOVERY_CONFIG_FILE=$DISCOVERY_CONFIG_FILE";
		if [ "$DISCOVERY_CONFIG_DIR" != "" ]; then
			VAR_DISCOVERY_CONFIG_DIRECTORY="--volume $DISCOVERY_CONFIG_DIR/:$DISCOVERY_CONFIG_DIR/";
		fi
	fi


	# Run installer tool

	$SUDO_CMD docker run \
	$VAR_DOCKER_REGISTRY_URL \
	$VAR_SMARTHOST_PROXY \
	$VAR_LOCAL_PROXY \
	$VAR_VPN_PROXY \
	$VAR_DOMAIN \
	$VAR_CRON \
	$VAR_DISCOVERY \
	$VAR_DISCOVERY_DIR \
	$VAR_DISCOVERY_DIRECTORY \
	$VAR_DISCOVERY_CONFIG_FILE \
	$VAR_DISCOVERY_CONFIG_DIRECTORY \
	--volume $HOME/.ssh/installer:/root/.ssh/id_rsa \
	--volume /etc/user/:/etc/user/ \
	--volume /etc/system/:/etc/system/ \
	--env LETSENCRYPT_MAIL=$LETSENCRYPT_MAIL \
	--env LETSENCRYPT_SERVERNAME=$LETSENCRYPT_SERVERNAME \
	$DOCKER_REGISTRY_URL/installer-tool
else

	$SUDO_CMD docker pull $DOCKER_REGISTRY_URL/installer-tool
	$SUDO_CMD docker pull $DOCKER_REGISTRY_URL/setup

fi;

	# test - alias doesn't work inside a function
	# must be outside of if
	shopt -s expand_aliases
	source $HOME/.bash_aliases

if [ "$INIT" == "true" ]; then

	INIT_SERVICE_PATH=/etc/user/config/services

	type -a service-debian

	service-debian core-dns start
	echo "$INIT_SERVICE_PATH/core-dns.json" >> $PWD/.init_services

	if [ "$CRON" == "yes" ]; then
		service-debian cron start
		echo "$INIT_SERVICE_PATH/cron.json" >> $PWD/.init_services
	fi

	if [ "$VPN_PROXY" == "yes" ]; then
		service-debian vpn-proxy start
		echo "$INIT_SERVICE_PATH/vpn-proxy.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-vpn-smarthost-loadbalancer" >> $PWD/.init_services 
		echo "$INIT_SERVICE_PATH/firewall-vpn-proxy-postrouting" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-vpn-proxy-prerouting" >> $PWD/.init_services

	fi

	if [ "$SMARTHOST_PROXY" == "yes" ]; then
		service-debian smarthost-proxy start
		service-debian smarthost-proxy-scheduler start
		service-debian local-proxy start

		echo "$INIT_SERVICE_PATH/smarthost-proxy.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-smarthost-loadbalancer-dns.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-letsencrypt.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-smarthostloadbalancer-from-publicbackend.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-smarthost-backend-dns.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/firewall-smarthost-to-backend.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/smarthost-proxy-scheduler.json" >> $PWD/.init_services
		echo "$INIT_SERVICE_PATH/local-proxy.json" >> $PWD/.init_services
		
		echo "Would you like to run local backend? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
			service-debian local-backend start
			echo "$INIT_SERVICE_PATH/local-backend.json" >> $PWD/.init_services
			echo "$INIT_SERVICE_PATH/firewall-local-backend.json" >> $PWD/.init_services
			echo "$INIT_SERVICE_PATH/domain-local-backend.json" >> $PWD/.init_services
		fi
	fi

fi;

# install additionals - run installer-tool again but additional_install.sh instead of deploy.sh
echo "Would you like to install additional applications? (Y/n)";
read -r ANSWER;
if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ]; then

	ask_additionals;

	ADDITIONAL_SERVICES="";

	if [ "$NEXTCLOUD" == "yes" ]; then
		VAR_NEXTCLOUD="--env NEXTCLOUD=$NEXTCLOUD";
		VAR_NEXTCLOUD="$VAR_NEXTCLOUD --env NEXTCLOUD_DOMAIN=$NEXTCLOUD_DOMAIN";
		VAR_NEXTCLOUD="$VAR_NEXTCLOUD --env NEXTCLOUD_USERNAME=$NEXTCLOUD_USERNAME";
		VAR_NEXTCLOUD="$VAR_NEXTCLOUD --env NEXTCLOUD_PASSWORD=$NEXTCLOUD_PASSWORD";

		if [ ! -d "/etc/user/data/nextcloud" ]; then
			for DIR in data apps config ; do
				$SUDO_CMD mkdir -p "/etc/user/data/nextcloud/$DIR"
				$SUDO_CMD chown -R 82:82 "/etc/user/data/nextcloud/$DIR"
			done
		fi;
	
		echo "Would you like to run Nextcloud after install? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
			ADDITIONAL_SERVICES="$ADDITIONAL_SERVICES nextcloud";
		fi
	fi
	
	if [ "$BITWARDEN" == "yes" ]; then
		VAR_BITWARDEN="--env BITWARDEN=$BITWARDEN";
		VAR_BITWARDEN="$VAR_BITWARDEN --env BITWARDEN_DOMAIN=$BITWARDEN_DOMAIN";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_SERVER=$SMTP_SERVER";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_HOST=$SMTP_HOST";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_PORT=$SMTP_PORT";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_SECURITY=$SMTP_SECURITY";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_FROM=$SMTP_FROM";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_USERNAME=$SMTP_USERNAME";
		VAR_BITWARDEN="$VAR_BITWARDEN --env SMTP_PASSWORD=$SMTP_PASSWORD";
		VAR_BITWARDEN="$VAR_BITWARDEN --env DOMAINS_WHITELIST=$DOMAINS_WHITELIST";
		
		echo "                                                                                      ";
		echo "######################################################################################";
		echo "# You can access your bitwarden admin page here: https://$BITWARDEN_DOMAIN/admin #";
		echo "# You will find ADMIN TOKEN in this file: /etc/user/secret/bitwarden.json            #";
		echo "######################################################################################";
		echo "                                                                                      ";
		echo "Would you like to run Bitwarden after install? (Y/n)";
		
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
			ADDITIONAL_SERVICES="$ADDITIONAL_SERVICES bitwarden";
		fi
	fi

	if [ "$GUACAMOLE" == "yes" ]; then
		VAR_GUACAMOLE="--env GUACAMOLE=$GUACAMOLE";
		VAR_GUACAMOLE="$VAR_GUACAMOLE --env GUACAMOLE_DOMAIN=$GUACAMOLE_DOMAIN";
		VAR_GUACAMOLE="$VAR_GUACAMOLE --env GUACAMOLE_ADMIN_NAME=$GUACAMOLE_ADMIN_NAME";
		VAR_GUACAMOLE="$VAR_GUACAMOLE --env GUACAMOLE_ADMIN_PASSWORD=$GUACAMOLE_ADMIN_PASSWORD";
		VAR_GUACAMOLE="$VAR_GUACAMOLE --env TOTP_USE=$TOTP_USE";
		VAR_GUACAMOLE="$VAR_GUACAMOLE --env BAN_DURATION=$BAN_DURATION";

		echo "Would you like to run Guacamole after install? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
			ADDITIONAL_SERVICES="$ADDITIONAL_SERVICES guacamole";
		fi
	fi
	
	if [ "$SMTP" == "yes" ]; then
		VAR_SMTP="--env SMTP=$SMTP";

		echo "Would you like to run SMTP after install? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
			ADDITIONAL_SERVICES="$ADDITIONAL_SERVICES smtp";
		fi
	fi

	if [ "$ROUNDCUBE" == "yes" ]; then
		VAR_ROUNDCUBE="--env ROUNDCUBE=$ROUNDCUBE";
		VAR_ROUNDCUBE="$VAR_ROUNDCUBE --env ROUNDCUBE_IMAP_HOST=$ROUNDCUBE_IMAP_HOST";
		VAR_ROUNDCUBE="$VAR_ROUNDCUBE --env ROUNDCUBE_IMAP_PORT=$ROUNDCUBE_IMAP_PORT";
		VAR_ROUNDCUBE="$VAR_ROUNDCUBE --env ROUNDCUBE_SMTP_HOST=$ROUNDCUBE_SMTP_HOST";
		VAR_ROUNDCUBE="$VAR_ROUNDCUBE --env ROUNDCUBE_SMTP_PORT=$ROUNDCUBE_SMTP_PORT";
		VAR_ROUNDCUBE="$VAR_ROUNDCUBE --env ROUNDCUBE_UPLOAD_MAX_FILESIZE=$ROUNDCUBE_UPLOAD_MAX_FILESIZE";
		VAR_ROUNDCUBE="$VAR_ROUNDCUBE --env ROUNDCUBE_DOMAIN=$ROUNDCUBE_DOMAIN";

		echo "Would you like to run roundcube after install? (Y/n)";
		read -r ANSWER;
		if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
			ADDITIONAL_SERVICES="$ADDITIONAL_SERVICES roundcube";
		fi
	fi

	# Run installer tool
	$SUDO_CMD docker run \
	--env ADDITIONALS=true \
	--env SERVICE_DIR=$SERVICE_DIR\
	$VAR_NEXTCLOUD \
	$VAR_BITWARDEN \
	$VAR_GUACAMOLE \
	$VAR_SMTP \
	$VAR_ROUNDCUBE \
	--volume $HOME/.ssh/installer:/root/.ssh/id_rsa \
	--volume /etc/user/:/etc/user/ \
	--volume /etc/system/:/etc/system/ \
	$DOCKER_REGISTRY_URL/installer-tool
fi

WSL_DISTRO_NAME=""; # disable WSL systemd support installation - not working correctly
# running on WSL
if [ -n "$WSL_DISTRO_NAME" ]; then
	# enable systemd support on current images
	echo "Would you like to install and enable systemd support? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then

		# Run installer tool
		$SUDO_CMD docker run \
		--env WSL_DISTRO_NAME=$WSL_DISTRO_NAME \
		--volume $HOME/.ssh/installer:/root/.ssh/id_rsa \
		--volume /etc/user/:/etc/user/ \
		--volume /etc/system/:/etc/system/ \
		--volume /usr/local/bin/:/usr/local/bin/ \
		$DOCKER_REGISTRY_URL/installer-tool

		/usr/local/bin/wsl2-systemd-script.sh
	fi;
fi;

shopt -s expand_aliases
source $HOME/.bash_aliases

if [ "$ADDITIONAL_SERVICES" != "" ]; then 
	for ADDITIONAL_SERVICE in $(echo $ADDITIONAL_SERVICES); do
		service-debian $ADDITIONAL_SERVICE start
		echo "$INIT_SERVICE_PATH/$ADDITIONAL_SERVICE.json" >> $PWD/.init_services
	done
fi

if [ "$DISCOVERY" != "yes" ] ; then
	discover_services;
fi;

if [ "$DISCOVERY" == "yes" ] ; then
	$SUDO_CMD chmod a+x $DISCOVERY_DIR/service-discovery.sh
	$DISCOVERY_DIR/service-discovery.sh $DISCOVERY_CONFIG_FILE;
	source $DISCOVERY_CONFIG_FILE;
	cat $DEST_FILE;

	echo "Would you like to run discovered services? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then
		$SUDO_CMD chmod a+x $DISCOVERY_DIR/service-files.sh
		$DISCOVERY_DIR/service-files.sh $DEST_FILE &
	fi;
fi;

if [ "$DEBIAN" == "true" ] || [ "$GENTOO" == "true" ] ; then

	echo "Do you want to start the discovered and actually started services at the next time when your system restarting? (Y/n)";
	read -r ANSWER;
	if [ "$ANSWER" == "y" ] || [ "$ANSWER" == "Y" ] || [ "$ANSWER" == "" ] ; then

		cp $DISCOVERY_CONFIG_FILE $DISCOVERY_CONFIG_FILE".copy";
		cp $DEST_FILE $DEST_FILE".copy";

		DISCOVERY_CONFIG_FILENAME=$(basename $DISCOVERY_CONFIG_FILE);
		source $DISCOVERY_CONFIG_FILE;
		{
			echo '#!/bin/bash';
			echo 'SOURCE_DIRS="'$SOURCE_DIRS'"; # separator space or |';
			echo 'DIRNAME="'$DIRNAME'"; # separator space or |';
			echo 'FILENAME="'$FILENAME'"; # separator space or |';
			echo 'KEYS="'$KEYS'"; # separator space or |';
			echo 'DEST_FILE="/usr/local/etc/results.txt";';
			echo 'USE_SUDO=0;';
		} > /tmp/$DISCOVERY_CONFIG_FILENAME

		$SUDO_CMD mkdir -p /usr/local/etc;

		$SUDO_CMD mv /tmp/$DISCOVERY_CONFIG_FILENAME /usr/local/etc/$DISCOVERY_CONFIG_FILENAME

		{
			cat $PWD/.init_services;
			cat $DEST_FILE;
		} > /tmp/$DEST_FILE

		$SUDO_CMD mv /tmp/$DEST_FILE /usr/local/etc/$DEST_FILE


		if [ "$DEBIAN" == "true" ] ; then
			{
				echo "
[Unit]
Description=Discover services

[Service]
Type=oneshot
ExecStart=/usr/local/bin/service-files.sh /usr/local/etc/results.txt restart

[Install]
WantedBy=multi-user.target
";

			} > /tmp/discovery.service
			$SUDO_CMD mv /tmp/discovery.service /etc/systemd/system/discovery.service
			$SUDO_CMD systemctl enable discovery.service

		elif [ "$GENTOO" == "true" ] ; then
			$SUDO_CMD echo "/usr/local/bin/service-files.sh /usr/local/etc/results.txt restart" > /etc/local.d/service-file.start;
			$SUDO_CMD chmod a+x /etc/local.d/service-file.start;
		fi;
	fi;
fi;

rm $PWD/.init_services

