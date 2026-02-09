

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
    
    if( isset($data["datos"]) && is_array($data["datos"])){
        include("conexionProyecto.php");
         $stmt = null;
         $recomendaciones = array();
         $datosClave = array();
         $pinturas = array();
         $respuestas = $data["datos"];
         
        try{
            //Primer Paso: Obtencion de datos que ayuden a identificar recomendaciones  
            foreach ($respuestas as $r) {

                if (!isset($r["id"]) || !isset($r["opcion"])) {
                echo json_encode(["success" => false, "error" => "Faltan campos"]);
                return;
                 }

            $pregunta_id = $r["id"];
            $opcion_value = $r["opcion"];

            // Llamar a la funcion para obtener los datos clave de la tabla correspondecia 
            //Atributo Objetivo, valor relacionado y peso 
            $resultado = ObtenerTablaCorrespondencias($mysqli, $pregunta_id, $opcion_value);

            // Obtener datos en base de las respuestas de los usuarios
            $datosClave = array_merge($datosClave, $resultado);
         }
          //Segundo Paso: Obtener Pinturas de mi base de Datos 
            $pinturas = ObtenerPinturas($mysqli);

         //Tercer Paso: Obtener Recomendaciones
            $recomendaciones = ObtenerRecomendaciones($pinturas,$datosClave);
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
    echo json_encode($recomendaciones);
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
 * Funcion para obtener recomendaciones
 * en base de las respuestas del usuario
 */
function ObtenerRecomendaciones($pinturas, $coincidencias ){
    $pesos = [];
     $pesos = AsignarPesos($pinturas, $coincidencias);
     ordenarPorPesoDesc($pesos);
     $pesos = array_slice($pesos, 0, 4);
     return $pesos;
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
 * Funcion que asigna peso y valor a las pinturas
 * disponibles en base sobre que tan cercano 
 * es a las respuestas de los usuarios
 */
function AsignarPesos($pinturas, $coincidencias){
   $pesos = [];

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
    // Guardar resultado
     $pesos[] = [
                    "id" => $fila1['id'],
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
  return $pesos;
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
