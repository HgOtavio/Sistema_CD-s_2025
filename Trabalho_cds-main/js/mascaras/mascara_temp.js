document.querySelectorAll('.tempo').forEach(function(input) {
    input.addEventListener('input', function () {
      let valor = this.value.replace(/\D/g, ''); // remove tudo que não for número
  
      if (valor.length > 2) {
        valor = valor.slice(0, 2) + ':' + valor.slice(2, 4);
      }
  
      this.value = valor.slice(0, 5); // limita a 5 caracteres (HH:MM)
    });
  });
  