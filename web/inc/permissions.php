<?php
	if ( $_SESSION["gid"] != $_SESSION["idGroupAdmin"]){
		echo "No tienes permisos para ver esta pagina";
		exit;
	}
?>
