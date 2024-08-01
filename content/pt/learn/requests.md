# Pedidos

Flight encapsula a solicitação HTTP em um único objeto, que pode ser
acessado fazendo:

```php
$request = Flight::request();
```

## Casos de Uso Típicos

Quando você está trabalhando com uma solicitação em uma aplicação web, normalmente você vai
querer extrair um cabeçalho, ou um parâmetro `$_GET` ou `$_POST`, ou talvez
até o corpo da solicitação bruta. Flight fornece uma interface simples para fazer tudo isso.

Aqui está um exemplo de obtenção de um parâmetro de string de consulta:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Você está procurando por: $keyword";
	// consultar um banco de dados ou algo mais com a $keyword
});
```

Aqui está um exemplo talvez de um formulário com um método POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Você enviou: $name, $email";
	// salvar em um banco de dados ou algo mais com o $name e $email
});
```

## Propriedades do Objeto de Solicitação

O objeto de solicitação fornece as seguintes propriedades:

- **body** - O corpo bruto da solicitação HTTP
- **url** - A URL sendo solicitada
- **base** - O subdiretório pai da URL
- **method** - O método da solicitação (GET, POST, PUT, DELETE)
- **referrer** - A URL do remetente
- **ip** - Endereço IP do cliente
- **ajax** - Se a solicitação é uma solicitação AJAX
- **scheme** - O protocolo do servidor (http, https)
- **user_agent** - Informações do navegador
- **type** - O tipo de conteúdo
- **length** - O comprimento do conteúdo
- **query** - Parâmetros de string de consulta
- **data** - Dados de postagem ou dados JSON
- **cookies** - Dados de cookie
- **files** - Arquivos enviados
- **secure** - Se a conexão é segura
- **accept** - Parâmetros de aceitação HTTP
- **proxy_ip** - Endereço IP do proxy do cliente. Verifica a matriz `$_SERVER` para `HTTP_CLIENT_IP`,`HTTP_X_FORWARDED_FOR`,`HTTP_X_FORWARDED`,`HTTP_X_CLUSTER_CLIENT_IP`,`HTTP_FORWARDED_FOR`,`HTTP_FORWARDED` nessa ordem.
- **host** - O nome do host da solicitação

Você pode acessar as propriedades `query`, `data`, `cookies` e `files`
como arrays ou objetos.

Então, para obter um parâmetro de string de consulta, você pode fazer:

```php
$id = Flight::request()->query['id'];
```

Ou você pode fazer:

```php
$id = Flight::request()->query->id;
```

## Corpo da Solicitação RAW

Para obter o corpo bruto da solicitação HTTP, por exemplo, ao lidar com solicitações PUT,
você pode fazer:

```php
$body = Flight::request()->getBody();
```

## Entrada JSON

Se você enviar uma solicitação com o tipo `application/json` e os dados `{"id": 123}`
estarão disponíveis a partir da propriedade `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Você pode acessar a matriz `$_GET` via a propriedade `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Você pode acessar a matriz `$_POST` via a propriedade `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Você pode acessar a matriz `$_COOKIE` via a propriedade `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Há um atalho disponível para acessar a matriz `$_SERVER` via o método `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Arquivos Enviados via `$_FILES`

Você pode acessar arquivos enviados via a propriedade `files`:

```php
$arquivoEnviado = Flight::request()->files['meuArquivo'];
```

## Cabeçalhos da Solicitação

Você pode acessar cabeçalhos da solicitação usando o método `getHeader()` ou `getHeaders()`:

```php

// Talvez você precise do cabeçalho de Autorização
$host = Flight::request()->getHeader('Authorization');
// ou
$host = Flight::request()->header('Authorization');

// Se você precisar pegar todos os cabeçalhos
$headers = Flight::request()->getHeaders();
// ou
$headers = Flight::request()->headers();
```

## Corpo da Solicitação

Você pode acessar o corpo da solicitação bruta usando o método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método da Solicitação

Você pode acessar o método da solicitação usando a propriedade `method` ou o método `getMethod()`:

```php
$método = Flight::request()->method; // na verdade chama getMethod()
$método = Flight::request()->getMethod();
```

**Nota:** O método `getMethod()` primeiro obtém o método de `$_SERVER['REQUEST_METHOD']`, então pode ser sobrescrito
por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` se ele existir ou `$_REQUEST['_method']` se existir.

## URLs da Solicitação

Existem alguns métodos auxiliares para unir partes de uma URL para sua conveniência.

### URL Completa

Você pode acessar a URL completa da solicitação usando o método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL Base

Você pode acessar a URL base usando o método `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Observe, sem barra no final.
// https://example.com
```

## Análise de Consulta

Você pode passar uma URL para o método `parseQuery()` para analisar a string de consulta em um array associativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```