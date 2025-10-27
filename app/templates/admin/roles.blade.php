{{-- resources/views/admin/roles.blade.php --}}
{{-- Vista independiente para gestionar ROLES.
     Colocar en resources/views/admin/roles.blade.php
     Usa /admin/roles y /admin/permissions (endpoints en routes/web.php)
--}}

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Gestión de Roles</title>

  {{-- Token CSRF para fetch --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Estilos mínimos (reemplaza por tu CSS/Tailwind si quieres) --}}
  <style>
    :root{ --bg:#eaf2f6; --card:#f6f9fb; --muted:#556174; --accent:#0ea5a4; --danger:#ef4444; --border:#d9e2e8 }
    html,body{height:100%}
    body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; margin:0; background:var(--bg); color:#0f172a; }
    .container{max-width:1100px;margin:24px auto;padding:16px}
    .hidden { display:none !important; }
    .btn { padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:600; border:1px solid transparent }
    .btn-primary { background:var(--accent); color:#fff; border-color:var(--accent); }
    .btn-outline { background:transparent; color:var(--accent); border-color:var(--accent); }
    .btn-danger { background:var(--danger); color:#fff; border-color:var(--danger); }
    .card { background:var(--card); padding:18px; border-radius:10px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th,td{ padding:12px 10px; border-bottom:1px solid var(--border); text-align:left; font-size:14px }
    td:last-child { width:180px; text-align:right; }
    thead th{ background:#fbfdff; font-weight:700 }
    thead th:last-child { text-align:right; }
    .modal{position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.35)}
    .modal:not(.hidden){display:flex}
  .card-small { width:720px; max-width:95%; background:var(--card); padding:16px; border-radius:8px; box-sizing:border-box; }
  /* evitar que inputs o labels se desborden del modal */
  .card-small input[type="text"], .card-small textarea { box-sizing:border-box; max-width:100%; width:100%; }
  .checkbox-list { box-sizing:border-box; width:100%; }
  .checkbox-list div { display:flex; gap:8px; align-items:flex-start; }
  .checkbox-list label { display:block; word-break:break-word; max-width:calc(100% - 28px); }
    #loader { position:fixed; right:18px; bottom:18px; background:rgba(255,255,255,0.95); padding:8px 12px; border-radius:8px; border:1px solid var(--border); box-shadow:0 8px 22px rgba(9,30,39,0.08); backdrop-filter: blur(4px); }
    .checkbox-list { max-height:200px; overflow:auto; border:1px solid #eee; padding:8px; border-radius:6px; }
  </style>
</head>
<body>
  {{-- Opcional: control UI simple. Recomendado también proteger ruta en backend. --}}
  @if(!Session::has('user_code'))
    <div class="card">
      <h2>Acceso denegado</h2>
      <p>Debes iniciar sesión para ver esta página.</p>
    </div>
  @else
    <div class="card">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:10px">
          <a href="/" title="Volver al inicio"><button class="btn btn-outline">← Volver</button></a>
          <h1 style="margin:0;">Gestión de Roles</h1>
        </div>
        <div>
          <button id="btnCreateRole" class="btn btn-primary">+ Crear Rol</button>
        </div>
      </div>

      <div id="loader" class="hidden">Cargando...</div>

      <table id="rolesTable" aria-live="polite">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Permisos</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody><!-- llenado por JS --></tbody>
      </table>
    </div>

    {{-- Modal crear/editar rol --}}
    <div id="roleModal" class="modal hidden" aria-hidden="true">
      <div class="card-small" role="dialog" aria-modal="true">
        <h2 id="roleModalTitle">Crear Rol</h2>

        <div style="margin-top:8px;">
          <label><strong>Nombre del rol</strong></label><br>
          <input id="roleName" type="text" style="width:100%; padding:8px; margin-top:6px;" placeholder="Ej: ADMIN">
          <div class="small" style="margin-top:6px; color:#6b7280;">Usa nombres únicos (p.ej. ADMIN)</div>
        </div>

        <div style="margin-top:12px;">
          <label><strong>Permisos</strong></label>
          <div id="rolePermissionsList" class="checkbox-list" aria-label="Lista de permisos"></div>
          <div class="small" style="margin-top:6px; color:#6b7280;">Marca los permisos que tendrá este rol.</div>
        </div>

        <div style="margin-top:12px; display:flex; justify-content:flex-end; gap:8px;">
          <button id="cancelRole" class="btn">Cancelar</button>
          <button id="saveRole" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </div>

    {{-- Modal mensajes --}}
    <div id="modal-result" class="modal hidden" aria-hidden="true">
      <div class="card-small">
        <h3 id="modal-title">Título</h3>
        <p id="modal-message">Mensaje</p>
        <div style="text-align:right;">
          <button id="modal-close" class="btn">Cerrar</button>
        </div>
      </div>
    </div>
  @endif

  {{-- CSRF meta (ya la colocamos arriba como meta) --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Incluir JS externo (coloca roles.js en public/js/) --}}
  <script src="{{ asset('static/scripts/admin/roles.js') }}"></script>
</body>
</html>
