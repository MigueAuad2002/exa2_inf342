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

  //MOSTRAR NOMBRE DE ARCHIVO
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      const name = fileInput.files[0].name;
      fileInfo.classList.remove('hidden');
      fileName.textContent = name;
    }
  });

  //CANCELLAR
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

  //IMPORTAR ARCHIV
  btnImportar.addEventListener('click', async () => {
    if (fileInput.files.length === 0) {
      showModal('Archivo requerido', 'Seleccione un archivo antes de continuar.');
      return;
    }

    loader.classList.remove('hidden');
    const formData = new FormData();
    formData.append('archivo', fileInput.files[0]);

    const response = await fetch('/admin/import-users', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: formData
    });

    const data = await response.json();
    loader.classList.add('hidden');

    if (data.success) {
      const results = data.data || [];
      const hasErrors = results.some(r => r.success === false);

      if (hasErrors) 
      {
        const errores = results
          .filter(r => !r.success)
          .map(r => `• ${r.message}`)
          .join('\n');
        showModal('Importación parcial', `Algunos usuarios no se importaron:\n${errores}`);
      } 
      else 
      {
        showModal('Importación exitosa', 'Todos los usuarios fueron registrados correctamente.');
      }
    } 
    else 
    {
      showModal('Error en importación', data.message || 'No se pudieron procesar los datos.');
    }

  });
});
