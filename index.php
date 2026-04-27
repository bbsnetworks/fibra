<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: ../menu/login/index.php");
  exit();
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contratos BBS - Fibra</title>

  <script src="js/jspdf.min.js"></script>
  <script src="js/signature_pad.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/generales.css">
  <link rel="stylesheet" href="css/index.css">
</head>

<body class="min-h-screen bg-[#071322] text-white">
  <?php include_once("includes/sidebar.php"); ?>

  <main class="min-h-screen px-4 py-6 md:px-8">
    <div class="mx-auto max-w-7xl">

      <!-- Encabezado -->
      <div class="mb-6 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-md">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Generación de contrato Fibra Óptica</h1>
            <p class="mt-1 text-sm text-white/70">
              Formulario rediseñado para contrato de internet fijo en casa por fibra óptica.
            </p>
          </div>

          <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-200">
            Contrato No. <span id="contratoBadge"></span>
          </div>
        </div>
      </div>

      <form id="formFibra" class="space-y-6" novalidate>

        <!-- Encabezado empresa -->
        <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl backdrop-blur-md">
          <div class="text-center">
            <h2 class="text-lg font-semibold tracking-wide text-cyan-300">TEKNE SEND.4, S. DE R.L. DE C.V.</h2>
            <p class="mt-2 text-sm leading-6 text-white/80">
              RFC: TSE230302694<br>
              AVENIDA JOSÉ MARÍA MORELOS 147, COLONIA CENTRO, MUNICIPIO DE URIANGATO, C.P. 38980, GUANAJUATO.
            </p>
          </div>
        </section>

        <!-- Datos generales -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5 flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-white">Datos generales</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4">
              <label for="ncontrato" class="mb-2 block text-sm font-medium text-white/80">Contrato No</label>
              <input
                type="number"
                id="ncontrato"
                name="ncontrato"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none transition focus:border-cyan-400/40 focus:bg-[#0c1d33]">
              <div id="error-message" class="mt-2 text-sm text-red-400"></div>
            </div>

            <div class="md:col-span-4">
              <label for="fechac" class="mb-2 block text-sm font-medium text-white/80">Fecha</label>
              <input
                type="date"
                id="fechac"
                name="fechac"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>
        </section>

        <!-- Suscriptor -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Datos del suscriptor</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4">
              <label for="nombre" class="mb-2 block text-sm font-medium text-white/80">Nombre / Denominación</label>
              <input
                type="text"
                id="nombre"
                name="nombre"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="apellidoPaterno" class="mb-2 block text-sm font-medium text-white/80">Apellido paterno</label>
              <input
                type="text"
                id="apellidoPaterno"
                name="apellidoPaterno"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="apellidoMaterno" class="mb-2 block text-sm font-medium text-white/80">Apellido materno</label>
              <input
                type="text"
                id="apellidoMaterno"
                name="apellidoMaterno"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="calle" class="mb-2 block text-sm font-medium text-white/80">Calle</label>
              <input
                type="text"
                id="calle"
                name="calle"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-2">
              <label for="numeroExterior" class="mb-2 block text-sm font-medium text-white/80"># Ext.</label>
              <input
                type="text"
                id="numeroExterior"
                name="numeroExterior"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-2">
              <label for="numeroInterior" class="mb-2 block text-sm font-medium text-white/80"># Int.</label>
              <input
                type="text"
                id="numeroInterior"
                name="numeroInterior"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="colonia" class="mb-2 block text-sm font-medium text-white/80">Colonia</label>
              <input
                type="text"
                id="colonia"
                name="colonia"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="municipio" class="mb-2 block text-sm font-medium text-white/80">Alcaldía / Municipio</label>
              <input
                type="text"
                id="municipio"
                name="municipio"
                onchange="cambioCiudad()"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-3">
              <label for="estado" class="mb-2 block text-sm font-medium text-white/80">Estado</label>
              <input
                type="text"
                id="estado"
                name="estado"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-2">
              <label for="cp" class="mb-2 block text-sm font-medium text-white/80">C.P.</label>
              <input
                type="text"
                id="cp"
                name="cp"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-3">
              <label for="rfc" class="mb-2 block text-sm font-medium text-white/80">RFC</label>
              <input
                type="text"
                id="rfc"
                name="rfc"
                maxlength="13"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="telefono" class="mb-2 block text-sm font-medium text-white/80">Teléfono</label>
              <input
                type="text"
                id="telefono"
                name="telefono"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Teléfono fijo / móvil</label>
              <div id="boxTipoTelefono" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="tipoTelefono" value="fijo" class="h-4 w-4">
                  <span class="text-sm text-white/80">Fijo</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="tipoTelefono" value="movil" class="h-4 w-4">
                  <span class="text-sm text-white/80">Móvil</span>
                </label>
              </div>
            </div>
          </div>
        </section>

        <!-- Servicio -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Servicio de internet fijo</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4">
              <label for="descripcionPaquete" class="mb-2 block text-sm font-medium text-white/80">Descripción paquete / oferta</label>
              <input
                type="text"
                id="descripcionPaquete"
                name="descripcionPaquete"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40"
                placeholder="Ej. Fibra 100 Megas">
            </div>

            <div class="md:col-span-4">
              <label for="mensualidad" class="mb-2 block text-sm font-medium text-white/80">Total de la mensualidad</label>
              <input
                type="text"
                id="mensualidad"
                name="mensualidad"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="fechaPago" class="mb-2 block text-sm font-medium text-white/80">Fecha de pago</label>
              <input
                type="text"
                id="fechaPago"
                name="fechaPago"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40"
                placeholder="Ej. día 05 de cada mes">
            </div>

            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Aplica tarifa por reconexión</label>
              <div id="boxReconexcion" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="aplicaReconexcion" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="aplicaReconexcion" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label for="montoReconexcion" class="mb-2 block text-sm font-medium text-white/80">Monto reconexión</label>
              <input
                type="text"
                id="montoReconexcion"
                name="montoReconexcion"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="nomNumeral" class="mb-2 block text-sm font-medium text-white/80">NOM numeral</label>
              <input
                type="text"
                id="nomNumeral"
                name="nomNumeral"
                value="5.1.2.1"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-6">
              <label class="mb-2 block text-sm font-medium text-white/80">Vigencia del contrato</label>
              <div id="boxVigencia" class="space-y-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="tipoVigencia" value="indefinido" class="h-4 w-4">
                  <span class="text-sm text-white/80">Indefinido: sin penalidad</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="tipoVigencia" value="plazo_forzoso" class="h-4 w-4">
                  <span class="text-sm text-white/80">Plazo forzoso</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-3">
              <label for="mesesPlazo" class="mb-2 block text-sm font-medium text-white/80">Meses plazo forzoso</label>
              <input
                type="number"
                id="mesesPlazo"
                name="mesesPlazo"
                min="0"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-3">
              <label for="penalidadTexto" class="mb-2 block text-sm font-medium text-white/80">Penalidad</label>
              <input
                type="text"
                id="penalidadTexto"
                name="penalidadTexto"
                value="20% del monto total de los meses pendientes"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>

          <div class="mt-5 rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4 text-sm text-cyan-100/90">
            En el estado de cuenta y/o factura se podrá visualizar la fecha de corte del servicio y fecha de pago.
          </div>
        </section>

        <!-- Equipo terminal -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Equipo terminal</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Equipo entregado en</label>
              <div id="boxTipoEntrega" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="tipoEntregaEquipo" value="comodato" class="h-4 w-4">
                  <span class="text-sm text-white/80">Comodato</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="tipoEntregaEquipo" value="compraventa" class="h-4 w-4">
                  <span class="text-sm text-white/80">Compraventa</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label for="marcaEquipo" class="mb-2 block text-sm font-medium text-white/80">Marca</label>
              <input
                type="text"
                id="marcaEquipo"
                name="marcaEquipo"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="modeloEquipo" class="mb-2 block text-sm font-medium text-white/80">Modelo</label>
              <input
                type="text"
                id="modeloEquipo"
                name="modeloEquipo"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="numeroSerie" class="mb-2 block text-sm font-medium text-white/80">Número de serie</label>
              <input
                type="text"
                id="numeroSerie"
                name="numeroSerie"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="numeroEquipos" class="mb-2 block text-sm font-medium text-white/80">Número de equipos</label>
              <input
                type="number"
                id="numeroEquipos"
                name="numeroEquipos"
                min="1"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="costoTotalEquipo" class="mb-2 block text-sm font-medium text-white/80">Costo total</label>
              <input
                type="text"
                id="costoTotalEquipo"
                name="costoTotalEquipo"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Modalidad de pago</label>
              <div id="boxModalidadPagoEquipo" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="modalidadPagoEquipo" value="unico" class="h-4 w-4">
                  <span class="text-sm text-white/80">Pago único</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="modalidadPagoEquipo" value="diferido" class="h-4 w-4">
                  <span class="text-sm text-white/80">Diferido</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label for="costoDiferido" class="mb-2 block text-sm font-medium text-white/80">Costo diferido</label>
              <input
                type="text"
                id="costoDiferido"
                name="costoDiferido"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="mesesDiferido" class="mb-2 block text-sm font-medium text-white/80">Meses diferido</label>
              <input
                type="number"
                id="mesesDiferido"
                name="mesesDiferido"
                min="0"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>
        </section>

        <!-- Instalación -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Instalación del equipo terminal</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-6">
              <label for="domicilioInstalacion" class="mb-2 block text-sm font-medium text-white/80">Domicilio de la instalación</label>
              <input
                type="text"
                id="domicilioInstalacion"
                name="domicilioInstalacion"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-3">
              <label for="fechaInstalacion" class="mb-2 block text-sm font-medium text-white/80">Fecha</label>
              <input
                type="date"
                id="fechaInstalacion"
                name="fechaInstalacion"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-3">
              <label for="horaInstalacion" class="mb-2 block text-sm font-medium text-white/80">Hora</label>
              <input
                type="time"
                id="horaInstalacion"
                name="horaInstalacion"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="costoInstalacion" class="mb-2 block text-sm font-medium text-white/80">Costo</label>
              <input
                type="text"
                id="costoInstalacion"
                name="costoInstalacion"
                class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>

          <div class="mt-5 rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4 text-sm text-cyan-100/90">
            “EL PROVEEDOR” entregará y realizará la instalación del equipo terminal en un plazo que no podrá ser mayor a 10 días hábiles contados a partir de la firma del presente contrato.
          </div>
        </section>

        <!-- Método de pago -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Método de pago</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4 space-y-3">
              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpEfectivo" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Efectivo</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpTransferencia" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Transferencia bancaria</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpDeposito" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Depósito a cuenta bancaria</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpTiendasServicios" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Pago en tiendas de servicios</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpTarjeta" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Tarjeta de crédito o débito</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpDomiciliado" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Domiciliado con tarjeta</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpEnLinea" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Pago en línea</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                <input type="checkbox" id="mpCentrosServicio" class="h-4 w-4 rounded">
                <span class="text-sm text-white/80">Pago en tiendas o centros de servicio</span>
              </label>
            </div>

            <div class="md:col-span-8">
              <label for="datosMetodoPago" class="mb-2 block text-sm font-medium text-white/80">Datos para el método de pago elegido</label>
              <textarea
                id="datosMetodoPago"
                rows="12"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40"></textarea>
            </div>
          </div>
        </section>

        <!-- Cargo a tarjeta -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Autorización para cargo de tarjeta</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Autoriza</label>
              <div id="boxCargoTarjeta" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="autorizaCargoTarjeta" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="autorizaCargoTarjeta" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label for="mesesCargoTarjeta" class="mb-2 block text-sm font-medium text-white/80">Vigencia de cargos / meses</label>
              <input
                type="number"
                id="mesesCargoTarjeta"
                name="mesesCargoTarjeta"
                min="0"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="banco" class="mb-2 block text-sm font-medium text-white/80">Banco</label>
              <input
                type="text"
                id="banco"
                name="banco"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-4">
              <label for="numeroTarjeta" class="mb-2 block text-sm font-medium text-white/80">Número de tarjeta</label>
              <input
                type="text"
                id="numeroTarjeta"
                name="numeroTarjeta"
                maxlength="16"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>
        </section>

        <!-- Servicios adicionales -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Servicios adicionales</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-8">
              <label for="servicioAdic1Desc" class="mb-2 block text-sm font-medium text-white/80">Descripción 1</label>
              <input
                type="text"
                id="servicioAdic1Desc"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
            <div class="md:col-span-4">
              <label for="servicioAdic1Costo" class="mb-2 block text-sm font-medium text-white/80">Costo 1</label>
              <input
                type="text"
                id="servicioAdic1Costo"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-8">
              <label for="servicioAdic2Desc" class="mb-2 block text-sm font-medium text-white/80">Descripción 2</label>
              <input
                type="text"
                id="servicioAdic2Desc"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
            <div class="md:col-span-4">
              <label for="servicioAdic2Costo" class="mb-2 block text-sm font-medium text-white/80">Costo 2</label>
              <input
                type="text"
                id="servicioAdic2Costo"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>
        </section>

        <!-- Facturables -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Conceptos facturables</h3>
            <p class="mt-1 text-sm text-white/60">
              Ejemplo: costo por cambio de domicilio, costos administrativos adicionales.
            </p>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-8">
              <label for="conceptoFact1Desc" class="mb-2 block text-sm font-medium text-white/80">Descripción 1</label>
              <input
                type="text"
                id="conceptoFact1Desc"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
            <div class="md:col-span-4">
              <label for="conceptoFact1Costo" class="mb-2 block text-sm font-medium text-white/80">Costo 1</label>
              <input
                type="text"
                id="conceptoFact1Costo"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-8">
              <label for="conceptoFact2Desc" class="mb-2 block text-sm font-medium text-white/80">Descripción 2</label>
              <input
                type="text"
                id="conceptoFact2Desc"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
            <div class="md:col-span-4">
              <label for="conceptoFact2Costo" class="mb-2 block text-sm font-medium text-white/80">Costo 2</label>
              <input
                type="text"
                id="conceptoFact2Costo"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>
        </section>

        <!-- Envío electrónico -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Envío por medios electrónicos</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Factura</label>
              <div id="boxFactura" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="envioFactura" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="envioFactura" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Carta de derechos mínimos</label>
              <div id="boxCartaDerechos" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="envioCartaDerechos" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="envioCartaDerechos" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Contrato de adhesión</label>
              <div id="boxContratoAdhesion" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="envioContratoAdhesion" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="envioContratoAdhesion" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label class="mb-2 block text-sm font-medium text-white/80">Medio electrónico autorizado</label>
              <div id="boxMedioElectronico" class="space-y-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="medioElectronico" value="correo" class="h-4 w-4">
                  <span class="text-sm text-white/80">Correo electrónico</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="medioElectronico" value="otro" class="h-4 w-4">
                  <span class="text-sm text-white/80">Otro</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-4">
              <label for="correoElectronico" class="mb-2 block text-sm font-medium text-white/80">Correo electrónico</label>
              <input
                type="email"
                id="correoElectronico"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-2">
              <label for="otroMedioElectronico" class="mb-2 block text-sm font-medium text-white/80">Otro medio</label>
              <input
                type="text"
                id="otroMedioElectronico"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>

            <div class="md:col-span-2">
              <label for="numeroOtroMedio" class="mb-2 block text-sm font-medium text-white/80">Número</label>
              <input
                type="text"
                id="numeroOtroMedio"
                class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
            </div>
          </div>
        </section>

        <!-- Uso de información -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Autorización para uso de información del suscriptor</h3>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
            <div class="md:col-span-6">
              <p class="mb-2 block text-sm font-medium text-white/80">
                ¿Autoriza que su información sea cedida o transmitida a terceros con fines mercadotécnicos o publicitarios?
              </p>
              <div id="boxCederInfo" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="autorizaCederInfo" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="autorizaCederInfo" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>

            <div class="md:col-span-6">
              <p class="mb-2 block text-sm font-medium text-white/80">
                ¿Acepta recibir llamadas del proveedor de promociones del servicio o paquetes?
              </p>
              <div id="boxLlamadasPromo" class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="autorizaLlamadasPromo" value="si" class="h-4 w-4">
                  <span class="text-sm text-white/80">Sí</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
                  <input type="radio" name="autorizaLlamadasPromo" value="no" class="h-4 w-4">
                  <span class="text-sm text-white/80">No</span>
                </label>
              </div>
            </div>
          </div>
        </section>

        <!-- Contrato -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-cyan-300">Contrato</h3>
          </div>

          <div class="rounded-2xl border border-white/10 bg-[#071322] p-3">
            <div id="visorContratoFibra" class="max-h-[70vh] overflow-y-auto overscroll-contain rounded-xl border border-white/10 bg-white/5 p-2">
              <div class="space-y-4">
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0001.jpg" alt="Contrato fibra 1" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0002.jpg" alt="Contrato fibra 2" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0003.jpg" alt="Contrato fibra 3" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0004.jpg" alt="Contrato fibra 4" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0005.jpg" alt="Contrato fibra 5" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0006.jpg" alt="Contrato fibra 6" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0007.jpg" alt="Contrato fibra 7" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0008.jpg" alt="Contrato fibra 8" class="block w-full">
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white">
                  <img src="img/fibra/Contrato-0009.jpg" alt="Contrato fibra 9" class="block w-full">
                </div>
              </div>
            </div>
          </div>

          <label id="boxAceptaContrato" class="mt-5 flex items-start gap-3 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-4 text-sm text-cyan-100">
            <input type="checkbox" id="aceptaContratoFibra" class="mt-1 h-4 w-4 rounded">
            <span>He leído y estoy de acuerdo con lo especificado en el contrato anterior.</span>
          </label>
        </section>

        <!-- Firma -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <div class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm leading-6 text-white/80">
              LA PRESENTE CARÁTULA SE RIGE CONFORME A LAS CLÁUSULAS DEL CONTRATO DE ADHESIÓN REGISTRADO EN PROFECO.
              LAS FIRMAS INSERTAS SON LA ACEPTACIÓN DE LA PRESENTE CARÁTULA Y CLAUSULADO DEL CONTRATO DE ADHESIÓN CON NÚMERO
              <span id="idContratoTexto"></span>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
              <div class="md:col-span-4">
                <label for="ciudadFirma" class="mb-2 block text-sm font-medium text-white/80">Ciudad</label>
                <input
                  type="text"
                  id="ciudadFirma"
                  name="ciudadFirma"
                  class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
              </div>

              <div class="md:col-span-2">
                <label for="diaFirma" class="mb-2 block text-sm font-medium text-white/80">Día</label>
                <input
                  type="text"
                  id="diaFirma"
                  name="diaFirma"
                  class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
              </div>

              <div class="md:col-span-3">
                <label for="mesFirma" class="mb-2 block text-sm font-medium text-white/80">Mes</label>
                <input
                  type="text"
                  id="mesFirma"
                  name="mesFirma"
                  class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
              </div>

              <div class="md:col-span-3">
                <label for="anioFirma" class="mb-2 block text-sm font-medium text-white/80">Año</label>
                <input
                  type="text"
                  id="anioFirma"
                  name="anioFirma"
                  class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
              </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-[#071322] p-3">
              <div id="signature-preview-wrapper" class="overflow-hidden rounded-2xl border border-dashed border-white/20 bg-white cursor-pointer">
                <canvas id="signature-canvas" class="block h-[200px] w-full"></canvas>
              </div>

              <div class="mt-3 flex flex-wrap gap-3">
                <button
                  type="button"
                  id="openSignatureModal"
                  class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-400">
                  Firmar en pantalla completa
                </button>

                <button
                  type="button"
                  id="clearSignaturePreview"
                  class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">
                  Limpiar
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Evidencia -->
        <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
          <label for="evidencia" class="mb-2 block text-sm font-medium text-white/80">Ingresa la identificación del cliente</label>
          <input
            id="evidencia"
            type="file"
            class="block w-full rounded-2xl border border-dashed border-white/20 bg-[#071322] px-4 py-4 text-sm text-white file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-500 file:px-4 file:py-2 file:text-white hover:file:bg-cyan-400">
        </section>

        <!-- Acciones -->
        <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl">
          <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
              Generar PDF
            </button>

            <button
              type="button"
              id="btnVistaDatos"
              class="inline-flex items-center justify-center rounded-2xl bg-slate-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-600">
              Ver datos
            </button>

            <div id="resultado"
              class="min-h-[48px] flex-1 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-sm text-white/80">
            </div>
          </div>
        </section>

      </form>

      <div id="datos"></div>
    </div>
  </main>

  <!-- Modal firma fullscreen -->
  <div id="signatureModal" class="fixed inset-0 z-[9999] hidden bg-black/90 backdrop-blur-sm">
    <div class="flex h-full w-full flex-col">
      <div class="flex items-center justify-between border-b border-white/10 bg-[#071322] px-4 py-3">
        <div>
          <h3 class="text-base font-semibold text-white">Firma en pantalla completa</h3>
          <p class="text-xs text-white/60">Puedes girar el teléfono para firmar más cómodo.</p>
        </div>

        <button
          type="button"
          id="closeSignatureModal"
          class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
          Cerrar
        </button>
      </div>

      <div class="flex-1 p-3 md:p-5">
        <div class="flex h-full flex-col rounded-2xl border border-white/10 bg-[#0b1a2d] p-3">
          <div class="mb-3 flex flex-wrap gap-3">
            <button
              type="button"
              id="clearSignatureModalPad"
              class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
              Limpiar
            </button>

            <button
              type="button"
              id="saveSignatureModal"
              class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-400">
              Guardar firma
            </button>
          </div>

          <div class="min-h-0 flex-1 overflow-hidden rounded-2xl border border-dashed border-white/20 bg-white">
            <canvas id="signature-canvas-modal" class="block h-full w-full"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="./js/swaldark.js"></script>
  <script src="https://momentjs.com/downloads/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/sidebar.js"></script>

  <script>
    const formFibra = document.getElementById('formFibra');
    const resultado = document.getElementById('resultado');
    const contratoBadge = document.getElementById('contratoBadge');
    const ncontrato = document.getElementById('ncontrato');
    const idContratoTexto = document.getElementById('idContratoTexto');

    let date = moment().format('YYYY-MM-DD');
    document.getElementById('fechac').value = date;

    document.getElementById('diaFirma').value = moment().format('DD');
    document.getElementById('mesFirma').value = moment().format('MM');
    document.getElementById('anioFirma').value = moment().format('YYYY');

    function cambioCiudad() {
      const municipio = document.getElementById("municipio");
      const ciudadFirma = document.getElementById("ciudadFirma");
      if (ciudadFirma) ciudadFirma.value = municipio.value;
    }

    function updateContratoBadge() {
      const value = ncontrato.value || '---';
      contratoBadge.textContent = value;
      idContratoTexto.textContent = value;
    }

    ncontrato.addEventListener('input', updateContratoBadge);
    updateContratoBadge();

    function getRadioValue(name) {
      const checked = document.querySelector(`input[name="${name}"]:checked`);
      return checked ? checked.value : '';
    }

    function getInputValue(id) {
      const el = document.getElementById(id);
      return el ? el.value.trim() : '';
    }

    function marcarErrorCampo(el) {
      if (!el) return;
      el.classList.add('border-red-500', 'ring-2', 'ring-red-500/20');
    }

    function limpiarErrores() {
      document.querySelectorAll('.requerido').forEach(el => {
        el.classList.remove('border-red-500', 'ring-2', 'ring-red-500/20');
      });

      [
        'boxTipoTelefono', 'boxReconexcion', 'boxVigencia', 'boxTipoEntrega',
        'boxCargoTarjeta', 'boxFactura', 'boxCartaDerechos', 'boxContratoAdhesion',
        'boxMedioElectronico', 'boxCederInfo', 'boxLlamadasPromo', 'boxAceptaContrato'
      ].forEach(id => {
        const box = document.getElementById(id);
        if (box) box.classList.remove('ring-2', 'ring-red-500/30', 'border-red-500');
      });
    }

    function marcarErrorBox(id) {
      const box = document.getElementById(id);
      if (!box) return;
      box.classList.add('ring-2', 'ring-red-500/30', 'border-red-500');
    }

    function validarFormulario() {
      limpiarErrores();
      let ok = true;

      document.querySelectorAll('.requerido').forEach(el => {
        if (!el.value.trim()) {
          marcarErrorCampo(el);
          ok = false;
        }
      });

      const radiosObligatorios = [
        { name: 'tipoTelefono', box: 'boxTipoTelefono' },
        { name: 'aplicaReconexcion', box: 'boxReconexcion' },
        { name: 'tipoVigencia', box: 'boxVigencia' },
        { name: 'tipoEntregaEquipo', box: 'boxTipoEntrega' },
        { name: 'autorizaCargoTarjeta', box: 'boxCargoTarjeta' },
        { name: 'envioFactura', box: 'boxFactura' },
        { name: 'envioCartaDerechos', box: 'boxCartaDerechos' },
        { name: 'envioContratoAdhesion', box: 'boxContratoAdhesion' },
        { name: 'medioElectronico', box: 'boxMedioElectronico' },
        { name: 'autorizaCederInfo', box: 'boxCederInfo' },
        { name: 'autorizaLlamadasPromo', box: 'boxLlamadasPromo' }
      ];

      radiosObligatorios.forEach(item => {
        if (!getRadioValue(item.name)) {
          marcarErrorBox(item.box);
          ok = false;
        }
      });

      const aceptaContrato = document.getElementById('aceptaContratoFibra');
      if (!aceptaContrato.checked) {
        marcarErrorBox('boxAceptaContrato');
        ok = false;
      }

      if (!signaturePadPreview || signaturePadPreview.isEmpty()) {
        document.getElementById('signature-preview-wrapper').classList.add('ring-2', 'ring-red-500/30');
        ok = false;
      } else {
        document.getElementById('signature-preview-wrapper').classList.remove('ring-2', 'ring-red-500/30');
      }

      if (!ok) {
        Swal.fire({
          icon: 'error',
          title: 'Faltan campos por llenar',
          text: 'Revisa los campos obligatorios, acepta el contrato y agrega la firma.',
          background: '#071322',
          color: '#fff',
          confirmButtonColor: '#06b6d4'
        });
      }

      return ok;
    }

    function obtenerDatosFibra() {
      return {
        ncontrato: getInputValue('ncontrato'),
        fechac: getInputValue('fechac'),

        suscriptor: {
          nombre: getInputValue('nombre'),
          apellidoPaterno: getInputValue('apellidoPaterno'),
          apellidoMaterno: getInputValue('apellidoMaterno'),
          calle: getInputValue('calle'),
          numeroExterior: getInputValue('numeroExterior'),
          numeroInterior: getInputValue('numeroInterior'),
          colonia: getInputValue('colonia'),
          municipio: getInputValue('municipio'),
          estado: getInputValue('estado'),
          cp: getInputValue('cp'),
          rfc: getInputValue('rfc'),
          telefono: getInputValue('telefono'),
          tipoTelefono: getRadioValue('tipoTelefono')
        },

        servicio: {
          descripcionPaquete: getInputValue('descripcionPaquete'),
          mensualidad: getInputValue('mensualidad'),
          fechaPago: getInputValue('fechaPago'),
          aplicaReconexcion: getRadioValue('aplicaReconexcion'),
          montoReconexcion: getInputValue('montoReconexcion'),
          nomNumeral: getInputValue('nomNumeral'),
          tipoVigencia: getRadioValue('tipoVigencia'),
          mesesPlazo: getInputValue('mesesPlazo'),
          penalidadTexto: getInputValue('penalidadTexto')
        },

        equipo: {
          tipoEntregaEquipo: getRadioValue('tipoEntregaEquipo'),
          marcaEquipo: getInputValue('marcaEquipo'),
          modeloEquipo: getInputValue('modeloEquipo'),
          numeroSerie: getInputValue('numeroSerie'),
          numeroEquipos: getInputValue('numeroEquipos'),
          costoTotalEquipo: getInputValue('costoTotalEquipo'),
          modalidadPagoEquipo: getRadioValue('modalidadPagoEquipo'),
          costoDiferido: getInputValue('costoDiferido'),
          mesesDiferido: getInputValue('mesesDiferido')
        },

        instalacion: {
          domicilioInstalacion: getInputValue('domicilioInstalacion'),
          fechaInstalacion: getInputValue('fechaInstalacion'),
          horaInstalacion: getInputValue('horaInstalacion'),
          costoInstalacion: getInputValue('costoInstalacion')
        },

        metodoPago: {
          efectivo: document.getElementById('mpEfectivo').checked,
          transferencia: document.getElementById('mpTransferencia').checked,
          deposito: document.getElementById('mpDeposito').checked,
          tiendasServicios: document.getElementById('mpTiendasServicios').checked,
          tarjeta: document.getElementById('mpTarjeta').checked,
          domiciliado: document.getElementById('mpDomiciliado').checked,
          enLinea: document.getElementById('mpEnLinea').checked,
          centrosServicio: document.getElementById('mpCentrosServicio').checked,
          datosMetodoPago: getInputValue('datosMetodoPago')
        },

        tarjeta: {
          autorizaCargoTarjeta: getRadioValue('autorizaCargoTarjeta'),
          mesesCargoTarjeta: getInputValue('mesesCargoTarjeta'),
          banco: getInputValue('banco'),
          numeroTarjeta: getInputValue('numeroTarjeta')
        },

        serviciosAdicionales: [
          {
            descripcion: getInputValue('servicioAdic1Desc'),
            costo: getInputValue('servicioAdic1Costo')
          },
          {
            descripcion: getInputValue('servicioAdic2Desc'),
            costo: getInputValue('servicioAdic2Costo')
          }
        ],

        conceptosFacturables: [
          {
            descripcion: getInputValue('conceptoFact1Desc'),
            costo: getInputValue('conceptoFact1Costo')
          },
          {
            descripcion: getInputValue('conceptoFact2Desc'),
            costo: getInputValue('conceptoFact2Costo')
          }
        ],

        envioElectronico: {
          factura: getRadioValue('envioFactura'),
          cartaDerechos: getRadioValue('envioCartaDerechos'),
          contratoAdhesion: getRadioValue('envioContratoAdhesion'),
          medioElectronico: getRadioValue('medioElectronico'),
          correoElectronico: getInputValue('correoElectronico'),
          otroMedioElectronico: getInputValue('otroMedioElectronico'),
          numeroOtroMedio: getInputValue('numeroOtroMedio')
        },

        usoInformacion: {
          autorizaCederInfo: getRadioValue('autorizaCederInfo'),
          autorizaLlamadasPromo: getRadioValue('autorizaLlamadasPromo')
        },

        cierre: {
          ciudadFirma: getInputValue('ciudadFirma'),
          diaFirma: getInputValue('diaFirma'),
          mesFirma: getInputValue('mesFirma'),
          anioFirma: getInputValue('anioFirma')
        },

        firma: signaturePadPreview && !signaturePadPreview.isEmpty()
          ? signatureCanvas.toDataURL('image/png')
          : '',

        aceptaContratoFibra: document.getElementById('aceptaContratoFibra').checked
      };
    }

    // =========================
    // FIRMA PREVIEW
    // =========================
    const signatureCanvas = document.getElementById('signature-canvas');
    const signaturePreviewWrapper = document.getElementById('signature-preview-wrapper');
    const clearSignaturePreview = document.getElementById('clearSignaturePreview');
    let signaturePadPreview;

    function resizeCanvas(canvas, pad) {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = canvas.getBoundingClientRect();
      canvas.width = rect.width * ratio;
      canvas.height = rect.height * ratio;
      const ctx = canvas.getContext("2d");
      ctx.scale(ratio, ratio);
      if (pad) pad.clear();
    }

    function initPreviewPad() {
      signaturePadPreview = new SignaturePad(signatureCanvas, {
        penColor: "rgb(13, 82, 191)",
        minWidth: 1.8,
        maxWidth: 3.2
      });
      resizeCanvas(signatureCanvas, signaturePadPreview);
    }

    initPreviewPad();
    window.addEventListener('resize', () => resizeCanvas(signatureCanvas, signaturePadPreview));

    clearSignaturePreview.addEventListener('click', () => {
      signaturePadPreview.clear();
      signaturePreviewWrapper.classList.remove('ring-2', 'ring-red-500/30');
    });

    // =========================
    // MODAL FIRMA
    // =========================
    const signatureModal = document.getElementById('signatureModal');
    const openSignatureModal = document.getElementById('openSignatureModal');
    const closeSignatureModal = document.getElementById('closeSignatureModal');
    const clearSignatureModalPad = document.getElementById('clearSignatureModalPad');
    const saveSignatureModal = document.getElementById('saveSignatureModal');
    const signatureCanvasModal = document.getElementById('signature-canvas-modal');

    let signaturePadModal;

    function initModalPad() {
      signaturePadModal = new SignaturePad(signatureCanvasModal, {
        penColor: "rgb(13, 82, 191)",
        minWidth: 2.0,
        maxWidth: 3.8
      });
    }

    function resizeModalCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = signatureCanvasModal.getBoundingClientRect();
      signatureCanvasModal.width = rect.width * ratio;
      signatureCanvasModal.height = rect.height * ratio;
      signatureCanvasModal.getContext("2d").scale(ratio, ratio);

      if (signaturePadModal) {
        signaturePadModal.clear();
      }
    }

    initModalPad();

    openSignatureModal.addEventListener('click', () => {
      signatureModal.classList.remove('hidden');
      setTimeout(() => {
        resizeModalCanvas();
      }, 80);
    });

    closeSignatureModal.addEventListener('click', () => {
      signatureModal.classList.add('hidden');
    });

    clearSignatureModalPad.addEventListener('click', () => {
      if (signaturePadModal) signaturePadModal.clear();
    });

    saveSignatureModal.addEventListener('click', () => {
      if (!signaturePadModal || signaturePadModal.isEmpty()) {
        Swal.fire({
          icon: 'warning',
          title: 'Firma vacía',
          text: 'Primero debes firmar antes de guardar.',
          background: '#071322',
          color: '#fff',
          confirmButtonColor: '#06b6d4'
        });
        return;
      }

      const dataURL = signaturePadModal.toDataURL("image/png");
      const img = new Image();
      img.onload = () => {
        signaturePadPreview.clear();
        const ctx = signatureCanvas.getContext('2d');
        const rect = signatureCanvas.getBoundingClientRect();

        ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const drawWidth = rect.width;
        const drawHeight = rect.height;

        ctx.drawImage(img, 0, 0, drawWidth * ratio, drawHeight * ratio);

        signatureModal.classList.add('hidden');
        signaturePreviewWrapper.classList.remove('ring-2', 'ring-red-500/30');
      };
      img.src = dataURL;
    });

    // =========================
    // FORM
    // =========================
    formFibra.addEventListener('submit', function(e) {
      e.preventDefault();

      if (!validarFormulario()) return;

      const datos = obtenerDatosFibra();
      console.log('DATOS FIBRA:', datos);

      resultado.innerHTML = 'Formulario validado correctamente. Listo para generar PDF.';
      document.getElementById('datos').innerHTML = `<pre class="mt-4 overflow-auto rounded-2xl border border-white/10 bg-[#071322] p-4 text-xs text-cyan-200">${JSON.stringify(datos, null, 2)}</pre>`;

      Swal.fire({
        icon: 'success',
        title: 'Formulario listo',
        text: 'La ventana de fibra ya incluye la firma. El siguiente paso es conectarlo con el PDF.',
        background: '#071322',
        color: '#fff',
        confirmButtonColor: '#06b6d4'
      });
    });

    document.getElementById('btnVistaDatos').addEventListener('click', () => {
      const datos = obtenerDatosFibra();

      Swal.fire({
        title: 'Datos capturados',
        html: `
          <div style="text-align:left; max-height:420px; overflow:auto; background:#020617; border:1px solid rgba(148,163,184,.2); padding:14px; border-radius:12px;">
            <pre style="white-space:pre-wrap; font-size:12px; color:#dbeafe; margin:0;">${JSON.stringify(datos, null, 2)}</pre>
          </div>
        `,
        width: 900,
        background: '#071322',
        color: '#fff',
        confirmButtonColor: '#06b6d4'
      });
    });
  </script>
</body>

</html>