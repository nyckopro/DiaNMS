<?php
	include("auth.php");
	include("inc/config.inc.php");
//	$sql = "select file_name, ts as fecha,(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(ts)) as ts from Diagrams where active='yes' and UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ts)<300;";
//	$sql = "select file_name, ts as fecha,(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(ts)) as ts from Diagrams where active='yes' order by ts desc limit 1;";
//	$sql = "select file_name,History.ts as fecha,(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(History.ts)) as ts from Diagrams, History where Diagrams.IdDiagram=History.IdDiagram and active='yes' and UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(History.ts)<300;";
//	$sql = "select *, UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ts) as diff from Alerts where UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ts)<500 order by ts desc limit 10;";
	$sql = "select Diagrams.IdDiagram, Alerts.object, Alerts.status, Alerts.ts, INET_NTOA(Alerts.host) as ipObject, Diagrams.file_name, UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(Alerts.ts) as diff from Alerts, Diagrams where Alerts.IdDiagram=Diagrams.IdDiagram and UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(Alerts.ts)<=3600 group by Diagrams.file_name order by Alerts.ts desc limit 10";
//	echo $sql;
//	$sql = "select * from Alerts where UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(ts)<=5600 and count >=2 order by ts desc limit 10;";
	$res = mysql_query($sql);
	$num_rows = mysql_num_rows($res);
	
	switch($num_rows){
		case 0: break;
		case 1: $row = mysql_fetch_array($res);
			if($row["diff"]<60){
				$tiempo = $row["diff"]."segundos";
			}else{
				$tiempo = round($row["diff"]/60);	
				$tiempo .= " minutos";
			}
			echo "<div class=alert-now>Cambio en <b><a href='#' onclick=\"changeDiagram('$row[file_name]');\">$row[file_name] ($row[ts])</a></b> hace $tiempo</div>";
			break;
		default: 
			echo "<div class='alert-now'>";
			echo "<a href=\"javascript:showAlerts();\">Cambio de estado en Diagramas</a>";
			echo "<ul class='alert-list'>";
			while($row = mysql_fetch_array($res)){
 				echo "<li><a href=\"javascript:void();\" onclick=\"changeDiagram('$row[IdDiagram]');\">$row[file_name]</a> ($row[ipObject] $row[object]: $row[status])</li>";
			}
			echo "</ul>";
			echo "</div>";
	}
	
?>
