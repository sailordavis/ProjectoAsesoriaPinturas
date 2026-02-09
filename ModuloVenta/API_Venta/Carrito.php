<?php

header('Content-Type: application/json; charset=utf-8');

switch($_SERVER['REQUEST_METHOD']){
    case 'PUT':       
        $postBody = file_get_contents("php://input");
        $data = json_decode($postBody,true);
        _put($data);
        break;
    case 'DELETE':
        $postBody = file_get_contents("php://input");
        $data = json_decode($postBody,true);
        _delete($data);
        break;
    case 'GET':
        _get();
        break;
    case 'POST':
        $postBody = file_get_contents("php://input");
        $data = json_decode($postBody,true);
        _post($data);
        break;
    default:
        header('HTTP/1.1 405 Method not allowed');
        header('Allow: GET, PUT, DELETE');
        break;
}


function _get()
{
    include("conexionVenta.php");
    $stmt = null;
    $respuesta = array();           
    try
    {
        if(isset($_GET['idCarrito']))
        {
            if($stmt =  $mysqli->prepare("SELECT 
    p.id as id,
    p.nombre as nombre,
    p.color as color,
    p.descripcion as descripcion,
    p.imagen as imagen,
    cp.cantidad as cantidad,
    p.stock as stock,
    (p.precio * cp.cantidad) AS total
FROM 
    CarritoPintura cp
JOIN 
    Pinturas p ON cp.pintura_id = p.id
WHERE 
    cp.carrito_id = ?;

            "))
            {
                $stmt->bind_param("i",$_GET['idCarrito']);
                $stmt->execute();
            }

        }
       


        if(isset($stmt))
        {
            
            $stmt->bind_result($id,$nombre,$color,$descripcion,$imagen,$cantidad,$stock,$total);
            while ($stmt->fetch()) {
                 $respuesta[] = [
                    'id' => $id,
                    'nombre' => $nombre,
                    'color' => $color,
                    'descripcion' => $descripcion,
                    'imagen' => $imagen,
                    'cantidad' => $cantidad,
                    'stock' => $stock,
                    'total' => $total
                  ];
                
               
            }

            // $stmt->free_result();
            // $stmt->close();
        }


    }
    catch (Exception $e) {
        echo "Exception ::";
        echo $e->getMessage();

    }
    finally {
        try {
            $mysqli->close();
            
        } catch (Exception $e) {
            echo '0';
        }
    }
    
    echo json_encode($respuesta);
}

function _post($datos)
{
    include("conexionVenta.php");
    $respuesta = ["success" => false];

    if( isset($datos['usuario_id']) && isset($datos['pintura_id']) && isset($datos['cantidad']) ) {

        $idUsuario = $datos['usuario_id'];
        $idPintura = $datos['pintura_id'];
        $cantidad  = $datos['cantidad'];

        try {
            //Paso 1. Buscar Carrito o crear Carrito
            $idCarrito = BuscarCarrito($mysqli, $idUsuario);

            if (!$idCarrito) {
                $idCarrito = CrearCarrito($mysqli, $idUsuario);
            }

            //Paso 2. Insertar o actualizar pintura
            PinturasSeleccionadas($mysqli, $idCarrito, $idPintura, $cantidad);

            $respuesta = [
                "success" => true,
                "carrito_id" => $idCarrito
            ];

        } catch (Exception $e) {
            $respuesta["error"] = $e->getMessage();
        }
        finally {
            $mysqli->close();
        }
    }
    else {
        die("Error: Sin datos.");
    }

    echo json_encode($respuesta);
}



function _delete($datos){
    include("conexionVenta.php");
     $respuesta = ["success" => false];
    if( isset($datos['idPintura']) && isset($datos['idCarrito']) ){

        try{
            if($stmt = $mysqli->prepare("DELETE FROM CarritoPintura WHERE carrito_id=? and pintura_id=?;"))
            {
                $stmt->bind_param("ii",$datos['idCarrito'],$datos['idPintura']);
                $stmt->execute();
            }
            else
            {
                echo "No disponible ::";
            }

            $respuesta = [
                "success" => true,
            ];
        }catch (Exception $e) {
            //echo "Exception ::";
            //echo $e->getMessage();
            
        }
        finally {
            try {
                $mysqli->close();    
            } catch (Exception $e) {
                echo '0';
            }
        }
    }
    else
    {
        //echo "Sin datos";
         die("Error: Sin datos.");
    }
    echo json_encode($respuesta);
}


function _put($datos){
     include("conexionVenta.php");
     $respuesta = ["success" => false];
    if( isset($datos['idCarrito']) && isset($datos['idPintura']) && isset($datos['cantidad']) ){

        try{
            if($stmt = $mysqli->prepare("UPDATE CarritoPintura SET  cantidad=? WHERE  carrito_id=? AND  pintura_id=?;"))
            {
                $stmt->bind_param("iii",$datos['cantidad'],$datos['idCarrito'],$datos['idPintura']);
                $stmt->execute();

            }
            else
            {
                echo "No disponible ::";
            }

            $respuesta = [
                "success" => true,
            ];
        }catch (Exception $e) {
            //echo "Exception ::";
            //echo $e->getMessage();
            
        }
        finally {
            try {
                $mysqli->close();    
            } catch (Exception $e) {
                echo '0';
            }
        }
    }
    else
    {
        //echo "Sin datos";
         die("Error: Sin datos.");
    }
    echo json_encode($respuesta);
}

//Funciones auxiliares

/**
 * Funcion para comprobar si el usuario ya cuenta con un carrito
 */
function BuscarCarrito($mysqli, $idUsuario) {
    $carritoId = null;

    if ($stmt = $mysqli->prepare("SELECT id FROM Carrito WHERE usuario_id = ? LIMIT 1;")) {
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->bind_result($id);

        if ($stmt->fetch()) {
            $carritoId = $id;   
        }

        $stmt->close();
    }

    return $carritoId; // devuelve null si no existe carrito
}

/**
 * Funcion para crear un carrito en caso de que el usuario no cuente con uno
 */
function CrearCarrito($mysqli, $idUsuario) {
    if ($stmt = $mysqli->prepare("INSERT INTO Carrito (usuario_id) VALUES (?);")) {

        $stmt->bind_param("i", $idUsuario);

        if ($stmt->execute()) {
            $id = $mysqli->insert_id;
            $stmt->close();
            return $id;
        }

        $stmt->close();
    }

    return null;
}

/**
 * Funcion que crea o actualiza sentencias de la Tabla CarritoPintura
 */
function PinturasSeleccionadas($mysqli, $idCarrito, $idPintura, $cantidad) {

    if (SeEscogioPinturaAntes($mysqli, $idCarrito, $idPintura)) {


        if ($stmt = $mysqli->prepare("UPDATE CarritoPintura cp
         JOIN Pinturas p ON cp.pintura_id = p.id
         SET cp.cantidad = cp.cantidad + ?
         WHERE cp.carrito_id = ?
         AND cp.pintura_id = ?
         AND (cp.cantidad + ?) <= p.stock;
        ")) {
            $stmt->bind_param("iiii", $cantidad, $idCarrito, $idPintura,$cantidad);
            $stmt->execute();
            $stmt->close();
        }

    } else {

        if ($stmt = $mysqli->prepare("INSERT INTO CarritoPintura (carrito_id,pintura_id,cantidad) VALUES(?,?,?);")) {
            $stmt->bind_param("iii", $idCarrito, $idPintura, $cantidad);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Funcion que comprueba si la pintura seleccionada ya pertenecia  al carrito del usuario
 */
function SeEscogioPinturaAntes($mysqli, $idCarrito, $idPintura) {

    if ($stmt = $mysqli->prepare("SELECT 1 FROM CarritoPintura WHERE carrito_id=? AND pintura_id=? LIMIT 1;")) {
        $stmt->bind_param("ii", $idCarrito, $idPintura);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows > 0;
    }

    return false;
}




?>