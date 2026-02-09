

<?php
header('Content-Type: application/json; charset=utf-8');

switch($_SERVER['REQUEST_METHOD']){
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




function _post($data)
{
    if (!isset($data) || !is_array($data)) {
        die("Error: Sin datos.");
    }

    include("conexionProyecto.php");

    try {
        // Obtener todas las pinturas desde la base de datos
        $pinturas = ObtenerPinturas($mysqli);

        // Recorrer los modulos por referencia para poder modificarlos
        foreach ($data as &$modulo) {
            // Recorrer los submodulos por referencia
            foreach ($modulo["submodulos"] as &$submodulo) {
                // Obtener correspondencias para el submodulo actual
                $datosClave = ObtenerTablaCorrespondencias(
                    $mysqli, 
                    $modulo['pregunta'], 
                    $submodulo['Nombre']
                );

                // Agregar pinturas que coincidan con las correspondencias
                $submodulo['pinturas'] = AgregarPinturas($pinturas, $datosClave);
            }
        }

    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    } finally {
        $mysqli->close();
    }

    // Devolver el JSON modificado
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


/**
 * Función para obtener las correspondencias de una pregunta y opción.
 */
function ObtenerTablaCorrespondencias($mysqli, $pregunta_id, $option_value) {
    $respuesta = [];

    if ($stmt = $mysqli->prepare("SELECT atributo_objetivo, valor_relacionado, peso 
                                  FROM Correspondencias 
                                  WHERE pregunta_id = ? AND opcion_value = ?")) {
        $stmt->bind_param("is", $pregunta_id, $option_value);
        $stmt->execute();
        $stmt->bind_result($atributo_objetivo, $valor_relacionado, $peso);

        while ($stmt->fetch()) {
            $respuesta[] = [
                "atributo_objetivo" => $atributo_objetivo,
                "valor_relacionado" => $valor_relacionado,
                "peso" => $peso
            ];
        }

        $stmt->close();
    }

    return $respuesta;
}
/**
 * Funcion para obtener las pinturas disponibles de la base de datos
 */
function ObtenerPinturas($mysqli){
    $respuesta = [];
    if($stmt =  $mysqli->prepare( "SELECT id, superficie, acabado, eficiencia, 
                                   gama_color, estilo_decorativo, imagen, nombre,
                                    color, descripcion, precio, stock FROM Pinturas"))
            {
                $stmt->execute();
            }


           if(isset($stmt))
        {
            
            $stmt->bind_result($id,$superficie,$acabado,$eficiencia, $gama_color, $estilo_decorativo,
            $imagen, $nombre, $color, $descripcion, $precio, $stock);
            while ($stmt->fetch()) {

                 $respuesta[] = [
                     'id' =>$id,
                    'superficie' =>$superficie,
                    'acabado' => $acabado,
                    'eficiencia' => $eficiencia,
                    'gama_color' => $gama_color,
                    'estilo_decorativo' => $estilo_decorativo,
                    'imagen' => $imagen,
                    'nombre' => $nombre,
                    'color' => $color,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'stock' => $stock
                  ];
                
            }

            $stmt->free_result();
            $stmt->close();
        }

        return $respuesta;
}


/**
 * Funcion para ordenar las recomendaciones
 * de la mas acertada a la menos acertada
 * en base del peso de sus pesos
 */
function ordenarPorPesoDesc(&$pesos) {
    usort($pesos, function($a, $b) {
        return $b['peso'] <=> $a['peso']; 
    });
}

/**
 * Funcion que asigna a cada pintura 
 * a su respectivo modulo
 */
function AgregarPinturas($pinturas, $coincidencias){
   $grupo = [];

foreach ($pinturas as $fila1) {

    $peso = 0;

    foreach ($coincidencias as $fila2) {

           $valor1 = normalizar($fila1[$fila2['atributo_objetivo']]);
           $valor2 = normalizar($fila2['valor_relacionado']); 
             if (strpos($valor1, $valor2) !== false) {
                $peso += $fila2['peso']; 
                }
}
    
    if($peso > 0){
     $grupo[] = [   
                    'id' => $fila1['id'],
                    'imagen' => $fila1['imagen'],  
                    'nombre' => $fila1['nombre'],  
                    'color' => $fila1['color'],  
                    'descripcion' => $fila1['descripcion'],  
                    'precio' => $fila1['precio'],  
                    'stock' => $fila1['stock'],
                     "peso" => $peso
            ];
        }

}
  if(empty($grupo)) return [];
  ordenarPorPesoDesc($grupo);
  return $grupo;
}


//Funcion que elimina acentos
//y convierte a minusculas
//contenido de texto
function normalizar($texto) {
    // Convertir a minusculas
    $texto = strtolower($texto);

    // Quitar acentos 
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

    return $texto;
}



?>
