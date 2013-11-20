<?php	
	include("parametros.php");
	$dbcon=mysql_connect($parametro["db"]["host"],$parametro["db"]["user"],$parametro["db"]["pass"]);
	if (! mysql_select_db($parametro["db"]["dbname"])){
		die ('Can\'t use foo : ' . mysql_error());
	}
?>
