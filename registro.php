<?php
include('config/db.php');
include('includes/header.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellido = $conn->real_escape_string($_POST['apellido']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $experiencia = $conn->real_escape_string($_POST['experiencia']);
    
    // Verificar que el email no esté registrado
    $check_email = $conn->query("SELECT id_usuario FROM usuarios WHERE email = '$email'");
    
    if ($check_email->num_rows > 0) {
        $error = "Este email ya está registrado. Por favor utiliza otro.";
    } else {
        // Generar un ID único (para simplificar)
        $id_usuario = rand(1000, 9999);
        
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (id_usuario, nombre, apellido, email, password, experiencia) 
                VALUES ($id_usuario, '$nombre', '$apellido', '$email', '$password', '$experiencia')";
        
        if ($conn->query($sql) === TRUE) {
            // Crear sesión
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido;
            $_SESSION['email'] = $email;
            $_SESSION['experiencia'] = $experiencia;
            
            // Redirigir a página de preferencias
            header("Location: preferencias.php");
            exit;
        } else {
            $error = "Error al registrar: " . $conn->error;
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Registro de Usuario</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="experiencia" class="form-label">Nivel de Experiencia</label>
                        <select class="form-select" id="experiencia" name="experiencia" required>
                            <option value="">Selecciona tu nivel</option>
                            <option value="Principiante">Principiante</option>
                            <option value="Intermedio">Intermedio</option>
                            <option value="Avanzado">Avanzado</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </form>
                <p class="mt-3">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>