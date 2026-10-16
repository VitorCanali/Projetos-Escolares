-- Pokédex Arena — base para MySQL 8+
-- Importar em um ambiente de aula. O script recria somente estas quatro tabelas.
CREATE DATABASE IF NOT EXISTS saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS batalhas;
DROP TABLE IF EXISTS pokemons;
DROP TABLE IF EXISTS treinadores;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE treinadores (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    regiao VARCHAR(60) NOT NULL,
    insignias TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT chk_treinadores_insignias CHECK (insignias <= 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pokemons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    treinador_id INT UNSIGNED NOT NULL,
    nome VARCHAR(80) NOT NULL,
    especie VARCHAR(80) NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    nivel TINYINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_pokemons_treinador (treinador_id),
    CONSTRAINT chk_pokemons_nivel CHECK (nivel BETWEEN 1 AND 100),
    CONSTRAINT fk_pokemons_treinador FOREIGN KEY (treinador_id)
        REFERENCES treinadores (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE batalhas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pokemon_id INT UNSIGNED NOT NULL,
    adversario VARCHAR(80) NOT NULL,
    local VARCHAR(100) NOT NULL,
    data_batalha DATE NOT NULL,
    resultado ENUM('Vitória', 'Derrota', 'Empate') NOT NULL,
    PRIMARY KEY (id),
    KEY idx_batalhas_pokemon_data (pokemon_id, data_batalha),
    CONSTRAINT fk_batalhas_pokemon FOREIGN KEY (pokemon_id)
        REFERENCES pokemons (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Senhas de demonstração: ash / pikachu123, misty / staryu123, brock / onix123.
INSERT INTO usuarios (nome, senha) VALUES
('ash',   '$2y$12$JzoUwoSFA.WLg42jEj0NHOKjWuv36I/nAgiJrCnmWM8H8.9Nh5DJ2'),
('misty', '$2y$12$OdrSRWsYkJsMOPtCH7i2/utr/OvDkbOs.3WM4.w8V/SzHDfTlkzbi'),
('brock', '$2y$12$neXI37bCK9wFevRD4c2oFuIsrCWSPu9wz.ueAMat1L1RZMu8VT0b4.');

INSERT INTO treinadores (id, nome, regiao, insignias) VALUES
(1, 'Ash Ketchum', 'Kanto', 8),
(2, 'Misty', 'Kanto', 7),
(3, 'Brock', 'Johto', 5);

INSERT INTO pokemons (id, treinador_id, nome, especie, tipo, nivel) VALUES
(1, 1, 'Pikachu', 'Pokémon Rato', 'Elétrico', 42),
(2, 1, 'Charizard', 'Pokémon Chama', 'Fogo', 55),
(3, 1, 'Bulbasaur', 'Pokémon Semente', 'Planta', 28),
(4, 2, 'Staryu', 'Pokémon Estrela', 'Água', 36),
(5, 2, 'Psyduck', 'Pokémon Pato', 'Água', 24),
(6, 2, 'Togepi', 'Pokémon Ovo', 'Fada', 19),
(7, 3, 'Onix', 'Pokémon Cobra de Pedra', 'Pedra', 44),
(8, 3, 'Geodude', 'Pokémon Rocha', 'Pedra', 31),
(9, 3, 'Vulpix', 'Pokémon Raposa', 'Fogo', 26);

INSERT INTO batalhas (id, pokemon_id, adversario, local, data_batalha, resultado) VALUES
(1, 1, 'Gary Carvalho', 'Liga Índigo', '2026-10-12', 'Vitória'),
(2, 4, 'Sabrina', 'Ginásio de Saffron', '2026-10-10', 'Derrota'),
(3, 7, 'Líder de Ginásio Falkner', 'Ginásio de Violet', '2026-10-08', 'Vitória'),
(4, 2, 'Equipe Rocket', 'Monte Lua', '2026-10-04', 'Vitória'),
(5, 5, 'Treinadora Erika', 'Jardim de Celadon', '2026-09-29', 'Empate'),
(6, 8, 'Rival Silver', 'Rota 32', '2026-09-21', 'Derrota');
