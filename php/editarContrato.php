<?php
include('conexion.php');

$idcontrato = (int) ($_POST['id'] ?? 0);

if ($conexion->connect_error) {
  die('Conexión fallida: ' . $conexion->connect_error);
}

$sql = "SELECT * FROM contratos WHERE idcontrato = $idcontrato";
$result = $conexion->query($sql);

if (!$result || $result->num_rows === 0) {
  echo "<div class='text-red-400'>No se encontró el contrato.</div>";
  exit;
}

$row = $result->fetch_assoc();

$fechaCancInput = '';
if (!empty($row['fecha_cancelacion'])) {
  $fechaCancInput = date('Y-m-d\TH:i', strtotime($row['fecha_cancelacion']));
}

$equiposDevueltosVal = htmlspecialchars($row['equipos_devueltos'] ?? '', ENT_QUOTES, 'UTF-8');

function h($v)
{
  return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function selected($a, $b)
{
  return (string) $a === (string) $b ? 'selected' : '';
}

function checkedAttr($cond)
{
  return $cond ? 'checked' : '';
}

$metodosPago = json_decode($row['metodos_pago'] ?? '[]', true);
if (!is_array($metodosPago)) {
  $metodosPago = array_filter(array_map('trim', explode(',', (string) ($row['metodos_pago'] ?? ''))));
}
?>

<form id="form" class="space-y-6" novalidate>
  <!-- Encabezado -->
  <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl">
    <div class="text-center">
      <h2 class="text-lg font-semibold tracking-wide text-cyan-300">TEKNE SEND.4, S. DE R.L. DE C.V.</h2>
      <p class="mt-2 text-sm leading-6 text-white/80">
        RFC: TSE230302694<br>
        DOMICILIO: EDUARDO ECHEVERRÍA, NÚMERO 21, INTERIOR B, LOCALIDAD MONTE DE LOS JUÁREZ, C.P. 38950, YURIRIA,
        GUANAJUATO.
      </p>
    </div>
  </section>

  <!-- Datos generales -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-4">
        <label for="ncontrato" class="mb-2 block text-sm font-medium text-white/80">Contrato No</label>
        <input type="number" id="ncontrato" name="ncontrato" value="<?= h($row['idcontrato']) ?>" disabled
          class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white/70 outline-none">
      </div>

      <div class="md:col-span-4">
        <label for="fechac" class="mb-2 block text-sm font-medium text-white/80">Fecha</label>
        <input type="date" id="fechac" name="fechac" value="<?= h($row['fecha']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="name" class="mb-2 block text-sm font-medium text-white/80">Nombre del suscriptor</label>
        <input type="text" id="name" name="name" value="<?= h($row['nombre']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="rlegal" class="mb-2 block text-sm font-medium text-white/80">Representante legal</label>
        <input type="text" id="rlegal" name="rlegal" value="<?= h($row['rlegal']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="street" class="mb-2 block text-sm font-medium text-white/80">Calle</label>
        <input type="text" id="street" name="calle" value="<?= h($row['calle']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-2">
        <label for="number" class="mb-2 block text-sm font-medium text-white/80">Número</label>
        <input type="text" id="number" name="number" value="<?= h($row['numero']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-2">
        <label for="colonia" class="mb-2 block text-sm font-medium text-white/80">Colonia</label>
        <input type="text" id="colonia" name="colonia" value="<?= h($row['colonia']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="municipio" class="mb-2 block text-sm font-medium text-white/80">Municipio</label>
        <input type="text" id="municipio" name="municipio" value="<?= h($row['municipio']) ?>" required
          onchange="cambioCiudad()"
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="cp" class="mb-2 block text-sm font-medium text-white/80">CP</label>
        <input type="text" id="cp" name="cp" value="<?= h($row['cp']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-2">
        <label for="estado" class="mb-2 block text-sm font-medium text-white/80">Estado</label>
        <input type="text" id="estado" name="estado" value="<?= h($row['estado']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="telefono" class="mb-2 block text-sm font-medium text-white/80">Teléfono</label>
        <input type="text" id="telefono" name="telefono" value="<?= h($row['telefono']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label class="mb-2 block text-sm font-medium text-white/80">TELÉFONO MÓVIL/FIJO</label>
        <div class="flex gap-3">
          <label class="flex flex-1 items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
            <input type="radio" name="ttipo" id="movil" value="movil" <?= checkedAttr(($row['ttelefono'] ?? '') === 'movil') ?> class="h-4 w-4">
            <span class="text-sm text-white/80">Móvil</span>
          </label>

          <label class="flex flex-1 items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
            <input type="radio" name="ttipo" id="fijo" value="fijo" <?= checkedAttr(($row['ttelefono'] ?? '') === 'fijo') ?> class="h-4 w-4">
            <span class="text-sm text-white/80">Fijo</span>
          </label>
        </div>
      </div>

      <div class="md:col-span-4">
        <label for="rfc" class="mb-2 block text-sm font-medium text-white/80">RFC</label>
        <input type="text" id="rfc" name="rfc" maxlength="13" value="<?= h($row['rfc']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>
    </div>
  </section>

  <!-- Servicio -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Servicio de internet fijo en casa</h3>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-4">
        <label for="tarifa" class="mb-2 block text-sm font-medium text-white/80">Tarifa</label>
        <select id="tarifa" name="tarifa"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">

          <option value="">Selecciona un paquete</option>

          <!-- Antena -->
          <option value="1" data-precio="250" <?= selected($row['tarifa'], '1') ?>>Residencial 7 MB/s</option>
          <option value="2" data-precio="300" <?= selected($row['tarifa'], '2') ?>>Residencial 10 MB/s</option>
          <option value="3" data-precio="350" <?= selected($row['tarifa'], '3') ?>>Residencial 15 MB/s</option>
          <option value="4" data-precio="400" <?= selected($row['tarifa'], '4') ?>>Residencial 20 MB/s</option>
          <option value="7" data-precio="450" <?= selected($row['tarifa'], '7') ?>>Residencial 30 MB/s</option>
          <option value="5" data-precio="500" <?= selected($row['tarifa'], '5') ?>>Residencial 40 MB/s</option>
          <option value="6" data-precio="600" <?= selected($row['tarifa'], '6') ?>>Residencial 50 MB/s</option>

          <!-- Fibra -->
          <option value="8" data-precio="300" <?= selected($row['tarifa'], '8') ?>>BBS Fiber 30</option>
          <option value="9" data-precio="400" <?= selected($row['tarifa'], '9') ?>>BBS Fiber 50</option>
          <option value="10" data-precio="500" <?= selected($row['tarifa'], '10') ?>>BBS Fiber 80</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="totalm" class="mb-2 block text-sm font-medium text-white/80">Total mensualidad</label>
        <input type="text" id="totalm" name="totalm" value="<?= h($row['tmensualidad']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="reconexion" class="mb-2 block text-sm font-medium text-white/80">Aplica tarifa por
          reconexión</label>
        <select id="reconexion" name="reconexion"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
          <option value="1" <?= selected($row['reconexion'], '1') ?>>No</option>
          <option value="2" <?= selected($row['reconexion'], '2') ?>>Sí</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="descm" class="mb-2 block text-sm font-medium text-white/80">Monto por desconexión</label>
        <input type="text" id="descm" name="descm" value="<?= h($row['mdesconexion']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="nom_numeral" class="mb-2 block text-sm font-medium text-white/80">Nombre del numeral</label>
        <input type="text" id="nom_numeral" name="nom_numeral" value="<?= h($row['nom_numeral']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="tipo_vigencia" class="mb-2 block text-sm font-medium text-white/80">Tipo de vigencia</label>
        <select id="tipo_vigencia" name="tipo_vigencia"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
          <option value="">Selecciona una opción</option>
          <option value="indefinido" <?= selected($row['tipo_vigencia'], 'indefinido') ?>>Indefinido</option>
          <option value="plazo_forzoso" <?= selected($row['tipo_vigencia'], 'plazo_forzoso') ?>>Plazo forzoso</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="pmeses" class="mb-2 block text-sm font-medium text-white/80">Plazo mínimo en meses</label>
        <input type="number" id="pmeses" name="pmeses" min="0" value="<?= h($row['plazo']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-8">
        <label for="penalidad_texto" class="mb-2 block text-sm font-medium text-white/80">Texto de penalidad</label>
        <textarea id="penalidad_texto" name="penalidad_texto" rows="3"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40"><?= h($row['penalidad_texto']) ?></textarea>
      </div>
    </div>

    <div class="mt-5 rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4 text-sm text-cyan-100/90">
      En el estado de cuenta y/o factura se podrá visualizar la fecha de corte del servicio y fecha de pago.
    </div>
  </section>

  <!-- Equipo -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Datos del equipo</h3>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-4">
        <label for="modemt" class="mb-2 block text-sm font-medium text-white/80">Modem entregado</label>
        <select id="modemt" name="modemt"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
          <option value="1" <?= selected($row['modeme'], '1') ?>>Comodato</option>
          <option value="2" <?= selected($row['modeme'], '2') ?>>Compraventa</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="tipo_entrega_equipo" class="mb-2 block text-sm font-medium text-white/80">Tipo de entrega de
          equipo</label>
        <select id="tipo_entrega_equipo" name="tipo_entrega_equipo"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
          <option value="">Selecciona una opción</option>
          <option value="comodato" <?= selected($row['tipo_entrega_equipo'], 'comodato') ?>>Comodato</option>
          <option value="compraventa" <?= selected($row['tipo_entrega_equipo'], 'compraventa') ?>>Compraventa</option>
          <option value="prestamo" <?= selected($row['tipo_entrega_equipo'], 'prestamo') ?>>Préstamo</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="marca" class="mb-2 block text-sm font-medium text-white/80">Marca</label>
        <input type="text" id="marca" name="marca" value="<?= h($row['marca']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="modelo" class="mb-2 block text-sm font-medium text-white/80">Modelo</label>
        <input type="text" id="modelo" name="modelo" value="<?= h($row['modelo']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="serie" class="mb-2 block text-sm font-medium text-white/80">Número de serie</label>
        <input type="text" id="serie" name="serie" value="<?= h($row['nserie']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="nequipos" class="mb-2 block text-sm font-medium text-white/80">Número de equipos</label>
        <input type="number" id="nequipos" name="nequipos" min="1" value="<?= h($row['nequipo']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="tpago" class="mb-2 block text-sm font-medium text-white/80">Pago único / Mes</label>
        <select id="tpago" name="tpago"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
          <option value="1" <?= selected($row['pagoum'], '1') ?>>Pago Único</option>
          <option value="2" <?= selected($row['pagoum'], '2') ?>>Mes</option>
          <option value="3" <?= selected($row['pagoum'], '3') ?>>Vacío</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="cequipos" class="mb-2 block text-sm font-medium text-white/80">Cantidad a pagar por equipo</label>
        <input type="number" step="0.01" id="cequipos" name="cequipos" value="<?= h($row['pequipo']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="costo_diferido" class="mb-2 block text-sm font-medium text-white/80">Costo diferido</label>
        <input type="number" step="0.01" id="costo_diferido" name="costo_diferido"
          value="<?= h($row['costo_diferido']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="meses_diferido" class="mb-2 block text-sm font-medium text-white/80">Meses diferido</label>
        <input type="number" id="meses_diferido" name="meses_diferido" min="0" value="<?= h($row['meses_diferido']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>
    </div>

    <div class="mt-5 rounded-2xl border border-amber-400/15 bg-amber-400/5 p-4 text-sm text-amber-100/90">
      Garantía de cumplimiento de obligación. Pagaré para garantizar la devolución del equipo entregado solo en
      comodato.
    </div>
  </section>

  <!-- Instalación -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Instalación de equipo</h3>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-6">
        <label for="domicilioi" class="mb-2 block text-sm font-medium text-white/80">Domicilio de la instalación</label>
        <input type="text" id="domicilioi" name="domicilioi" value="<?= h($row['domicilioi']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-3">
        <label for="fechai" class="mb-2 block text-sm font-medium text-white/80">Fecha</label>
        <input type="date" id="fechai" name="fechai" value="<?= h($row['fechai']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-3">
        <label for="horai" class="mb-2 block text-sm font-medium text-white/80">Hora</label>
        <input type="time" id="horai" name="horai" value="<?= h($row['hora']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="costoi" class="mb-2 block text-sm font-medium text-white/80">Costo</label>
        <input type="text" id="costoi" name="costoi" value="<?= h($row['costoi']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>
    </div>

    <div class="mt-5 rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4 text-sm text-cyan-100/90">
      El proveedor deberá efectuar las instalaciones y empezar a prestar el servicio en un plazo que no exceda de 10
      días hábiles a partir de la firma del contrato.
    </div>
  </section>

  <!-- Método de pago -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Método de pago</h3>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-4">
        <label class="mb-2 block text-sm font-medium text-white/80">Autorización por cargo a tarjeta</label>
        <div class="space-y-3">
          <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
            <input type="radio" name="acargo" value="si" <?= checkedAttr(($row['autorizacion'] ?? '') === 'si') ?>
              class="h-4 w-4">
            <span class="text-sm text-white/80">Sí</span>
          </label>
          <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
            <input type="radio" name="acargo" value="no" <?= checkedAttr(($row['autorizacion'] ?? '') !== 'si') ?>
              class="h-4 w-4">
            <span class="text-sm text-white/80">No</span>
          </label>
        </div>
      </div>

      <div class="md:col-span-4">
        <label for="mpago" class="mb-2 block text-sm font-medium text-white/80">Método de pago principal</label>
        <select id="mpago" name="mpago"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
          <option value="1" <?= selected($row['mpago'], '1') ?>>Efectivo</option>
          <option value="2" <?= selected($row['mpago'], '2') ?>>Tarjeta de crédito/débito</option>
          <option value="3" <?= selected($row['mpago'], '3') ?>>Transferencia bancaria</option>
          <option value="4" <?= selected($row['mpago'], '4') ?>>Depósito a cuenta bancaria</option>
        </select>
      </div>

      <div class="md:col-span-4">
        <label for="cmes" class="mb-2 block text-sm font-medium text-white/80">Vigencia de cargos / Mes</label>
        <input type="number" id="cmes" name="cmes" min="1" value="<?= h($row['vigencia']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <?php
$metodo = trim((string)($row['metodos_pago'] ?? ''));

$mapMetodoPago = [
    'efectivo'      => '1',
    'tarjeta'       => '2',
    'transferencia' => '3',
    'deposito'      => '4',
    'tiendas'       => '5',
    'domiciliado'   => '6',
    'enlinea'       => '7',
    'centros'       => '8',
];

if (str_starts_with($metodo, '[')) {
    $arr = json_decode($metodo, true);
    $metodo = $arr[0] ?? '';
}

if (isset($mapMetodoPago[$metodo])) {
    $metodo = $mapMetodoPago[$metodo];
}
?>

<div class="md:col-span-12">
  <label class="mb-2 block text-sm font-medium text-white/80">
    Método de pago permitido
  </label>

  <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="efectivo" <?= checkedAttr($metodo === '1') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Efectivo</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="transferencia" <?= checkedAttr($metodo === '3') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Transferencia bancaria</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="deposito" <?= checkedAttr($metodo === '4') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Depósito a cuenta bancaria</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="tiendas" <?= checkedAttr($metodo === '5') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Pago en tiendas de servicios</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="tarjeta" <?= checkedAttr($metodo === '2') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Tarjeta de crédito o débito</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="domiciliado" <?= checkedAttr($metodo === '6') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Domiciliado con tarjeta</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="enlinea" <?= checkedAttr($metodo === '7') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Pago en línea</span>
    </label>

    <label class="flex min-h-[58px] items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
      <input type="radio" name="metodoPago" value="centros" <?= checkedAttr($metodo === '8') ?> class="h-4 w-4 shrink-0">
      <span class="text-sm text-white/80">Pago en tiendas o centros de servicio</span>
    </label>
  </div>
</div>

      <div class="md:col-span-12">
        <label for="datos_metodo_pago" class="mb-2 block text-sm font-medium text-white/80">Datos del método de
          pago</label>
        <textarea id="datos_metodo_pago" name="datos_metodo_pago" rows="3"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40"><?= h($row['datos_metodo_pago']) ?></textarea>
      </div>

      <div class="md:col-span-4">
        <label for="banco" class="mb-2 block text-sm font-medium text-white/80">Banco</label>
        <input type="text" id="banco" name="banco" value="<?= h($row['banco']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="ntarjeta" class="mb-2 block text-sm font-medium text-white/80">No. de tarjeta</label>
        <input type="text" id="ntarjeta" name="ntarjeta" maxlength="30" value="<?= h($row['notarjeta']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="correo_electronico" class="mb-2 block text-sm font-medium text-white/80">Correo electrónico</label>
        <input type="email" id="correo_electronico" name="correo_electronico"
          value="<?= h($row['correo_electronico']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="otro_medio_electronico" class="mb-2 block text-sm font-medium text-white/80">Otro medio
          electrónico</label>
        <input type="text" id="otro_medio_electronico" name="otro_medio_electronico"
          value="<?= h($row['otro_medio_electronico']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="numero_otro_medio" class="mb-2 block text-sm font-medium text-white/80">Número otro medio</label>
        <input type="text" id="numero_otro_medio" name="numero_otro_medio" value="<?= h($row['numero_otro_medio']) ?>"
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
      <div class="md:col-span-4">
        <label for="sadicional1" class="mb-2 block text-sm font-medium text-white/80">Servicio adicional 1</label>
        <input type="text" id="sadicional1" name="sadicional1" value="<?= h($row['sadicional1']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-8">
        <label for="sdescripcion1" class="mb-2 block text-sm font-medium text-white/80">Descripción</label>
        <input type="text" id="sdescripcion1" name="sdescripcion1" value="<?= h($row['dadicional1']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="scosto1" class="mb-2 block text-sm font-medium text-white/80">Costo</label>
        <input type="text" id="scosto1" name="scosto1" value="<?= h($row['costoa1']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="hidden md:col-span-8 md:block"></div>

      <div class="md:col-span-4">
        <label for="sadicional2" class="mb-2 block text-sm font-medium text-white/80">Servicio adicional 2</label>
        <input type="text" id="sadicional2" name="sadicional2" value="<?= h($row['sadicional2']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-8">
        <label for="sdescripcion2" class="mb-2 block text-sm font-medium text-white/80">Descripción</label>
        <input type="text" id="sdescripcion2" name="sdescripcion2" value="<?= h($row['dadicional2']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="scosto2" class="mb-2 block text-sm font-medium text-white/80">Costo</label>
        <input type="text" id="scosto2" name="scosto2" value="<?= h($row['costoa2']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>
    </div>
  </section>

  <!-- Facturables -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Facturables</h3>
      <p class="mt-1 text-sm text-white/60">Ejemplo: costo por cambio de domicilio, costos administrativos adicionales.
      </p>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-4">
        <label for="fadicional1" class="mb-2 block text-sm font-medium text-white/80">Facturable 1</label>
        <input type="text" id="fadicional1" name="fadicional1" value="<?= h($row['sfacturable1']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-8">
        <label for="fdescripcion1" class="mb-2 block text-sm font-medium text-white/80">Descripción</label>
        <input type="text" id="fdescripcion1" name="fdescripcion1" value="<?= h($row['dfacturable1']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="fcosto1" class="mb-2 block text-sm font-medium text-white/80">Costo</label>
        <input type="text" id="fcosto1" name="fcosto1" value="<?= h($row['costof1']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="hidden md:col-span-8 md:block"></div>

      <div class="md:col-span-4">
        <label for="fadicional2" class="mb-2 block text-sm font-medium text-white/80">Facturable 2</label>
        <input type="text" id="fadicional2" name="fadicional2" value="<?= h($row['sfacturable2']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-8">
        <label for="fdescripcion2" class="mb-2 block text-sm font-medium text-white/80">Descripción</label>
        <input type="text" id="fdescripcion2" name="fdescripcion2" value="<?= h($row['dfacturable2']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-4">
        <label for="fcosto2" class="mb-2 block text-sm font-medium text-white/80">Costo</label>
        <input type="text" id="fcosto2" name="fcosto2" value="<?= h($row['costof2']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>
    </div>
  </section>

  <!-- Permisos y aceptación -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Autorizaciones y aceptación</h3>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
        <input type="checkbox" id="autoriza_ceder_info" name="autoriza_ceder_info"
          <?= checkedAttr((string) $row['autoriza_ceder_info'] === '1') ?> class="h-4 w-4 rounded">
        <span class="text-sm text-white/80">Autoriza ceder información</span>
      </label>

      <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
        <input type="checkbox" id="autoriza_llamadas_promo" name="autoriza_llamadas_promo"
          <?= checkedAttr((string) $row['autoriza_llamadas_promo'] === '1') ?> class="h-4 w-4 rounded">
        <span class="text-sm text-white/80">Autoriza llamadas promocionales</span>
      </label>

      <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
        <input type="checkbox" id="acepta_contrato" name="acepta_contrato"
          <?= checkedAttr((string) $row['acepta_contrato'] === '1') ?> class="h-4 w-4 rounded">
        <span class="text-sm text-white/80">Acepta contrato</span>
      </label>
    </div>
  </section>

  <!-- Documentos -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Recepción de documentos</h3>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
        <input type="checkbox" id="ccontrato" name="ccontrato" <?= checkedAttr((string) $row['ccontrato'] === '1') ?>
          class="h-4 w-4 rounded">
        <span class="text-sm text-white/80">Copia de contrato y carátula</span>
      </label>

      <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3">
        <input type="checkbox" id="cderechos" name="cderechos" <?= checkedAttr((string) $row['cderechos'] === '1') ?>
          class="h-4 w-4 rounded">
        <span class="text-sm text-white/80">Carta de derechos mínimos</span>
      </label>
    </div>
  </section>

  <!-- Firma -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="mb-5">
      <h3 class="text-lg font-semibold text-cyan-300">Datos de firma</h3>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-4">
        <label for="ciudad" class="mb-2 block text-sm font-medium text-white/80">Ciudad</label>
        <input type="text" id="ciudad" name="ciudad" value="<?= h($row['cciudad']) ?>" required
          class="requerido w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-2">
        <label for="dia_firma" class="mb-2 block text-sm font-medium text-white/80">Día firma</label>
        <input type="text" id="dia_firma" name="dia_firma" value="<?= h($row['dia_firma']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-3">
        <label for="mes_firma" class="mb-2 block text-sm font-medium text-white/80">Mes firma</label>
        <input type="text" id="mes_firma" name="mes_firma" value="<?= h($row['mes_firma']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>

      <div class="md:col-span-3">
        <label for="anio_firma" class="mb-2 block text-sm font-medium text-white/80">Año firma</label>
        <input type="text" id="anio_firma" name="anio_firma" value="<?= h($row['anio_firma']) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
      </div>
    </div>
  </section>

  <!-- Cancelación -->
  <section class="rounded-3xl border border-white/10 bg-[#0b1a2d] p-6 shadow-xl">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-12">
      <div class="md:col-span-12">
        <h3 class="text-lg font-semibold text-cyan-300">Cancelación (opcional)</h3>
      </div>

      <div class="md:col-span-8">
        <label for="equipos_devueltos" class="mb-2 block text-sm font-medium text-white/80">Equipos devueltos</label>
        <textarea id="equipos_devueltos" name="equipos_devueltos" rows="3"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40"
          placeholder="Ej: ONT Huawei SN12345, Router TP-Link SN67890..."><?= $equiposDevueltosVal ?></textarea>
        <p class="mt-2 text-xs text-white/50">Si el contrato está cancelado, aquí queda el detalle de los equipos
          recibidos.</p>
      </div>

      <div class="md:col-span-4">
        <label for="fecha_cancelacion" class="mb-2 block text-sm font-medium text-white/80">Fecha de cancelación</label>
        <input type="datetime-local" id="fecha_cancelacion" name="fecha_cancelacion" value="<?= h($fechaCancInput) ?>"
          class="w-full rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-white outline-none focus:border-cyan-400/40">
        <p class="mt-2 text-xs text-white/50">Déjalo vacío para mantenerla en NULL.</p>
      </div>
    </div>
  </section>

  <!-- Acciones -->
  <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl">
    <div class="flex flex-col gap-4 md:flex-row md:items-center">
      <button type="button" onclick="updateContrato()"
        class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
        Actualizar datos
      </button>

      <div id="resultado"
        class="min-h-[48px] flex-1 rounded-2xl border border-white/10 bg-[#071322] px-4 py-3 text-sm text-white/80">
      </div>
    </div>
  </section>
</form>