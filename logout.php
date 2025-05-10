<?php
session_start();
$_SESSION = [];
session_destroy();

// Redirigir a la página principal (index.php) con el mensaje de cierre de sesión
header("Location: index.php?logout=1");
exit();
?>
