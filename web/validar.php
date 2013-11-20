<?php
session_start();
include("inc/config.inc.php");

function auth_ldap(){
	include("inc/parametros.php");
	$username=$_POST["user"];
	$password=$_POST["pass"];
	$ds=ldap_connect($parametro["ldap"]["host"]);  // Debe ser un servidor LDAP válido!
	$user=$parametro["ldap"]["user"];
	$pass=$parametro["ladp"]["pass"];
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	$dn = $parametro["ldap"]["basedn"];
	$filtro = "(&(uid=$username))";

	if ($ds) { 
		$sr=ldap_search($ds,$dn,$filtro);
		$info=ldap_get_entries($ds, $sr);

		if($res=@ldap_bind($ds,$info[0]["dn"],$password)){
			//Informacion desde el LDAP
			$cn = $info[0]["cn"][0];
			$uid = $info[0]["uidnumber"][0];
			$gid = $info[0]["gidnumber"][0];
//			echo "[$uid]";
			session_set_cookie_params(0, "/", $HTTP_SERVER_VARS["HTTP_HOST"], 0);
			setcookie("dianms", "nycko", time() + 32140800);
			//Valido que el usuario ldap exista en la db local de usuarios
			$sql = "select * from Users where username='$username'";
			$ressql = mysql_query($sql);
			$c = mysql_num_rows($ressql);
			if($c==0){
		//		echo "<pre>",var_dump($info),"</pre>";
				$password = md5($password);
				$email = $username."@".$parametro["dianms"]["domain"];
				$sqlins = "insert into Users (IdUser,username,name,password,email,idGroup) values ('$uid','$username','$cn','$password','$email','$gid')";
				mysql_query($sqlins);
			}
			$_SESSION["gid"] = $gid;
			$_SESSION["username"] = $username;
			$_SESSION["uid"] = $uid;
			$sql = "select value as idGroupAdmin from Settings where `key`='idGroupAdmin';";
			$res = mysql_query($sql);
			$row = mysql_fetch_array($res);
			$_SESSION["idGroupAdmin"] = $row["idGroupAdmin"];
			$_SESSION["mensaje"] = "Has sido logueado correctamente";
			header ("location:".$parametro["path"]["index"]."");
		}else{
			echo "Error de Validacion ($res)";
		}
		ldap_close($ds);

	} else {
	    echo "<h4>Ha sido imposible conectar al servidor LDAP</h4>";
	}	
}

function auth_db(){
	include("inc/parametros.php");
	if(trim($_POST["user"]) != "" && trim($_POST["pass"]) != ""){
	        $usuario = strtolower(htmlentities($_POST["user"], ENT_QUOTES));
        	$password = md5($_POST["pass"]);
	        $result = mysql_query("SELECT password, username,IdUser, idGroup FROM Users WHERE username='$usuario';");
        	if($row = @mysql_fetch_array($result)){
                	if($row["password"] == $password){
				session_set_cookie_params(0, "/", $HTTP_SERVER_VARS["HTTP_HOST"], 0);
				$_SESSION["gid"] = $row["idGroup"];
                        	$_SESSION["username"] = $row['username'];
	                        $_SESSION["idUsuario"] = $row["IdUser"];
				$sql = "select value as idGroupAdmin from Settings where `key`='idGroupAdmin';";
				$res = mysql_query($sql);
				$row = mysql_fetch_array($res);
				$_SESSION["idGroupAdmin"] = $row["idGroupAdmin"];
        	                $_SESSION["mensaje"] = "Has sido logueado correctamente";
                	        header ("location:".$parametro["path"]["index"]."");
	                }else{
        	                echo "Password incorrecto";
                	}
	        }else{
        	        echo "Usuario $usuario no existe en la base de datos";
	        }
        	@mysql_free_result($result);
	}else{
        	echo 'Debe especificar un usuario y password';
	}
	mysql_close();	
}

switch($_POST["auth"]){
	case "ldap": auth_ldap();
			break;
	case "db": auth_db();
			break;
	default: echo "Error ($_POST[auth])";
}
?>
