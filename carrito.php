<?php
// Iniciamos la sesión y conexión a base de datos
session_start();
include("config/db.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Verificar si existe un carrito activo para el usuario
$sql_carrito = "SELECT id_carrito FROM carritos_compras WHERE id_usuario = $id_usuario AND status = 'Activo'";
$result_carrito = mysqli_query($conn, $sql_carrito); // Cambiado $conexion por $conn

if (mysqli_num_rows($result_carrito) == 0) {
    // Crear nuevo carrito si no existe uno activo
    $fecha_actual = date("Y-m-d");
    $sql_crear_carrito = "INSERT INTO carritos_compras (id_usuario, fecha_creacion, status) 
                         VALUES ($id_usuario, '$fecha_actual', 'Activo')";
    mysqli_query($conn, $sql_crear_carrito); // Cambiado $conexion por $conn
    $id_carrito = mysqli_insert_id($conn); // Cambiado $conexion por $conn
} else {
    $row_carrito = mysqli_fetch_assoc($result_carrito);
    $id_carrito = $row_carrito['id_carrito'];
}

// Procesar formulario POST para agregar productos
if (isset($_POST['agregar_carrito']) && isset($_POST['id_producto'])) {
    $id_producto = $_POST['id_producto'];
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    
    // Crear nuevo item para el carrito
    $sql_nuevo_item = "INSERT INTO items_carritos (id_carrito) VALUES ($id_carrito)";
    
    if (mysqli_query($conn, $sql_nuevo_item)) {
        $id_item = mysqli_insert_id($conn);
        
        // Asociar producto con el item
        $sql_update_producto = "UPDATE productos SET id_item = $id_item WHERE id_producto = $id_producto";
        mysqli_query($conn, $sql_update_producto);
        
        header("Location: carrito.php?msg=agregado");
        exit();
    } else {
        echo "Error al agregar producto: " . mysqli_error($conn);
    }
}

// Manejar acciones del carrito
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    
    if ($accion == 'agregar' && isset($_GET['id'])) {
        $id_producto = $_GET['id'];
        
        // Crear nuevo item para el carrito
        $sql_nuevo_item = "INSERT INTO items_carritos (id_carrito) VALUES ($id_carrito)";
        mysqli_query($conn, $sql_nuevo_item); // Cambiado $conexion por $conn
        $id_item = mysqli_insert_id($conn); // Cambiado $conexion por $conn
        
        // Asociar producto con el item
        $sql_update_producto = "UPDATE productos SET id_item = $id_item WHERE id_producto = $id_producto";
        mysqli_query($conn, $sql_update_producto); // Cambiado $conexion por $conn
        
        header("Location: carrito.php?msg=agregado");
        exit();
        
    } elseif ($accion == 'eliminar' && isset($_GET['id_item'])) {
        $id_item = $_GET['id_item'];
        
        // Primero desasociar el producto del item
        $sql_desasociar = "UPDATE productos SET id_item = 0 WHERE id_item = $id_item";
        mysqli_query($conn, $sql_desasociar); // Cambiado $conexion por $conn
        
        // Luego eliminar el item
        $sql_eliminar = "DELETE FROM items_carritos WHERE id_item = $id_item";
        mysqli_query($conn, $sql_eliminar); // Cambiado $conexion por $conn
        
        header("Location: carrito.php?msg=eliminado");
        exit();
        
    } elseif ($accion == 'completar') {
        // Crear nueva compra
        $fecha_actual = date("Y-m-d");
        
        // Obtener total del carrito
        $sql_total = "SELECT SUM(p.precio) as total 
                     FROM productos p 
                     JOIN items_carritos i ON p.id_item = i.id_item 
                     WHERE i.id_carrito = $id_carrito";
        $result_total = mysqli_query($conn, $sql_total); // Cambiado $conexion por $conn
        $row_total = mysqli_fetch_assoc($result_total);
        $total = $row_total['total'];
        
        // Si hay productos en el carrito
        if ($total > 0) {
            // Crear registro de compra
            $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : 'Tarjeta';
            $sql_compra = "INSERT INTO compras (id_usuario, fecha, total, metodo_pago) 
                         VALUES ($id_usuario, '$fecha_actual', $total, '$metodo_pago')";
            mysqli_query($conn, $sql_compra); // Cambiado $conexion por $conn
            $id_compra = mysqli_insert_id($conn); // Cambiado $conexion por $conn
            
            // Obtener productos del carrito
            $sql_productos = "SELECT p.id_producto, p.precio 
                            FROM productos p 
                            JOIN items_carritos i ON p.id_item = i.id_item 
                            WHERE i.id_carrito = $id_carrito";
            $result_productos = mysqli_query($conn, $sql_productos); // Cambiado $conexion por $conn
            
            // Crear detalle de compra para cada producto
            while ($producto = mysqli_fetch_assoc($result_productos)) {
                $id_producto = $producto['id_producto'];
                $precio = $producto['precio'];
                
                $sql_detalle = "INSERT INTO detalles_compras (id_compra, id_producto, cantidad, precio_unitario) 
                             VALUES ($id_compra, $id_producto, 1, $precio)";
                mysqli_query($conn, $sql_detalle); // Cambiado $conexion por $conn
            }
            
            // Cambiar estado del carrito
            $sql_update_carrito = "UPDATE carritos_compras SET status = 'Pendiente' WHERE id_carrito = $id_carrito";
            mysqli_query($conn, $sql_update_carrito); // Cambiado $conexion por $conn
            
            header("Location: carrito.php?msg=compra_completada&id_compra=$id_compra");
            exit();
        }
    }
}

// Obtener productos en el carrito
$sql_productos = "SELECT p.*, i.id_item 
                FROM productos p 
                JOIN items_carritos i ON p.id_item = i.id_item 
                WHERE i.id_carrito = $id_carrito";
$result_productos = mysqli_query($conn, $sql_productos); // Cambiado $conexion por $conn

// Calcular total
$sql_total = "SELECT SUM(precio) as total FROM productos WHERE id_item IN (SELECT id_item FROM items_carritos WHERE id_carrito = $id_carrito)";
$result_total = mysqli_query($conn, $sql_total); // Cambiado $conexion por $conn
$row_total = mysqli_fetch_assoc($result_total);
$total = $row_total['total'] ?? 0;

// Incluir header - esto reemplaza todo el HTML hasta el contenido principal
include("includes/header.php");
?>

<div class="container my-5">
    <h1 class="mb-4">Mi carrito</h1>
    
    <?php if (isset($_GET['msg'])) { 
        $msg = $_GET['msg'];
        if ($msg == 'agregado') { ?>
            <div class="alert alert-success">
                Producto agregado al carrito correctamente.
            </div>
        <?php } elseif ($msg == 'eliminado') { ?>
            <div class="alert alert-warning">
                Producto eliminado del carrito.
            </div>
        <?php } elseif ($msg == 'compra_completada') { 
            $id_compra = $_GET['id_compra']; ?>
            <div class="alert alert-success">
                <h4>¡Compra realizada con éxito!</h4>
                <p>Tu número de orden es: <?php echo $id_compra; ?></p>
                <p>Recibirás un correo con los detalles de tu compra.</p>
            </div>
        <?php }
    } ?>
    
    <?php if (mysqli_num_rows($result_productos) > 0) { ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = mysqli_fetch_assoc($result_productos)) { ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $producto['imagen_url']; ?>" alt="<?php echo $producto['nombre']; ?>" class="producto-img" style="max-height: 100px; object-fit: contain;">
                                </td>
                                <td>
                                    <h5><?php echo $producto['nombre']; ?></h5>
                                    <p class="text-muted"><?php echo $producto['descripcion']; ?></p>
                                </td>
                                <td class="text-end">
                                    $<?php echo number_format($producto['precio'], 2); ?>
                                </td>
                                <td class="text-center">
                                    <a href="carrito.php?accion=eliminar&id_item=<?php echo $producto['id_item']; ?>" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total:</th>
                            <th class="text-end">$<?php echo number_format($total, 2); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <a href="productos.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Seguir comprando
                </a>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmarCompraModal">
                    <i class="fas fa-check"></i> Finalizar compra
                </button>
            </div>
        </div>
        
        <!-- Modal de confirmación de compra -->
        <div class="modal fade" id="confirmarCompraModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="carrito.php?accion=completar" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar compra</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Total a pagar: <strong>$<?php echo number_format($total, 2); ?></strong></p>
                            
                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Método de pago:</label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="Tarjeta">Tarjeta de crédito/débito</option>
                                    <option value="PayPal">PayPal</option>
                                    <option value="Transferencia">Transferencia bancaria</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <small>Al finalizar la compra, recibirás un correo con los detalles de tu pedido.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Confirmar compra</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    <?php } else { ?>
        <div class="alert alert-info">
            <h4 class="alert-heading">¡Tu carrito está vacío!</h4>
            <p>Añade productos para comenzar a comprar.</p>
            <hr>
            <a href="productos.php" class="btn btn-primary">Ver productos</a>
        </div>
    <?php } ?>
</div>

<?php include("includes/footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>