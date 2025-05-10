<?php
include('config/db.php');
include('includes/header.php');
?>

<!-- Mostrar mensaje de cierre de sesión -->
<?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
    <div class="alert alert-success text-center">
        Has cerrado sesión correctamente.
    </div>
<?php endif; ?>

<!-- Hero Banner -->
<div class="bg-light p-5 rounded-lg mb-5">
    <div class="container">
        <h1 class="display-4">Descubre tu lado creativo con Crafted</h1>
        <p class="lead">Plataforma especializada en materiales para pintura, dibujo, escultura, fotografía y más.</p>
        <hr class="my-4">
        <p>Únete hoy y recibe recomendaciones personalizadas basadas en tus intereses creativos.</p>
        <?php if(!isset($_SESSION['id_usuario'])): ?>
        <a class="btn btn-primary btn-lg" href="registro.php">Registrarse</a>
        <a class="btn btn-outline-primary btn-lg" href="login.php">Iniciar Sesión</a>
        <?php else: ?>
        <a class="btn btn-primary btn-lg" href="cajas-tematicas.php">Ver Cajas Temáticas</a>
        <?php endif; ?>
    </div>
</div>

<!-- Características Principales -->
<div class="row mb-5">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box-seam text-primary" style="font-size: 3rem;"></i>
                <h3 class="card-title">Cajas Temáticas</h3>
                <p class="card-text">Recibe mensualmente kits curados con materiales de alta calidad y guías paso a paso.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-palette text-primary" style="font-size: 3rem;"></i>
                <h3 class="card-title">Productos Especializados</h3>
                <p class="card-text">Catálogo amplio organizado por disciplinas creativas y niveles de experiencia.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                <h3 class="card-title">Experiencia Personalizada</h3>
                <p class="card-text">Recomendaciones adaptadas a tu estilo creativo y nivel de experiencia.</p>
            </div>
        </div>
    </div>
</div>

<!-- Estilos Creativos -->
<h2 class="text-center mb-4">Disciplinas Creativas</h2>
<div class="row">
    <?php
    $estilos = ["Pintura", "Escultura", "Fotografía", "Acuarela", "Dibujo"];
    foreach($estilos as $estilo):
    ?>
    <div class="col-md-2 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><?php echo $estilo; ?></h5>
                <?php if(isset($_SESSION['id_usuario'])): ?>
                <a href="productos.php?estilo=<?php echo urlencode($estilo); ?>" class="btn btn-sm btn-outline-primary">Ver productos</a>
                <?php else: ?>
                <a href="registro.php" class="btn btn-sm btn-outline-primary">Regístrate para ver</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include('includes/footer.php'); ?>
