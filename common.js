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

	// manage
	html_data = '';
	data = jQuery.parseJSON(data);
	for (var k in data) {
		console.log(data[k]);
		service_name = data[k].name;
		orig_service_name = data[k].orig_name;
		content = data[k].content;
		installed = data[k].installed;
		if (installed=='true') {
			html_data += '<div><a href="#" onclick="reinstall(\''+service_name+'\',\''+service_name+'\')">'+orig_service_name+'</a> - '+content+' - INSTALLED</div>';
		}
		else {
			html_data += '<div><a href="#" onclick="load_template(\''+service_name+'\',\''+service_name+'\')">'+orig_service_name+'</a> - '+content+'</div>';
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
		get_deployments();
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

function check_vpn() {
  var url  = 'scan.php?op=check_vpn';
  jQuery.get(url, function(data) {
        console.log('check_vpn: '+data);
        if (data=="2") {
          $('#vpn_off').hide();
          $('#vpn_on').show();
        }
        else {
          $('#vpn_on').hide();
          $('#vpn_off').show();
        }
        setTimeout(check_vpn, 10000);
  });
}

function save_vpn() {
  var url  = 'scan.php?op=save_vpn&vpn_domain='+jQuery('#vpn_domain').val()+'&vpn_pass='+jQuery('#vpn_pass').val()+'&letsencrypt_mail='+jQuery('#letsencrypt_mail').val()+'&letsencrypt_servername='+jQuery('#letsencrypt_servername').val();

  jQuery.get(url, function(data) {
	console.log('save_vpn: '+data);
	  if (data=="OK") {
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

function check_upgrade() {
  var url  = 'scan.php?op=check_upgrade';
  jQuery.get(url, function(data) {
        console.log('check_upgrade: '+data);
      if (data=="WAIT" || data=="") {
        setTimeout(check_upgrade, 1000);
      }
      else {
              // TODO
      }
  });
}

function upgrade(service) {
  var url  = 'scan.php?op=upgrade&service='+service;
  console.log('upgrade start: '+service);
  jQuery.get(url, function(data) {
        console.log('upgrade end: '+service);
          if (data=="OK") {
                setTimeout(check_upgrade, 1000);
          }
  });
}

function load_template(additional, block) {

  jQuery("div.deployment").each(function(index) {
        $(this).html('');
  });
  jQuery("#"+block).html('Loading '+additional+' template...');
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
  jQuery("#"+block).html('Loading '+additional+' template...');
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
      }
      if (data!="OK") {
              setTimeout(check_uninstall, 1000, additional);
      }
      else {
	  jQuery("#"+additional).html('Uninstall has finished');
	  get_deployments();
      }
  });
}

function uninstall(additional) {

  jQuery("div.deployment").each(function(index) {
        $(this).html('');
  });
	data = '<fieldset><form action="#" method="post"><div class="row">YOU ARE GOING TO UNINSTALL '+additional.toUpperCase()+'.<br>ARE YOU SURE? IF YES, PLEASE CLICK ON THE BUTTON BELOW.<br><br></div><div class="row"><div class="mb-3"><button class="btn btn-lg btn-primary btn-block" type="button" onclick="confirm_uninstall(\''+additional+'\')">Uninstall</button></div></div></form></fieldset>';
	jQuery("#"+additional).html(data);
}

function confirm_uninstall(additional) {
  jQuery("#"+additional).html('Loading...');
  var url  = 'scan.php?op=uninstall&additional='+additional;
  jQuery.get(url, function(data) {
        console.log('uninstall '+additional+': '+data);
          if (data!="") {
                jQuery("#"+additional).html(data);
                setTimeout(check_uninstall, 1000, additional);
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
      //console.log('check_deployment data: '+data);
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

jQuery(document).ready(function(){

	get_repositories();
	get_system();
	get_services();
	check_vpn();

	jQuery('#deployments_btn').click(function() {
		jQuery('#services').hide();
		jQuery('#deployments').toggle();
	});

	jQuery('#services_btn').click(function() {
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
		jQuery('#updates').html('Looking for updates... Please wait...');
		get_updates();
	});

	jQuery('#add_repo').submit(function() {
		jQuery('#repositories').html('Loading...');
		add_repository();
		return false;
	});

	jQuery('#save_vpn').submit(function() {
		save_vpn();
		jQuery('#vpn').html('Loading...');
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

