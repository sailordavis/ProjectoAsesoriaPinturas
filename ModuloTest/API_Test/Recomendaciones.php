<?php

header('Content-Type: application/json; charset=utf-8');

switch($_SERVER['REQUEST_METHOD']){
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
    include("conexionProyecto.php");
    $stmt = null;
    $respuesta = array();
    try
    {
        if(isset($_GET['usuario_id']))
        {
            if($stmt =  $mysqli->prepare("SELECT id, usuario_id  FROM Carrito WHERE usuario_id = ? LIMIT 1;"))
            {
                $stmt->bind_param("i",$_GET['usuario_id']);
                $stmt->execute();
            }

        }
       


        if(isset($stmt))
        {
            
            $stmt->bind_result($id,$usuario_id);
            while ($stmt->fetch()) {
                
                $carrito = (object) array(
                    'id' =>$id,
                    'usuario_id' =>$usuario_id
                );
                array_push($respuesta, $carrito);   
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
    include("conexionProyecto.php");
    $respuesta = ["success" => false];

    if( isset($datos['usuario_id']) && isset($datos['Pinturas']) && is_array($datos['Pinturas'])){
         $idUsuario = $datos['usuario_id'];
         $Pinturas = $datos['Pinturas'];

        try {
            // Crear resultado de test + recomendaciones
            $idResultados = CrearResultadoConRecomendaciones($mysqli, $idUsuario, $Pinturas);

            if ($idResultados !== null) {
                $respuesta["success"] = true;
                $respuesta["idResultados"] = $idResultados;
            } else {
                $respuesta["error"] = "No se pudo crear el resultado del test.";
            }

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

//Funciones auxiliares


/**
 * Crea un nuevo resultado de test y guarda sus pinturas recomendadas
 *
 * @param mysqli $mysqli Conexión a la base de datos
 * @param int $idUsuario ID del usuario que hizo el test
 * @param array $pinturas Array con IDs de pinturas recomendadas
 * @return int|null ID del resultado creado, o null si hubo error
 */
function CrearResultadoConRecomendaciones($mysqli, int $idUsuario, array $pinturas): ?int {
    // 1. Crear registro en resultadosTest
    $stmt = $mysqli->prepare("INSERT INTO resultadosTest (usuario_id) VALUES (?)");
    if (!$stmt) {
        error_log("Error en prepare resultadosTest: " . $mysqli->error);
        return null;
    }

    $stmt->bind_param("i", $idUsuario);
    if (!$stmt->execute()) {
        error_log("Error en execute resultadosTest: " . $stmt->error);
        $stmt->close();
        return null;
    }

    $resultadoId = $mysqli->insert_id;
    $stmt->close();

    // 2. Insertar pinturas recomendadas
    $stmt = $mysqli->prepare("INSERT INTO Recomendaciones (resultado_id, pintura_id) VALUES (?, ?)");
    if (!$stmt) {
        error_log("Error en prepare Recomendaciones: " . $mysqli->error);
        return null;
    }

    foreach ($pinturas as $pintura_id) {
        if (!$stmt->bind_param("ii", $resultadoId, $pintura_id) || !$stmt->execute()) {
            error_log("Error al insertar pintura_id $pintura_id: " . $stmt->error);
        }
    }

    $stmt->close();

    return $resultadoId;
}



?>