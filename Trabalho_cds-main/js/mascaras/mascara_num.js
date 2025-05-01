document.querySelectorAll('.num').forEach(function(input) {
    input.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '');
    });
  });
  