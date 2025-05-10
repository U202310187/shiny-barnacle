<?php
include('config/db.php');
include('includes/header.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Obtener filtro de estilo si existe
$filtro_estilo = isset($_GET['estilo']) ? $conn->real_escape_string($_GET['estilo']) : "";

// Construir consulta SQL
$sql = "SELECT p.*, te.nombre_estilo 
        FROM productos p
        LEFT JOIN items_carritos ic ON p.id_item = ic.id_item
        LEFT JOIN carritos_compras cc ON ic.id_carrito = cc.id_carrito
        LEFT JOIN usuarios u ON cc.id_usuario = u.id_usuario
        LEFT JOIN estilos_usuarios eu ON u.id_usuario = eu.id_usuario
        LEFT JOIN tipos_estilos te ON eu.id_estilo = te.id_estilo";

if (!empty($filtro_estilo)) {
    $sql .= " WHERE te.nombre_estilo = '$filtro_estilo'";
}

$sql .= " GROUP BY p.id_producto";
$productos_query = $conn->query($sql);

// Obtener todos los estilos para el filtro
$estilos_query = $conn->query("SELECT * FROM tipos_estilos");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Catálogo de Productos</h2>
    
    <div class="btn-group">
        <a href="productos.php" class="btn <?php echo empty($filtro_estilo) ? 'btn-primary' : 'btn-outline-primary'; ?>">Todos</a>
        <?php while($estilo = $estilos_query->fetch_assoc()): ?>
        <a href="productos.php?estilo=<?php echo urlencode($estilo['nombre_estilo']); ?>" 
           class="btn <?php echo ($filtro_estilo == $estilo['nombre_estilo']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
            <?php echo $estilo['nombre_estilo']; ?>
        </a>
        <?php endwhile; ?>
    </div>
</div>

<div class="row">
    <?php if ($productos_query->num_rows > 0): ?>
        <?php while($producto = $productos_query->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="<?php echo $producto['imagen_url']; ?>" class="card-img-top" alt="<?php echo $producto['nombre']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                    <p class="card-text"><?php echo $producto['descripcion']; ?></p>
                    <p><strong>Precio:</strong> $<?php echo $producto['precio']; ?></p>
                    <?php if(isset($producto['nombre_estilo'])): ?>
                    <p><span class="badge bg-info"><?php echo $producto['nombre_estilo']; ?></span></p>
                    <?php endif; ?>
                    
                    <form action="carrito.php" method="post">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                <span class="input-group-text">Cant.</span>
                                <input type="number" class="form-control" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>">
                            </div>
                            <button type="submit" name="agregar_carrito" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus"></i> Añadir
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    Stock disponible: <?php echo $producto['stock']; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                No se encontraron productos que coincidan con los criterios de búsqueda.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>