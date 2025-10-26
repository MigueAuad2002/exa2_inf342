document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const inpCodigo = document.getElementById('codigo');
    const inpPass = document.getElementById('password');
    const alertError = document.getElementById('alert-error');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // evitar que el form recargue la página

        console.log(token)
        const codigo = inpCodigo.value.trim();
        const password = inpPass.value;

        // Validar campos vacíos
        if (!codigo || !password) {
            alertError.textContent = "Debe completar ambos campos.";
            alertError.classList.remove('hidden');
            return;
        }

        
        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ codigo, password })
        });

        const data = await response.json();

        if (data.success) 
        {
            // Éxito → redirigir o mostrar mensaje
            alertError.classList.add('hidden');
            console.log('Inicio de sesión exitoso:', data.message);
            localStorage.setItem('user_code',codigo);
            window.location.href = '/'; 
        } 
        else 
        {
            // Error → mostrar mensaje
            alertError.textContent = data.message || 'Error al iniciar sesión.';
            alertError.classList.remove('hidden');
        }

    });
});
