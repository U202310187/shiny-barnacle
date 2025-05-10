<?php
session_start();
include 'conexion.php';

// Suponiendo que el carrito está almacenado en $_SESSION['carrito']
$carrito = $_SESSION['carrito'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Resumen de tu compra</h2>
    <?php if (empty($carrito)): ?>
        <p>No hay productos en el carrito.</p>
    <?php else: ?>
        <ul class="list-group mb-3">
            <?php 
            $total = 0;
            foreach ($carrito as $item): 
                $subtotal = $item['precio'] * $item['cantidad'];
                $total += $subtotal;
            ?>
                <li class="list-group-item d-flex justify-content-between">
                    <?= $item['nombre'] ?> x <?= $item['cantidad'] ?>
                    <span>S/ <?= number_format($subtotal, 2) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <h4>Total: S/ <?= number_format($total, 2) ?></h4>

        <form action="confirmacion_compra.php" method="POST">
            <input type="hidden" name="accion" value="confirmar">
            <button type="submit" class="btn btn-success">Confirmar compra</button>
        </form>
        <form action="confirmacion_compra.php" method="POST" class="mt-2">
            <input type="hidden" name="accion" value="cancelar">
            <button type="submit" class="btn btn-danger">Cancelar</button>
        </form>
    <?php endif; ?>
</body>
</html>
