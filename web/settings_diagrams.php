	<div id='diagram_group'>
	 <ul>
<?php
//	include("auth.php");
//	include("inc/config.inc.php");
	$sql = "select * from DiagramsGroups where IdDiagramGroup<>4;";
	$res = mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		echo "<li><a href='lib/get.php?data=diagram_group&param=all&id=".$row["IdDiagramGroup"]."'>".$row["name"]."</a></li>";
	}
?>
	 </ul>
	</div>
	<div id='diagram_list'><ul></ul></div>
	<div id='diagram_detail'>
		<form class='form' action='lib/set.php'>
			<input type='hidden' name='form' value='diagrams' />
			<input type='hidden' name='id' />
			<input type='submit' value='Guardar' />
			<label for='file_name'>Diagrama: </label>
			<input type='text' name='file_name' /><br>
			<label for='modifico'>Asignado a: </label>
			<select name='modifico'></select><br>
			<label for='grupo'>Grupo: </label>
			<select name='grupo'></select><br>
			<label for='ts'>Ultima Modificacion: </label>
			<input type='text' name='ts' readonly disabled /><br>
			<label for='status'>Estado: </label>
			<input type='text' name='status' readonly disabled /><br>
			<label for='active'>Habilitado: </label>
			<input type='radio' name='active' value='yes'/>Si
			<input type='radio' name='active' value='no'/>No<br>
			<label for='email'>Alertas a: </label>
			<input type='text' name='email' readonly disabled /><br>
			<label for='preview'>Vista Previa: </label>
			<img src='' name='preview' onerror="this.src='img/dianms.png'"/>
		</form>
	</div>
