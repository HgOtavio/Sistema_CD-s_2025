let contador = 0;

    function sincronizarLabels() {
      const lista = document.getElementById('butoes_endereco');
      const inputs = lista.querySelectorAll('input[type="checkbox"]');
      const labels = lista.querySelectorAll('label.checkbox_label_b');

      inputs.forEach((input, index) => {
        const id = `check-${index}`;
        input.id = id;

        const label = labels[index];
        if (label) {
          label.setAttribute('for', id);
        }
      });

      contador = inputs.length; // atualiza contador
    }


    // Rodar ao carregar a página
    window.addEventListener('DOMContentLoaded', sincronizarLabels);