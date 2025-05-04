const elementos = document.querySelectorAll('.nome_cd');
const fontSizeInicial = 35;
const fontSizeMinimo = 12;
const fatorReducao = 0.3;

elementos.forEach(elemento => {
  const comprimento = elemento.textContent.length;
  let novoTamanho = fontSizeInicial - comprimento * fatorReducao;
  novoTamanho = Math.max(fontSizeMinimo, novoTamanho);
  elemento.style.fontSize = novoTamanho + 'px';
});