<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../../menu/login/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Generación de contrato Fibra</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/jspdf.min.js"></script>
    <script src="../js/signature_pad.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #071322;
            color: #e5eefc;
            font-family: Arial, Helvetica, sans-serif;
        }

        .glass-card {
            background: linear-gradient(180deg, rgba(0, 15, 53, .96) 0%, rgba(7, 19, 34, .98) 100%);
            border: 1px solid rgba(125, 211, 252, .18);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
        }

        .input-dark,
        .select-dark,
        .textarea-dark {
            width: 100%;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(148, 163, 184, .25);
            color: #f8fafc;
            border-radius: 12px;
            padding: 12px 14px;
            outline: none;
            transition: .2s ease;
        }

        .input-dark:focus,
        .select-dark:focus,
        .textarea-dark:focus {
            border-color: rgba(56, 189, 248, .8);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, .15);
        }

        .input-dark::placeholder,
        .textarea-dark::placeholder {
            color: #94a3b8;
        }

        .field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .14) !important;
        }

        .field-error-box {
            outline: 1px solid rgba(239, 68, 68, .75);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .12);
            border-radius: 12px;
        }

        .section-title {
            background: linear-gradient(90deg, rgba(14, 165, 233, .16), rgba(14, 165, 233, .05));
            border: 1px solid rgba(125, 211, 252, .15);
        }

        .mini-check {
            width: 18px;
            height: 18px;
            accent-color: #38bdf8;
        }

        .radio-line {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e2e8f0;
            cursor: pointer;
        }

        .chk-line {
            display: flex;
            align-items: start;
            gap: 10px;
            color: #e2e8f0;
            cursor: pointer;
            line-height: 1.2rem;
        }

        .btn-main {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: white;
            border: 0;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s ease;
            box-shadow: 0 12px 30px rgba(37, 99, 235, .25);
        }

        .btn-main:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .btn-soft {
            background: rgba(255, 255, 255, .05);
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, .2);
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s ease;
        }

        .btn-soft:hover {
            background: rgba(255, 255, 255, .08);
        }

        .label-main {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 7px;
        }

        .hint {
            font-size: 12px;
            color: #94a3b8;
        }

        .divider-glow {
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(56, 189, 248, .35), transparent);
        }

        .scroll-fibra::-webkit-scrollbar {
            width: 10px;
        }

        .scroll-fibra::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .04);
            border-radius: 999px;
        }

        .scroll-fibra::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .55);
            border-radius: 999px;
        }

        .scroll-fibra::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, .75);
        }

        select.select-dark option {
            background-color: #0f172a;
            color: #f8fafc;
        }

        select.select-dark {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include("../includes/sidebar.php"); ?>

    <div class="max-w-7xl mx-auto px-4 py-6 md:px-6 lg:px-8">

        <!-- ENCABEZADO -->
        <div class="glass-card rounded-3xl p-5 md:p-7 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-2xl bg-sky-400/15 border border-sky-300/20 flex items-center justify-center text-sky-300 text-xl">
                            📄
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-white">Generación de contrato Fibra Óptica
                            </h1>
                            <p class="text-slate-300 mt-1">Captura la información del suscriptor y del servicio para
                                preparar el contrato.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:w-auto">
                    <button type="button" class="btn-soft" id="btnLimpiar">
                        Limpiar formulario
                    </button>
                    <button type="button" class="btn-main" id="btnGenerarContrato">
                        Generar contrato
                    </button>
                    <button type="button" id="btnPreviewFibra"
                        class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-400">
                        Vista previa PDF fibra
                    </button>
                </div>
            </div>
        </div>

        <form id="formContratoFibra" class="space-y-6" autocomplete="on">
            <div class="grid grid-cols-1 md:grid-cols-[453px_265px] gap-4 items-start">
                <div class="space-y-2">
                    <label for="idcontrato" class="text-sm font-semibold text-slate-200">
                        Número de contrato *
                    </label>
                    <input type="number" id="idcontrato"
                        class="requerido w-full rounded-xl border border-slate-700 bg-slate-900/80 px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                        placeholder="Cargando..." min="1" step="1" inputmode="numeric">
                    <p class="text-xs text-slate-400">
                        Se carga automáticamente, pero puedes modificarlo si necesitas generar un contrato específico o
                        atrasado.
                    </p>
                </div>

                <div class="flex items-start pt-[30px]"> 
                    <button type="button" id="btnRecargarContrato"
                        class="w-full md:w-auto rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold px-4 py-3 transition">
                        Obtener siguiente número
                    </button>
                </div>
            </div>


            <!-- DATOS DEL SUSCRIPTOR -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">1. Datos del suscriptor</h2>
                    <p class="text-sm text-slate-300">Información principal del cliente o suscriptor.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-main">Nombre / Denominación *</label>
                        <input type="text" id="nombre" class="input-dark requerido" placeholder="Nombre o razón social"
                            value="">
                    </div>

                    <div>
                        <label class="label-main">Apellido paterno *</label>
                        <input type="text" id="apellidoPaterno" class="input-dark requerido" value=""
                            placeholder="Apellido paterno">
                    </div>

                    <div>
                        <label class="label-main">Apellido materno *</label>
                        <input type="text" id="apellidoMaterno" class="input-dark requerido" value=""
                            placeholder="Apellido materno">
                    </div>
                </div>
            </section>

            <!-- DOMICILIO -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">2. Domicilio</h2>
                    <p class="text-sm text-slate-300">Datos del domicilio del suscriptor.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4">
                        <label class="label-main">Calle *</label>
                        <input type="text" id="calle" class="input-dark requerido" placeholder="Calle" value="">
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-main"># Ext. *</label>
                        <input type="text" id="numeroExterior" class="input-dark requerido" placeholder="Exterior"
                            value="">
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-main"># Int.</label>
                        <input type="text" id="numeroInterior" class="input-dark" placeholder="Interior" value="">
                    </div>

                    <div class="md:col-span-4">
                        <label class="label-main">Colonia *</label>
                        <input type="text" id="colonia" class="input-dark requerido" placeholder="Colonia" value="">
                    </div>

                    <div class="md:col-span-4">
                        <label class="label-main">Alcaldía / Municipio *</label>
                        <input type="text" id="municipio" class="input-dark requerido" placeholder="Municipio" value="">
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">Estado *</label>
                        <input type="text" id="estado" class="input-dark requerido" placeholder="Estado" value="">
                    </div>

                    <div class="md:col-span-2">
                        <label class="label-main">C.P. *</label>
                        <input type="text" id="cp" class="input-dark requerido" placeholder="C.P." value="">
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">RFC</label>
                        <input type="text" id="rfc" class="input-dark" placeholder="RFC">
                    </div>
                </div>
            </section>

            <!-- CONTACTO -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">3. Contacto</h2>
                    <p class="text-sm text-slate-300">Selecciona el tipo de teléfono que irá marcado en el contrato.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    <div class="md:col-span-2" data-radio-group="tipoTelefono">
                        <label class="label-main">Tipo de teléfono *</label>
                        <div class="flex flex-wrap gap-6 mt-2">
                            <label class="radio-line">
                                <input type="radio" name="tipoTelefono" value="fijo" class="mini-check requerido-radio">
                                <span>Teléfono fijo</span>
                            </label>
                            <label class="radio-line">
                                <input type="radio" name="tipoTelefono" value="movil"
                                    class="mini-check requerido-radio">
                                <span>Móvil</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="label-main">Número telefónico *</label>
                        <input type="tel" id="telefono" maxlength="10" pattern="\d{10}" inputmode="numeric"
                            class="input-dark requerido" placeholder="Teléfono" value="">
                    </div>
                </div>
            </section>

            <!-- SERVICIO -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">4. Servicio de internet fijo</h2>
                    <p class="text-sm text-slate-300">Datos del paquete, mensualidad y vigencia del servicio.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label class="label-main">Paquete / oferta *</label>
                        <select id="descripcionPaquete" class="select-dark requerido">
                            <option value="">Selecciona un paquete</option>
                            <!-- Antena -->
                            <option value="1" data-precio="250">Residencial 7 MB/s</option>
                            <option value="2" data-precio="300">BBS Air 10</option>
                            <option value="3" data-precio="350">Residencial 15 MB/s</option>
                            <option value="4" data-precio="400">BBS Air 20</option>
                            <option value="7" data-precio="450">BBS Air 30</option>
                            <option value="5" data-precio="500">Residencial 40 MB/s</option>
                            <option value="6" data-precio="600">Residencial 50 MB/s</option>
                            <!-- Fibra -->
                            <option value="8" data-precio="300">BBS Fiber 30</option>
                            <option value="9" data-precio="400">BBS Fiber 50</option>
                            <option value="10" data-precio="500">BBS Fiber 80</option>

                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">Total de la mensualidad *</label>
                        <input type="text" id="mensualidad" class="input-dark requerido" placeholder="$0.00">
                    </div>

                    <div class="md:col-span-4">
                        <label class="label-main">Fecha de pago *</label>
                        <input type="text" id="fechaPago" class="input-dark requerido"
                            placeholder="Ej. día 05 de cada mes" value="día 05 de cada mes">
                    </div>

                    <div class="md:col-span-6" data-radio-group="aplicaReconexcion">
                        <label class="label-main">¿Aplica tarifa por reconexión? *</label>
                        <div class="flex flex-wrap gap-6 mt-2">
                            <label class="radio-line">
                                <input type="radio" name="aplicaReconexcion" value="2"
                                    class="mini-check requerido-radio">
                                <span>Sí</span>
                            </label>
                            <label class="radio-line">
                                <input type="radio" name="aplicaReconexcion" value="1"
                                    class="mini-check requerido-radio">
                                <span>No</span>
                            </label>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">Monto reconexión</label>
                        <input type="text" id="montoReconexcion" class="input-dark" placeholder="$0.00">
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">NOM numeral</label>
                        <input type="text" id="nomNumeral" class="input-dark" placeholder="5.1.2.1">
                    </div>

                    <div class="md:col-span-6" data-radio-group="tipoVigencia">
                        <label class="label-main">Vigencia / penalidad *</label>
                        <div class="space-y-3 mt-2">
                            <label class="radio-line">
                                <input type="radio" name="tipoVigencia" value="indefinido"
                                    class="mini-check requerido-radio">
                                <span>Indefinido: sin penalidad</span>
                            </label>
                            <label class="radio-line">
                                <input type="radio" name="tipoVigencia" value="plazo_forzoso"
                                    class="mini-check requerido-radio">
                                <span>Plazo forzoso</span>
                            </label>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">Meses plazo forzoso</label>
                        <input type="number" id="mesesPlazo" class="input-dark" placeholder="Meses" value="">
                    </div>

                    <div class="md:col-span-3">
                        <label class="label-main">Penalidad</label>
                        <input type="text" id="penalidadTexto" class="input-dark" placeholder="20% de meses pendientes">
                    </div>
                </div>
            </section>

            <!-- EQUIPO / INSTALACIÓN -->
            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                <div class="glass-card rounded-3xl p-5 md:p-6">
                    <div class="section-title rounded-2xl px-4 py-3 mb-5">
                        <h2 class="text-lg font-bold text-white">5. Equipo terminal</h2>
                        <p class="text-sm text-slate-300">Datos del equipo entregado al suscriptor.</p>
                    </div>

                    <div class="space-y-4">
                        <div data-radio-group="tipoEntregaEquipo">
                            <label class="label-main">Equipo entregado en *</label>
                            <div class="flex flex-wrap gap-6 mt-2">
                                <label class="radio-line">
                                    <input type="radio" name="tipoEntregaEquipo" value="1"
                                        class="mini-check requerido-radio">
                                    <span>Comodato</span>
                                </label>
                                <label class="radio-line">
                                    <input type="radio" name="tipoEntregaEquipo" value="2"
                                        class="mini-check requerido-radio">
                                    <span>Compraventa</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-main">Marca *</label>
                                <input type="text" id="marcaEquipo" class="input-dark requerido" placeholder="Marca"
                                    value="">
                            </div>

                            <div>
                                <label class="label-main">Modelo *</label>
                                <input type="text" id="modeloEquipo" class="input-dark requerido" placeholder="Modelo"
                                    value="">
                            </div>

                            <div>
                                <label class="label-main">Número de serie *</label>
                                <input type="text" id="numeroSerie" class="input-dark requerido" placeholder="Serie"
                                    value="">
                            </div>

                            <div>
                                <label class="label-main">Número de equipos *</label>
                                <input type="number" id="numeroEquipos" class="input-dark requerido"
                                    placeholder="Cantidad" value="">
                            </div>
                        </div>

                        <div class="divider-glow my-2"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-main">Costo total</label>
                                <input type="text" id="costoTotalEquipo" class="input-dark" placeholder="$0.00"
                                    value="">
                            </div>

                            <div data-radio-group="modalidadPagoEquipo">
                                <label class="label-main">Modalidad de pago</label>
                                <div class="flex flex-wrap gap-6 mt-2">
                                    <label class="radio-line">
                                        <input type="radio" name="modalidadPagoEquipo" value="1" class="mini-check">
                                        <span>Pago único</span>
                                    </label>
                                    <label class="radio-line">
                                        <input type="radio" name="modalidadPagoEquipo" value="2" class="mini-check">
                                        <span>Diferido</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="label-main">Costo diferido</label>
                                <input type="text" id="costoDiferido" class="input-dark" placeholder="$0.00" value="">
                            </div>

                            <div>
                                <label class="label-main">Meses diferido</label>
                                <input type="number" id="mesesDiferido" class="input-dark" placeholder="Meses" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-5 md:p-6">
                    <div class="section-title rounded-2xl px-4 py-3 mb-5">
                        <h2 class="text-lg font-bold text-white">6. Instalación del equipo terminal</h2>
                        <p class="text-sm text-slate-300">Datos de instalación y costo del servicio.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="label-main">Domicilio de la instalación *</label>
                            <input type="text" id="domicilioInstalacion" class="input-dark requerido" value=""
                                placeholder="Domicilio de instalación">
                        </div>

                        <div>
                            <label class="label-main">Fecha *</label>
                            <input type="date" id="fechaInstalacion" class="input-dark requerido" value="">
                        </div>

                        <div>
                            <label class="label-main">Hora *</label>
                            <input type="time" id="horaInstalacion" class="input-dark requerido" value="">
                        </div>

                        <div class="md:col-span-2">
                            <label class="label-main">Costo *</label>
                            <input type="text" id="costoInstalacion" class="input-dark requerido" placeholder="$0.00"
                                value="1500">
                        </div>
                    </div>
                </div>
            </section>

            <!-- MÉTODO DE PAGO -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">7. Método de pago</h2>
                    <p class="text-sm text-slate-300">Marca uno o varios métodos si así lo requiere tu contrato.</p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="efectivo" id="mpEfectivo" class="mini-check">
                            <span>Efectivo</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="transferencia" id="mpTransferencia"
                                class="mini-check">
                            <span>Transferencia bancaria</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="deposito" id="mpDeposito" class="mini-check">
                            <span>Depósito a cuenta bancaria</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="tiendas" id="mpTiendasServicios"
                                class="mini-check">
                            <span>Pago en tiendas de servicios</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="tarjeta" id="mpTarjeta" class="mini-check">
                            <span>Tarjeta de crédito o débito</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="domiciliado" id="mpDomiciliado"
                                class="mini-check">
                            <span>Domiciliado con tarjeta</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="enlinea" id="mpEnLinea" class="mini-check">
                            <span>Pago en línea</span>
                        </label>

                        <label class="chk-line">
                            <input type="radio" name="metodoPago" value="centros" id="mpCentrosServicio"
                                class="mini-check">
                            <span>Pago en tiendas o centros de servicio</span>
                        </label>

                    </div>

                    <div>
                        <label class="label-main">Datos para el método de pago elegido</label>
                        <textarea id="datosMetodoPago" rows="8" class="textarea-dark"
                            placeholder="Ej. banco, referencia, observaciones, datos de la forma de pago..."></textarea>
                    </div>
                </div>
            </section>

            <!-- CARGO A TARJETA -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">8. Autorización para cargo de tarjeta</h2>
                    <p class="text-sm text-slate-300">Solo se llena si aplica cargo mensual a tarjeta de crédito o
                        débito.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2" data-radio-group="autorizaCargoTarjeta">
                        <label class="label-main">¿Autoriza el cargo? *</label>
                        <div class="flex flex-wrap gap-6 mt-2">
                            <label class="radio-line">
                                <input type="radio" name="autorizaCargoTarjeta" value="si"
                                    class="mini-check requerido-radio">
                                <span>Sí</span>
                            </label>
                            <label class="radio-line">
                                <input type="radio" name="autorizaCargoTarjeta" value="no"
                                    class="mini-check requerido-radio">
                                <span>No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="label-main">Vigencia de cargos por meses</label>
                        <input type="number" id="mesesCargoTarjeta" class="input-dark" placeholder="Meses" value="">
                    </div>
                </div>
            </section>

            <!-- BANCO / TARJETA -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">9. Datos bancarios</h2>
                    <p class="text-sm text-slate-300">Solo si el método de pago elegido los requiere.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-main">Banco</label>
                        <input type="text" id="banco" class="input-dark" placeholder="Nombre del banco" value="">
                    </div>

                    <div>
                        <label class="label-main">Número de tarjeta</label>
                        <input type="text" id="numeroTarjeta" class="input-dark" placeholder="Número de tarjeta"
                            value="">
                    </div>
                </div>
            </section>

            <!-- SERVICIOS ADICIONALES -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">10. Servicios adicionales</h2>
                    <p class="text-sm text-slate-300">Captura hasta dos servicios adicionales.</p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="rounded-2xl border border-slate-700/60 p-4 bg-white/2">
                        <h3 class="font-bold text-sky-300 mb-3">Servicio adicional 1</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="label-main">Descripción</label>
                                <input type="text" id="servicioAdic1Desc" class="input-dark" placeholder="Descripción"
                                    value="">
                            </div>
                            <div>
                                <label class="label-main">Costo</label>
                                <input type="text" id="servicioAdic1Costo" class="input-dark" placeholder="$0.00"
                                    value="">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-700/60 p-4 bg-white/2">
                        <h3 class="font-bold text-sky-300 mb-3">Servicio adicional 2</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="label-main">Descripción</label>
                                <input type="text" id="servicioAdic2Desc" class="input-dark" placeholder="Descripción"
                                    value="">
                            </div>
                            <div>
                                <label class="label-main">Costo</label>
                                <input type="text" id="servicioAdic2Costo" class="input-dark" placeholder="$0.00"
                                    value="">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CONCEPTOS FACTURABLES -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">11. Conceptos facturables</h2>
                    <p class="text-sm text-slate-300">Ejemplo: cambio de domicilio, administrativos, cargos extra, etc.
                    </p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="rounded-2xl border border-slate-700/60 p-4 bg-white/2">
                        <h3 class="font-bold text-sky-300 mb-3">Concepto facturable 1</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="label-main">Descripción</label>
                                <input type="text" id="conceptoFact1Desc" class="input-dark" placeholder="Descripción"
                                    value="">
                            </div>
                            <div>
                                <label class="label-main">Costo</label>
                                <input type="text" id="conceptoFact1Costo" class="input-dark" placeholder="$0.00"
                                    value="">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-700/60 p-4 bg-white/2">
                        <h3 class="font-bold text-sky-300 mb-3">Concepto facturable 2</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="label-main">Descripción</label>
                                <input type="text" id="conceptoFact2Desc" class="input-dark" placeholder="Descripción"
                                    value="">
                            </div>
                            <div>
                                <label class="label-main">Costo</label>
                                <input type="text" id="conceptoFact2Costo" class="input-dark" placeholder="$0.00"
                                    value="">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ENVÍO ELECTRÓNICO -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">12. Envío por medios electrónicos</h2>
                    <p class="text-sm text-slate-300">Autorizaciones para envío digital de documentos.</p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="space-y-5">
                        <div data-radio-group="envioFactura">
                            <label class="label-main">Factura *</label>
                            <div class="flex gap-6 mt-2">
                                <label class="radio-line"><input type="radio" name="envioFactura" value="si"
                                        class="mini-check requerido-radio"> <span>Sí</span></label>
                                <label class="radio-line"><input type="radio" name="envioFactura" value="no"
                                        class="mini-check requerido-radio"> <span>No</span></label>
                            </div>
                        </div>

                        <div data-radio-group="envioCartaDerechos">
                            <label class="label-main">Carta de derechos mínimos *</label>
                            <div class="flex gap-6 mt-2">
                                <label class="radio-line"><input type="radio" name="envioCartaDerechos" value="si"
                                        class="mini-check requerido-radio"> <span>Sí</span></label>
                                <label class="radio-line"><input type="radio" name="envioCartaDerechos" value="no"
                                        class="mini-check requerido-radio"> <span>No</span></label>
                            </div>
                        </div>

                        <div data-radio-group="envioContratoAdhesion">
                            <label class="label-main">Contrato de adhesión *</label>
                            <div class="flex gap-6 mt-2">
                                <label class="radio-line"><input type="radio" name="envioContratoAdhesion" value="si"
                                        class="mini-check requerido-radio"> <span>Sí</span></label>
                                <label class="radio-line"><input type="radio" name="envioContratoAdhesion" value="no"
                                        class="mini-check requerido-radio"> <span>No</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div data-radio-group="medioElectronico">
                            <label class="label-main">Medio electrónico autorizado *</label>
                            <div class="flex flex-wrap gap-6 mt-2">
                                <label class="radio-line">
                                    <input type="radio" name="medioElectronico" value="correo"
                                        class="mini-check requerido-radio">
                                    <span>Correo electrónico</span>
                                </label>
                                <label class="radio-line">
                                    <input type="radio" name="medioElectronico" value="otro"
                                        class="mini-check requerido-radio">
                                    <span>Otro</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="label-main">Correo electrónico</label>
                            <input type="email" id="correoElectronico" class="input-dark"
                                placeholder="correo@dominio.com" value="">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-main">Otro medio</label>
                                <input type="text" id="otroMedioElectronico" class="input-dark"
                                    placeholder="WhatsApp, SMS, etc." value="">
                            </div>

                            <div>
                                <label class="label-main">Número</label>
                                <input type="text" id="numeroOtroMedio" class="input-dark" placeholder="Número"
                                    value="">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- USO DE INFORMACIÓN -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">13. Autorización para uso de información</h2>
                    <p class="text-sm text-slate-300">Sección de autorizaciones del suscriptor.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-2xl border border-slate-700/60 p-4 bg-white/2"
                        data-radio-group="autorizaCederInfo">
                        <p class="text-slate-200 mb-3">1. ¿Autoriza que su información sea cedida o transmitida a
                            terceros con fines mercadotécnicos o publicitarios?</p>
                        <div class="flex gap-6 mt-2">
                            <label class="radio-line"><input type="radio" name="autorizaCederInfo" value="si"
                                    class="mini-check requerido-radio"> <span>Sí</span></label>
                            <label class="radio-line"><input type="radio" name="autorizaCederInfo" value="no"
                                    class="mini-check requerido-radio"> <span>No</span></label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-700/60 p-4 bg-white/2"
                        data-radio-group="autorizaLlamadasPromo">
                        <p class="text-slate-200 mb-3">2. ¿Acepta recibir llamadas del proveedor de promociones del
                            servicio o paquetes?</p>
                        <div class="flex gap-6 mt-2">
                            <label class="radio-line"><input type="radio" name="autorizaLlamadasPromo" value="si"
                                    class="mini-check requerido-radio"> <span>Sí</span></label>
                            <label class="radio-line"><input type="radio" name="autorizaLlamadasPromo" value="no"
                                    class="mini-check requerido-radio"> <span>No</span></label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CIERRE -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">14. Datos de cierre</h2>
                    <p class="text-sm text-slate-300">Datos finales para la tercera hoja del contrato.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="label-main">Ciudad donde se firma *</label>
                        <input type="text" id="ciudadFirma" class="input-dark requerido" placeholder="Ciudad"
                            value="">
                    </div>

                    <div>
                        <label class="label-main">Día *</label>
                        <input type="text" id="diaFirma" class="input-dark requerido" placeholder="Día" value="">
                    </div>

                    <div>
                        <label class="label-main">Mes *</label>
                        <input type="text" id="mesFirma" class="input-dark requerido" placeholder="Mes" value="">
                    </div>

                    <div>
                        <label class="label-main">Año *</label>
                        <input type="text" id="anioFirma" class="input-dark requerido" placeholder="Año" value="">
                    </div>
                </div>
            </section>
            <!-- CONTRATO / VISOR -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">Contrato</h2>
                    <p class="text-sm text-slate-300">Revisa el contenido del contrato base antes de continuar.</p>
                </div>

                <div class="rounded-2xl border border-sky-200/10 bg-[#021024] p-3">
                    <div id="visorContratoFibra"
                        class="scroll-fibra max-h-[650px] overflow-y-auto rounded-2xl border border-sky-200/10 bg-[#04101f] p-3 space-y-4">
                        <!-- Imágenes del contrato -->
                    </div>
                </div>

                <div id="boxAceptaContratoFibra"
                    class="mt-5 rounded-2xl bg-cyan-900/20 border border-cyan-300/10 p-4 transition-all">
                    <label class="flex items-start gap-3 text-slate-200 cursor-pointer">
                        <input type="checkbox" id="aceptaContratoFibra"
                            class="mt-1 h-5 w-5 rounded border-slate-500 bg-slate-900 text-sky-400">
                        <span>He leído y estoy de acuerdo con lo especificado en el contrato anterior.</span>
                    </label>
                </div>
            </section>
            <!-- FIRMA -->
            <section class="glass-card rounded-3xl p-5 md:p-6">
                <div class="section-title rounded-2xl px-4 py-3 mb-5">
                    <h2 class="text-lg font-bold text-white">15. Firma del suscriptor</h2>
                    <p class="text-sm text-slate-300">Firma en el recuadro o abre la vista completa para firmar más
                        cómodo.</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm leading-6 text-white/80 mb-5">
                    LA PRESENTE CARÁTULA SE RIGE CONFORME A LAS CLÁUSULAS DEL CONTRATO DE ADHESIÓN REGISTRADO EN
                    PROFECO.
                    LA FIRMA INSERTA ES LA ACEPTACIÓN DE LA PRESENTE CARÁTULA Y CLAUSULADO DEL CONTRATO.
                </div>

                <div class="rounded-2xl border border-white/10 bg-[#071322] p-3">
                    <div id="signature-preview-wrapper"
                        class="overflow-hidden rounded-2xl border border-dashed border-white/20 bg-white cursor-pointer">
                        <canvas id="signature-canvas" class="block h-[200px] w-full"></canvas>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-3">
                        <button type="button" id="openSignatureModal"
                            class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-400">
                            Firmar en pantalla completa
                        </button>

                        <button type="button" id="clearSignaturePreview"
                            class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">
                            Limpiar
                        </button>
                    </div>
                </div>
            </section>

            <!-- PIE -->
            <section class="glass-card rounded-3xl p-5 md:p-6 mb-8">
                <div class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-white font-bold text-lg">Formulario base listo</h3>
                        <p class="text-slate-300 text-sm mt-1">
                            En el siguiente paso conectamos este formulario al generador PDF y al sistema de llenado
                            rápido.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" class="btn-soft" id="btnVistaDatos">Ver datos capturados</button>
                        <button type="button" class="btn-main" id="btnGenerarContratoBottom">Generar contrato</button>
                    </div>
                </div>
            </section>
        </form>
    </div>
    <!-- Modal firma fullscreen -->
    <div id="signatureModal" class="fixed inset-0 z-[9999] hidden bg-black/90 backdrop-blur-sm">
        <div class="flex h-full w-full flex-col">
            <div class="flex items-center justify-between border-b border-white/10 bg-[#071322] px-4 py-3">
                <div>
                    <h3 class="text-base font-semibold text-white">Firma en pantalla completa</h3>
                    <p class="text-xs text-white/60">Puedes girar el teléfono para firmar más cómodo.</p>
                </div>

                <button type="button" id="closeSignatureModal"
                    class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                    Cerrar
                </button>
            </div>

            <div class="flex-1 p-3 md:p-5">
                <div class="flex h-full flex-col rounded-2xl border border-white/10 bg-[#0b1a2d] p-3">
                    <div class="mb-3 flex flex-wrap gap-3">
                        <button type="button" id="clearSignatureModalPad"
                            class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                            Limpiar
                        </button>

                        <button type="button" id="saveSignatureModal"
                            class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-400">
                            Guardar firma
                        </button>
                    </div>

                    <div
                        class="min-h-0 flex-1 overflow-hidden rounded-2xl border border-dashed border-white/20 bg-white">
                        <canvas id="signature-canvas-modal" class="block h-full w-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://momentjs.com/downloads/moment.min.js"></script>
    <script src="../js/fibra.js"></script>
    <script src="../js/signature_pad.umd.min.js"></script>
    <!-- <script src="../js/fibra_preview.js"></script> -->
    <script src="../js/swaldark.js"></script>
    <script src="../js/sidebar.js"></script>
</body>

</html>