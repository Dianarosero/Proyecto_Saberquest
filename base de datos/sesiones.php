<?php
session_start();
if(!isset($_SESSION['usuario'])){
echo "redirigir al login";
header('Location:http://localhost/Proyecto_Saberquest/index.php');
}
?>