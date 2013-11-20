<?php
	include("../auth.php");
	include("../inc/permissions.php");
	include("../inc/config.inc.php");
	extract($_REQUEST);
	switch($form){
		case "diagrams":
			$file_ext = pathinfo($file_name);
			if($file_ext["extension"]!="dia"){
				$data = array("status"=>"fail", "msg" => "La extension en el nombre del Diagrama no puede ser distinto de .dia");
				echo json_encode($data);
				exit;
			}
			$sql = sprintf("update Diagrams set file_name='%s', idUser=%d, active='%s', idDiagramGroup=%d, ts=ts where IdDiagram=%d",mysql_real_escape_string($file_name),mysql_real_escape_string($modifico),mysql_real_escape_string($active),mysql_real_escape_string($grupo),mysql_real_escape_string($id));
			$sql = htmlentities($sql);
			$msg = "Datos Actualizados correctamente";
			break;
		case "users":
			$sql = sprintf("update Users set name='%s', email='%s', idGroup=%d where IdUser=%d",mysql_real_escape_string($name),mysql_real_escape_string($email),mysql_real_escape_string($idgroup),mysql_real_escape_string($id));
			$sql = htmlentities($sql);
			$msg = "Datos Actualizados correctamente";
			break;
		case "graph_add":
			$sql = sprintf("insert into DiagramsGraph (idDiagram, idObject, host, instance, label, community, idGraph) values ('%d','%s',INET_ATON('%s'),'%d','%s','%s','%d')",mysql_real_escape_string($idDiagram),mysql_real_escape_string($idObject),mysql_real_escape_string($host),mysql_real_escape_string($instance),mysql_real_escape_string($label),mysql_real_escape_string($community),mysql_real_escape_string($idGraph));
			$sql = htmlentities($sql);
			$msg = "Datos Actualizados correctamente";
			break;
		case "graph_rem":
			$sql = sprintf("delete from DiagramsGraph where idDiagram='%d' and idObject='%s'",mysql_real_escape_string($idDiagram),mysql_real_escape_string($idObject));
			$sql = htmlentities($sql);
			$msg = "Datos Actualizados correctamente";
			break;
		case "settings":
			$sql = sprintf("update Settings set `value`='%s' where `key`='emailAlertas'",mysql_real_escape_string($email));
			mysql_query($sql);
			$sql = sprintf("update Settings set `value`='%d' where `key`='idGroupAdmin'",mysql_real_escape_string($idGroupAdmin));
			mysql_query($sql);
			$sql = sprintf("update Settings set `value`='%s' where `key`='community'",mysql_real_escape_string($community));
			$sql = htmlentities($sql);
			$msg = "Datos Actualizados correctamente";
			break;
		case "graph_opts":
			$sql = sprintf("update DiagramsGraph set label='%s' where IdDiagramGraph='%d'", mysql_real_escape_string($label), mysql_real_escape_string($idDiagramGraph));
			$msg = "Actualizado";
			break;
	}

	$res = mysql_query($sql);                                                          
	if(!$res){
		$data = array("status"=>"fail", "msg"=>"Error en DB: ".mysql_error());
	}else{
		$data = array("status"=>"ok","msg"=>$msg);
	}
	echo json_encode($data);
?>
