<?php
$file = $_GET["file"];
$file = "img/diaimg/$file";
//$file_basename = basename($file);
$fileinfo = pathinfo($file);
//$type_file = explode(".",$file_basename);
$type_file = $fileinfo["extension"];
$name_file = $fileinfo["basename"];

header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=$name_file");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($file));

if($type_file == "dia"){
	if (!@readfile($file))
        	echo "Download Error";
}

?>
