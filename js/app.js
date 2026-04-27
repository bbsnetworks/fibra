function loadImage(url) {
  return new Promise((resolve) => {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.responseType = "blob";
    xhr.onload = function (e) {
      const reader = new FileReader();
      reader.onload = function (event) {
        const res = event.target.result;
        resolve(res);
      };
      const file = this.response;
      reader.readAsDataURL(file);
    };
    xhr.send();
  });
}
//window.onload = datoContrato();
let signatureImageSaved1 = null;
let signatureImageSaved2 = null;
let signaturePad = null;
let signaturePad2 = null;
let signaturePadModal = null;
let activePreviewCanvas = null;
let activePreviewPad = null;

//cambio de precio
const ptarifa = document.getElementById("tarifa");
const mensualidad = document.getElementById("totalm");
const mpago = document.getElementById("mpago");
const banco = document.getElementById("banco");
const ntarjeta = document.getElementById("ntarjeta");

ptarifa.addEventListener("change", (event) => {
  if (ptarifa.value == 1) {
    mensualidad.value = "250";
  } else if (ptarifa.value == 2) {
    mensualidad.value = "350";
  } else if (ptarifa.value == 3) {
    mensualidad.value = "450";
  } else if (ptarifa.value == 4) {
    mensualidad.value = "500";
  } else if (ptarifa.value == 5) {
    mensualidad.value = "500";
  } else if (ptarifa.value == 6) {
    mensualidad.value = "600";
  } else if (ptarifa.value == 7) {
    mensualidad.value = "350";
  }
});

async function evidencia(mostrarAlerta = true) {
  const dataFile = new FormData();

  const fileInput = document.getElementById("evidencia");
  const ncontrato = document.getElementById("ncontrato");
  const file = fileInput.files[0];

  if (!file) {
  if (mostrarAlerta) {
    Swal.fire({
      ...swalDark,
      title: "Selecciona un archivo",
      icon: "warning",
    });
  }
  return;
}

if (!ncontrato.value.trim()) {
  if (mostrarAlerta) {
    Swal.fire({
      ...swalDark,
      title: "Falta el número de contrato",
      icon: "warning",
    });
  }
  return;
}

  dataFile.append("archivo", file);
  dataFile.append("numero", ncontrato.value.trim());

  try {
    const response = await fetch("./php/evidencia.php", {
      method: "POST",
      body: dataFile,
    });

    const data = await response.json();

    if (!response.ok || !data.ok) {
      throw new Error(data.mensaje || "No se pudo guardar el archivo.");
    }

    if (mostrarAlerta) {
  Swal.fire({
    ...swalDark,
    title: "¡Guardado!",
    text: data.mensaje,
    icon: "success",
  });
}

  } catch (error) {
    console.error(error);
    if (mostrarAlerta) {
  Swal.fire({
    ...swalDark,
    title: "No se pudo guardar el archivo",
    text: error.message,
    icon: "error",
    confirmButtonText: "OK",
    width: "35rem",
  });
}
  }
}
async function validarNumeroContrato(numero) {
  try {
    const formData = new FormData();
    formData.append("numero", numero);

    const response = await fetch("./php/validar_contrato.php", {
      method: "POST",
      body: formData
    });

    const data = await response.json();

    if (!data.ok) throw new Error(data.message);

    return data;

  } catch (error) {
    console.error("Error validando contrato:", error);
    return null;
  }
}
const inputContrato = document.getElementById("ncontrato");

inputContrato.addEventListener("blur", async () => {
  const numero = inputContrato.value.trim();
  if (!numero) return;

  const res = await validarNumeroContrato(numero);
  if (!res) return;

  // limpiar estilos previos
  inputContrato.classList.remove("ring-2", "ring-red-500", "ring-green-500");

  if (!res.disponible) {
    inputContrato.classList.add("ring-2", "ring-red-500");

    let mensaje = "";

    if (res.clienteExiste) mensaje += "Cliente ya existe. ";
    if (res.contratoExiste) mensaje += "Contrato ya existe.";

    Swal.fire({
      ...swalDark,
      icon: "error",
      title: "Número no disponible",
      text: mensaje
    });

  } else {
    inputContrato.classList.add("ring-2", "ring-green-500");
  }
});
function resetFormularioCompleto() {
  const form = document.querySelector("#form");
  if (form) form.reset();

  document.querySelectorAll(".input-error").forEach((el) => {
    el.classList.remove("input-error");
  });

  document.querySelectorAll(".ring-red-500, .ring-green-500").forEach((el) => {
  el.classList.remove("ring-2", "ring-red-500", "ring-green-500");
});

  if (signaturePad) signaturePad.clear();
  if (signaturePad2) signaturePad2.clear();
  if (signaturePadModal) signaturePadModal.clear();

  signatureImageSaved1 = null;
  signatureImageSaved2 = null;

  const canvas1 = document.getElementById("signature-canvas");
  const canvas2 = document.getElementById("signature-canvas2");

  if (canvas1) {
    const ctx1 = canvas1.getContext("2d");
    ctx1.clearRect(0, 0, canvas1.width, canvas1.height);
  }

  if (canvas2) {
    const ctx2 = canvas2.getContext("2d");
    ctx2.clearRect(0, 0, canvas2.width, canvas2.height);
  }

  const fileInput = document.getElementById("evidencia");
  if (fileInput) fileInput.value = "";

  const banco = document.getElementById("banco");
  const ntarjeta = document.getElementById("ntarjeta");
  const totalm = document.getElementById("totalm");
  const descm = document.getElementById("descm");

  if (banco) {
    banco.value = "";
    banco.disabled = true;
  }

  if (ntarjeta) {
    ntarjeta.value = "";
    ntarjeta.disabled = true;
  }

  if (totalm) totalm.value = "";
  if (descm) descm.value = "";
}
async function cargarSiguienteContrato() {
  try {
    const response = await fetch("./php/obtener_siguiente_cliente.php");
    const data = await response.json();

    if (!response.ok || !data.ok) {
      throw new Error(data.message || "No se pudo obtener el siguiente número.");
    }

    const numero = String(data.numero);
    const ncontrato = document.getElementById("ncontrato");
    const contratoBadge = document.getElementById("contratoBadge");

    if (ncontrato) {
      ncontrato.value = numero;
      ncontrato.classList.remove("ring-red-500", "ring-green-500", "ring-2");
    }

    if (contratoBadge) contratoBadge.textContent = numero;

    const res = await validarNumeroContrato(numero);
    if (ncontrato && res) {
      ncontrato.classList.remove("ring-red-500", "ring-green-500", "ring-2");

      if (res.disponible) {
        ncontrato.classList.add("ring-2", "ring-green-500");
      } else {
        ncontrato.classList.add("ring-2", "ring-red-500");
      }
    }

  } catch (error) {
    console.error("Error al cargar contrato:", error);
  }
}
function validarFormulario() {
  let esValido = true;
  const errores = [];

  const campos = document.querySelectorAll(".requerido");

  campos.forEach((campo) => {
    if (campo.disabled) return;

    const valor = campo.value.trim();
    if (!valor) {
  campo.classList.add("input-error");
  const label = document.querySelector(`label[for="${campo.id}"]`);
  errores.push(label ? label.innerText : campo.name || "Campo requerido");
  esValido = false;
} else {
  campo.classList.remove("input-error");
}
  });

  const checkboxesObligatorios = [
    { id: "check-c", label: "Aceptar términos y condiciones" },
    { id: "ccontrato", label: "Copia de contrato y carátula" },
    { id: "cdminimos", label: "Carta de derechos mínimos" }
  ];

  checkboxesObligatorios.forEach((item) => {
    const checkbox = document.getElementById(item.id);
    if (!checkbox) return;

    if (!checkbox.checked) {
      errores.push(item.label);
      checkbox.classList.add("ring-2", "ring-red-500");
      esValido = false;
    } else {
      checkbox.classList.remove("ring-2", "ring-red-500");
    }
  });

  const firma1Wrapper = document.getElementById("signature-preview-wrapper");
  const firma2Wrapper = document.getElementById("signature-preview-wrapper-2");

  const firma1Existe =
    !!signatureImageSaved1 || (signaturePad && !signaturePad.isEmpty());

  const firma2Existe =
    !!signatureImageSaved2 || (signaturePad2 && !signaturePad2.isEmpty());

  if (!firma1Existe) {
    errores.push("Firma del contrato");
    firma1Wrapper?.classList.add("ring-2", "ring-red-500");
    esValido = false;
  } else {
    firma1Wrapper?.classList.remove("ring-2", "ring-red-500");
  }

  const modemSelect = document.getElementById("modemt");

  // 1 = Comodato -> requiere segunda firma
  if (modemSelect && modemSelect.value === "1" && !firma2Existe) {
    errores.push("Firma del pagaré");
    firma2Wrapper?.classList.add("ring-2", "ring-red-500");
    esValido = false;
  } else {
    firma2Wrapper?.classList.remove("ring-2", "ring-red-500");
  }

  if (!esValido) {
    Swal.fire({
	  ...swalDark,	
      icon: "error",
      title: "Campos incompletos",
      html: `<ul class="text-left">${errores.map((e) => `<li>• ${e}</li>`).join("")}</ul>`,
    });
  }

  return esValido;
}
document.querySelectorAll(".requerido").forEach((campo) => {
  campo.addEventListener("input", () => {
    if (campo.value.trim() !== "") {
      campo.classList.remove("input-error");
    }
  });

  campo.addEventListener("change", () => {
    if (campo.value.trim() !== "") {
      campo.classList.remove("input-error");
    }
  });
});
mpago.addEventListener("change", (event) => {
  if (mpago.value == 2) {
    banco.disabled = false;
    ntarjeta.disabled = false;
  } else {
    banco.disabled = true;
    ntarjeta.disabled = true;
  }
});

//cambio reconexion
const reconexion = document.getElementById("reconexion");
const mdesconexion = document.getElementById("descm");

reconexion.addEventListener("change", (event) => {
  //console.log(reconexion.value);
  if (reconexion.value == 1) {
    mdesconexion.value = "0";
  } else if (reconexion.value == 2) {
    mdesconexion.value = "500";
  }
});

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

window.addEventListener("load", async () => {
  const canvas = document.querySelector("#signature-canvas");
  const canvas2 = document.querySelector("#signature-canvas2");
  const modalCanvas = document.querySelector("#signature-canvas-modal");

  const signatureModal = document.getElementById("signatureModal");
  const openSignatureModalBtn1 = document.getElementById("openSignatureModal");
  const openSignatureModalBtn2 = document.getElementById("openSignatureModal2");
  const closeSignatureModalBtn = document.getElementById("closeSignatureModal");
  const clearSignatureModalPadBtn = document.getElementById(
    "clearSignatureModalPad",
  );
  const saveSignatureModalBtn = document.getElementById("saveSignatureModal");
  const clearSignaturePreviewBtn1 = document.getElementById(
    "clearSignaturePreview",
  );
  const clearSignaturePreviewBtn2 = document.getElementById(
    "clearSignaturePreview2",
  );
  

  function resizeCanvas(canvasEl, padInstance, preserve = false) {
    if (!canvasEl || !padInstance) return;

    let data = null;
    if (preserve && !padInstance.isEmpty()) {
      data = canvasEl.toDataURL("image/png");
    }

    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvasEl.getBoundingClientRect();

    canvasEl.width = rect.width * ratio;
    canvasEl.height = rect.height * ratio;

    const ctx = canvasEl.getContext("2d");
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(ratio, ratio);

    if (data) {
      padInstance.fromDataURL(data);
    } else {
      padInstance.clear();
    }
  }

  signaturePad = new SignaturePad(canvas, {
  backgroundColor: "rgb(255,255,255)",
  penColor: "#1d4ed8", // azul rey
  minWidth: 2.2,
  maxWidth: 4.0,
  velocityFilterWeight: 0.7
});

signaturePad2 = new SignaturePad(canvas2, {
  backgroundColor: "rgb(255,255,255)",
  penColor: "#1d4ed8",
  minWidth: 2.2,
  maxWidth: 4.0,
  velocityFilterWeight: 0.7
});

signaturePadModal = new SignaturePad(modalCanvas, {
  backgroundColor: "rgb(255,255,255)",
  penColor: "#1d4ed8",
  minWidth: 2.2,
  maxWidth: 4.0,
  velocityFilterWeight: 0.7
});

  resizeCanvas(canvas, signaturePad);
  resizeCanvas(canvas2, signaturePad2);

  function openSignatureModal(targetCanvas, targetPad) {
    activePreviewCanvas = targetCanvas;
    activePreviewPad = targetPad;

    signatureModal.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");

    setTimeout(() => {
      resizeCanvas(modalCanvas, signaturePadModal);
      signaturePadModal.clear();

      let firmaGuardada = null;

      if (targetCanvas.id === "signature-canvas") {
        firmaGuardada = signatureImageSaved1;
      } else if (targetCanvas.id === "signature-canvas2") {
        firmaGuardada = signatureImageSaved2;
      }

      if (firmaGuardada) {
        signaturePadModal.fromDataURL(firmaGuardada);
      } else if (!targetPad.isEmpty()) {
        const dataUrl = targetCanvas.toDataURL("image/png");
        signaturePadModal.fromDataURL(dataUrl);
      }
    }, 120);
  }

  function closeSignatureModal() {
    signatureModal.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
    signaturePadModal.clear();
    activePreviewCanvas = null;
    activePreviewPad = null;
  }

  async function saveModalSignatureToPreview() {
    if (!activePreviewCanvas || !activePreviewPad) return;
    if (!signaturePadModal || signaturePadModal.isEmpty()) return;

    const dataUrl = modalCanvas.toDataURL("image/png");

    // Guardar imagen según qué firma sea
    if (activePreviewCanvas.id === "signature-canvas") {
      signatureImageSaved1 = dataUrl;
    } else if (activePreviewCanvas.id === "signature-canvas2") {
      signatureImageSaved2 = dataUrl;
    }

    // Limpiar pad y canvas previo
    activePreviewPad.clear();

    // Pintar imagen directamente en el canvas pequeño
    await drawDataUrlOnCanvas(activePreviewCanvas, dataUrl);

    closeSignatureModal();
  }

  openSignatureModalBtn1?.addEventListener("click", () => {
    openSignatureModal(canvas, signaturePad);
  });

  openSignatureModalBtn2?.addEventListener("click", () => {
    openSignatureModal(canvas2, signaturePad2);
  });

  document
    .getElementById("signature-preview-wrapper")
    ?.addEventListener("click", () => {
      openSignatureModal(canvas, signaturePad);
    });

  document
    .getElementById("signature-preview-wrapper-2")
    ?.addEventListener("click", () => {
      openSignatureModal(canvas2, signaturePad2);
    });

  closeSignatureModalBtn?.addEventListener("click", closeSignatureModal);
  clearSignatureModalPadBtn?.addEventListener("click", () =>
    signaturePadModal.clear(),
  );
  saveSignatureModalBtn?.addEventListener("click", saveModalSignatureToPreview);

  clearSignaturePreviewBtn1?.addEventListener("click", () => {
  signaturePad.clear();
  signatureImageSaved1 = null;
});
clearSignaturePreviewBtn2?.addEventListener("click", () => {
  signaturePad2.clear();
  signatureImageSaved2 = null;
});

  window.addEventListener("resize", () => {
    resizeCanvas(canvas, signaturePad, true);
    resizeCanvas(canvas2, signaturePad2, true);

    if (!signatureModal.classList.contains("hidden")) {
      resizeCanvas(modalCanvas, signaturePadModal, true);
    }
  });

  window.addEventListener("orientationchange", () => {
    setTimeout(() => {
      resizeCanvas(canvas, signaturePad, true);
      resizeCanvas(canvas2, signaturePad2, true);

      if (!signatureModal.classList.contains("hidden")) {
        resizeCanvas(modalCanvas, signaturePadModal, true);
      }
    }, 250);
  });
  const form = document.querySelector("#form");

  form.addEventListener("submit", (e) => {
    e.preventDefault();
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
    let ttipo = document.querySelector('input[name="ttipo"]:checked').value;
    let tarifa = document.getElementById("tarifa").value;
    let total = document.getElementById("totalm").value;
    let plazo = document.getElementById("pmeses").value;
    let reconexion = document.getElementById("reconexion").value;
    let mdesco = document.getElementById("descm").value;
    let modemt = document.getElementById("modemt").value;
    let marca = document.getElementById("marca").value;
    let modelo = document.getElementById("modelo").value;
    let serie = document.getElementById("serie").value;
    let nequipos = document.getElementById("nequipos").value;
    let tpago = document.getElementById("tpago").value;
    let cequipos = document.getElementById("cequipos").value;
    let domicilioi = document.getElementById("domicilioi").value;
    let fechai = document.getElementById("fechai").value;
    let horai = document.getElementById("horai").value;
    let costoi = document.getElementById("costoi").value;
    let acargo = document.querySelector('input[name="acargo"]:checked').value;
    let mpago = document.getElementById("mpago").value;
    let cmes = document.getElementById("cmes").value;
    let banco = document.getElementById("banco").value;
    let ntarjeta = document.getElementById("ntarjeta").value;
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
    let cdminimos = document.getElementById("cdminimos").checked;
    let ciudad = document.getElementById("ciudad").value;
    let checkc = document.getElementById("check-c").checked;
    let scontrato = document.getElementById("scontrato").checked;
    let ncontrato = document.getElementById("ncontrato").value;
    //console.log(ncontrato);

    let fechac = document.getElementById("fechac");

    let yearContrato = fechac.value.substring(0, 4);
    let mes;

    let monthContrato = fechac.value.substring(5, 7);
    switch (monthContrato) {
      case "01":
        mes = "Enero";
        break;
      case "02":
        mes = "Febrero";
        break;
      case "03":
        mes = "Marzo";
        break;
      case "04":
        mes = "Abril";
        break;
      case "05":
        mes = "Mayo";
        break;
      case "06":
        mes = "Junio";
        break;
      case "07":
        mes = "Julio";
        break;
      case "08":
        mes = "Agosto";
        break;
      case "09":
        mes = "Septiembre";
        break;
      case "10":
        mes = "Octubre";
        break;
      case "11":
        mes = "Noviembre";
        break;
      case "12":
        mes = "Diciembre";
        break;
    }

    let dayContrato = fechac.value.substring(8, 10);

    const inputs = document.querySelectorAll(".requerido");
    let j = 0;
    for (var i = 0; i < inputs.length; i++) {
      if (
        inputs[i].value == "" ||
        inputs[i].value == null ||
        inputs[i].value == undefined
      ) {
        //console.log("falta algun dato de ingresar");
        //console.log(inputs[i].value);
      } else {
        //console.log("lleno");
        //console.log(inputs[i].value);
        j++;
      }
    }

    if (validarFormulario()) {
      //document.querySelector("#resultado").innerHTML="Documento creado exitosamente";
      //document.querySelector("#resultado").style.color='green';
      generatePDF(
        idcontrato,
        name,
        rlegal,
        street,
        number,
        colonia,
        municipio,
        cp,
        estado,
        rfc,
        telefono,
        ttipo,
        tarifa,
        total,
        reconexion,
        mdesco,
        plazo,
        modemt,
        marca,
        modelo,
        serie,
        nequipos,
        tpago,
        cequipos,
        domicilioi,
        fechai,
        horai,
        costoi,
        acargo,
        mpago,
        cmes,
        banco,
        ntarjeta,
        sadicional1,
        sdescripcion1,
        scosto1,
        sadicional2,
        sdescripcion2,
        scosto2,
        fadicional1,
        fdescripcion1,
        fcosto1,
        fadicional2,
        fdescripcion2,
        fcosto2,
        ccontrato,
        cdminimos,
        yearContrato,
        mes,
        dayContrato,
        monthContrato,
        scontrato,
        ciudad,
        ncontrato,
      );
    } else {
      //document.querySelector("#resultado").style.color='red';
      if (j < inputs.length && scontrato) {
        //document.querySelector("#resultado").innerHTML="Faltan Datos por llenar";
        //document.getElementById("euser").classList.add('d-none');
      } else {
        //document.querySelector("#resultado").innerHTML="el usurio ya existe, si deseas continuar marca la casilla de arriba";
        //document.getElementById("euser").classList.remove('d-none');
      }
    }
  });
  await cargarSiguienteContrato();
  //const image = await loadImage("../img/1.jpg");
});

async function generatePDF(
  idcontrato,
  name,
  rlegal,
  street,
  number,
  colonia,
  municipio,
  cp,
  estado,
  rfc,
  telefono,
  ttipo,
  tarifa,
  total,
  reconexion,
  mdesco,
  plazo,
  modemt,
  marca,
  modelo,
  serie,
  nequipos,
  tpago,
  cequipos,
  domicilioi,
  fechai,
  horai,
  costoi,
  acargo,
  mpago,
  cmes,
  banco,
  ntarjeta,
  sadicional1,
  sdescripcion1,
  scosto1,
  sadicional2,
  sdescripcion2,
  scosto2,
  fadicional1,
  fdescripcion1,
  fcosto1,
  fadicional2,
  fdescripcion2,
  fcosto2,
  ccontrato,
  cdminimos,
  yearContrato,
  mes,
  dayContrato,
  monthContrato,
  scontrato,
  ciudad,
  ncontrato,
) {
  const image1 = await loadImage("./img/bbs-c-1.jpg");
  const image2 = await loadImage("./img/bbs-c-2.jpg");
  const image3 = await loadImage("./img/bbs-c-3.jpg");
  const image4 = await loadImage("./img/bbs-c-4.jpg");
  const image5 = await loadImage("./img/bbs-c-5.jpg");
  const image6 = await loadImage("./img/bbs-c-6.jpg");
  const image7 = await loadImage("./img/bbs-c-7.jpg");
  const image8 = await loadImage("./img/bbs-c-8.jpg");
  const image9 = await loadImage("./img/bbs-c-9.jpg");
  const image10 = await loadImage("./img/bbs-c-10.jpg");
  const image11 = await loadImage("./img/bbs-c-11.jpg");
  const firma = await loadImage("./img/firma-s.png");

  const fechacInput = document.getElementById("fechac");

  const numero = document.getElementById("ncontrato").value;

const validacion = await validarNumeroContrato(numero);

if (!validacion || !validacion.disponible) {
  Swal.fire({
    ...swalDark,
    icon: "error",
    title: "No se puede guardar",
    text: "El número de contrato ya está en uso."
  });
  return;
}

  const signatureImage =
  signatureImageSaved1 ||
  (signaturePad && !signaturePad.isEmpty() ? signaturePad.toDataURL() : null);

const signatureImage2 =
  signatureImageSaved2 ||
  (signaturePad2 && !signaturePad2.isEmpty() ? signaturePad2.toDataURL() : null);

  const pdf = new jsPDF("p", "pt", "letter");
  pdf.addImage(image1, "jpeg", 0, 0, 565, 792);
  // pdf2.addImage(image2, "jpeg", 0, 0, 565, 792);
  // pdf.addImage(signatureImage,'PNG',200,715,300,50);

  pdf.setFontSize(7);
  pdf.setFontStyle("bold");
  pdf.text(name, 200, 113);
  pdf.text(rlegal, 155, 126);
  pdf.text(street, 50, 157);
  pdf.text(number, 215, 157);
  pdf.text(colonia, 240, 157);
  pdf.text(municipio, 305, 157);
  pdf.text(cp, 377, 157);
  pdf.text(estado, 420, 157);
  pdf.text(rfc, 377, 183);
  //pdf.text(telefono,70,185);
  if (ttipo == "movil") {
    pdf.text(telefono, 230, 181);
    pdf.circle(83, 167, 3, "F");
  } else {
    pdf.text(telefono, 228, 182);
    pdf.circle(132, 167, 3, "F");
  }
  if (parseInt(tarifa) === 1) {
    pdf.text("Residencial 7 MBPS", 230, 240);
  } else if (parseInt(tarifa) === 2) {
    pdf.text("BBS Air 10", 230, 240);
  } else if (parseInt(tarifa) === 3) {
    pdf.text("Residencial 15 MBPS", 230, 240);
  } else if (parseInt(tarifa) === 4) {
    pdf.text("BBS Air 20", 230, 240);
  } else if (parseInt(tarifa) === 5) {
    pdf.text("Residencial 40 MBPS", 230, 240);
  } else if (parseInt(tarifa) === 6) {
    pdf.text("Residencial 50 MBPS", 230, 240);
  } else if (parseInt(tarifa) === 7) {
    pdf.text("BBS Air 30", 230, 240);
  }
  pdf.text(plazo, 425, 272);
  pdf.text("1 al 5", 455, 230);
  pdf.text("cada mes", 455, 236);
  pdf.text(total, 270, 252);

  if (parseInt(reconexion) === 1) {
    pdf.circle(215, 303, 3, "F");
    pdf.text("0", 285, 295);
    pdf.circle(357, 250, 3, "F");
    //pdf.circle(358,254,3,'F');
  } else if (parseInt(reconexion) === 2) {
    pdf.circle(193, 302, 3, "F");
    pdf.text("500", 285, 295);
  }

  if (parseInt(modemt) === 1) {
    pdf.circle(200, 349, 3, "F");
  } else if (parseInt(modemt) === 2) {
    pdf.circle(406, 350, 3, "F");
  }

  pdf.text(marca, 210, 365);
  pdf.text(modelo, 210, 376);
  pdf.text(serie, 210, 387);
  pdf.text(nequipos, 210, 399);

  if (parseInt(tpago) === 1) {
    pdf.circle(386, 442, 3, "F");
  } else if (parseInt(tpago) === 2) {
    pdf.circle(350, 442, 3, "F");
  } else if (parseInt(tpago) === 3) {
    //pdf.circle(332,450,3,'F');
  }

  pdf.text(cequipos, 242, 444);
  pdf.text(domicilioi, 190, 482);
  pdf.text(fechai, 180, 493);
  pdf.text(horai, 350, 493);
  pdf.text(costoi, 180, 506);

  if (acargo == "si") {
    pdf.circle(158, 645, 3, "F");
  } else {
    pdf.circle(199, 645, 3, "F");
  }

  if (parseInt(mpago) === 1) {
    pdf.circle(42, 583, 3, "F");
  } else if (parseInt(mpago) === 2) {
    pdf.circle(42, 593, 3, "F");
  } else if (parseInt(mpago) === 3) {
    pdf.circle(42, 603, 3, "F");
  } else if (parseInt(mpago) === 4) {
    pdf.circle(42, 613, 3, "F");
  }

  pdf.text(cmes, 455, 662);

  //end first page

  //second page
  pdf.addPage();
  pdf.addImage(image2, 0, 0, 565, 792);
  pdf.text(banco, 80, 91.5);
  pdf.text(ntarjeta, 330, 92);

  pdf.text(sadicional1, 120, 94);
  pdf.text(sdescripcion1, 50, 117);
  pdf.text(scosto1, 240, 117);

  pdf.text(sadicional2, 320, 94);
  pdf.text(sdescripcion2, 290, 117);
  pdf.text(scosto2, 445, 117);

  pdf.text(fadicional1, 120, 178);
  pdf.text(fdescripcion1, 50, 200);
  pdf.text(fcosto1, 240, 200);

  pdf.text(fadicional2, 320, 178);
  pdf.text(fdescripcion2, 290, 200);
  pdf.text(fcosto2, 445, 200);

  if (ccontrato == true) {
    pdf.circle(448, 226, 3, "F");
  } else {
    pdf.circle(464, 226, 3, "F");
  }

  if (cdminimos == true) {
    pdf.circle(448, 238, 3, "F");
  } else {
    pdf.circle(464, 238, 3, "F");
  }

  pdf.text(ncontrato, 155, 440);

  pdf.text(ciudad, 255, 496);
  pdf.text(dayContrato, 323, 496);

  pdf.text(mes, 360, 496);
  pdf.text(yearContrato, 415, 496);

  pdf.addImage(firma, "PNG", 70, 515, 200, 30);
  if (signatureImage) {
    pdf.addImage(signatureImage, "PNG", 270, 515, 200, 30);
  }

  //Third Page
  pdf.addPage();
  pdf.addImage(image3, 0, 0, 565, 792);

  //Fourth Page
  pdf.addPage();
  pdf.addImage(image4, 0, 0, 565, 792);

  //Fifth Page
  pdf.addPage();
  pdf.addImage(image5, 0, 0, 565, 792);

  //Sixth Page
  pdf.addPage();
  pdf.addImage(image6, 0, 0, 565, 792);

  //Seventh Page
  pdf.addPage();
  pdf.addImage(image7, 0, 0, 565, 792);

  //Eight Page
  pdf.addPage();
  pdf.addImage(image8, 0, 0, 565, 792);

  //Ninth Page
  pdf.addPage();
  pdf.addImage(image9, 0, 0, 565, 792);

  //Tenth Page
  // pdf.addPage();
  // pdf.addImage(image10, 0, 0, 565, 792);

  //Eleventh Page
  pdf.addPage();
  pdf.addImage(image11, 0, 0, 565, 792);
  //console.log(modemt.value);
  if (parseInt(modemt) == 1) {
    pdf.text(dayContrato + "/" + monthContrato + "/" + yearContrato, 161, 252);
    pdf.text(name, 123, 323.5);
    pdf.text(municipio, 240, 299);
    pdf.text(dayContrato, 349.5, 299);
    pdf.text(monthContrato, 375, 299);
    pdf.text(yearContrato.substring(2, 4), 412, 299);
    pdf.text("Av. José María Morelos 147, Loma Linda", 126, 335);
    pdf.text("38980 Uriangato, Gto.", 100, 346);
    if (signatureImage2) {
      pdf.addImage(signatureImage2, "PNG", 292, 327, 165, 28);
    }
  } else {
    //console.log("no entro");
  }

  var blob = pdf.output("blob");
  var formData = new FormData();
  formData.append("pdf", blob);
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
  formData.append("fechac", fechacInput ? fechacInput.value : "");
  formData.append("telefono", telefono);
  formData.append("ttipo", ttipo);
  formData.append("tarifa", tarifa);
  formData.append("total", total);
  formData.append("reconexion", reconexion);
  formData.append("mdesco", mdesco);
  formData.append("plazo", plazo);
  formData.append("modemt", modemt);
  formData.append("marca", marca);
  formData.append("modelo", modelo);
  formData.append("serie", serie);
  formData.append("nequipos", nequipos);
  formData.append("tpago", tpago);
  formData.append("cequipos", cequipos);
  formData.append("domicilioi", domicilioi);
  formData.append("fechai", fechai);
  formData.append("horai", horai);
  formData.append("costoi", costoi);
  formData.append("acargo", acargo);
  formData.append("mpago", mpago);
  formData.append("cmes", cmes);
  formData.append("banco", banco);
  formData.append("ntarjeta", ntarjeta);
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
  formData.append("ccontrato", ccontrato);
  formData.append("cdminimos", cdminimos);
  formData.append("yearContrato", yearContrato);
  formData.append("mes", mes);
  formData.append("dayContrato", dayContrato);
  formData.append("monthContrato", monthContrato);
  formData.append("scontrato", scontrato);
  formData.append("ciudad", ciudad);
  formData.append("ncontrato", ncontrato);
  formData.append("firma1", signatureImage);
  formData.append("firma2", signatureImage2);
  formData.append("ex", scontrato);
  try {
  const response = await fetch("./php/upload.php", {
    method: "POST",
    body: formData,
  });

  const raw = await response.text();
  let res;

  try {
    res = JSON.parse(raw);
  } catch (e) {
    console.error("Respuesta inválida del servidor:", raw);
    throw new Error("Respuesta inválida del servidor.");
  }

  if (res.ok) {
  window.open(pdf.output("bloburl"), "_blank");

  const fileInput = document.getElementById("evidencia");
  if (fileInput && fileInput.files.length > 0) {
    await evidencia(false);
  }

  Swal.fire({
    ...swalDark,
    title: "¡Creado!",
    text: "El contrato se ha creado correctamente.",
    icon: "success",
  }).then(async () => {
    resetFormularioCompleto();
    await cargarSiguienteContrato();
  });
} else {
    Swal.fire({
      ...swalDark,
      title: "Error",
      text: res.message || "Error desconocido.",
      icon: "error",
    });
  }
} catch (error) {
  console.error("Error al generar contrato:", error);
  Swal.fire({
    ...swalDark,
    title: "Error",
    text: error.message || "No se pudo generar el contrato.",
    icon: "error",
  });
}
}
