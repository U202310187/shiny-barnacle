<?php
//Configuración de DB y cabecera HTML
include('config/db.php');
include('includes/header.php');

//Sistema de autenticación: verifica si existe una sesión de usuario activa
//Si no existe, redirige al usuario a la página de login y termina la ejecución
if (!isset($_SESSION['id_usuario'])) {
   header("Location: login.php");
   exit;
}

//Captura el parámetro 'estilo' de la URL si existe, aplicando escape de caracteres para evitar inyección SQL
//Si no existe, establece una cadena vacía como valor predeterminado
$filtro_estilo = isset($_GET['estilo']) ? $conn->real_escape_string($_GET['estilo']) : "";

//Define un array asociativo con los IDs específicos de productos que pertenecen a cada categoría
//Esto permite controlar exactamente qué productos se muestran en cada categoría y en qué orden
$orden_ids = array(
   'Acuarela' => array(3, 13),
   'Fotografía' => array(4, 9, 14),
   'Escultura' => array(2, 10, 15),
   'Pintura' => array(1, 5, 6, 8, 12),
   'Dibujo' => array(7, 11)
);

//Lógica de filtrado condicional:
if (!empty($filtro_estilo) && isset($orden_ids[$filtro_estilo])) {
   // Si hay un filtro de estilo seleccionado y existe en nuestro array de categorías:
   // Convierte el array de IDs en una cadena separada por comas para usar en SQL
   $ids_categoria = implode(',', $orden_ids[$filtro_estilo]);
   
   //Consulta SQL que selecciona productos específicos por sus IDs
   //La función FIELD() de MySQL permite ordenar los resultados en el mismo orden que el array de IDs
   $sql = "SELECT * FROM productos WHERE id_producto IN ($ids_categoria) ORDER BY FIELD(id_producto, $ids_categoria)";
} else {
   //Si no hay filtro activo, muestra todos los productos ordenados por ID
   $sql = "SELECT * FROM productos ORDER BY id_producto";
}

//Ejecuta la consulta SQL
$productos_query = $conn->query($sql);

//Verifica si hubo errores en la consulta y muestra el mensaje de error si es necesario
if (!$productos_query) {
   die("Error en la consulta: " . $conn->error);
}

//Consulta para obtener la lista de categorías/temas disponibles para mostrar en la navegación
$temas_query = $conn->query("SELECT DISTINCT tema FROM cajas_tematicas ORDER BY tema");
?>

<!-- Encabezado del catálogo con sistema de navegación por categorías -->
<div class="d-flex justify-content-between align-items-center mb-4">
   <h2>Catálogo de Productos</h2>
   
   <!-- Grupo de botones para la navegación por categorías -->
   <div class="btn-group">
       <!-- Botón para mostrar todos los productos -->
       <a href="productos.php" class="btn <?php echo empty($filtro_estilo) ? 'btn-primary' : 'btn-outline-primary'; ?>">Todos</a>
       <!-- Genera botones dinámicamente para cada categoría desde la base de datos -->
       <?php while($tema = $temas_query->fetch_assoc()): ?>
           <a href="productos.php?estilo=<?php echo urlencode($tema['tema']); ?>" 
           class="btn <?php echo ($filtro_estilo == $tema['tema']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
               <?php echo $tema['tema']; ?>
           </a>
       <?php endwhile; ?>
   </div>
</div>

<!-- Contenedor principal que muestra los productos en formato de rejilla -->
<div class="row">
   <?php if ($productos_query->num_rows > 0): ?>
       <!-- Itera a través de los productos encontrados -->
       <?php while($producto = $productos_query->fetch_assoc()): ?>
       <!-- Cada producto se muestra en una tarjeta dentro de una columna -->
       <div class="col-md-4 mb-4">
           <div class="card h-100">
               <!-- Imagen del producto -->
               <img src="<?php echo $producto['imagen_url']; ?>" class="card-img-top" alt="<?php echo $producto['nombre']; ?>">
               <div class="card-body">
                   <!-- Información del producto: nombre, descripción y precio -->
                   <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                   <p class="card-text"><?php echo $producto['descripcion']; ?></p>
                   <p><strong>Precio:</strong> $<?php echo $producto['precio']; ?></p>
                   
                   <!-- Formulario para añadir el producto al carrito de compras -->
                   <form action="carrito.php" method="post">
                       <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                       <div class="d-flex justify-content-between align-items-center">
                           <!-- Control para seleccionar la cantidad -->
                           <div class="input-group input-group-sm" style="max-width: 120px;">
                               <span class="input-group-text">Cant.</span>
                               <input type="number" class="form-control" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>">
                           </div>
                           <!-- Botón para añadir al carrito -->
                           <button type="submit" name="agregar_carrito" class="btn btn-primary btn-sm">
                               <i class="bi bi-cart-plus"></i> Añadir
                           </button>
                       </div>
                   </form>
               </div>
               <!-- Pie de la tarjeta que muestra el stock disponible -->
               <div class="card-footer text-muted">
                   Stock disponible: <?php echo $producto['stock']; ?>
               </div>
           </div>
       </div>
       <?php endwhile; ?>
   <?php else: ?>
       <!-- Mensaje cuando no se encuentran productos -->
       <div class="col-12">
           <div class="alert alert-info">
               No se encontraron productos que coincidan con los criterios de búsqueda.
           </div>
       </div>
   <?php endif; ?>
</div>

<!-- footer -->
<?php include('includes/footer.php'); ?>