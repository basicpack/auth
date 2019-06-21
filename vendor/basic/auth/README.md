# auth
Sistema básico de autenticação

## Composer
composer require basic/auth

## Instalação
O Auth funciona por injeção de dependência. Para tanto é necessário ter o [Medoo](http://medoo.in/) instalado e configurado.
```
<?php
require 'vendor/autoload.php';
//$db=Instância do Medoo
$Auth=new Auth($db);
```

## Tabela users
```
id
email
name
password
type
token
token_expiration
created_at
updated_at
deleted_at
uuid
```

## Dados do usuário
Retorna os dados do usuário ou false

$user=$Auth->isAuth();

## Logout
Retorna sempre true

$user=$Auth->logout();

## Signup
Campos $_POST requeridos:
```
name
email
password
type (admin ou user)
```

Retorna os dados do usuário ou um array com as mensagens de erro

$user=$Auth->signup();

## Mensagens de erro de signup
- invalid_name (apenas letras, números e espaços)
- invalid_email
- invalid_password (maior ou igual a 8 caracteres)

## Signin
Campos $_POST requeridos:
```
email
password
```

Retorna os dados do usuário ou um array com as mensagens de erro

$user=$Auth->signin();

## Mensagens de erro de signin
- invalid_email
- invalid_password
