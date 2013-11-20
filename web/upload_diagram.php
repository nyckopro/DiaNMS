<?php 
include ("inc/config.inc.php");
//var_dump($_POST);

function filesize_format($bytes, $format = '', $force = ''){
	$bytes=(float)$bytes;
	if ($bytes< 1024){
		$numero=number_format($bytes, 0, '.', ',');
		return array($numero,"B");
	}
	if ($bytes< 1048576){
		$numero=number_format($bytes/1024, 2, '.', ',');
		return array($numero,"KBs");
	}
	if ($bytes>= 1048576){
		$numero=number_format($bytes/1048576, 2, '.', ',');
		return array($numero,"MB");
	}
}

if(sizeof($_FILES)==0){
	echo json_encode(array("type"=>"error", "msg"=>"ERROR al subir el archivo, aparentemente esta vacio. Intente nuevamente por favor."));
	exit();
}
$archivo = $_FILES["archivo"]["tmp_name"];
$type = $_FILES["archivo"]["type"];
$tamanio=array();
$tamanio = $_FILES["archivo"]["size"];
$nombre_archivo = str_replace(" ","_",$_FILES["archivo"]["name"]);

$s = file_get_contents($archivo); 
if ( bin2hex(substr($s,0,2)) == '1f8b' ){ 
	echo json_encode(array("type"=>"error", "msg"=>"ERROR: Archivo gzipeado, por favor, destilde la opcion 'comprimir' en el momento de guardar el diagrama"));
	exit;
}

if($type == "application/x-dia-diagram" || $type == "application/dia"){

extract($_REQUEST);
if ( $archivo != "" ){
	$fp = fopen($archivo, "rb");
	$contenido = @fread($fp, $tamanio);
	$contenido = addslashes($contenido);
	$checksum = md5_file($archivo);
	fclose($fp);
	if ($tamanio < 1048576){
		$tamanio=filesize_format($tamanio);
	}

	mysql_select_db($parametro["db"]["dbnamemon"]);
	$sqlCheck = "SELECT IdDiagram FROM Diagrams WHERE file_name='$nombre_archivo'";
	$resCheck = mysql_query($sqlCheck);
	$rowCheck = mysql_fetch_array($resCheck);
	
	if($dgroup==0){
		$sqldgroupchk = "select * from DiagramsGroups where name='$newCategory';";		
		$resdgroupchk = mysql_query($sqldgroupchk);
		$rowdgroupchk = mysql_fetch_array($resdgroupchk);
//		echo "[".$rowdgroupchk["IdDiagramGroup"]."]";
		if($rowdgroupchk["IdDiagramGroup"]==""){
			$sqlins = "insert into DiagramsGroups (name) values ('$newCategory');";
//			echo $sqlins;
			$resins = mysql_query($sqlins);
			$dgroup = mysql_insert_id();
		}else{
			$dgroup = $rowdgroupchk["IdDiagramGroup"];
		}
	}

	if($rowCheck["IdDiagram"]){
		$qry = "
			UPDATE Diagrams 
			SET content='$contenido',idUser='$uid',idDiagramGroup='$dgroup'
			WHERE IdDiagram='$rowCheck[IdDiagram]'
			";
		$msg = "Diagrama $nombre_archivo($rowCheck[IdDiagram]) actualizado";
	}else{
		$qry = "
			INSERT INTO Diagrams (file_name,content,active,idUser,idDiagramGroup) 
			VALUES ('$nombre_archivo','$contenido','yes','$uid', '$dgroup')
			";
		$msg = "Diagrama $nombre_archivo agregado";
	}
	mysql_query($qry) or die("Query: $qry <br />Error: ".mysql_error());
	mysql_close();
	echo json_encode(array("type"=>"info", "msg"=>"Cargado Correctamente"));
}else{
	echo json_encode(array("type"=>"error", "msg"=>"ERROR: No fue posible subir el archivo"));
}


}else {
	echo json_encode(array("type"=>"error", "msg"=>"ERROR: $type: Extension no permitida"));
}

?>
