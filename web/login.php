<?php
include("inc/parametros.php");
if (isset($_SESSION['username'])) {
	include ("dianms.php");
}else{
?>

<form action="validar.php" method="post">

<TABLE>
 <TR>
  <TD><label for='user'>Usuario: </label></TD><TD><input type="text" name="user" size="20" maxlength="50" /></TD>
 </TR>
 <TR>
  <TD><label for='pass'>Password: </label></TD><TD><input type="password" name="pass" size="20" maxlength="50" /><br></TD>
 </TR>
 <TR>
  <TD colspan=2>
	<SELECT NAME='auth'>
		<OPTION VALUE='ldap'>ldap
		<OPTION VALUE='db'>local
	</SELECT>
  </TD>
 </TR>
 <TR>
  <TD colspan=2 align=center>
	<input type="submit" value="Ingresar" />
  </TD>
 </TR>
 <TR>
  <TD colspan=2 align=center>
	
  </TD>
 </TR>
</TABLE>

</form>
<?php
}
?>
