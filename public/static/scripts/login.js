document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const loader = document.getElementById('loader');
  const modal = document.getElementById('modal-result');
  const modalTitle = document.getElementById('modal-title');
  const modalMessage = document.getElementById('modal-message');
  const modalClose = document.getElementById('modal-close');
  const token = document.querySelector('meta[name="csrf-token"]').content;

  console.log('DOM CARGADO EXITOSAMENTE.');

  const showModal = (title, message) => {
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modal.classList.remove('hidden');
  };

  const hideModal = () => {
    modal.classList.add('hidden');
  };

  modalClose.addEventListener('click', hideModal);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    loader.classList.remove('hidden');

    const codigo = document.getElementById('codigo').value.trim();
    const password = document.getElementById('password').value;

    
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token
      },
      body: JSON.stringify({ codigo, password })
    });

    const data = await response.json();
    loader.classList.add('hidden');

    if (data.success) {
      showModal('Inicio de sesión exitoso', 'Redirigiendo al panel principal.');
      setTimeout(() => window.location.href = '/', 1200);
    } else {
      showModal('Error de autenticación', data.message || 'Credenciales incorrectas.');
    }
  });
});
