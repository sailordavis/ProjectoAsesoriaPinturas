<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Interactivo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Enlace css -->
     <link rel="stylesheet" href="pruebaTEST.css">
     <!-- jQuery -->
     <script src="https://code.jquery.com/jquery-3.7.1.min.js"
     integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
     crossorigin="anonymous"></script>
    <link rel="icon" href="../IMG/Logo.png" type="image/png">

</head>
<body>

    <a href="../admin.php" class="text-black" style="font-size: 2rem; position: absolute; top: 15px; left: 20px;">
        <i class="bi bi-house"></i>
    </a>

    <!-- Carrito-->
    <a id="btnCarrito" class="text-black"
    style="font-size: 2rem; position: absolute; top: 15px; right: 70px; display: none;"
    data-bs-toggle="offcanvas"
    data-bs-target="#carritoCanvas">
    <i class="bi bi-cart2"></i>
    </a>

     <!-- Titulo -->
    <h1 class="fw-bold text-primary" 
    style=" align-items: center; margin-left: 550px ; margin-bottom: 20px; margin-top: 20px;">TEST INTERACTIVO</h1>
    <!-- Seccion de preguntas -->
    <div id="Preguntas" style="margin-top: 20px; "></div>

     <!-- Modal Finalizacion del test -->
    <div class="modal" tabindex="-1" id="aviso_final">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ha finalizado el test</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Presione el boton finalizar para recibir las recomendaciones.</p>
        <p>Si no esta seguro con sus respuestas puede presionar</p>
        <p>El boton Continuar</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="EliminarUltimoDato()">Continuar</button>
        <button type="button" class="btn btn-primary" onclick="MostrarResultados();">Finalizar</button>
      </div>
    </div>
  </div>
</div>

<!-- OFFCANVAS DEL CARRITO -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="carritoCanvas">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Mi Carrito</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <div id="listaCarrito">
      <!-- Aquí van los productos agregados por JavaScript -->
      <div id="Seleccionadas">
        <!-- Se eliminara estos elementos cuando se agregue producto al Carrito -->
       <p class="text-center">El carrito esta vacio</p>
       <h5>Total: $<span id="totalCarrito">0</span></h5>
       <hr>
      </div>
    </div>
  </div>
</div>
<!-- FIN DEL OFFCANVAS DEL CARRITO -->



<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- Archivo javascript -->
<script src="pruebaTEST.js?v=<?php echo time(); ?>"></script>

</body>
</html>
