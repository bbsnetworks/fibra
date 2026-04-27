const form = document.getElementById("formContratoFibra");
const btnGenerarContrato = document.getElementById("btnGenerarContrato");
const btnGenerarContratoBottom = document.getElementById(
  "btnGenerarContratoBottom",
);
const btnVistaDatos = document.getElementById("btnVistaDatos");
const btnLimpiar = document.getElementById("btnLimpiar");

let signatureImageSaved1 = null;
let signaturePadPreview = null;
let signaturePadModal = null;

function loadImage(url) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.responseType = "blob";
    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        const reader = new FileReader();
        reader.onload = function (event) {
          resolve(event.target.result);
        };
        reader.readAsDataURL(this.response);
      } else {
        reject(new Error(`No se pudo cargar la imagen: ${url}`));
      }
    };
    xhr.onerror = () => reject(new Error(`Error de red al cargar: ${url}`));
    xhr.send();
  });
}
function write(doc, text, cfg, size = 7, style = "bold") {
  if (!cfg || text === undefined || text === null || text === "") return;
  doc.setFontSize(size);
  doc.setFont("helvetica", style);
  doc.text(String(text), cfg.x, cfg.y);
}

function markCircle(doc, active, x, y, r = 3) {
  if (!active) return;
  doc.circle(x, y, r, "F");
}

function splitAndWrite(doc, text, x, y, maxWidth, lineHeight = 9, size = 7) {
  if (!text) return;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(size);
  const lines = doc.splitTextToSize(String(text), maxWidth);
  doc.text(lines, x, y, { lineHeightFactor: lineHeight / size });
}

function valor(id) {
  const el = document.getElementById(id);
  return el ? el.value.trim() : "";
}

function checked(id) {
  const el = document.getElementById(id);
  return !!(el && el.checked);
}

function radioValue(name) {
  const el = document.querySelector(`input[name="${name}"]:checked`);
  return el ? el.value : "";
}

function limpiarErrores() {
  document
    .querySelectorAll(".field-error")
    .forEach((el) => el.classList.remove("field-error"));
  document
    .querySelectorAll(".field-error-box")
    .forEach((el) => el.classList.remove("field-error-box"));
}

function marcarError(el) {
  if (!el) return;
  el.classList.add("field-error");
}

function marcarErrorBox(selector) {
  const el = document.querySelector(selector);
  if (!el) return;
  el.classList.add("field-error-box");
}

function cargarVistaContratoFibra() {
  const visor = document.getElementById("visorContratoFibra");
  if (!visor) return;

  const base = "../img/fibra/";
  const imagenes = [
    "Contrato-0001.jpg",
    "Contrato-0002.jpg",
    "Contrato-0003.jpg",
    "Contrato-0004.jpg",
    "Contrato-0005.jpg",
    "Contrato-0006.jpg",
    "Contrato-0007.jpg",
    "Contrato-0008.jpg",
    "Contrato-0009.jpg",
  ];

  visor.innerHTML = imagenes
    .map(
      (nombre, index) => `
    <div class="rounded-2xl overflow-hidden border border-slate-700/60 bg-white shadow">
      <img
        src="${base}${nombre}"
        alt="Contrato fibra página ${index + 1}"
        class="w-full h-auto block"
        loading="lazy"
        onerror="this.closest('div').innerHTML='<div class=&quot;p-6 text-sm text-red-300 bg-[#1e293b]&quot;>No se pudo cargar: ${base}${nombre}</div>'"
      >
    </div>
  `,
    )
    .join("");
}
function inicializarPaqueteYPrecio() {
  const selectPaquete = document.getElementById("descripcionPaquete");
  const inputMensualidad = document.getElementById("mensualidad");

  if (!selectPaquete || !inputMensualidad) return;

  function actualizarPrecio() {
    const opcion = selectPaquete.options[selectPaquete.selectedIndex];
    const precio = opcion?.dataset?.precio || "";
    inputMensualidad.value = precio || "";
  }

  selectPaquete.addEventListener("change", actualizarPrecio);
  actualizarPrecio();
}
// =========================
// FIRMA
// =========================
const signatureCanvas = document.getElementById("signature-canvas");
const signaturePreviewWrapper = document.getElementById(
  "signature-preview-wrapper",
);
const clearSignaturePreview = document.getElementById("clearSignaturePreview");

const signatureModal = document.getElementById("signatureModal");
const openSignatureModal = document.getElementById("openSignatureModal");
const closeSignatureModal = document.getElementById("closeSignatureModal");
const clearSignatureModalPad = document.getElementById(
  "clearSignatureModalPad",
);
const saveSignatureModal = document.getElementById("saveSignatureModal");
const signatureCanvasModal = document.getElementById("signature-canvas-modal");

function drawDataUrlOnCanvas(canvasEl, dataUrl) {
  return new Promise((resolve) => {
    if (!canvasEl || !dataUrl) {
      resolve();
      return;
    }

    const ctx = canvasEl.getContext("2d");
    const img = new Image();

    img.onload = function () {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = canvasEl.getBoundingClientRect();

      canvasEl.width = rect.width * ratio;
      canvasEl.height = rect.height * ratio;

      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.scale(ratio, ratio);
      ctx.clearRect(0, 0, rect.width, rect.height);
      ctx.drawImage(img, 0, 0, rect.width, rect.height);

      resolve();
    };

    img.src = dataUrl;
  });
}

function resizeCanvasPreserve(canvasEl, padInstance, savedDataUrl = null) {
  if (!canvasEl || !padInstance) return;

  let data = savedDataUrl;

  if (!data && !padInstance.isEmpty()) {
    data = canvasEl.toDataURL("image/png");
  }

  const ratio = Math.max(window.devicePixelRatio || 1, 1);
  const rect = canvasEl.getBoundingClientRect();

  canvasEl.width = rect.width * ratio;
  canvasEl.height = rect.height * ratio;

  const ctx = canvasEl.getContext("2d");
  ctx.setTransform(1, 0, 0, 1, 0, 0);
  ctx.scale(ratio, ratio);

  padInstance.clear();

  if (data) {
    padInstance.fromDataURL(data);
  }
}

function initSignaturePads() {
  if (!signatureCanvas || !signatureCanvasModal) return;

  signaturePadPreview = new SignaturePad(signatureCanvas, {
    penColor: "#1d4ed8",
    minWidth: 2.2,
    maxWidth: 4.0,
    velocityFilterWeight: 0.7,
  });

  signaturePadModal = new SignaturePad(signatureCanvasModal, {
    penColor: "#1e40af",
    minWidth: 2.5,
    maxWidth: 4.5,
    velocityFilterWeight: 0.7,
  });

  resizeCanvasPreserve(
    signatureCanvas,
    signaturePadPreview,
    signatureImageSaved1,
  );
}

function openSignatureModalFibra() {
  if (!signatureModal || !signaturePadModal) return;

  signatureModal.classList.remove("hidden");
  document.body.classList.add("overflow-hidden");

  setTimeout(() => {
    resizeCanvasPreserve(
      signatureCanvasModal,
      signaturePadModal,
      signatureImageSaved1,
    );

    if (signatureImageSaved1) {
      signaturePadModal.fromDataURL(signatureImageSaved1);
    } else if (signaturePadPreview && !signaturePadPreview.isEmpty()) {
      const currentData = signatureCanvas.toDataURL("image/png");
      signaturePadModal.fromDataURL(currentData);
    } else {
      signaturePadModal.clear();
    }
  }, 120);
}

function closeSignatureModalFibra() {
  if (!signatureModal) return;
  signatureModal.classList.add("hidden");
  document.body.classList.remove("overflow-hidden");
}

async function saveModalSignatureToPreviewFibra() {
  if (!signaturePadModal || signaturePadModal.isEmpty()) {
    Swal.fire({
      icon: "warning",
      title: "Firma vacía",
      text: "Primero debes firmar antes de guardar.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
    return;
  }

  const dataUrl = signatureCanvasModal.toDataURL("image/png");
  signatureImageSaved1 = dataUrl;

  if (signaturePadPreview) {
    signaturePadPreview.clear();
  }

  await drawDataUrlOnCanvas(signatureCanvas, dataUrl);

  signaturePreviewWrapper?.classList.remove("field-error-box");
  closeSignatureModalFibra();
}

function clearPreviewSignatureFibra() {
  if (signaturePadPreview) signaturePadPreview.clear();
  signatureImageSaved1 = null;

  if (signatureCanvas) {
    const ctx = signatureCanvas.getContext("2d");
    ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
  }

  signaturePreviewWrapper?.classList.remove("field-error-box");
}

function clearModalSignatureFibra() {
  if (signaturePadModal) signaturePadModal.clear();
}

function bindSignatureEvents() {
  openSignatureModal?.addEventListener("click", openSignatureModalFibra);
  signaturePreviewWrapper?.addEventListener("click", openSignatureModalFibra);
  closeSignatureModal?.addEventListener("click", closeSignatureModalFibra);
  clearSignaturePreview?.addEventListener("click", clearPreviewSignatureFibra);
  clearSignatureModalPad?.addEventListener("click", clearModalSignatureFibra);
  saveSignatureModal?.addEventListener(
    "click",
    saveModalSignatureToPreviewFibra,
  );

  window.addEventListener("resize", () => {
    if (signaturePadPreview) {
      resizeCanvasPreserve(
        signatureCanvas,
        signaturePadPreview,
        signatureImageSaved1,
      );
    }

    if (
      signatureModal &&
      !signatureModal.classList.contains("hidden") &&
      signaturePadModal
    ) {
      const modalData = !signaturePadModal.isEmpty()
        ? signatureCanvasModal.toDataURL("image/png")
        : signatureImageSaved1;

      resizeCanvasPreserve(signatureCanvasModal, signaturePadModal, modalData);
    }
  });

  window.addEventListener("orientationchange", () => {
    setTimeout(() => {
      if (signaturePadPreview) {
        resizeCanvasPreserve(
          signatureCanvas,
          signaturePadPreview,
          signatureImageSaved1,
        );
      }

      if (
        signatureModal &&
        !signatureModal.classList.contains("hidden") &&
        signaturePadModal
      ) {
        const modalData = !signaturePadModal.isEmpty()
          ? signatureCanvasModal.toDataURL("image/png")
          : signatureImageSaved1;

        resizeCanvasPreserve(
          signatureCanvasModal,
          signaturePadModal,
          modalData,
        );
      }
    }, 250);
  });
}
function validarFormulario() {
  limpiarErrores();

  let valido = true;

  document.querySelectorAll(".requerido").forEach((el) => {
    if (!el.value.trim()) {
      marcarError(el);
      valido = false;
    }
  });

  const radiosRequeridos = [
    "tipoTelefono",
    "aplicaReconexcion",
    "tipoVigencia",
    "tipoEntregaEquipo",
    "autorizaCargoTarjeta",
    "envioFactura",
    "envioCartaDerechos",
    "envioContratoAdhesion",
    "medioElectronico",
    "autorizaCederInfo",
    "autorizaLlamadasPromo",
  ];

  radiosRequeridos.forEach((name) => {
    const marcado = document.querySelector(`input[name="${name}"]:checked`);
    if (!marcado) {
      marcarErrorBox(`[data-radio-group="${name}"]`);
      valido = false;
    }
  });

  const aceptaContrato = document.getElementById("aceptaContratoFibra");
  const boxAceptaContrato = document.getElementById("boxAceptaContratoFibra");

  if (aceptaContrato && !aceptaContrato.checked) {
    if (boxAceptaContrato) {
      boxAceptaContrato.classList.add("field-error-box");
    }
    valido = false;
  }
  const firmaExiste =
    !!signatureImageSaved1 ||
    (signaturePadPreview && !signaturePadPreview.isEmpty());

  if (!firmaExiste) {
    signaturePreviewWrapper?.classList.add("field-error-box");
    valido = false;
  }

  if (!valido) {
    Swal.fire({
      icon: "error",
      title: "Faltan campos por llenar",
      text: "Revisa los campos obligatorios del formulario, acepta el contrato y agrega la firma.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  }

  return valido;
}
/* =========================
   MAPA DE POSICIONES
========================= */
const mapaFibra = {
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

function normalizarDatosFibra(datos) {
  return {
    contrato: {
      idcontrato: datos.contrato?.idcontrato || "",
    },
    suscriptor: {
      nombre: datos.suscriptor.nombre || "",
      apellidoPaterno: datos.suscriptor.apellidoPaterno || "",
      apellidoMaterno: datos.suscriptor.apellidoMaterno || "",
      calle: datos.domicilio.calle || "",
      numeroExterior: datos.domicilio.numeroExterior || "",
      numeroInterior: datos.domicilio.numeroInterior || "",
      colonia: datos.domicilio.colonia || "",
      municipio: datos.domicilio.municipio || "",
      estado: datos.domicilio.estado || "",
      cp: datos.domicilio.cp || "",
      rfc: datos.domicilio.rfc || "",
      telefono: datos.contacto.telefono || "",
      tipoTelefono: datos.contacto.tipoTelefono || "",
    },

    servicio: {
      descripcionPaquete: datos.servicio.descripcionPaquete || "",
      nombrePaquete: datos.servicio.nombrePaquete || "",
      mensualidad: datos.servicio.mensualidad || "",
      fechaPago: datos.servicio.fechaPago || "",
      aplicaReconexcion: datos.servicio.aplicaReconexcion || "",
      montoReconexcion: datos.servicio.montoReconexcion || "",
      nomNumeral: datos.servicio.nomNumeral || "",
      tipoVigencia: datos.servicio.tipoVigencia || "",
      mesesPlazo: datos.servicio.mesesPlazo || "",
      penalidadTexto: datos.servicio.penalidadTexto || "",
    },

    equipo: {
      tipoEntregaEquipo: datos.equipo.tipoEntrega || "",
      marcaEquipo: datos.equipo.marca || "",
      modeloEquipo: datos.equipo.modelo || "",
      numeroSerie: datos.equipo.numeroSerie || "",
      numeroEquipos: datos.equipo.numeroEquipos || "",
      costoTotalEquipo: datos.equipo.costoTotal || "",
      modalidadPagoEquipo: datos.equipo.modalidadPago || "",
      costoDiferido: datos.equipo.costoDiferido || "",
      mesesDiferido: datos.equipo.mesesDiferido || "",
    },

    instalacion: {
      domicilioInstalacion: datos.instalacion.domicilio || "",
      fechaInstalacion: datos.instalacion.fecha || "",
      horaInstalacion: datos.instalacion.hora || "",
      costoInstalacion: datos.instalacion.costo || "",
    },

    metodoPago: {
      tipo: datos.metodoPago.tipo || "",
      datosMetodoPago: datos.metodoPago.datosMetodo || "",
    },

    tarjeta: {
      autorizaCargoTarjeta: datos.autorizacionCargoTarjeta.autoriza || "",
      mesesCargoTarjeta: datos.autorizacionCargoTarjeta.meses || "",
      banco: datos.banco.nombre || "",
      numeroTarjeta: datos.banco.numeroTarjeta || "",
    },

    serviciosAdicionales: [
      {
        Nombre: datos.serviciosAdicionales?.[0]?.descripcion || "",
        descripcion: datos.serviciosAdicionales?.[0]?.descripcion || "",
        costo: datos.serviciosAdicionales?.[0]?.costo || "",
      },
      {
        Nombre: datos.serviciosAdicionales?.[1]?.descripcion || "",
        descripcion: datos.serviciosAdicionales?.[1]?.descripcion || "",
        costo: datos.serviciosAdicionales?.[1]?.costo || "",
      },
    ],

    conceptosFacturables: [
      {
        nombre: datos.conceptosFacturables?.[0]?.descripcion || "",
        descripcion: datos.conceptosFacturables?.[0]?.descripcion || "",
        costo: datos.conceptosFacturables?.[0]?.costo || "",
      },
      {
        nombre: datos.conceptosFacturables?.[1]?.descripcion || "",
        descripcion: datos.conceptosFacturables?.[1]?.descripcion || "",
        costo: datos.conceptosFacturables?.[1]?.costo || "",
      },
    ],

    envioElectronico: {
      factura: datos.envioElectronico.factura || "",
      cartaDerechos: datos.envioElectronico.cartaDerechosMinimos || "",
      contratoAdhesion: datos.envioElectronico.contratoAdhesion || "",
      medioElectronico: datos.envioElectronico.medio || "",
      correoElectronico: datos.envioElectronico.correo || "",
      otroMedioElectronico: datos.envioElectronico.otro || "",
      numeroOtroMedio: datos.envioElectronico.numero || "",
    },

    usoInformacion: {
      autorizaCederInfo: datos.usoInformacion.cederInformacion || "",
      autorizaLlamadasPromo: datos.usoInformacion.recibirLlamadas || "",
    },

    cierre: {
      ciudadFirma: datos.cierre.ciudadFirma || "",
      diaFirma: datos.cierre.dia || "",
      mesFirma: datos.cierre.mes || "",
      anioFirma: datos.cierre.anio || "",
    },
  };
}

function obtenerDatosFibra() {
  return {
    contrato: {
      idcontrato: valor("idcontrato"),
    },
    suscriptor: {
      nombre: valor("nombre"),
      apellidoPaterno: valor("apellidoPaterno"),
      apellidoMaterno: valor("apellidoMaterno"),
    },
    domicilio: {
      calle: valor("calle"),
      numeroExterior: valor("numeroExterior"),
      numeroInterior: valor("numeroInterior"),
      colonia: valor("colonia"),
      municipio: valor("municipio"),
      estado: valor("estado"),
      cp: valor("cp"),
      rfc: valor("rfc"),
    },
    contacto: {
      tipoTelefono: radioValue("tipoTelefono"),
      telefono: valor("telefono"),
    },
    servicio: {
      descripcionPaquete: valor("descripcionPaquete"),
      nombrePaquete: textoSeleccionado("descripcionPaquete"),
      mensualidad: valor("mensualidad"),
      fechaPago: valor("fechaPago"),
      aplicaReconexcion: radioValue("aplicaReconexcion"),
      montoReconexcion: valor("montoReconexcion"),
      nomNumeral: valor("nomNumeral"),
      tipoVigencia: radioValue("tipoVigencia"),
      mesesPlazo: valor("mesesPlazo"),
      penalidadTexto: valor("penalidadTexto"),
    },
    equipo: {
      tipoEntrega: radioValue("tipoEntregaEquipo"),
      marca: valor("marcaEquipo"),
      modelo: valor("modeloEquipo"),
      numeroSerie: valor("numeroSerie"),
      numeroEquipos: valor("numeroEquipos"),
      costoTotal: valor("costoTotalEquipo"),
      modalidadPago: radioValue("modalidadPagoEquipo"),
      costoDiferido: valor("costoDiferido"),
      mesesDiferido: valor("mesesDiferido"),
    },
    instalacion: {
      domicilio: valor("domicilioInstalacion"),
      fecha: valor("fechaInstalacion"),
      hora: valor("horaInstalacion"),
      costo: valor("costoInstalacion"),
    },
    metodoPago: {
      tipo: radioValue("metodoPago"),
      datosMetodo: valor("datosMetodoPago"),
    },
    autorizacionCargoTarjeta: {
      autoriza: radioValue("autorizaCargoTarjeta"),
      meses: valor("mesesCargoTarjeta"),
    },
    banco: {
      nombre: valor("banco"),
      numeroTarjeta: valor("numeroTarjeta"),
    },
    serviciosAdicionales: [
      {
        descripcion: valor("servicioAdic1Desc"),
        costo: valor("servicioAdic1Costo"),
      },
      {
        descripcion: valor("servicioAdic2Desc"),
        costo: valor("servicioAdic2Costo"),
      },
    ],
    conceptosFacturables: [
      {
        descripcion: valor("conceptoFact1Desc"),
        costo: valor("conceptoFact1Costo"),
      },
      {
        descripcion: valor("conceptoFact2Desc"),
        costo: valor("conceptoFact2Costo"),
      },
    ],
    envioElectronico: {
      factura: radioValue("envioFactura"),
      cartaDerechosMinimos: radioValue("envioCartaDerechos"),
      contratoAdhesion: radioValue("envioContratoAdhesion"),
      medio: radioValue("medioElectronico"),
      correo: valor("correoElectronico"),
      otro: valor("otroMedioElectronico"),
      numero: valor("numeroOtroMedio"),
    },
    usoInformacion: {
      cederInformacion: radioValue("autorizaCederInfo"),
      recibirLlamadas: radioValue("autorizaLlamadasPromo"),
    },
    cierre: {
      ciudadFirma: valor("ciudadFirma"),
      dia: valor("diaFirma"),
      mes: valor("mesFirma"),
      anio: valor("anioFirma"),
    },
    firmaCliente:
      signatureImageSaved1 ||
      (signaturePadPreview && !signaturePadPreview.isEmpty()
        ? signatureCanvas.toDataURL("image/png")
        : ""),
    contratoAceptado: checked("aceptaContratoFibra"),
  };
}
async function cargarNumeroContrato() {
  const inputContrato = document.getElementById("idcontrato");
  if (!inputContrato) return;

  inputContrato.placeholder = "Cargando...";

  try {
    const resp = await fetch("../php/obtener_siguiente_cliente.php");
    const data = await resp.json();

    if (!data.ok) {
      throw new Error(
        data.message || "No se pudo obtener el siguiente número.",
      );
    }

    if (!inputContrato.value.trim()) {
      inputContrato.value = data.numero;
    }
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message || "No se pudo cargar el número de contrato.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  }
}
const btnRecargarContrato = document.getElementById("btnRecargarContrato");

async function recargarNumeroContrato() {
  const inputContrato = document.getElementById("idcontrato");
  if (!inputContrato) return;

  try {
    const resp = await fetch("../php/obtener_siguiente_cliente.php");
    const data = await resp.json();

    if (!data.ok) {
      throw new Error(
        data.message || "No se pudo obtener el siguiente número.",
      );
    }

    inputContrato.value = data.numero;

    Swal.fire({
      icon: "success",
      title: "Número actualizado",
      text: `Se asignó el número ${data.numero}.`,
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message || "No se pudo actualizar el número.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  }
}
async function validarNumeroContrato() {
  const input = document.getElementById("idcontrato");
  const numero = input.value.trim();

  if (!numero || isNaN(numero) || Number(numero) <= 0) {
    marcarError(input);
    return false;
  }

  const resp = await fetch("../php/id-check.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "ncontrato=" + encodeURIComponent(numero),
  });

  const data = await resp.json();

  if (!data.ok) {
    throw new Error(data.message || "Error validando contrato");
  }

  if (data.exists) {
    Swal.fire({
      icon: "warning",
      title: "Número no disponible",
      text: "Ese número ya existe en el sistema.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });

    marcarError(input);
    return false;
  }

  return true;
}

if (btnRecargarContrato) {
  btnRecargarContrato.addEventListener("click", recargarNumeroContrato);
}

if (btnGenerarContrato) {
  btnGenerarContrato.addEventListener("click", accionGenerar);
}

if (btnGenerarContratoBottom) {
  btnGenerarContratoBottom.addEventListener("click", accionGenerar);
}

if (btnVistaDatos) {
  btnVistaDatos.addEventListener("click", () => {
    const datosFibra = obtenerDatosFibra();

    Swal.fire({
      title: "Datos capturados",
      html: `
        <div style="text-align:left; max-height: 420px; overflow:auto; background:#020617; border:1px solid rgba(148,163,184,.2); padding:14px; border-radius:12px;">
          <pre style="white-space:pre-wrap; font-size:12px; color:#dbeafe; margin:0;">${JSON.stringify(datosFibra, null, 2)}</pre>
        </div>
      `,
      width: 900,
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  });
}

if (btnLimpiar) {
  btnLimpiar.addEventListener("click", () => {
    Swal.fire({
      title: "¿Limpiar formulario?",
      text: "Se borrarán los datos capturados.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, limpiar",
      cancelButtonText: "Cancelar",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
      cancelButtonColor: "#475569",
    }).then((result) => {
      if (!result.isConfirmed) return;

      form.reset();
      limpiarErrores();
      clearPreviewSignatureFibra();

      Swal.fire({
        icon: "success",
        title: "Formulario limpio",
        background: "#0b1120",
        color: "#e2e8f0",
        confirmButtonColor: "#0284c7",
      });
    });
  });
}

async function generarPdfFibra(datosFormulario) {
  const datos = normalizarDatosFibra(datosFormulario);

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

    const firmaCliente = datosFormulario.firmaCliente || null;

    const pdf = new jsPDF("p", "pt", "letter");

    // Página 1
    pdf.addImage(image1, "JPEG", 0, 0, 565, 792);

    write(pdf, datos.suscriptor.nombre, mapaFibra.p1.nombre);
    write(pdf, datos.suscriptor.apellidoPaterno, mapaFibra.p1.apellidoPaterno);
    write(pdf, datos.suscriptor.apellidoMaterno, mapaFibra.p1.apellidoMaterno);

    write(pdf, datos.suscriptor.calle, mapaFibra.p1.calle);
    write(pdf, datos.suscriptor.numeroExterior, mapaFibra.p1.numeroExterior);
    write(pdf, datos.suscriptor.numeroInterior, mapaFibra.p1.numeroInterior);
    write(pdf, datos.suscriptor.colonia, mapaFibra.p1.colonia);
    write(pdf, datos.suscriptor.municipio, mapaFibra.p1.municipio);
    write(pdf, datos.suscriptor.estado, mapaFibra.p1.estado);
    write(pdf, datos.suscriptor.cp, mapaFibra.p1.cp);
    write(pdf, datos.suscriptor.rfc, mapaFibra.p1.rfc);
    write(pdf, datos.suscriptor.telefono, mapaFibra.p1.telefono);

    markCircle(pdf, datos.suscriptor.tipoTelefono === "fijo", 98, 219, 3);
    markCircle(pdf, datos.suscriptor.tipoTelefono === "movil", 146, 218, 3);

    write(pdf, datos.servicio.nombrePaquete || datos.servicio.descripcionPaquete, mapaFibra.p1.paquete);
    write(pdf, datos.servicio.mensualidad, mapaFibra.p1.mensualidad);
    splitAndWrite(
      pdf,
      datos.servicio.fechaPago,
      mapaFibra.p1.fechaPago.x,
      mapaFibra.p1.fechaPago.y,
      70,
    );

    markCircle(pdf, datos.servicio.aplicaReconexcion === "2", 208.5, 373, 3);
    markCircle(pdf, datos.servicio.aplicaReconexcion === "1", 250, 372.5, 3);
    write(pdf, datos.servicio.montoReconexcion, mapaFibra.p1.montoReconexcion);

    markCircle(pdf, datos.servicio.tipoVigencia === "indefinido", 410, 299, 3);
    markCircle(
      pdf,
      datos.servicio.tipoVigencia === "plazo_forzoso",
      410,
      320,
      3,
    );
    write(pdf, datos.servicio.mesesPlazo, mapaFibra.p1.mesesPlazo);

    markCircle(
      pdf,
      datos.equipo.tipoEntregaEquipo === "1",
      146.5,
      436.5,
      3,
    );
    markCircle(
      pdf,
      datos.equipo.tipoEntregaEquipo === "2",
      146.5,
      448.5,
      3,
    );

    write(pdf, datos.equipo.marcaEquipo, mapaFibra.p1.marcaEquipo);
    write(pdf, datos.equipo.modeloEquipo, mapaFibra.p1.modeloEquipo);
    write(pdf, datos.equipo.numeroSerie, mapaFibra.p1.numeroSerie);
    write(pdf, datos.equipo.numeroEquipos, mapaFibra.p1.numeroEquipos);
    write(pdf, datos.equipo.costoTotalEquipo, mapaFibra.p1.costoTotalEquipo);
    write(pdf, datos.equipo.costoDiferido, mapaFibra.p1.costoDiferido);
    write(pdf, datos.equipo.mesesDiferido, mapaFibra.p1.mesesDiferido);

    splitAndWrite(
      pdf,
      datos.instalacion.domicilioInstalacion,
      mapaFibra.p1.domicilioInstalacion.x,
      mapaFibra.p1.domicilioInstalacion.y,
      250,
    );
    write(
      pdf,
      datos.instalacion.fechaInstalacion,
      mapaFibra.p1.fechaInstalacion,
    );
    write(pdf, datos.instalacion.horaInstalacion, mapaFibra.p1.horaInstalacion);
    write(
      pdf,
      datos.instalacion.costoInstalacion,
      mapaFibra.p1.costoInstalacion,
    );

    markCircle(pdf, datos.metodoPago.tipo === "efectivo", 34, 588, 3);
    markCircle(pdf, datos.metodoPago.tipo === "transferencia", 34, 600, 3);
    markCircle(pdf, datos.metodoPago.tipo === "deposito", 32, 610.5, 3);
    markCircle(pdf, datos.metodoPago.tipo === "tiendas", 32, 620.5, 3);
    markCircle(pdf, datos.metodoPago.tipo === "tarjeta", 32, 631, 3);
    markCircle(pdf, datos.metodoPago.tipo === "domiciliado", 32, 641, 3);
    markCircle(pdf, datos.metodoPago.tipo === "enlinea", 32, 651.5, 3);
    markCircle(pdf, datos.metodoPago.tipo === "centros", 32, 662, 3);
    splitAndWrite(
      pdf,
      datos.metodoPago.datosMetodoPago,
      mapaFibra.p1.datosMetodoPago.x,
      mapaFibra.p1.datosMetodoPago.y,
      420,
    );

    markCircle(pdf, datos.tarjeta.autorizaCargoTarjeta === "si", 147.5, 708, 3);
    markCircle(pdf, datos.tarjeta.autorizaCargoTarjeta === "no", 177.5, 708, 3);
    write(pdf, datos.tarjeta.mesesCargoTarjeta, mapaFibra.p1.mesesCargoTarjeta);

    if (firmaCliente) {
      pdf.addImage(
        firmaCliente,
        "PNG",
        mapaFibra.p1.firmaSuscriptor.x,
        mapaFibra.p1.firmaSuscriptor.y,
        mapaFibra.p1.firmaSuscriptor.w,
        mapaFibra.p1.firmaSuscriptor.h,
      );
    }

    // Página 2
    pdf.addPage();
    pdf.addImage(image2, "JPEG", 0, 0, 565, 792);

    write(pdf, datos.tarjeta.banco, mapaFibra.p2.banco);
    write(pdf, datos.tarjeta.numeroTarjeta, mapaFibra.p2.numeroTarjeta);

    write(
      pdf,
      datos.serviciosAdicionales[0]?.Nombre,
      mapaFibra.p2.servicio1Name,
    );
    write(
      pdf,
      datos.serviciosAdicionales[0]?.costo,
      mapaFibra.p2.servicio1Costo,
    );
    write(
      pdf,
      datos.serviciosAdicionales[0]?.descripcion,
      mapaFibra.p2.servicio1Desc,
    );
    write(
      pdf,
      datos.serviciosAdicionales[1]?.Nombre,
      mapaFibra.p2.servicio2Name,
    );
    write(
      pdf,
      datos.serviciosAdicionales[1]?.costo,
      mapaFibra.p2.servicio2Costo,
    );
    write(
      pdf,
      datos.serviciosAdicionales[1]?.descripcion,
      mapaFibra.p2.servicio2Desc,
    );

    write(pdf, datos.conceptosFacturables[0]?.nombre, mapaFibra.p2.fact1Name);
    write(pdf, datos.conceptosFacturables[0]?.costo, mapaFibra.p2.fact1Costo);
    write(
      pdf,
      datos.conceptosFacturables[0]?.descripcion,
      mapaFibra.p2.fact1Desc,
    );
    write(pdf, datos.conceptosFacturables[1]?.nombre, mapaFibra.p2.fact2Name);
    write(pdf, datos.conceptosFacturables[1]?.costo, mapaFibra.p2.fact2Costo);
    write(
      pdf,
      datos.conceptosFacturables[1]?.descripcion,
      mapaFibra.p2.fact2Desc,
    );

    markCircle(pdf, datos.envioElectronico.factura === "si", 118, 539, 3);
    markCircle(pdf, datos.envioElectronico.factura === "no", 156, 539, 3);
    markCircle(
      pdf,
      datos.envioElectronico.cartaDerechos === "si",
      325,
      538.5,
      3,
    );
    markCircle(
      pdf,
      datos.envioElectronico.cartaDerechos === "no",
      361,
      538.5,
      3,
    );
    markCircle(
      pdf,
      datos.envioElectronico.contratoAdhesion === "si",
      484,
      538.5,
      3,
    );
    markCircle(
      pdf,
      datos.envioElectronico.contratoAdhesion === "no",
      518.5,
      538.5,
      3,
    );

    markCircle(
      pdf,
      datos.envioElectronico.medioElectronico === "correo",
      101.5,
      571.5,
      3,
    );
    markCircle(
      pdf,
      datos.envioElectronico.medioElectronico === "otro",
      101.5,
      592,
      3,
    );

    write(
      pdf,
      datos.envioElectronico.correoElectronico,
      mapaFibra.p2.correoElectronico,
    );
    write(pdf, datos.envioElectronico.otroMedioElectronico, { x: 140, y: 593 });
    write(pdf, datos.envioElectronico.numeroOtroMedio, { x: 255, y: 594 });

    markCircle(
      pdf,
      datos.usoInformacion.autorizaCederInfo === "si",
      128,
      641,
      3,
    );
    markCircle(
      pdf,
      datos.usoInformacion.autorizaCederInfo === "no",
      168,
      641,
      3,
    );
    markCircle(
      pdf,
      datos.usoInformacion.autorizaLlamadasPromo === "si",
      168,
      675,
      3,
    );
    markCircle(
      pdf,
      datos.usoInformacion.autorizaLlamadasPromo === "no",
      212,
      675,
      3,
    );

    if (firmaCliente) {
      pdf.addImage(
        firmaCliente,
        "PNG",
        mapaFibra.p2.firmaProveedor.x,
        mapaFibra.p2.firmaProveedor.y,
        mapaFibra.p2.firmaProveedor.w,
        mapaFibra.p2.firmaProveedor.h,
      );
      pdf.addImage(
        firmaCliente,
        "PNG",
        mapaFibra.p2.firmaProvedor2.x,
        mapaFibra.p2.firmaProvedor2.y,
        mapaFibra.p2.firmaProvedor2.w,
        mapaFibra.p2.firmaProvedor2.h,
      );
      pdf.addImage(
        firmaCliente,
        "PNG",
        mapaFibra.p2.firmaProvedor3.x,
        mapaFibra.p2.firmaProvedor3.y,
        mapaFibra.p2.firmaProvedor3.w,
        mapaFibra.p2.firmaProvedor3.h,
      );
      pdf.addImage(
        firmaCliente,
        "PNG",
        mapaFibra.p2.firmaProvedor4.x,
        mapaFibra.p2.firmaProvedor4.y,
        mapaFibra.p2.firmaProvedor4.w,
        mapaFibra.p2.firmaProvedor4.h,
      );
    }
    // Página 3
    pdf.addPage();
    pdf.addImage(image3, "JPEG", 0, 0, 565, 792);

    write(pdf, datos.cierre.ciudadFirma, mapaFibra.p3.ciudadFirma);
    write(pdf, datos.cierre.diaFirma, mapaFibra.p3.diaFirma);
    write(pdf, datos.cierre.mesFirma, mapaFibra.p3.mesFirma);
    write(pdf, datos.cierre.anioFirma, mapaFibra.p3.anioFirma);

    write(
      pdf,
      datos.contrato.idcontrato,
      mapaFibra.p3.numeroContrato,
      9,
      "bold",
    );

    pdf.addImage(
      firmaProveedor,
      "PNG",
      mapaFibra.p3.firmaProveedor.x,
      mapaFibra.p3.firmaProveedor.y,
      mapaFibra.p3.firmaProveedor.w,
      mapaFibra.p3.firmaProveedor.h,
    );

    if (firmaCliente) {
      pdf.addImage(
        firmaCliente,
        "PNG",
        mapaFibra.p3.firmaSuscriptor.x,
        mapaFibra.p3.firmaSuscriptor.y,
        mapaFibra.p3.firmaSuscriptor.w,
        mapaFibra.p3.firmaSuscriptor.h,
      );
    }

    // resto
    pdf.addPage();
    pdf.addImage(image4, "JPEG", 0, 0, 565, 792);
    pdf.addPage();
    pdf.addImage(image5, "JPEG", 0, 0, 565, 792);
    pdf.addPage();
    pdf.addImage(image6, "JPEG", 0, 0, 565, 792);
    pdf.addPage();
    pdf.addImage(image7, "JPEG", 0, 0, 565, 792);
    pdf.addPage();
    pdf.addImage(image8, "JPEG", 0, 0, 565, 792);
    pdf.addPage();
    pdf.addImage(image9, "JPEG", 0, 0, 565, 792);

    const blob = pdf.output("blob");
    const url = URL.createObjectURL(blob);
    window.open(url, "_blank");
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message || "No se pudo generar el PDF.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  }
}
async function guardarContratoFibra(datosFibra) {
  const formData = new FormData();

  // Contrato
  formData.append("ncontrato", datosFibra.contrato.idcontrato || "");

  // Fecha general del contrato
  const hoy = new Date().toISOString().slice(0, 10);
  formData.append("fechac", hoy);

  // Suscriptor
  formData.append("nombre", datosFibra.suscriptor.nombre || "");
  formData.append(
    "apellidoPaterno",
    datosFibra.suscriptor.apellidoPaterno || "",
  );
  formData.append(
    "apellidoMaterno",
    datosFibra.suscriptor.apellidoMaterno || "",
  );

  // Domicilio
  formData.append("calle", datosFibra.domicilio.calle || "");
  formData.append("numeroExterior", datosFibra.domicilio.numeroExterior || "");
  formData.append("numeroInterior", datosFibra.domicilio.numeroInterior || "");
  formData.append("colonia", datosFibra.domicilio.colonia || "");
  formData.append("municipio", datosFibra.domicilio.municipio || "");
  formData.append("estado", datosFibra.domicilio.estado || "");
  formData.append("cp", datosFibra.domicilio.cp || "");
  formData.append("rfc", datosFibra.domicilio.rfc || "");

  // Contacto
  formData.append("telefono", datosFibra.contacto.telefono || "");
  formData.append("tipoTelefono", datosFibra.contacto.tipoTelefono || "");

  // Servicio
  formData.append(
    "descripcionPaquete",
    datosFibra.servicio.descripcionPaquete || "",
  );
  formData.append("mensualidad", datosFibra.servicio.mensualidad || "");
  formData.append("fechaPago", datosFibra.servicio.fechaPago || "");
  formData.append(
    "aplicaReconexcion",
    datosFibra.servicio.aplicaReconexcion || "",
  );
  formData.append(
    "montoReconexcion",
    datosFibra.servicio.montoReconexcion || "",
  );
  formData.append("nomNumeral", datosFibra.servicio.nomNumeral || "");
  formData.append("tipoVigencia", datosFibra.servicio.tipoVigencia || "");
  formData.append("mesesPlazo", datosFibra.servicio.mesesPlazo || "");
  formData.append("penalidadTexto", datosFibra.servicio.penalidadTexto || "");

  // Equipo
  formData.append("tipoEntregaEquipo", datosFibra.equipo.tipoEntrega || "");
  formData.append("marcaEquipo", datosFibra.equipo.marca || "");
  formData.append("modeloEquipo", datosFibra.equipo.modelo || "");
  formData.append("numeroSerie", datosFibra.equipo.numeroSerie || "");
  formData.append("numeroEquipos", datosFibra.equipo.numeroEquipos || "");
  formData.append("costoTotalEquipo", datosFibra.equipo.costoTotal || "");
  formData.append("modalidadPagoEquipo", datosFibra.equipo.modalidadPago || "");
  formData.append("costoDiferido", datosFibra.equipo.costoDiferido || "");
  formData.append("mesesDiferido", datosFibra.equipo.mesesDiferido || "");

  // Instalación
  formData.append(
    "domicilioInstalacion",
    datosFibra.instalacion.domicilio || "",
  );
  formData.append("fechaInstalacion", datosFibra.instalacion.fecha || "");
  formData.append("horaInstalacion", datosFibra.instalacion.hora || "");
  formData.append("costoInstalacion", datosFibra.instalacion.costo || "");

  // Métodos de pago
  formData.append("metodoPago", datosFibra.metodoPago.tipo || "");
  formData.append("datosMetodoPago", datosFibra.metodoPago.datosMetodo || "");

  // Tarjeta
  formData.append(
    "autorizaCargoTarjeta",
    datosFibra.autorizacionCargoTarjeta?.autoriza || "",
  );
  formData.append(
    "mesesCargoTarjeta",
    datosFibra.autorizacionCargoTarjeta?.meses || "",
  );
  formData.append("banco", datosFibra.banco?.nombre || "");
  formData.append("numeroTarjeta", datosFibra.banco?.numeroTarjeta || "");

  // Servicios adicionales
  formData.append(
    "servicioAdic1Desc",
    datosFibra.serviciosAdicionales?.[0]?.descripcion || "",
  );
  formData.append(
    "servicioAdic1Costo",
    datosFibra.serviciosAdicionales?.[0]?.costo || "",
  );
  formData.append(
    "servicioAdic2Desc",
    datosFibra.serviciosAdicionales?.[1]?.descripcion || "",
  );
  formData.append(
    "servicioAdic2Costo",
    datosFibra.serviciosAdicionales?.[1]?.costo || "",
  );

  // Conceptos facturables
  formData.append(
    "conceptoFact1Desc",
    datosFibra.conceptosFacturables?.[0]?.descripcion || "",
  );
  formData.append(
    "conceptoFact1Costo",
    datosFibra.conceptosFacturables?.[0]?.costo || "",
  );
  formData.append(
    "conceptoFact2Desc",
    datosFibra.conceptosFacturables?.[1]?.descripcion || "",
  );
  formData.append(
    "conceptoFact2Costo",
    datosFibra.conceptosFacturables?.[1]?.costo || "",
  );

  // Envío electrónico
  formData.append("envioFactura", datosFibra.envioElectronico.factura || "");
  formData.append(
    "envioCartaDerechos",
    datosFibra.envioElectronico.cartaDerechosMinimos || "",
  );
  formData.append(
    "envioContratoAdhesion",
    datosFibra.envioElectronico.contratoAdhesion || "",
  );
  formData.append("medioElectronico", datosFibra.envioElectronico.medio || "");
  formData.append(
    "correoElectronico",
    datosFibra.envioElectronico.correo || "",
  );
  formData.append(
    "otroMedioElectronico",
    datosFibra.envioElectronico.otro || "",
  );
  formData.append("numeroOtroMedio", datosFibra.envioElectronico.numero || "");

  // Uso de información
  formData.append(
    "autorizaCederInfo",
    datosFibra.usoInformacion.cederInformacion || "",
  );
  formData.append(
    "autorizaLlamadasPromo",
    datosFibra.usoInformacion.recibirLlamadas || "",
  );

  // Cierre
  formData.append("ciudadFirma", datosFibra.cierre.ciudadFirma || "");
  formData.append("diaFirma", datosFibra.cierre.dia || "");
  formData.append("mesFirma", datosFibra.cierre.mes || "");
  formData.append("anioFirma", datosFibra.cierre.anio || "");

  // Firma y aceptación
  formData.append("firma", datosFibra.firmaCliente || "");
  formData.append(
    "aceptaContratoFibra",
    datosFibra.contratoAceptado ? "1" : "0",
  );

  // Evidencia: de momento vacía o luego la llenas con la ruta que te devuelva tu upload aparte
  formData.append("evidencia", "");

  const resp = await fetch("../php/guardar_contrato.php", {
    method: "POST",
    body: formData,
  });

  const data = await resp.json();

  if (!data.ok) {
    throw new Error(data.message || "No se pudo guardar el contrato.");
  }

  return data;
}
async function accionGenerar() {
  try {
    limpiarErrores();

    const contratoValido = await validarNumeroContrato();
    if (!contratoValido) return;

    if (!validarFormulario()) return;

    const datosFibra = obtenerDatosFibra();
    console.log("DATOS FIBRA:", datosFibra);

    // 1. Generar PDF
    await generarPdfFibra(datosFibra);

    // 2. Guardar en base de datos
    const respuestaGuardado = await guardarContratoFibra(datosFibra);

    Swal.fire({
      icon: "success",
      title: "Contrato generado",
      text: `Se guardó correctamente el contrato ${respuestaGuardado.idcontrato}.`,
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  } catch (error) {
    console.error(error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text:
        error.message || "Ocurrió un error al generar o guardar el contrato.",
      background: "#0b1120",
      color: "#e2e8f0",
      confirmButtonColor: "#0284c7",
    });
  }
}
//auxiliares
function actualizarEstadoReconexcion() {
  const tipo = radioValue('aplicaReconexcion');
  const inputMonto = document.getElementById('montoReconexcion');
  const inputNom = document.getElementById('nomNumeral');

  if (!inputMonto || !inputNom) return;

  // HTML actual:
  // 2 = Sí
  // 1 = No
  const aplica = tipo === '2';

  if (aplica) {
    inputMonto.disabled = false;
    inputNom.disabled = false;

    inputMonto.dataset.requiredConditional = 'true';
    inputNom.dataset.requiredConditional = 'true';
  } else {
    inputMonto.value = '';
    inputNom.value = '';

    inputMonto.disabled = true;
    inputNom.disabled = true;

    inputMonto.dataset.requiredConditional = 'false';
    inputNom.dataset.requiredConditional = 'false';

    inputMonto.classList.remove('field-error');
    inputNom.classList.remove('field-error');
  }
}
function textoSeleccionado(id) {
  const el = document.getElementById(id);
  if (!el) return "";
  const option = el.options[el.selectedIndex];
  return option ? option.text.trim() : "";
}
function actualizarEstadoVigencia() {
  const tipo = radioValue('tipoVigencia');
  const inputMeses = document.getElementById('mesesPlazo');
  const inputPenalidad = document.getElementById('penalidadTexto');

  if (!inputMeses || !inputPenalidad) return;

  const aplicaPlazo = tipo === 'plazo_forzoso';

  if (aplicaPlazo) {
    inputMeses.disabled = false;
    inputPenalidad.disabled = false;

    inputMeses.dataset.requiredConditional = 'true';
    inputPenalidad.dataset.requiredConditional = 'true';
  } else {
    inputMeses.value = '';
    inputPenalidad.value = '';

    inputMeses.disabled = true;
    inputPenalidad.disabled = true;

    inputMeses.dataset.requiredConditional = 'false';
    inputPenalidad.dataset.requiredConditional = 'false';

    inputMeses.classList.remove('field-error');
    inputPenalidad.classList.remove('field-error');
  }
}
function bindServicioInternetEvents() {
  document.querySelectorAll('input[name="aplicaReconexcion"]').forEach(el => {
    el.addEventListener('change', () => {
      actualizarEstadoReconexcion();
    });
  });

  document.querySelectorAll('input[name="tipoVigencia"]').forEach(el => {
    el.addEventListener('change', () => {
      actualizarEstadoVigencia();
    });
  });

  actualizarEstadoReconexcion();
  actualizarEstadoVigencia();
}

function validarSeccionServicioInternet() {
  let valido = true;

  const paquete = document.getElementById('descripcionPaquete');
  const mensualidad = document.getElementById('mensualidad');
  const fechaPago = document.getElementById('fechaPago');
  const montoReconexcion = document.getElementById('montoReconexcion');
  const nomNumeral = document.getElementById('nomNumeral');
  const mesesPlazo = document.getElementById('mesesPlazo');
  const penalidadTexto = document.getElementById('penalidadTexto');

  if (!paquete || !paquete.value.trim()) {
    marcarError(paquete);
    valido = false;
  }

  if (!mensualidad || !mensualidad.value.trim()) {
    marcarError(mensualidad);
    valido = false;
  }

  if (!fechaPago || !fechaPago.value.trim()) {
    marcarError(fechaPago);
    valido = false;
  }

  // Reconexión
  const aplicaReconexcion = radioValue('aplicaReconexcion');

  if (!aplicaReconexcion) {
    marcarErrorBox('[data-radio-group="aplicaReconexcion"]');
    valido = false;
  }

  // HTML actual: 2 = Sí, 1 = No
  if (aplicaReconexcion === '2') {
    if (!montoReconexcion || !montoReconexcion.value.trim()) {
      marcarError(montoReconexcion);
      valido = false;
    }

    if (!nomNumeral || !nomNumeral.value.trim()) {
      marcarError(nomNumeral);
      valido = false;
    }
  }

  // Vigencia
  const tipoVigencia = radioValue('tipoVigencia');

  if (!tipoVigencia) {
    marcarErrorBox('[data-radio-group="tipoVigencia"]');
    valido = false;
  }

  if (tipoVigencia === 'plazo_forzoso') {
    const meses = Number(mesesPlazo?.value || 0);

    if (!mesesPlazo || !mesesPlazo.value.trim()) {
      marcarError(mesesPlazo);
      valido = false;
    } else if (isNaN(meses) || meses <= 1) {
      marcarError(mesesPlazo);
      valido = false;
    }

    if (!penalidadTexto || !penalidadTexto.value.trim()) {
      marcarError(penalidadTexto);
      valido = false;
    }
  }

  return valido;
}
document.addEventListener("DOMContentLoaded", () => {
  cargarVistaContratoFibra();
  inicializarPaqueteYPrecio();
  initSignaturePads();
  bindSignatureEvents();
  cargarNumeroContrato();
  bindServicioInternetEvents();
});
