<?php
session_start();
if(!isset($_SESSION['auth']) & !isset($_SESSION['auth_user']))
{
 $_SESSION['auth_status']="Login To Access Dashboard";
 header("Location: login.php");
}
?>