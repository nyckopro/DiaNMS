<?php
	include("auth.php");
	include("inc/permissions.php");
	include("inc/config.inc.php");
?>
<html>
 <head>
  <link type="text/css" href="css/base.css" rel="stylesheet" /> 
  <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
  <script type="text/javascript">
	$(document).ready(function(){
		basic_fc();
		$("#diagram_group a").click(function(){
			$("#diagram_list").show();
			var url = this.href;
			$.getJSON(url,function(data){
				$("#diagram_list ul").html("");
				$.each(data,function(){
					$("#diagram_list ul").append("<li><a href='lib/get.php?data=diagram&id="+this.IdDiagram+"'>"+this.file_name+"</a></li>");
				});
				diagram_details();
				form();
			});
			return false;
		});
		
		function basic_fc(){
			$(".l").click(function(){
				var url = this.href;
				var container = this.rel;
				if(container == ""){
					container = "container";
				}
				var name = this.name;
				$.ajax({
                                	url: url,
                        	        success: function(data){
						$("#"+container).html(data);
						switch(name){
							case "user_list":
									user_details();
									break;
						}
						form();
                        	        }
                	        });		
				return false;
			});
		}

		function diagram_details(){
			$("#diagram_list a").click(function(){
				$("#diagram_detail").show();
				$("#diagram_detail .form").show();
				var url = this.href;
				$.getJSON(url,function(data){
					$.each(data[0],function(key , value){
						$(".form [name=modifico]").html("");
						$(".form [name=grupo]").html("");
						switch(key){
							case "IdUser": 	
								$.getJSON("lib/get.php?data=users",function(res){
									$.each(res,function(){
										if(value == this.IdUser){
											$(".form [name=modifico]").append("<option selected value="+this.IdUser+">"+this.name+"</option>");
										}else{
											$(".form [name=modifico]").append("<option value="+this.IdUser+">"+this.name+"</option>");
										}
									});
								});
								break;
							case "active": $(".form [name="+key+"][value="+value+"]").attr("checked","checked");
								break;
							case "IdDiagram": $(".form [name=id]").val(value);
								break;
							case "grupo": 
								$.getJSON("lib/get.php?data=diagram_group_list",function(res){
									$.each(res,function(){
										if(value == this.IdDiagramGroup){
											$(".form [name=grupo]").append("<option selected value="+this.IdDiagramGroup+">"+this.name+"</option>");
										}else{
											$(".form [name=grupo]").append("<option value="+this.IdDiagramGroup+">"+this.name+"</option>");
										}
									});
								});
								break;
							default:
								$(".form [type=text][name="+key+"]").val(value);
						}
					});
					$.get("files.php?diagram="+data[0]['file_name']+"&entry=0",function(preview){
						$(".form [name=preview]").attr('src','img/diaimg/'+preview);
					});
				});
				return false;
			});
		}

		function user_details(){
			$("#user_list a").click(function(){
				$("#user_detail").show();
				$("#user_detail .form").show();
				var url = this.href;
				$.getJSON(url,function(data){
					$.each(data[0],function(key,value){
						switch(key){
							case "IdUser": $(".form [type=hidden][name=id]").val(value);
								break;
							case "idGroup":
								$.getJSON("lib/get.php?data=user_group",function(res){
									$(".form [name=idgroup]").html("");
									$.each(res,function(){	
										if(value == this.IdGroup){
											$(".form [name=idgroup]").append("<option selected value="+this.IdUserGroup+">"+this.name+"</option>");
										}else{
											$(".form [name=idgroup]").append("<option value="+this.IdGroup+">"+this.name+"</option>");
										}
									});
								})
								break;
							default:
								$(".form [type=text][name="+key+"]").val(value);
						}
					});	
				});
				return false;
			});
		}

		function form(){
			$(".form").submit(function(event){
				event.preventDefault();
        	                var url = $(this).attr('action');  
	                        var datos = $(this).serialize(); 
				$.post(url,datos,function(data){
					alert(data["msg"]);
				},'json');
			});
		}
	});	
  </script>
 </head>

 <body>
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
		<a href='dianms.php'>Diagramas</a>
		|
		<a href='logout.php'>Salir</a>
	</nobr>
    </div>
   </div>
   <div class='hb'>
    <nobr>
	<a href='settings.php'>Diagramas</a>
	<a href='settings_users.php' class='l' name='user_list'>Usuarios</a>
	<a href='settings_general.php' class='l' name='params'>Parametros</a>
    </nobr>
   </div>
  </div>
  <div id='container'>
<?php	include("settings_diagrams.php"); ?>	
  </div>
 </body>
</html>
