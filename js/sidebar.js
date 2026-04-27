document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const btnsOpen = document.querySelectorAll("#btn-sidebar");

  if (!sidebar || !backdrop || !btnsOpen.length) return;

  function openSidebar() {
    sidebar.classList.remove("-translate-x-full");
    backdrop.classList.remove("hidden");

    btnsOpen.forEach((btn) => {
      btn.style.display = "none";
    });

    document.body.classList.add("overflow-hidden");
  }

  function closeSidebar() {
    sidebar.classList.add("-translate-x-full");
    backdrop.classList.add("hidden");

    btnsOpen.forEach((btn) => {
      btn.style.display = "inline-flex";
    });

    document.body.classList.remove("overflow-hidden");
  }

  btnsOpen.forEach((btn) => {
    btn.addEventListener("click", openSidebar);
  });

  backdrop.addEventListener("click", closeSidebar);

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSidebar();
  });

  window.openSidebar = openSidebar;
  window.closeSidebar = closeSidebar;
});