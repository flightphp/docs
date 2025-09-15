# Pedidos

Flight encapsula o pedido HTTP em um único objeto, que pode ser acessado da seguinte forma:

```php
$request = Flight::request();
```

## Casos de Uso Típicos

Quando você está trabalhando com um pedido em uma aplicação web, geralmente você deseja extrair um cabeçalho, ou um parâmetro `$_GET` ou `$_POST`, ou talvez até o corpo bruto do pedido. Flight fornece uma interface simples para fazer todas essas coisas.

Aqui está um exemplo para obter um parâmetro da string de consulta:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// consultar um banco de dados ou algo mais com o $keyword
});
```

Aqui está um exemplo de talvez um formulário com método POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// salvar em um banco de dados ou algo mais com o $name e $email
});
```

## Propriedades do Objeto de Pedido

O objeto de pedido fornece as seguintes propriedades:

- **body** - O corpo bruto do pedido HTTP
- **url** - A URL sendo solicitada
- **base** - O subdiretório pai da URL
- **method** - O método do pedido (GET, POST, PUT, DELETE)
- **referrer** - A URL de referência
- **ip** - O endereço IP do cliente
- **ajax** - Se o pedido é um pedido AJAX
- **scheme** - O protocolo do servidor (http, https)
- **user_agent** - Informações do navegador
- **type** - O tipo de conteúdo
- **length** - O comprimento do conteúdo
- **query** - Parâmetros da string de consulta
- **data** - Dados de POST ou dados JSON
- **cookies** - Dados de cookies
- **files** - Arquivos enviados
- **secure** - Se a conexão é segura
- **accept** - Parâmetros HTTP accept
- **proxy_ip** - Endereço IP do proxy do cliente. Examina o array `$_SERVER` para `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` nessa ordem.
- **host** - O nome do host do pedido
- **servername** - O SERVER_NAME do `$_SERVER`

Você pode acessar as propriedades `query`, `data`, `cookies` e `files` como arrays ou objetos.

Então, para obter um parâmetro da string de consulta, você pode fazer:

```php
$id = Flight::request()->query['id'];
```

Ou você pode fazer:

```php
$id = Flight::request()->query->id;
```

## Corpo Bruto do Pedido

Para obter o corpo bruto do pedido HTTP, por exemplo ao lidar com pedidos PUT, você pode fazer:

```php
$body = Flight::request()->getBody();
```

## Entrada JSON

Se você enviar um pedido com o tipo `application/json` e os dados `{"id": 123}`, ele estará disponível na propriedade `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Você pode acessar o array `$_GET` via a propriedade `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Você pode acessar o array `$_POST` via a propriedade `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Você pode acessar o array `$_COOKIE` via a propriedade `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Há um atalho disponível para acessar o array `$_SERVER` via o método `getVar()`:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Acessando Arquivos Enviados via `$_FILES`

Você pode acessar arquivos enviados via a propriedade `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Processamento de Envios de Arquivos (v3.12.0)

Você pode processar envios de arquivos usando o framework com alguns métodos auxiliares. Basicamente, isso se resume a puxar os dados do arquivo do pedido e movê-lo para um novo local.

```php
Flight::route('POST /upload', function(){
	// Se você tivesse um campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Se você tiver vários arquivos enviados, você pode iterar por eles:

```php
Flight::route('POST /upload', function(){
	// Se você tivesse um campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de Segurança:** Sempre valide e sanitize a entrada do usuário, especialmente ao lidar com envios de arquivos. Sempre valide o tipo de extensões que você permitirá ser enviadas, mas você também deve validar os "bytes mágicos" do arquivo para garantir que ele seja realmente o tipo de arquivo que o usuário alega que é. Há [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) disponíveis para ajudar com isso.

## Cabeçalhos do Pedido

Você pode acessar cabeçalhos do pedido usando o método `getHeader()` ou `getHeaders()`:

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

## Corpo do Pedido

Você pode acessar o corpo bruto do pedido usando o método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método do Pedido

Você pode acessar o método do pedido usando a propriedade `method` ou o método `getMethod()`:

```php
$method = Flight::request()->method; // na verdade, chama getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** O método `getMethod()` primeiro puxa o método de `$_SERVER['REQUEST_METHOD']`, então ele pode ser sobrescrito por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` se existir ou `$_REQUEST['_method']` se existir.

## URLs do Pedido

Há alguns métodos auxiliares para juntar partes de uma URL para sua conveniência.

### URL Completa

Você pode acessar a URL completa do pedido usando o método `getFullUrl()`:

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