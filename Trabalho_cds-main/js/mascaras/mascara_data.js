document.querySelectorAll('.data').forEach(function(input) {
    input.addEventListener('input', function () {
      // Remove tudo que não for número
      let valor = this.value.replace(/\D/g, '');
  
      // Adiciona as barras conforme o usuário digita
      if (valor.length > 2 && valor.length <= 4) {
        valor = valor.slice(0, 2) + '/' + valor.slice(2);
      } else if (valor.length > 4) {
        valor = valor.slice(0, 2) + '/' + valor.slice(2, 4) + '/' + valor.slice(4, 8);
      }
  
      this.value = valor.slice(0, 10); // Limita a 10 caracteres
    });
  });
  