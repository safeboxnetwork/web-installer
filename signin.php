<?php

// Ensure session files can be written (default save path may not be writable)
ini_set('session.save_path', '/tmp');

session_start();

$auth_file = "/tmp/.htpasswd";

// If the auth file does not exist yet, redirect back to signin
if (!file_exists($auth_file)) {
    header('Location: signin.html?error=noauth');
    exit;
}

$content = file_get_contents($auth_file);
if ($content === false) {
    header('Location: signin.html?error=noauth');
    exit;
}

$arr = explode(":", $content);
if (count($arr) < 2) {
    header('Location: signin.html?error=noauth');
    exit;
}

$user = $arr[0];
$pass_hash = trim($arr[1]);

if ($user == $_POST["AUTH_USERNAME"]) {

	$pass_input = trim($_POST["AUTH_PASSWORD"]);

	try {
		$ok = password_verify($pass_input, $pass_hash);
	} catch (ValueError $e) {
		$ok = false; // invalid hash stored in auth file
	}

	if ($ok) {
		//echo "OK – jelszó jó!";
		$_SESSION["username"] = $_POST["AUTH_USERNAME"];
		header('Location: manage.html');
		exit;
	} else {
		//echo "ROSSZ – jelszó hibás!";
		unset($_SESSION["username"]);
		header('Location: signin.html?error=wrong');
		exit;
	}

}
else {
	header('Location: signin.html?error=wrong');
	exit;
}

?>
