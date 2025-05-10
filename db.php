<?php
    $conn = mysqli_connect(
        'localhost', # servidor de la base de datos
        'root', # usuario de la base de datos
        '', # contraseña de la base de datos
        'crafted' # nombre de la base de datos
    );

    /*if(isset($conn)){
        echo "Conexión exitosa a la base de datos";
    } else{
        echo "Error de conexión a la base de datos: " . mysqli_connect_error();
    }*/
?>