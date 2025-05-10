<?php
session_start();
include("config/db.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener información de suscripción del usuario
$sql_suscripcion = "SELECT * FROM suscripciones 
                   WHERE id_usuario = $id_usuario 
                   AND (status = 'Activa' OR status = 'Pausada')
                   ORDER BY fecha_ini DESC LIMIT 1";
$result_suscripcion = mysqli_query($conn, $sql_suscripcion);
$tiene_suscripcion = mysqli_num_rows($result_suscripcion) > 0;

// Procesar nueva suscripción si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_suscripcion'])) {
    $tipo = $_POST['tipo'];
    $fecha_ini = date('Y-m-d');
    
    // Calcular fecha fin según tipo de suscripción
    if ($tipo == 'Mensual') {
        $fecha_fin = date('Y-m-d', strtotime('+1 month'));
    } else if ($tipo == 'Trimestral') {
        $fecha_fin = date('Y-m-d', strtotime('+3 months'));
    } else {
        $fecha_fin = date('Y-m-d', strtotime('+1 year'));
    }
    
    // Insertar nueva suscripción
    $sql_nueva = "INSERT INTO suscripciones (id_usuario, fecha_ini, fecha_fin, status, tipo) 
                 VALUES ($id_usuario, '$fecha_ini', '$fecha_fin', 'Activa', '$tipo')";
    
    if (mysqli_query($conn, $sql_nueva)) {
        $mensaje = "¡Tu suscripción ha sido activada con éxito!";
        // Recargar información de suscripción
        $result_suscripcion = mysqli_query($conn, $sql_suscripcion);
        $tiene_suscripcion = true;
    } else {
        $error = "Error al activar la suscripción: " . mysqli_error($conn);
    }
}

// Actualizar estado de suscripción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_status'])) {
    $id_suscripcion = $_POST['id_suscripcion'];
    $nuevo_status = $_POST['nuevo_status'];
    
    $sql_update = "UPDATE suscripciones SET status = '$nuevo_status' 
                  WHERE id_suscripcion = $id_suscripcion AND id_usuario = $id_usuario";
    
    if (mysqli_query($conn, $sql_update)) {
        $mensaje = "Estado de suscripción actualizado a: $nuevo_status";
        // Recargar información de suscripción
        $result_suscripcion = mysqli_query($conn, $sql_suscripcion);
        $tiene_suscripcion = mysqli_num_rows($result_suscripcion) > 0;
    } else {
        $error = "Error al actualizar estado: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripciones - Crafted</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container py-5">
        <h1 class="mb-4 text-center">Mi Suscripcion</h1>
        
        <?php if(isset($mensaje)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <?php if($tiene_suscripcion): ?>
                    <?php $suscripcion = mysqli_fetch_assoc($result_suscripcion); ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3>Tu Suscripción Actual</h3>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Plan <?php echo $suscripcion['tipo']; ?></h5>
                            <p class="card-text">
                                <strong>Estado:</strong> <span class="badge <?php echo $suscripcion['status'] == 'Activa' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo $suscripcion['status']; ?>
                                </span>
                            </p>
                            <p class="card-text"><strong>Fecha de inicio:</strong> <?php echo date('d/m/Y', strtotime($suscripcion['fecha_ini'])); ?></p>
                            <p class="card-text"><strong>Fecha de finalización:</strong> <?php echo date('d/m/Y', strtotime($suscripcion['fecha_fin'])); ?></p>
                            <p class="card-text"><strong>Días restantes:</strong> 
                                <?php 
                                $hoy = new DateTime();
                                $fin = new DateTime($suscripcion['fecha_fin']);
                                $diferencia = $hoy->diff($fin);
                                echo $diferencia->format('%a días');
                                ?>
                            </p>
                            
                            <?php if($suscripcion['status'] == 'Activa'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="id_suscripcion" value="<?php echo $suscripcion['id_suscripcion']; ?>">
                                    <input type="hidden" name="nuevo_status" value="Pausada">
                                    <button type="submit" name="actualizar_status" class="btn btn-warning">Pausar Suscripción</button>
                                </form>
                            <?php elseif($suscripcion['status'] == 'Pausada'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="id_suscripcion" value="<?php echo $suscripcion['id_suscripcion']; ?>">
                                    <input type="hidden" name="nuevo_status" value="Activa">
                                    <button type="submit" name="actualizar_status" class="btn btn-success">Reactivar Suscripción</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3>Suscríbete Ahora</h3>
                        </div>
                        <div class="card-body">
                            <p class="card-text">No tienes ninguna suscripción activa actualmente.</p>
                            <h5>Selecciona tu plan ideal:</h5>
                            
                            <form method="post" action="">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="tipo" id="mensual" value="Mensual" checked>
                                    <label class="form-check-label" for="mensual">
                                        <strong>Plan Mensual</strong> - Acceso a cajas básicas
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="tipo" id="trimestral" value="Trimestral">
                                    <label class="form-check-label" for="trimestral">
                                        <strong>Plan Trimestral</strong> - Acceso a cajas premium + 10% descuento
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="tipo" id="anual" value="Anual">
                                    <label class="form-check-label" for="anual">
                                        <strong>Plan Anual</strong> - Acceso a todas las cajas + 20% descuento
                                    </label>
                                </div>
                                
                                <button type="submit" name="nueva_suscripcion" class="btn btn-primary">Activar Suscripción</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3>Beneficios de Suscripción</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Cajas temáticas mensuales personalizadas
                            </li>
                            <li class="list-group-item d-flex">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Tutoriales exclusivos para suscriptores
                            </li>
                            <li class="list-group-item d-flex">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Descuentos en productos regulares
                            </li>
                            <li class="list-group-item d-flex">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Acceso anticipado a nuevos lanzamientos
                            </li>
                            <li class="list-group-item d-flex">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Envío gratuito en todas tus compras
                            </li>
                        </ul>
                        
                        <!-- Mostrar cajas recomendadas según perfil del usuario -->
                        <?php
                        // Obtener estilo y experiencia del usuario
                        $sql_estilo = "SELECT t.nombre_estilo FROM estilos_usuarios e 
                                      JOIN tipos_estilos t ON e.id_estilo = t.id_estilo 
                                      WHERE e.id_usuario = $id_usuario";
                        $sql_exp = "SELECT experiencia FROM usuarios WHERE id_usuario = $id_usuario";
                        
                        $result_estilo = mysqli_query($conn, $sql_estilo);
                        $result_exp = mysqli_query($conn, $sql_exp);
                        
                        if (mysqli_num_rows($result_estilo) > 0 && mysqli_num_rows($result_exp) > 0) {
                            $estilo = mysqli_fetch_assoc($result_estilo)['nombre_estilo'];
                            $experiencia = mysqli_fetch_assoc($result_exp)['experiencia'];
                            
                            // Obtener caja recomendada
                            $sql_caja = "SELECT * FROM cajas_tematicas 
                                        WHERE tema = '$estilo' AND experiencia = '$experiencia' 
                                        LIMIT 1";
                            $result_caja = mysqli_query($conn, $sql_caja);
                            
                            if (mysqli_num_rows($result_caja) > 0) {
                                $caja = mysqli_fetch_assoc($result_caja);
                                ?>
                                <div class="mt-4">
                                    <h5>Caja recomendada para ti:</h5>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo $caja['nombre']; ?></h6>
                                            <p class="card-text"><small><?php echo $caja['descripcion']; ?></small></p>
                                            <p><span class="badge bg-primary"><?php echo $caja['tema']; ?></span> 
                                               <span class="badge bg-secondary"><?php echo $caja['experiencia']; ?></span></p>
                                            <a href="cajas-tematicas.php?id=<?php echo $caja['id_caja']; ?>" class="btn btn-sm btn-outline-primary">Ver detalles</a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <!-- Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>