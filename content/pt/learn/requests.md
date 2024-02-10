# Pedidos

O Flight encapsula o pedido HTTP em um único objeto, que pode ser
acessado através de:

```php
$request = Flight::request();
```

O objeto de pedido fornece as seguintes propriedades:

- **body** - O corpo bruto do pedido HTTP
- **url** - O URL sendo solicitado
- **base** - O subdiretório pai do URL
- **method** - O método do pedido (GET, POST, PUT, DELETE)
- **referrer** - O URL do remetente
- **ip** - Endereço IP do cliente
- **ajax** - Se o pedido é uma solicitação AJAX
- **scheme** - O protocolo do servidor (http, https)
- **user_agent** - Informações do navegador
- **type** - O tipo de conteúdo
- **length** - O comprimento do conteúdo
- **query** - Parâmetros da string de consulta
- **data** - Dados de postagem ou dados JSON
- **cookies** - Dados dos cookies
- **files** - Arquivos enviados
- **secure** - Se a conexão é segura
- **accept** - Parâmetros de aceitação HTTP
- **proxy_ip** - Endereço IP do proxy do cliente
- **host** - O nome do host do pedido

Você pode acessar as propriedades `query`, `data`, `cookies` e `files`
como arrays ou objetos.

Portanto, para obter um parâmetro da string de consulta, você pode fazer:

```php
$id = Flight::request()->query['id'];
```

Ou você pode fazer:

```php
$id = Flight::request()->query->id;
```

## Corpo do Pedido Bruto

Para obter o corpo bruto do pedido HTTP, por exemplo, ao lidar com pedidos PUT,
você pode fazer:

```php
$body = Flight::request()->getBody();
```

## Entrada JSON

Se você enviar um pedido com o tipo `application/json` e os dados `{"id": 123}`,
eles estarão disponíveis na propriedade `data`:

```php
$id = Flight::request()->data->id;
```

## Acessando `$_SERVER`

Há um atalho disponível para acessar a matriz `$_SERVER` através do método `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Acessando Cabeçalhos do Pedido

Você pode acessar os cabeçalhos do pedido usando o método `getHeader()` ou `getHeaders()`:

```php

// Talvez você precise do cabeçalho de Autorização
$host = Flight::request()->getHeader('Authorization');

// Se precisar pegar todos os cabeçalhos
$headers = Flight::request()->getHeaders();
```