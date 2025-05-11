document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll("#favoritar-form");

    forms.forEach(function (form) {
        const button = form.querySelector(".btn-favorito");
        const img = form.querySelector(".img_favorito");

        // Caminhos das imagens
        const img1 = "../../img/todos_produtos/icone_favoritos.png";
        const img2 = "../../img/todos_produtos/icone_favoritos_selecionado.png";

        button.addEventListener("click", function () {
            const formData = new FormData(form);

            fetch("favoritar.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.favoritado) {
                    img.src = img2;
                } else {
                    img.src = img1;
                }
            })
            .catch(error => {
                console.error("Erro ao favoritar:", error);
            });
        });
    });
});
