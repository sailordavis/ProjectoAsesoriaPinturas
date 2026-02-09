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
    include("conexion.php");
    $stmt = null;
    $respuesta = array();
    try
    {
        if(isset($_GET['tela']))
        {
            if($stmt =  $mysqli->prepare("SELECT id, nombre, pais FROM telas WHERE id = ?;"))
            {
                $stmt->bind_param("i",$_GET['tela']);
                $stmt->execute();
            }

        }
        else
        {
            if($stmt =  $mysqli->prepare( "SELECT id, nombre, pais FROM telas"))
            {
                $stmt->execute();
            }
        }


        if(isset($stmt))
        {
            
            $stmt->bind_result($id,$nombre,$pais);
            while ($stmt->fetch()) {
                
                $tela = (object) array(
                    'id' =>$id,
                    'nombre' =>$nombre,
                    'pais' => $pais
                );
                array_push($respuesta, $tela);   
            }

            $stmt->free_result();
            $stmt->close();
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
    $respuesta = ["success" => false, "mensaje" => ""];

    // Validar campos necesarios
    $camposNecesarios = [
        "id_usuario", "calle", "numero",
        "colonia", "ciudad", "estado",
        "codigo_postal", "telefono"
    ];

    foreach ($camposNecesarios as $campo) {
        if (!isset($datos[$campo]) || empty($datos[$campo])) {
            die("Error: Falta el campo '$campo'.");
        }
    }

    try {
        $query = "INSERT INTO Direcciones 
            (id_usuario, calle, numero, colonia, ciudad, estado, codigo_postal, telefono)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($query)) 
        {
            $stmt->bind_param(
                "isssssss",
                $datos['id_usuario'],
                $datos['calle'],
                $datos['numero'],
                $datos['colonia'],
                $datos['ciudad'],
                $datos['estado'],
                $datos['codigo_postal'],
                $datos['telefono']
            );

            if ($stmt->execute()) {

                $respuesta["success"] = true;
                $respuesta["mensaje"] = "Dirección guardada correctamente.";

                
                $respuesta["id_direccion"] = $mysqli->insert_id;

            } else {
                $respuesta["mensaje"] = "Error al ejecutar INSERT.";
            }
        } else {
            $respuesta["mensaje"] = "No se pudo preparar el INSERT.";
        }

    } catch (Exception $e) {
        $respuesta["mensaje"] = "Excepción: " . $e->getMessage();
    } finally {
        try { $mysqli->close(); } catch (Exception $e) {}
    }

    echo json_encode($respuesta);
}




function _delete($datos){
    $respuesta=1;
    if( isset($datos['id']) ){
        include("conexion.php");

        try{
            if($stmt = $mysqli->prepare("DELETE FROM telas WHERE id=?;"))
            {
                $stmt->bind_param("i",$datos['id']);
                $stmt->execute();
            }
            else
            {
                echo "No disponible ::";
            }
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

    if (!isset($datos['id']) || empty($datos['id'])) {
        die(json_encode(["ok" => false, "mensaje" => "Falta el ID de la dirección."]));
    }

    $id = $datos['id'];
    unset($datos['id']); 

    // Si no hay otros campos, error
    if (empty($datos)) {
        die(json_encode(["ok" => false, "mensaje" => "No hay campos para actualizar."]));
    }

    // Campos permitidos
    $camposValidos = ['calle','numero','colonia','ciudad','estado','codigo_postal','telefono'];

    $set = [];
    $valores = [];

    foreach ($datos as $campo => $valor) {
        if (in_array($campo, $camposValidos)) {
            $set[] = "$campo = ?";
            $valores[] = $valor;
        }
    }

    if (empty($set)) {
        die(json_encode(["ok" => false, "mensaje" => "No hay campos válidos para actualizar."]));
    }

    // SQL final
    $sql = "UPDATE direcciones SET " . implode(", ", $set) . " WHERE id = ?";

    // Agregar id al final
    $valores[] = $id;

    // Tipos: todo string excepto el último (id = int)
    $tipos = str_repeat("s", count($valores) - 1) . "i";

    try {
        $stmt = $mysqli->prepare($sql);

        if (!$stmt) {
            die(json_encode(["ok" => false, "mensaje" => "Error al preparar statement"]));
        }

        $stmt->bind_param($tipos, ...$valores);

        if ($stmt->execute()) {
            echo json_encode(["ok" => true, "mensaje" => "Dirección actualizada"]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => "Error al ejecutar el UPDATE"]);
        }

    } catch (Exception $e) {
        echo json_encode(["ok" => false, "mensaje" => $e->getMessage()]);
    } finally {
        $mysqli->close();
    }
}



?>