# Respostas

Flight ajuda a gerar parte dos cabeçalhos de resposta para você, mas você mantém a maior parte do controle sobre o que envia de volta ao usuário. Às vezes, você pode acessar o objeto `Response` diretamente, mas na maioria das vezes, você usará a instância do `Flight` para enviar uma resposta.

## Enviando uma Resposta Básica

Flight usa ob_start() para bufferizar a saída. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta ao usuário e o Flight capturará e enviará de volta ao usuário com os cabeçalhos apropriados.

```php
// Isso enviará "Hello, World!" para o navegador do usuário
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

Como alternativa, você pode chamar o método `write()` para adicionar ao corpo também.

```php
// Isso enviará "Hello, World!" para o navegador do usuário
Flight::route('/', function() {
	// verboso, mas às vezes é útil quando você precisa
	Flight::response()->write("Hello, World!");

	// se você quiser recuperar o corpo que você definiu neste ponto
	// você pode fazer assim
	$body = Flight::response()->getBody();
});
```

## Códigos de Status

Você pode definir o código de status da resposta usando o método `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Se você quiser obter o código de status atual, você pode usar o método `status` sem argumentos:

```php
Flight::response()->status(); // 200
```

## Definindo um Corpo de Resposta

Você pode definir o corpo da resposta usando o método `write`, no entanto, se você usar echo ou print, isso será capturado e enviado como o corpo da resposta via bufferização de saída.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// o mesmo que

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Limpando um Corpo de Resposta

Se você quiser limpar o corpo da resposta, você pode usar o método `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Executando uma Callback no Corpo da Resposta

Você pode executar uma callback no corpo da resposta usando o método `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Isso fará o gzip de todas as respostas para qualquer rota
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Você pode adicionar várias callbacks e elas serão executadas na ordem em que foram adicionadas. Como isso pode aceitar qualquer [callable](https://www.php.net/manual/en/language.types.callable.php), ele pode aceitar um array de classe `[ $class, 'method' ]`, um closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou um nome de função `'minify'` se você tiver uma função para minimizar seu código HTML, por exemplo.

**Nota:** As callbacks de rota não funcionarão se você estiver usando a opção de configuração `flight.v2.output_buffering`.

### Callback para Rota Específica

Se você quiser que isso se aplique apenas a uma rota específica, você pode adicionar a callback na rota em si:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Isso fará o gzip apenas da resposta para esta rota
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Opção de Middleware

Você também pode usar middleware para aplicar a callback a todas as rotas via middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Aplicar a callback aqui no objeto response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minimizar o corpo de alguma forma
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Definindo um Cabeçalho de Resposta

Você pode definir um cabeçalho, como o tipo de conteúdo da resposta, usando o método `header`:

```php
// Isso enviará "Hello, World!" para o navegador do usuário em texto simples
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight fornece suporte para enviar respostas JSON e JSONP. Para enviar uma resposta JSON, você passa alguns dados para serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

> **Nota:** Por padrão, Flight enviará um cabeçalho `Content-Type: application/json` com a resposta. Ele também usará as constantes `JSON_THROW_ON_ERROR` e `JSON_UNESCAPED_SLASHES` ao codificar o JSON.

### JSON com Código de Status

Você também pode passar um código de status como o segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON com Impressão Bonita

Você também pode passar um argumento na última posição para habilitar a impressão bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Se você estiver alterando opções passadas para `Flight::json()` e quiser uma sintaxe mais simples, você pode remapear o método JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// E agora pode ser usado assim
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON e Parar a Execução (v3.10.0)

Se você quiser enviar uma resposta JSON e parar a execução, você pode usar o método `jsonHalt()`. Isso é útil para casos em que você está verificando algum tipo de autorização e, se o usuário não estiver autorizado, pode enviar uma resposta JSON imediatamente, limpar o conteúdo do corpo existente e parar a execução.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifique se o usuário está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// Continue com o resto da rota
});
```

Antes da v3.10.0, você teria que fazer algo assim:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifique se o usuário está autorizado
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Continue com o resto da rota
});
```

### JSONP

Para solicitações JSONP, você pode opcionalmente passar o nome do parâmetro de consulta que está usando para definir sua função de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Então, ao fazer uma solicitação GET usando `?q=my_func`, você deve receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será padronizado para `jsonp`.

## Redirecionar para outra URL

Você pode redirecionar a solicitação atual usando o método `redirect()` e passando uma nova URL:

```php
Flight::redirect('/new/location');
```

Por padrão, Flight envia um código de status HTTP 303 ("See Other"). Você pode opcionalmente definir um código personalizado:

```php
Flight::redirect('/new/location', 401);
```

## Parando

Você pode parar o framework em qualquer ponto chamando o método `halt`:

```php
Flight::halt();
```

Você também pode especificar um código de status HTTP opcional e uma mensagem:

```php
Flight::halt(200, 'Be right back...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até aquele ponto. Se você quiser parar o framework e emitir a resposta atual, use o método `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Nota:** `Flight::stop()` tem alguns comportamentos estranhos, como emitir a resposta, mas continuar executando seu script. Você pode usar `exit` ou `return` após chamar `Flight::stop()` para impedir a execução adicional, mas é geralmente recomendado usar `Flight::halt()`.

## Limpando Dados de Resposta

Você pode limpar o corpo e os cabeçalhos da resposta usando o método `clear()`. Isso limpará quaisquer cabeçalhos atribuídos à resposta, limpará o corpo da resposta e definirá o código de status como `200`.

```php
Flight::response()->clear();
```

### Limpando Apenas o Corpo da Resposta

Se você quiser limpar apenas o corpo da resposta, você pode usar o método `clearBody()`:

```php
// Isso ainda manterá quaisquer cabeçalhos definidos no objeto response().
Flight::response()->clearBody();
```

## Cache HTTP

Flight fornece suporte integrado para cache no nível HTTP. Se a condição de cache for atendida, Flight retornará uma resposta HTTP `304 Not Modified`. Na próxima vez que o cliente solicitar o mesmo recurso, eles serão incentivados a usar sua versão local em cache.

### Cache no Nível de Rota

Se você quiser cachear toda a sua resposta, você pode usar o método `cache()` e passar o tempo para cachear.

```php
// Isso cacheará a resposta por 5 minutos
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Como alternativa, você pode usar uma string que você passaria
// para o método strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Você pode usar o método `lastModified` e passar um timestamp UNIX para definir a data e hora em que uma página foi modificada pela última vez. O cliente continuará usando seu cache até que o valor da última modificação seja alterado.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

O cache `ETag` é semelhante ao `Last-Modified`, exceto que você pode especificar qualquer ID que quiser para o recurso:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Lembre-se de que chamar `lastModified` ou `etag` definirá e verificará o valor do cache. Se o valor do cache for o mesmo entre as solicitações, Flight enviará imediatamente uma resposta `HTTP 304` e parará o processamento.

## Baixar um Arquivo (v3.12.0)

Há um método auxiliar para baixar um arquivo. Você pode usar o método `download` e passar o caminho.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```