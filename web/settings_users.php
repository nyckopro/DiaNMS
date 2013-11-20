<div id='user_list'>
 <ul>
<?php
	include("inc/config.inc.php");	
	$sql = "select IdUser,name from Users where IdUser>0";
	$res = mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		echo "<li><a href='lib/get.php?data=users&id=".$row["IdUser"]."'>".$row["name"]."</a></li>";
	}
?>
 </ul>
</div>
<div id='user_detail'>
	<form class='form' action='lib/set.php'>
		<input type='hidden' name='form' value='users' />
		<input type='hidden' name='id' />
		<input type='submit' value='Guardar' />
		<label for='username'>Username: </label>
		<input type='text' name='username' readonly/><br>
		<label for='name'>Nombre: </label>
		<input type='text' name='name' /><br>
		<label for='email'>E-Mail: </label>
		<input type='text' name='email' /><br>
		<label for='idgroup'>Grupo: </label>
		<select name='idgroup'></select>
	</form>
</div>
