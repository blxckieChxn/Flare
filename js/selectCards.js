document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.card');
    const hiddenInput = document.getElementById('selectedImages');
    let selectedValues = [];

    images.forEach(image => {
        image.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const isSelected = this.getAttribute('data-selected') === 'true';

            if (isSelected) {
                this.setAttribute('data-selected', 'false');
                selectedValues = selectedValues.filter(val => val !== value);
            } else {
                this.setAttribute('data-selected', 'true');
                // Agregar valor solo si no está en el array y no es nulo o cadena vacía
                if (value && value.trim() !== '' && !selectedValues.includes(value)) {
                    selectedValues.push(value);
                }
            }

            hiddenInput.value = JSON.stringify(selectedValues);
        });
    });
});