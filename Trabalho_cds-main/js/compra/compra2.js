document.addEventListener("DOMContentLoaded", function () {
    const tipoPagamentoSelect = document.querySelectorAll("select.tipo")[1]; // Segundo select (Boleto/Cartão)
    const divCartao = document.getElementById("cart");
    const divPix = document.getElementById("pix");

    tipoPagamentoSelect.addEventListener("change", () => {
        if (tipoPagamentoSelect.value === "cart") {
            divCartao.style.display = "block";
            divPix.style.display = "none";
        } else if (tipoPagamentoSelect.value === "pix") {
            divCartao.style.display = "none";
            divPix.style.display = "block";
        }
    });

    // Oculta inicialmente
    divCartao.style.display = "none";
    divPix.style.display = "none";
});
