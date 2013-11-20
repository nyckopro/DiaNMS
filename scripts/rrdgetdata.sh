#!/bin/bash
CONF="/usr/local/dianms/web/inc/parametros.php";
host_db=$(cat $CONF | grep "\$parametro\[\"db\"\]\[\"host\"\]" | awk -F = '{print $2}' | awk -F \" '{print $2}');
user_db=$(cat $CONF | grep "\$parametro\[\"db\"\]\[\"user\"\]" | awk -F = '{print $2}' | awk -F \" '{print $2}');
pass_db=$(cat $CONF | grep "\$parametro\[\"db\"\]\[\"pass\"\]" | awk -F = '{print $2}' | awk -F \" '{print $2}');
table_db=$(cat $CONF | grep "\$parametro\[\"db\"\]\[\"dbname\"\]" | awk -F = '{print $2}' | awk -F \" '{print $2}');
parametros_SQL="--password=$pass_db --user=$user_db -h $host_db $table_db -e "
#RES=$(mysql $parametros_SQL "select idDiagram, idObject, INET_NTOA(host) as host, host as hostint, instance from DiagramsGraph;" -N | tr '\t' '_');
#RES=$(mysql $parametros_SQL "select DiagramsGraph.idDiagram, DiagramsGraph.idObject, INET_NTOA(host) as host, host as hostint, instance, community from DiagramsGraph, Diagrams where DiagramsGraph.idDiagram = Diagrams.IdDiagram and Diagrams.active='yes';" -N | tr '\t' '_');
#RES=$(mysql $parametros_SQL "select DiagramsGraph.idDiagram, DiagramsGraph.idObject, INET_NTOA(host) as host, host as hostint, instance, IF(community='',(select \`value\` from Settings where \`key\`='community'),community) as community, idGraph from DiagramsGraph, Diagrams where DiagramsGraph.idDiagram = Diagrams.IdDiagram and Diagrams.active='yes';" -N | tr '\t' '_');
#RES=$(mysql $parametros_SQL "select DiagramsGraph.idDiagram, DiagramsGraph.idObject, INET_NTOA(host) as host, host as hostint, instance, IF(community='',(select \`value\` from Settings where \`key\`='community'),community) as community, DiagramsGraph.idGraph, ObjectType.name, period, DiagramsGraph.IdDiagramGraph from DiagramsGraph, Diagrams, GraphAvailable, ObjectType where GraphAvailable.idObjectType=ObjectType.IdObjectType and GraphAvailable.idGraph=DiagramsGraph.idGraph and  DiagramsGraph.idDiagram = Diagrams.IdDiagram and Diagrams.active='yes';" -N | tr '\t' '_');
RES=$(mysql $parametros_SQL "select DiagramsGraph.idDiagram, DiagramsGraph.idObject, INET_NTOA(host) as host, host as hostint, instance, IF(community='',(select \`value\` from Settings where \`key\`='community'),community) as community, DiagramsGraph.idGraph, ObjectType.name, ObjectType.period, DiagramsGraph.IdDiagramGraph from DiagramsGraph, Diagrams, GraphAvailable, ObjectType where GraphAvailable.idObjectType=ObjectType.IdObjectType and GraphAvailable.idGraph=DiagramsGraph.idGraph and  DiagramsGraph.idDiagram = Diagrams.IdDiagram and Diagrams.active='yes' and (NOW()-DiagramsGraph.ts)>=ObjectType.period;" -N | tr '\t' '_');
version=1;
ifOutOctets="1.3.6.1.2.1.2.2.1.16"
ifInOctets="1.3.6.1.2.1.2.2.1.10"

LOCKFILE="/tmp/dianms.rrdgetdata.lock"

if [ ! -f "$LOCKFILE" ];then
	touch $LOCKFILE
fi

for DATA in $RES;do
	IDDIAGRAM=$(echo $DATA | awk -F _ '{print $1}');
	IDOBJECT=$(echo $DATA | awk -F _ '{print $2}');
	HOST=$(echo $DATA | awk -F _ '{print $3}');
	HOSTINT=$(echo $DATA | awk -F _ '{print $4}');
	PORT=$(echo $DATA | awk -F _ '{print $5}');
	COMMUNITY=$(echo $DATA | awk -F _ '{print $6}');
	IDGRAPH=$(echo $DATA | awk -F _ '{print $7}');
	TYPE=$(echo $DATA | awk -F _ '{print $8}');
	PERIOD=$(echo $DATA | awk -F _ '{print $9}');
	idDiagramGraph=$(echo $DATA | awk -F _ '{print $10}');

#	echo -n $IDDIAGRAM"_"$HOSTINT"_"$PORT"_"$IDGRAPH 
	FILE=$(echo -n $IDDIAGRAM"_"$HOSTINT"_"$PORT"_"$IDGRAPH | md5sum | awk '{print $1}');
	FILE="/usr/local/dianms/scripts/rrd/$FILE.rrd";
	case "$IDGRAPH" in
		"1")
				if [ ! -f "$FILE" ];then
					rrdtool create "$FILE" -s 60 \
						DS:t_in:DERIVE:600:0:644245094400 \
						DS:t_out:DERIVE:600:0:644245094400 \
						RRA:AVERAGE:0.5:1:8640 \
						RRA:AVERAGE:0.5:30:3456 \
						RRA:AVERAGE:0.5:360:2880
				fi

				PORTIN=$(snmpget -v $version -c $COMMUNITY $HOST ${ifInOctets}.$PORT);
				PORTOUT=$(snmpget -v $version -c $COMMUNITY $HOST ${ifOutOctets}.$PORT);
				rrdtool update "$FILE" N:${PORTIN/*: }:${PORTOUT/*: }
				if [ "$?" == 0 ];then
					mysql $parametros_SQL "update DiagramsGraph set ts=now() where IdDiagramGraph = $idDiagramGraph;"
				fi
				echo "`date +%Y%m%d\ %H%M%S` $TYPE rrdtool update $FILE N:${PORTIN/*: }:${PORTOUT/*: }";
				;;
		"2")
				if [ ! -f "$FILE" ];then
					rrdtool create "$FILE" \
						DS:user:COUNTER:600:U:U       \
						DS:nice:COUNTER:600:U:U       \
						DS:system:COUNTER:600:U:U     \
						DS:idle:COUNTER:600:U:U       \
						DS:wait:COUNTER:600:U:U       \
						RRA:AVERAGE:0.5:1:576         \
						RRA:AVERAGE:0.5:6:672         \
						RRA:AVERAGE:0.5:24:744        \
						RRA:AVERAGE:0.5:288:732       \
						RRA:MAX:0.5:1:576             \
						RRA:MAX:0.5:6:672             \
						RRA:MAX:0.5:24:744            \
						RRA:MAX:0.5:288:732
				fi
				ARGS=$(snmpget -v$version -c$COMMUNITY -Ovq -Os $HOST ssCpuRawUser.0 ssCpuRawNice.0 ssCpuRawSystem.0 ssCpuRawIdle.0 ssCpuRawWait.0  | tr '\n' ' ' | awk "{ printf(\"update $FILE N:%d:%d:%d:%d:%d\", \$1, \$2, \$3, \$4, \$5) }");
				rrdtool $ARGS # 2>/dev/null >/dev/null
				if [ "$?" == 0 ];then
					mysql $parametros_SQL "update DiagramsGraph set ts=now() where IdDiagramGraph = $idDiagramGraph;"
				fi
				echo "`date +%Y%m%d\ %H%M%S` $TYPE rrdtool $ARGS";
				
				;;
		"4" )
				if [ ! -f "$FILE" ];then
					rrdtool create "$FILE" -s 60 \
						DS:pl:GAUGE:600:0:100 \
						DS:rtt:GAUGE:600:0:10000000 \
						RRA:AVERAGE:0.5:1:800 \
						RRA:AVERAGE:0.5:6:800 \
						RRA:AVERAGE:0.5:24:800 \
						RRA:AVERAGE:0.5:288:800 \
						RRA:MAX:0.5:1:800 \
						RRA:MAX:0.5:6:800 \
						RRA:MAX:0.5:24:800 \
						RRA:MAX:0.5:288:800
				fi

#				temp=$(ping -q -n -c 4 -w 10 $HOST | egrep "packets|^rtt"| tr ' ' '/' | awk -F \/ '{if($2=="packets"){ print $6 }else{ print $8}}' | tr '\n' ':' | tr -d '%');
				temp=$(ping -q -c4 -w 10 $HOST | egrep "packets|^rtt"| tr ' ' '/' | awk -F \/ '{if($7=="packet"){ print $6 }else{ if($7=="errors,"){ print "100\n0.1" } else { print $8 }}}' | tr '\n' ':' | tr -d '%' |sed 's/:$//');
				rrdtool update $FILE --template pl:rtt N:$temp
				if [[ "$?" == 0 || $temp == "" ]];then
					mysql $parametros_SQL "update DiagramsGraph set ts=now() where IdDiagramGraph = $idDiagramGraph;"
				fi
				echo "`date +%Y%m%d\ %H%M%S` $TYPE rrdtool update $FILE N:$temp";
				;;
		"5" )
#			echo $IDDIAGRAM"_"$HOSTINT"_"$PORT"_"$IDGRAPH;
			if [ ! -f "$FILE" ];then
#				echo -en "$FILE No existe, creanding... ";
				rrdtool create "$FILE" -s 600 \
					DS:stations:GAUGE:600:0:10000000 \
					RRA:MAX:0.5:1:800 \
					RRA:MAX:0.5:6:800 \
					RRA:MAX:0.5:24:800 \
					RRA:MAX:0.5:288:800 #&& echo "[DONE]" || echo "[FAIL]";
#					RRA:AVERAGE:0.5:1:800 \
#					RRA:AVERAGE:0.5:6:800 \
#					RRA:AVERAGE:0.5:24:800 \
#					RRA:AVERAGE:0.5:288:800 \
			fi
			temp=$(snmpwalk -v $version -c$COMMUNITY $HOST 1.3.6.1.4.1.14988.1.1.1.2.1.3 | wc -l	);
			rrdtool update $FILE N:$temp
			if [[ "$?" == 0 || $temp == "" ]];then
				mysql $parametros_SQL "update DiagramsGraph set ts=now() where IdDiagramGraph = $idDiagramGraph;"
			fi
			echo "`date +%Y%m%d\ %H%M%S` $IDDIAGRAM $TYPE rrdtool update $FILE N:$temp";
			;;
		"6" )
#			echo "`date +%Y%m%d\ %H%M%S` ubiquiti ccq $HOST";
			KEY_SSH="/usr/local/dianms/data/sshkeys/0_id_rsa";
			USER="itcwladmin";
			#HOST="172.17.237.82";
			DATA_JSON=$(ssh -i $KEY_SSH $USER@$HOST "wstalist");
#			echo "ssh -i $KEY_SSH $USER@$HOST \"wstalist\""
			jsawk="/usr/local/dianms/data/bin/jsawk";
			PATHRRD="/usr/local/dianms/scripts/rrd";
#			echo "$DATA_JSON" | $jsawk 'return this.mac' | tr -d '[]"' | tr ',' ' '
			for MAC in `echo "$DATA_JSON" | $jsawk 'return this.mac' | tr -d '[]"' | tr ',' ' '`;do
				NAME=$(echo "$DATA_JSON" | $jsawk -v MAC=$MAC 'if(this.mac!=MAC) return null' | $jsawk 'return this.name' | sed 's/^\[//' | sed 's/\]$//' | sed 's/^\"//' |  sed 's/\"$//');
				CCQ=$(echo "$DATA_JSON" | $jsawk -v MAC=$MAC 'if(this.mac!=MAC) return null' | $jsawk 'return this.ccq' | sed 's/^\[//' | sed 's/\]$//' | sed 's/^\"//' |  sed 's/\"$//');
				IPST=$(echo "$DATA_JSON" | $jsawk -v MAC=$MAC 'if(this.mac!=MAC) return null' | $jsawk 'return this.lastip' | sed 's/^\[//' | sed 's/\]$//' | sed 's/^\"//' |  sed 's/\"$//');
				FILE=$PATHRRD"/ccq_"$HOST"_"$IPST".rrd"
#				echo $FILE;
				if [ ! -f "$FILE" ];then
					rrdtool create "$FILE" -s 600 \
						DS:ccq:GAUGE:600:0:10000000 \
						RRA:MAX:0.5:1:800 \
						RRA:MAX:0.5:6:800 \
						RRA:MAX:0.5:24:800 \
						RRA:MAX:0.5:288:800
				fi

				rrdtool update $FILE N:$CCQ
				if [[ "$?" == 0 || $temp == "" ]];then
					mysql $parametros_SQL "update DiagramsGraph set ts=now() where IdDiagramGraph = $idDiagramGraph;"
				fi
				echo "`date +%Y%m%d\ %H%M%S` rrdtool update $FILE N:$CCQ";
			done
		;;
	esac
done

rm $LOCKFILE
