# Respostas

O Flight ajuda a gerar parte dos cabeçalhos de respostas para você, mas você controla a maior parte do que envia de volta para o usuário. Às vezes você pode acessar o objeto `Response` diretamente, mas na maioria das vezes você usará a instância do `Flight` para enviar uma resposta.

## Enviando uma Resposta Básica

O Flight usa ob_start() para armazenar em buffer a saída. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta para o usuário e o Flight irá capturá-la e enviá-la de volta para o usuário com os cabeçalhos apropriados.

```php

// Isso enviará "Olá, Mundo!" para o navegador do usuário
Flight::route('/', function() {
	echo "Olá, Mundo!";
});

// HTTP/1.1 200 OK
// Tipo de Conteúdo: text/html
//
// Olá, Mundo!
```

Como alternativa, você pode chamar o método `write()` para adicionar ao corpo também.

```php

// Isso enviará "Olá, Mundo!" para o navegador do usuário
Flight::route('/', function() {
	// detalhado, mas funciona às vezes quando você precisa
	Flight::response()->write("Olá, Mundo!");

	// se você quiser recuperar o corpo que definiu até este ponto
	// você pode fazer isso assim
	$corpo = Flight::response()->getBody();
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

Se quiser obter o código de status atual, você pode usar o método `status` sem argumentos:

```php
Flight::response()->status(); // 200
```

## Executando um Callback no Corpo da Resposta

Você pode executar um callback no corpo da resposta usando o método `addResponseBodyCallback`:

```php
Flight::route('/usuarios', function() {
	$bd = Flight::db();
	$usuarios = $bd->fetchAll("SELECT * FROM usuarios");
	Flight::render('tabela_usuarios', ['usuarios' => $usuarios]);
});

// Isso irá compactar todas as respostas para qualquer rota
Flight::response()->addResponseBodyCallback(function($corpo) {
	return gzencode($corpo, 9);
});
```

Você pode adicionar vários callbacks e eles serão executados na ordem em que foram adicionados. Como isso pode aceitar qualquer [callable](https://www.php.net/manual/en/language.types.callable.php), pode aceitar um array de classe `[ $classe, 'método' ]`, um closure `$strReplace = function($corpo) { str_replace('olá', 'aí', $corpo); };`, ou um nome de função `'minificar'` se você tivesse uma função para minificar seu código html, por exemplo.

**Nota:** Callbacks de rota não funcionarão se você estiver usando a opção de configuração `flight.v2.output_buffering`.

### Callback de Rota Específica

Se você quiser que isso se aplique apenas a uma rota específica, você pode adicionar o callback na rota em si:

```php
Flight::route('/usuarios', function() {
	$bd = Flight::db();
	$usuarios = $bd->fetchAll("SELECT * FROM usuarios");
	Flight::render('tabela_usuarios', ['usuarios' => $usuarios]);

	// Isso irá compactar apenas a resposta para esta rota
	Flight::response()->addResponseBodyCallback(function($corpo) {
		return gzencode($corpo, 9);
	});
});
```

### Opção de Middleware

Você também pode usar middleware para aplicar o callback a todas as rotas via middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($corpo) {
			// Isto é um
			return $this->minify($corpo);
		});
	}

	protected function minify(string $corpo): string {
		// minificar o corpo
		return $corpo;
	}
}

// index.php
Flight::group('/usuarios', function() {
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
	echo "Olá, Mundo!";
});
```

## JSON

O Flight fornece suporte para envio de respostas JSON e JSONP. Para enviar uma resposta JSON você
passa alguns dados para serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

### JSON com Código de Status

Você também pode passar um código de status como segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON com Impressão Simples

Você também pode passar um argumento para a última posição para habilitar a impressão simples:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Se você estiver alterando as opções passadas para `Flight::json()` e quiser uma sintaxe mais simples, você pode 
apenas remapear o método JSON:

```php
Flight::map('json', function($dados, $codigo = 200, $opções = 0) {
	Flight::_json($dados, $codigo, true, 'utf-8', $opções);
}

// E agora pode ser usado assim
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON e Parar a Execução

Se quiser enviar uma resposta JSON e interromper a execução, você pode usar o método `jsonHalt`.
Isso é útil para casos em que você está verificando talvez algum tipo de autorização e se
o usuário não estiver autorizado, você pode enviar imediatamente uma resposta JSON, limpar o conteúdo do corpo existente
e interromper a execução.

```php
Flight::route('/usuarios', function() {
	$autorizado = algumaVerificaçãoDeAutorização();
	// Verifique se o usuário está autorizado
	if($autorizado === false) {
		Flight::jsonHalt(['erro' => 'Não autorizado'], 401);
	}

	// Continue com o restante da rota
});
```

### JSONP

Para solicitações JSONP, você pode opcionalmente passar o nome do parâmetro de consulta que você está
usando para definir sua função de retorno de chamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Assim, ao fazer uma solicitação GET usando `?q=MinhaFunc`, você deverá receber a saída:

```javascript
MinhaFunc({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será padrão para `jsonp`.

## Redirecionar para outra URL

Você pode redirecionar a solicitação atual usando o método `redirect()` e passando
em uma nova URL:

```php
Flight::redirect('/nova/localizacao');
```

Por padrão, o Flight envia um código de status HTTP 303 ("Veja Outro"). Você pode opcionalmente definir um
código personalizado:

```php
Flight::redirect('/nova/localizacao', 401);
```

## Parar

Você pode parar o framework em qualquer ponto chamando o método `halt`:

```php
Flight::halt();
```

Você também pode especificar um código de status `HTTP` opcional e uma mensagem:

```php
Flight::halt(200, 'Voltamos em breve...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até aquele ponto. Se você quiser parar
o framework e exibir a resposta atual, use o método `stop`:

```php
Flight::stop();
```

## Cache HTTP

O Flight fornece suporte embutido para o cache de nível HTTP. Se a condição de cache
for atendida, o Flight retornará uma resposta HTTP `304 Não Modificado`. A próxima vez que o
cliente solicitar o mesmo recurso, ele será solicitado a usar sua versão em cache localmente.

### Cache de Nível de Rota

Se você deseja armazenar em cache toda a sua resposta, você pode usar o método `cache()` e passar o tempo de cache.

```php

// Isso armazenará em cache a resposta por 5 minutos
Flight::route('/noticias', function () {
  Flight::response()->cache(time() + 300);
  echo 'Este conteúdo será armazenado em cache.';
});

// Alternativamente, você pode usar uma string que passaria
// para o método strtotime()
Flight::route('/noticias', function () {
  Flight::response()->cache('+5 minutos');
  echo 'Este conteúdo será armazenado em cache.';
});
```

### Modificado pela última vez

Você pode usar o método `lastModified` e passar um carimbo de data UNIX para definir a data
e hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até
o valor da última modificação ser alterado.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

### ETag

O cache `ETag` é semelhante ao `Modificado pela última vez`, exceto que você pode especificar qualquer ID
desejado para o recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('meu-id-único');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Lembre-se que chamar `lastModified` ou `etag` definirá e verificará o valor de cache. Se o valor da cache for o mesmo entre as solicitações, o Flight enviará imediatamente
uma resposta `HTTP 304` e interromperá o processamento.