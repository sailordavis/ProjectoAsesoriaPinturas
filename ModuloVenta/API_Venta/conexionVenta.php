<?php
$mysqli  = new mysqli("localhost", "root", "W41fuIlove","ProyectoPinturas");

if ($mysqli ->connect_error) {
    die("Error de conexión: " . $mysqli ->connect_error);
}
?>