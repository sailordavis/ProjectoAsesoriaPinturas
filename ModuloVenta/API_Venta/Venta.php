<?php

header('Content-Type: application/json; charset=utf-8');

switch($_SERVER['REQUEST_METHOD']){
    case 'PUT':       
        $postBody = file_get_contents("php://input");
        $data = json_decode($postBody,true);
        _put($data);
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
   
}

function _post($datos)
{
     include("conexionVenta.php");
    $respuesta = ["success" => false];
    if( isset($datos['usuario_id'])  && isset($datos['metodo_pago'])  && isset($datos['estado']) ){

    $idUsuario =$datos['usuario_id'];  
    $metodoPago = $datos['metodo_pago'];
    $estado = $datos['estado'];

        try{
            //Paso 1. Crear el recibo de la venta
             $idRecibo = CrearRecibo($mysqli,$idUsuario,$metodoPago,$estado);
            //Paso 2. Obtener el Carrito del usuario que quiere realizar la operacion de compra
            $idCarrito = BuscarCarrito($mysqli, $idUsuario);
            //Paso 3. Agregar el producto que se va a comprar al recibo
            //Tambien se vaciara el carrito y actualizara el stock de la Pintura comprada
            Producto_A_Comprar($mysqli,$idCarrito,$idRecibo);

              $respuesta = [
                "success" => true,
            ];
        }catch (Exception $e) {
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
    }
    else
    {
        //echo "Sin datos";
         die("Error: Sin datos.");
    }
    echo json_encode($respuesta);
}


function _delete($datos){
   
}


function _put($datos){
  
}

function CrearRecibo($mysqli,$idUsuario,$metodoPago,$estado){
       $total = 0;

        if ($stmt = $mysqli->prepare("INSERT INTO Ventas (usuario_id,total,metodo_pago,estado) VALUES(?,?,?,?);")) {

        $stmt->bind_param("idss",$idUsuario,$total,$metodoPago,$estado);

        if ($stmt->execute()) {
            $id = $mysqli->insert_id;
            $stmt->close();
            return $id;
        }

        $stmt->close();
    }

    return null;
            
}

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

function Producto_A_Comprar($mysqli, $idCarrito, $idRecibo)
{
    $productos = [];

    // 1. Obtener productos del carrito
    if ($stmt = $mysqli->prepare("
        SELECT 
            p.id AS pintura_id, 
            cp.cantidad AS cantidad, 
            (p.precio * cp.cantidad) AS subtotal
        FROM CarritoPintura cp
        JOIN Pinturas p ON cp.pintura_id = p.id
        WHERE cp.carrito_id = ?
    ")) {
        $stmt->bind_param("i", $idCarrito);
        $stmt->execute();
        $stmt->bind_result($idPintura, $cantidad, $subtotal);

        // Guardamos los resultados en un array
        while ($stmt->fetch()) {
            $productos[] = [
                "idPintura" => $idPintura,
                "cantidad" => $cantidad,
                "subtotal" => $subtotal
            ];
        }

        $stmt->free_result();
        $stmt->close();
    }

    // 2. Ahora sí recorrerlos y hacer los INSERT/DELETE/UPDATE
    foreach ($productos as $prod) {

        AgregarProducto_A_Recibo(
            $mysqli,
            $idRecibo,
            $prod["idPintura"],
            $prod["cantidad"],
            $prod["subtotal"]
        );

        VaciarCarrito($mysqli, $idCarrito, $prod["idPintura"]);

        DecrementarStock($mysqli, $prod["idPintura"], $prod["cantidad"]);
    }
}



function VaciarCarrito($mysqli,$idCarrito,$idPintura){
    if($stmt = $mysqli->prepare("DELETE FROM CarritoPintura WHERE carrito_id=? and pintura_id=?;"))
            {
                $stmt->bind_param("ii",$idCarrito,$idPintura);
                $stmt->execute();
                $stmt->close();
            }
}

function AgregarProducto_A_Recibo($mysqli, $idRecibo, $idPintura, $cantidad, $subtotal)
{
    if ($stmt = $mysqli->prepare(" INSERT INTO detalle_venta (venta_id, pintura_id, cantidad, subtotal)
        VALUES (?, ?, ?, ?)
    ")) {
        $stmt->bind_param("iiid", $idRecibo, $idPintura, $cantidad, $subtotal);
        $stmt->execute();
        $stmt->close();
    }
}



function DecrementarStock($mysqli,$idPintura,$cantidad){ 

   if ($stmt = $mysqli->prepare(" UPDATE Pinturas SET stock = stock - ? WHERE id = ?;")) {
        $stmt->bind_param("ii", $cantidad, $idPintura);
        $stmt->execute();
        $stmt->close();
    }
}


?>