document.addEventListener('DOMContentLoaded', () => {
  const fileInput = document.getElementById('archivo');
  const fileInfo = document.getElementById('file-info');
  const fileName = document.getElementById('file-name');
  const btnImportar = document.getElementById('btn-importar');
  const btnCancelar = document.getElementById('btn-cancelar');
  const loader = document.getElementById('loader');
  const modal = document.getElementById('modal');
  const modalTitle = document.getElementById('modal-title');
  const modalMessage = document.getElementById('modal-message');
  const modalClose = document.getElementById('modal-close');
  const token = document.querySelector('meta[name="csrf-token"]').content;

  console.log('DOM CARGADO EXITOSAMENTE.');

  // Mostrar nombre del archivo
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      const name = fileInput.files[0].name;
      fileInfo.classList.remove('hidden');
      fileName.textContent = name;
    }
  });

  // Botón cancelar
  btnCancelar.addEventListener('click', () => {
    fileInput.value = '';
    fileInfo.classList.add('hidden');
  });

  // Mostrar modal
  const showModal = (title, message) => {
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modal.classList.remove('hidden');
  };

  modalClose.addEventListener('click', () => modal.classList.add('hidden'));

  // Importar archivo (simulación por ahora)
  btnImportar.addEventListener('click', async () => {
    if (fileInput.files.length === 0) {
      showModal('Archivo requerido', 'Seleccione un archivo antes de continuar.');
      return;
    }

    loader.classList.remove('hidden');
    const formData = new FormData();
    formData.append('archivo', fileInput.files[0]);

    try {
      const response = await fetch('/admin/importar-usuarios', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: formData
      });

      const data = await response.json();
      loader.classList.add('hidden');

      if (data.success) {
        showModal('Importación exitosa', 'Los usuarios fueron registrados correctamente.');
      } else {
        showModal('Error en importación', data.message || 'No se pudieron procesar los datos.');
      }
    } catch (error) {
      loader.classList.add('hidden');
      showModal('Error del servidor', 'No se pudo conectar con el servidor.');
    }
  });
});
