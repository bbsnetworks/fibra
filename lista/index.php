
<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: ../../menu/login/index.php");
  exit();
}
?>

<!Doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contratos BBS</title>

  <script src="../js/jspdf.min.js"></script>
  <script src="../js/signature_pad.umd.min.js"></script>

  <link href="../css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/generales.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../css/lista.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  
</head>

<body class="min-h-screen bg-[#071322] text-white">

  <?php include("../includes/sidebar.php"); ?>

  <main class="min-h-screen px-4 py-6 md:px-8">
    <div class="mx-auto max-w-8xl">

      <!-- Encabezado -->
      <section class="mb-6 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-md">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Lista de contratos</h1>
            <p class="mt-1 text-sm text-white/70">
              Consulta, busca y administra los contratos registrados.
            </p>
          </div>

          <div
            class="inline-flex items-center gap-2 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-200">
            <i class="bi bi-file-earmark-text"></i>
            Gestión de contratos
          </div>
        </div>
      </section>

      <!-- Barra de herramientas -->
      <section class="mb-6 rounded-3xl border border-white/10 bg-[#0b1a2d] p-4 shadow-xl md:p-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">

          <!-- Buscador -->
          <div class="lg:col-span-7">
            <label for="busqueda" class="mb-2 block text-sm font-medium text-white/70">
              Buscar contrato
            </label>

            <div class="relative">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-white/40">
                <i class="bi bi-search"></i>
              </span>

              <input
                type="text"
                id="busqueda"
                name="busqueda"
                placeholder="Buscar por nombre, contrato, teléfono, RFC..."
                class="w-full rounded-2xl border border-white/10 bg-[#071322] py-3 pl-11 pr-4 text-white placeholder:text-white/35 outline-none transition focus:border-cyan-400/40 focus:bg-[#0c1d33]">
            </div>
          </div>

          <!-- Filtro -->
          <div class="lg:col-span-3">
            <label for="filtro-estado" class="mb-2 block text-sm font-medium text-white/70">
              Estado
            </label>

            <select
              id="filtro-estado"
              class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none transition focus:border-cyan-400/40 focus:bg-[#0c1d33]">
              <option value="activo" selected>Activos</option>
              <option value="cancelado">Cancelados</option>
              <option value="todos">Todos</option>
            </select>
          </div>

          <!-- Botón -->
          <div class="lg:col-span-2 flex items-end">
            <button
              id="btnBuscar"
              type="button"
              class="w-full rounded-2xl bg-cyan-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
              Buscar
            </button>
          </div>
        </div>
      </section>

      <!-- Lista / Tabla -->
      <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-4 shadow-xl md:p-6">
        <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-cyan-300">Registros</h2>
            <p class="mt-1 text-sm text-white/60">
              Los resultados de contratos aparecerán en este panel.
            </p>
          </div>

          <div class="text-xs text-white/45">
            Usa el buscador o filtra por estado.
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#071322]">
          <div id="tabla" class="min-h-[320px] overflow-x-auto p-4 md:p-5">
            <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
              <div>
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
                  <i class="bi bi-search text-xl"></i>
                </div>
                <h3 class="text-base font-semibold text-white">Sin resultados cargados</h3>
                <p class="mt-2 text-sm text-white/55">
                  Aquí se mostrará la lista de contratos cuando conectemos la búsqueda.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div id="respuesta" class="mt-4"></div>
    </div>
  </main>

  <!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border border-white/10 bg-[#0b1a2d] text-white shadow-xl">
      <div class="modal-header border-bottom border-white/10">
        <h1 class="modal-title fs-5">Agregar Cliente</h1>

        <button 
          type="button" 
          class="btn-close btn-close-white" 
          data-bs-dismiss="modal" 
          aria-label="Close">
        </button>
      </div>

      <div class="modal-body" id="modal"></div>
    </div>
  </div>
</div>

  <!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border border-white/10 bg-[#0b1a2d] text-white shadow-xl">
      <div class="modal-header border-bottom border-white/10">
        <h1 class="modal-title fs-5">Editar Contrato</h1>

        <button 
          type="button" 
          class="btn-close btn-close-white" 
          data-bs-dismiss="modal" 
          aria-label="Close">
        </button>
      </div>

      <div class="modal-body" id="modal2"></div>
    </div>
  </div>
</div>

  <script src="../js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://momentjs.com/downloads/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/bootstrapval.js"></script>
  <script src="../js/booststraptoogletips.js"></script>
  <script src="../js/lista.js"></script>
  <script src="../js/cancelaciones.js"></script>
  <script src="../js/sidebar.js"></script>
  <script src="../js/swaldark.js"></script>
  <script src="../js/swalConfig.js"></script>

  <script>
    document.querySelectorAll('.crear').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.lista').forEach(el => el.style.display = 'none');
  </script>
</body>

</html>