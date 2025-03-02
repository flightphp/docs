# Requisições

Flight encapsula a requisição HTTP em um único objeto, que pode ser
acessado fazendo:

```php
$request = Flight::request();
```

## Casos de Uso Típicos

Quando você está trabalhando com uma requisição em uma aplicação web, tipicamente você vai
querer extrair um cabeçalho, ou um parâmetro `$_GET` ou `$_POST`, ou talvez
até mesmo o corpo bruto da requisição. Flight fornece uma interface simples para fazer todas essas coisas.

Aqui está um exemplo obtendo um parâmetro de string de consulta:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Você está pesquisando por: $keyword";
	// consultar um banco de dados ou algo assim com o $keyword
});
```

Aqui está um exemplo de talvez um formulário com um método POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Você enviou: $name, $email";
	// salvar em um banco de dados ou algo assim com o $name e $email
});
```

## Propriedades do Objeto de Requisição

O objeto de requisição fornece as seguintes propriedades:

- **body** - O corpo bruto da requisição HTTP
- **url** - A URL sendo requisitada
- **base** - O subdiretório pai da URL
- **method** - O método da requisição (GET, POST, PUT, DELETE)
- **referrer** - A URL de referência
- **ip** - Endereço IP do cliente
- **ajax** - Se a requisição é uma requisição AJAX
- **scheme** - O protocolo do servidor (http, https)
- **user_agent** - Informações do navegador
- **type** - O tipo de conteúdo
- **length** - O comprimento do conteúdo
- **query** - Parâmetros de string de consulta
- **data** - Dados POST ou dados JSON
- **cookies** - Dados de cookie
- **files** - Arquivos enviados
- **secure** - Se a conexão é segura
- **accept** - Parâmetros de aceitação HTTP
- **proxy_ip** - Endereço IP do proxy do cliente. Escaneia o array `$_SERVER` para `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` nessa ordem.
- **host** - O nome do host da requisição

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

## Corpo Bruto da Requisição

Para obter o corpo bruto da requisição HTTP, por exemplo, ao lidar com requisições PUT,
você pode fazer:

```php
$body = Flight::request()->getBody();
```

## Entrada JSON

Se você enviar uma requisição com o tipo `application/json` e os dados `{"id": 123}`
eles estarão disponíveis na propriedade `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Você pode acessar o array `$_GET` através da propriedade `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Você pode acessar o array `$_POST` através da propriedade `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Você pode acessar o array `$_COOKIE` através da propriedade `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Há um atalho disponível para acessar o array `$_SERVER` através do método `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Acessando Arquivos Enviados via `$_FILES`

Você pode acessar arquivos enviados através da propriedade `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Processando Envio de Arquivos

Você pode processar o envio de arquivos usando o framework com alguns métodos auxiliares. Basicamente 
resume-se a puxar os dados do arquivo da requisição e movê-los para um novo local.

```php
Flight::route('POST /upload', function(){
	// Se você tiver um campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/caminho/para/uploads/' . $uploadedFile->getClientFilename());
});
```

Se você tiver vários arquivos enviados, você pode percorrê-los:

```php
Flight::route('POST /upload', function(){
	// Se você tiver um campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/caminho/para/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de Segurança:** Sempre valide e sanitize a entrada do usuário, especialmente ao lidar com envios de arquivos. Sempre valide o tipo de extensões que você permitirá que sejam enviadas, mas você também deve validar os "bytes mágicos" do arquivo para garantir que ele é realmente do tipo de arquivo que o usuário afirma que é. Existem [artigos](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [e](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotecas](https://github.com/RikudouSage/MimeTypeDetector) disponíveis para ajudar com isso.

## Cabeçalhos da Requisição

Você pode acessar os cabeçalhos da requisição usando o método `getHeader()` ou `getHeaders()`:

```php

// Talvez você precise do cabeçalho Authorization
$host = Flight::request()->getHeader('Authorization');
// ou
$host = Flight::request()->header('Authorization');

// Se você precisar obter todos os cabeçalhos
$headers = Flight::request()->getHeaders();
// ou
$headers = Flight::request()->headers();
```

## Corpo da Requisição

Você pode acessar o corpo bruto da requisição usando o método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método da Requisição

Você pode acessar o método da requisição usando a propriedade `method` ou o método `getMethod()`:

```php
$method = Flight::request()->method; // na verdade chama getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** O método `getMethod()` primeiro puxa o método de `$_SERVER['REQUEST_METHOD']`, depois pode ser substituído 
por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` se existir ou `$_REQUEST['_method']` se existir.

## URLs da Requisição

Há alguns métodos auxiliares para juntar partes de uma URL para sua conveniência.

### URL Completa

Você pode acessar a URL completa da requisição usando o método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL Base

Você pode acessar a URL base usando o método `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Aviso, sem barra final.
// https://example.com
```

## Análise de Consulta

Você pode passar uma URL para o método `parseQuery()` para analisar a string de consulta em um array associativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```