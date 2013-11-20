<?php
function make_graph($idGraph, $from,$to,$path,$file,$label,$width=400,$height=200){
	//echo $path."/".$file."<br>";
	if($file=="") return 3;

	if($from=="" || $to==""){
		$to = time();
		$from = $to - 86400;
	}

	switch($idGraph){
		case "1":
			$file = md5($file);
			$opts = array(  
				"--start",$from,
				"--end",$to,	
        			"--vertical-label","bits/s",
	                       	"--title=$label",
        	               	"--width=$width",
                	       	"--height=$height",
                       		"--watermark=DiaNMS",

	                       	"DEF:inoctets=$path/$file.rrd:t_in:AVERAGE",
        	               	"DEF:outoctets=$path/$file.rrd:t_out:AVERAGE",
                	       	"CDEF:inbits=inoctets,8,*",
                       		"CDEF:outbits=outoctets,8,*",
	                       	"LINE1:inbits#00CF00FF:In traffic\\n",
        	               	"AREA:inbits#00CF0033:",
                	       	"LINE1:outbits#005199FF:Out traffic\\n",
                       		"AREA:outbits#00519933:"
	                );
			break;
		case "2":
			$file = md5($file);
			$opts = array( 
				"--start", $from,
				"--end",$to,
		        	"--vertical-label", "Load (%)",
	                       	"--title=$label",
	        		"--width", "$width",
			        "--height", "$height",
        			"--watermark=DiaNMS",

		        	"DEF:user=$path/$file.rrd:user:AVERAGE",
			        "DEF:nice=$path/$file.rrd:nice:AVERAGE",
        			"DEF:system=$path/$file.rrd:system:AVERAGE",
		        	"DEF:idle=$path/$file.rrd:system:AVERAGE",
        			"DEF:wait=$path/$file.rrd:wait:AVERAGE",

				"CDEF:total=user,nice,+,system,+,idle,+",
				"CDEF:userperc=user,total,/,100,*",
				"CDEF:niceperc=nice,total,/,100,*",
				"CDEF:systemperc=system,total,/,100,*",
				"CDEF:idleperc=idle,total,/,100,*",
				"CDEF:waitperc=wait,total,/,100,*",
		
        			"LINE1:idle#DFDFDFFF:Idle",
		        	"AREA:idle#DFDFDFAA:",
				"GPRINT:idleperc:AVERAGE:Average\:%3.0lf%%\\n",


	        		"LINE1:nice#FFFF00FF:Nice",
			        "AREA:nice#FFFF00AA:",
				"GPRINT:niceperc:AVERAGE:Average\:%3.0lf%%\\n",

	        		"LINE1:system#FF0000FF:System",
			        "AREA:system#FF0000AA:",
				"GPRINT:systemperc:AVERAGE:Average\:%3.0lf%%\\n",

		        	"LINE1:user#00CF00FF:User",
        			"AREA:user#00CF00AA:",
				"GPRINT:userperc:AVERAGE:Average\:%3.0lf%%\\n",

			        "LINE1:wait#0000FFFF:Wait",
	        		"AREA:wait#0000FFAA:",
				"GPRINT:waitperc:AVERAGE:Average\:%3.0lf%%\\n"

			);
			break;
		case "4":
			$file = md5($file);
			$opts = array(  
				"--start",$from,
				"--end",$to,	
        			"--vertical-label","ms / %",
	                       	"--title=$label",
        	               	"--width=$width",
                	       	"--height=$height",
                       		"--watermark=DiaNMS",

	                       	"DEF:pl=$path/$file.rrd:pl:AVERAGE",
        	               	"DEF:rtt=$path/$file.rrd:rtt:AVERAGE",

	                       	"LINE1:rtt#00CF00FF:Round Trip Time\\n",
        	               	"AREA:rtt#00CF0033:",

                	       	"LINE1:pl#FF0000FF:Packet Loss\\n",
                       		"AREA:pl#FF000033:"
	                );
			break;
		case "5":
			$file = md5($file);
			$opts = array(  
				"--start",$from,
				"--end",$to,	
        			"--vertical-label","stations",
	                       	"--title=$label",
        	               	"--width=$width",
                	       	"--height=$height",
                       		"--watermark=DiaNMS",

        	               	"DEF:stations=$path/$file.rrd:stations:MAX",

	                       	"LINE1:stations#00CF00FF:Stations\\n",
        	               	"AREA:stations#00CF0033:",
	                );
			break;
		case "6":
			$host = explode("_",$file);
			$host = long2ip($host[1]);
			$host = str_replace(".","\.",$host); 
			$pathDir = "/usr/local/dianms/scripts/rrd";
			$dir = opendir($pathDir);
			$pattern = "/^ccq\_$host\_.*rrd$/";
			$opts = array(  
				"--start",$from,
				"--end",$to,	
        			"--vertical-label","ccq",
	                       	"--title=$label",
        	               	"--width=$width",
                	       	"--height=$height",
                       		"--watermark=DiaNMS",
	                );
			$i=0;
			while($archivo = readdir($dir)){
				if($archivo!="." && $archivo!=".." && preg_match($pattern,$archivo)){
					$label = explode("_",$archivo);
					$label = $label[2];
					$label = str_replace(".rrd","",$label);
					$opts[]="DEF:ccq$i=$path/$archivo:ccq:MAX";
					//$color = sprintf("#%06X",mt_rand($i, 0xFFFFFF));
					$opts[]="CDEF:normal$i=ccq$i,90,GT,ccq$i,UNKN,IF";
					$opts[]="CDEF:hot$i=ccq$i,90,LE,ccq$i,UNKN,IF";
					$opts[]="LINE1:normal$i#287F1CFF:";
					$opts[]="LINE2:hot$i#FF0000AA:";
					$i++;
				}
			}
			$opts[]="LINE1:normal0#287F1CFF:CCQ Normal > 90 \\n";
			$opts[]="LINE1:hot0#FF0000FF:CCQ Bajo <= 90 \\n";

			break;
		default: return 4;
	}

	$ret = rrd_graph("/usr/local/dianms/web/img/".$file.".gif", $opts);
	if( !is_array($ret) ){
		$err = rrd_error();
              	echo "rrd_graph() ERROR: $err\n";
		return 2;
       	}
	return $file;
}
