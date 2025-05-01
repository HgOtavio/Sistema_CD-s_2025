document.querySelectorAll('.ano').forEach(function(input) {
    input.addEventListener('input', function () {
      // Remove caracteres não numéricos
      this.value = this.value.replace(/\D/g, '');
      // Limita a 4 caracteres
      if (this.value.length > 4) {
        this.value = this.value.slice(0, 4);
      }
    });
  });
  