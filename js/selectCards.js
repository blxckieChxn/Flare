document.addEventListener('DOMContentLoaded', function () {
    const images = document.querySelectorAll('.card');
    const hiddenInput = document.getElementById('selectedImages');
    let selectedValues = [];

    images.forEach(image => {
        image.addEventListener('click', function () {
            const value = this.getAttribute('data-value');
            const isSelected = this.getAttribute('data-selected') === 'true';

            if (isSelected) {
                this.setAttribute('data-selected', 'false');
                this.classList.remove('selected');
                selectedValues = selectedValues.filter(val => val !== value);
            } else {
                this.setAttribute('data-selected', 'true');
                this.classList.add('selected');
                if (value && value.trim() !== '' && !selectedValues.includes(value)) {
                    selectedValues.push(value);
                }
            }

            hiddenInput.value = JSON.stringify(selectedValues);
        });
    });
});
