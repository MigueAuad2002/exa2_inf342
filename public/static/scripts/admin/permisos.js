// public/js/permisos.js
// JavaScript para CRUD de permisos (lista, crear, editar, eliminar)

document.addEventListener('DOMContentLoaded', () => {
  try {
  const token = document.querySelector('meta[name="csrf-token"]').content;
  const loader = document.getElementById('loader');
  const tableBody = document.querySelector('#permissionsTable tbody');
  const btnCreatePerm = document.getElementById('btnCreatePerm');

  const permModal = document.getElementById('permModal');
  const permModalTitle = document.getElementById('permModalTitle');
  const permKey = document.getElementById('permKey');
  const permDescription = document.getElementById('permDescription');
  const savePerm = document.getElementById('savePerm');
  const cancelPerm = document.getElementById('cancelPerm');

  const modal = document.getElementById('modal-result');
  const modalTitle = document.getElementById('modal-title');
  const modalMessage = document.getElementById('modal-message');
  const modalClose = document.getElementById('modal-close');

  let editingPermId = null;

  // Función para bloquear/desbloquear todos los botones
  function toggleButtons(disabled) {
    document.querySelectorAll('#btnCreatePerm, .btn-edit, .btn-delete, #savePerm, #cancelPerm').forEach(btn => {
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

  const showModal = (title, message) => {
    if (!modal) return alert(message);
    // hide other modals to avoid stacking and make close button reachable
    try { if (permModal) permModal.classList.add('hidden'); } catch(e){}
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    // ensure modal is above other UI
    try { modal.style.zIndex = '9999'; } catch(e){}
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden','false');
    // attach direct close handler (overwrite to avoid duplicates)
    if (modalClose) modalClose.onclick = hideModal;
  };
  const hideModal = () => {
    try {
      if (modal) { modal.classList.add('hidden'); modal.setAttribute('aria-hidden','true'); }
      if (modalTitle) modalTitle.textContent = '';
      if (modalMessage) modalMessage.textContent = '';
    } catch(e){ console.error('hideModal error', e); }
  };

  // close handlers (use delegated listener to be robust)
  document.addEventListener('click', function(e){
    if (e.target && e.target.id === 'modal-close') hideModal();
  });

  // cerrar modal al hacer click fuera del contenido
  if (modal) {
    modal.addEventListener('click', function(e){
      if (e.target === modal) hideModal();
    });
  }
  // cerrar con ESC
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') hideModal(); });

  // Toast breve
  const toastEl = document.getElementById('toast');
  function showToast(msg, time=2200){
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.style.display = 'block';
    toastEl.setAttribute('aria-hidden','false');
    setTimeout(()=>{ toastEl.style.display='none'; toastEl.setAttribute('aria-hidden','true'); }, time);
  }

  function escapeHtml(text='') {
    return text.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  // Cargar tabla
  async function loadTable() {
    try {
      showLoader();
  const res = await fetch('/admin/permissions', { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Error al cargar permisos');
      const data = await res.json();
      const list = data.permissions || [];
      tableBody.innerHTML = '';
      if (!list.length) {
        tableBody.innerHTML = `<tr><td class="empty-row" colspan="4">No hay permisos registrados. Usa "Crear Permiso" para agregar uno.</td></tr>`;
        return;
      }
      list.forEach((p, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${i+1}</td>
          <td>${escapeHtml(p.nombre)}</td>
          <td>${escapeHtml(p.descripcion || '')}</td>
          <td>
            <button data-id="${p.id}" class="btn-edit btn">Editar</button>
            <button data-id="${p.id}" class="btn-delete btn btn-danger">Eliminar</button>
          </td>
        `;
        tableBody.appendChild(tr);
      });

      // Eventos
      document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', onEditPerm));
      document.querySelectorAll('.btn-delete').forEach(b => b.addEventListener('click', onDeletePerm));
    } catch (err) {
      console.error('loadTable error', err);
      showModal('Error', err.message || 'Error desconocido al cargar permisos');
    } finally {
      hideLoader();
    }
  }

  // Crear permiso - abrir modal
  btnCreatePerm && btnCreatePerm.addEventListener('click', () => {
    editingPermId = null;
    permModalTitle.textContent = 'Crear Permiso';
    permKey.value = '';
    permDescription.value = '';
    permModal.classList.remove('hidden');
    permModal.setAttribute('aria-hidden','false');
  });

  // Editar permiso - cargar y abrir
  async function onEditPerm(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    try {
      showLoader();
  const res = await fetch(`/admin/permissions/${id}`, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
      if (!res.ok) throw new Error('No se pudo obtener permiso');
      const data = await res.json();
      const p = data.permission;
      if (!p) throw new Error('Permiso no encontrado');
      editingPermId = p.id;
      permModalTitle.textContent = 'Editar Permiso';
      permKey.value = p.nombre || '';
      permDescription.value = p.descripcion || '';
      permModal.classList.remove('hidden');
      permModal.setAttribute('aria-hidden','false');
    } catch (err) {
      console.error('onEditPerm error', err);
      showModal('Error', err.message || 'Error al obtener permiso');
    } finally {
      hideLoader();
    }
  }

  // Eliminar permiso
  async function onDeletePerm(e) {
    const btn = e.currentTarget;
    if (btn.disabled) return; // evitar doble click
    
    const id = btn.dataset.id;
    if (!id) return;
    // usar confirmación modal personalizada
    const confirmText = '¿Eliminar permiso? Esto puede afectar roles que lo usan. ¿Deseas continuar?';
    const ok = await showConfirm(confirmText);
    if (!ok) return;

    // Deshabilitar botón y cambiar texto
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Eliminando...';

    try {
      showLoader();
      const res = await fetch(`/admin/permissions/${id}`, {
        method: 'DELETE',
        headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With':'XMLHttpRequest' }
      });
      if (!res.ok) {
        const body = await res.json().catch(()=>({}));
        throw new Error(body.message || 'Error al eliminar');
      }
      const body = await res.json();
      showModal(body.success ? 'OK' : 'Error', body.message || '');
      if (body.success) showToast(body.message || 'Permiso eliminado');
      await loadTable();
    } catch (err) {
      console.error('onDeletePerm error', err);
      showModal('Error', err.message || 'Error al eliminar permiso');
    } finally {
      hideLoader();
      // Re-habilitar botón y restaurar texto
      btn.disabled = false;
      btn.textContent = originalText;
    }
  }

  // confirm modal helper (returns Promise<boolean>)
  function showConfirm(message){
    return new Promise((resolve)=>{
      const cModal = document.getElementById('confirmModal');
      const cMsg = document.getElementById('confirmMessage');
      const btnYes = document.getElementById('confirmYes');
      const btnNo = document.getElementById('confirmNo');
      if (!cModal || !btnYes || !btnNo || !cMsg) return resolve(false);
      // set text
      cMsg.textContent = message;
      // show
      cModal.classList.remove('hidden'); cModal.setAttribute('aria-hidden','false');
      // handlers
      const cleanup = ()=>{ btnYes.removeEventListener('click', onYes); btnNo.removeEventListener('click', onNo); cModal.classList.add('hidden'); cModal.setAttribute('aria-hidden','true'); };
      const onYes = ()=>{ cleanup(); resolve(true); };
      const onNo = ()=>{ cleanup(); resolve(false); };
      btnYes.addEventListener('click', onYes);
      btnNo.addEventListener('click', onNo);
      // allow click outside or ESC to cancel
      const outsideClick = (ev)=>{ if (ev.target === cModal) { cleanup(); resolve(false); document.removeEventListener('click', outsideClick); } };
      document.addEventListener('click', outsideClick);
      const esc = (ev)=>{ if (ev.key === 'Escape') { cleanup(); resolve(false); document.removeEventListener('keydown', esc); } };
      document.addEventListener('keydown', esc);
    });
  }

  // Guardar permiso (crear o actualizar)
  savePerm && savePerm.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    if (btn.disabled) return; // evitar doble click

    const key = permKey.value.trim();
    const description = permDescription.value.trim();
    if (!key) { showModal('Validación', 'Debe ingresar el nombre del permiso.'); return; }

    // Deshabilitar botón y cambiar texto
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = editingPermId ? 'Guardando...' : 'Creando...';

    // backend espera { nombre, descripcion }
    const payload = { nombre: key, descripcion: description };
    const url = editingPermId ? `/admin/permissions/${editingPermId}` : '/admin/permissions';
    const method = editingPermId ? 'PUT' : 'POST';

    try {
      showLoader();
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify(payload)
      });
      const body = await res.json().catch(()=>({ success:false, message:'Respuesta inválida' }));
      if (!res.ok) throw new Error(body.message || 'Error en la petición');
      showModal(body.success ? 'Éxito' : 'Error', body.message || '');
      permModal.classList.add('hidden');
      if (body.success) showToast(body.message || 'Permiso guardado');
      await loadTable();
    } catch (err) {
      console.error('savePerm error', err);
      showModal('Error', err.message || 'Error al guardar permiso');
    } finally {
      hideLoader();
      // Re-habilitar botón y restaurar texto
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });

  cancelPerm && cancelPerm.addEventListener('click', () => {
    editingPermId = null;
    permModal.classList.add('hidden');
  });
  // Asegurar modales ocultos al inicio (defensa contra estados abiertos accidentalmente)
  try { if (permModal) permModal.classList.add('hidden'); if (modal) modal.classList.add('hidden'); } catch(e){}

  // Inicializar
  loadTable();
  } catch (e) { console.error('permisos.js init error', e); }
});
