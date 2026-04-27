/* ================= cancelaciones.js ================= */
(function () {
  async function loadImageLocal(url, typeHint) {
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status} al cargar ${url}`);
    const ct = (res.headers.get("Content-Type") || "").toLowerCase();
    if (!ct.startsWith("image/")) throw new Error(`No es imagen (${ct}) -> ${url}`);

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

  const mimeToFormatLocal = (mime) => (mime && mime.includes("png") ? "PNG" : "JPEG");
  const pad2 = (n) => String(n).padStart(2, "0");

  function formateaFechaMXLocal(f) {
    const d = new Date((f || "").replace(" ", "T"));
    if (isNaN(d)) return String(f || "");
    return `${pad2(d.getDate())}/${pad2(d.getMonth() + 1)}/${d.getFullYear()} ${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
  }

  const RUTA_PLANTILLA = "../img/comprobante-cancelacion.jpg";
  const COLOR_LABEL = [60, 60, 60];
  const COLOR_VALUE = [10, 102, 194];
  const COLOR_VALUE_ALT = [237, 125, 49];

  function setRGB(pdf, rgb) {
    pdf.setTextColor(rgb[0], rgb[1], rgb[2]);
  }

  function drawInfoLine(pdf, x, y, label, value, maxWidth = 430, gap = 8, lineHeight = 16, margin = 8) {
    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(11);
    setRGB(pdf, COLOR_LABEL);

    const lbl = label.endsWith(":") ? label : `${label}:`;
    pdf.text(lbl, x, y);

    const labelWidth = pdf.getTextWidth(lbl) + gap;
    const colX = x + labelWidth;

    pdf.setFont("helvetica", "normal");
    setRGB(pdf, COLOR_VALUE);

    const logical = Array.isArray(value) ? value : [String(value ?? "—")];

    let yCursor = y;
    for (const ln of logical) {
      const wrapped = pdf.splitTextToSize(ln, maxWidth - labelWidth);
      for (let i = 0; i < wrapped.length; i++) {
        pdf.text(wrapped[i], colX, yCursor + i * lineHeight);
      }
      yCursor += wrapped.length * lineHeight;
    }

    return yCursor + margin;
  }

  function splitMulti(value = "") {
    return String(value)
      .split(/[,;|]\s*|\s{2,}/g)
      .map((s) => s.trim())
      .filter(Boolean);
  }

  function buildEquiposDevueltos(marca, modelo, nserie, nequipo) {
    const marcas = splitMulti(marca);
    const modelos = splitMulti(modelo);
    const nseries = splitMulti(nserie);

    const maxLen = Math.max(marcas.length, modelos.length, nseries.length, 1);
    const filas = [];

    for (let i = 0; i < maxLen; i++) {
      const mrc = (marcas[i] ?? marcas[0] ?? "").trim();
      const mdl = (modelos[i] ?? modelos[0] ?? "").trim();
      const ns = (nseries[i] ?? nseries[0] ?? "").trim();

      const partes = [];
      if (mrc) partes.push(mrc);
      if (mdl) partes.push(mdl);
      if (ns) partes.push(`NS: ${ns}`);

      const linea = partes.join(" — ");
      if (linea) filas.push(linea);
    }

    if (!filas.length) return ["—"];
    if (nequipo && Number(nequipo) > 1) filas.push(`Total reportado: ${nequipo}`);
    return filas;
  }

  function deriveFlagsFromContrato(d) {
    const m = String(d.modeme ?? "").trim();
    const esRentado = (m === "1" || m === 1);
    const cubrioInstalacion = Boolean(d.costoi) || esRentado;
    return { cubrioInstalacion, esRentado };
  }

  async function generarPDFCancelacion(data, opciones = {}) {
    const pdf = new jsPDF("p", "pt", "letter");

    try {
      const plantilla = await loadImageLocal(RUTA_PLANTILLA, "image/jpeg");
      pdf.addImage(plantilla.dataUrl, mimeToFormatLocal(plantilla.mime), 0, 0, 565, 792);
    } catch (e) {
      console.warn("Plantilla no cargó:", e.message);
    }

    const folio = data.folio_cancelacion
      || `CNL-${new Date().toISOString().slice(0, 10).replace(/-/g, "")}-${String(data.idcontrato).padStart(5, "0")}`;

    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(16);
    setRGB(pdf, COLOR_VALUE);
    pdf.text(folio, 125, 238);

    const flagsDetectados = deriveFlagsFromContrato(data);
    const flags = { ...flagsDetectados, ...opciones };

    let x = 60, y = 300, ancho = 460;
    y = drawInfoLine(pdf, x, y, "Fecha", formateaFechaMXLocal(data.fecha_cancelacion), ancho);
    y = drawInfoLine(pdf, x, y, "Cliente", data.nombre || "—", ancho);

    const ref = opciones.numeroCliente
      ? `Cliente #${opciones.numeroCliente}`
      : `Cliente #${data.idcontrato}`;

    y = drawInfoLine(pdf, x, y, "Referencia", ref, ancho);
    y = drawInfoLine(pdf, x, y, "RFC", data.rfc || "—", ancho);
    y = drawInfoLine(pdf, x, y, "Domicilio", data.direccion || "—", ancho);

    let equiposValue;
    if (flags.esRentado) {
      equiposValue = buildEquiposDevueltos(data.marca, data.modelo, data.nserie, data.nequipo);
    } else {
      equiposValue = "El cliente es propietario de los equipos; no aplica devolución.";
    }

    y = drawInfoLine(pdf, x, y, "Equipos devueltos", equiposValue, ancho);

    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(11);
    setRGB(pdf, COLOR_LABEL);
    pdf.text("Observaciones:", x, y);
    y += 18;

    pdf.setFont("helvetica", "normal");
    setRGB(pdf, COLOR_VALUE_ALT);

    const obs = [];
    obs.push(`Instalación: ${flags.cubrioInstalacion ? "pago cubierto" : "pendiente/no aplica"}.`);
    obs.push(` Equipos: ${flags.esRentado ? "rentados (deben devolverse si aplica)" : "propiedad del cliente (no aplica devolución)"}.`);

    const obsLines = pdf.splitTextToSize(obs.join(" "), ancho);
    for (let i = 0; i < obsLines.length; i++) {
      pdf.text(obsLines[i], x, y + i * 16);
    }
    y += obsLines.length * 16 + 16;

    pdf.setFont("helvetica", "normal");
    setRGB(pdf, COLOR_LABEL);

    const nota = "Este comprobante acredita la cancelación del servicio relacionado con la referencia indicada.";
    pdf.text(pdf.splitTextToSize(nota, ancho), x, y);

    const blobUrl = pdf.output("bloburl");
    const a = document.createElement("a");
    a.href = blobUrl;
    a.target = "_blank";
    a.rel = "noopener";
    document.body.appendChild(a);
    a.click();
    a.remove();
  }

  async function doCancelar(idcontrato, equiposDevueltos, force = 0) {
    const response = await fetch("../php/cancelar_contrato.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id: idcontrato,
        equipos: equiposDevueltos,
        force,
      }),
    });

    return await response.json();
  }

  async function confirmarCancelacion(idcontrato) {
    const result = await Swal.fire({
      ...swalDark,
      title: "Cancelar contrato",
      html: `
        <label for="equiposDevueltos" style="display:block; text-align:left; margin-bottom:6px;">
          Ingrese los códigos/números de serie de los equipos devueltos:
        </label>
        <textarea id="equiposDevueltos" class="swal2-textarea"
          placeholder="Ej: ONT Huawei SN12345, Router TP-Link SN67890" required></textarea>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: "Sí, cancelar",
      cancelButtonText: "No, mantener activo",
      preConfirm: () => {
        const equipos = document.getElementById("equiposDevueltos").value.trim();
        if (!equipos) {
          Swal.showValidationMessage("Debes ingresar los equipos devueltos");
          return false;
        }
        if (equipos.length < 5) {
          Swal.showValidationMessage("Agrega un poco más de detalle");
          return false;
        }
        return equipos;
      },
    });

    if (!result.isConfirmed) return;

    const equiposDevueltos = result.value;

    try {
      const res = await doCancelar(idcontrato, equiposDevueltos, 0);

      if (res?.conflict) {
        const confirmacion = await Swal.fire({
          ...swalDark,
          title: "Verificar identidad",
          html: `
            <div style="text-align:left">
              <p><b>⚠ Posible conflicto de cliente</b></p>
              <p>El <b>ID ${idcontrato}</b> existe en <b>clientes</b> y <b>contratos</b>, pero el nombre no coincide.</p>
              <hr>
              <p><b>clientes:</b> ${res?.clientes_nombre || "—"}</p>
              <p><b>contratos:</b> ${res?.contratos_nombre || "—"}</p>
              <hr>
              <p>¿Confirmas que es el <b>mismo cliente</b> y deseas continuar con la cancelación?</p>
            </div>
          `,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, es el mismo (continuar)",
          cancelButtonText: "No, cancelar",
        });

        if (!confirmacion.isConfirmed) return;

        const res2 = await doCancelar(idcontrato, equiposDevueltos, 1);

        if (!res2.ok) {
          Swal.fire({
            ...swalDark,
            title: "Error",
            text: res2.message || "No se pudo cancelar.",
            icon: "error",
          });
          return;
        }

        Swal.fire({
          ...swalDark,
          title: "Cancelado",
          text: res2.message,
          icon: "success",
        });

        if (typeof cargarTabla === "function") await cargarTabla();
        generarPDFCancelacion(res2.data, { numeroCliente: String(res2.data.idcontrato) });
        return;
      }

      if (!res.ok) {
        Swal.fire({
          ...swalDark,
          title: "Error",
          text: res.message || "No se pudo cancelar.",
          icon: "error",
        });
        return;
      }

      Swal.fire({
        ...swalDark,
        title: "Cancelado",
        text: res.message,
        icon: "success",
      });

      if (typeof cargarTabla === "function") await cargarTabla();
      generarPDFCancelacion(res.data, { numeroCliente: String(res.data.idcontrato) });
    } catch (error) {
      console.error(error);
      Swal.fire({
        ...swalDark,
        title: "Error",
        text: "No se pudo cancelar el contrato.",
        icon: "error",
      });
    }
  }

  async function confirmarReactivacion(idcontrato) {
    const result = await Swal.fire({
      ...swalDark,
      title: "Reactivar contrato",
      text: "El contrato pasará a estado ACTIVO.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, reactivar",
      cancelButtonText: "Cancelar",
    });

    if (!result.isConfirmed) return;

    try {
      const response = await fetch("../php/reactivar_contrato.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: idcontrato }),
      });

      const res = await response.json();

      if (res.ok) {
        Swal.fire({
          ...swalDark,
          title: "Reactivado",
          text: res.message,
          icon: "success",
        });

        if (typeof cargarTabla === "function") await cargarTabla();
      } else {
        Swal.fire({
          ...swalDark,
          title: "Error",
          text: res.message || "No se pudo reactivar.",
          icon: "error",
        });
      }
    } catch (error) {
      console.error(error);
      Swal.fire({
        ...swalDark,
        title: "Error",
        text: "No se pudo reactivar el contrato.",
        icon: "error",
      });
    }
  }

  async function descargarCancelacion(idcontrato) {
    try {
      const body = new URLSearchParams({ id: idcontrato });

      const response = await fetch("../php/imprimirCancelacion.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        },
        body,
      });

      const json = await response.json();

      if (!json || !json.ok) {
        Swal.fire({
          ...swalDark,
          title: "Error",
          text: json?.message || "No fue posible generar el comprobante",
          icon: "error",
        });
        return;
      }

      generarPDFCancelacion(json.data, { numeroCliente: String(json.data.idcontrato) });
    } catch (error) {
      console.error(error);
      Swal.fire({
        ...swalDark,
        title: "Error",
        text: "No fue posible generar el comprobante",
        icon: "error",
      });
    }
  }

  window.Cancelaciones = {
    confirmarCancelacion,
    confirmarReactivacion,
    descargarCancelacion,
    generarPDFCancelacion,
  };

  window.confirmarCancelacion = confirmarCancelacion;
  window.confirmarReactivacion = confirmarReactivacion;
  window.descargarCancelacion = descargarCancelacion;
})();