# Respostas

O Flight ajuda a gerar parte dos cabeçalhos de resposta para você, mas você tem a maior parte do controle sobre o que envia de volta para o usuário. Às vezes você pode acessar o objeto `Response` diretamente, mas na maioria das vezes você usará a instância `Flight` para enviar uma resposta.

## Enviando uma Resposta Básica

O Flight usa ob_start() para armazenar em buffer a saída. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta ao usuário e o Flight irá capturá-la e enviá-la de volta ao usuário com os cabeçalhos apropriados.

```php

// Isto enviará "Olá, Mundo!" para o navegador do usuário
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

// Isto enviará "Olá, Mundo!" para o navegador do usuário
Flight::route('/', function() {
	// verboso, mas resolve o problema às vezes quando você precisa
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

Se quiser obter o código de status atual, pode usar o método `status` sem nenhum argumento:

```php
Flight::response()->status(); // 200
```

## Definindo um Corpo de Resposta

Você pode definir o corpo da resposta usando o método `write`, no entanto, se você usar echo ou print em qualquer coisa,
será capturado e enviado como o corpo de resposta via armazenamento em buffer de saída.

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

Se quiser limpar o corpo da resposta, pode usar o método `clearBody`:

```php
Flight::route('/', function() {
	if($algumaCondicao) {
		Flight::response()->write("Olá, Mundo!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Executando um Callback no Corpo de Resposta

Você pode executar um callback no corpo de resposta usando o método `addResponseBodyCallback`:

```php
Flight::route('/usuarios', function() {
	$bd = Flight::bd();
	$usuarios = $bd->fetchAll("SELECIONAR * FROM usuarios");
	Flight::render('tabela_usuarios', ['usuarios' => $usuarios]);
});

// Isto irá compactar todas as respostas para qualquer rota
Flight::response()->addResponseBodyCallback(function($corpo) {
	return gzencode($corpo, 9);
});
```

Você pode adicionar vários callbacks e eles serão executados na ordem em que foram adicionados. Porque isso pode aceitar qualquer [chamável](https://www.php.net/manual/en/language.types.callable.php), pode aceitar um array de classe `[ $classe, 'método' ]`, um fechamento `$strReplace = function($corpo) { str_replace('oi', 'olá', $corpo); };`, ou um nome de função `'minify'` se você tiver uma função para minificar seu código html, por exemplo.

**Nota:** Os callbacks de rota não funcionarão se você estiver usando a opção de configuração `flight.v2.output_buffering`.

### Callback de Rota Específica

Se desejar que isso se aplique apenas a uma rota específica, poderá adicionar o callback na própria rota:

```php
Flight::route('/usuarios', function() {
	$bd = Flight::bd();
	$usuarios = $bd->fetchAll("SELECIONAR * FROM usuarios");
	Flight::render('tabela_usuarios', ['usuarios' => $usuarios]);

	// Isto compactará apenas a resposta para esta rota
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
		// Aplique o callback aqui no objeto response().
		Flight::response()->addResponseBodyCallback(function($corpo) {
			return $this->minify($corpo);
		});
	}

	protected function minify(string $corpo): string {
		// minimize o corpo de alguma forma
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

Você pode configurar um cabeçalho, como o tipo de conteúdo da resposta, usando o método `header`:

```php

// Isto enviará "Olá, Mundo!" para o navegador do usuário em texto simples
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// ou
	Flight::response()->setHeader('Content-Type', 'text/plain');
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

### JSON com Impressão Bonita

Você também pode passar um argumento na última posição para habilitar a impressão bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Se estiver alterando as opções passadas para `Flight::json()` e desejar uma sintaxe mais simples, pode
remapear o método JSON:

```php
Flight::map('json', function($dados, $código = 200, $opções = 0) {
	Flight::_json($dados, $código, true, 'utf-8', $opções);
}

// E agora pode ser usado assim
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON e Interromper a Execução (v3.10.0)

Se desejar enviar uma resposta JSON e interromper a execução, pode usar o método `jsonHalt`.
Isso é útil para casos em que você está verificando talvez algum tipo de autorização e se
o usuário não estiver autorizado, você pode enviar imediatamente uma resposta JSON, limpar o conteúdo do corpo existente
e interromper a execução.

```php
Flight::route('/usuarios', function() {
	$autorizado = algumaVerificaçãoDeAutorização();
	// Verifique se o usuário está autorizado
	if($autorizado === falso) {
		Flight::jsonHalt(['erro' => 'Não autorizado'], 401);
	}

	// Continue com o restante da rota
});
```

Antes da v3.10.0, você teria que fazer algo assim:

```php
Flight::route('/usuarios', function() {
	$autorizado = algumaVerificaçãoDeAutorização();
	// Verifique se o usuário está autorizado
	if($autorizado === falso) {
		Flight::haltar(401, json_encode(['erro' => 'Não autorizado']));
	}

	// Continue com o restante da rota
});
```

### JSONP

Para requisições JSONP, você pode opcionalmente passar o nome do parâmetro da query que você está
usando para definir sua função de retorno de chamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Portanto, ao fazer uma solicitação GET usando `?q=my_func`, você deverá receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de query, ele será padrão para `jsonp`.

## Redirecionar para outra URL

Você pode redirecionar a solicitação atual usando o método `redirect()` e passando
uma nova URL:

```php
Flight::redirect('/nova/localização');
```

Por padrão, o Flight enviará um código de status HTTP 303 ("See Other"). Você pode opcionalmente definir um
código personalizado:

```php
Flight::redirect('/nova/localização', 401);
```

## Parando

Você pode parar o framework a qualquer momento chamando o método `halt`:

```php
Flight::halt();
```

Você também pode especificar um código de `HTTP` opcional e mensagem:

```php
Flight::halt(200, 'Já volto...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até esse ponto. Se quiser parar
o framework e enviar a resposta atual, use o método `stop`:

```php
Flight::stop();
```

## Limpar Dados de Resposta

Você pode limpar o corpo e os cabeçalhos da resposta usando o método `clear()`. Isto limpará
quaisquer cabeçalhos atribuídos à resposta, limpará o corpo da resposta e definirá o código de status para `200`.

```php
Flight::response()->clear();
```

### Limpar Somente o Corpo da Resposta

Se quiser limpar apenas o corpo da resposta, pode usar o método `clearBody()`:

```php
// Isto ainda manterá quaisquer cabeçalhos definidos no objeto response().
Flight::response()->clearBody();
```

## Cache HTTP

O Flight fornece suporte embutido para caching a nível HTTP. Se a condição de cache
for atendida, o Flight retornará uma resposta HTTP `304 Not Modified`. Da próxima vez que o
cliente solicitar o mesmo recurso, será solicitado a usar sua versão em cache local.

### Cache a Nível de Rota

Se desejar armazenar em cache toda a sua resposta, pode usar o método `cache()` e passar o tempo de cacheamento.

```php

// Isto vai armazenar em cache a resposta por 5 minutos
Flight::route('/notícias', function () {
  Flight::response()->cache(time() + 300);
  echo 'Este conteúdo será armazenado em cache.';
});

// Alternativamente, você pode usar uma string que passaria
// para o método strtotime()
Flight::route('/notícias', function () {
  Flight::response()->cache('+5 minutos');
  echo 'Este conteúdo será armazenado em cache.';
});
```

### Última Modificação

Você pode usar o método `lastModified` e passar um carimbo de data UNIX para definir a data
e hora em que uma página foi modificada pela última vez. O cliente continuará usando seu cache até
o valor da última modificação ser alterado.

```php
Flight::route('/notícias', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

### ETag

O cache do `ETag` é semelhante ao `Última Modificação`, exceto que você pode especificar qualquer identificador
desejado para o recurso:

```php
Flight::route('/notícias', function () {
  Flight::etag('meu-id-único');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Lembre-se que chamar `lastModified` ou `etag` definirá e verificará o valor em cache. Se o valor em cache for o mesmo entre as solicitações, o Flight enviará imediatamente uma resposta `HTTP 304` e interromperá o processamento.

### Baixar um Arquivo

Há um método auxiliar para baixar um arquivo. Você pode usar o método `download` e passar o caminho.

```php
Flight::route('/baixar', function () {
  Flight::download('/caminho/para/arquivo.txt');
});
```