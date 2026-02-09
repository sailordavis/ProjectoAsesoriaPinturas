<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogo de pinturas con Carrito</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="Catalogo.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
    crossorigin="anonymous"></script>

    <link rel="icon" href="../IMG/Logo.png" type="image/png">
</head>

<body>

  <!-- Botón Pagina Principal -->
  <a href="../admin.php" class="text-black" style="font-size: 2rem; position: absolute; top: 15px; right: 20px;">
      <i class="bi bi-house"></i>
  </a>

  <!-- Carrito-->
  <a class="text-black"
     style="font-size: 2rem; position: absolute; top: 15px; right: 70px;"
     data-bs-toggle="offcanvas"
     data-bs-target="#carritoCanvas">
      <i class="bi bi-cart2"></i>
  </a>


<div class="text-center mt-3">
<h1>
  <h1 style="display: flex; justify-content: center; align-items: center; gap: 15px;">
  <i class="bi bi-paint-bucket" style="display:inline-block;"></i>
  Catalogo de Pinturas
  <i class="bi bi-paint-bucket" style="display:inline-block; transform: scaleX(-1);"></i>
  </h1>

  <a class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button">
      Encuentra tus pinturas ideales
  </a>
</div>

<!-- Offcanvas de Categorías -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasExampleLabel">Categorias</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <div style="margin-top: -22px;">
      Encuentra todo tipo de pinturas; desde pinturas para todo tipo de superficie. 
      Hasta pinturas para cualquier estilo decorativo.
    </div>
    <div id="Opciones"></div>
  </div>
</div>

<br>
<div id="Pinturas"></div>

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

<!-- Archivo JavaScript -->
<script src="Catalogo.js?v=<?php echo time(); ?>"></script>

</body>
</html>
