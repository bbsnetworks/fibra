/* ===================== resagados.js ===================== */

const tablaResagadosEl = document.getElementById("tabla-resagados");
const filtroResagadosEl = document.getElementById("filtro-resagados");
const busquedaResagadosEl = document.getElementById("busqueda-resagados");
const btnBuscarResagadosEl = document.getElementById("btnBuscarResagados");
const ordenResagadosEl = document.getElementById("orden-resagados");
const paginacionResagadosEl = document.getElementById("paginacion-resagados");
const paginacionInfoResagadosEl = document.getElementById("paginacion-resagados-info");
const resumenResagadosEl = document.getElementById("resagados-info");

let resagadosRows = [];
let paginaActualResagados = 1;
const filasPorPaginaResagados = 10;

function mostrarEstadoInicialResagados(mensaje, icono = "bi-search") {
  if (!tablaResagadosEl) return;

  tablaResagadosEl.innerHTML = `
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

function getTextoCelda(tr, index) {
  const td = tr.children[index];
  return td ? td.textContent.trim() : "";
}

function normalizarFecha(fechaTexto) {
  const valor = (fechaTexto || "").trim();
  const d = new Date(valor.replace(" ", "T"));
  return isNaN(d) ? 0 : d.getTime();
}

function mapearFilasDesdeTabla(tabla) {
  const tbodyRows = Array.from(tabla.querySelectorAll("tbody tr"));

  return tbodyRows.map((tr, idx) => {
    const celdas = tr.querySelectorAll("td");

    return {
      rawHtmlCreado: celdas[0]?.innerHTML || "",
      id: getTextoCelda(tr, 1),
      nombre: getTextoCelda(tr, 2),
      direccion: getTextoCelda(tr, 3),
      fecha: getTextoCelda(tr, 4),
      status: getTextoCelda(tr, 5),
      extra: getTextoCelda(tr, 6),
      comprobanteHtml: celdas[7]?.innerHTML || '<span class="text-slate-500">—</span>',
      accionesHtml: Array.from(celdas)
        .slice(8)
        .map(td => td.innerHTML),
      searchable: tr.textContent.toLowerCase(),
      fechaValor: normalizarFecha(getTextoCelda(tr, 4)),
      indexOriginal: idx,
    };
  });
}

function filtrarYOrdenarResagados() {
  const q = (busquedaResagadosEl?.value || "").trim().toLowerCase();
  const orden = ordenResagadosEl?.value || "desc";

  let rows = [...resagadosRows];

  if (q) {
    rows = rows.filter(row => row.searchable.includes(q));
  }

  rows.sort((a, b) => {
    if (orden === "asc") {
      return a.fechaValor - b.fechaValor;
    }
    return b.fechaValor - a.fechaValor;
  });

  return rows;
}

function renderTablaResagados() {
  const rows = filtrarYOrdenarResagados();
  const total = rows.length;
  const totalPaginas = Math.max(1, Math.ceil(total / filasPorPaginaResagados));

  if (paginaActualResagados > totalPaginas) {
    paginaActualResagados = totalPaginas;
  }

  const inicio = (paginaActualResagados - 1) * filasPorPaginaResagados;
  const fin = inicio + filasPorPaginaResagados;
  const paginaRows = rows.slice(inicio, fin);

  if (!total) {
    tablaResagadosEl.innerHTML = `
      <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
        <div>
          <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
            <i class="bi bi-inbox text-xl"></i>
          </div>
          <h3 class="text-base font-semibold text-white">Sin resultados</h3>
          <p class="mt-2 text-sm text-white/55">No se encontraron clientes rezagados con los filtros actuales.</p>
        </div>
      </div>
    `;
    paginacionResagadosEl.innerHTML = "";
    paginacionInfoResagadosEl.textContent = "Mostrando 0 resultados";
    resumenResagadosEl.textContent = "Sin coincidencias para la búsqueda actual.";
    return;
  }

  let extraHeader = "Detalle";
  const filtro = filtroResagadosEl?.value || "pendientes";
  if (filtro === "cancelados") extraHeader = "Fecha cancelación";
  else if (filtro === "legado" || filtro === "todos") extraHeader = "Tipo / detalle";

  const accionesCount = Math.max(...paginaRows.map(r => r.accionesHtml.length), 0);

  tablaResagadosEl.innerHTML = `
    <div class="w-full overflow-x-auto">
      <table class="min-w-full overflow-hidden rounded-2xl text-sm text-white">
        <thead>
          <tr class="border-b border-white/10 bg-[#0f172a] text-xs uppercase tracking-wide text-slate-300">
            <th class="px-4 py-4 text-center font-semibold">Creado</th>
            <th class="px-4 py-4 text-center font-semibold">ID</th>
            <th class="px-4 py-4 text-left font-semibold">Nombre</th>
            <th class="px-4 py-4 text-left font-semibold">Dirección</th>
            <th class="px-4 py-4 text-center font-semibold">Fecha</th>
            <th class="px-4 py-4 text-center font-semibold">Status</th>
            <th class="px-4 py-4 text-center font-semibold">${extraHeader}</th>
            <th class="px-4 py-4 text-center font-semibold">Comprobante</th>
            ${accionesCount > 0 ? '<th class="px-4 py-4 text-center font-semibold">Acciones</th>' : ""}
          </tr>
        </thead>
        <tbody>
          ${paginaRows.map(row => `
            <tr class="border-b border-white/5 bg-white/[0.02] transition hover:bg-cyan-400/5">
              <td class="px-4 py-4 text-center">${row.rawHtmlCreado}</td>
              <td class="px-4 py-4 text-center">
                <span class="inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-300">${row.id}</span>
              </td>
              <td class="px-4 py-4 text-left font-medium text-white">${row.nombre}</td>
              <td class="px-4 py-4 text-left text-slate-300">${row.direccion}</td>
              <td class="px-4 py-4 text-center text-slate-200 whitespace-nowrap">${row.fecha}</td>
              <td class="px-4 py-4 text-center">${renderBadgeEstadoResagado(row.status)}</td>
              <td class="px-4 py-4 text-center text-slate-100">${row.extra || "—"}</td>
              <td class="px-4 py-4 text-center">${row.comprobanteHtml}</td>
              ${accionesCount > 0 ? `<td class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                  ${row.accionesHtml.map(html => html).join("")}
                </div>
              </td>` : ""}
            </tr>
          `).join("")}
        </tbody>
      </table>
    </div>
  `;

  const desde = inicio + 1;
  const hasta = Math.min(fin, total);
  paginacionInfoResagadosEl.textContent = `Mostrando ${desde} a ${hasta} de ${total} resultados`;
  resumenResagadosEl.textContent = `Página ${paginaActualResagados} de ${totalPaginas}`;

  renderPaginacionResagados(totalPaginas);
}

function renderBadgeEstadoResagado(status) {
  const s = (status || "").trim().toLowerCase();

  if (s === "activo") {
    return `<span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
      <i class="bi bi-check-circle-fill"></i> Activo
    </span>`;
  }

  if (s === "cancelado") {
    return `<span class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-400">
      <i class="bi bi-x-circle-fill"></i> Cancelado
    </span>`;
  }

  if (s === "pausado") {
    return `<span class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300">
      <i class="bi bi-pause-circle-fill"></i> Pausado
    </span>`;
  }

  if (s === "pendiente" || s === "sin contrato") {
    return `<span class="inline-flex items-center gap-2 rounded-full bg-cyan-500/15 px-3 py-1 text-xs font-semibold text-cyan-300">
      <i class="bi bi-hourglass-split"></i> Pendiente
    </span>`;
  }

  return `<span class="text-slate-300">${status || "—"}</span>`;
}

function renderPaginacionResagados(totalPaginas) {
  if (!paginacionResagadosEl) return;

  if (totalPaginas <= 1) {
    paginacionResagadosEl.innerHTML = "";
    return;
  }

  let html = `
    <button type="button"
      class="rounded-xl border border-white/10 bg-[#071322] px-3 py-2 text-sm text-white/80 transition hover:bg-white/5 disabled:opacity-40"
      ${paginaActualResagados === 1 ? "disabled" : ""}
      data-page="prev">
      Anterior
    </button>
  `;

  for (let i = 1; i <= totalPaginas; i++) {
    const activo = i === paginaActualResagados;
    html += `
      <button type="button"
        class="rounded-xl px-3 py-2 text-sm font-medium transition ${
          activo
            ? "bg-cyan-500 text-white shadow-lg shadow-cyan-500/20"
            : "border border-white/10 bg-[#071322] text-white/80 hover:bg-white/5"
        }"
        data-page="${i}">
        ${i}
      </button>
    `;
  }

  html += `
    <button type="button"
      class="rounded-xl border border-white/10 bg-[#071322] px-3 py-2 text-sm text-white/80 transition hover:bg-white/5 disabled:opacity-40"
      ${paginaActualResagados === totalPaginas ? "disabled" : ""}
      data-page="next">
      Siguiente
    </button>
  `;

  paginacionResagadosEl.innerHTML = html;
}

async function cargarTablaResagados() {
  const filtro = filtroResagadosEl?.value || "pendientes";

  mostrarEstadoInicialResagados("Cargando rezagados...", "bi-hourglass-split");

  try {
    const response = await fetch("../php/cargarTablaResagados.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ filtro }),
    });

    const html = await response.text();

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const temp = document.createElement("div");
    temp.innerHTML = html;

    const tabla = temp.querySelector("table");

    if (!tabla) {
      resagadosRows = [];
      tablaResagadosEl.innerHTML = html;
      paginacionResagadosEl.innerHTML = "";
      paginacionInfoResagadosEl.textContent = "Mostrando 0 resultados";
      resumenResagadosEl.textContent = "Sin datos disponibles.";
      return;
    }

    resagadosRows = mapearFilasDesdeTabla(tabla);
    paginaActualResagados = 1;
    renderTablaResagados();
  } catch (error) {
    console.error("Error al cargar rezagados:", error);
    tablaResagadosEl.innerHTML = `
      <div class="rounded-2xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
        Error al cargar los clientes rezagados.
      </div>
    `;
    paginacionResagadosEl.innerHTML = "";
    paginacionInfoResagadosEl.textContent = "Error de carga";
  }
}

function cancelarResagado(idcliente) {
  if (window.Cancelaciones?.confirmarCancelacion) {
    window.Cancelaciones.confirmarCancelacion(idcliente);
  } else if (typeof confirmarCancelacion === "function") {
    confirmarCancelacion(idcliente);
  } else {
    Swal.fire({
      ...swalDark,
      title: "Error",
      text: "No se encontró cancelaciones.js",
      icon: "error",
    });
  }
}

/* Eventos */
btnBuscarResagadosEl?.addEventListener("click", () => {
  paginaActualResagados = 1;
  renderTablaResagados();
});

busquedaResagadosEl?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    paginaActualResagados = 1;
    renderTablaResagados();
  }
});

filtroResagadosEl?.addEventListener("change", () => {
  cargarTablaResagados();
});

ordenResagadosEl?.addEventListener("change", () => {
  paginaActualResagados = 1;
  renderTablaResagados();
});

paginacionResagadosEl?.addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-page]");
  if (!btn) return;

  const action = btn.dataset.page;
  const total = Math.max(1, Math.ceil(filtrarYOrdenarResagados().length / filasPorPaginaResagados));

  if (action === "prev" && paginaActualResagados > 1) {
    paginaActualResagados--;
  } else if (action === "next" && paginaActualResagados < total) {
    paginaActualResagados++;
  } else if (!isNaN(Number(action))) {
    paginaActualResagados = Number(action);
  }

  renderTablaResagados();
});

/* Carga inicial */
document.addEventListener("DOMContentLoaded", () => {
  cargarTablaResagados();
});

/* =================== fin resagados.js =================== */