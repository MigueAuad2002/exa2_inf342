<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio — Plataforma Universitaria INF342</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col font-sans">

  <!-- Barra superior -->
  <header class="bg-purple-100 shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="bg-purple-100 max-w-7xl mx-auto flex justify-between items-center px-6 py-3">
      <h1 class="text-lg md:text-xl font-semibold text-purple-700 tracking-wide">
        🎓 Plataforma Universitaria — INF342
      </h1>

      <div class="flex items-center gap-4">
        <div class="hidden sm:block text-right">
          <p class="font-semibold text-gray-700">{{ $user['nomb_comp'] }}</p>
          <p class="text-xs text-gray-500">{{ ucfirst($user['nombre']) }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold shadow-sm">
          {{ strtoupper(substr($user['nomb_comp'],0,1)) }}
        </div>
        <form action="/logout" method="POST">
          @csrf
          <button type="submit"
                  class="ml-2 bg-gray-100 hover:bg-purple-200 text-purple-700 px-3 py-1.5 rounded-md text-sm font-medium transition">
            Cerrar sesión
          </button>
        </form>
      </div>
    </div>
  </header>

  <!-- Contenido principal -->
  <main class="flex-1 max-w-7xl mx-auto w-full py-10 px-6">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-8">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Panel principal</h2>
        <p class="text-gray-500 text-sm">Gestión académica y control docente</p>
      </div>
      <div id="clock" class="text-sm text-gray-600 font-medium mt-3 md:mt-0"></div>
    </div>

    <!-- Tarjetas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="dashboard-cards">

      <!-- Datos personales -->
      <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg border border-gray-100 transition">
        <h3 class="text-lg font-semibold text-purple-700 mb-3">Datos personales</h3>
        <ul class="text-sm text-gray-600 space-y-1">
          <li><span class="font-semibold">CI:</span> {{ $user['ci'] }}</li>
          <li><span class="font-semibold">Correo:</span> {{ $user['correo'] ?? '—' }}</li>
          <li><span class="font-semibold">Teléfono:</span> {{ $user['tel'] ?? '—' }}</li>
          <li><span class="font-semibold">Rol:</span> {{ ucfirst($user['nombre']) }}</li>
        </ul>
      </div>

      @php $rol = strtolower($user['nombre']); @endphp

      <!-- ADMIN -->
      @if ($rol === 'admin')
        <div class="bg-gradient-to-br from-purple-100 to-gray-50 p-6 rounded-xl border border-purple-200 shadow-sm hover:shadow-md transition">
          <h3 class="text-lg font-semibold text-purple-700 mb-3">Administración general</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>👥 Gestión de docentes y usuarios</li>
            <li>📚 Asignación de materias y grupos</li>
            <li>🏫 Administración de aulas</li>
            <li>📈 Reportes globales</li>
          </ul>
        </div>
      @elseif ($rol === 'autoridad')
        <div class="bg-gradient-to-br from-violet-100 to-gray-50 p-6 rounded-xl border border-violet-200 shadow-sm hover:shadow-md transition">
          <h3 class="text-lg font-semibold text-violet-700 mb-3">Panel de autoridad</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>📊 Estadísticas de asistencia</li>
            <li>📄 Reportes por docente y grupo</li>
            <li>🏫 Monitoreo de aulas y horarios</li>
          </ul>
        </div>
      @elseif ($rol === 'docente')
        <div class="bg-gradient-to-br from-indigo-100 to-gray-50 p-6 rounded-xl border border-indigo-200 shadow-sm hover:shadow-md transition">
          <h3 class="text-lg font-semibold text-indigo-700 mb-3">Panel docente</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>📘 Ver materias asignadas</li>
            <li>🕒 Registrar asistencia</li>
            <li>📅 Consultar horarios</li>
          </ul>
        </div>
      @elseif ($rol === 'administrativo')
        <div class="bg-gradient-to-br from-gray-100 to-purple-50 p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
          <h3 class="text-lg font-semibold text-gray-700 mb-3">Panel administrativo</h3>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>🧾 Validar registros</li>
            <li>👥 Control de personal</li>
            <li>🏫 Gestión de aulas y equipos</li>
          </ul>
        </div>
      @endif

      <!-- Avisos -->
      <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg border border-gray-100 transition">
        <h3 class="text-lg font-semibold text-purple-700 mb-3">Avisos y novedades</h3>
        <ul id="news-list" class="text-sm text-gray-600 space-y-2">
          <li>📢 Nueva gestión académica: <span class="font-medium text-purple-700">2025-I</span></li>
          <li>🧾 Se habilitó el registro de asistencia docente</li>
          <li>📊 Actualización en reportes de aula</li>
        </ul>
      </div>

    </div>
  </main>

  <!-- Footer -->
  <footer class="text-center py-4 text-xs text-gray-500 border-t border-gray-200 bg-white mt-10">
    © {{ date('Y') }} Facultad de Ingeniería — UAGRM | INF342
  </footer>

  <script src="{{ secure_asset('static/scripts/index.js') }}"></script>
</body>
</html>
