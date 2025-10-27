// public/js/roles.js
// JavaScript para roles: carga, crear, editar, eliminar.
// Requisitos: endpoints en routes/web.php: GET/POST/PUT/DELETE /admin/roles y GET /admin/permissions

document.addEventListener('DOMContentLoaded', () => {
  try {
  // Referencias DOM
  const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  const loader = document.getElementById('loader');
  const rolesTableBody = document.querySelector('#rolesTable tbody');
  const btnCreateRole = document.getElementById('btnCreateRole');

  const roleModal = document.getElementById('roleModal');
  const roleModalTitle = document.getElementById('roleModalTitle');
  const roleName = document.getElementById('roleName');
  const rolePermissionsList = document.getElementById('rolePermissionsList');
  const saveRole = document.getElementById('saveRole');
  const cancelRole = document.getElementById('cancelRole');

  const modal = document.getElementById('modal-result');
  const modalTitle = document.getElementById('modal-title');
  const modalMessage = document.getElementById('modal-message');
  const modalClose = document.getElementById('modal-close');

  let allPermissions = []; // [{id,nombre,descripcion}]
  let editingRoleId = null; // null => crear, id => editar

  // Helpers UI
  // Función para bloquear/desbloquear todos los botones
  function toggleButtons(disabled) {
    document.querySelectorAll('#btnCreateRole, .btn-edit, .btn-delete, #saveRole, #cancelRole').forEach(btn => {
      btn.disabled = disabled;
    });
  }

  const showLoader = () => {
    if (loader) {
      loader.classList.remove('hidden');
      toggleButtons(true); // bloquear botones mientras carga
    }
  };
  const hideLoader = () => {
    if (loader) {
      loader.classList.add('hidden');
      toggleButtons(false); // desbloquear botones al terminar
    }
  };

  const showModal = (title, message, isConfirm = false) => {
    if (!modal) return alert(message);
    // hide role modal to avoid stacking
    try { if (roleModal) roleModal.classList.add('hidden'); } catch(e){}
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    try { modal.style.zIndex = '9999'; } catch(e){}
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden','false');

    // Restaurar el botón de cerrar por defecto si no es modal de confirmación
    if (!isConfirm) {
      const btnContainer = document.createElement('div');
      btnContainer.style.textAlign = 'right';
      btnContainer.style.marginTop = '12px';

      const closeBtn = document.createElement('button');
      closeBtn.id = 'modal-close';
      closeBtn.className = 'btn';
      closeBtn.textContent = 'Aceptar';
      closeBtn.onclick = hideModal;

      btnContainer.appendChild(closeBtn);

      // Encontrar el contenedor del modal
      const modalContent = modal.querySelector('.card-small');
      if (modalContent) {
        // Buscar si ya existe un div de botones
        let buttonsDiv = modalContent.querySelector('div:last-child');
        if (buttonsDiv && buttonsDiv.querySelector('button')) {
          // Si existe, reemplazarlo
          buttonsDiv.replaceWith(btnContainer);
        } else {
          // Si no existe, agregar el nuevo
          modalContent.appendChild(btnContainer);
        }
      }
    }
  };
  const hideModal = () => {
    try {
      if (modal) { modal.classList.add('hidden'); modal.setAttribute('aria-hidden','true'); }
      if (modalTitle) modalTitle.textContent = '';
      if (modalMessage) modalMessage.textContent = '';
    } catch(e){ console.error('hideModal error', e); }
  };

  document.addEventListener('click', function(e){ if (e.target && e.target.id === 'modal-close') hideModal(); });

  // cerrar modal al hacer click fuera del contenido
  if (modal) {
    modal.addEventListener('click', function(e){
      if (e.target === modal) hideModal();
    });
  }
  // cerrar con ESC
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') hideModal(); });

  // Escape simple
  function escapeHtml(text='') {
    return text.toString()
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  // Cargar permisos (para checkboxes)
  async function loadPermissions() {
    try {
      showLoader();
      const res = await fetch('/admin/permissions', { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
      if (!res.ok) throw new Error('No se pudo cargar permisos');
      const data = await res.json();
  allPermissions = data.permissions || [];
    } catch (err) {
      console.error('loadPermissions error', err);
      showModal('Error', 'No se pudieron cargar los permisos: ' + (err.message || err));
      allPermissions = [];
    } finally {
      hideLoader();
    }
  }

  // Render checkboxes
  function renderRolePermissionsCheckboxes(selected=[]) {
    rolePermissionsList.innerHTML = '';
  allPermissions.forEach(p => {
      const div = document.createElement('div');
      div.style.marginBottom = '6px';

      const chk = document.createElement('input');
      chk.type = 'checkbox';
  chk.id = 'perm_' + p.id;
      chk.value = p.id;
      if (selected.includes(p.id)) chk.checked = true;

      const lbl = document.createElement('label');
  lbl.htmlFor = chk.id;
      lbl.style.marginLeft = '6px';
  lbl.textContent = p.nombre + (p.descripcion ? ' · ' + p.descripcion : '');

      div.appendChild(chk);
      div.appendChild(lbl);
      rolePermissionsList.appendChild(div);
    });
  }

  // Cargar roles y renderizar tabla
  async function loadRoles() {
    try {
      showLoader();
      const res = await fetch('/admin/roles', { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
       if (!res.ok) {
        if (res.status === 403) { showModal('Acceso denegado', 'No tienes permisos para ver roles'); return; }
        throw new Error('Error al cargar roles');
      }
      const data = await res.json();
      const roles = data.roles || [];
      // Por defecto sin permisos - solo se muestran acciones si el backend explícitamente las permite
      const can = data.can || { view:false, create:false, edit:false, delete:false };

      // Si no tiene permiso de ver, mostrar mensaje y salir
      if (!can.view) {
        rolesTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">No tienes permiso para ver esta sección.</td></tr>';
        if (btnCreateRole) btnCreateRole.style.display = 'none';
        return;
      }
      rolesTableBody.innerHTML = '';

      // mostrar/ocultar botón crear según permiso
      if (btnCreateRole) btnCreateRole.style.display = can.create ? 'inline-block' : 'none';

      roles.forEach((r, i) => {
        const tr = document.createElement('tr');
        const permKeys = (r.permissions || []).map(p => p.nombre).join(', ');

        // botones según permisos del usuario
        const editBtn = can.edit ? `<button data-id="${r.id}" class="btn-edit btn">Editar</button>` : '';
        const deleteBtn = can.delete ? `<button data-id="${r.id}" class="btn-delete btn btn-danger">Eliminar</button>` : '';

        tr.innerHTML = `
          <td>${i+1}</td>
          <td>${escapeHtml(r.nombre)}</td>
          <td>${escapeHtml(permKeys)}</td>
          <td>
            ${editBtn}
            ${deleteBtn}
          </td>
        `;
        rolesTableBody.appendChild(tr);
      });

      // Asignar eventos solo a botones que existen
      document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', onEditClicked));
      document.querySelectorAll('.btn-delete').forEach(b => b.addEventListener('click', onDeleteClicked));
    } catch (err) {
      console.error('loadRoles error', err);
      showModal('Error', err.message || 'Error al cargar roles');
    } finally {
      hideLoader();
    }
  }

  // Crear rol - abrir modal
  btnCreateRole && btnCreateRole.addEventListener('click', () => {
    editingRoleId = null;
    roleModalTitle.textContent = 'Crear Rol';
    roleName.value = '';
    renderRolePermissionsCheckboxes([]);
    roleModal.classList.remove('hidden');
    roleModal.setAttribute('aria-hidden','false');
  });

  // Editar rol - cargar datos y abrir modal
  async function onEditClicked(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    try {
      showLoader();
      const res = await fetch(`/admin/roles/${id}`, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
      if (!res.ok) throw new Error('No se pudo obtener el rol');
  const data = await res.json();
  const role = data.role;
      if (!role) throw new Error('Rol no encontrado');
      editingRoleId = role.id;
      roleModalTitle.textContent = 'Editar Rol';
      roleName.value = role.nombre || '';
  const selected = (role.permissions || []).map(p => p.id);
      renderRolePermissionsCheckboxes(selected);
      roleModal.classList.remove('hidden');
      roleModal.setAttribute('aria-hidden','false');
    } catch (err) {
      console.error('onEditClicked error', err);
      showModal('Error', err.message || 'Error al obtener rol');
    } finally {
      hideLoader();
    }
  }

  // Eliminar rol
  async function onDeleteClicked(e) {
    const btn = e.currentTarget;
    if (btn.disabled) return; // evitar doble click
    
    const id = btn.dataset.id;
    if (!id) return;

    showModal('Eliminar Rol', '¿Estás seguro de que deseas eliminar este rol? Esta acción es irreversible.', true);
    
    // Crear botones de confirmación
    const confirmBtn = document.createElement('button');
    confirmBtn.className = 'btn btn-danger';
    confirmBtn.textContent = 'Eliminar';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn';
    cancelBtn.textContent = 'Cancelar';
    cancelBtn.style.marginRight = '8px';
    
    // Crear contenedor para botones
    const btnContainer = document.createElement('div');
    btnContainer.style.textAlign = 'right';
    btnContainer.style.marginTop = '12px';
    btnContainer.appendChild(cancelBtn);
    btnContainer.appendChild(confirmBtn);
    
    // Reemplazar botón cerrar por botones de confirmación
    const modalClose = document.getElementById('modal-close');
    if (modalClose && modalClose.parentNode) {
      modalClose.parentNode.replaceChild(btnContainer, modalClose);
    }
    
    // Eventos
    cancelBtn.onclick = hideModal;
    confirmBtn.onclick = async () => {
      hideModal();
      
      // Deshabilitar botón y cambiar texto
      const originalText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Eliminando...';

      try {
        showLoader();
      const res = await fetch(`/admin/roles/${id}`, {
        method: 'DELETE',
        headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With':'XMLHttpRequest' }
      });
      if (!res.ok) {
        const body = await res.json().catch(()=>({}));
        throw new Error(body.message || 'Error al eliminar rol');
      }
      const body = await res.json();
      showModal(body.success ? 'Éxito' : 'Error', body.message || '');
      await loadRoles();
    } catch (err) {
      showModal('Error', err.message);
    } finally {
      hideLoader();
      // Re-habilitar botón y restaurar texto
      btn.disabled = false;
      btn.textContent = originalText;
    }
    };
  }

  // Guardar rol (crear o actualizar)
  saveRole && saveRole.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    if (btn.disabled) return; // evitar doble click

    const name = roleName.value.trim();
    if (!name) { showModal('Validación', 'Debe ingresar un nombre de rol'); return; }
    const checked = Array.from(rolePermissionsList.querySelectorAll('input[type=checkbox]:checked')).map(c => parseInt(c.value));

    // Deshabilitar botón y cambiar texto
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = editingRoleId ? 'Guardando...' : 'Creando...';

    // backend espera { nombre, descripcion, permissions }
    const payload = { nombre: name, descripcion: '', permissions: checked };
    const url = editingRoleId ? `/admin/roles/${editingRoleId}` : '/admin/roles';
    const method = editingRoleId ? 'PUT' : 'POST';

    try {
      showLoader();
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify(payload)
      });
      const body = await res.json().catch(()=>({ success:false, message:'Respuesta inválida' }));
      if (!res.ok) {
        throw new Error(body.message || 'Error en la petición');
      }
      showModal(body.success ? 'Éxito' : 'Error', body.message || '');
      roleModal.classList.add('hidden');
      await loadRoles();
    } catch (err) {
      showModal('Error', err.message);
    } finally {
      hideLoader();
      // Re-habilitar botón y restaurar texto
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });

  cancelRole && cancelRole.addEventListener('click', () => {
    editingRoleId = null;
    roleModal.classList.add('hidden');
  });
  // Asegurar modales ocultos en inicio
  try { if (roleModal) roleModal.classList.add('hidden'); if (modal) modal.classList.add('hidden'); } catch(e){}

  // Inicialización
  (async () => {
    try {
      await loadPermissions();
      renderRolePermissionsCheckboxes([]);
      await loadRoles();
    } catch(e){ console.error('roles init error', e); }
  })();
  } catch(e) { 
    console.error('roles.js init error', e); 
  }
});