<?php
// Configuración de la conexión
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    // Configuración local
    $host = "localhost";
    $user = "root";
    $password = "";
} else {
    // Configuración en la nube
    $host = "20.57.118.228";
    $user = "panconchorizo7";
    $password = "adrian";
}
$database = "Crafted";

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8");
?>