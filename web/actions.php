<?php
//session_start();
include("auth.php");
//Functions
function fc_down(){
	$file = $_GET["file"];
	$file_basename = basename($file);
	$type_file = explode(".",$file_basename);

	header("Content-type: application/force-download"); 
	header("Content-Disposition: attachment; filename=$file_basename"); 
	header("Content-Transfer-Encoding: binary"); 
	header("Content-Length: ".filesize($file)); 

	if($type_file[1] == "dia" || $type_file[1] == "tar" ){
		if (!@readfile($file)) 
		echo "Download Error"; 
	}
}

function fc_del(){
	include("inc/config.inc.php");
	$idDiagram = $_GET["id"];
	$sql = "DELETE FROM diagrams WHERE IdDiagram='$idDiagram'";
	mysql_query($sql);
	header("location:".$parametro["path"]["index"]."");
}

function fc_timeline(){
	include("inc/config.inc.php");
	$idDiagram = $_SESSION["idDiagram"];

	$entry = $_GET["entry"];
	$entry = 300 - $entry;
	$sqlDate = "
			SELECT IdDiagramHistory,date 
			FROM DiagramHistory 
			WHERE idDiagram='$idDiagram'
			ORDER BY date DESC
			LIMIT $entry,1
		";
//	echo "[$sqlDate]";
	$resDate = mysql_query($sqlDate);
	$rowDate = mysql_fetch_array($resDate);

//	echo "<p>$entry: ($rowDate[IdDiagramHistory])[$rowDate[date]]</p>";
	if($rowDate["IdDiagramHistory"]){
		echo "<p>Fecha: $rowDate[date]</p>";
	}else{
		echo "No hay registros para esta entrada";
	}
	echo "<img src='actions.php?action=view_image&id=$rowDate[IdDiagramHistory]'>";
}

function fc_view_image(){
	include("inc/config.inc.php");
	$idDiagram = $_GET["id"];
	$sqlImage = "
		    	SELECT image 
			FROM DiagramHistory 
			WHERE IdDiagramHistory='$_GET[id]' 
			";

	$resImage = mysql_query($sqlImage);
	$rowImage = mysql_fetch_array($resImage);
	if($rowImage){
		header("Content-Type: image/png");
		echo $rowImage["image"];
	}else{
		$fd = fopen("".$parametro["path"]["dianms"]."/web/img/dianms.png","rb");
                $image = fread($fd,filesize("".$parametro["path"]["dianms"]."/web/img/dianms.png"));
		header("Content-Type: image/png");
		echo $image;
	}
}

$action = $_GET["action"];

switch($action){
	case "del":	fc_del();
			break;
	case "download": fc_down();
			break;
	case "timeline": fc_timeline();
			break;
	case "view_image": fc_view_image();
			break;
	default: echo "Error";
}
?>
