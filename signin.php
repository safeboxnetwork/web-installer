<?php

session_start();

$content = file_get_contents("/tmp/.htpasswd");
$arr = explode(":",$content);

$user = $arr[0];
$pass_hash = trim($arr[1]);

if ($user == $_POST["AUTH_USERNAME"]) {

	$pass_input = trim($_POST["AUTH_PASSWORD"]);

	if (password_verify($pass_input, $pass_hash)) {
		//echo "OK – jelszó jó!";
		$_SESSION["username"] = $_POST["AUTH_USERNAME"];
		header('Location: manage.html');
	} else {
		//echo "ROSSZ – jelszó hibás!";
		unset($_SESSION["username"]);
		header('Location: signin.html');
	}

}
else header('Location: signin.html');

?>
