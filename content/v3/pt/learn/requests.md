# Solicitações

Flight encapsula a solicitação HTTP em um único objeto, que pode ser
acessado da seguinte forma:

```php
$request = Flight::request();
```

## Casos de Uso Típicos

Quando você está trabalhando com uma solicitação em uma aplicação web, geralmente você
quer extrair um cabeçalho, ou um parâmetro `$_GET` ou `$_POST`, ou talvez
até o corpo bruto da solicitação. Flight fornece uma interface simples para fazer tudo isso.

Aqui está um exemplo de obtenção de um parâmetro da string de consulta:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// consulte um banco de dados ou algo mais com o $keyword
});
```

Aqui está um exemplo de talvez um formulário com método POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// salve em um banco de dados ou algo mais com o $name e $email
});
```

## Propriedades do Objeto de Solicitação

O objeto de solicitação fornece as seguintes propriedades:

- **body** - O corpo bruto da solicitação HTTP
- **url** - A URL sendo solicitada
- **base** - O subdiretório pai da URL
- **method** - O método da solicitação (GET, POST, PUT, DELETE)
- **referrer** - A URL de referência
- **ip** - O endereço IP do cliente
- **ajax** - Se a solicitação é uma solicitação AJAX
- **scheme** - O protocolo do servidor (http, https)
- **user_agent** - Informações do navegador
- **type** - O tipo de conteúdo
- **length** - O comprimento do conteúdo
- **query** - Parâmetros da string de consulta
- **data** - Dados de POST ou JSON
- **cookies** - Dados de cookies
- **files** - Arquivos enviados
- **secure** - Se a conexão é segura
- **accept** - Parâmetros de aceitação HTTP
- **proxy_ip** - Endereço IP do proxy do cliente. Verifica o array `$_SERVER` para `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` nessa ordem.
- **host** - O nome do host da solicitação
- **servername** - O SERVER_NAME do `$_SERVER`

Você pode acessar as propriedades `query`, `data`, `cookies` e `files`
como arrays ou objetos.

Então, para obter um parâmetro da string de consulta, você pode fazer:

```php
$id = Flight::request()->query['id'];
```

Ou você pode fazer:

```php
$id = Flight::request()->query->id;
```

## Corpo Bruto da Solicitação

Para obter o corpo bruto da solicitação HTTP, por exemplo ao lidar com solicitações PUT,
você pode fazer:

```php
$body = Flight::request()->getBody();
```

## Entrada JSON

Se você enviar uma solicitação com o tipo `application/json` e os dados `{"id": 123}`,
ela estará disponível na propriedade `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Você pode acessar o array `$_GET` pela propriedade `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Você pode acessar o array `$_POST` pela propriedade `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Você pode acessar o array `$_COOKIE` pela propriedade `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Há um atalho disponível para acessar o array `$_SERVER` pelo método `getVar()`:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Acessando Arquivos Enviados via `$_FILES`

Você pode acessar arquivos enviados pela propriedade `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Processamento de Envio de Arquivos (v3.12.0)

Você pode processar envios de arquivos usando o framework com alguns métodos auxiliares. Basicamente, 
isso se resume a extrair os dados do arquivo da solicitação e movê-lo para um novo local.

```php
Flight::route('POST /upload', function(){
	// Se você tivesse um campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Se você tiver vários arquivos enviados, você pode percorrê-los:

```php
Flight::route('POST /upload', function(){
	// Se você tivesse um campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de Segurança:** Sempre valide e sanitize a entrada do usuário, especialmente ao lidar com envios de arquivos. Sempre valide o tipo de extensões que você permitirá ser enviadas, mas você também deve validar os "bytes mágicos" do arquivo para garantir que ele seja realmente o tipo de arquivo que o usuário alega ser. Há [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) disponíveis para ajudar com isso.

## Cabeçalhos da Solicitação

Você pode acessar cabeçalhos da solicitação usando o método `getHeader()` ou `getHeaders()`:

```php
// Talvez você precise do cabeçalho Authorization
$host = Flight::request()->getHeader('Authorization');
// ou
$host = Flight::request()->header('Authorization');

// Se você precisar pegar todos os cabeçalhos
$headers = Flight::request()->getHeaders();
// ou
$headers = Flight::request()->headers();
```

## Corpo da Solicitação

Você pode acessar o corpo bruto da solicitação usando o método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método da Solicitação

Você pode acessar o método da solicitação usando a propriedade `method` ou o método `getMethod()`:

```php
$method = Flight::request()->method; // na verdade, chama getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** O método `getMethod()` primeiro obtém o método de `$_SERVER['REQUEST_METHOD']`, então ele pode ser substituído 
por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` se existir ou `$_REQUEST['_method']` se existir.

## URLs da Solicitação

Há alguns métodos auxiliares para juntar partes de uma URL para sua conveniência.

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