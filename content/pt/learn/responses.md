# Respostas

Flight ajuda a gerar parte dos cabeçalhos de resposta para você, mas você tem a maior parte do controle sobre o que envia de volta ao usuário. Às vezes, você pode acessar o objeto `Response` diretamente, mas na maioria das vezes você usará a instância de `Flight` para enviar uma resposta.

## Enviando uma Resposta Básica

Flight usa ob_start() para armazenar a saída em buffer. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta ao usuário e o Flight irá capturá-la e enviá-la de volta ao usuário com os cabeçalhos apropriados.

```php

// Isso enviará "Olá, Mundo!" para o navegador do usuário
Flight::route('/', function() {
	echo "Olá, Mundo!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Olá, Mundo!
```

Como alternativa, você pode chamar o método `write()` para adicionar ao corpo também.

```php

// Isso enviará "Olá, Mundo!" para o navegador do usuário
Flight::route('/', function() {
	// detalhado, mas resolve o trabalho às vezes quando você precisa
	Flight::response()->write("Olá, Mundo!");

	// se você quiser recuperar o corpo que definiu neste ponto
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
		echo "Olá, Mundo!";
	} else {
		Flight::response()->status(403);
		echo "Proibido";
	}
});
```

Se você quiser obter o código de status atual, pode usar o método `status` sem argumentos:

```php
Flight::response()->status(); // 200
```

## Definindo um Corpo de Resposta

Você pode definir o corpo da resposta usando o método `write`, no entanto, se você ecoar ou imprimir qualquer coisa, 
isso será capturado e enviado como o corpo da resposta via buffer de saída.

```php
Flight::route('/', function() {
	Flight::response()->write("Olá, Mundo!");
});

// mesmo que

Flight::route('/', function() {
	echo "Olá, Mundo!";
});
```

### Limpando um Corpo de Resposta

Se você quiser limpar o corpo da resposta, pode usar o método `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Olá, Mundo!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Executando um Callback no Corpo da Resposta

Você pode executar um callback no corpo da resposta usando o método `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Isso irá gzip todas as respostas para qualquer rota
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Você pode adicionar vários callbacks e eles serão executados na ordem em que foram adicionados. Como isso pode aceitar qualquer [callable](https://www.php.net/manual/en/language.types.callable.php), pode aceitar um array de classe `[ $class, 'method' ]`, um closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou um nome de função `'minify'` se você tiver uma função para minificar seu código html, por exemplo.

**Nota:** callbacks de rota não funcionarão se você estiver usando a opção de configuração `flight.v2.output_buffering`.

### Callback de Rota Específica

Se você quiser que isso se aplique apenas a uma rota específica, você pode adicionar o callback na própria rota:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Isso irá gzip apenas a resposta para esta rota
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Opção Middleware

Você também pode usar middleware para aplicar o callback a todas as rotas via middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Aplique o callback aqui no objeto response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minificar o corpo de alguma forma
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

Você pode definir um cabeçalho como o tipo de conteúdo da resposta usando o método `header`:

```php

// Isso enviará "Olá, Mundo!" para o navegador do usuário em texto simples
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Olá, Mundo!";
});
```

## JSON

Flight fornece suporte para enviar respostas JSON e JSONP. Para enviar uma resposta JSON você
passa alguns dados para serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

> **Nota:** Por padrão, o Flight enviará um cabeçalho `Content-Type: application/json` com a resposta. Ele também usará as constantes `JSON_THROW_ON_ERROR` e `JSON_UNESCAPED_SLASHES` ao codificar o JSON.

### JSON com Código de Status

Você também pode passar um código de status como o segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON com Impressão Bonita

Você também pode passar um argumento para a última posição para habilitar a impressão bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Se você estiver mudando opções passadas para `Flight::json()` e quiser uma sintaxe mais simples, você pode 
apenas remapear o método JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// E agora pode ser usado assim
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON e Parar Execução (v3.10.0)

Se você quiser enviar uma resposta JSON e parar a execução, pode usar o método `jsonHalt`.
Isso é útil para casos em que você está verificando talvez algum tipo de autorização e se
o usuário não estiver autorizado, você pode enviar imediatamente uma resposta JSON, limpar o corpo existente
e parar a execução.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifica se o usuário está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Não Autorizado'], 401);
	}

	// Continuar com o resto da rota
});
```

Antes da v3.10.0, você teria que fazer algo como isto:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifica se o usuário está autorizado
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Não Autorizado']));
	}

	// Continuar com o resto da rota
});
```

### JSONP

Para requisições JSONP, você pode opcionalmente passar o nome do parâmetro de consulta que está
usando para definir sua função de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Assim, ao fazer uma requisição GET usando `?q=my_func`, você deve receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será padrão para `jsonp`.

## Redirecionar para outra URL

Você pode redirecionar a requisição atual usando o método `redirect()` e passando
uma nova URL:

```php
Flight::redirect('/new/location');
```

Por padrão, o Flight envia um código de status HTTP 303 ("Veja Outro"). Você pode opcionalmente definir um
código personalizado:

```php
Flight::redirect('/new/location', 401);
```

## Parando

Você pode parar o framework a qualquer momento chamando o método `halt`:

```php
Flight::halt();
```

Você também pode especificar um código de status e mensagem HTTP opcionais:

```php
Flight::halt(200, 'Voltamos já...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até aquele ponto. Se você quiser parar
o framework e devolver a resposta atual, use o método `stop`:

```php
Flight::stop();
```

## Limpando Dados da Resposta

Você pode limpar o corpo e os cabeçalhos da resposta usando o método `clear()`. Isso limpará
quaisquer cabeçalhos atribuídos à resposta, limpará o corpo da resposta e definirá o código de status como `200`.

```php
Flight::response()->clear();
```

### Limpando Apenas o Corpo da Resposta

Se você quiser apenas limpar o corpo da resposta, pode usar o método `clearBody()`:

```php
// Isso ainda manterá quaisquer cabeçalhos definidos no objeto response().
Flight::response()->clearBody();
```

## Cache HTTP

Flight fornece suporte embutido para cache em nível HTTP. Se a condição de cache
for atendida, o Flight retornará uma resposta HTTP `304 Não Modificado`. Da próxima vez que o
cliente solicitar o mesmo recurso, ele será solicitado a usar sua versão em cache local.

### Cache em Nível de Rota

Se você quiser armazenar em cache toda a sua resposta, pode usar o método `cache()` e passar o tempo para armazenar em cache.

```php

// Isso armazenará em cache a resposta por 5 minutos
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Este conteúdo será armazenado em cache.';
});

// Alternativamente, você pode usar uma string que você passaria
// para o método strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Este conteúdo será armazenado em cache.';
});
```

### Última-Modificação

Você pode usar o método `lastModified` e passar um timestamp UNIX para definir a data
e a hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até que
o valor da última modificação seja alterado.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

### ETag

O cache `ETag` é semelhante ao `Última-Modificação`, exceto que você pode especificar qualquer id que quiser para o recurso:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Lembre-se de que chamar `lastModified` ou `etag` definirá e verificará o
valor do cache. Se o valor do cache for o mesmo entre as requisições, o Flight enviará imediatamente
uma resposta `HTTP 304` e parará o processamento.

## Baixar um Arquivo (v3.12.0)

Há um método auxiliar para baixar um arquivo. Você pode usar o método `download` e passar o caminho.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```