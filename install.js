
function updateProgress(percentage) {
    // Clamp percentage between 0 and 99.9 (never reach 100)
    percentage = Math.max(0, Math.min(99.9, percentage));
    
    progressBar.style.width = percentage + '%';
    progressText.textContent = Math.round(percentage) + '%';
    currentProgress = percentage;
}

function setProgress(percentage) {
    clearInterval(progressInterval);
    updateProgress(percentage);
    startProgress(60000);
}

function startProgress(duration) {
    clearInterval(progressInterval);
    const startTime = Date.now() - (currentProgress / 100) * duration;
    
    progressInterval = setInterval(() => {
	const elapsed = Date.now() - startTime;
	// Use exponential approach to 100% - gets slower as it approaches
	let progress = (1 - Math.exp(-4 * elapsed / duration)) * 100;
	
	// Ensure it never reaches 100%
	progress = Math.min(progress, 99.9);
	
	updateProgress(progress);
	
	// Stop when we've been running for 60+ seconds
	if (elapsed >= duration) {
	    clearInterval(progressInterval);
	}
    }, 100); // Update every 100ms
}

function resetProgress() {
    clearInterval(progressInterval);
    updateProgress(0);
}

// Example: Simulate loading with custom speeds
function simulateLoading(duration = 3000) {
    clearInterval(progressInterval);
    const startTime = Date.now();
    
    progressInterval = setInterval(() => {
	const elapsed = Date.now() - startTime;
	const progress = Math.min((elapsed / duration) * 100, 99.9);
	
	updateProgress(progress);
	
	if (elapsed >= duration) {
	    clearInterval(progressInterval);
	}
    }, 16); // ~60fps
}

function redirectToInstall() {
	setProgress(100);
	window.location.href = 'install.html?t='+Date.now();
}

function redirectToManage() {
	setProgress(100);
	window.location.href = 'manage.html?t='+Date.now();
}

function start_system() {
  var url  = 'scan.php?op=system';
  $.get(url, function(data){
    console.log('start_system: '+data);
    if (data=='OK') {
      $("#info").html('Scanning for previous install. Please wait...');
      check_system();
    }
    else {
      $("#info").html('Scanning for previous install has aborted...');
    }
  });
}

function check_system() {
  var url  = 'scan.php?op=check_system';
  $.get(url, function(data){
    console.log('check_system: '+data);
    if (data=='NEW') {
      $("#info").html('No previous install has found...');
      setTimeout(redirectToInstall, 3000);
    }
    else if (data=='EXISTS') {
      $("#info").html('Previous install has found...');
      setProgress(80);
      setTimeout(redirectToManage, 3000);
    }
    else if (data=='WAIT') {
      setTimeout(check_system, 1000);
    }
    else {
	    // UNEXPECTED ERROR
    }
  });
}

function check_redis() {

  var url  = 'scan.php?op=redis';
  $.get(url, function(data){
    console.log('check_redis: '+data);
    if (data=='OK') {
      $("#info").html('Redis server - OK');
      check_install();
    }
    else {
      $("#info").html('Redis server is not available...');
      setTimeout(check_redis, 1000);
    }
  });
}

function check_directory() {

  var url  = 'scan.php?op=directory';
  $.get(url, function(data){
    console.log('check_directory: '+data);
    if (data=='OK') {
      $("#info").html('Connection is ready - OK');
      check_install();
    }
    else {
      $("#info").html('Shared directory is not available...');
    }
  });
}

function check_interface() {

  var url  = 'scan.php?op=get_interface';
  $.get(url, function(data){
    console.log('check_interface: '+data);
    if (data=='redis') {
  	check_redis();
    }
    else if (data=='directory') {
  	check_directory();
    }
    else {
	$("#info").html('Invalid interface definition...');
    }
  });
}

function check_install() {

    console.log('install: '+install);
    if (install==1) {
	  var url  = 'scan.php?op=check_install';
	  $.get(url, function(data){
	    console.log('check_install:'+data+' counter: '+counter);
	    if (data=='INSTALLED') {
		redirectToManage();
	    }
	    else {
	      counter+=1
	      $("#info").html('Please wait ...');
	      setTimeout(check_install, 1000);
	    }
	  });
    }
    else start_system(); // scan
}

