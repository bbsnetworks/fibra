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

/* =========================
   DATOS MOCK
========================= */
function getMockFibra() {
  return {
    suscriptor: {
      nombre: "PEDRO",
      apellidoPaterno: "DIAZ",
      apellidoMaterno: "LOPEZ",
      calle: "AV. MORELOS",
      numeroExterior: "147",
      numeroInterior: "B",
      colonia: "CENTRO",
      municipio: "URIANGATO",
      estado: "GUANAJUATO",
      cp: "38980",
      rfc: "DILP900101ABC",
      telefono: "4451234567",
      tipoTelefono: "movil"
    },

    servicio: {
      descripcionPaquete: "FIBRA 100 MEGAS",
      mensualidad: "450",
      fechaPago: "1 AL 5 DE CADA MES",
      aplicaReconexcion: "si",
      montoReconexcion: "500",
      nomNumeral: "5.1.2.1",
      tipoVigencia: "plazo_forzoso",
      mesesPlazo: "12",
      penalidadTexto: "20% DE LOS MESES PENDIENTES"
    },

    equipo: {
      tipoEntregaEquipo: "comodato",
      marcaEquipo: "HUAWEI",
      modeloEquipo: "HG8145V5",
      numeroSerie: "SN123456789",
      numeroEquipos: "1",
      costoTotalEquipo: "1500",
      modalidadPagoEquipo: "unico",
      costoDiferido: "100",
      mesesDiferido: "12"
    },

    instalacion: {
      domicilioInstalacion: "AV. MORELOS 147, CENTRO, URIANGATO",
      fechaInstalacion: "2026-03-26",
      horaInstalacion: "13:30",
      costoInstalacion: "1500"
    },

    metodoPago: {
      efectivo: true,
      transferencia: true,
      deposito: true,
      tiendasServicios: true,
      tarjeta: true,
      domiciliado: true,
      enLinea: true,
      centrosServicio: true,
      datosMetodoPago: "TRANSFERENCIA A BANCO BBVA, REF. 123456"
    },

    tarjeta: {
      autorizaCargoTarjeta: "si",
      mesesCargoTarjeta: "12",
      banco: "BBVA",
      numeroTarjeta: "** **** **** 1234"
    },

    serviciosAdicionales: [
      { Nombre: "IP PUBLICA", costo: "100",descripcion: "IP PUBLICA descripcion" },
      { Nombre: "EXTENSOR WIFI", costo: "80", descripcion: "EXTENSOR WIFI descripcion" }
    ],

    conceptosFacturables: [
      { nombre: "CAMBIO DE DOMICILIO", costo: "300", descripcion: "CAMBIO DE DOMICILIO descripcion" },
      { nombre: "VISITA TECNICA EXTRA", costo: "150", descripcion: "VISITA TECNICA EXTRA descripcion" }
    ],

    envioElectronico: {
      factura: "no",
      cartaDerechos: "no",
      contratoAdhesion: "no",
      medioElectronico: "otro",
      correoElectronico: "cliente@correo.com",
      otroMedioElectronico: "si",
      numeroOtroMedio: "1234567890"
    },

    usoInformacion: {
      autorizaCederInfo: "si",
      autorizaLlamadasPromo: "no"
    },

    cierre: {
      ciudadFirma: "URIANGATO",
      diaFirma: "26",
      mesFirma: "MARZO",
      anioFirma: "2026"
    }
  };
}

/* =========================
   MAPA DE POSICIONES
   AJÚSTALO POCO A POCO
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

  firmaProveedor: { x: 115, y: 385, w: 130, h: 28 },
  firmaSuscriptor: { x: 355, y: 385, w: 130, h: 28 }
}
};

/* =========================
   HELPERS
========================= */
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
function drawBox(doc, cfg, color = [255, 0, 0]) {
  if (!cfg) return;
  doc.setDrawColor(...color);
  doc.setLineWidth(0.8);
  doc.rect(cfg.x, cfg.y, cfg.w, cfg.h);
}

/* =========================
   OPCIONAL: guías visuales
========================= */
function drawGuides(doc) {
  doc.setDrawColor(255, 0, 0);
  doc.setLineWidth(0.2);

  for (let y = 0; y <= 792; y += 20) {
    doc.line(0, y, 565, y);
  }

  for (let x = 0; x <= 565; x += 20) {
    doc.line(x, 0, x, 792);
  }
}

/* =========================
   GENERADOR PREVIEW
========================= */
async function generarPreviewFibra() {
  try {
    const datos = getMockFibra();

    const [
      image1, image2, image3, image4, image5,
      image6, image7, image8, image9,
      firmaProveedor
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
      loadImage("../img/firma-s.png")
    ]);

    const pdf = new jsPDF("p", "pt", "letter");

    /* ========= PÁGINA 1 ========= */
    pdf.addImage(image1, "JPEG", 0, 0, 565, 792);
     // drawGuides(pdf);

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

    // // fijo / móvil
     markCircle(pdf, datos.suscriptor.tipoTelefono === "fijo", 68, 219, 3);
     markCircle(pdf, datos.suscriptor.tipoTelefono === "movil", 146, 218, 3);

     write(pdf, datos.servicio.descripcionPaquete, mapaFibra.p1.paquete);
     write(pdf, datos.servicio.mensualidad, mapaFibra.p1.mensualidad);
     splitAndWrite(pdf, datos.servicio.fechaPago, mapaFibra.p1.fechaPago.x, mapaFibra.p1.fechaPago.y, 70);

    // // reconexión
     markCircle(pdf, datos.servicio.aplicaReconexcion === "si", 209, 372, 3);
     markCircle(pdf, datos.servicio.aplicaReconexcion === "no", 215, 303, 3);
     write(pdf, datos.servicio.montoReconexcion, mapaFibra.p1.montoReconexcion);

    // // vigencia
     markCircle(pdf, datos.servicio.tipoVigencia === "indefinido", 357, 250, 3);
     markCircle(pdf, datos.servicio.tipoVigencia === "plazo_forzoso", 408, 320, 3);
     write(pdf, datos.servicio.mesesPlazo, mapaFibra.p1.mesesPlazo);
    // splitAndWrite(pdf, datos.servicio.penalidadTexto, mapaFibra.p1.penalidadTexto.x, mapaFibra.p1.penalidadTexto.y, 140);

    // // equipo
     markCircle(pdf, datos.equipo.tipoEntregaEquipo === "comodato", 146.5, 436.5, 3);
    // markCircle(pdf, datos.equipo.tipoEntregaEquipo === "compraventa", 406, 350, 3);7

     write(pdf, datos.equipo.marcaEquipo, mapaFibra.p1.marcaEquipo);
     write(pdf, datos.equipo.modeloEquipo, mapaFibra.p1.modeloEquipo);
     write(pdf, datos.equipo.numeroSerie, mapaFibra.p1.numeroSerie);
     write(pdf, datos.equipo.numeroEquipos, mapaFibra.p1.numeroEquipos);

    // markCircle(pdf, datos.equipo.modalidadPagoEquipo === "unico", 386, 442, 3);
    // markCircle(pdf, datos.equipo.modalidadPagoEquipo === "diferido", 350, 442, 3);

     write(pdf, datos.equipo.costoTotalEquipo, mapaFibra.p1.costoTotalEquipo);
     write(pdf, datos.equipo.costoDiferido, mapaFibra.p1.costoDiferido);
     write(pdf, datos.equipo.mesesDiferido, mapaFibra.p1.mesesDiferido);

    // // instalación
     splitAndWrite(pdf, datos.instalacion.domicilioInstalacion, mapaFibra.p1.domicilioInstalacion.x, mapaFibra.p1.domicilioInstalacion.y, 250);
     write(pdf, datos.instalacion.fechaInstalacion, mapaFibra.p1.fechaInstalacion);
     write(pdf, datos.instalacion.horaInstalacion, mapaFibra.p1.horaInstalacion);
     write(pdf, datos.instalacion.costoInstalacion, mapaFibra.p1.costoInstalacion);

    // // métodos de pago
     markCircle(pdf, datos.metodoPago.efectivo, 34, 588, 3);
     markCircle(pdf, datos.metodoPago.tarjeta, 32, 631, 3);
     markCircle(pdf, datos.metodoPago.transferencia, 34, 600, 3);
     markCircle(pdf, datos.metodoPago.deposito, 32, 610.5, 3);
     markCircle(pdf, datos.metodoPago.tiendasServicios, 32, 620.5, 3);
     markCircle(pdf, datos.metodoPago.domiciliado, 32, 641, 3);
     markCircle(pdf, datos.metodoPago.enLinea, 32, 651.5, 3);
     markCircle(pdf, datos.metodoPago.centrosServicio, 32, 662, 3);
     splitAndWrite(pdf, datos.metodoPago.datosMetodoPago, mapaFibra.p1.datosMetodoPago.x, mapaFibra.p1.datosMetodoPago.y, 420);

    // // cargo tarjeta
     markCircle(pdf, datos.tarjeta.autorizaCargoTarjeta === "si", 147.5, 708, 3);
     markCircle(pdf, datos.tarjeta.autorizaCargoTarjeta === "no", 177.5, 708, 3);

     write(pdf, datos.tarjeta.mesesCargoTarjeta, mapaFibra.p1.mesesCargoTarjeta);

     //drawBox(pdf, mapaFibra.p1.firmaSuscriptor, [255, 0, 0]);

pdf.addImage(
  firmaProveedor,
  "PNG",
  mapaFibra.p1.firmaSuscriptor.x,
  mapaFibra.p1.firmaSuscriptor.y,
  mapaFibra.p1.firmaSuscriptor.w,
  mapaFibra.p1.firmaSuscriptor.h
);

     
        
    
    /* ========= PÁGINA 2 ========= */
    pdf.addPage();
    pdf.addImage(image2, "JPEG", 0, 0, 565, 792);

    write(pdf, datos.tarjeta.banco, mapaFibra.p2.banco);
    write(pdf, datos.tarjeta.numeroTarjeta, mapaFibra.p2.numeroTarjeta);

     write(pdf, datos.serviciosAdicionales[0]?.Nombre, mapaFibra.p2.servicio1Name);
     write(pdf, datos.serviciosAdicionales[0]?.costo, mapaFibra.p2.servicio1Costo);
     write(pdf, datos.serviciosAdicionales[0]?.descripcion, mapaFibra.p2.servicio1Desc);
     write(pdf, datos.serviciosAdicionales[1]?.Nombre, mapaFibra.p2.servicio2Name);
     write(pdf, datos.serviciosAdicionales[1]?.costo, mapaFibra.p2.servicio2Costo);
     write(pdf, datos.serviciosAdicionales[1]?.descripcion, mapaFibra.p2.servicio2Desc);

     write(pdf, datos.conceptosFacturables[0]?.nombre, mapaFibra.p2.fact1Name);
     write(pdf, datos.conceptosFacturables[0]?.costo, mapaFibra.p2.fact1Costo);
     write(pdf, datos.conceptosFacturables[0]?.descripcion, mapaFibra.p2.fact1Desc);
     write(pdf, datos.conceptosFacturables[1]?.nombre, mapaFibra.p2.fact2Name);
     write(pdf, datos.conceptosFacturables[1]?.costo, mapaFibra.p2.fact2Costo);
     write(pdf, datos.conceptosFacturables[1]?.descripcion, mapaFibra.p2.fact2Desc);

    // // envío electrónico
     markCircle(pdf, datos.envioElectronico.factura === "si", 118, 539, 3);
     markCircle(pdf, datos.envioElectronico.factura === "no", 156, 539, 3);

     markCircle(pdf, datos.envioElectronico.cartaDerechos === "si", 325, 538.5, 3);
     markCircle(pdf, datos.envioElectronico.cartaDerechos === "no", 361, 538.5, 3);

     markCircle(pdf, datos.envioElectronico.contratoAdhesion === "si", 484, 538.5, 3);
     markCircle(pdf, datos.envioElectronico.contratoAdhesion === "no", 518.5, 538.5, 3);

     markCircle(pdf, datos.envioElectronico.medioElectronico === "correo", 101.5, 571.5, 3);
     write(pdf, datos.envioElectronico.correoElectronico, mapaFibra.p2.correoElectronico);

     markCircle(pdf, datos.envioElectronico.medioElectronico === "otro", 101.5, 592, 3);
     write(pdf, datos.envioElectronico.otroMedioElectronico, { x: 140, y: 593 });
     write(pdf, datos.envioElectronico.numeroOtroMedio, { x: 255, y: 594 });

    // // uso de información
     markCircle(pdf, datos.usoInformacion.autorizaCederInfo === "si", 128, 641, 3);
     markCircle(pdf, datos.usoInformacion.autorizaCederInfo === "no", 168, 641, 3);

     markCircle(pdf, datos.usoInformacion.autorizaLlamadasPromo === "si", 168, 675, 3);
     markCircle(pdf, datos.usoInformacion.autorizaLlamadasPromo === "no", 212, 675, 3);

    

    //firma suscriptor: reusa firma proveedor solo para calibrar
    pdf.addImage(firmaProveedor, "PNG", mapaFibra.p2.firmaProveedor.x, mapaFibra.p2.firmaProveedor.y, mapaFibra.p2.firmaProveedor.w, mapaFibra.p2.firmaProveedor.h);
    //firma suscriptor 2: reusa firma proveedor solo para calibrar
    pdf.addImage(firmaProveedor, "PNG", mapaFibra.p2.firmaProvedor2.x, mapaFibra.p2.firmaProvedor2.y, mapaFibra.p2.firmaProvedor2.w, mapaFibra.p2.firmaProvedor2.h);

    //firma suscriptor 3: reusa firma proveedor solo para calibrar
    pdf.addImage(firmaProveedor, "PNG", mapaFibra.p2.firmaProvedor3.x, mapaFibra.p2.firmaProvedor3.y, mapaFibra.p2.firmaProvedor3.w, mapaFibra.p2.firmaProvedor3.h);

    //firma suscriptor 4: reusa firma proveedor solo para calibrar
    pdf.addImage(firmaProveedor, "PNG", mapaFibra.p2.firmaProvedor4.x, mapaFibra.p2.firmaProvedor4.y, mapaFibra.p2.firmaProvedor4.w, mapaFibra.p2.firmaProvedor4.h);
    // firma de prueba del suscriptor: reusa firma proveedor solo para calibrar
    //pdf.addImage(firmaProveedor, "PNG", mapaFibra.p2.firmaSuscriptor.x, mapaFibra.p2.firmaSuscriptor.y, mapaFibra.p2.firmaSuscriptor.w, mapaFibra.p2.firmaSuscriptor.h);

    /* ========= PÁGINA 3 ========= */
pdf.addPage();
pdf.addImage(image3, "JPEG", 0, 0, 565, 792);

// drawGuides(pdf); // actívalo si quieres ajustar fino

write(pdf, datos.cierre.ciudadFirma, mapaFibra.p3.ciudadFirma);
write(pdf, datos.cierre.diaFirma, mapaFibra.p3.diaFirma);
write(pdf, datos.cierre.mesFirma, mapaFibra.p3.mesFirma);
write(pdf, datos.cierre.anioFirma, mapaFibra.p3.anioFirma);

// guía visual de firmas
drawBox(pdf, mapaFibra.p3.firmaProveedor, [255, 0, 0]);
drawBox(pdf, mapaFibra.p3.firmaSuscriptor, [0, 0, 255]);

// firma proveedor de prueba
pdf.addImage(
  firmaProveedor,
  "PNG",
  mapaFibra.p3.firmaProveedor.x,
  mapaFibra.p3.firmaProveedor.y,
  mapaFibra.p3.firmaProveedor.w,
  mapaFibra.p3.firmaProveedor.h
);

// firma suscriptor de prueba
pdf.addImage(
  firmaProveedor,
  "PNG",
  mapaFibra.p3.firmaSuscriptor.x,
  mapaFibra.p3.firmaSuscriptor.y,
  mapaFibra.p3.firmaSuscriptor.w,
  mapaFibra.p3.firmaSuscriptor.h
);
    /* ========= RESTO DE PÁGINAS SOLO BASE ========= */
    
    pdf.addPage(); pdf.addImage(image4, "JPEG", 0, 0, 565, 792);
    pdf.addPage(); pdf.addImage(image5, "JPEG", 0, 0, 565, 792);
    pdf.addPage(); pdf.addImage(image6, "JPEG", 0, 0, 565, 792);
    pdf.addPage(); pdf.addImage(image7, "JPEG", 0, 0, 565, 792);
    pdf.addPage(); pdf.addImage(image8, "JPEG", 0, 0, 565, 792);
    pdf.addPage(); pdf.addImage(image9, "JPEG", 0, 0, 565, 792);

    const blob = pdf.output("blob");
    const url = URL.createObjectURL(blob);
    window.open(url, "_blank");

  } catch (error) {
    console.error(error);
    Swal.fire({
      ...swalDark,
      icon: "error",
      title: "Error",
      text: error.message || "No se pudo generar la vista previa."
    });
  }
}

/* =========================
   BOTÓN DE PRUEBA
========================= */
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("btnPreviewFibra");
  if (btn) {
    btn.addEventListener("click", generarPreviewFibra);
  }
});