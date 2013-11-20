<?php
include("inc/config.inc.php");
include("inc/permissions.php");
$sql = "select * from Settings";
$res = mysql_query($sql);
while($row = mysql_fetch_array($res)){
	switch($row["key"]){
		case "idGroupAdmin": $idGroupAdmin = $row["value"]; break;
		case "community": $community = $row["value"]; break;
		case "emailAlertas": $emailAlertas = $row["value"]; break;
	}
}
?>
<div class='param_general'>
	<form class='form' action='lib/set.php'>
		<input type='hidden' name='form' value='settings' />
		<label for=email>Email Alertas: </label>
		<input type='text' name='email' value='<?php echo $emailAlertas; ?>'/><br>
		<label for=idGroupAdmin>Grupo de Administracion: </label>
		<input type='text' name='idGroupAdmin' value='<?php echo $idGroupAdmin; ?>'/><br>
		<label for=community>Comunidad por defecto: </label>
		<input type='text' name='community' value='<?php echo $community; ?>'/><br>
		<input type=submit value='Guardar' />
	</form>
</div>
