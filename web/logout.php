<?php
session_start();
// Borramos toda la sesion
session_destroy();
header("location:index.php");
?>
