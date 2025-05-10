<?php
session_start();
include 'conexion.php';

$accion = $_POST['accion'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    echo "Debes iniciar sesión para completar la compra.";
    exit;
}

if ($accion == 'confirmar') {
    $carrito = $_SESSION['carrito'] ?? [];

    if (empty($carrito)) {
        echo "El carrito está vacío.";
        exit;
    }

    // Crear una orden
    $fecha = date("Y-m-d H:i:s");
    $conn->query("INSERT INTO ordenes (usuario_id, fecha, estado) VALUES ($usuario_id, '$fecha', 'confirmada')");
    $orden_id = $conn->insert_id;

    // Insertar detalles
    foreach ($carrito as $item) {
        $producto_id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        $conn->query("INSERT INTO orden_detalles (orden_id, producto_id, cantidad, precio_unitario)
                      VALUES ($orden_id, $producto_id, $cantidad, $precio)");
    }

    unset($_SESSION['carrito']);

    echo "<h3>Compra confirmada. Gracias por tu pedido.</h3>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";

} elseif ($accion == 'cancelar') {
    unset($_SESSION['carrito']);
    echo "<h3>Compra cancelada.</h3>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
} else {
    echo "Acción no válida.";
}
?>
