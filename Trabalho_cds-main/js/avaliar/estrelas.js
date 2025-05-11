const estrelas = document.querySelectorAll(".estrela");

estrelas.forEach((estrela, index) => {
  estrela.addEventListener("click", () => {
    let nota = index + 1;
    document.getElementById("nota_escolhida").value = nota;

    // Remove seleção anterior
    estrelas.forEach(e => e.classList.remove("selecionada"));

    // Marca as estrelas até a nota
    for (let i = 0; i < nota; i++) {
      estrelas[i].classList.add("selecionada");
    }
  });
});

// Validação antes de enviar o formulário
document.querySelector("form").addEventListener("submit", function(event) {
  const nota = document.getElementById("nota_escolhida").value;
  if (!nota) {
    alert("Por favor, selecione uma nota!");
    event.preventDefault(); // Impede o envio do formulário
  }
});
