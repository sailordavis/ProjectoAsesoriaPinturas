<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceso de Compra</title>
     <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
   

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
    crossorigin="anonymous"></script>
</head>
<body>
<script>
    //Variables Globales (datos a mandar como json al archivo php Venta)
    let estado = null;
    let idUsuario = 1;
    let metodo_pago = null;
    //Variables Globales auxiliares
    let banderaEditar = false;
    let camposModificados = {};
</script>

<div class="container mt-5">

    <h2 class="mb-4">Proceso de Compra</h2>

    <!-- ==================== 1. MÉTODO DE ENTREGA ==================== -->
    <div class="card mb-4">
        <div class="card-body">
            <h4>Método de entrega</h4>

            <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="envio" id="recoger" value="recoger">
                <label class="form-check-label" for="recoger">Recoger en tienda</label>
            </div>

            <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="envio" id="domicilio" value="domicilio">
                <label class="form-check-label" for="domicilio">Enviar a domicilio</label>
            </div>
        </div>
    </div>
    

   <!-- ==================== 2. FORMULARIO DOMICILIO ==================== -->
<div id="formDomicilio" class="card mb-4" style="display:none;">
    <div class="card-body">
        <h4>Datos de envío</h4>

        <form id="datosEnvioForm">

            <div class="row mt-3">

                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <div class="input-group">
                        <input id="telefono" type="text" class="form-control" name="telefono" placeholder="Ej: 555-123-4567">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-6">
                    <label class="form-label">Calle</label>
                    <div class="input-group">
                        <input id="calle" type="text" class="form-control" name="calle" placeholder="Ej: Av. México">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Número</label>
                    <div class="input-group">
                        <input id="numero" type="text" class="form-control" name="numero" placeholder="Ej: 123">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-6">
                    <label class="form-label">Colonia</label>
                    <div class="input-group">
                        <input id="colonia" type="text" class="form-control" name="colonia" placeholder="Ej: Centro">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Código Postal</label>
                    <div class="input-group">
                        <input id="codigo_postal" type="text" class="form-control" name="codigo_postal" placeholder="Ej: 44100">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-6">
                    <label class="form-label">Ciudad</label>
                    <div class="input-group">
                        <input id="ciudad" type="text" class="form-control" name="ciudad" placeholder="Ej: Guadalajara">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <div class="input-group">
                        <input id="estado" type="text" class="form-control" name="estado" placeholder="Ej: Jalisco">
                        <button type="button" class="btn btn-secondary btn-editar">Editar</button>
                    </div>
                </div>

            </div>

            <button type="button" class="btn btn-primary mt-3" id="btnGuardarContinuar">
                Guardar y continuar
            </button>

        </form>
    </div>
</div>


    <!-- ==================== 3. MÉTODO DE PAGO ==================== -->
    <div id="pagoSection" class="card mb-4" style="display:none;">
        <div class="card-body">
            <h4>Metodo de pago</h4>

            <div class="form-check mt-2">
                <input class="form-check-input metodo-pago" type="radio" name="pago" id="tarjeta" value="tarjeta">
                <label class="form-check-label" for="tarjeta">Tarjeta de crédito / débito</label>
            </div>

            <div class="form-check mt-2">
                <input class="form-check-input metodo-pago" type="radio" name="pago" id="transferencia" value="transferencia">
                <label class="form-check-label" for="transferencia">Transferencia bancaria</label>
            </div>

            <div class="form-check mt-2">
                <input class="form-check-input metodo-pago" type="radio" name="pago" id="efectivo" value="efectivo">
                <label class="form-check-label" for="efectivo">Pago en efectivo al recoger</label>
            </div>

            <!-- FORM TARJETA -->
            <div id="formTarjeta" class="mt-3" style="display:none;">
                <div class="card p-3">
                    <label>Número de tarjeta</label>
                    <input class="form-control mb-2" value="0000 0000 0000 0000" disabled>

                    <label>Titular</label>
                    <input class="form-control mb-2" value="Usuario Ejemplo" disabled>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Expiración</label>
                            <input class="form-control" value="12/28" disabled>
                        </div>
                        <div class="col-md-6">
                            <label>CVV</label>
                            <input class="form-control" value="123" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INFO TRANSFERENCIA -->
            <div id="infoTransferencia" class="alert alert-info mt-3" style="display:none;">
                Realiza tu pago a la cuenta: <br>
                <strong>BBVA - 0123456789</strong> a nombre de <strong>Pinturas S.A.</strong>
            </div>
        </div>
    </div>


    <!-- ==================== 4. BOTON FINALIZAR ==================== -->
    <div class="text-end mb-5" id="btnFinalizarContainer" style="display:none;">
        <button class="btn btn-success btn-lg">Finalizar compra</button>
    </div>

</div>


<!-- ==================== JAVASCRIPT ==================== -->
<script>

// ----------- MOSTRAR FORM DE DOMICILIO O NO -------------
document.getElementById("domicilio").addEventListener("change", () => {
    formDomicilio.style.display = "block";
    pagoSection.style.display = "none";
    btnFinalizarContainer.style.display = "none";
    estado = "enviado"; //Se marca el estado de la compra
});

document.getElementById("recoger").addEventListener("change", () => {
    formDomicilio.style.display = "none";
    pagoSection.style.display = "block";
    estado = "apartado"; //se marca el estado de la compra
});

// ----------- VALIDAR Y BLOQUEAR CAMPOS DOMICILIO -------------
document.getElementById("btnGuardarContinuar").addEventListener("click", () => {

    const inputs = document.querySelectorAll("#datosEnvioForm input");
    let ok = true;

    inputs.forEach(input => {
        if (input.value.trim() === "") {
            ok = false;
            input.classList.add("is-invalid");
        } else input.classList.remove("is-invalid");
    });

    if (!ok) {
        alert("Por favor llena todos los campos.");
        return;
    }

    



    // ----------- LLAMAR METODO PARA GUARDAR DIRECCIÓN -----------
   if (banderaEditar) {

    // —— EDITAR DIRECCION EXISTENTE ——

        // Capturar solo los campos que estaban habilitados (fueron editados)
        inputs.forEach(input => {
            if (!input.disabled) {
                camposModificados[input.name] = input.value;
            }
        });

    EditarDireccion(camposModificados);

} else {

    const jsonDireccion = {
        id_usuario: idUsuario,
        calle: document.getElementById("calle").value,
        numero: document.getElementById("numero").value,
        colonia: document.getElementById("colonia").value,
        ciudad: document.getElementById("ciudad").value,
        estado: document.getElementById("estado").value,
        codigo_postal: document.getElementById("codigo_postal").value,
        telefono: document.getElementById("telefono").value
    };

    guardarDireccion(jsonDireccion);
}


   // Bloquear campos
    inputs.forEach(i => i.disabled = true);

    // Mostrar metodo de pago
    pagoSection.style.display = "block";
});


// ----------- BOTONES EDITAR CAMPOS -------------
document.querySelectorAll(".btn-editar").forEach(btn => {
    btn.addEventListener("click", () => {
         banderaEditar = true;
        const input = btn.parentElement.querySelector("input");
        input.disabled = false;
        input.focus();
       
       
    });
});


// ----------- MOSTRAR OPCIONES SEGÚN MÉTODO DE PAGO -------------
document.querySelectorAll(".metodo-pago").forEach(radio => {
    radio.addEventListener("change", () => {

        metodo_pago = radio.value; //Se guarda el metodo de pago seleccionado

        if (radio.value === "tarjeta") {
            formTarjeta.style.display = "block";
            infoTransferencia.style.display = "none";
        } 
        else if (radio.value === "transferencia") {
            formTarjeta.style.display = "none";
            infoTransferencia.style.display = "block";
        }
        else {
            formTarjeta.style.display = "none";
            infoTransferencia.style.display = "none";
        }

        // Activar boton finalizar compra
        btnFinalizarContainer.style.display = "block";
    });
});

//LLamada a Venta.php con API para la operacion de venta
document.getElementById("btnFinalizarContainer").addEventListener("click", () => {

    let datos = {
        usuario_id: idUsuario,
        metodo_pago: metodo_pago,
        estado: estado
    };
     $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Venta.php',
                type: 'POST',
                data: JSON.stringify(datos),
                contentType: 'application/json',
                success: function(response) {
                    console.log('OperacionExitosa:', response);
                    alert("Compra efectuada correctamente, Gracias por su compra.");
                },
                error: function(error) {
                    console.error('Error al crear el registro:', error);
                }
            });
});

function guardarDireccion(jsonDireccion) {
    $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Direcciones.php',
                type: 'POST',
                data: JSON.stringify(jsonDireccion),
                contentType: 'application/json',
                success: function(response) {
                    console.log('OperacionExitosa:', response);
                   camposModificados = {}; // reiniciar objeto limpio
                    camposModificados.id = response.id_direccion; // guardar id
                       
                    
                },
                error: function(error) {
                    console.error('Error al crear el registro:', error);
                }
            });
}

function EditarDireccion(camposModificados){
    console.log("Haber que paso aqui: ",camposModificados);
     $.ajax({
                url: 'http://localhost/ProyectoAsesoriaPinturas/ModuloVenta/API_Venta/Direcciones.php',
                type: 'PUT',
                data: JSON.stringify(camposModificados),
                contentType: 'application/json',
                success: function(response) {
                    console.log('OperacionExitosa:', response);
                },
                error: function(error) {
                    console.error('Error al crear el registro:', error);
                }
            });
}

</script>




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>