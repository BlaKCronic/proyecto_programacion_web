<?php
session_start();

unset($_SESSION['vendedor_id']);
unset($_SESSION['vendedor_nombre_tienda']);
unset($_SESSION['vendedor_email']);

header("Location: login.php");
exit();
?>