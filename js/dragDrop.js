const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('fileInput');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length) {
    // Aquí puedes manejar la carga de archivos
    console.log('Archivo subido:', files[0]);
    }
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
    console.log('Archivo seleccionado:', fileInput.files[0]);
    }
});
