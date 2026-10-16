# DER — Pokédex Arena

~~~mermaid
erDiagram
    USUARIOS {
        int id PK
        varchar nome UK
        varchar senha
    }
    TREINADORES {
        int id PK
        varchar nome
        varchar regiao
        tinyint insignias
    }
    POKEMONS {
        int id PK
        int treinador_id FK
        varchar nome
        varchar especie
        varchar tipo
        tinyint nivel
    }
    BATALHAS {
        int id PK
        int pokemon_id FK
        varchar adversario
        varchar local
        date data_batalha
        enum resultado
    }
    TREINADORES ||--o{ POKEMONS : treina
    POKEMONS ||--o{ BATALHAS : participa
~~~

usuarios não possui relacionamento. Um treinador pode cadastrar vários Pokémons, e cada Pokémon pertence a um treinador. Um Pokémon pode participar de várias batalhas, e cada batalha pertence a um Pokémon.
