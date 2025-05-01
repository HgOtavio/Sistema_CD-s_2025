document.querySelectorAll('.preco').forEach(function(input) {
    input.addEventListener('input', function () {
      let valor = this.value.replace(/\D/g, '');
  
      if (valor === '') {
        this.value = '';
        return;
      }
  
      valor = (parseInt(valor, 10) / 100).toFixed(2);
  
      valor = valor
        .replace('.', ',')
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  
      this.value = 'R$ ' + valor;
    });
  
    // Remove o "R$" se o campo for limpo manualmente
    input.addEventListener('blur', function () {
      if (this.value === 'R$ 0,00') {
        this.value = '';
      }
    });
  });
  