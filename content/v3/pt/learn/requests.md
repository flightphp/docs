# Requests

## Visão Geral

Flight encapsula a requisição HTTP em um único objeto, que pode ser acessado fazendo:

```php
$request = Flight::request();
```

## Entendendo

Requisições HTTP são um dos aspectos principais para entender sobre o ciclo de vida HTTP. Um usuário realiza uma ação em um navegador web ou em um cliente HTTP, e eles enviam uma série de cabeçalhos, corpo, URL, etc para o seu projeto. Você pode capturar esses cabeçalhos (a linguagem do navegador, que tipo de compressão eles podem lidar, o agente do usuário, etc) e capturar o corpo e a URL que é enviada para a sua aplicação Flight. Essas requisições são essenciais para que sua app entenda o que fazer em seguida.

## Uso Básico

PHP tem vários super globais incluindo `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` e `$_COOKIE`. Flight abstrai esses para [Collections](/learn/collections) práticas. Você pode acessar as propriedades `query`, `data`, `cookies` e `files` como arrays ou objetos.

> **Nota:** É **ALTAMENTE** desaconselhado usar esses super globais no seu projeto e eles devem ser referenciados através do objeto `request()`.

> **Nota:** Não há abstração disponível para `$_ENV`.

### `$_GET`

Você pode acessar o array `$_GET` via a propriedade `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// ou
	$keyword = Flight::request()->query->keyword;
	echo "Você está procurando por: $keyword";
	// consultar um banco de dados ou algo mais com o $keyword
});
```

### `$_POST`

Você pode acessar o array `$_POST` via a propriedade `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// ou
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Você enviou: $name, $email";
	// salvar em um banco de dados ou algo mais com o $name e $email
});
```

### `$_COOKIE`

Você pode acessar o array `$_COOKIE` via a propriedade `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// ou
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// verificar se foi realmente salvo ou não e se foi, fazer login automático
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Para ajuda em definir novos valores de cookies, veja [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Há um atalho disponível para acessar o array `$_SERVER` via o método `getVar()`:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Você pode acessar arquivos enviados via a propriedade `files`:

```php
// acesso raw à propriedade $_FILES. Veja abaixo para a abordagem recomendada
$uploadedFile = Flight::request()->files['myFile']; 
// ou
$uploadedFile = Flight::request()->files->myFile;
```

Veja [Uploaded File Handler](/learn/uploaded-file) para mais informações.

#### Processando Uploads de Arquivos

_v3.12.0_

Você pode processar uploads de arquivos usando o framework com alguns métodos auxiliares. Basicamente, isso se resume a puxar os dados do arquivo da requisição e movê-lo para um novo local.

```php
Flight::route('POST /upload', function(){
	// Se você tivesse um campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Se você tiver múltiplos arquivos enviados, você pode iterar através deles:

```php
Flight::route('POST /upload', function(){
	// Se você tivesse um campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de Segurança:** Sempre valide e sanitize a entrada do usuário, especialmente ao lidar com uploads de arquivos. Sempre valide o tipo de extensões que você permitirá serem enviadas, mas você também deve validar os "magic bytes" do arquivo para garantir que é realmente o tipo de arquivo que o usuário afirma ser. Há [artigos](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [e](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotecas](https://github.com/RikudouSage/MimeTypeDetector) disponíveis para ajudar com isso.

### Corpo da Requisição

Para obter o corpo raw da requisição HTTP, por exemplo ao lidar com requisições POST/PUT, você pode fazer:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// fazer algo com o XML que foi enviado.
});
```

### Corpo JSON

Se você receber uma requisição com o tipo de conteúdo `application/json` e os dados de exemplo `{"id": 123}`, ele estará disponível na propriedade `data`:

```php
$id = Flight::request()->data->id;
```

### Cabeçalhos da Requisição

Você pode acessar os cabeçalhos da requisição usando o método `getHeader()` ou `getHeaders()`:

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

### Método da Requisição

Você pode acessar o método da requisição usando a propriedade `method` ou o método `getMethod()`:

```php
$method = Flight::request()->method; // na verdade populado pelo getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** O método `getMethod()` primeiro puxa o método de `$_SERVER['REQUEST_METHOD']`, então ele pode ser sobrescrito por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` se existir ou `$_REQUEST['_method']` se existir.

## Propriedades do Objeto Requisição

O objeto de requisição fornece as seguintes propriedades:

- **body** - O corpo raw da requisição HTTP
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
- **query** - Parâmetros da string de consulta
- **data** - Dados de post ou dados JSON
- **cookies** - Dados de cookies
- **files** - Arquivos enviados
- **secure** - Se a conexão é segura
- **accept** - Parâmetros HTTP accept
- **proxy_ip** - Endereço IP proxy do cliente. Varre o array `$_SERVER` por `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` nessa ordem.
- **host** - O nome do host da requisição
- **servername** - O SERVER_NAME de `$_SERVER`

## Métodos Auxiliares de URL

Há alguns métodos auxiliares para montar partes de uma URL para sua conveniência.

### URL Completa

Você pode acessar a URL completa da requisição usando o método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL Base

Você pode acessar a URL base usando o método `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Note, sem barra final.
```

## Análise de Consulta

Você pode passar uma URL para o método `parseQuery()` para analisar a string de consulta em um array associativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Veja Também
- [Routing](/learn/routing) - Veja como mapear rotas para controladores e renderizar views.
- [Responses](/learn/responses) - Como personalizar respostas HTTP.
- [Why a Framework?](/learn/why-frameworks) - Como as requisições se encaixam no quadro geral.
- [Collections](/learn/collections) - Trabalhando com coleções de dados.
- [Uploaded File Handler](/learn/uploaded-file) - Lidando com uploads de arquivos.

## Solução de Problemas
- `request()->ip` e `request()->proxy_ip` podem ser diferentes se o seu servidor web estiver atrás de um proxy, balanceador de carga, etc. 

## Changelog
- v3.12.0 - Adicionada capacidade de lidar com uploads de arquivos através do objeto de requisição.
- v1.0 - Lançamento inicial.