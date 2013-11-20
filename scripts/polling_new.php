#!/usr/bin/php
<?php
include("config.inc.php");

//error_reporting();

function check_db($idXml,$host,$idObject,$object,$status){
	$fecha = date("d-m-y H:i");
	$sqlCount = "select IdAlert,status,count from Alerts where IdObject='$idObject' and IdDiagram='$idXml' order by ts desc limit 1;";
	$resCount = mysql_query($sqlCount);
//echo $sqlCount;	
	if(!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		echo "\n$fecha Error: Host [$host] no es una ip valida.";
		$sql = "update Diagrams set idDiagramStatus=2 where IdDiagram=$idXml";
		if(!mysql_query($sql)){
			echo "Error DB: ".mysql_error()." [FAIL]";
		}
		mail("nicolase@itcsa.net.ar, ".$GLOBALS["email"]."","DiaNMS Host Error","Host: [$host] no es una ipv4 valida");
		return 0;
//		continue;
	}

	if (!$resCount) {
		echo "\nSQL: $sqlCount";
		die(' Invalid query: ' . mysql_error());
	}
	
	$rowCount = mysql_fetch_array($resCount);

	if($rowCount == NULL){
		$sqlAlert = "INSERT INTO Alerts (IdDiagram,host,IdObject,object,status,count) VALUES ('$idXml',INET_ATON('$host'),'$idObject','$object','$status','0')";
		$result = mysql_query($sqlAlert);
		if (!$result) {
			echo "\nSQL: $sqlAlert";
			die(' Invalid query: ' . mysql_error());
			mail("nicolase@itcsa.net.ar, ".$GLOBALS["email"]."","Dianms Broken","Invalid query: ".mysql_error());
			echo "I: Borrando ".$GLOBALS["file"];
			@unlink($GLOBALS["file"]);	
			return 0;
		}
	}	
	if($status == $rowCount["status"]){
		if($rowCount["count"] < 2){
			$c = $rowCount["count"] + 1;
			$sqlUpd = "UPDATE Alerts SET count='$c' WHERE IdAlert='$rowCount[IdAlert]';";
			mysql_query($sqlUpd);
			echo "[count++ = $c ($rowCount[count])]";
			$GLOBALS["body"] .= $host." ".$id." ".$object." ".$status."\n";
		}else{
			if($rowCount["count"]>=2){
		//		echo "\$status($status) is equal \$rowCount[status]($rowCount[status]) y es = 3\n";
				$c = $rowCount["count"] + 1;
				$sqlUpd = "UPDATE Alerts SET count='$c' WHERE IdAlert='$rowCount[IdAlert]';";
				mysql_query($sqlUpd);
				$GLOBALS["flag"] = $GLOBALS["flag"] + 1;
				//echo "2- $flag";
				$GLOBALS["body"] .= $host." ".$id." ".$object." ".$status."\n";
			}else{
		//		echo "\$status($status) is equal \$rowCount[status]($rowCount[status]) y no es < 3\n";
				$GLOBALS["flag"] = 0;
				echo "CHAN! verificar: \$rowCount[count] = $rowCount[count] | \$status = $status | \$rowCount[status] = $rowCount[status]";
		//		echo "\t$rowCount[count] >= 3: ya notificado\n";
			}
		}
	}else{
//		echo "[$status] != [$rowCount[status]]\n";	
		if($rowCount["count"] < 2){
			$c = $rowCount["count"] + 1;
			$sqlUpd = "UPDATE Alerts SET count='$c' WHERE IdAlert='$rowCount[IdAlert]';";
			mysql_query($sqlUpd);
			echo "\tcount++ = $c ($rowCount[count])\n";
		}else{
			$sqlAlert = "INSERT INTO Alerts (IdDiagram,host,IdObject,object,status,count) VALUES ('$idXml',INET_ATON('$host'),'$idObject','$object','$status','0')";
			$result = mysql_query($sqlAlert);
			if (!$result) {
				echo "\nSQL: $sqlAlert";
				die('-> Invalid query: ' . mysql_error());
			}
			$GLOBALS["body"] .= $host." ".$id." ".$object." ".$status."\n";
		}
	}
}

function snmpAction($idXml,$ID,$xmlfile,$salida){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Host']/dia:string");
	$host = explode("#",$host[0]);
	$host = $host[1];

	$community = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP_Community']/dia:string");
	$community = explode("#",$community[0]);
	$community = $community[1];

	$version = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP_Version']/dia:string");
	$version = explode("#",$version[0]);
	$version = $version[1];

	$oid = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP_Oid']/dia:string");
	$oid = explode("#",$oid[0]);
	$oid = $oid[1];

	$instance = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP_Instance']/dia:string");
	$instance = explode("#",$instance[0]);
	$instance = $instance[1];

	if(!$host || !$community || !$version || !$oid || !$instance){
//	 	echo "\n\tI: Verificar, Faltan Parametros en $ID";
		return 0;
	}
	
	$object="$oid.$instance";

	$res = @snmpget($host,$community,$object);
	$res = explode(": ",$res);
	
	$oidMin = strtolower($oid);

	
	switch($oidMin){
		//Link Status
		case "1.3.6.1.2.1.2.2.1.8":
		case "ifoperstatus":
			switch($res[1]){
				case "up(1)":	$newcolor="#32cd32";
					break;
				case "down(2)": $newcolor="#FF0000";
					break;
				default :	//echo "ERROR [$res[1]]";
						$newcolor="#000000";
			}	
			break;
		//Admin status
		case "ifadminstatus":
			switch($res[1]){
				case "up(1)":	$newcolor="#32cd32";
					break;
				case "down(2)": $newcolor="#FF0000";
					break;
				default :	//echo "ERROR [$res[1]]";
						$newcolor="#000000";
			}	
			break;
		//STP Port State
		case "1.3.6.1.2.1.17.2.15.1.3":
			switch($res[1]){
				case "1": $STATUS="DISABLED";
					$newcolor="#000000";
			                break;
                		case "2": $STATUS="BLOCKED";
					$newcolor="#0000FF";
					break;
		                case "3": $STATUS="LISTENING";
					$newcolor="#000000";
					break;
				case "4": $STATUS="LEARNING";
					$newcolor="#000000";
        				break;
				case "5": $STATUS="FORWARDING";
					$newcolor="#32cd32";
                        		break;
				case "6": $STATUS="BROKEN";
					$newcolor="#FF0000";
                       			break;
				default : $STATUS="UNKNOW";
					$newcolor="#000000";
			}
			break;
		default : 
			switch($res[1]){
				case "0":	$newcolor="#32cd32";
					break;
				case "1": $newcolor="#FF0000";
					break;
				default :	//echo "ERROR [$res[1]]";
						$newcolor="#000000";
			}	
	}
	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
	if($originalColor != $newcolor){
//		echo "($ID) ifStatus: ($originalColor) ($newcolor)\n";
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]");
		$color[0]->color["val"]="$newcolor";
		$xmlfile->asXML($salida);
//		$GLOBALS["changes"][] = $host." ".$oid.": ".$res[1];
//		$GLOBALS["changes"][] = $host." ".$ID." ".$oid." ".$res[1];
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I:($ID) $host $oid.$instance Status: $res[1] ($newcolor)";
		check_db($idXml,$host,$ID,$object,$res[1]);
//		var_dump($changes);
	}

}

function pingAction($idXml,$ID,$xmlfile,$salida){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Host']/dia:string");
	$host = explode("#",$host[0]);
	$host = $host[1];
	
	if(!$host){
//		echo "\n\tI: Verificar, Falta el Parametro \"HOST\" en $ID";
		return 0;
	}
	exec("fping $host 2>/dev/null | awk '{print $3}'",$res);
	switch($res[0]){
		case "alive":	$newcolor="#000000";
				break;
		case "unreachable": $newcolor="#FF0000";
				break;
		default :	$newcolor="#0000FF";
	}	

	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"line_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
//	echo $host.": ".$originalColor."|".$newcolor;
	if($originalColor != $newcolor){
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"line_colour\"]");
		$color[0]->color["val"]="$newcolor";
		$xmlfile->asXML($salida);
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I: ($ID) $host Status: $res[0] ($newcolor)";
		$ret = check_db($idXml,$host,$ID,"icmp",$res[0]);
		echo "[$ret]";
	}
}

function stpAction($idXml,$ID,$xmlfile,$salida){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:STP.Host']/dia:string");
	$host = explode("#",$host[0]);
	$host = $host[1];

	$community = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:STP.Community']/dia:string");
	$community = explode("#",$community[0]);
	$community = $community[1];

	/*$version = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP_Version']/dia:string");
	$version = explode("#",$version[0]);
	$version = $version[1];*/
	
	$instance = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:STP.Instance']/dia:int");
	$instance = $instance[0]["val"];
	
	if(!$host || !$community || !$instance){
//		echo "\n\tI: Verificar, Faltan Parametros en $ID | Host: $host | Community: $community | Instance: $instance";
		return 0;
	}
	$object="1.3.6.1.2.1.17.2.15.1.3.$instance";

	$res = @snmpget($host,$community,$object);
	$res = explode("INTEGER: ",$res);
	
	switch($res[1]){
		case "1": $STATUS="D"; //DISABLED
			$newcolor="#000000";
		          break;
                case "2": $STATUS="B"; //BLOQUED
			$newcolor="#0000FF";
			$tcolor="#FFFFFF";
			break;
		case "3": $STATUS="LI"; //LISTENING
			$newcolor="#000000";
			break;
		case "4": $STATUS="LE"; //LEARNING
			$newcolor="#000000";
        		break;
		case "5": $STATUS="F"; //FORWARDING
			$newcolor="#32cd32";
			$tcolor="#000000";
                 	break;
		case "6": $STATUS="BR"; //BROKEN
			$newcolor="#FF0000";
                    	break;	
		default: $STATUS="U"; //UNKNOW
			$newcolor="#000000";
                    	break;	
	}
	
	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
	if($originalColor != $newcolor){
		//echo "stpAction: $originalColor ($newcolor)\n";
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]");
		$color[0]->color["val"]="$newcolor";
	
		$stpstatus=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"text\"]/dia:composite/dia:attribute[@name=\"string\"]");
		$stpstatus[0]->string="#$STATUS#";
	
		$textcolor=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"text\"]/dia:composite/dia:attribute[@name=\"color\"]");
		$textcolor[0]->color["val"]="$tcolor";

		$xmlfile->asXML($salida);
		//$GLOBALS["changes"][] = $host." ".$ID." STP ".$STATUS;
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I:($ID) $host 1.3.6.1.2.1.17.2.15.1.3.$instance Status: $res[1] ($newcolor) $STATUS";
		check_db($idXml,$host,$ID,"stp: $instance",$STATUS);
		//var_dump($changes);
	}
}


function ifStatus($idXml,$ID,$xmlfile,$salida){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP.Host']/dia:string");
	$host = explode("#",$host[0]);
	$host = $host[1];

	$community = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP.Community']/dia:string");
	$community = explode("#",$community[0]);
	$community = $community[1];
	
	$instance = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP.Instance']/dia:int");
	$instance = $instance[0]["val"];
	   
	if(!$host || !$community || !$instance){
//		echo "\n\tI: Verificar, Faltan Parametros en $ID: Host: $host | Community: $community | Instance: $instance";
		return 0;
	}
	$object="ifOperStatus.$instance";
	$res = @snmpget($host,$community,$object);
//	echo "\$res = snmpget($host,$community,$object);\n";
	$res = explode("INTEGER: ",$res);
	
	switch($res[1]){
		case "up(1)":	$newcolor="#32cd32";
			break;
		case "down(2)": $newcolor="#FF0000";
			break;
		default :	//echo "snmp response: [$res[1]]";
				$newcolor="#000000";
	}	
	
	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
	if($originalColor != $newcolor){
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]");
		$color[0]->color["val"]="$newcolor";
		$xmlfile->asXML($salida);
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I: ($ID) $host $object Status: $res[1] ($newcolor)";
		check_db($idXml,$host,$ID,$object,$res[1]);
	}

}

function extOutput($idXml,$ID,$xmlfile,$salida){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP.Host']/dia:string");
	$host = explode("#",$host[0]);
	$host = $host[1];

	$community = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP.Community']/dia:string");
	$community = explode("#",$community[0]);
	$community = $community[1];
	
	$instance = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:SNMP.Instance']/dia:int");
	$instance = $instance[0]["val"];
	   
	if(!$host || !$community || !$instance){
//		echo "\n\tI: Verificar, Faltan Parametros en $ID: Host: $host | Community: $community | Instance: $instance";
		return 0;
	}
	$object="extOutput.$instance";

	$res = @snmpget($host,$community,$object);
	$res = explode("STRING: ",$res);
	
	switch($res[1]){
		case "0" : $newcolor="#000000";
				break;
		case "1": $newcolor="#D8E5E5";
				break;
		case "2": $newcolor="#FFC255";
				break;
		case "3": $newcolor="#FF0000";
				break;
		default : $newcolor="#0000FF";
				break;
	}
	
	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"line_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
	if($originalColor != $newcolor){
	//	echo "extOutput: $originalColor ($newcolor)\n";
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"line_colour\"]");
		$color[0]->color["val"]="$newcolor";
		$xmlfile->asXML($salida);
		//$GLOBALS["changes"][] = $host."->".$object."(".$res[1].")";
		//$GLOBALS["changes"][] = $host." ".$ID." ".$object." ".$res[1];
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I: ($ID) $host $object Status: $res[1] ($newcolor)";
		check_db($idXml,$host,$ID,$object,$res[1]);
	//	var_dump($changes);
	}
}

function checkService($idXml,$ID,$xmlfile,$salida,$service){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Host']/dia:string");
	$host = explode("#",$host[0]);
	$host= $host[1];
	
	if(!$host){
//		echo "\n\tI: Verificar, Faltan Parametros en $ID: Host: $host";
		return 0;
	}
	
	switch($service){	
		case "http":	$port = 80;
				break;
		case "mysql":	$port = 3306;
				break;
		case "dns":	$port = 53;
				break;
		case "imap":	$port = 143;
				break;
		case "pop":	$port = 110;
				break;
		case "smtp":	$port = 25;
				break;
		case "generic":	
				$port = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Port']/dia:int");
				$port = (int) $port[0]["val"];
				break;
		
	}

	error_reporting(0);
	$fp = fsockopen($host,$port,$errno,$errstr,11);
	if($fp){
		$newcolor="#32cd32";
		$state = "up";
		fclose($fp);
	}else{
		$state = "down";
		$newcolor="#FF0000";
	}
	//flush();

	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
	if($originalColor != $newcolor){
	//	echo "($ID) checkService: $originalColor ($newcolor)\n";
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]");
		$color[0]->color["val"]="$newcolor";
		$xmlfile->asXML($salida);
//		$GLOBALS["changes"][] = $host." ".$service.": ".$state;
//		$GLOBALS["changes"][] = $host." ".$ID." ".$service." ".$state;
		//var_dump($changes);
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I: ($ID) $host Status: ($newcolor)";
		check_db($idXml,$host,$ID,$service,$state);
	}
	
}

function checkDisk($idXml,$ID,$xmlfile,$salida){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Host']/dia:string");
	$host = explode("#",$host[0]);
	$host = $host[1];

	$community = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Community']/dia:string");
	$community = explode("#",$community[0]);
	$community = $community[1];
	
	$partition = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Partition']/dia:string");
	$partition = explode("#",$partition[0]);
	$partition = $partition[1];
	   
	if(!$host || !$community || !$partition){
//		echo "\n\tI: Verificar, Faltan Parametros en $ID: Host: $host | Community: $community | tInstance: $instance";
		return 0;
	}
	
	//echo $host."\n".$community."\n".$partition;

	//busco la particion a monitorear
	$hrStorageDescr = "hrStorageDescr";
	$res = @snmprealwalk($host,$community,$hrStorageDescr);
	$res = array_search("STRING: ".$partition,$res);
	$id = explode(".",$res);
	$id = $id[1];

	//busco en que unidad se encuentra
	$hrStorageAllocationUnits = "hrStorageAllocationUnits";
	$res = @snmpget($host,$community,$hrStorageAllocationUnits.".".$id);
	$units = explode(" ",$res);
	$unit = $units[2];
	$factor = $units[1];

	//busco el tama~o de la particion
	$hrStorageSize = "hrStorageSize";
	$res = @snmpget($host,$community,$hrStorageSize.".".$id);
	$size = explode(": ",$res);
	$size = $size[1] * $factor;
	//echo "Size: $size\n";

	//busco el espacio ocupado
	$hrStorageUsed = "hrStorageUsed";
	$res = @snmpget($host,$community,$hrStorageUsed.".".$id);
	$used = explode(": ",$res);
	$used = $used[1] * $factor;	
	//echo "Used: $used\n";
	
	$freespace = $size - $used;
	//echo "Free: $freespace\n";
	
	$percent = round(($used * 100) / $size);

	if($percent > 0 && $percent <= 75){ $level = "normal"; $newcolor="#32cd32"; $tcolor="#000000";}
	if($percent > 75 && $percent <= 95){ $level = "warning"; $newcolor="#FFFF00"; $tcolor="#000000";}
	if($percent > 95 && $percent <= 100){ $level = "critical"; $newcolor="#FF0000"; $tcolor="#FFFFFF";}
	
	$originalColor = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]/dia:color");
	$originalColor = $originalColor[0]["val"];
	if($originalColor != $newcolor){
		$color=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"fill_colour\"]");
		$color[0]->color["val"]="$newcolor";
		
		$label=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"text\"]/dia:composite/dia:attribute[@name=\"string\"]");
		$label[0]->string="#$partition
$percent%#";
		
		$textcolor=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"text\"]/dia:composite/dia:attribute[@name=\"color\"]");
		$textcolor[0]->color["val"]="$tcolor";

		$xmlfile->asXML($salida);
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I: ($ID) $host disk $partition Status: $percent ($newcolor) $level";
		check_db($idXml,$host,$ID,"disk $partition",$level);
	}
	//echo "%: $percent ($level)\n";
}

function ubiquiti($idXml,$ID,$xmlfile,$salida,$info){
	$host = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Host']/dia:string");
	$host = explode("#",$host[0]);
	$host= $host[1];

	$community = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name='custom:Community']/dia:string");
	$community = explode("#",$community[0]);
	$community = $community[1];
	
	if(!$host){
//		echo "\n\tI: Verificar, Faltan Parametros en $ID: Host: $host";
		return 0;
	}
	
	switch($info){
		case "stations":	
				//busco las estaciones conectadas
				$mtxrWlRtabStrength = "1.3.6.1.4.1.14988.1.1.1.2.1.3";
				$res = @snmprealwalk($host,$community,$mtxrWlRtabStrength);
				$stations = count($res);
				$label = $xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"text\"]/dia:composite/dia:attribute[@name=\"string\"]/dia:string");
				$label = explode("#",$label[0]);
				$last_stations = $label[1];
				if($last_stations != $stations){
					$label=$xmlfile->xpath("//dia:object[@id=\"$ID\"]/dia:attribute[@name=\"text\"]/dia:composite/dia:attribute[@name=\"string\"]");
					$label[0]->string="#$stations#";
					$xmlfile->asXML($salida);
					$fecha = date("d-m-y H:i");
					echo "\n$fecha I: ($ID) $host Ubiquiti Stations: $stations";
					check_db($idXml,$host,$ID,"ubiquiti stations",$stations);
				}
				break;
	}	
}


/* MAIN */
//is running?
//echo "[$parametro[mail]]";
$PID = getmypid();
$file = "/tmp/dianms.polling.lock";
$fp = fopen($file,"r");
$tsnow = date("U");
if(!$fp){
//	fclose($fp);
	$fp = fopen($file,"w+"); 
	fwrite($fp,$tsnow, 20); 
	fclose($fp); 
}else{
	$fdata = fgets($fp,20);
	fclose($fp);
	$tsdiff = $tsnow - $fdata;
	if($tsdiff >= 1800){
		echo "\n$tsdiff -> del lock file";
		unlink($file);
		$fp = fopen($file,"w+"); 
		fwrite($fp,$tsnow, 20); 
		fclose($fp); 
	}else{
		//echo "\n[$PID] Is running, stoped.";
		echo "\nIs running, stoped.";
		exit;
	}
}

//$tsdiff = $tsnow - $fdata;
//if($tsdiff 

$hashdate = date("YmdHi");
$tsdate = date("Y-m-d H:i:00");

$dianmsdir = $parametro["path"]["dianms"];
$sqlXml="SELECT IdDiagram,content,file_name,Users.email FROM Diagrams, Users WHERE Users.IdUSer=Diagrams.idUser and active='yes'"; //dDiagram='82'";
$resXml=mysql_query($sqlXml);
//global $changes;

if (!$resXml) {
	echo "\nSQL: $sqlXml";
	die(' Invalid query: ' . mysql_error());
}

flush();

while($xml=mysql_fetch_array($resXml)){
	$fecha = date("d-m-y H:i");
	$body = $fecha."\n";
	$flag = 0;
	$changes = array();
	$email = $xml["email"];
	$diagramName = $xml["file_name"];
	//echo "\n[$PID] $fecha $diagramName...\t\t";
	echo "\n$fecha $diagramName...\t\t";

	// Obtengo el contenido xml del diagrama desde la db y lo guardo en disco
	$content = $xml["content"];
	$xmlfilename = "$dianmsdir/web2/img/diaimg/$xml[file_name]";
	if(! file_exists($xmlfilename)){
		$flag = 1;
	}
	$idXml = $xml["IdDiagram"];
	$fd = fopen($xmlfilename,"w");
	if(!$fd){
		$flag = 1;
	}
	fputs($fd,$xml["content"]);
	fclose($fd);
	
	//Leo el archivo para trabajarlo como xml	
	$xmlfile = simplexml_load_file($xmlfilename);
	if($xmlfile == FALSE){
		echo "\n\tE:\tXML Invalido.($xmlfilename) [FAIL]\n";
		$sql = "update Diagrams set idDiagramStatus='1' where IdDiagram=$idXml";
		if(!mysql_query($sql)){
			echo "Error DB: ".mysql_error()." [FAIL]";
		}
		mail("nicolase@itcsa.net.ar,$email","DiaNMS - Archivo XML Invalido","El Diagrama id: $idXml: $diagramName ($xmlfilename) contiene errores o es invalido");
		continue;
	}
	$xml = $xmlfile->xpath("/dia:diagram/dia:layer/dia:object[@type[starts-with(.,'DIANMS')]]");
//	var_dump($xml);
	//Recorro el xml en busca de objetos validos
	foreach ($xml as $item){
		$action = explode("-",$item["type"]);
//		echo "=>$action[1]\n";

		switch($action[1]){
			case "SNMP": 	snmpAction($idXml,$item["id"],$xmlfile,$xmlfilename);
					break;
			case "PING":	pingAction($idXml,$item["id"],$xmlfile,$xmlfilename);
					break;
			case "STP":	stpAction($idXml,$item["id"],$xmlfile,$xmlfilename);
					break;
			case "IfStatus":ifStatus($idXml,$item["id"],$xmlfile,$xmlfilename);
					break;
			case "snmpNube":extOutput($idXml,$item["id"],$xmlfile,$xmlfilename);
					break;
			case "service":checkService($idXml,$item["id"],$xmlfile,$xmlfilename,$action["2"]);
					break;
			case "disk":	checkDisk($idXml,$item["id"],$xmlfile,$xmlfilename);
					break;
			case "ubiquiti": ubiquiti($idXml,$item["id"],$xmlfile,$xmlfilename,$action["2"]);
					break;
			default:	echo "No definido: ".$action[1];
					break;
		}
	}	
//	echo "-> $body ($GLOBALS[body])";	
	//Abro el archivo y obtengo el contenido para comparar
	$fd = fopen($xmlfilename,"r");
	$contenido = fread($fd,filesize($xmlfilename));
	
	$count = count($changes);
	$mail = "nicolase@itcsa.net.ar";
	$subject = "DIANMS-Alert: $diagramName";
	
	if($flag>0){
		//Envio Mail
		$body .= "\n--\nnycko";
		$fecha = date("d-m-y H:i");
		echo "\n$fecha I: Enviando mail a $mail y $email";
//		echo "\n\t$body\n\n";
		mail($mail,$subject,$body);
		if($mail != $email){
			mail($email,$subject,$body);
		}
		$body = "";
			
		//Update Diagram
		$image = $diagramName."_".$hashdate."00.png";
		system("dia $xmlfilename -t png  -e $dianmsdir/web2/img/diaimg/$image 2>&1 >>/tmp/dianms_diacreate.log 2>>/tmp/dianms_diacreate.log");
		echo "\n\tU: system('dia $xmlfilename -t png  -e $dianmsdir/web2/img/diaimg/$image');";
		
		$sqlUpdate = "UPDATE Diagrams SET content='$contenido' WHERE IdDiagram='$idXml'";
		mysql_query($sqlUpdate);
		
		$sqlHist = "INSERT INTO History (IdDiagram,content,ts) VALUES ('$idXml','$contenido','$tsdate')";
		mysql_query($sqlHist);
	}
	$sql = "select idDiagramStatus from Diagrams where IdDiagram=$idXml";
	$res = mysql_query($sql);
	$row = mysql_fetch_array($res);
	if($row["idDiagramStatus"]!=0){
		$sql = "update Diagrams set idDiagramStatus='0' where IdDiagram=$idXml";
		if(!mysql_query($sql)){
			echo "Error DB: ".mysql_error()." [FAIL]";
		}else{
			echo "[DONE]";
		}
	}else{
		echo "[DONE]";
	}
	fclose($fd);
}
	mysql_close();

//Del Lock file
$res = unlink($file);
echo "\n$fecha Borrando lock file $file [$res]";
?>
