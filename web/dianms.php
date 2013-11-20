<?php
	include("auth.php");
	include("inc/config.inc.php");
?>
<html>
 <head>
  <link type="text/css" href="css/base.css" rel="stylesheet" /> 
<!--  <link rel="stylesheet" href="css/jquery.ui.all.css"> -->
  <link rel="stylesheet" href="js/jgrowl/jquery.jgrowl.css">
  <!--<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script> -->
  <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script> 
  <!-- jQuery UI -->
  <script type="text/javascript" src="js/jquery-ui-1.10.3.custom.min.js"></script> 
  <link rel="stylesheet" href="css/jquery-ui-1.10.3.custom.min.css">
  <link rel="stylesheet" href="css/jquery-ui-timepicker-addon.css">
  <script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script> 
  <script type="text/javascript" src="js/jquery-ui-timepicker-es.js"></script> 
  <script type="text/javascript" src="js/jquery-ui-sliderAccess.js"></script> 

<!-- Slider -->
<!--  <script type="text/javascript" src="js/jquery.ui.core.js"></script> 
  <script type="text/javascript" src="js/jquery.ui.widget.js"></script> 
  <script type="text/javascript" src="js/jquery.ui.mouse.js"></script> 
  <script type="text/javascript" src="js/jquery.ui.slider.js"></script> --!>
<!--  <script type="text/javascript" src="js/jgrowl/jquery.jgrowl.js"></script> -->
<!-- Dialog -->
<!--  <script type="text/javascript" src="js/jquery.ui.dialog.js"></script> 
  <script type="text/javascript" src="js/jquery.ui.draggable.js"></script> 
  <script type="text/javascript" src="js/jquery.ui.resizable.js"></script> 
  <script type="text/javascript" src="js/jquery.ui.position.js"></script> --!>
<!-- Form -->
  <script type="text/javascript" src="js/jquery.form.js"></script> 
<!-- loupe -->
<!--  <script type="text/javascript" src="js/jquery.loupe/js/jquery.loupe.js"></script> 
  <link rel="stylesheet" href="js/jquery.loupe/css/jquery.loupe.css"></link> -->

<!--   <script type="text/javascript" src="js/jquery.dump.js"></script> --!>
  <script type="text/javascript">
	function InicioDiaNMS(){
		$('#nameDiagram').html("DiaNMS");
		$('#imageDiagram').attr("src","img/dianms.png");
		$('#contentDiaNMS').css('display','block');
	}

	function selectDiagram(form){
		if(form.diagram.value == 0){
			InicioDiaNMS();
		}else{
			$('#contentDiaNMS').css('display','none');
			nameDiagram = form.diagram[form.diagram.value].text;
			changeDiagram(nameDiagram);
		}
	}
	
	function changeDiagram(idDiagram){
		$.getJSON("lib/get.php?data=diagram&id="+idDiagram,function(data){
			$("#slider-owner").val(data[0].modifico);
			$('#nameDiagram').html(data[0].file_name);
			$("#slider-last").html("");
			$("#slider-last").append("<a id='dialogAlerts' href='lib/get.php?data=alerts&idDiagram="+data[0].IdDiagram+"'>Ver Alertas</a> ");
			$("#slider-last").append("<a class='l' href='graph.php?show=graph&idDiagram="+data[0].IdDiagram+"'>Ver Graficos</a>");
			$('#downDiagram').attr('href','download.php?file='+data[0].file_name);
			$('.ui-slider-handle').focus();
			slider();
			$("#dialogAlerts").click(function(){
				$.getJSON(this.href,function(data){
					if(data == ''){
						$("#alerts_data").html("No hay alertas para mostrar");
					}else{
						$("#alerts_data").html("");
						$.each(data,function(){
							var date = new Date(this.ts*1000);
							var hours = date.getHours();
							if(hours<10) hours = "0"+hours;
							var minutes = date.getMinutes();
							if(minutes<10) minutes = "0"+minutes;
							var day = date.getDay();
							if(day<10) day = "0"+day;
							var month = date.getMonth()+1;
							if(month<10) month = "0"+month;
							$("#alerts_data").append("<tr class='level_"+this.count+"'><td>"+day+"/"+month+" "+hours+":"+minutes+"</td><td>"+this.ip+"</td><td>"+this.object+"</td><td>"+this.status+"</td></tr>");
						});
					}
					$("#alerts").dialog('open');
				});
				return false;
			});
			
			basics_fc();
		});
	}
	
	function basics_fc(){
		$(".l").click(function(){
			var url = this.href;
			var container = this.rel;
			if(container == ""){
				container = ".content";
			}else{
				container = "#"+container;
			}
//			$(".content").css("width","70%");
			$.get(url,function(res){
				$(container).html(res);
				basics_fc();
			});
			//$("#graphlist").show("1000");
			return false;
		});

		$(".ckbx").click(function(){
			var thisCheck = $(this);
			var idObject = this.name;
			var host = $(".host_"+idObject).val();
			var instance = $(".instance_"+idObject).val();
			var community = $(".community_"+idObject).val();
			var idDiagram = $(".diagram_"+idObject).val();
//			var label =  $(".label_"+idObject).val();
			var idGraph =  $(this).val();
			if(!label){
				var label = $(".label_"+idObject).attr('placeholder');
			}

			if (thisCheck.is (':checked')){
				$.getJSON("lib/set.php?form=graph_add&idDiagram="+idDiagram+"&idObject="+idObject+"&host="+host+"&instance="+instance+"&community="+community+"&idGraph="+idGraph,function(res){
					alert(res["msg"]);
				});
			}else{
				$.getJSON("lib/set.php?form=graph_rem&idDiagram="+idDiagram+"&idObject="+idObject+"&host="+host+"&instance="+instance,function(res){
					alert(res["msg"]);
				});
			}
		});
	
		$(".date").datetimepicker({
			onClose: function (selectedDateTime){
				//alert(selectedDateTime);
				var id = $(this).attr("name");
				var hostint = $(".hostint_"+id).val();
				var instance = $(".instance_"+id).val();
				var width = $(".width_"+id).val();
				var height = $(".height_"+id).val();
				var idGraph = $(".graph_"+id).val();
				var from = $(".date_from_"+id).datetimepicker('getDate');
				var to = $(".date_to_"+id).datetimepicker('getDate');
				var idDiagram = $("#idDiagram").val();

				if(from!=null && to!=null){
					from = from.getTime()/1000;
					to = to.getTime()/1000;
					if(from<to){
						if(id == "OO"){
							$.get("graph.php?show=graph&idDiagram="+idDiagram+"&f="+from+"&t="+to,function(res){
                                                                $(".content").html(res);
								basics_fc();
                                                        });
						}else{
							$.getJSON("lib/get.php?data=graph&idDiagram="+idDiagram+"&idObject="+id+"&f="+from+"&t="+to+"&hostint="+hostint+"&instance="+instance+"&w="+width+"&h="+height+"&idGraph="+idGraph, function(res){
								if(res["status"]=="ok"){
									$(".image_"+id).attr("src",res["imgurl"]);
								}
							});
						}
					}else{
						alert("la fecha de inicio no puede ser mayo que el final");
					}
				}
			}
		});
		
		$(".graph_opts").click(function(){
			var id = this.name;
			if($("#graph_opts_"+id).css('display')=="none"){
				$("#graph_opts_"+id).show();
				$(this).html("-opciones");
			}else{
				$("#graph_opts_"+id).hide();
				$(this).html("+opciones");
			}
			return false;
		});

		$(".form").submit(function(event){
			event.preventDefault();
        	        var url = $(this).attr('action');  
	                var datos = $(this).serialize(); 
			$.post(url,datos,function(data){
				if(data["status"]=="ok"){
					datos = datos.split("&");
					if(datos[0].match(/^idDiagramGraph/)){
						var id = datos[0].split("=");
						var idObject = datos[2].split("=");
						$.getJSON("lib/get.php?data=graph&idDiagramGraph="+id[1],function(res){
						$(".image_"+id[1]).attr("src",res["imgurl"]);
					});
						
					}
				}
				alert(data["msg"]);
			},'json');
		});
	}

	function slider() {
		maxValue = 100;
		$( "#slider-range-max" ).slider({
			range: "max",
			min: 1,
			max: 100,
			value: maxValue,
			change: function(event,ui){
				var entry = maxValue - $("#slider-range-max").slider("value");
				var diagram = $("#nameDiagram").text();
				var url = 'files.php?diagram='+diagram+'&entry='+entry;
				$("#slider-data").load(url,function(response){
					fecha = response.split('dia_');
					fecha = fecha["1"].split('.');
					var year = fecha["0"].substring(0,4);
					var month = fecha["0"].substring(4,6);
					var day = fecha["0"].substring(6,8);
					var hour = fecha["0"].substring(8,10);
					var min = fecha["0"].substring(10,12);
					$("#slider-data").val(day+"-"+month+"-"+year+" "+hour+":"+min+"hs");
					if(response){
						$(".content").html("<img id='imageDiagram'></img>");
						$('#imageDiagram').attr("src","img/diaimg/"+response);
		/*$("#imageDiagram").loupe({
			'default_zoom': 100,
                        'glossy' : false,
                        'drop_shadow' : false 
                });*/
					}
				});
			}
		});
		$( "#slider-data" ).val("Ahora");
	}
	function initialize(){
		InicioDiaNMS();
		$("#select_dgroup").change(function(){
			var iddgroup = $("#select_dgroup").val();
			$.getJSON("lib/get.php?data=diagram_group&id="+iddgroup,function(data){
				$("#select_diagram").html("");
				$("#select_diagram").append("<option value=''>Elegir diagrama</option>");
				$.each(data, function(){
					$("#select_diagram").append("<option value=\""+this.IdDiagram+"\">"+this.file_name+"</option>");
				});
			});
		});
		$("#formUpload [name=dgroup]").change(function(){
			var iddgroup = $("#formUpload [name=dgroup]").val();
			$("#formUpload [name=newGroup]").hide();
			if(iddgroup==0){
				$("#formUpload [name=newGroup]").show();
			}
		});

		$("#select_diagram").change(function(){
			$('#contentDiaNMS').css('display','none');
//			var nameDiagram = $("#select_diagram option:selected").text();
			var idDiagram = $("#select_diagram").val();
			changeDiagram(idDiagram);
		});

		setInterval(function(){
			$("#alert").load("check.php",function(response){
				if(response!=""){
					diagramName = response.split('>');
				}
			});
		},60000);

		slider();
		$('#form-upload').dialog({	
			autoOpen: false,
			width: 500,
			close: function(){ $("#resform").hide(); }
		});
		$('.lnkupload').click(function(){
			$("#resform").html("");
			$('#form-upload').dialog('open');
			return false;
		});
		$("#formUpload").ajaxForm({
			dataType: 'json',
			success: function(data){
				switch(data.type){
					case "error": 
						$("#resform").attr("class",data.type);
						$("#resform").html(data.msg);
						$("#resform").show();
						break;
					case "info":
						$("#resform").attr("class",data.type);
						$("#resform").html(data.msg);
						$("#resform").show();
						setTimeout(function(){
							$("#form-upload").dialog("close");
							$("#resform").hide();
						},3000);
						break;
				}
			}
		});
		
		$("#alerts").dialog({
			autoOpen: false,
			width: 600,
			height: 600
		});
		
		$("#zoom").click(function(){
			var url = $("#imageDiagram").attr("src");
			window.open(url);
			return false;
		});
	}
	function showAlerts(){
		$(".alert-list").toggle("slow");
	}
  </script>
 </head>

 <body onload="initialize();">
 <div id="alert"></div>
<!-- 
	hp -> Header Principal
	ht -> Header Top
	hl -> Header Left
	hc -> Header Center
	hr -> Header Right
--!>
<!-- Header Principal --!>
  <div class='hp'>
   <div class='ht'>
    <div class='hl'>
	<nobr>
		<a href='upload_diagram.php' class='lnkupload'>Subir Diagrama</a> |
		<a href='download/dia.shapes.tar.gz'>Shapes</a>
	</nobr>
    </div>
    <div class='hr'>
	<nobr>
		<b><?php echo $_SESSION["username"]; ?></b>
		|
		<?php 
			$sqlg = "select name from Groups where IdGroup='".$_SESSION["gid"]."'";
			$resg = mysql_query($sqlg);
			$rowg = mysql_fetch_array($resg);
			echo $rowg["name"]; 
		?>
		|
		<a href='settings.php'>Configurar</a>
		|
		<a href='logout.php'>Salir</a>
	</nobr>
    </div>
   </div>
   
   <div class='hb'>
	<form>
	<select name='dgroups' id='select_dgroup'>
		<option value=0>Todos</option>
<?php
	if($_SESSION["username"]!="nicolase"){
		$sqldg = "select * from DiagramsGroups where IdDiagramGroup<>4";
	}else{
		$sqldg = "select * from DiagramsGroups";
	}
	$resdg = mysql_query($sqldg);
	
	while($rowdg = mysql_fetch_array($resdg)){
		$dgroups[] = $rowdg;
	}

	foreach($dgroups as $dgroup){
		echo "<option value='".$dgroup["IdDiagramGroup"]."'>".$dgroup["name"]."</option>";
	}
?>
	</select>
	
	<select name='diagram' id='select_diagram'>
		<option value=0>Diagramas</option>
<?php
	$sqlDiagrams = "select IdDiagram,file_name from Diagrams where active='yes' order by file_name;";
	$resDiagrams = mysql_query($sqlDiagrams);

	while($rowDiagrams = mysql_fetch_array($resDiagrams)){
		echo "<option value='".$rowDiagrams["IdDiagram"]."'>".$rowDiagrams["file_name"]."</option>";
	}
?>
	</select>
	<span id=nameDiagram></span>
	<span id='linksActions'>
		<a href='#' id='downDiagram' title='Bajar'><img src='img/download.png'></a>
<!--		<a href='#' title='Borrar'><img src='img/delete.png'></a> -->
		<a href='#' id='zoom' title='Zoom'><img src='img/zoom.png'></a>
	</span>
	</form>
   </div>
   <div id="slider-range-max"></div>
   <div id="slider-label">
	<label for="slider-owner">Modifico:</label>
	<input type="text" id="slider-owner" readonly />
	<label for="slider-data">Fecha:</label>
	<input type="text" id="slider-data" readonly/>
	<label id="slider-last"></label>
   </div>
  </div>
  <div class='content'>
	<img id='imageDiagram' src='img/dianms.png' onerror="this.src='img/dianms.png'">
	<span id='contentDiaNMS'><br>DiaNMS Another NMS Simple :D</span>
  </div>

</div>
 <div id='form-upload' title='Cargar Diagrama'>
	<div id='resform'></div>
	<form  name='formUpload' id="formUpload" action="upload_diagram.php" enctype="multipart/form-data" method="post">
		<label for='archivo'>Diagrama:</label>
		<input type="file" id="archivo" name="archivo"/>
		<label for='dgroup'>Categoria:</label>
		<select name='dgroup'>
<?php
		foreach($dgroups as $dgroup){
			echo "<option value='".$dgroup["IdDiagramGroup"]."'>".$dgroup["name"]."</option>";
		}
?>
			<option value=0>Nuevo</option>
		</select>
		<div name='newGroup' style='display:none'>
			<label for='newCategory'>Nueva Categoria: </label>
			<input type='text' name='newCategory'/>
		</div>
		<input type="hidden" name="uid" value="<?php echo $_SESSION["uid"];?>"/>
		<input type="submit" value="Cargar"/>
	</form>
 </div>

 <div id=alerts title='Alertas del Mes'><table id='alerts_data'></table></div>
 <div id='graphlist'></div>
 </body>
</html>
