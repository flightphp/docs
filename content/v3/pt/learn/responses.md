# Respostas

## Visão Geral

Flight ajuda a gerar parte dos cabeçalhos de resposta para você, mas você tem a maior parte do controle sobre o que envia de volta ao usuário. Na maioria das vezes, você acessará o objeto `response()` diretamente, mas Flight tem alguns métodos auxiliares para definir alguns dos cabeçalhos de resposta para você.

## Entendendo

Após o usuário enviar sua [request](/learn/requests) para sua aplicação, você precisa gerar uma resposta apropriada para eles. Eles enviaram informações como a linguagem que preferem, se podem lidar com certos tipos de compressão, seu agente de usuário, etc., e após processar tudo, é hora de enviar de volta uma resposta apropriada. Isso pode ser definindo cabeçalhos, exibindo um corpo de HTML ou JSON para eles, ou redirecionando para uma página.

## Uso Básico

### Enviando um Corpo de Resposta

Flight usa `ob_start()` para bufferizar a saída. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta ao usuário e Flight a capturará e enviará de volta ao usuário com os cabeçalhos apropriados.

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
	// verboso, mas faz o trabalho às vezes quando você precisa
	Flight::response()->write("Hello, World!");

	// se você quiser recuperar o corpo que definiu neste ponto
	// você pode fazer assim
	$body = Flight::response()->getBody();
});
```

### JSON

Flight fornece suporte para enviar respostas JSON e JSONP. Para enviar uma resposta JSON, você
passa alguns dados para serem codificados em JSON:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// de alguma forma, puxe seus usuários de um banco de dados, por exemplo
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* mais usuários */ ]
```

> **Nota:** Por padrão, Flight enviará um cabeçalho `Content-Type: application/json` com a resposta. Ele também usará as flags `JSON_THROW_ON_ERROR` e `JSON_UNESCAPED_SLASHES` ao codificar o JSON.

#### JSON com Código de Status

Você também pode passar um código de status como o segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

#### JSON com Impressão Bonita

Você também pode passar um argumento na última posição para ativar a impressão bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Alterando a Ordem dos Argumentos JSON

`Flight::json()` é um método muito legado, mas o objetivo do Flight é manter a compatibilidade com versões anteriores
para projetos. Na verdade, é muito simples se você quiser redefinir a ordem dos argumentos para usar uma sintaxe mais simples,
você pode apenas remapear o método JSON [como qualquer outro método do Flight](/learn/extending):

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// agora você não precisa de `true, 'utf-8'` ao usar o método json()!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// E agora pode ser usado assim
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON e Parando a Execução

_v3.10.0_

Se você quiser enviar uma resposta JSON e parar a execução, você pode usar o método `jsonHalt()`.
Isso é útil para casos em que você está verificando algum tipo de autorização e se
o usuário não estiver autorizado, você pode enviar uma resposta JSON imediatamente, limpar o conteúdo do corpo existente
e parar a execução.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifique se o usuário está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// sem exit; necessário aqui.
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

O caso de uso acima provavelmente não é comum, no entanto, poderia ser mais comum se usado em um [middleware](/learn/middleware).

### Executando um Callback no Corpo da Resposta

Você pode executar um callback no corpo da resposta usando o método `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Isso fará gzip de todas as respostas para qualquer rota
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Você pode adicionar múltiplos callbacks e eles serão executados na ordem em que foram adicionados. Como isso pode aceitar qualquer [callable](https://www.php.net/manual/en/language.types.callable.php), pode aceitar um array de classe `[ $class, 'method' ]`, uma closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, ou um nome de função `'minify'` se você tivesse uma função para minificar seu código HTML, por exemplo.

**Nota:** Os callbacks de rota não funcionarão se você estiver usando a opção de configuração `flight.v2.output_buffering`.

#### Callback de Rota Específica

Se você quisesse que isso se aplicasse apenas a uma rota específica, poderia adicionar o callback na própria rota:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Isso fará gzip apenas da resposta desta rota
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Opção de Middleware

Você também pode usar [middleware](/learn/middleware) para aplicar o callback a todas as rotas via middleware:

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

### Códigos de Status

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

Se você quiser obter o código de status atual, pode usar o método `status` sem argumentos:

```php
Flight::response()->status(); // 200
```

### Definindo um Cabeçalho de Resposta

Você pode definir um cabeçalho, como o tipo de conteúdo da resposta, usando o método `header`:

```php
// Isso enviará "Hello, World!" para o navegador do usuário em texto plano
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### Redirecionamento

Você pode redirecionar a solicitação atual usando o método `redirect()` e passando
uma nova URL:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // isso é necessário para que a funcionalidade abaixo não execute
	}

	// adicione o novo usuário...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Nota:** Por padrão, Flight envia um código de status HTTP 303 ("See Other"). Você pode opcionalmente definir um
código personalizado:

```php
Flight::redirect('/new/location', 301); // permanente
```

### Parando a Execução da Rota

Você pode parar o framework e sair imediatamente em qualquer ponto chamando o método `halt`:

```php
Flight::halt();
```

Você também pode especificar um código de status `HTTP` opcional e mensagem:

```php
Flight::halt(200, 'Be right back...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até aquele ponto e parará toda a execução.
Se você quiser parar o framework e exibir a resposta atual, use o método `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Nota:** `Flight::stop()` tem um comportamento estranho, como exibir a resposta, mas continuar executando seu script, o que pode não ser o que você deseja. Você pode usar `exit` ou `return` após chamar `Flight::stop()` para evitar execução adicional, mas geralmente é recomendado usar `Flight::halt()`.

Isso salvará a chave e o valor do cabeçalho no objeto de resposta. No final do ciclo de vida da solicitação,
ele construirá os cabeçalhos e enviará uma resposta.

## Uso Avançado

### Enviando um Cabeçalho Imediatamente

Pode haver momentos em que você precisa fazer algo personalizado com o cabeçalho e precisa enviar o cabeçalho
na própria linha de código com a qual está trabalhando. Se você estiver definindo uma [rota transmitida](/learn/routing),
isso é o que você precisaria. Isso é alcançável através de `response()->setRealHeader()`.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

Para solicitações JSONP, você pode opcionalmente passar o nome do parâmetro de consulta que está
usando para definir sua função de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Então, ao fazer uma solicitação GET usando `?q=my_func`, você deve receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele usará `jsonp` por padrão.

> **Nota:** Se você ainda estiver usando solicitações JSONP em 2025 e além, entre no chat e nos diga por quê! Adoramos ouvir boas histórias de batalha/horror!

### Limpando Dados de Resposta

Você pode limpar o corpo da resposta e os cabeçalhos usando o método `clear()`. Isso limpará
qualquer cabeçalho atribuído à resposta, limpará o corpo da resposta e definirá o código de status como `200`.

```php
Flight::response()->clear();
```

#### Limpando Apenas o Corpo da Resposta

Se você quiser apenas limpar o corpo da resposta, pode usar o método `clearBody()`:

```php
// Isso ainda manterá quaisquer cabeçalhos definidos no objeto response().
Flight::response()->clearBody();
```

### Cache HTTP

Flight fornece suporte integrado para cache no nível HTTP. Se a condição de cache
for atendida, Flight retornará uma resposta HTTP `304 Not Modified`. Na próxima vez que o
cliente solicitar o mesmo recurso, eles serão solicitados a usar sua versão em cache local.

#### Cache no Nível de Rota

Se você quiser cachear toda a sua resposta, pode usar o método `cache()` e passar o tempo para cachear.

```php

// Isso cacheará a resposta por 5 minutos
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternativamente, você pode usar uma string que passaria
// para o método strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

Você pode usar o método `lastModified` e passar um timestamp UNIX para definir a data
e hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até
que o valor de última modificação seja alterado.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

O cache `ETag` é semelhante ao `Last-Modified`, exceto que você pode especificar qualquer ID que
desejar para o recurso:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Tenha em mente que chamar `lastModified` ou `etag` definirá e verificará o
valor do cache. Se o valor do cache for o mesmo entre as solicitações, Flight enviará imediatamente
uma resposta `HTTP 304` e parará o processamento.

### Baixar um Arquivo

_v3.12.0_

Há um método auxiliar para transmitir um arquivo para o usuário final. Você pode usar o método `download` e passar o caminho.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## Veja Também
- [Routing](/learn/routing) - Como mapear rotas para controladores e renderizar visualizações.
- [Requests](/learn/requests) - Entendendo como lidar com solicitações de entrada.
- [Middleware](/learn/middleware) - Usando middleware com rotas para autenticação, logging, etc.
- [Why a Framework?](/learn/why-frameworks) - Entendendo os benefícios de usar um framework como Flight.
- [Extending](/learn/extending) - Como estender Flight com sua própria funcionalidade.

## Solução de Problemas
- Se você estiver com problemas com redirecionamentos não funcionando, certifique-se de adicionar um `return;` ao método.
- `stop()` e `halt()` não são a mesma coisa. `halt()` parará a execução imediatamente, enquanto `stop()` permitirá que a execução continue.

## Changelog
- v3.12.0 - Adicionado método auxiliar downloadFile.
- v3.10.0 - Adicionado `jsonHalt`.
- v1.0 - Lançamento inicial.