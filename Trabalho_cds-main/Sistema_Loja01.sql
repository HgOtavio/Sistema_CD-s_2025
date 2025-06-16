CREATE DATABASE LojaCDs;
USE LojaCDs;

-- Criação da tabela Usuario (armazenando clientes e administradores)
CREATE TABLE Usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único do usuário
    nome_completo VARCHAR(100) NOT NULL,       -- Nome completo do usuário
    email VARCHAR(100) UNIQUE NOT NULL,        -- E-mail (único)
    cpf VARCHAR(14) UNIQUE NOT NULL,           -- CPF (único)
    telefone VARCHAR(15) NOT NULL,             -- Telefone de contato
    login VARCHAR(50) UNIQUE NOT NULL,         -- Nome de usuário para login (único)
    senha VARCHAR(255) NOT NULL,               -- Senha criptografada
    tipo ENUM('admin', 'cliente') NOT NULL     -- Define se é 'admin' ou 'cliente'
);

-- Tabela Usuario --
ALTER TABLE Usuario ADD COLUMN data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;-- salvar data do cadastro --

ALTER TABLE Usuario ADD COLUMN foto_perfil VARCHAR(255);
SELECT * FROM Usuario;
UPDATE Usuario  
SET login = 'admin'  
WHERE id_usuario = 1; -- Substitua 1 pelo ID correto  


-- Insere um usuário administrador padrão
INSERT INTO Usuario (nome_completo, email, cpf, telefone, login, senha, tipo) 
VALUES ('Administrador', 'admin@email.com', '000.000.000-00', '(00) 00000-0000', 'admin', MD5('1234'), 'admin');

-- Criação da tabela CD (armazenando informações dos CDs)
CREATE TABLE CD (
    id_cd INT AUTO_INCREMENT PRIMARY KEY,  -- Identificador único do CD
    titulo VARCHAR(100) NOT NULL,          -- Nome do CD
    capa VARCHAR(255),                     -- Caminho da imagem da capa
    disponibilidade INT NOT NULL,          -- Quantidade disponível
    preco DOUBLE NOT NULL,                 -- Preço do CD
    destaque VARCHAR(50),                  -- Indica se é um destaque
    anoLancamento INT NOT NULL,            -- Ano de lançamento
    genero VARCHAR(50),                     -- Gênero musical
    descricao  varchar(120)
    
);

-- Tabela cd --

ALTER TABLE CD ADD COLUMN vendas INT DEFAULT 0;


-- Criação da tabela Artista (para armazenar artistas dos CDs)
CREATE TABLE Artista (
    id_artista INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único do artista
    nomeArtista VARCHAR(100) NOT NULL          -- Nome do artista
);

-- Tabela Artista --

ALTER TABLE Artista ADD CONSTRAINT unique_nomeArtista UNIQUE (nomeArtista);
ALTER TABLE Artista
ADD COLUMN dataNascimento DATE,
ADD COLUMN fotoPerfil VARCHAR(255),
ADD COLUMN descricao TEXT;



-- Criação da tabela Musica (para armazenar músicas dos CDs)
CREATE TABLE Musica (
    id_musica INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único da música
    nomeMusica VARCHAR(100) NOT NULL,         -- Nome da música
    tempo DOUBLE                              -- Duração da música
);

-- Tabela MUsica --

ALTER TABLE Musica ADD CONSTRAINT unique_nomeMusica UNIQUE (nomeMusica);
ALTER TABLE Musica ADD COLUMN audio LONGBLOB;

-- Criação da tabela Compra (armazenando compras feitas pelos clientes)
CREATE TABLE Compra (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,  -- Identificador único da compra
    id_usuario INT NOT NULL,                   -- Referência ao usuário que fez a compra
    id_cd INT NOT NULL,                        -- Referência ao CD comprado
    formaPagamento VARCHAR(50) NOT NULL,       -- Forma de pagamento usada
    enderecoEntrega VARCHAR(255),              -- Endereço de entrega da compra
    valorTotal DOUBLE NOT NULL,                -- Valor total da compra
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE, -- Se o usuário for deletado, suas compras também serão
    FOREIGN KEY (id_cd) REFERENCES CD(id_cd) ON DELETE CASCADE                  -- Se o CD for deletado, a compra também será removida
    
);

-- Tabela compra --

ALTER TABLE Compra ADD quantidade INT NOT NULL;

ALTER TABLE Compra ADD COLUMN data_compra DATETIME DEFAULT CURRENT_TIMESTAMP;


-- Tabela intermediária CD_Artista (relaciona CDs com artistas)
CREATE TABLE CD_Artista (
    id_cd INT NOT NULL,        -- ID do CD
    id_artista INT NOT NULL,   -- ID do artista
    PRIMARY KEY (id_cd, id_artista),  -- Chave composta para garantir relação única
    FOREIGN KEY (id_cd) REFERENCES CD(id_cd) ON DELETE CASCADE,   -- Deleta relacionamento se o CD for removido
    FOREIGN KEY (id_artista) REFERENCES Artista(id_artista) ON DELETE CASCADE -- Deleta relacionamento se o artista for removido
);

-- Tabela intermediária CD_Musica (relaciona CDs com músicas)
CREATE TABLE CD_Musica (
    id_cd INT NOT NULL,        -- ID do CD
    id_musica INT NOT NULL,    -- ID da música
    PRIMARY KEY (id_cd, id_musica),  -- Chave composta para garantir relação única
    FOREIGN KEY (id_cd) REFERENCES CD(id_cd) ON DELETE CASCADE,   -- Deleta relacionamento se o CD for removido
    FOREIGN KEY (id_musica) REFERENCES Musica(id_musica) ON DELETE CASCADE -- Deleta relacionamento se a música for removida
);

-- Tabela Carrinho
CREATE TABLE Carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cd INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_cd) REFERENCES CD(id_cd) ON DELETE CASCADE
);

-- Tabela Favoritos
CREATE TABLE Favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cd INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_cd) REFERENCES CD(id_cd) ON DELETE CASCADE
);

SELECT * FROM Favoritos WHERE id_usuario = 2; -- Substitua X pelo ID do usuário logado

CREATE TABLE Promocao (
    id_promocao INT AUTO_INCREMENT PRIMARY KEY,
    id_cd INT NOT NULL,
    desconto INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_cd) REFERENCES CD(id_cd) ON DELETE CASCADE
);
CREATE TABLE Sugestoes (
    id_sugestao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    genero VARCHAR(100),
    anoLancamento INT,
    preco DECIMAL(10,2),
    descricao TEXT,
    capa VARCHAR(255),
    data_sugestao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);

-- Tabela sugestoes --

ALTER TABLE Sugestoes ADD COLUMN status VARCHAR(20) DEFAULT 'Pendente';


CREATE TABLE avaliacoes (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    nota INT NOT NULL, 
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
);


-- inserções no banco de dados--

INSERT INTO CD (titulo, capa, disponibilidade, preco, destaque, anoLancamento, genero, descricao) VALUES
('Abbey Road', 'imagens/rock1.jpg', 50, 100, 'Destaque', 1969, 'Rock', 'Álbum clássico dos Beatles com hits icônicos.'),
('Let It Bleed', 'imagens/rock2.jpg', 40, 80, 'Não destaque', 1969, 'Rock', 'Álbum essencial dos Rolling Stones.'),
('Led Zeppelin IV', 'imagens/rock3.jpg', 50, 120, 'Destaque', 1971, 'Rock', 'Um dos discos mais icônicos da história do rock.'),
('The Dark Side of the Moon', 'imagens/rock4.jpg', 60, 130, 'Destaque', 1973, 'Rock Progressivo', 'Obra-prima do Pink Floyd.'),
('A Night at the Opera', 'imagens/rock5.jpg', 50, 90, 'Destaque', 1975, 'Rock', 'Um dos álbuns mais criativos do Queen.'),
('Back in Black', 'imagens/rock6.jpg', 60, 110, 'Não destaque', 1980, 'Rock', 'Clássico do AC/DC que definiu o hard rock.'),
('Who’s Next', 'imagens/rock7.jpg', 50, 90, 'Não destaque', 1971, 'Rock', 'Uma das grandes obras do The Who.'),
('Rumours', 'imagens/rock8.jpg', 50, 120, 'Destaque', 1977, 'Rock', 'Álbum épico do Fleetwood Mac.'),
('The Doors', 'imagens/rock9.jpg', 50, 80, 'Não destaque', 1967, 'Rock Psicodélico', 'Álbum de estreia dos Doors.'),
('The Rise and Fall of Ziggy Stardust and the Spiders from Mars', 'imagens/rock10.jpg', 50, 100, 'Destaque', 1972, 'Glam Rock', 'Álbum revolucionário de David Bowie.'),
('Nevermind', 'imagens/rock11.jpg', 50, 80, 'Destaque', 1991, 'Grunge', 'O álbum que definiu a era grunge.'),
('Ten', 'imagens/rock12.jpg', 50, 90, 'Não destaque', 1991, 'Grunge', 'A estreia icônica do Pearl Jam.'),
('OK Computer', 'imagens/rock13.jpg', 50, 120, 'Destaque', 1997, 'Alternativo', 'Álbum essencial do Radiohead.'),
('Siamese Dream', 'imagens/rock14.jpg', 50, 100, 'Não destaque', 1993, 'Alternativo', 'Clássico do The Smashing Pumpkins.'),
('Californication', 'imagens/rock15.jpg', 50, 100, 'Não destaque', 1999, 'Rock Alternativo', 'Álbum marcante dos Red Hot Chili Peppers.'),
('Master of Puppets', 'imagens/rock16.jpg', 50, 120, 'Destaque', 1986, 'Heavy Metal', 'O melhor do Metallica.'),
('The Number of the Beast', 'imagens/rock17.jpg', 50, 90, 'Não destaque', 1982, 'Heavy Metal', 'Um marco do Iron Maiden.'),
('Ramones', 'imagens/rock18.jpg', 50, 80, 'Não destaque', 1976, 'Punk Rock', 'Álbum de estreia do Ramones.'),
('Never Mind the Bollocks, Here’s the Sex Pistols', 'imagens/rock19.jpg', 50, 100, 'Não destaque', 1977, 'Punk Rock', 'Disco essencial do Sex Pistols.'),
('Dookie', 'imagens/rock20.jpg', 50, 90, 'Não destaque', 1994, 'Punk Rock', 'Álbum clássico do Green Day.');

INSERT INTO Artista (nomeArtista) VALUES
('The Beatles'),
('The Rolling Stones'),
('Led Zeppelin'),
('Pink Floyd'),
('Queen'),
('AC/DC'),
('The Who'),
('Fleetwood Mac'),
('The Doors'),
('David Bowie'),
('Nirvana'),
('Pearl Jam'),
('Radiohead'),
('The Smashing Pumpkins'),
('Red Hot Chili Peppers'),
('Metallica'),
('Iron Maiden'),
('Ramones'),
('Sex Pistols'),
('Green Day');


UPDATE Artista SET
    dataNascimento = '1960-01-01',
    fotoPerfil = 'Artista/the_beatles.jpg',
    descricao = 'Banda britânica formada em Liverpool, considerada uma das mais influentes de todos os tempos.'
WHERE nomeArtista = 'The Beatles';

UPDATE Artista SET
    dataNascimento = '1962-01-01',
    fotoPerfil = 'Artista/the_rolling_stones.jpg',
    descricao = 'Banda britânica de rock formada em Londres, conhecida pelo seu estilo energético e atitude rebelde.'
WHERE nomeArtista = 'The Rolling Stones';

UPDATE Artista SET
    dataNascimento = '1968-01-01',
    fotoPerfil = 'Artista/led_zeppelin.jpg',
    descricao = 'Banda britânica pioneira do hard rock e heavy metal, conhecida por seus riffs poderosos e letras místicas.'
WHERE nomeArtista = 'Led Zeppelin';

UPDATE Artista SET
    dataNascimento = '1965-01-01',
    fotoPerfil = 'Artista/pink_floyd.jpg',
    descricao = 'Banda britânica conhecida pelo seu rock progressivo, álbuns conceituais e espetáculos visuais impressionantes.'
WHERE nomeArtista = 'Pink Floyd';

UPDATE Artista SET
    dataNascimento = '1970-01-01',
    fotoPerfil = 'Artista/queen.jpg',
    descricao = 'Banda britânica icônica conhecida por sua diversidade musical e a voz incomparável de Freddie Mercury.'
WHERE nomeArtista = 'Queen';

UPDATE Artista SET
    dataNascimento = '1973-01-01',
    fotoPerfil = 'Artista/acdc.jpg',
    descricao = 'Banda australiana de hard rock conhecida por seu estilo enérgico e riffs clássicos como "Back in Black".'
WHERE nomeArtista = 'AC/DC';

UPDATE Artista SET
    dataNascimento = '1964-01-01',
    fotoPerfil = 'Artista/the_who.jpg',
    descricao = 'Banda britânica conhecida por sua inovação musical e performances destrutivas ao vivo.'
WHERE nomeArtista = 'The Who';

UPDATE Artista SET
    dataNascimento = '1967-01-01',
    fotoPerfil = 'Artista/fleetwood_mac.jpg',
    descricao = 'Banda britânica de rock conhecida por seu som diversificado e o álbum clássico "Rumours".'
WHERE nomeArtista = 'Fleetwood Mac';

UPDATE Artista SET
    dataNascimento = '1965-01-01',
    fotoPerfil = 'Artista/the_doors.jpg',
    descricao = 'Banda americana liderada por Jim Morrison, conhecida por suas letras poéticas e som psicodélico.'
WHERE nomeArtista = 'The Doors';

UPDATE Artista SET
    dataNascimento = '1967-01-08',
    fotoPerfil = 'Artista/david_bowie.jpg',
    descricao = 'Cantor, compositor e ator britânico conhecido por sua reinvenção musical e estilo único.'
WHERE nomeArtista = 'David Bowie';

UPDATE Artista SET
    dataNascimento = '1987-01-01',
    fotoPerfil = 'Artista/nirvana.jpg',
    descricao = 'Banda americana de grunge liderada por Kurt Cobain, responsável por popularizar o gênero na década de 1990.'
WHERE nomeArtista = 'Nirvana';

UPDATE Artista SET
    dataNascimento = '1990-01-01',
    fotoPerfil = 'Artista/pearl_jam.jpg',
    descricao = 'Banda americana de rock alternativo conhecida por suas letras emotivas e performances ao vivo intensas.'
WHERE nomeArtista = 'Pearl Jam';

UPDATE Artista SET
    dataNascimento = '1985-01-01',
    fotoPerfil = 'Artista/radiohead.jpg',
    descricao = 'Banda britânica de rock alternativo conhecida por sua inovação musical e letras introspectivas.'
WHERE nomeArtista = 'Radiohead';

UPDATE Artista SET
    dataNascimento = '1988-01-01',
    fotoPerfil = 'Artista/the_smashing_pumpkins.jpg',
    descricao = 'Banda americana de rock alternativo liderada por Billy Corgan, conhecida por sua mistura de estilos musicais.'
WHERE nomeArtista = 'The Smashing Pumpkins';

UPDATE Artista SET
    dataNascimento = '1983-01-01',
    fotoPerfil = 'Artista/red_hot_chili_peppers.jpg',
    descricao = 'Banda americana de rock conhecida por sua fusão de rock, funk e punk, além de performances energéticas.'
WHERE nomeArtista = 'Red Hot Chili Peppers';

UPDATE Artista SET
    dataNascimento = '1981-01-01',
    fotoPerfil = 'Artista/metallica.jpg',
    descricao = 'Banda americana de heavy metal, considerada uma das maiores e mais influentes do gênero.'
WHERE nomeArtista = 'Metallica';

UPDATE Artista SET
    dataNascimento = '1975-01-01',
    fotoPerfil = 'Artista/iron_maiden.jpg',
    descricao = 'Banda britânica de heavy metal conhecida por sua mascote Eddie e álbuns icônicos como "The Number of the Beast".'
WHERE nomeArtista = 'Iron Maiden';

UPDATE Artista SET
    dataNascimento = '1974-01-01',
    fotoPerfil = 'Artista/ramones.jpg',
    descricao = 'Banda americana pioneira do punk rock, conhecida por seu estilo simples e direto.'
WHERE nomeArtista = 'Ramones';

UPDATE Artista SET
    dataNascimento = '1975-01-01',
    fotoPerfil = 'Artista/sex_pistols.jpg',
    descricao = 'Banda britânica que ajudou a definir o movimento punk rock com seu som agressivo e atitude rebelde.'
WHERE nomeArtista = 'Sex Pistols';

UPDATE Artista SET
    dataNascimento = '1987-01-01',
    fotoPerfil = 'Artista/green_day.jpg',
    descricao = 'Banda americana de punk rock/pop punk conhecida por álbuns como "Dookie" e "American Idiot".'
WHERE nomeArtista = 'Green Day';

INSERT INTO Musica (nomeMusica, tempo) VALUES
-- The Beatles - Abbey Road
('Come Together', 4.19),
('Something', 3.03),
('Here Comes the Sun', 3.05),

-- The Rolling Stones - Let It Bleed
('Gimme Shelter', 4.30),
('You Can’t Always Get What You Want', 4.30),

-- Led Zeppelin - Led Zeppelin IV
('Stairway to Heaven', 8.02),
('Black Dog', 4.55),
('Rock and Roll', 3.40),

-- Pink Floyd - The Dark Side of the Moon
('Time', 6.53),
('Money', 6.22),
('The Great Gig in the Sky', 4.44),

-- Queen - A Night at the Opera
('Bohemian Rhapsody', 5.55),
('You’re My Best Friend', 2.50),
('Love of My Life', 3.39),

-- AC/DC - Back in Black
('Hells Bells', 5.12),
('Back in Black', 4.15),
('You Shook Me All Night Long', 3.30),

-- The Who - Who’s Next
('Baba O’Riley', 5.00),
('Behind Blue Eyes', 3.43),

-- Fleetwood Mac - Rumours
('Go Your Own Way', 3.38),
('Dreams', 4.14),
('The Chain', 4.30),

-- The Doors - The Doors
('Light My Fire', 6.50),
('Break on Through (To the Other Side)', 2.30),

-- David Bowie - The Rise and Fall of Ziggy Stardust and the Spiders from Mars
('Starman', 4.10),
('Suffragette City', 3.25),

-- Nirvana - Nevermind
('Smells Like Teen Spirit', 5.01),
('Come as You Are', 3.39),

-- Pearl Jam - Ten
('Alive', 5.40),
('Even Flow', 4.54),
('Jeremy', 5.18),

-- Radiohead - OK Computer
('Paranoid Android', 6.27),
('Karma Police', 4.21),
('No Surprises', 3.49),

-- The Smashing Pumpkins - Siamese Dream
('Today', 3.18),
('Cherub Rock', 5.30),
('Disarm', 3.13),

-- Red Hot Chili Peppers - Californication
('Scar Tissue', 3.37),
('Otherside', 4.15),
('Californication', 5.21),

-- Metallica - Master of Puppets
('Battery', 5.12),
('Master of Puppets', 8.35),
('Orion', 8.27),

-- Iron Maiden - The Number of the Beast
('Run to the Hills', 3.54),
('Hallowed Be Thy Name', 7.12),

-- Ramones - Ramones
('Blitzkrieg Bop', 2.12),
('Judy Is a Punk', 1.32),

-- Sex Pistols - Never Mind the Bollocks, Here’s the Sex Pistols
('Anarchy in the U.K.', 3.33),
('God Save the Queen', 3.20),

-- Green Day - Dookie
('Basket Case', 3.01),
('When I Come Around', 2.58);

UPDATE Musica SET audio = 'audio/come_together.mp3' WHERE nomeMusica = 'Come Together';
UPDATE Musica SET audio = 'audio/something.mp3' WHERE nomeMusica = 'Something';
UPDATE Musica SET audio = 'audio/here_comes_the_sun.mp3' WHERE nomeMusica = 'Here Comes the Sun';

UPDATE Musica SET audio = 'audio/gimme_shelter.mp3' WHERE nomeMusica = 'Gimme Shelter';
UPDATE Musica SET audio = 'audio/you_cant_always_get_what_you_want.mp3' WHERE nomeMusica = 'You Can’t Always Get What You Want';

UPDATE Musica SET audio = 'audio/stairway_to_heaven.mp3' WHERE nomeMusica = 'Stairway to Heaven';
UPDATE Musica SET audio = 'audio/black_dog.mp3' WHERE nomeMusica = 'Black Dog';
UPDATE Musica SET audio = 'audio/rock_and_roll.mp3' WHERE nomeMusica = 'Rock and Roll';

UPDATE Musica SET audio = 'audio/time.mp3' WHERE nomeMusica = 'Time';
UPDATE Musica SET audio = 'audio/money.mp3' WHERE nomeMusica = 'Money';
UPDATE Musica SET audio = 'audio/the_great_gig_in_the_sky.mp3' WHERE nomeMusica = 'The Great Gig in the Sky';

UPDATE Musica SET audio = 'audio/bohemian_rhapsody.mp3' WHERE nomeMusica = 'Bohemian Rhapsody';
UPDATE Musica SET audio = 'audio/youre_my_best_friend.mp3' WHERE nomeMusica = 'You’re My Best Friend';
UPDATE Musica SET audio = 'audio/love_of_my_life.mp3' WHERE nomeMusica = 'Love of My Life';

UPDATE Musica SET audio = 'audio/hells_bells.mp3' WHERE nomeMusica = 'Hells Bells';
UPDATE Musica SET audio = 'audio/back_in_black.mp3' WHERE nomeMusica = 'Back in Black';
UPDATE Musica SET audio = 'audio/you_shook_me_all_night_long.mp3' WHERE nomeMusica = 'You Shook Me All Night Long';

UPDATE Musica SET audio = 'audio/baba_oreilly.mp3' WHERE nomeMusica = 'Baba O’Riley';
UPDATE Musica SET audio = 'audio/behind_blue_eyes.mp3' WHERE nomeMusica = 'Behind Blue Eyes';

UPDATE Musica SET audio = 'audio/go_your_own_way.mp3' WHERE nomeMusica = 'Go Your Own Way';
UPDATE Musica SET audio = 'audio/dreams.mp3' WHERE nomeMusica = 'Dreams';
UPDATE Musica SET audio = 'audio/the_chain.mp3' WHERE nomeMusica = 'The Chain';

UPDATE Musica SET audio = 'audio/light_my_fire.mp3' WHERE nomeMusica = 'Light My Fire';
UPDATE Musica SET audio = 'audio/break_on_through.mp3' WHERE nomeMusica = 'Break on Through (To the Other Side)';

UPDATE Musica SET audio = 'audio/starman.mp3' WHERE nomeMusica = 'Starman';
UPDATE Musica SET audio = 'audio/suffragette_city.mp3' WHERE nomeMusica = 'Suffragette City';

UPDATE Musica SET audio = 'audio/smells_like_teen_spirit.mp3' WHERE nomeMusica = 'Smells Like Teen Spirit';
UPDATE Musica SET audio = 'audio/come_as_you_are.mp3' WHERE nomeMusica = 'Come as You Are';

UPDATE Musica SET audio = 'audio/alive.mp3' WHERE nomeMusica = 'Alive';
UPDATE Musica SET audio = 'audio/even_flow.mp3' WHERE nomeMusica = 'Even Flow';
UPDATE Musica SET audio = 'audio/jeremy.mp3' WHERE nomeMusica = 'Jeremy';

UPDATE Musica SET audio = 'audio/paranoid_android.mp3' WHERE nomeMusica = 'Paranoid Android';
UPDATE Musica SET audio = 'audio/karma_police.mp3' WHERE nomeMusica = 'Karma Police';
UPDATE Musica SET audio = 'audio/no_surprises.mp3' WHERE nomeMusica = 'No Surprises';

UPDATE Musica SET audio = 'audio/today.mp3' WHERE nomeMusica = 'Today';
UPDATE Musica SET audio = 'audio/cherub_rock.mp3' WHERE nomeMusica = 'Cherub Rock';
UPDATE Musica SET audio = 'audio/disarm.mp3' WHERE nomeMusica = 'Disarm';

UPDATE Musica SET audio = 'audio/scar_tissue.mp3' WHERE nomeMusica = 'Scar Tissue';
UPDATE Musica SET audio = 'audio/otherside.mp3' WHERE nomeMusica = 'Otherside';
UPDATE Musica SET audio = 'audio/californication.mp3' WHERE nomeMusica = 'Californication';

UPDATE Musica SET audio = 'audio/battery.mp3' WHERE nomeMusica = 'Battery';
UPDATE Musica SET audio = 'audio/master_of_puppets.mp3' WHERE nomeMusica = 'Master of Puppets';
UPDATE Musica SET audio = 'audio/orion.mp3' WHERE nomeMusica = 'Orion';

UPDATE Musica SET audio = 'audio/run_to_the_hills.mp3' WHERE nomeMusica = 'Run to the Hills';
UPDATE Musica SET audio = 'audio/hallowed_be_thy_name.mp3' WHERE nomeMusica = 'Hallowed Be Thy Name';

UPDATE Musica SET audio = 'audio/blitzkrieg_bop.mp3' WHERE nomeMusica = 'Blitzkrieg Bop';
UPDATE Musica SET audio = 'audio/judy_is_a_punk.mp3' WHERE nomeMusica = 'Judy Is a Punk';

UPDATE Musica SET audio = 'audio/anarchy_in_the_uk.mp3' WHERE nomeMusica = 'Anarchy in the U.K.';
UPDATE Musica SET audio = 'audio/god_save_the_queen.mp3' WHERE nomeMusica = 'God Save the Queen';

UPDATE Musica SET audio = 'audio/basket_case.mp3' WHERE nomeMusica = 'Basket Case';
UPDATE Musica SET audio = 'audio/when_i_come_around.mp3' WHERE nomeMusica = 'When I Come Around';

INSERT INTO CD_Artista (id_cd, id_artista) VALUES
(1, 1),   -- Abbey Road com The Beatles
(2, 2),   -- Let It Bleed com The Rolling Stones
(3, 3),   -- Led Zeppelin IV com Led Zeppelin
(4, 4),   -- The Dark Side of the Moon com Pink Floyd
(5, 5),   -- A Night at the Opera com Queen
(6, 6),   -- Back in Black com AC/DC
(7, 7),   -- Who’s Next com The Who
(8, 8),   -- Rumours com Fleetwood Mac
(9, 9),   -- The Doors com The Doors
(10, 10), -- The Rise and Fall of Ziggy Stardust com David Bowie
(11, 11), -- Nevermind com Nirvana
(12, 12), -- Ten com Pearl Jam
(13, 13), -- OK Computer com Radiohead
(14, 14), -- Siamese Dream com The Smashing Pumpkins
(15, 15), -- Californication com Red Hot Chili Peppers
(16, 16), -- Master of Puppets com Metallica
(17, 17), -- The Number of the Beast com Iron Maiden
(18, 18), -- Ramones com Ramones
(19, 19), -- Never Mind the Bollocks com Sex Pistols
(20, 20); -- Dookie com Green Day

INSERT INTO CD_Musica (id_cd, id_musica) VALUES
(1, 1),   -- Abbey Road com música 1
(2, 2),   -- Let It Bleed com música 2
(3, 3),   -- Led Zeppelin IV com música 3
(4, 4),   -- The Dark Side of the Moon com música 4
(5, 5),   -- A Night at the Opera com música 5
(6, 6),   -- Back in Black com música 6
(7, 7),   -- Who’s Next com música 7
(8, 8),   -- Rumours com música 8
(9, 9),   -- The Doors com música 9
(10, 10), -- The Rise and Fall of Ziggy Stardust com música 10
(11, 11), -- Nevermind com música 11
(12, 12), -- Ten com música 12
(13, 13), -- OK Computer com música 13
(14, 14), -- Siamese Dream com música 14
(15, 15), -- Californication com música 15
(16, 16), -- Master of Puppets com música 16
(17, 17), -- The Number of the Beast com música 17
(18, 18), -- Ramones com música 18
(19, 19), -- Never Mind the Bollocks com música 19
(20, 20); -- Dookie com música 20
