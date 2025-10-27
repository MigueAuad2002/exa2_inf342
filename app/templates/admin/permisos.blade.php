{{-- resources/views/admin/permissions.blade.php --}}
{{-- Vista para gestionar PERMISOS.
     Colocar en resources/views/admin/permissions.blade.php
     Usa endpoints /admin/permissions en routes/web.php
--}}

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Gestión de Permisos</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

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
  /* suaves transiciones */
  .card, .card-small, button, input { transition: all 180ms ease-in-out; }
  .toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .title{font-size:20px;margin:0}
    .table-wrap{margin-top:14px;border-radius:8px;overflow:hidden;background:var(--card);border:1px solid var(--border)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px 10px;border-bottom:1px solid var(--border);text-align:left;font-size:14px}
    thead th{background:#fbfdff;font-weight:700}
    tr:last-child td{border-bottom:0}
    @media (max-width:720px){
      th,td{padding:10px 8px;font-size:13px}
      .title{font-size:18px}
    }
    .empty-row{padding:24px;text-align:center;color:var(--muted)}
    /* Loader + toast */
  #loader{position:fixed;right:18px;bottom:18px;background:rgba(255,255,255,0.95);padding:8px 12px;border-radius:8px;border:1px solid var(--border);box-shadow:0 8px 22px rgba(9,30,39,0.08);backdrop-filter: blur(4px)}
    #toast{position:fixed;left:50%;transform:translateX(-50%);bottom:24px;background:rgba(15,23,42,0.95);color:#fff;padding:8px 12px;border-radius:8px;display:none}
    /* Modal */
  .modal{position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(2,6,23,0.45)}
  .modal:not(.hidden){display:flex}
    .modal .card-small{width:520px;max-width:96%;background:var(--card);padding:18px;border-radius:10px}
    input[type=text]{
      box-sizing: border-box;
      width: 100%;
      padding: 8px 10px;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 14px;
      height: 36px;
    }
    input[type=text]:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 2px rgba(14,165,164,0.1);
    }
    /* Grupos de form más compactos */
    .form-group {
      margin-top: 12px;
    }
    .form-group label {
      display: block;
      margin-bottom: 4px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  @if(!Session::has('user_code'))
    <div class="card">
      <h2>Acceso denegado</h2>
      <p>Debes iniciar sesión.</p>
    </div>
  @else
    <div class="card">
      <div class="toolbar">
        <div style="display:flex; align-items:center; gap:10px">
          <a href="/" title="Volver al inicio"><button class="btn btn-outline">← Volver</button></a>
          <h1 class="title">Gestión de Permisos</h1>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <button id="btnCreatePerm" class="btn btn-primary">+ Crear Permiso</button>
        </div>
      </div>

  <div id="loader" class="hidden">Cargando...</div>

      <div class="table-wrap">
        <table id="permissionsTable" aria-live="polite">
          <thead>
            <tr><th style="width:48px">#</th><th>Nombre</th><th>Descripción</th><th style="width:160px">Acciones</th></tr>
          </thead>
          <tbody><!-- llenado por JS --></tbody>
        </table>
      </div>
      
      {{-- Confirmación personalizada para eliminar --}}
      <div id="confirmModal" class="modal hidden" aria-hidden="true" role="dialog" aria-labelledby="confirmTitle">
        <div class="card-small" style="max-width:520px;">
          <h3 id="confirmTitle">Confirmar eliminación</h3>
          <p id="confirmMessage" style="color:var(--muted); margin-top:8px;">¿Está seguro? Eliminar este permiso puede afectar roles que lo usan.</p>
          <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
            <button id="confirmNo" class="btn">Cancelar</button>
            <button id="confirmYes" class="btn btn-danger">Eliminar</button>
          </div>
        </div>
      </div>
      <div id="toast" role="status" aria-hidden="true"></div>
    </div>

    {{-- Modal permiso --}}
    <div id="permModal" class="modal hidden" aria-hidden="true" role="dialog" aria-labelledby="permModalTitle">
      <div class="card-small">
        <h2 id="permModalTitle">Crear Permiso</h2>
        <div class="form-group">
          <label for="permKey"><strong>Nombre</strong></label>
          <input id="permKey" type="text" placeholder="VER_DOCENTES">
        </div>
        <div class="form-group">
          <label for="permDescription"><strong>Descripción</strong></label>
          <input id="permDescription" type="text" placeholder="Permite ver la lista de docentes">
        </div>
        <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:8px;">
          <button id="cancelPerm" class="btn">Cancelar</button>
          <button id="savePerm" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </div>

    {{-- Modal mensajes --}}
    <div id="modal-result" class="modal hidden" aria-hidden="true">
      <div class="card-small">
        <h3 id="modal-title">Título</h3>
        <p id="modal-message">Mensaje</p>
        <div style="text-align:right;"><button id="modal-close" class="btn">Cerrar</button></div>
      </div>
    </div>
  @endif

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="{{ asset('static/scripts/admin/permisos.js') }}"></script>
</body>
</html>
