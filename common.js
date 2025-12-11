function check_deployments() {
  var url  = 'scan.php?op=check_deployments';
  jQuery.get(url, function(data) {
      console.log('check_deployments: '+data);
      if (data=="WAIT" || data=="") {
	  setTimeout(check_deployments, 1000);
      }
      else {
	// manage2
	apps.length = 0; // reset
	apps.push(...JSON.parse(data)); // push each element
	console.log(apps);
	document.getElementById('installAppsBtn').click();

	// manage
	html_data = '';
	data = jQuery.parseJSON(data);
	for (var k in data) {
		console.log(data[k]);
		service_name = data[k].name;
		orig_service_name = data[k].orig_name;
		version = data[k].version;
		subtitle = data[k].subtitle;
		installed = data[k].installed;
		if (installed=='true') {
			html_data += '<div><a href="#" onclick="reinstall(\''+service_name+'\',\''+service_name+'\')">'+orig_service_name+'</a> - '+version+' - INSTALLED</div>';
		}
		else {
			html_data += '<div><a href="#" onclick="load_template(\''+service_name+'\',\''+service_name+'\')">'+orig_service_name+'</a> - '+version+'</div>';
		}
		html_data += '<div id="'+service_name+'" class="deployment"></div>';
	}
	jQuery("#deployments").html(html_data);

      }
  });
}

function get_deployments() {
  var url  = 'scan.php?op=deployments';
  jQuery.get(url, function(data) {
  	console.log('deployments: '+data);
	if (data=="OK") {
      		setTimeout(check_deployments, 1000);
	}
	else jQuery("#deployments").html(data);
  });
}

function check_system() {
  var url  = 'scan.php?op=check_system&services=1';
  jQuery.get(url, function(data) {
    console.log('check_system: '+data);
      if (data=="WAIT" || data=="") {
        setTimeout(check_system, 1000);
      }
      else {
        jQuery("#system").html(data);
      }
  });
}

function get_system() {
  var url  = 'scan.php?op=system';
  jQuery.get(url, function(data) {
    console.log('system: '+data);
	  if (data=="OK") {
      		setTimeout(check_system, 1000);
	  }
	  else alert(data);
  });
}

function check_repositories() {
  var url  = 'scan.php?op=check_repositories';
  jQuery.get(url, function(data) {
      console.log('check_repositories: '+data);
      if (data=="WAIT" || data=="") {
		setTimeout(check_repositories, 500);
      }
      else {
		jQuery("#repositories").html(data);
      }
  });
}

function get_repositories() {
  var url  = 'scan.php?op=repositories';
  jQuery.get(url, function(data) {
	console.log('repositories: '+data);
	if (data=="OK") {
		setTimeout(check_repositories, 500);
		//get_deployments();
	}
	else alert(data);
  });
}

function add_repository() {
  var url  = 'scan.php?op=add_repository&repo='+jQuery('#repository').val();
  jQuery.get(url, function(data) {
	console.log('add_repository: '+data);
	  if (data=="OK") {
	  }
	get_repositories();
  });
}

function check_vpn(service) {
  var url = 'scan.php?op=check_vpn';
  jQuery.ajax({
    url: url,
    method: 'GET',
    success: function(data) {
        console.log('check_vpn: '+data);
        if (data=="1") { // VPN ON
		$('#pro_off').hide();
		$('#pro_on').show();
		$('#vpn_off').hide();
		$('#vpn_on').show();
		//document.getElementById('vpnToggle').checked = true;
		//document.querySelector(".switch-label").textContent = "ON";
	}
	else if (data=="2") {
		$('#pro_off').hide();
		$('#pro_on').show();
		$('#vpn_on').hide();
		$('#vpn_off').show();
		//document.getElementById('vpnToggle').checked = false;
		//document.querySelector(".switch-label").textContent = "OFF";
        }
        else { // data == 0
		$('#pro_on').hide();
		$('#pro_off').show();
        }
        setTimeout(check_vpn, 10000);
    },
    error: function(xhr, status, error) {
      console.warn('check_vpn error: ' + status + ' - ' + error);
      setTimeout(check_vpn, 10000, service);
    }
  });
}

function check_save_vpn(service) {
  var url = 'scan.php?op=check_vpn';
  jQuery.ajax({
    url: url,
    method: 'GET',
    success: function(data) {
        console.log('check_save_vpn: '+data);
        if (data=="1") { // save_vpn has finished or VPN ON
	  const vpn_div = document.getElementById("vpn");
	  if (vpn_div) {
	  	vpn_div.innerHTML = '<div class="loading">VPN start process has finished</div>';
		setTimeout(function() {
			document.getElementById('installAppsBtn').click();
		}, 2000);
	  }
	}
	else setTimeout(check_save_vpn, 1000);
    },
    error: function(xhr, status, error) {
      console.warn('check_save_vpn error: ' + status + ' - ' + error);
      setTimeout(check_save_vpn, 1000);
    }
  });
}

function save_vpn() {
  var url  = 'scan.php?op=save_vpn&vpn_domain='+jQuery('#vpn_domain').val()+'&vpn_pass='+jQuery('#vpn_pass').val()+'&letsencrypt_mail='+jQuery('#letsencrypt_mail').val()+'&letsencrypt_servername='+jQuery('#letsencrypt_servername').val();

  jQuery.get(url, function(data) {
	console.log('save_vpn: '+data);
	  if (data=="OK") {
		check_save_vpn();
	  }
	//get_vpn();
  });
}

function check_updates() {
  var url  = 'scan.php?op=check_updates';
  jQuery.get(url, function(data) {
	console.log('check_updates: '+data);
      if (data=="WAIT" || data=="") {
        setTimeout(check_updates, 1000);
      }
      else {
        jQuery("#updates").html(data);
      }
  });
}

function get_updates() {
  var url  = 'scan.php?op=updates';
  jQuery.get(url, function(data) {
	console.log('updates: '+data);
	  if (data=="ERROR") {
        	jQuery("#updates").html('Searching for updates is in progress...');
	  }
      	setTimeout(check_updates, 1000);
  });
}

function check_upgrade(service) {
  var url = 'scan.php?op=check_upgrade&service=' + service;
  jQuery.ajax({
    url: url,
    method: 'GET',
    success: function(data) {
      console.log('check_upgrade ' + service + ': ' + data);
      if (data !== "") {
        jQuery("#status_" + service).html(data);
      }
      if (data !== "OK") {
        setTimeout(check_upgrade, 1000, service);
      } else {
        console.log('upgrade end: ' + service);
        jQuery("#status_" + service).html('Upgrade has finished');
      }
    },
    error: function(xhr, status, error) {
      console.warn('check_upgrade error: ' + status + ' - ' + error);
      setTimeout(check_upgrade, 5000, service);
    }
  });
}

function upgrade(service) {
  var url  = 'scan.php?op=upgrade&service='+service;
  jQuery("#status_"+service).html('Upgrade has started');
  console.log('upgrade start: '+service);
  jQuery.get(url, function(data) {
          console.log('check_upgrade '+service+': '+data);
          if (data=="OK") {
                setTimeout(check_upgrade, 1000, service);
          }
	  else jQuery("#status_"+service).html(data);
  });
}

function load_template(additional, block) {

  jQuery("div.deployment").each(function(index) {
        $(this).html('');
  });
  //jQuery("#"+block).html('Loading '+additional+' template...');
  jQuery("#"+block).html('<div class="loading">Loading...</div>');
  var url  = 'scan.php?op=deployment&additional='+additional;
  jQuery.get(url, function(data) {
	console.log('load_template: '+data);
	  if (data=="OK") {
      		setTimeout(check_deployment, 1000, additional);
	  }
  });
}

function check_reinstall(additional) {
  var url  = 'scan.php?op=check_reinstall';
  jQuery.get(url, function(data) {
	console.log('check_reinstall: '+data);
      if (data!="") {
	      jQuery("#"+additional).html(data);
	      jQuery("#popupText").html(data); // manage2
      }
      else setTimeout(check_reinstall, 1000, additional);
  });
}

function reinstall(additional, block) {
  jQuery("div.deployment").each(function(index) {
        $(this).html('');
  });
  //jQuery("#"+block).html('Loading '+additional+' template...');
  jQuery("#"+block).html('<div class="loading">Loading...</div>');
  var url  = 'scan.php?op=reinstall&additional='+additional;
  jQuery.get(url, function(data) {
	console.log('reinstall '+additional+': '+data);
	  if (data=="OK") {
      		setTimeout(check_reinstall, 1000, additional);
	  }
  });
}

function check_uninstall(additional) {
  var url  = 'scan.php?op=check_uninstall&additional='+additional;
  jQuery.get(url, function(data) {
        console.log('check_uninstall '+additional+': '+data);
      if (data!="") {
              jQuery("#"+additional).html(data);
	      jQuery("#popupText").html('<div class="loading">'+data+'</div>'); // manage2
      }
      if (data!="OK") {
              setTimeout(check_uninstall, 1000, additional);
      }
      else {
	  jQuery("#popupText").html('<div class="loading">Uninstall has finished</div>'); // manage2
	  jQuery("#"+additional).html('Uninstall has finished');
	  get_deployments();
      }
  });
}

function uninstall(additional) {

  jQuery("div.deployment").each(function(index) {
        $(this).html('');
  });
	data = '<div class="confirm"><form action="#" method="post"><div class="row">You are going to uninstall '+additional.toUpperCase()+'.<br>Are you sure? If you click on Uninstall button below then all your data will be deleted.<br><br></div><div class="row buttons"><div class="mb-3"><button class="btn" type="button" onclick="confirm_uninstall(\''+additional+'\')">Uninstall</button></div><div class="mb-3" style="margin-left:200px;float:"><button class="btn" onclick="reinstall(\''+additional+'\',\''+additional+'\')">Cancel</button></div></div></form></div>';
	jQuery("#"+additional).html(data);
	jQuery("#popupText").html(data); // manage2
}

function confirm_uninstall(additional) {
  jQuery("#"+additional).html('<div class="loading">Loading...</div>');
  jQuery("#popupText").html('<div class="loading">Loading...</div>'); // manage2
  var url  = 'scan.php?op=uninstall&additional='+additional;
  jQuery.get(url, function(data) {
        console.log('uninstall '+additional+': '+data);
          if (data!="") {
                jQuery("#"+additional).html(data);
	        jQuery("#popupText").html('<div class="loading">'+data+'</div>'); // manage2
                setTimeout(check_uninstall, 1000, additional);
          }
  });
}

function update_deployment(additional) {
  //jQuery("#"+additional).html('Loading...');
  pars = '';
  jQuery('input.additional_'+additional).each(function(index) {
	console.log('Field ' + $(this).attr('name') + ': ' + $(this).val());
	//pars += '&'+$(this).attr('id') + '=' + $(this).val();
	pars += '&'+$(this).attr('name') + '=' + $(this).val();
  });
  //console.log(pars);
  var url  = 'scan.php?op=edit&additional='+additional+pars;
  jQuery.get(url, function(data) {
        console.log('edit '+additional+': '+data);
          if (data!="") {
                jQuery("#"+additional).html(data);
	        jQuery("#popupText").html(data); // manage2
                setTimeout(check_deployment, 1000, additional);
          }
  });
}

function request_letsencrypt(domain) {
  var url  = 'scan.php?op=request_letsencrypt&domain='+domain;
  jQuery.get(url, function(data) {
      console.log('letsencrypt '+domain);
      if (data!="") {
          jQuery("#letsencrypt").html(data);
      }
      setTimeout(check_letsencrypt, 2000, domain);
  });
}

function check_letsencrypt(domain) {
  var url  = 'scan.php?op=check_letsencrypt&domain='+domain;
  jQuery.get(url, function(data) {
      console.log('check_letsencrypt '+domain);
      if (data!="") {
          jQuery("#letsencrypt").html(data);
      }
      //setTimeout(check_letsencrypt, 1500, domain);
  });
}

function check_deployment(additional) {
  var url  = 'scan.php?op=check_deployment&additional='+additional;
  jQuery.get(url, function(data) {
      console.log('check_deployment '+additional);
      console.log('check_deployment data: '+data);
      if (data!="") {
	      jQuery("#"+additional).html(data);
	      jQuery("#popupText").html(data); // manage2
      }
      else setTimeout(check_deployment, 1000, additional);
  });
}

function deploy(additional) {
  pars = '';
  jQuery('input.additional_'+additional).each(function(index) {
	console.log('Field ' + $(this).attr('name') + ': ' + $(this).val());
	//pars += '&'+$(this).attr('id') + '=' + $(this).val();
	pars += '&'+$(this).attr('name') + '=' + $(this).val();
  });
  //console.log(pars);
  var url  = 'scan.php?op=deploy&additional='+additional+pars;
  jQuery.get(url, function(data) {
	console.log('deploy '+additional+': '+data);
	  if (data!="") {
	        jQuery("#"+additional).html(data);
	        jQuery("#popupText").html('<div class="loading">'+data+'</div>'); // manage2
      		setTimeout(check_deployment, 1000, additional);
	  }
  });
}

function redeploy(additional) {
  pars = '';
  jQuery('input.additional_'+additional).each(function(index) {
	console.log('Field ' + $(this).attr('name') + ': ' + $(this).val());
	//pars += '&'+$(this).attr('id') + '=' + $(this).val();
	pars += '&'+$(this).attr('name') + '=' + $(this).val();
  });
  //console.log(pars);
  var url  = 'scan.php?op=redeploy&additional='+additional+pars;
  jQuery.get(url, function(data) {
	console.log('redeploy '+additional+': '+data);
	  if (data!="") {
	        jQuery("#"+additional).html(data);
		jQuery("#popupText").html('<div class="loading">'+data+'</div>'); // manage2
      		setTimeout(check_deployment, 1000, additional);
	  }
  });
}

function check_services() {
  var url  = 'scan.php?op=check_services';
  jQuery.get(url, function(data) {
	console.log('check_services: '+data);
      if (data=="WAIT" || data=="") {
        setTimeout(check_services, 1000);
      }
      else {
	jQuery("#services").html(data);
      }
  });
}

function get_services() {
  var url  = 'scan.php?op=services';
  jQuery.get(url, function(data) {
	console.log('services: '+data);
  	setTimeout(check_services, 1000);
  });
}

function get_proxy_html() {
        proxy_html = `
	<form class="form-install" action="#" method="post" id="save_vpn">
        <div class="app-fields">
        	<div class="app-field">
		<div class="row">
			<label for="vpn_domain">Please add domain url to download the VPN hash from:</label>
		    <div class="input-container">
				<input type="text" class="form-control" name="VPN_DOMAIN" id="vpn_domain" value="https://pro.safebox.network" size="40">
		    </div>
		</div>
		</div>
        	<div class="app-field">
		<div class="row">
			<label for="vpn_pass">Please type in the generated VPN passkey (8 digits):</label>
		    <div class="input-container">
				<input type="text" class="form-control" name="VPN_PASS" id="vpn_pass" value="" maxlength="8" size="10">
		    </div>
		</div>
		</div>
        	<div class="app-field">
		<div class="row">
			<label for="letsencrypt_mail">Please add the letsencrypt mail address:</label>
		    <div class="input-container">
				<input type="email" class="form-control" name="LETSENCRYPT_MAIL" id="letsencrypt_mail" value="">
		    </div>
		</div>
		</div>
        	<div class="app-field">
		<div class="row">
			<label for="letsencrypt_servername">Please add letsencrypt server name (default is letsencrypt but you can add zerossl too):</label>
		    <div class="input-container">
				<input type="text" class="form-control" name="LETSENCRYPT_SERVERNAME" id="letsencrypt_servername" value="letsencrypt">
		    </div>
		</div>
		</div>
	</div>
	<div class="row buttons">
	    <div class="mb-3">
		<button class="btn btn-lg btn-primary btn-block" type="button" id="vpn_save_btn"> Save </button>
	    </div>
	</div>
	</form>
	<script>
	jQuery('#vpn_save_btn').click(function() {
		console.log('vpn save');
		save_vpn();
		jQuery('#vpn').html('<div class="loading">VPN start process in progress. Please wait...</div>');
	});
	</script>
        `;
	jQuery("#vpn").html(proxy_html);
}

function check_containers() {
  var url  = 'scan.php?op=check_containers';
  jQuery.get(url, function(data) {
	console.log('check_containers: '+data);
      if (data!="") {
	      jQuery("#containers").html(data);
      }
      else setTimeout(check_containers, 1000);
  });
}

function get_containers() {
  var url  = 'scan.php?op=containers';
  jQuery.get(url, function(data) {
	console.log('containers: '+data);
	  if (data=="OK") {
      		setTimeout(check_containers, 1000);
	  }
  });
}

function get_version() {
  var url  = 'scan.php?op=version';
  jQuery.get(url, function(data) {
	console.log('version: '+data);
	jQuery('#logo').attr('title',data);
  });
}

function check_session() {
	var url  = 'check_session.php';
	jQuery.get(url, function(data) {
		console.log('check_session: '+data);
		if (data=="") {
			document.location='signin.html';
			exit;
		}
	});
}

jQuery(document).ready(function(){

	check_session();

	get_version();
	get_repositories();
	get_deployments();
	get_system();
	check_vpn();

	jQuery('#deployments_btn').click(function() {
		jQuery('#services').hide();
		jQuery('#deployments').toggle();
	});

	jQuery('#services_btn').click(function() {
		get_services();
		jQuery('#deployments').hide();
		jQuery('#services').toggle();
	});

	jQuery('#settings_btn').click(function() {
		jQuery('#settings').toggle();
		jQuery('#default').toggle();
		jQuery('#vpn').hide();
	});

	jQuery('#vpn_btn').click(function() {
		jQuery('#vpn').toggle();
		jQuery('#settings').hide();
	});

	jQuery('#vpn_cancel_btn').click(function() {
		jQuery('#vpn').hide();
	});

	jQuery('#update_btn').click(function() {
		jQuery('#updates').html('<div class="loading">Looking for updates...</div>');
		get_updates();
	});

	jQuery('#add_repo').submit(function() {
		jQuery('#repositories').html('<div class="loading">Loading...</div>');
		add_repository();
		return false;
	});

	jQuery('#save_vpn').submit(function() {
		save_vpn();
		jQuery('#vpn').html('<div class="loading">Loading...</div>');
		return false;
	});

	jQuery('select#smarthost').click(function() {
		if (jQuery(this).val()=='yes') jQuery('#div_smarthost').show();
		else jQuery('#div_smarthost').hide();

		if (jQuery("#smarthost").val()=='no' && jQuery("#localproxy").val()=='yes') {
			alert("Warning! Local proxy will not work without smarthost proxy service.");
		}
	});
	jQuery('select#vpn').click(function() {
		if (jQuery(this).val()=='yes') jQuery('#div_vpn').show();
		else jQuery('#div_vpn').hide();
	});
/*
	jQuery('select#discovery').click(function() {
		if (jQuery(this).val()=='yes') jQuery('#div_discover').show();
		else jQuery('#div_discover').hide();
	});
	jQuery('select#additionals').click(function() {
		if (jQuery(this).val()=='yes') jQuery('#div_additionals').show();
		else jQuery('#div_additionals').hide();
	});
	jQuery('select#nextcloud').click(function() {
		if (jQuery(this).val()=='Y') jQuery('#div_nextcloud').show();
		else jQuery('#div_nextcloud').hide();
	});
	jQuery('select#bitwarden').click(function() {
		if (jQuery(this).val()=='Y') jQuery('#div_bitwarden').show();
		else jQuery('#div_bitwarden').hide();
	});
	jQuery('select#bitwarden_smtp').click(function() {
		if (jQuery(this).val()=='3') jQuery('#div_bitwarden_smtp').show();
		else jQuery('#div_bitwarden_smtp').hide();
	});
	jQuery('select#guacamole').click(function() {
		if (jQuery(this).val()=='Y') jQuery('#div_guacamole').show();
		else jQuery('#div_guacamole').hide();
	});
	jQuery('select#smtp_server').click(function() {
		if (jQuery(this).val()=='Y') jQuery('#div_smtp_server').show();
		else jQuery('#div_smtp_server').hide();
	});
	jQuery('select#roundcube').click(function() {
		if (jQuery(this).val()=='Y') jQuery('#div_roundcube').show();
		else jQuery('#div_roundcube').hide();
	});
*/
});

