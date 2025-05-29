// js/selectRow.js
document.addEventListener("DOMContentLoaded", function() {
    const filas = document.querySelectorAll(".fila-edicion");
    const inputHidden = document.getElementById("inputAbreviatura");
  
    filas.forEach(fila => {
      fila.addEventListener("click", () => {
        filas.forEach(f => f.classList.remove("selected"));
        fila.classList.add("selected");
  
        inputHidden.value = fila.dataset.abreviatura;
      });
    });
  });
  