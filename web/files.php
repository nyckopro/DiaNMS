<?php
//include("auth.php");
     function Ultima_Modificacion( $pathDir , $archivos, $pos){

	if (empty($archivos)) return false;
	
	foreach ($archivos as $archivo){
		$ts = filemtime($pathDir."/".$archivo);
//		$timestamps["ts"][] = $ts;
		$timestamps[$ts]["file"] = $archivo;
	}
	krsort($timestamps);
//	$pos = 5;
	$count = 0;

	foreach($timestamps as $key => $val){
		if($pos == $count){
			return "$val[file]\n";
		}
		$count++;
	}
//	$timestamp = max($timestamps["ts"]);
//	$file = $timestamps[$timestamp]["file"];
	return false;
	}
	$pos = $_GET["entry"];
	$pathDir = "img/diaimg";
	$dir = opendir($pathDir); 
	$diagram = str_replace(".dia","\.dia",$_GET["diagram"]);
//	$diagram = $_GET["diagram"];
	$pattern = "/^$diagram\_.*png$/";
//	echo $pattern;
//	$pattern = "/^Anillos\.dia*png$/";
	
	while ($archivo = readdir($dir)){
		if($archivo!="." && $archivo!=".." && preg_match($pattern,$archivo)){
			$archivos[]=$archivo; 
		}
	}
	closedir($dir);
	echo Ultima_Modificacion($pathDir,$archivos,$pos)."\n";

?>
