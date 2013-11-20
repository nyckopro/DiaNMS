<?php
//	error_reporting(E_ALL);
	include("../auth.php");
	include("../inc/config.inc.php");
	include("../lib/fc.php");

/*$_GET["data"]="graph";
$_GET["idDiagram"]="84";
$_GET["idObject"]="O1";
$_GET["f"]=1367377200;
$_GET["t"]=1367463600;
$_GET["hostint"]=2886860104;
$_GET["instance"]=4227841;*/

	function draw_img_graph($idDiagramGraph,$from,$to,$idDiagram,$idObject,$hostint,$instance,$width,$height,$idGraph){
		if($idDiagramGraph!=""){
			$sql = "select label, ObjectType.name, DiagramsGraph.idDiagram, instance, host, DiagramsGraph.idGraph from DiagramsGraph, ObjectType, Graph, GraphAvailable where DiagramsGraph.idGraph=Graph.IdGraph and Graph.IdGraph=GraphAvailable.idGraph and GraphAvailable.idObjectType=ObjectType.IdObjectType and IdDiagramGraph=$idDiagramGraph;";
                	$res = mysql_query($sql);
	                $row = mysql_fetch_array($res);
			$idDiagram = $row["idDiagram"];
			$hostint = $row["host"];
			$instance = $row["instance"];
			$idGraph = $row["idGraph"];
		}else{
			$sql = "select label, ObjectType.name from DiagramsGraph, ObjectType, Graph, GraphAvailable where DiagramsGraph.idGraph=Graph.IdGraph and Graph.IdGraph=GraphAvailable.idGraph and GraphAvailable.idObjectType=ObjectType.IdObjectType and idDiagram='$idDiagram' and instance='$instance' and host='$hostint' and DiagramsGraph.idGraph='$idGraph';";
                	$res = mysql_query($sql);
	                $row = mysql_fetch_array($res);                                                                                                                     
		}
                if($row["label"]) $label = $row["label"]; else $label = "$host-$instance";

		$path = "/usr/local/dianms/scripts/rrd";
		//$file = $idDiagram."_".$idObject."_".$hostint."_".$instance;
		$file = $idDiagram."_".$hostint."_".$instance."_".$idGraph;
		$file = make_graph($idGraph,$from,$to,$path,$file,$label,$width,$height);
//		$file = md5($file);
//		if($ret == 0){
			return "img/".$file.".gif?".rand();
//		}else{
//			return $ret;
//		}
	}

	extract($_GET);
	switch($data){
		case "diagram_group":
			$andWhere = "and idDiagramGroup=$id";
			if($id==0) $andWhere = "";
			if($param!="all") 
				$param = "active='yes'";
			else 
				$param = "1";
			$sql = "select IdDiagram,file_name from Diagrams where $param $andWhere order by file_name;";
			break;
		case "diagram":
			$sql = "select IdDiagram,file_name,Diagrams.active, Users.name as modifico, Users.IdUser, ts,DiagramsStatus.description as status, Users.email, idDiagramGroup as grupo from Diagrams,Users,DiagramsStatus where Diagrams.idDiagramStatus=DiagramsStatus.IdDiagramStatus and Diagrams.idUser=Users.IdUser and IdDiagram=$id";
//			$sql = "select Diagrams.IdDiagram,file_name,Users.name as modifico,INET_NTOA(Alerts.host) as ip, Alerts.ts, Alerts.status, Alerts.object from Diagrams,Users,Alerts where Diagrams.IdDiagram=Alerts.IdDiagram and Diagrams.idUser=Users.IdUser and Alerts.count=3 and Diagrams.IdDiagram=$id order by Alerts.ts desc limit 1;";
			break;
		case "alerts":
			$sql = "select UNIX_TIMESTAMP(ts) as ts,INET_NTOA(host) as ip,object,status,count from Alerts where MONTH(ts)=MONTH(NOW()) and YEAR(ts)=YEAR(NOW()) and IdDiagram='$idDiagram' order by ts desc;";
			break;
		case "users":
			if($id) $andWhere = "and IdUser=$id";
			$sql = "select IdUser, username, name, email, idGroup from Users where 1 $andWhere;";
			break;
		case "user_group":
			$sql = "select * from Groups";
			break;
		case "diagram_group_list":
			$sql = "select IdDiagramGroup,name from DiagramsGroups where idDiagramGroup<>4;"; //idGroup == 4 pruebas
			break;
		case "graph":	
			if($h=="" || $w==""){
				$w = "500";
				$h = "150";
			}
			$imgurl = draw_img_graph($idDiagramGraph,$f,$t,$idDiagram,$idObject,$hostint,$instance,$w,$h,$idGraph);
			echo json_encode(array("status"=>"ok","imgurl"=>$imgurl));
			exit;
			break;
		
	}

	$res = mysql_query($sql);                                                          
        $data = array();
        while($row = mysql_fetch_array($res,MYSQL_ASSOC)){                                 
//                $data[] = array_map('utf8_encode', $row);                                  
                $data[] =  $row;                                  
        }
        echo json_encode($data);
?>
