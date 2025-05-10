<?php
// Configuración de la conexión
$host = "localhost";
$user = "root";
$password = "";
$database = "crafted";

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8");
?>