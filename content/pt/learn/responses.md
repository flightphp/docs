# Respostas

Flight ajuda a gerar parte dos cabeçalhos de resposta para você, mas você possui a maior parte do controle sobre o que envia de volta ao usuário. Às vezes, você pode acessar o objeto `Response` diretamente, mas na maioria das vezes você usará a instância `Flight` para enviar uma resposta.

## Enviando uma Resposta Básica

Flight usa ob_start() para armazenar em buffer a saída. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta ao usuário e o Flight irá capturá-la e enviá-la de volta ao usuário com os cabeçalhos apropriados.

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
	// detalhado, mas muitas vezes faz o trabalho quando você precisa
	Flight::response()->write("Olá, Mundo!");

	// se você quiser recuperar o corpo que definiu até este ponto
	// você pode fazer isso assim
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

Você pode definir o corpo da resposta usando o método `write`, no entanto, se você echo ou print qualquer coisa, 
isso será capturado e enviado como o corpo da resposta através do buffering de saída.

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

Você pode adicionar múltiplos callbacks e eles serão executados na ordem em que foram adicionados. Como isso pode aceitar qualquer [callable](https://www.php.net/manual/en/language.types.callable.php), pode aceitar um array de classe `[ $class, 'method' ]`, uma closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou um nome de função `'minify'` se você tiver uma função para minificar seu código html, por exemplo.

**Nota:** Callbacks de rota não funcionarão se você estiver usando a opção de configuração `flight.v2.output_buffering`.

### Callback de Rota Específica

Se você quiser que isso se aplique apenas a uma rota específica, pode adicionar o callback na própria rota:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Isso irá gzip somente a resposta para esta rota
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Opção de Middleware

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
		// minifique o corpo de alguma forma
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

Você pode definir um cabeçalho como tipo de conteúdo da resposta usando o método `header`:

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

Flight fornece suporte para o envio de respostas JSON e JSONP. Para enviar uma resposta JSON você
passa alguns dados para serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

> **Nota:** Por padrão, o Flight enviará um cabeçalho `Content-Type: application/json` com a resposta. Ele também usará as constantes `JSON_THROW_ON_ERROR` e `JSON_UNESCAPED_SLASHES` ao codificar o JSON.

### JSON com Código de Status

Você também pode passar um código de status como segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON com Impressão Bonita

Você também pode passar um argumento para a última posição para habilitar a impressão bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Se você estiver mudando as opções passadas para `Flight::json()` e quiser uma sintaxe mais simples, você pode 
apenas reconfigurar o método JSON:

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
o usuário não estiver autorizado, você pode enviar uma resposta JSON imediatamente, limpar o corpo
existente e parar a execução.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifique se o usuário está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Não Autorizado'], 401);
	}

	// Continue com o restante da rota
});
```

Antes da v3.10.0, você teria que fazer algo assim:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifique se o usuário está autorizado
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Não Autorizado']));
	}

	// Continue com o restante da rota
});
```

### JSONP

Para solicitações JSONP você pode, opcionalmente, passar o nome do parâmetro de consulta que você está
usando para definir sua função de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Assim, ao fazer uma solicitação GET usando `?q=my_func`, você deverá receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será definido como `jsonp`.

## Redirecionar para outra URL

Você pode redirecionar a solicitação atual usando o método `redirect()` e passar
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

Você também pode especificar um código e mensagem de status `HTTP` opcionais:

```php
Flight::halt(200, 'Be right back...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até aquele ponto. Se você quiser parar
o framework e exibir a resposta atual, use o método `stop`:

```php
Flight::stop();
```

## Limpando Dados da Resposta

Você pode limpar o corpo da resposta e os cabeçalhos usando o método `clear()`. Isso limpará
quaisquer cabeçalhos atribuídos à resposta, limpará o corpo da resposta e definirá o código de status como `200`.

```php
Flight::response()->clear();
```

### Limpando Apenas o Corpo da Resposta

Se você apenas deseja limpar o corpo da resposta, pode usar o método `clearBody()`:

```php
// Isso ainda manterá quaisquer cabeçalhos definidos no objeto response().
Flight::response()->clearBody();
```

## Cache HTTP

Flight fornece suporte embutido para caching a nível HTTP. Se a condição de cache
for atendida, o Flight retornará uma resposta HTTP `304 Not Modified`. Na próxima vez que o
cliente solicitar o mesmo recurso, ele será solicitado a usar sua versão armazenada em cache localmente.

### Cache a Nível de Rota

Se você quiser armazenar em cache toda a sua resposta, pode usar o método `cache()` e passar o tempo para cache.

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

### Last-Modified

Você pode usar o método `lastModified` e passar um timestamp UNIX para definir a data
e a hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até
que o valor da última modificação seja alterado.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

### ETag

O cache `ETag` é semelhante ao `Last-Modified`, exceto que você pode especificar qualquer id que
quiser para o recurso:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Lembre-se de que chamar `lastModified` ou `etag` definirá e verificará ambos os valores de
cache. Se o valor de cache for o mesmo entre as solicitações, o Flight enviará imediatamente
uma resposta `HTTP 304` e parará o processamento.

## Fazer o Download de um Arquivo (v3.12.0)

Há um método auxiliar para fazer o download de um arquivo. Você pode usar o método `download` e passar o caminho.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```