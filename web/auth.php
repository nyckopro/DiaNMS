<?php
	setcookie("dianms", "nycko", time() + 32140800);
	session_start();
	if ( !$_SESSION["username"] && $_COOKIE["dianms"]){
		header("location:index.php");
	}
?>
