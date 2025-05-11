const select = document.getElementById("tipo_sugestao");
    const infoDivs = document.querySelectorAll(".itens_input");

    select.addEventListener("change", () => {
      const itemSelecionado = select.value;

      // Esconde todos os blocos
      infoDivs.forEach(div => div.style.display = "none");

      // Mostra o bloco correspondente, se existir
      if (itemSelecionado) {
        const divSelecionada = document.getElementById(itemSelecionado);
        if (divSelecionada) {
          divSelecionada.style.display = "flex";
        }
      }
    });