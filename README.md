# PetService

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=for-the-badge&logo=docker&logoColor=white)
![Swagger](https://img.shields.io/badge/-Swagger-%23Clojure?style=for-the-badge&logo=swagger&logoColor=white)

API construída com PHP/Laravel e o banco de dados MySQL para realizar serviços para pets, como: banho, tosa, exames e consultas. Registra clientes, pets, agenda serviços e disponibiliza prontuários de veterinários de forma segura. Utiliza Docker como ambiente de desenvolvimento e deploy. Possui sua documentação feita com Swagger, onde é possível ver todos os endpoints e as regras de negócio.

## Como usar o projeto

Instale o Docker, caso não possuir em sua maquina

### Clone o Repositório
```sh
git clone https://github.com/Hiago-Laureano/pet-service.git
```

### Crie o arquivo .env
```sh
cp .env.example .env
```

### Dê o nome e senha que desejar ao seu banco de dados e usuário, alterando as variáveis do arquivo .env
```dosini
DB_DATABASE=laravel
DB_PASSWORD=12345
```

### Subir o ambiente de desenvolvimento com Docker
```sh
docker-compose -f docker-compose-dev.yml up -d
```

### Acesse o container da API para poder usar os comandos do Laravel
```sh
docker-compose -f docker-compose-dev.yml exec app bash
```

### Instale as dependências do projeto
```sh
composer install
```

### Gere a key do projeto Laravel
```sh
php artisan key:generate
```

### Faça a migração de dados
```sh
php artisan migrate
```

### Gere a documentação da API
```sh
php artisan l5-swagger:generate
```

### Acessar a API

A API estará rodando no http://localhost

### Documentação da API
Para acessar as informações de endpoints e as regras de negócio:

[http://localhost/api/doc](http://localhost/api/doc)