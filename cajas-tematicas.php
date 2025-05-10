<?php
include('config/db.php');
include('includes/header.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$experiencia = $_SESSION['experiencia'];

// Obtener estilos del usuario
$estilos_query = $conn->query("
    SELECT te.nombre_estilo
    FROM estilos_usuarios eu
    JOIN tipos_estilos te ON eu.id_estilo = te.id_estilo
    WHERE eu.id_usuario = $id_usuario
");

$estilos_usuario = [];
while ($row = $estilos_query->fetch_assoc()) {
    $estilos_usuario[] = $row['nombre_estilo'];
}

// Consultar cajas temáticas recomendadas
$cajas_query = $conn->query("
    SELECT *
    FROM cajas_tematicas
    WHERE tema IN ('" . implode("','", $estilos_usuario) . "')
    AND experiencia = '$experiencia'
");
?>

<h2 class="mb-4">Cajas Temáticas Recomendadas para Ti</h2>

<?php if ($cajas_query->num_rows > 0): ?>
<div class="row">
    <?php while($caja = $cajas_query->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo $caja['nombre']; ?></h5>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo $caja['descripcion']; ?></p>
                <p><strong>Tema:</strong> <?php echo $caja['tema']; ?></p>
                <p><strong>Nivel:</strong> <?php echo $caja['experiencia']; ?></p>
                
                <?php
                // Obtener productos de la caja
                $id_caja = $caja['id_caja'];
                $productos_query = $conn->query("
                    SELECT p.nombre, p.precio, pc.cantidad
                    FROM productos p
                    JOIN productos_cajas pc ON p.id_producto = pc.id_producto
                    WHERE pc.id_caja = $id_caja
                ");
                ?>
                
                <h6>Productos incluidos:</h6>
                <ul class="list-group list-group-flush mb-3">
                    <?php while($producto = $productos_query->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?php echo $producto['cantidad']; ?> x <?php echo $producto['nombre']; ?> 
                        <span class="text-muted">($<?php echo $producto['precio']; ?>)</span>
                    </li>
                    <?php endwhile; ?>
                </ul>
                
                <a href="suscripciones.php?caja=<?php echo $id_caja; ?>" class="btn btn-primary">Suscribirme</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
    No encontramos cajas temáticas que coincidan con tus preferencias. 
    <a href="preferencias.php">Actualiza tus preferencias</a> o explora nuestro 
    <a href="productos.php">catálogo completo</a>.
</div>
<?php endif; ?>

<h3 class="mt-5 mb-4">Otras Cajas Temáticas</h3>

<?php
// Consultar otras cajas temáticas
$otras_cajas_query = $conn->query("
    SELECT *
    FROM cajas_tematicas
    WHERE NOT (tema IN ('" . implode("','", $estilos_usuario) . "') AND experiencia = '$experiencia')
    LIMIT 6
");
?>

<div class="row">
    <?php while($caja = $otras_cajas_query->fetch_assoc()): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $caja['nombre']; ?></h5>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo $caja['descripcion']; ?></p>
                <p><strong>Tema:</strong> <?php echo $caja['tema']; ?></p>
                <p><strong>Nivel:</strong> <?php echo $caja['experiencia']; ?></p>
                <a href="suscripciones.php?caja=<?php echo $caja['id_caja']; ?>" class="btn btn-outline-primary">Ver Detalles</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include('includes/footer.php'); ?>