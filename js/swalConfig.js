// swalConfig.js

// Modal de éxito (verde)
window.swalSuccess = Swal.mixin({
  icon: 'success',
  background: '#1e293b', // gray-800
  color: '#f8fafc',       // zinc-50
  confirmButtonColor: '#22c55e', // verde tailwind
  cancelButtonColor: '#334155',  // gris oscuro
  customClass: {
    popup: 'rounded-xl shadow-lg',
    title: 'text-lg font-semibold',
    confirmButton: 'px-4 py-2',
  },
});

// Modal de error (rojo)
window.swalError = Swal.mixin({
  icon: 'error',
  background: '#1e293b',
  color: '#f8fafc',
  confirmButtonColor: '#ef4444', // rojo tailwind
  cancelButtonColor: '#334155',
  customClass: {
    popup: 'rounded-xl shadow-lg',
    title: 'text-lg font-semibold',
    confirmButton: 'px-4 py-2',
  },
});
window.swalInfo = Swal.mixin({
  icon: 'info',
  background: '#1e293b',
  color: '#f8fafc',
  confirmButtonColor: '#6366f1',
  customClass: {
    popup: 'rounded-xl shadow-md',
    title: 'text-white text-lg font-semibold text-center', // ← centrado
    htmlContainer: 'text-slate-200 text-sm text-center px-4', // ← centrado y padding
    confirmButton: 'bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded mx-2',
    cancelButton: 'bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded mx-2',
    actions: 'flex justify-center gap-4 mt-4', // ← separa botones
    closeButton: 'text-white hover:text-red-500'
  },
  buttonsStyling: false
});
window.swalcard = Swal.mixin({
  background: '#1e293b',
  color: '#f8fafc',
  confirmButtonColor: '#6366f1',
  customClass: {
    popup: 'rounded-xl shadow-md',
    title: 'text-white text-lg font-semibold text-center', // ← centrado
    htmlContainer: 'text-slate-200 text-sm text-center px-4', // ← centrado y padding
    confirmButton: 'bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded mx-2',
    cancelButton: 'bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded mx-2',
    actions: 'flex justify-center gap-4 mt-4', // ← separa botones
    closeButton: 'text-white hover:text-red-500'
  },
  buttonsStyling: false
});