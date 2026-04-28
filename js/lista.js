/* ===================== lista.js ===================== */
/* Funciones de listado, edición y PDF de contrato (NO cancelaciones) */

const tablaEl = document.getElementById("tabla");
const respuestaEl = document.getElementById("respuesta");
const busquedaEl = document.getElementById("busqueda");
const filtroEstadoEl = document.getElementById("filtro-estado");
const btnBuscarEl = document.getElementById("btnBuscar");
const modalAgregarEl = document.getElementById("modalAgregar");
const modalEditarEl = document.getElementById("modalEditar");
const modalBodyAgregarEl = document.getElementById("modal");
const modalBodyEditarEl = document.getElementById("modal2");

const modalAgregar = modalAgregarEl
  ? new bootstrap.Modal(modalAgregarEl)
  : null;
const modalEditar = modalEditarEl ? new bootstrap.Modal(modalEditarEl) : null;

function escapeHtml(text) {
  return String(text ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function mostrarMensajeTabla(mensaje, icono = "bi-search") {
  if (!tablaEl) return;

  tablaEl.innerHTML = `
    <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
      <div>
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
          <i class="bi ${icono} text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-white">${mensaje}</h3>
      </div>
    </div>
  `;
}

async function cargarTabla() {
  const estado = filtroEstadoEl?.value || "activo";
  const busqueda = busquedaEl?.value?.trim() || "";

  mostrarMensajeTabla("Cargando contratos...", "bi-hourglass-split");

  try {
    const response = await fetch("../php/cargarTabla.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ estado, busqueda }),
    });

    const html = await response.text();

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    tablaEl.innerHTML = html?.trim()
      ? html
      : `
        <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
          <div>
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
              <i class="bi bi-inbox text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-white">Sin resultados</h3>
            <p class="mt-2 text-sm text-white/55">No se encontraron contratos con los filtros seleccionados.</p>
          </div>
        </div>
      `;
  } catch (error) {
    console.error("Error al cargar la tabla:", error);
    tablaEl.innerHTML = `
      <div class="rounded-2xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
        Error al cargar la tabla.
      </div>
    `;
  }
}

/* --- Utilidades compartidas con el PDF de contrato --- */
async function loadImage(url, typeHint) {
  const res = await fetch(url, { cache: "no-store" });
  if (!res.ok) throw new Error(`HTTP ${res.status} al cargar ${url}`);
  const ct = (res.headers.get("Content-Type") || "").toLowerCase();
  if (!ct.startsWith("image/"))
    throw new Error(`No es imagen (${ct}) -> ${url}`);
  const blob = await res.blob();
  const mime = (blob.type || typeHint || "").toLowerCase();

  const dataUrl = await new Promise((resolve, reject) => {
    const fr = new FileReader();
    fr.onload = () => resolve(fr.result);
    fr.onerror = reject;
    fr.readAsDataURL(blob);
  });

  return { dataUrl, mime };
}

const mimeToFormat = (mime) => (mime && mime.includes("png") ? "PNG" : "JPEG");

const addImg = (pdf, img, x, y, w, h) => {
  const format = mimeToFormat(img.mime);
  pdf.addImage(img.dataUrl, format, x, y, w, h);
};

const pad2 = (n) => String(n).padStart(2, "0");

function formateaFechaMX(f) {
  const d = new Date((f || "").replace(" ", "T"));
  if (isNaN(d)) return String(f || "");
  return `${pad2(d.getDate())}/${pad2(d.getMonth() + 1)}/${d.getFullYear()} ${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
}

/* -------------------- PDF del CONTRATO FIBRA -------------------- */

function writePdf(pdf, text, cfg, size = 7, style = "bold") {
  if (!cfg || text === undefined || text === null || text === "") return;
  pdf.setFontSize(size);
  pdf.setFont("helvetica", style);
  pdf.text(String(text), cfg.x, cfg.y);
}

function splitWritePdf(pdf, text, x, y, maxWidth, lineHeight = 9, size = 7) {
  if (!text) return;
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(size);
  const lines = pdf.splitTextToSize(String(text), maxWidth);
  pdf.text(lines, x, y, { lineHeightFactor: lineHeight / size });
}

function markPdf(pdf, active, x, y, r = 3) {
  if (!active) return;
  pdf.circle(x, y, r, "F");
}

function textoPaqueteFibra(valor) {
  const map = {
    1: "Residencial 7 MB/s",
    2: "BBS Air 10",
    3: "Residencial 15 MB/s",
    4: "BBS Air 20",
    7: "BBS Air 30",
    5: "Residencial 40 MB/s",
    6: "Residencial 50 MB/s",
    8: "BBS Fiber 30",
    9: "BBS Fiber 50",
    10: "BBS Fiber 80",
  };

  return map[String(valor)] || "";
}

function textoMetodoPago(valor) {
  const map = {
    1: "Efectivo",
    2: "Tarjeta de crédito o débito",
    3: "Transferencia bancaria",
    4: "Depósito a cuenta bancaria",
    5: "Pago en tiendas de servicios",
    6: "Domiciliado con tarjeta",
    7: "Pago en línea",
    8: "Pago en tiendas o centros de servicio",
  };

  return map[String(valor)] || "";
}

function partesNombre(nombreCompleto) {
  const partes = String(nombreCompleto || "")
    .trim()
    .split(/\s+/);

  return {
    nombre: partes.slice(0, Math.max(1, partes.length - 2)).join(" "),
    apellidoPaterno: partes.length >= 2 ? partes[partes.length - 2] : "",
    apellidoMaterno: partes.length >= 3 ? partes[partes.length - 1] : "",
  };
}

function B64PNG(b) {
  if (!b) return "";
  return b.startsWith("data:") ? b : `data:image/png;base64,${b}`;
}

const mapaFibraReimpresion = {
  p1: {
    nombre: { x: 30, y: 160 },
    apellidoPaterno: { x: 230, y: 160 },
    apellidoMaterno: { x: 400, y: 160 },

    calle: { x: 30, y: 197 },
    numeroExterior: { x: 202, y: 197 },
    numeroInterior: { x: 228, y: 197 },
    colonia: { x: 250, y: 197 },
    municipio: { x: 340, y: 197 },

    estado: { x: 435, y: 197 },
    cp: { x: 515, y: 197 },
    rfc: { x: 340, y: 222 },
    telefono: { x: 208, y: 222 },

    paquete: { x: 65, y: 330 },
    mensualidad: { x: 285, y: 312 },
    fechaPago: { x: 482, y: 265 },

    montoReconexcion: { x: 285, y: 360 },
    mesesPlazo: { x: 497, y: 324 },
    penalidadTexto: { x: 320, y: 315 },

    marcaEquipo: { x: 132, y: 466 },
    modeloEquipo: { x: 132, y: 486 },
    numeroSerie: { x: 132, y: 497 },
    numeroEquipos: { x: 132, y: 510 },

    costoTotalEquipo: { x: 176, y: 524 },
    costoDiferido: { x: 160, y: 542 },
    mesesDiferido: { x: 240, y: 542 },

    domicilioInstalacion: { x: 423, y: 447 },
    fechaInstalacion: { x: 354, y: 463 },
    horaInstalacion: { x: 464, y: 463 },
    costoInstalacion: { x: 350, y: 475 },

    datosMetodoPago: { x: 220, y: 625 },
    mesesCargoTarjeta: { x: 425, y: 722 },
    firmaSuscriptor: { x: 235, y: 726, w: 110, h: 26 },
  },

  p2: {
    banco: { x: 120, y: 154 },
    numeroTarjeta: { x: 400, y: 154 },

    servicio1Name: { x: 110, y: 200 },
    servicio1Costo: { x: 260, y: 216 },
    servicio1Desc: { x: 40, y: 236 },

    servicio2Name: { x: 380, y: 200 },
    servicio2Costo: { x: 495, y: 216 },
    servicio2Desc: { x: 350, y: 236 },

    fact1Name: { x: 110, y: 284 },
    fact1Costo: { x: 260, y: 300 },
    fact1Desc: { x: 40, y: 320 },

    fact2Name: { x: 380, y: 284 },
    fact2Costo: { x: 495, y: 300 },
    fact2Desc: { x: 350, y: 320 },

    ciudadFirma: { x: 255, y: 496 },
    diaFirma: { x: 323, y: 496 },
    mesFirma: { x: 360, y: 496 },
    anioFirma: { x: 415, y: 496 },

    correoElectronico: { x: 200, y: 572 },
    otroMedioElectronico: { x: 130, y: 592 },
    numeroOtroMedio: { x: 250, y: 592 },

    firmaProveedor: { x: 260, y: 490, w: 120, h: 16 },
    firmaSuscriptor: { x: 325, y: 500, w: 120, h: 16 },
    firmaProvedor2: { x: 482, y: 590, w: 70, h: 10 },
    firmaProvedor3: { x: 200, y: 650, w: 40, h: 8 },
    firmaProvedor4: { x: 80, y: 684, w: 40, h: 8 },
  },

  p3: {
    ciudadFirma: { x: 222, y: 345 },
    diaFirma: { x: 315, y: 345 },
    mesFirma: { x: 390, y: 345 },
    anioFirma: { x: 468, y: 345 },
    numeroContrato: { x: 294, y: 322 },
    firmaProveedor: { x: 115, y: 385, w: 130, h: 28 },
    firmaSuscriptor: { x: 355, y: 385, w: 130, h: 28 },
  },
};

function convertirContratoDBaFibra(raw) {
  const S = (v, d = "") => (v ?? d).toString();
  const nombrePartes = partesNombre(raw.nombre);

  return {
    contrato: {
      idcontrato: S(raw.idcontrato),
    },

    suscriptor: {
      nombre: nombrePartes.nombre,
      apellidoPaterno: nombrePartes.apellidoPaterno,
      apellidoMaterno: nombrePartes.apellidoMaterno,
    },

    domicilio: {
      calle: S(raw.calle),
      numeroExterior: S(raw.numero),
      numeroInterior: "",
      colonia: S(raw.colonia),
      municipio: S(raw.municipio),
      estado: S(raw.estado),
      cp: S(raw.cp),
      rfc: S(raw.rfc),
    },

    contacto: {
      telefono: S(raw.telefono),
      tipoTelefono: S(raw.ttelefono),
    },

    servicio: {
      descripcionPaquete: S(raw.tarifa),
      nombrePaquete: textoPaqueteFibra(raw.tarifa),
      mensualidad: S(raw.tmensualidad),
      fechaPago: "1 al 5 de cada mes",
      aplicaReconexcion: S(raw.reconexion),
      montoReconexcion: S(raw.mdesconexion),
      nomNumeral: S(raw.nom_numeral),
      tipoVigencia: S(raw.tipo_vigencia),
      mesesPlazo: S(raw.plazo),
      penalidadTexto: S(raw.penalidad_texto),
    },

    equipo: {
      tipoEntrega: S(raw.tipo_entrega_equipo || raw.modeme),
      marca: S(raw.marca),
      modelo: S(raw.modelo),
      numeroSerie: S(raw.nserie),
      numeroEquipos: S(raw.nequipo),
      costoTotal: S(raw.pequipo),
      modalidadPago: S(raw.pagoum),
      costoDiferido: S(raw.costo_diferido),
      mesesDiferido: S(raw.meses_diferido),
    },

    instalacion: {
      domicilio: S(raw.domicilioi),
      fecha: S(raw.fechai),
      hora: S(raw.hora),
      costo: S(raw.costoi),
    },

    metodoPago: {
      tipo: S(raw.metodos_pago),
      datosMetodo: S(
        raw.datos_metodo_pago || textoMetodoPago(raw.metodos_pago),
      ),
    },

    autorizacionCargoTarjeta: {
      autoriza: S(raw.autorizacion),
      meses: S(raw.vigencia),
      banco: S(raw.banco),
      numeroTarjeta: S(raw.notarjeta),
    },

    adicionales: {
      servicio1: S(raw.sadicional1),
      descripcion1: S(raw.dadicional1),
      costo1: S(raw.costoa1),
      servicio2: S(raw.sadicional2),
      descripcion2: S(raw.dadicional2),
      costo2: S(raw.costoa2),
    },

    facturables: {
      servicio1: S(raw.sfacturable1),
      descripcion1: S(raw.dfacturable1),
      costo1: S(raw.costof1),
      servicio2: S(raw.sfacturable2),
      descripcion2: S(raw.dfacturable2),
      costo2: S(raw.costof2),
    },

    envioElectronico: {
      correo: S(raw.correo_electronico),
      otro: S(raw.otro_medio_electronico),
      numero: S(raw.numero_otro_medio),
    },

    usoInformacion: {
      cfactura: S(raw.cfactura),
      ccontrato: S(raw.ccontrato),
      cderechos: S(raw.cderechos),
      cederInformacion: S(raw.autoriza_ceder_info),
      recibirLlamadas: S(raw.autoriza_llamadas_promo),
    },

    cierre: {
      ciudadFirma: S(raw.cciudad),
      dia: S(raw.dia_firma),
      mes: S(raw.mes_firma),
      anio: S(raw.anio_firma),
    },

    firmaCliente: B64PNG(S(raw.firma1)),
  };
}

async function imprimirContratoFibra(datosFormulario, opciones = {}) {
  const m = mapaFibraReimpresion;

  try {
    const [
      image1,
      image2,
      image3,
      image4,
      image5,
      image6,
      image7,
      image8,
      image9,
      firmaProveedor,
    ] = await Promise.all([
      loadImage("../img/fibra/Contrato-0001.jpg"),
      loadImage("../img/fibra/Contrato-0002.jpg"),
      loadImage("../img/fibra/Contrato-0003.jpg"),
      loadImage("../img/fibra/Contrato-0004.jpg"),
      loadImage("../img/fibra/Contrato-0005.jpg"),
      loadImage("../img/fibra/Contrato-0006.jpg"),
      loadImage("../img/fibra/Contrato-0007.jpg"),
      loadImage("../img/fibra/Contrato-0008.jpg"),
      loadImage("../img/fibra/Contrato-0009.jpg"),
      loadImage("../img/firma-s.png"),
    ]);

    const pdf = new jsPDF("p", "pt", "letter");

    const addPageImage = (img) => {
      addImg(pdf, img, 0, 0, 565, 792);
    };

    const d = datosFormulario;
    let tipoPago = d.metodoPago.tipo;

    // Si viene como ["deposito"]
    if (typeof tipoPago === "string" && tipoPago.startsWith("[")) {
      try {
        tipoPago = JSON.parse(tipoPago)[0];
      } catch (e) {
        tipoPago = "";
      }
    }

    addPageImage(image1);

    writePdf(pdf, d.suscriptor.nombre, m.p1.nombre);
    writePdf(pdf, d.suscriptor.apellidoPaterno, m.p1.apellidoPaterno);
    writePdf(pdf, d.suscriptor.apellidoMaterno, m.p1.apellidoMaterno);

    writePdf(pdf, d.domicilio.calle, m.p1.calle);
    writePdf(pdf, d.domicilio.numeroExterior, m.p1.numeroExterior);
    writePdf(pdf, d.domicilio.numeroInterior, m.p1.numeroInterior);
    writePdf(pdf, d.domicilio.colonia, m.p1.colonia);
    writePdf(pdf, d.domicilio.municipio, m.p1.municipio);
    writePdf(pdf, d.domicilio.estado, m.p1.estado);
    writePdf(pdf, d.domicilio.cp, m.p1.cp);
    writePdf(pdf, d.domicilio.rfc, m.p1.rfc);
    writePdf(pdf, d.contacto.telefono, m.p1.telefono);

    markPdf(pdf, d.contacto.tipoTelefono === "movil", 98, 218);
    markPdf(pdf, d.contacto.tipoTelefono === "fijo", 146, 218);

    writePdf(pdf, d.servicio.nombrePaquete, m.p1.paquete);
    writePdf(pdf, d.servicio.mensualidad, m.p1.mensualidad);
    writePdf(pdf, d.servicio.fechaPago, m.p1.fechaPago);

    markPdf(pdf, String(d.servicio.aplicaReconexcion) === "2", 208.5, 373);
    markPdf(pdf, String(d.servicio.aplicaReconexcion) === "1", 250, 372.5);
    writePdf(pdf, d.servicio.montoReconexcion, m.p1.montoReconexcion);

    markPdf(pdf, d.servicio.tipoVigencia === "indefinido", 410, 299);
    markPdf(pdf, d.servicio.tipoVigencia === "plazo_forzoso", 410, 320);

    writePdf(pdf, d.servicio.mesesPlazo, m.p1.mesesPlazo);
    //splitWritePdf(pdf, d.servicio.penalidadTexto, 320, 315, 160, 8, 6);

    markPdf(pdf, String(d.equipo.tipoEntrega) === "1", 146.5, 436.5);
    markPdf(pdf, String(d.equipo.tipoEntrega) === "2", 146.5, 448.5);

    writePdf(pdf, d.equipo.marca, m.p1.marcaEquipo);
    writePdf(pdf, d.equipo.modelo, m.p1.modeloEquipo);
    writePdf(pdf, d.equipo.numeroSerie, m.p1.numeroSerie);
    writePdf(pdf, d.equipo.numeroEquipos, m.p1.numeroEquipos);
    writePdf(pdf, d.equipo.costoTotal, m.p1.costoTotalEquipo);
    writePdf(pdf, d.equipo.costoDiferido, m.p1.costoDiferido);
    writePdf(pdf, d.equipo.mesesDiferido, m.p1.mesesDiferido);

    writePdf(pdf, d.instalacion.domicilio, m.p1.domicilioInstalacion);
    writePdf(pdf, d.instalacion.fecha, m.p1.fechaInstalacion);
    writePdf(pdf, d.instalacion.hora, m.p1.horaInstalacion);
    writePdf(pdf, d.instalacion.costo, m.p1.costoInstalacion);

    splitWritePdf(pdf, d.metodoPago.datosMetodo, 220, 625, 250, 8, 6);
    writePdf(pdf, d.autorizacionCargoTarjeta.meses, m.p1.mesesCargoTarjeta);

    writePdf(pdf, d.instalacion.costo, m.p1.costoInstalacion);
    console.log("metodos_pago DB:", d.metodoPago.tipo);
    markPdf(pdf, tipoPago === "efectivo", 34, 588);
    markPdf(pdf, tipoPago === "transferencia", 34, 600);
    markPdf(pdf, tipoPago === "deposito", 32, 610.5);
    markPdf(pdf, tipoPago === "tiendas", 32, 620.5);
    markPdf(pdf, tipoPago === "tarjeta", 32, 631);
    markPdf(pdf, tipoPago === "domiciliado", 32, 641);
    markPdf(pdf, tipoPago === "enlinea", 32, 651.5);
    markPdf(pdf, tipoPago === "centros", 32, 662);

    splitWritePdf(pdf, d.metodoPago.datosMetodo, 220, 625, 420, 8, 6);

    markPdf(pdf, d.autorizacionCargoTarjeta.autoriza === "si", 147.5, 708);
    markPdf(pdf, d.autorizacionCargoTarjeta.autoriza === "no", 177.5, 708);

    writePdf(pdf, d.autorizacionCargoTarjeta.meses, m.p1.mesesCargoTarjeta);

    if (d.firmaCliente) {
      pdf.addImage(
        d.firmaCliente,
        "PNG",
        m.p1.firmaSuscriptor.x,
        m.p1.firmaSuscriptor.y,
        m.p1.firmaSuscriptor.w,
        m.p1.firmaSuscriptor.h,
      );
    }

    pdf.addPage();
    addPageImage(image2);

    writePdf(pdf, d.autorizacionCargoTarjeta.banco, m.p2.banco);
    writePdf(pdf, d.autorizacionCargoTarjeta.numeroTarjeta, m.p2.numeroTarjeta);

    writePdf(pdf, d.adicionales.servicio1, m.p2.servicio1Name);
    writePdf(pdf, d.adicionales.costo1, m.p2.servicio1Costo);
    splitWritePdf(
      pdf,
      d.adicionales.descripcion1,
      m.p2.servicio1Desc.x,
      m.p2.servicio1Desc.y,
      200,
    );

    writePdf(pdf, d.adicionales.servicio2, m.p2.servicio2Name);
    writePdf(pdf, d.adicionales.costo2, m.p2.servicio2Costo);
    splitWritePdf(
      pdf,
      d.adicionales.descripcion2,
      m.p2.servicio2Desc.x,
      m.p2.servicio2Desc.y,
      180,
    );

    writePdf(pdf, d.facturables.servicio1, m.p2.fact1Name);
    writePdf(pdf, d.facturables.costo1, m.p2.fact1Costo);
    splitWritePdf(
      pdf,
      d.facturables.descripcion1,
      m.p2.fact1Desc.x,
      m.p2.fact1Desc.y,
      200,
    );

    writePdf(pdf, d.facturables.servicio2, m.p2.fact2Name);
    writePdf(pdf, d.facturables.costo2, m.p2.fact2Costo);
    splitWritePdf(
      pdf,
      d.facturables.descripcion2,
      m.p2.fact2Desc.x,
      m.p2.fact2Desc.y,
      180,
    );

    //writePdf(pdf, d.cierre.ciudadFirma, m.p2.ciudadFirma);
    //writePdf(pdf, d.cierre.dia, m.p2.diaFirma);
    //writePdf(pdf, d.cierre.mes, m.p2.mesFirma);
    //writePdf(pdf, d.cierre.anio, m.p2.anioFirma);

    writePdf(pdf, d.envioElectronico.correo, m.p2.correoElectronico);
    writePdf(pdf, d.envioElectronico.otro, m.p2.otroMedioElectronico);
    writePdf(pdf, d.envioElectronico.numero, m.p2.numeroOtroMedio);

   markPdf(pdf, String(d.usoInformacion.cfactura) === "1", 118, 538.5);
markPdf(pdf, String(d.usoInformacion.cfactura) !== "1", 156, 538.5);

markPdf(pdf, String(d.usoInformacion.cderechos) === "1", 325, 538.5);
markPdf(pdf, String(d.usoInformacion.cderechos) !== "1", 361, 538.5);

markPdf(pdf, String(d.usoInformacion.ccontrato) === "1", 484, 538.5);
markPdf(pdf, String(d.usoInformacion.ccontrato) !== "1", 518.5, 538.5);

// Medio electrónico autorizado
markPdf(pdf, !!d.envioElectronico.correo, 101.5, 571.5);
markPdf(pdf, !!d.envioElectronico.otro, 101.5, 592);

// Autorización para uso de información
markPdf(pdf, String(d.usoInformacion.cederInformacion) === "1", 128, 641);
markPdf(pdf, String(d.usoInformacion.cederInformacion) !== "1", 168, 641);

markPdf(pdf, String(d.usoInformacion.recibirLlamadas) === "1", 168, 675);
markPdf(pdf, String(d.usoInformacion.recibirLlamadas) !== "1", 212, 675);

// Firma principal de página 2: aquí va CLIENTE, no proveedor
if (d.firmaCliente) {
  pdf.addImage(
    d.firmaCliente,
    "PNG",
    m.p2.firmaProveedor.x,
    m.p2.firmaProveedor.y,
    m.p2.firmaProveedor.w,
    m.p2.firmaProveedor.h
  );

  pdf.addImage(
    d.firmaCliente,
    "PNG",
    m.p2.firmaProvedor2.x,
    m.p2.firmaProvedor2.y,
    m.p2.firmaProvedor2.w,
    m.p2.firmaProvedor2.h
  );

  pdf.addImage(
    d.firmaCliente,
    "PNG",
    m.p2.firmaProvedor3.x,
    m.p2.firmaProvedor3.y,
    m.p2.firmaProvedor3.w,
    m.p2.firmaProvedor3.h
  );

  pdf.addImage(
    d.firmaCliente,
    "PNG",
    m.p2.firmaProvedor4.x,
    m.p2.firmaProvedor4.y,
    m.p2.firmaProvedor4.w,
    m.p2.firmaProvedor4.h
  );
}

    pdf.addPage();
    addPageImage(image3);

    writePdf(pdf, d.contrato.idcontrato, m.p3.numeroContrato);
    writePdf(pdf, d.cierre.ciudadFirma, m.p3.ciudadFirma);
    writePdf(pdf, d.cierre.dia, m.p3.diaFirma);
    writePdf(pdf, d.cierre.mes, m.p3.mesFirma);
    writePdf(pdf, d.cierre.anio, m.p3.anioFirma);

    pdf.addImage(
      firmaProveedor.dataUrl,
      "PNG",
      m.p3.firmaProveedor.x,
      m.p3.firmaProveedor.y,
      m.p3.firmaProveedor.w,
      m.p3.firmaProveedor.h,
    );

    if (d.firmaCliente) {
      pdf.addImage(
        d.firmaCliente,
        "PNG",
        m.p3.firmaSuscriptor.x,
        m.p3.firmaSuscriptor.y,
        m.p3.firmaSuscriptor.w,
        m.p3.firmaSuscriptor.h,
      );
    }

    pdf.addPage();
    addPageImage(image4);
    pdf.addPage();
    addPageImage(image5);
    pdf.addPage();
    addPageImage(image6);
    pdf.addPage();
    addPageImage(image7);
    pdf.addPage();
    addPageImage(image8);
    pdf.addPage();
    addPageImage(image9);

    if (opciones.returnBlob) {
      return pdf.output("blob");
    }

    window.open(pdf.output("bloburl"), "_blank");
  } catch (error) {
    console.error(error);
    Swal.fire({
      ...swalDark,
      title: "No se pudo generar el contrato",
      text: error.message || "Ocurrió un error al reimprimir el contrato.",
      icon: "error",
      width: "38rem",
    });
  }
}

async function descargarContrato(id) {
  try {
    const body = new URLSearchParams({ id });

    const response = await fetch("../php/imprimirPDF.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      },
      body,
    });

    const raw = await response.json();

    if (!response.ok || raw.error || raw.status === "error") {
      throw new Error(
        raw.message || "No se pudo obtener la información del contrato.",
      );
    }

    const datosFibra = convertirContratoDBaFibra(raw);
    await imprimirContratoFibra(datosFibra);
  } catch (error) {
    console.error("imprimirPDF.php falló:", error);
    Swal.fire({
      ...swalDark,
      title: "No se pudo obtener el contrato",
      text:
        error.message ||
        "Ocurrió un error al obtener la información del contrato.",
      icon: "error",
      width: "38rem",
    });
  }
}

async function addContract(id) {
  try {
    const body = new URLSearchParams({ id });

    const response = await fetch("../php/agregarCliente.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      },
      body,
    });

    const html = await response.text();
    modalBodyAgregarEl.innerHTML = html;
    modalAgregar?.show();
  } catch (error) {
    console.error(error);
    if (respuestaEl) {
      respuestaEl.innerHTML = `<div class="text-red-400 text-sm">Error al cargar el formulario.</div>`;
    }
  }
}

function validateAndAddUsuario(id) {
  const localidad = document.getElementById("localidad")?.value || "";
  const nodo = document.getElementById("nodo")?.value || "";
  const ip = document.getElementById("ip")?.value || "";
  const email = document.getElementById("email")?.value || "";
  const splitter = document.getElementById("splitter")?.value || "";

  if (localidad === "" || nodo === "" || ip === "" || email === "") {
    Swal.fire({
      ...swalDark,
      title: "Campos incompletos",
      text: "Por favor, complete todos los campos obligatorios.",
      icon: "warning",
    });
    return;
  }

  const ipPattern =
    /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;

  if (!ipPattern.test(ip)) {
    Swal.fire({
      ...swalDark,
      title: "IP no válida",
      text: "Por favor, ingrese una dirección IP válida (ej. 192.168.0.1).",
      icon: "error",
    });
    return;
  }

  addUsuario(id, splitter);
}
async function enviarContratoPorCorreo(id, emailCliente) {
  const body = new URLSearchParams({ id });

  const response = await fetch("../php/imprimirPDF.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
    },
    body,
  });

  const raw = await response.json();

  if (!response.ok || raw.error || raw.status === "error") {
    throw new Error(raw.message || "No se pudo obtener el contrato para enviar.");
  }

  const datosFibra = convertirContratoDBaFibra(raw);
  const contratoBlob = await imprimirContratoFibra(datosFibra, { returnBlob: true });

  const formData = new FormData();
  formData.append("idcontrato", id);
  formData.append("email_cliente", emailCliente);
  formData.append("nombre", raw.nombre || "");
  formData.append("contrato_pdf", contratoBlob, `contrato_${id}.pdf`);

  const envio = await fetch("../php/enviarContratoCorreo.php", {
    method: "POST",
    body: formData,
  });

  const result = await envio.json();

  if (!envio.ok || result.status !== "success") {
    throw new Error(result.message || "No se pudo enviar el correo.");
  }

  return result;
}
async function addUsuario(id) {
  const localidadSelect = document.getElementById("localidad");
  const nodoSelect = document.getElementById("nodo");

  const localidadTexto =
    localidadSelect?.selectedOptions?.[0]?.textContent || "";
  const nodoTexto = nodoSelect?.selectedOptions?.[0]?.textContent || "";

  const params = new URLSearchParams({
    id,
    localidad: localidadTexto,
    nodo: nodoTexto,
    ip: document.getElementById("ip")?.value || "",
    email: document.getElementById("email")?.value || "",
    splitter: document.getElementById("splitter")?.value || "",
  });

  try {
    const response = await fetch(
      `../php/insertCliete.php?${params.toString()}`,
      {
        method: "GET",
      },
    );

    const text = (await response.text()).trim();

    if (text === "insercion exitosa") {
  const emailCliente = document.getElementById("email")?.value || "";

  modalAgregar?.hide();
  await cargarTabla();

  try {
    await enviarContratoPorCorreo(id, emailCliente);

    Swal.fire({
      ...swalDark,
      title: "¡Cliente creado!",
      text: "El cliente se creó correctamente y el contrato fue enviado por correo.",
      icon: "success",
    });
  } catch (correoError) {
    console.error(correoError);

    Swal.fire({
      ...swalDark,
      title: "Cliente creado, pero no se envió el correo",
      text: correoError.message || "Revisa la configuración del correo.",
      icon: "warning",
      width: "38rem",
    });
  }
} else {
      Swal.fire({
        ...swalDark,
        title: "No se pudo generar el usuario",
        html: "<div style='text-align:center;font-weight:500;'>El ID ingresado ya existe.</div>",
        icon: "error",
        width: "35rem",
      });
    }
  } catch (error) {
    console.error(error);
    if (respuestaEl) {
      respuestaEl.innerHTML = `<div class="text-red-400 text-sm">Error al crear el usuario.</div>`;
    }
  }
}

async function editContract(id) {
  try {
    const body = new URLSearchParams({ id });

    const response = await fetch("../php/editarContrato.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      },
      body,
    });

    const html = await response.text();
    modalBodyEditarEl.innerHTML = html;
    modalEditar?.show();
  } catch (error) {
    console.error(error);
    if (respuestaEl) {
      respuestaEl.innerHTML = `<div class="text-red-400 text-sm">Error al editar el contrato.</div>`;
    }
  }
}

async function updateContrato() {
  let idcontrato = document.getElementById("ncontrato").value;
  let name = document.getElementById("name").value;
  let rlegal = document.getElementById("rlegal").value;
  let street = document.getElementById("street").value;
  let number = document.getElementById("number").value;
  let colonia = document.getElementById("colonia").value;
  let municipio = document.getElementById("municipio").value;
  let cp = document.getElementById("cp").value;
  let estado = document.getElementById("estado").value;
  let rfc = document.getElementById("rfc").value;
  let telefono = document.getElementById("telefono").value;
  let ttipo =
    document.querySelector('input[name="ttipo"]:checked')?.value || "";
  let tarifa = document.getElementById("tarifa").value;
  let total = document.getElementById("totalm").value;
  let plazo = document.getElementById("pmeses").value;
  let reconexion = document.getElementById("reconexion").value;
  let mdesco = document.getElementById("descm").value;

  let nomNumeral = document.getElementById("nom_numeral")?.value || "";
  let penalidadTexto = document.getElementById("penalidad_texto")?.value || "";

  let modemt = document.getElementById("modemt").value;
  let tipoEntregaEquipo =
    document.getElementById("tipo_entrega_equipo")?.value || "";
  let marca = document.getElementById("marca").value;
  let modelo = document.getElementById("modelo").value;
  let serie = document.getElementById("serie").value;
  let nequipos = document.getElementById("nequipos").value;
  let tpago = document.getElementById("tpago").value;
  let cequipos = document.getElementById("cequipos").value;

  let costoDiferido = document.getElementById("costo_diferido")?.value || "";
  let mesesDiferido = document.getElementById("meses_diferido")?.value || "";

  let domicilioi = document.getElementById("domicilioi").value;
  let fechai = document.getElementById("fechai").value;
  let horai = document.getElementById("horai").value;
  let costoi = document.getElementById("costoi").value;
  let acargo =
    document.querySelector('input[name="acargo"]:checked')?.value || "";

  let mpago = document.getElementById("mpago").value;
  let cmes = document.getElementById("cmes").value;
  let tipoVigencia = document.getElementById("tipo_vigencia")?.value || "";

  let banco = document.getElementById("banco").value;
  let ntarjeta = document.getElementById("ntarjeta").value;

  let metodoPago =
    document.querySelector('input[name="metodoPago"]:checked')?.value || "";

  let datosMetodoPago =
    document.getElementById("datos_metodo_pago")?.value || "";
  let correoElectronico =
    document.getElementById("correo_electronico")?.value || "";
  let otroMedioElectronico =
    document.getElementById("otro_medio_electronico")?.value || "";
  let numeroOtroMedio =
    document.getElementById("numero_otro_medio")?.value || "";

  let sadicional1 = document.getElementById("sadicional1").value;
  let sdescripcion1 = document.getElementById("sdescripcion1").value;
  let scosto1 = document.getElementById("scosto1").value;
  let sadicional2 = document.getElementById("sadicional2").value;
  let sdescripcion2 = document.getElementById("sdescripcion2").value;
  let scosto2 = document.getElementById("scosto2").value;

  let fadicional1 = document.getElementById("fadicional1").value;
  let fdescripcion1 = document.getElementById("fdescripcion1").value;
  let fcosto1 = document.getElementById("fcosto1").value;
  let fadicional2 = document.getElementById("fadicional2").value;
  let fdescripcion2 = document.getElementById("fdescripcion2").value;
  let fcosto2 = document.getElementById("fcosto2").value;

  let ccontrato = document.getElementById("ccontrato").checked;
  let cderechos = document.getElementById("cderechos")?.checked || false;
  let autorizaCederInfo =
    document.getElementById("autoriza_ceder_info")?.checked || false;
  let autorizaLlamadasPromo =
    document.getElementById("autoriza_llamadas_promo")?.checked || false;
  let aceptaContrato =
    document.getElementById("acepta_contrato")?.checked || false;

  let ciudad = document.getElementById("ciudad").value;
  let diaFirma = document.getElementById("dia_firma")?.value || "";
  let mesFirma = document.getElementById("mes_firma")?.value || "";
  let anioFirma = document.getElementById("anio_firma")?.value || "";

  let scontrato = document.getElementById("scontrato")?.checked || false;
  let ncontrato = document.getElementById("ncontrato").value;
  let equiposDev = document.getElementById("equipos_devueltos")?.value || "";
  let fechaCancel = document.getElementById("fecha_cancelacion")?.value || "";
  let fechac = document.getElementById("fechac");

  const formData = new FormData();
  formData.append("nombre", name);
  formData.append("idcontrato", idcontrato);
  formData.append("rlegal", rlegal);
  formData.append("calle", street);
  formData.append("numero", number);
  formData.append("colonia", colonia);
  formData.append("municipio", municipio);
  formData.append("cp", cp);
  formData.append("estado", estado);
  formData.append("rfc", rfc);
  formData.append("fechac", fechac ? fechac.value : "");
  formData.append("telefono", telefono);
  formData.append("ttipo", ttipo);
  formData.append("tarifa", tarifa);
  formData.append("total", total);
  formData.append("reconexion", reconexion);
  formData.append("mdesco", mdesco);
  formData.append("nom_numeral", nomNumeral);
  formData.append("penalidad_texto", penalidadTexto);
  formData.append("plazo", plazo);

  formData.append("modemt", modemt);
  formData.append("tipo_entrega_equipo", tipoEntregaEquipo);
  formData.append("marca", marca);
  formData.append("modelo", modelo);
  formData.append("serie", serie);
  formData.append("nequipos", nequipos);
  formData.append("tpago", tpago);
  formData.append("cequipos", cequipos);
  formData.append("costo_diferido", costoDiferido);
  formData.append("meses_diferido", mesesDiferido);

  formData.append("domicilioi", domicilioi);
  formData.append("fechai", fechai);
  formData.append("horai", horai);
  formData.append("costoi", costoi);
  formData.append("acargo", acargo);

  formData.append("mpago", mpago);
  formData.append("cmes", cmes);
  formData.append("tipo_vigencia", tipoVigencia);
  formData.append("banco", banco);
  formData.append("ntarjeta", ntarjeta);
  formData.append("metodos_pago", metodoPago);
  formData.append("datos_metodo_pago", datosMetodoPago);
  formData.append("correo_electronico", correoElectronico);
  formData.append("otro_medio_electronico", otroMedioElectronico);
  formData.append("numero_otro_medio", numeroOtroMedio);

  formData.append("sadicional1", sadicional1);
  formData.append("sdescripcion1", sdescripcion1);
  formData.append("scosto1", scosto1);
  formData.append("sadicional2", sadicional2);
  formData.append("sdescripcion2", sdescripcion2);
  formData.append("scosto2", scosto2);
  formData.append("fadicional1", fadicional1);
  formData.append("fdescripcion1", fdescripcion1);
  formData.append("fcosto1", fcosto1);
  formData.append("fadicional2", fadicional2);
  formData.append("fdescripcion2", fdescripcion2);
  formData.append("fcosto2", fcosto2);

  formData.append("ccontrato", ccontrato ? "1" : "0");
  formData.append("cderechos", cderechos ? "1" : "0");
  formData.append("autoriza_ceder_info", autorizaCederInfo ? "1" : "0");
  formData.append("autoriza_llamadas_promo", autorizaLlamadasPromo ? "1" : "0");
  formData.append("acepta_contrato", aceptaContrato ? "1" : "0");

  formData.append("ciudad", ciudad);
  formData.append("dia_firma", diaFirma);
  formData.append("mes_firma", mesFirma);
  formData.append("anio_firma", anioFirma);

  formData.append("scontrato", scontrato ? "1" : "0");
  formData.append("ncontrato", ncontrato);
  formData.append("ex", scontrato ? "1" : "0");
  formData.append("equipos_devueltos", equiposDev);
  formData.append("fecha_cancelacion", fechaCancel);

  try {
    const response = await fetch("../php/updateContrato.php", {
      method: "POST",
      body: formData,
    });

    const text = await response.text();
    const jsonResponse = JSON.parse(text);

    if (jsonResponse.status === "success") {
      modalEditar?.hide();
      await cargarTabla();

      Swal.fire({
        ...swalDark,
        title: "Éxito",
        text: jsonResponse.message,
        icon: "success",
      });
    } else {
      Swal.fire({
        ...swalDark,
        title: "Error",
        text: jsonResponse.message,
        icon: "error",
      });
    }
  } catch (error) {
    console.error("Error:", error);
    Swal.fire({
      ...swalDark,
      title: "No se pudo actualizar el contrato",
      icon: "error",
      width: "35rem",
    });
  }
}

/* Listeners dinámicos */
document.addEventListener("change", (e) => {
  if (e.target && e.target.id === "tarifa") {
    const tarifa = e.target.value;
    const mensualidad = document.getElementById("totalm");
    if (!mensualidad) return;

    switch (tarifa) {
      case "1":
        mensualidad.value = "250";
        break;
      case "2":
        mensualidad.value = "350";
        break;
      case "3":
        mensualidad.value = "450";
        break;
      case "4":
        mensualidad.value = "500";
        break;
      case "5":
        mensualidad.value = "600";
        break;
      case "7":
        mensualidad.value = "350";
        break;
      case "8":
        mensualidad.value = "800";
        break;
      default:
        mensualidad.value = "";
    }
  }

  if (e.target && e.target.id === "reconexion") {
    const reconexion = document.getElementById("reconexion");
    const mdesconexion = document.getElementById("descm");
    if (!reconexion || !mdesconexion) return;

    if (reconexion.value === "1") mdesconexion.value = "$0";
    else if (reconexion.value === "2") mdesconexion.value = "$500";
  }
});

/* Buscador */
btnBuscarEl?.addEventListener("click", cargarTabla);

busquedaEl?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    cargarTabla();
  }
});

filtroEstadoEl?.addEventListener("change", cargarTabla);

/* Carga inicial */
document.addEventListener("DOMContentLoaded", () => {
  cargarTabla();
});
/* =================== fin lista.js =================== */
