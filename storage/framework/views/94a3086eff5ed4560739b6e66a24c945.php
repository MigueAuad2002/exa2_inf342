<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plataforma Universitaria | INF342</title>
  <!-- Tailwind desde CDN (puedes cambiarlo luego a local) -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">
  <!-- Barra superior -->
  <header class="bg-blue-700 text-white shadow-md py-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center px-6">
      <h1 class="text-xl font-bold tracking-wide">🎓 Plataforma Universitaria</h1>
      <nav class="space-x-6">
        <a href="#" class="hover:text-blue-200 transition">Inicio</a>
        <a href="#" class="hover:text-blue-200 transition">Materias</a>
        <a href="#" class="hover:text-blue-200 transition">Docentes</a>
        <a href="#" class="hover:text-blue-200 transition">Contacto</a>
      </nav>
    </div>
  </header>

  <!-- Contenido principal -->
  <main class="flex-1 flex items-center justify-center px-4">
    <div class="bg-white p-10 rounded-2xl shadow-lg max-w-lg w-full text-center">
      <h2 class="text-2xl font-semibold mb-4 text-blue-700">Bienvenido a la Plataforma</h2>
      <p class="text-gray-600 mb-6">
        Sistema de gestión universitaria para estudiantes y docentes.
        Accede a tus materias, horarios, calificaciones y más.
      </p>
      <div class="flex justify-center gap-4">
        <a href="/login" 
           class="bg-blue-700 hover:bg-blue-800 text-white py-2 px-6 rounded-lg transition">
           Iniciar Sesión
        </a>
        <a href="/register" 
           class="border border-blue-700 hover:bg-blue-50 text-blue-700 py-2 px-6 rounded-lg transition">
           Registrarse
        </a>
      </div>
    </div>
  </main>

  <!-- Pie de página -->
  <footer class="bg-blue-800 text-blue-100 text-center py-3">
    <p class="text-sm">&copy; <?php echo e(date('Y')); ?> Facultad de Ingeniería - UAGRM | INF342</p>
  </footer>
  <script src="<?php echo e(asset('static/scripts/index.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\Users\migue\OneDrive\Escritorio\projects\inf342_2exa\app\templates/index.blade.php ENDPATH**/ ?>