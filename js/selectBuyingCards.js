document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.cantidad-input');
    const totalContainer = document.getElementById('totalContainer');

    function updateTotal() {
      let total = 0;
      inputs.forEach(input => {
        const cantidad = parseInt(input.value) || 0;
        const precio = parseFloat(input.getAttribute('data-precio')) || 0;
        total += cantidad * precio;
      });
      totalContainer.textContent = 'Total: ' + total.toFixed(2) + ' €';
    }

    inputs.forEach(input => {
      input.addEventListener('input', updateTotal);
    });
  });