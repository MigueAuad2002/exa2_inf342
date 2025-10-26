document.addEventListener('DOMContentLoaded', () => {
  const clock = document.getElementById('clock');
  const updateClock = () => {
    const now = new Date();
    clock.textContent = now.toLocaleString('es-BO', {
      weekday: 'long', hour: '2-digit', minute: '2-digit'
    });
  };
  updateClock();
  setInterval(updateClock, 60000);
});
