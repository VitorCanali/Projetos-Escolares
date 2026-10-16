# Pokédex Arena

Projeto PHP + MySQL inspirado no sistema da oficina. O tema Pokémon segue a hierarquia **Treinador → Pokémons → Batalhas**.

## O que tem

- Login por usuário e senha, com senha armazenada em hash.
- Painel com o nome da pessoa conectada, resumo dos registros e botão para sair.
- CRUD de treinadores com busca por nome ou região.
- CRUD de Pokémons, cada um associado a um treinador.
- CRUD de batalhas, cada uma associada a um Pokémon e listada da data mais recente para a mais antiga.
- Relacionamentos com chaves estrangeiras e exclusão em cascata.
- Layout responsivo em tema claro, roxo, vermelho e amarelo.
- Dados de demonstração: pelo menos três linhas em cada tabela.

## Preparar o banco

Importe o arquivo saep_db.sql em um MySQL 8+. O arquivo cria e preenche o banco saep_db. Ele apaga e recria as tabelas usuarios, treinadores, pokemons e batalhas para carregar os dados de demonstração.

    mysql -u root -p < saep_db.sql

Se o usuário ou a senha local do MySQL forem diferentes, ajuste os valores no início de sistema/config.php ou defina as variáveis DB_HOST, DB_USER, DB_PASSWORD e DB_NAME. A configuração não inclui senha local; defina DB_PASSWORD no ambiente ou configure o seu próprio valor localmente.

## Executar

Coloque a pasta sistema no diretório do Apache/PHP e abra login.php. O projeto usa PHP 8, MySQLi e as extensões mbstring e mysqli.

## Acessos de demonstração

| Usuário | Senha |
| --- | --- |
| ash | pikachu123 |
| misty | staryu123 |
| brock | onix123 |

## Arquivos

- sistema/ — aplicação PHP e CSS.
- saep_db.sql — criação do banco, relacionamentos e dados iniciais.
- DER.svg e DER.md — diagrama visual e fonte Mermaid do modelo.
- prints/ — capturas das telas do projeto.
