<?php
session_start();

unset($_SESSION['admin_id']);
unset($_SESSION['admin_nombre']);
unset($_SESSION['admin_usuario']);

header("Location: login.php");
exit();
?>