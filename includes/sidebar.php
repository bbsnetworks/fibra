<?php
$base = '/fibra';
$current = $_SERVER['REQUEST_URI'] ?? '';

function isActive($needle, $current)
{
  return strpos($current, $needle) !== false;
}
?>

<button id="btn-sidebar" type="button"
  class="fixed left-4 top-4 z-[999] inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-[#0b1a2d]/90 text-white shadow-xl backdrop-blur-md transition hover:bg-[#10233b]"
  aria-label="Abrir menú">
  <i class="bi bi-list text-xl"></i>
</button>

<!-- Fondo -->
<div id="sidebar-backdrop" class="fixed inset-0 z-[990] hidden bg-black/60 backdrop-blur-[2px]">
</div>

<!-- Sidebar -->
<aside id="sidebar"
  class="fixed left-0 top-0 z-[995] flex h-full w-72 -translate-x-full flex-col border-r border-white/10 bg-[#071322]/98 shadow-2xl backdrop-blur-xl transition-transform duration-300">

  <!-- Header -->
  <div class="border-b border-white/10 px-5 py-5">
    <div class="flex items-center justify-between gap-3">
      <div class="flex-1">
        <img src="<?= $base ?>/img/logo.png" class="w-full max-w-[180px]" alt="Logo">
      </div>
    </div>
  </div>

  <!-- Navegación -->
  <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-5">
    <!-- <a href="<?= $base ?>/index.php"
      class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition <?= isActive('/contratos/index.php', $current) ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-400/20' : 'text-white/85 hover:bg-white/5 hover:text-cyan-300' ?>">
      <i class="bi bi-file-earmark-text w-5 text-center"></i>
      <span>Crear contrato Antena</span>
    </a> -->

    <a href="<?= $base ?>/fibra/index.php"
      class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition <?= isActive('/contratos/fibra/', $current) ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-400/20' : 'text-white/85 hover:bg-white/5 hover:text-cyan-300' ?>">
      <i class="bi bi-modem-fill"></i>
      <span>Crear contrato Fibra</span>
    </a>
    <a href="<?= $base ?>/lista/index.php"
      class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition <?= isActive('/contratos/lista/', $current) ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-400/20' : 'text-white/85 hover:bg-white/5 hover:text-cyan-300' ?>">
      <i class="bi bi-list-ul w-5 text-center"></i>
      <span>Lista de contratos</span>
    </a>

    <a href="<?= $base ?>/resagados/index.php"
      class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition <?= isActive('/contratos/resagados/', $current) ? 'bg-cyan-400/15 text-cyan-300 border border-cyan-400/20' : 'text-white/85 hover:bg-white/5 hover:text-cyan-300' ?>">
      <i class="bi bi-clock-history w-5 text-center"></i>
      <span>Rezagados</span>
    </a>
  </nav>

  <!-- Footer -->
  <div class="border-t border-white/10 p-4">
    <a href="<?= $base ?>/../menu/index.php"
      class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-red-300 transition hover:bg-red-500/10 hover:text-red-200">
      <i class="bi bi-box-arrow-left w-5 text-center"></i>
      <span>Salir al menú</span>
    </a>
  </div>
</aside>