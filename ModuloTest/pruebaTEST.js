
let preguntas = [];  
let datosRecopilados = [];
let datosGuia = [];
let Pinturas = [];
let idUsuario=2;
let pinturasCarrito = [];
let idCarrito = null;

fetch("dato.json")
  .then(res => res.json())
  .then(data => {
     preguntas = data;
    mostrarPregunta(1);
  })
  .catch(err => console.error("Error al cargar JSON:", err));



// Funcion para mostrar una pregunta segun su id
//Y realizar el cambio de pregunta hacia atras o adelante
function mostrarPregunta(idPregunta) {
 
  const pregunta = preguntas.find(p => p.id === idPregunta);
  if (!pregunta) return;
  // Insertar el HTML en el contenedor
  $("#Preguntas").html(pregunta.contenido);
  
 //En caso de que se presione el boton de regreso 
 //Se regresara a la pregunta anterior (La pregunta 1 no cuenta con boton de regreso)
document.addEventListener("click", function(e) {
  if (e.target.closest("#botonRegreso")) {
    EliminarDatos(pregunta.anterior);
    mostrarPregunta(pregunta.anterior); 
  }
});
    
  // Volver a conectar los eventos de selección
  document.querySelectorAll('input[type="radio"]').forEach(input => {
    input.addEventListener('change', function() {
      const valorSeleccionado = this.value;
      RecopilarDatos(pregunta.id,valorSeleccionado);//Recopilar datos de preguntas claves
      // Buscar en las opciones cual es la siguiente pregunta
      const opcion = pregunta.opciones.find(o => o.value === valorSeleccionado);
      if ((opcion && opcion.siguiente) && opcion.siguiente !== 117) {
        AsignarPadre(opcion.siguiente,pregunta.id);//Conectar la pregunta con la pregunta anterior
        mostrarPregunta(opcion.siguiente);  // Mostrar la siguiente pregunta
         
      } else if(opcion.siguiente === 117) { //Se han contestado todas las preguntas
        $("#aviso_final").modal('show'); //Mostrar Aviso de finalizacion
      }else{
        alert("No se identifico la seleccion");
      }
    });
  });

}
//Funcion para agregar un nodo padre al nodo actual
//Que el usuario esta interactuando 
function AsignarPadre(idActual,idAnterior){
  const preguntaActual = preguntas.find(p => p.id === idActual);
  if (!preguntaActual) return;
  preguntaActual.anterior = idAnterior;
}

//Funcion que recopila datos de preguntas claves
//Datos claves para la recomendacion de pinturas
function RecopilarDatos(idpregunta,option_value){
   switch(idpregunta){
     case 1:
     case 4:
     case 5:
     case 6: break;
     default:
     datosRecopilados.push({"id":idpregunta,"opcion":option_value});
     break;
   }
}

//Funcion que elimina datos recopilados si el usuario 
//decide retroceder a la pregunta anterior 
function EliminarDatos(idpregunta){
   switch(idpregunta){
     case 2:
     case 3:
    datosRecopilados.pop();break;
    default: break;
   }
}

//Funcion que envia los datos del usuario a otro Archivo
//por la API
//en caso de que el usuario de por finalizado el test
function MostrarResultados(){
   $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloTest/API_Test/pruebaTEST_API.php',
                type: 'POST',
                data: JSON.stringify({"datos":datosRecopilados}),
                contentType: 'application/json',
                success: function(data) {
                    console.log('Registro obtenido:', data);
                    $("#aviso_final").modal('hide'); //Cierra el modal
                     datosGuia = data;
                     MostrarRecomendaciones();
                     LlamarAPI_Recomendaciones();
                },
                error: function(error) {
                    console.error('Error al crear el registro:', error);
                }
            });
}

//Funcion que se activa al presionar el boton continuar
//Elimina el ultimo dato recopilado
function EliminarUltimoDato(){
  datosRecopilados.pop();
}

//Funcion que muestran las recomendaciones
//en base de las respuestas de los usuarios
function MostrarRecomendaciones(){
  let resultado = ` 
     <div class="container mt-4">
    <div class="row g-4">`;
    datosGuia.forEach(element => {
      Pinturas.push(element.id); //Se guardan en un array especial los id's de las pinturas recomendadas
       resultado += ` 
         <div class="col-md-3">
            <div class="card h-100 shadow"  data-stock="${element.stock}" data-id="${element.id}">
                <img src="${element.imagen}" class="card-img-top" alt="Pintura Recomendada">
                <div class="card-body">
                    <h5 class="card-title">${element.nombre}</h5>
                    <p class="card-text">Color: ${element.color}</p>
                     <p class="card-text">Limite de Compra: ${element.stock}</p>
                    <p class="card-text">${element.descripcion}</p>
                    <p class="card-text">$ ${element.precio}</p>
                    <!-- CONTADOR + BOTON -->
                    <div class="d-flex align-items-center gap-3 mt-3">

                        <!-- Contador -->
                        <div class="d-flex border rounded overflow-hidden" style="width: 130px;">
                            <button class="btn py-2 flex-fill btn-menos"
                                    style="border:none; color:#ff7a00; font-size:20px;">−</button>

                            <div class="cantidad d-flex justify-content-center align-items-center"
                                 style="width:50px; font-size:18px; font-weight:600;">
                                1
                            </div>

                            <button class="btn py-2 flex-fill btn-mas"
                                    style="border:none; color:#ff7a00; font-size:20px;">+</button>
                        </div>

                        <!-- Botón Agregar -->
                        <button class="btn btn-agregar"
                                style="border: 2px solid #c40000; color:#c40000; padding:8px 20px; border-radius:8px;"
                                 onclick = "AgregarAlCarrito(${element.id},this)">
                            Agregar
                        </button>

                    </div>

                </div>
            </div>
        </div>
       `;

    });
    resultado += `   </div>
                      </div>
                      
                      `;
      $("#Preguntas").html(resultado);
      //ActivarBotonCarrito
      const btn = document.getElementById("btnCarrito");
      btn.style.display = "inline-block"; 

       // Registrar nuevamente los eventos del contador
        ActivarEventosContadores();
}



//Funcion que activa los eventos de la interfaz
//que muestra recomendaciones
function ActivarEventosContadores() {

    $(".card").each(function () {

        const card = $(this);
        const stock = parseInt(card.data("stock"), 10);

        const cantidadDiv = card.find(".cantidad");
        const btnMas = card.find(".btn-mas");
        const btnMenos = card.find(".btn-menos");

        btnMas.off().click(() => {
            let valor = parseInt(cantidadDiv.text(), 10);

            if (valor < stock) {
                cantidadDiv.text(valor + 1);
            } 
        });

        btnMenos.off().click(() => {
            let valor = parseInt(cantidadDiv.text(), 10);
            if (valor > 1) {
                cantidadDiv.text(valor - 1);
            }
        });

    });
}

//Funcion que guarda los resultados del test de los usuarios
//en la base de datos
function LlamarAPI_Recomendaciones(){
  if (!idUsuario) {
        alert("Debes iniciar sesion para agregar productos al carrito.");
        return;
    }
   let datos = {
        usuario_id: idUsuario,
        Pinturas: Pinturas
    };
    $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloTest/API_Test/Recomendaciones.php',
                type: 'POST',
                data: JSON.stringify(datos),
                contentType: 'application/json',
                success: function(response) {
                   console.log("Accion ejecutada correctamemte");
                },
                error: function(error) {
                    console.error('Error al crear el registro:', error);
                }
            });

}


//Funciones que establecen una conexion entre el  modulo Test y el modulo de Ventas

/**
 * Agrega un producto al carrito enviando los datos a la API
 * @param {number} idPintura - ID de la pintura seleccionada
 * @param {HTMLElement} boton - El botón que se presionó
 */
function AgregarAlCarrito(idPintura, boton) {
    
  //Validacion para comprobar que ya se inicio sesion 
    if (!idUsuario) {
        alert("Debes iniciar sesion para agregar productos al carrito.");
        return;
    }

    
    //Se obtiene la cantidad seleccionada
    let cantidadElemento = boton .closest(".d-flex.align-items-center") .querySelector(".cantidad"); 
    let cantidad = parseInt(cantidadElemento.innerText);
    
    //Para comprobar que se obtuvo los datos precisos
    console.log("Pintura:", idPintura); 
    console.log("Cantidad seleccionada:", cantidad);


    let datos = {
        usuario_id: idUsuario,
        pintura_id: idPintura,
        cantidad: cantidad
    };

     LlamarAPI_Ventas(datos);
}

/**
 * Funcion que invoca la API que se comunicara con el archivo PHP y le
 * Asignara la tarea de modificar las tablas Carrito y CarritoPintura 
 * con los datos recibidos
 * @param {*} datos objeto que se convertira en formato json con los datos que mandara la API
 *                  al archivo PHP
 */
function LlamarAPI_Ventas(datos){
   $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Carrito.php',
                type: 'POST',
                data: JSON.stringify(datos),
                contentType: 'application/json',
                success: function(response) {
                   console.log("Accion ejecutada correctamemte");
                    idCarrito = response.carrito_id;
                    ObtenerSeleccionados();
                },
                error: function(error) {
                    console.error('Error al crear el registro:', error);
                }
            });
}

function ObtenerSeleccionados(){
     $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Carrito.php?idCarrito='+idCarrito, 
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Registros obtenidos:', data);
                    pinturasCarrito = data;
                    MostrarSeleccionados();
                },
                error: function(error) {
                    console.error('Error al obtener los registros:', error);
                }
            });
}



function MostrarSeleccionados(){
   let total = 0;
   let resultado = ``;
     pinturasCarrito.forEach(element => {
       total += element.total;
       resultado +=`
       <div class="producto" data-id="${element.id}" data-stock="${element.stock}">
       <div class="producto-info">
       <img src="${element.imagen}" style = "width: 70px; height: 70px; "  alt="Pintura Agregada"> <br> <br>
                <h4>${element.nombre}</h4>
                <p>Color: ${element.color}</p>
                <p>Limite de Compra: ${element.stock} </p>
                <p>${element.descripcion}</p>
                <p>$ ${element.total}</p>
            </div>
            <!-- CONTADOR + BOTON -->
                    <div class="d-flex align-items-center gap-3 mt-3">
                        <!-- Contador -->
                        <div class="d-flex border rounded overflow-hidden" style="width: 130px;">
                            <button class="btn py-2 flex-fill btn-menos"
                                    style="border:none; color:#ff7a00; font-size:20px;">−</button>

                            <div class="cantidad d-flex justify-content-center align-items-center"
                                 style="width:50px; font-size:18px; font-weight:600;">
                                ${element.cantidad}
                            </div>

                            <button class="btn py-2 flex-fill btn-mas"
                                    style="border:none; color:#ff7a00; font-size:20px;">+</button>
                        </div>

                        <!-- Boton Eliminar -->
                        <button class="bi bi-trash3"
                                style="border: 2px solid #c40000ff; color:#c40000; padding:8px 20px; border-radius:8px;"
                                onclick = "EliminarSeleccion(${element.id})">
                        </button>

                    </div>
                   </div> 
            <hr>
       `;

     });
     resultado += `<h5>Total: $<span id="totalCarrito">${total}</span></h5> <br>
     <button id="btnFinalizarCompra" class="btn btn-success w-100">
             Finalizar Compra
            </button>`;
    $("#Seleccionadas").html(resultado);
    
    //Desactivar BotonFinalizar si el carrito esta vacio
    if(pinturasCarrito.length === 0){
      const btn = document.getElementById("btnFinalizarCompra");
      btn.style.display = "none"; 
    }else{
    //Activar Eventos del Boton btnFinalizarCompra
    document.getElementById("btnFinalizarCompra").addEventListener("click", function () {
    window.location.href = "../ModuloVenta/ProcesoCompra.php";});
    }

    // Registrar nuevamente los eventos del contador
    $(document).off("click", ".btn-mas, .btn-menos").on("click", ".btn-mas, .btn-menos", function () {

    // 1. Obtener el contenedor del producto
    const contenedorProducto = $(this).closest(".producto"); 
    const idPintura = contenedorProducto.data("id"); // <div class="producto" data-id="###">
    const stockPintura = contenedorProducto.data("stock"); //Limite de cantidad a vender

    // 2. Obtener el DIV donde esta la cantidad
    const cantidadDiv = contenedorProducto.find(".cantidad");
    let cantidad = parseInt(cantidadDiv.text(), 10);

    // 3. Modificar cantidad segun el boton presionado
    if ($(this).hasClass("btn-mas")) {
        if(cantidad < stockPintura){
            cantidad++;
        }
    } else if (cantidad > 1) {
        cantidad--;
    }

    // 4. Actualizar DOM
    cantidadDiv.text(cantidad);

    // 5. Llamar la función que actualiza en BD
    CambiarCantidad(idPintura,idCarrito,cantidad);
});


}


function CambiarCantidad(idPintura, idCarrito, cantidad){
    let data = {
        idPintura: idPintura,
        idCarrito: idCarrito,
        cantidad: cantidad
    };
    $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Carrito.php', 
                type: 'PUT',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(response) {
                    console.log('Registro actualizado:', response);
                    ObtenerSeleccionados(); // Refresca los productos del Carrito
                    
                },
                error: function(error) {
                    console.error('Error al actualizar el registro:', error);
                }
            });
}


function EliminarSeleccion(idPintura){
  let data = {
        idPintura: idPintura,
        idCarrito: idCarrito,
    };
     $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Carrito.php', 
                type: 'DELETE',
                 data: JSON.stringify(data), 
                contentType: 'application/json',
                success: function(response) {
                    console.log('Registro eliminado:', response);
                    ObtenerSeleccionados(); // Refresca los productos del Carrito
                },
                error: function(error) {
                    console.error('Error al eliminar el registro:', error);
                }
            });
}








