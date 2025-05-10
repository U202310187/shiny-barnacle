<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("config/db.php");
include("includes/functions.php");

// Redirigir si no está logueado
if(!estaAutenticado()) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$error = "";
$success = "";

// Obtener datos del usuario
$sql_usuario = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
$result_usuario = mysqli_query($conn, $sql_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Obtener estilos del usuario
$estilos_usuario = obtenerEstilosUsuario($id_usuario, $conn);

// Obtener todos los estilos disponibles
$sql_todos_estilos = "SELECT * FROM tipos_estilos";
$result_todos_estilos = mysqli_query($conn, $sql_todos_estilos);
$todos_estilos = array();
while($estilo = mysqli_fetch_assoc($result_todos_estilos)) {
    $todos_estilos[] = $estilo;
}

// Actualizar perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_perfil'])) {
    $nombre = limpiarDatos($_POST['nombre'], $conn);
    $apellido = limpiarDatos($_POST['apellido'], $conn);
    $email = limpiarDatos($_POST['email'], $conn);
    $experiencia = limpiarDatos($_POST['experiencia'], $conn);
    
    // Verificar si el email ya existe y no es el del usuario actual
    $check_email = "SELECT * FROM usuarios WHERE email = '$email' AND id_usuario != $id_usuario";
    $result_email = mysqli_query($conn, $check_email);
    
    if(mysqli_num_rows($result_email) > 0) {
        $error = "Este email ya está registrado por otro usuario";
    } else {
        // Actualizar usuario
        $sql = "UPDATE usuarios SET 
                nombre = '$nombre', 
                apellido = '$apellido', 
                email = '$email', 
                experiencia = '$experiencia' 
                WHERE id_usuario = $id_usuario";
                
        if(mysqli_query($conn, $sql)) {
            // Actualizar datos de sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido;
            $_SESSION['email'] = $email;
            $_SESSION['experiencia'] = $experiencia;
            
            $success = "Perfil actualizado correctamente";
            
            // Recargar datos del usuario
            $result_usuario = mysqli_query($conn, $sql_usuario);
            $usuario = mysqli_fetch_assoc($result_usuario);
        } else {
            $error = "Error al actualizar perfil: " . mysqli_error($conn);
        }
    }
}

// Actualizar estilos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_estilos'])) {
    if(isset($_POST['estilos']) && !empty($_POST['estilos'])) {
        $estilos_seleccionados = $_POST['estilos'];
        
        // Eliminar estilos previos
        $eliminar = "DELETE FROM estilos_usuarios WHERE id_usuario = $id_usuario";
        mysqli_query($conn, $eliminar);
        
        // Insertar nuevos estilos
        foreach($estilos_seleccionados as $id_estilo) {
            $id_estilo = intval($id_estilo);
            $sql = "INSERT INTO estilos_usuarios (id_usuario, id_estilo) VALUES ($id_usuario, $id_estilo)";
            mysqli_query($conn, $sql);
        }
        
        $success = "Estilos actualizados correctamente";
        
        // Recargar estilos del usuario
        $estilos_usuario = obtenerEstilosUsuario($id_usuario, $conn);
    } else {
        $error = "Debes seleccionar al menos un estilo creativo";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Crafted</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include("includes/header.php"); ?>
    
    <div class="container py-5">
        <h1 class="mb-4">Mi Perfil</h1>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Información Personal</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo $usuario['apellido']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $usuario['email']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="experiencia" class="form-label">Nivel de Experiencia</label>
                                <select class="form-select" id="experiencia" name="experiencia" required>
                                    <option value="Principiante" <?php echo ($usuario['experiencia'] == 'Principiante') ? 'selected' : ''; ?>>Principiante</option>
                                    <option value="Intermedio" <?php echo ($usuario['experiencia'] == 'Intermedio') ? 'selected' : ''; ?>>Intermedio</option>
                                    <option value="Avanzado" <?php echo ($usuario['experiencia'] == 'Avanzado') ? 'selected' : ''; ?>>Avanzado</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="actualizar_perfil" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Mis Estilos Creativos</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <?php foreach($todos_estilos as $estilo): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="estilos[]" 
                                            value="<?php echo $estilo['id_estilo']; ?>" 
                                            id="estilo<?php echo $estilo['id_estilo']; ?>"
                                            <?php 
                                            foreach($estilos_usuario as $eu) {
                                                if($eu['id_estilo'] == $estilo['id_estilo']) {
                                                    echo 'checked';
                                                    break;
                                                }
                                            }
                                            ?>>
                                        <label class="form-check-label" for="estilo<?php echo $estilo['id_estilo']; ?>">
                                            <?php echo $estilo['nombre_estilo']; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" name="actualizar_estilos" class="btn btn-primary">Actualizar Estilos</button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow mt-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Actividad Reciente</h3>
                    </div>
                    <div class="card-body">
                        <h5>Últimas Compras</h5>
                        <?php
                        $sql_compras = "SELECT * FROM compras WHERE id_usuario = $id_usuario ORDER BY fecha DESC LIMIT 3";
                        $result_compras = mysqli_query($conn, $sql_compras);
                        
                        if(mysqli_num_rows($result_compras) > 0):
                        ?>
                            <ul class="list-group mb-3">
                                <?php while($compra = mysqli_fetch_assoc($result_compras)): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Compra #<?php echo $compra['id_compra']; ?> - <?php echo date('d/m/Y', strtotime($compra['fecha'])); ?>
                                        <span class="badge bg-primary rounded-pill">$<?php echo $compra['total']; ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No has realizado compras todavía.</p>
                        <?php endif; ?>
                        
                        <h5>Recomendaciones Recientes</h5>
                        <?php
                        $sql_recs = "SELECT * FROM historial_recomendaciones WHERE id_usuario = $id_usuario ORDER BY fecha DESC LIMIT 3";
                        $result_recs = mysqli_query($conn, $sql_recs);
                        
                        if(mysqli_num_rows($result_recs) > 0):
                        ?>
                            <ul class="list-group">
                                <?php while($rec = mysqli_fetch_assoc($result_recs)): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Recomendación #<?php echo $rec['id_recomendacion']; ?> - <?php echo date('d/m/Y', strtotime($rec['fecha'])); ?>
                                        <span class="badge bg-secondary rounded-pill"><?php echo $rec['accion']; ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No hay recomendaciones recientes.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("includes/footer.php"); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>