<!Doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contratos BBS</title>

  <link href="../css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/generales.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="../css/lista.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: ../../menu/login/index.php");
  exit();
}
?>

<body class="min-h-screen bg-[#071322] text-white">
  <?php include("../includes/sidebar.php"); ?>

  <main class="min-h-screen px-4 py-6 md:px-8">
    <div class="mx-auto max-w-7xl">

      <!-- Encabezado -->
      <section class="mb-6 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-md">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Clientes rezagados</h1>
            <p class="mt-1 text-sm text-white/70">
              Consulta clientes pendientes, legados y cancelados.
            </p>
          </div>

          <div
            class="inline-flex items-center gap-2 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-200">
            <i class="bi bi-clock-history"></i>
            Seguimiento de rezagados
          </div>
        </div>
      </section>

      <!-- Barra de herramientas -->
      <section class="mb-6 rounded-3xl border border-white/10 bg-[#0b1a2d] p-4 shadow-xl md:p-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">

          <div class="lg:col-span-6">
            <label for="busqueda-resagados" class="mb-2 block text-sm font-medium text-white/70">
              Buscar cliente
            </label>

            <div>
              <input type="text" id="busqueda-resagados" name="busqueda-resagados"
                placeholder="Buscar por nombre, dirección, teléfono..."
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white placeholder:text-white/35 outline-none transition focus:border-cyan-400/40 focus:bg-[#0c1d33]">
            </div>
          </div>

          <div class="lg:col-span-3">
            <label for="filtro-resagados" class="mb-2 block text-sm font-medium text-white/70">
              Filtro
            </label>

            <select id="filtro-resagados"
              class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none transition focus:border-cyan-400/40 focus:bg-[#0c1d33]">
              <option value="pendientes" selected>Pendientes (sin contrato)</option>
              <option value="legado">Legados (es_legado=1)</option>
              <option value="cancelados">Cancelados (legado)</option>
              <option value="todos">Todos</option>
            </select>
          </div>

          <div class="lg:col-span-2">
            <label for="orden-resagados" class="mb-2 block text-sm font-medium text-white/70">
              Orden
            </label>

            <select id="orden-resagados"
              class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none transition focus:border-cyan-400/40 focus:bg-[#0c1d33]">
              <option value="desc" selected>Más recientes</option>
              <option value="asc">Más antiguos</option>
            </select>
          </div>

          <div class="lg:col-span-1 flex items-end">
            <button id="btnBuscarResagados" type="button"
              class="flex w-full items-center justify-center gap-2 rounded-2xl bg-cyan-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
              <i class="bi bi-search text-base"></i>
              <span>Buscar</span>
            </button>
          </div>
        </div>
      </section>

      <!-- Tabla -->
      <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-4 shadow-xl md:p-6">
        <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-cyan-300">Registros</h2>
            <p class="mt-1 text-sm text-white/60">
              Los resultados de rezagados aparecerán en este panel.
            </p>
          </div>

          <div id="resagados-info" class="text-xs text-white/45">
            Usa el buscador o filtra por tipo.
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#071322]">
          <div id="tabla-resagados" class="min-h-[320px] overflow-x-auto p-4 md:p-5">
            <div
              class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
              <div>
                <div
                  class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
                  <i class="bi bi-search text-xl"></i>
                </div>
                <h3 class="text-base font-semibold text-white">Sin resultados cargados</h3>
                <p class="mt-2 text-sm text-white/55">
                  Aquí se mostrará la lista de clientes rezagados.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="text-sm text-white/60" id="paginacion-resagados-info">Mostrando 0 resultados</div>

          <div class="flex items-center gap-2" id="paginacion-resagados">
            <!-- botones dinámicos -->
          </div>
        </div>
      </section>

      <div id="respuesta" class="mt-4"></div>
    </div>
  </main>

  <script src="../js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://momentjs.com/downloads/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/cancelaciones.js"></script>
  <script src="../js/sidebar.js"></script>
  <script src="../js/swalConfig.js"></script>
  <script src="../js/resagados.js"></script>
  <script src="../js/swaldark.js"></script>
</body>

</html>