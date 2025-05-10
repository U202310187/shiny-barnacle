<?php
include('config/db.php');
include('includes/header.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$error = '';
$success = '';

// Obtener estilos actuales del usuario
$estilos_usuario = [];
$query = $conn->query("SELECT id_estilo FROM estilos_usuarios WHERE id_usuario = $id_usuario");
while ($row = $query->fetch_assoc()) {
    $estilos_usuario[] = $row['id_estilo'];
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener estilos seleccionados
    $estilos_seleccionados = isset($_POST['estilos']) ? $_POST['estilos'] : [];
    
    // Eliminar estilos actuales
    $conn->query("DELETE FROM estilos_usuarios WHERE id_usuario = $id_usuario");
    
    // Insertar nuevos estilos
    if (!empty($estilos_seleccionados)) {
        foreach ($estilos_seleccionados as $estilo) {
            $conn->query("INSERT INTO estilos_usuarios (id_usuario, id_estilo) VALUES ($id_usuario, $estilo)");
        }
    }
    
    $success = "Preferencias guardadas correctamente.";
    
    // Redirigir a cajas temáticas
    header("Refresh: 2; URL=cajas-tematicas.php");
}

// Obtener todos los estilos disponibles
$estilos = $conn->query("SELECT * FROM tipos_estilos");
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Personaliza tus Preferencias</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <p class="lead">Selecciona las disciplinas creativas que te interesan:</p>
                
                <form method="post" action="">
                    <div class="row mb-4">
                        <?php while($estilo = $estilos->fetch_assoc()): ?>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="estilos[]" 
                                       value="<?php echo $estilo['id_estilo']; ?>" id="estilo<?php echo $estilo['id_estilo']; ?>"
                                       <?php if(in_array($estilo['id_estilo'], $estilos_usuario)) echo 'checked'; ?>>
                                <label class="form-check-label" for="estilo<?php echo $estilo['id_estilo']; ?>">
                                    <?php echo $estilo['nombre_estilo']; ?>
                                </label>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Preferencias</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>