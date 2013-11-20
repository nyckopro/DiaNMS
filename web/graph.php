<?php
//	error_reporting(E_ALL);
	session_start();
	include("inc/config.inc.php");
	include("lib/fc.php");

	function show_list($idDiagram,$idObject,$host,$instance,$community){
		//echo $idDiagram."|".$idObject."|".$host."|".$GLOBALS["type"]."<br>";
		$sql = "select * from Graph order by IdGraph";
		$res = mysql_query($sql);
		$graphs = array();
		while($row = mysql_fetch_array($res,MYSQL_ASSOC)){
			$graphs[] = $row;
		}

		if($GLOBALS["flag"]==0){
			echo "<thead>";
			echo " <th>Objeto</th>";
			echo " <th>Host</th>";
			echo " <th>Community</th>";
			echo " <th>Instance</th>";
	//		echo " <th>Label</th>";
			foreach($graphs as $graph){
				echo " <th>".$graph["name"]."</th>";
			}
			echo "</thead>";
			$GLOBALS["flag"]++;
		}

		
		echo "  <input class='diagram_".$idObject."' type=hidden name='idDiagram' value='".$idDiagram."' />";
		echo "  <input class='host_".$idObject."' type=hidden name='host' value='".$host."' />";
		echo "  <input class='instance_".$idObject."' type=hidden name='instance' value='".$instance."' />";
		echo "  <input class='community_".$idObject."' type=hidden name='community' value='".$community."'/></td>";
		echo "<tr>";
		echo "  <td><input type=text readonly value='".$GLOBALS["type"]."'/></td>";
		echo "  <td><input type=text readonly value='".$host."'/></td>";
		echo "  <td><input type=text readonly value='".$community."'/></td>";
		echo "  <td><input type=text value='".$instance."' readonly/></td>";
	///	echo "  <td><input class='label_".$idObject."' type=text name='label' placeholder='".$ifDescr."' value='".$rowChk["label"]."' /></td>";
		foreach($graphs as $graph){

			$sql = "select Graph.name, Graph.IdGraph from GraphAvailable, ObjectType, Graph where GraphAvailable.idObjectType=ObjectType.IdObjectType and GraphAvailable.idGraph=Graph.idGraph and ObjectType.name='".$GLOBALS["type"]."' and Graph.name='".$graph["name"]."';";
//			echo $sql."<br>";
			$res = mysql_query($sql);
			$c = mysql_num_rows($res);
			if($c==0) $disabled="disabled"; else $disabled="";

			
			$sqlChk = "select idObject,label from DiagramsGraph, Graph where DiagramsGraph.idGraph = Graph.IdGraph and idDiagram='$idDiagram' and idObject = '$idObject' and Graph.name='".$graph["name"]."'; ";
			$resChk = mysql_query($sqlChk);
			$rowChk = mysql_fetch_array($resChk);
			if($rowChk["idObject"] == $idObject) $checked = "checked"; else $checked = "";


			echo "  <td><input $checked $disabled class='ckbx' value='".$graph["IdGraph"]."' type=checkbox name='".$idObject."' /></td>";
		}
		echo "</tr>";
	}

	function show_graphs($idDiagramGraph,$idDiagram,$idObject,$host,$instance,$community,$label,$width,$height){
		$hostint = ip2long($host);
		$hostint = sprintf("%u",$hostint);
		$path = "/usr/local/dianms/scripts/rrd";
		$idGraph = $GLOBALS["idGraph"];
		$file = $idDiagram."_".$hostint."_".$instance."_".$idGraph;
//		echo $file."<br>";
	//	echo $idGraph."|".$GLOBALS["type"]."<br>";
		$file = make_graph($idGraph,$GLOBALS["from"],$GLOBALS["to"],$path,$file,$label,$width,$height);
//		$file = md5($file);
//		echo "make_graph(".$GLOBALS["from"].",".$GLOBALS["to"].",$path,$file,$label,$width,$height,".$GLOBALS["type"].");<br>";
//		echo "[$ret]";
		$rand = rand();

		if($GLOBALS["flag"]==0 && $GLOBALS["all"] == TRUE ){
                        echo " <tr class='trHead'>";  
                        echo "  <td>";
			echo "	 Todos los Graficos: ";
                        echo "   <input type=text name='OO' class='date date_from_OO' placeholder='Desde' />";
                        echo "   <input type=text name='OO' class='date date_to_OO' placeholder='Hasta' />";
                        echo "  </td>";
                        echo " </tr>";
                        $GLOBALS["flag"]++;
                }
	
		echo "<tr>";
		echo " <td>";	
		echo "  <a class='l' href='graph.php?show=graph&idDiagram=$idDiagram&idDiagramGraph=$idDiagramGraph'>";
		echo "   <img class='image_".$idDiagramGraph."' src=img/$file.gif?$rand />";
		echo "  </a>";
		echo "  <div>";
		echo "	 <input type='hidden' class='width_$idDiagramGraph' value='$width' />";
		echo "	 <input type='hidden' class='height_$idDiagramGraph' value='$height' />";
		echo "	 <input type='hidden' class='hostint_$idDiagramGraph' value='$hostint' />";
		echo "	 <input type='hidden' class='instance_$idDiagramGraph' value='$instance' />";
		echo "	 <input type='hidden' class='graph_$idDiagramGraph' value='$idGraph' />";
		echo "	 <input type='text' class='date date_from_".$idDiagramGraph."' name='".$idDiagramGraph."' placeholder='Desde' />";
		echo "	 <input type='text' class='date date_to_".$idDiagramGraph."' name='".$idDiagramGraph."' placeholder='Hasta' />";
		echo "	 <a class='graph_opts' name='".$idDiagramGraph."'>+opciones</a>";
		echo "	 <div id='graph_opts_".$idDiagramGraph."'>";
		echo "	  <form class='form' action='lib/set.php'>";
		echo "	   <input type='hidden' name='idDiagramGraph' value='$idDiagramGraph' />";
		echo "	   <input type='hidden' name='form' value='graph_opts' />";
		echo "	   <input type='hidden' name='idObject' value='$idObject' />";
		echo "	   <label for='label'>Label: </label>";
		echo "	   <input type='text' name='label' value='$label' placeholder='".$label."'/><br>";
		echo "	   <input type='submit' value='Aplicar' />";
		echo "	   <p class='cl'></p>";
		echo "	  </form>";
		echo "	 </div>";
		echo "  </div>";
		echo " </td>";
		echo "</tr>";
	
	}
/*	$_GET["show"]="graph";
	$_GET["idDiagram"] = 84;
	$_GET["idDiagramGraph"]=32;*/
	$idDiagram = $_GET["idDiagram"];
	$sql = "select file_name from Diagrams where IdDiagram=$idDiagram";
	$res = mysql_query($sql);
	$row = mysql_fetch_array($res);
	$filename = $row["file_name"];

	$file = "img/diaimg/$filename";
	$xmlfile = simplexml_load_file($file);
	$xml = $xmlfile->xpath("/dia:diagram/dia:layer/dia:object[@type[starts-with(.,'DIANMS')]]");
//	echo "<ul>";
//	echo " <li>";
	echo "<input type='hidden' id='idDiagram' value='".$idDiagram."' />";
	echo "<table class='tbl'>";	
	echo " <caption>";
	if($_SESSION["gid"]==$_SESSION["idGroupAdmin"]){
		echo "  <a class='l' href='graph.php?show=configure&idDiagram=$idDiagram'> Configurar</a> |";
	}
	echo "  <a class='l' href='graph.php?show=graph&idDiagram=$idDiagram' >Ver Graficos</a></li>";
	echo " </caption>";
	

	$flag=0;
	foreach($xml as $item){
		$type = $item["type"];
		//echo $type.":".$item["id"]."<br>";
		if($_GET["idObject"]!="" && $_GET["idObject"]!=$item["id"]) continue;
		if($_GET["idDiagramGraph"]!=""){
			$width = "900";
			$height = "350";
			$all = FALSE;
		}else{
			$width = "500";
			$height = "150";
			$all = TRUE;
		}
		$from = $_GET["f"];
		$to = $_GET["t"];

		if($_GET["w"]!="") $width = $_GET["w"];
		if($_GET["h"]!="") $height = $_GET["w"];

		$idObject = $item["id"];
		$host = $xmlfile->xpath("//dia:object[@id=\"$idObject\"]/dia:attribute[@name='custom:Host']/dia:string");
		$host = explode("#",$host[0]);
		$host = $host[1];

		if(!$host){
			//Modo Compatibilidad con shapes viejos.
			$host = $xmlfile->xpath("//dia:object[@id=\"$idObject\"]/dia:attribute[@name='custom:SNMP.Host']/dia:string");
			$host = explode("#",$host[0]);
			$host = $host[1];

			if(!$host) continue;
		}	

		$instance = $xmlfile->xpath("//dia:object[@id=\"$idObject\"]/dia:attribute[@name='custom:SNMP.Instance']/dia:int");
		$instance = $instance[0]["val"];
		if($instance=="") $instance=0;

		$community = $xmlfile->xpath("//dia:object[@id=\"$idObject\"]/dia:attribute[@name='custom:SNMP.Community']/dia:string");
		$community = explode("#",$community[0]);
		$community = $community[1];
		if(!$community){
			$community = $xmlfile->xpath("//dia:object[@id=\"$idObject\"]/dia:attribute[@name='custom:Community']/dia:string");
			$community = explode("#",$community[0]);
			$community = $community[1];
		}
		
		if($_GET["show"] == "configure"){
			if($_SESSION["gid"]==$_SESSION["idGroupAdmin"]){
				show_list($idDiagram,$idObject,$host,$instance,$community);
			}else{
				echo "no tienes permiso para ver este modulo";
			}
		
		}else{
			if($_GET["idDiagramGraph"]==""){
				$sqlChk = "select IdDiagramGraph,idObject,label, DiagramsGraph.idGraph, Graph.name as graph from DiagramsGraph, Graph where DiagramsGraph.idGraph=Graph.IdGraph and idDiagram='$idDiagram' and host = INET_ATON('$host') and instance = '$instance' and idObject='$idObject';";	
			}else{
				$sqlChk = "select IdDiagramGraph,idObject,label,DiagramsGraph.idGraph, Graph.name as graph from DiagramsGraph, Graph where DiagramsGraph.idGraph=Graph.IdGraph and IdDiagramGraph='".$_GET["idDiagramGraph"]."'";
			}
			$resChk = mysql_query($sqlChk);
			while($rowChk = mysql_fetch_array($resChk)){
				if($rowChk["label"]) $label = $rowChk["graph"]." - ".$rowChk["label"]; else $label = $rowChk["graph"]." - ".$host."-".$instance;
				$idGraph = $rowChk["idGraph"];
				$idDiagramGraph = $rowChk["IdDiagramGraph"];
				if($rowChk["idObject"] == $idObject){
					show_graphs($idDiagramGraph,$idDiagram,$idObject,$host,$instance,$community,$label,$width,$height);
				}
			}
		}
	}	
	echo "</table>";	
//	echo "</ul>";
?>
