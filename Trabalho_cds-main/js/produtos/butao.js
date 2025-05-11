const botoes = document.querySelectorAll('.but_baixo');
      const conteudos = document.querySelectorAll('.dentro');
    
      botoes.forEach((botao, index) => {
        botao.addEventListener('click', () => {
          // Remove 'ativo' de todos os botões
          botoes.forEach(b => b.classList.remove('ativo'));
          
          // Adiciona 'ativo' no botão clicado
          botao.classList.add('ativo');
          
          // Esconde todos os conteúdos
          conteudos.forEach(c => c.classList.remove('ativo'));
          
          // Mostra o conteúdo correspondente
          document.getElementById(`conteudo${index + 1}`).classList.add('ativo');
        });
      });