<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inicio de Sesión — Sistema FICCT</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-100 via-blue-200 to-blue-400 min-h-screen flex items-center justify-center font-sans antialiased">

    <div class="w-full max-w-md bg-white/95 backdrop-blur-md shadow-2xl rounded-3xl p-10 border border-blue-100">
        
        <!-- Encabezado -->
        <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
            <img src="/static/images/ficct_logo.png" alt="FICCT Logo" class="w-20 h-20 rounded-full shadow-md ring-2 ring-blue-300/40">
        </div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Sistema de Gestión Facultativa</h1>
        <p class="text-sm text-gray-600 mt-1 leading-tight">Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones</p>
        </div>

        <!-- Mensaje de error -->
        <div id="alert-error" class="hidden bg-red-100 text-red-700 px-4 py-2 rounded-lg mb-5 text-sm border border-red-200 text-center">
        Credenciales incorrectas. Intente nuevamente.
        </div>

        <!-- Formulario -->
        <form id="loginForm" class="space-y-6">
        <div>
            <label for="codigo" class="block text-sm font-semibold text-gray-700 mb-1">Código de Usuario</label>
            <input type="text" id="codigo" name="codigo" required
                placeholder="Ej: 202112345"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 placeholder-gray-400 
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
            <input type="password" id="password" name="password" required
                placeholder="••••••••"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 placeholder-gray-400 
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150">
        </div>

        <button type="submit"
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md 
                        transition duration-200 ease-in-out focus:ring-4 focus:ring-blue-300">
            Iniciar Sesión
        </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-gray-500">
        <p>© {{ date('Y') }} Facultad de Ingeniería — UAGRM</p>
        <p class="mt-1">
            <a href="#" id="reset-password" class="text-blue-500 hover:underline font-medium">Restablecer Contraseña</a>
        </p>
        </div>

    </div>
    <script src="{{ secure_asset('static/scripts/login.js') }}"></script>

</body>
</html>
