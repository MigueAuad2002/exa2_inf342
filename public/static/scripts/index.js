document.addEventListener('DOMContentLoaded', () => {

  console.log('DOM CARGADO EXITOSAMENTE.');

  const clock = document.getElementById('clock');
  const updateClock = () => {
    const now = new Date();
    clock.textContent = now.toLocaleString('es-BO', {
      weekday: 'long',
      hour: '2-digit',
      minute: '2-digit'
    });
  };
  updateClock();
  setInterval(updateClock, 60000);

  //Redirección al módulo de importación
  const importBtn = document.getElementById('btn-import-users');
  if (importBtn) {
    importBtn.addEventListener('click', () => {
      window.location.href = '/admin/import-users';
    });
  }

  //Mostrar aside del usuario
  const avatar = document.getElementById('user-avatar');
  const aside = document.getElementById('user-aside');
  let hoverTimeout;

  if (avatar && aside) {
    avatar.addEventListener('mouseenter', () => {
      clearTimeout(hoverTimeout);
      aside.classList.remove('hidden');
      setTimeout(() => {
        aside.classList.add('opacity-100', 'scale-100');
        aside.classList.remove('opacity-0', 'scale-95');
      }, 10);
    });

    avatar.addEventListener('mouseleave', () => {
      hoverTimeout = setTimeout(() => {
        aside.classList.add('opacity-0', 'scale-95');
        aside.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => aside.classList.add('hidden'), 150);
      }, 200);
    });

    aside.addEventListener('mouseenter', () => clearTimeout(hoverTimeout));
    aside.addEventListener('mouseleave', () => {
      hoverTimeout = setTimeout(() => {
        aside.classList.add('opacity-0', 'scale-95');
        aside.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => aside.classList.add('hidden'), 150);
      }, 200);
    });
  }
});
