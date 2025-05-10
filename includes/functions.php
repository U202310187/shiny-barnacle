<?php
// Función para limpiar inputs
function limpiarDatos($datos) {
    global $conn;
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    $datos = mysqli_real_escape_string($conn, $datos);
    return $datos;
}

// Función para verificar si el usuario ya está autenticado
function estaAutenticado() {
    return isset($_SESSION['id_usuario']);
}

// Función para redirigir si no está autenticado
function verificarSesion() {
    if (!estaAutenticado()) {
        header("Location: login.php");
        exit();
    }
}

// Función para obtener los estilos de un usuario
function obtenerEstilosUsuario($id_usuario) {
    global $conn;
    $estilos = [];
    
    $sql = "SELECT te.id_estilo, te.nombre_estilo 
            FROM estilos_usuarios eu 
            JOIN tipos_estilos te ON eu.id_estilo = te.id_estilo 
            WHERE eu.id_usuario = $id_usuario";
    
    $resultado = mysqli_query($conn, $sql);
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $estilos[] = $fila;
    }
    
    return $estilos;
}

// Función para obtener suscripciones de un usuario
function obtenerSuscripcionUsuario($id_usuario) {
    global $conn;
    
    $sql = "SELECT * FROM suscripciones WHERE id_usuario = $id_usuario ORDER BY fecha_ini DESC LIMIT 1";
    $resultado = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($resultado) > 0) {
        return mysqli_fetch_assoc($resultado);
    } else {
        return false;
    }
}

// Función para obtener cajas temáticas recomendadas
function obtenerCajasRecomendadas($id_usuario) {
    global $conn;
    
    // Obtener nivel de experiencia del usuario
    $sql_exp = "SELECT experiencia FROM usuarios WHERE id_usuario = $id_usuario";
    $result_exp = mysqli_query($conn, $sql_exp);
    $experiencia = mysqli_fetch_assoc($result_exp)['experiencia'];
    
    // Obtener estilos del usuario
    $estilos = obtenerEstilosUsuario($id_usuario);
    $temas = [];
    
    foreach ($estilos as $estilo) {
        $temas[] = "'" . $estilo['nombre_estilo'] . "'";
    }
    
    $temas_str = implode(',', $temas);
    
    // Obtener cajas recomendadas por tema y experiencia
    $sql = "SELECT * FROM cajas_tematicas WHERE tema IN ($temas_str) AND experiencia = '$experiencia' LIMIT 4";
    $resultado = mysqli_query($conn, $sql);
    
    $cajas = [];
    while ($caja = mysqli_fetch_assoc($resultado)) {
        $cajas[] = $caja;
    }
    
    return $cajas;
}

// Función para obtener productos por caja
function obtenerProductosPorCaja($id_caja) {
    global $conn;
    
    $sql = "SELECT p.*, pc.cantidad 
            FROM productos p 
            JOIN productos_cajas pc ON p.id_producto = pc.id_producto 
            WHERE pc.id_caja = $id_caja";
    
    $resultado = mysqli_query($conn, $sql);
    
    $productos = [];
    while ($producto = mysqli_fetch_assoc($resultado)) {
        $productos[] = $producto;
    }
    
    return $productos;
}

// Función para obtener carrito activo
function obtenerCarritoActivo($id_usuario) {
    global $conn;
    
    $sql = "SELECT * FROM carritos_compras WHERE id_usuario = $id_usuario AND status = 'Activo' LIMIT 1";
    $resultado = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($resultado) > 0) {
        return mysqli_fetch_assoc($resultado);
    } else {
        // Crear nuevo carrito
        $fecha = date('Y-m-d');
        $sql_nuevo = "INSERT INTO carritos_compras (id_usuario, fecha_creacion, status) VALUES ($id_usuario, '$fecha', 'Activo')";
        mysqli_query($conn, $sql_nuevo);
        
        return [
            'id_carrito' => mysqli_insert_id($conn),
            'id_usuario' => $id_usuario,
            'fecha_creacion' => $fecha,
            'status' => 'Activo'
        ];
    }
}

// Función para obtener productos en carrito
function obtenerProductosCarrito($id_carrito) {
    global $conn;
    
    $sql = "SELECT p.* 
            FROM productos p 
            JOIN items_carritos ic ON p.id_item = ic.id_item 
            WHERE ic.id_carrito = $id_carrito";
    
    $resultado = mysqli_query($conn, $sql);
    
    $productos = [];
    while ($producto = mysqli_fetch_assoc($resultado)) {
        $productos[] = $producto;
    }
    
    return $productos;
}
?>