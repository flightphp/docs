# Respostas

Flight ajuda a gerar parte dos cabeçalhos de resposta para você, mas você tem a maior parte do controle sobre o que enviar de volta para o usuário. Às vezes, você pode acessar o objeto `Response` diretamente, mas na maioria das vezes, usará a instância `Flight` para enviar uma resposta.

## Enviando uma Resposta Básica

Flight usa ob_start() para armazenar em buffer a saída. Isso significa que você pode usar `echo` ou `print` para enviar uma resposta ao usuário e o Flight capturará e enviará de volta para o usuário com os cabeçalhos apropriados.

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
	// verboso, mas faz o trabalho às vezes quando você precisa
	Flight::response()->write("Olá, Mundo!");

	// se deseja recuperar o corpo que definiu neste ponto
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

Se desejar obter o código de status atual, pode usar o método `status` sem argumentos:

```php
Flight::response()->status(); // 200
```

## Definindo um Cabeçalho de Resposta

Você pode definir um cabeçalho, como o tipo de conteúdo da resposta, usando o método `header`:

```php

// Isso enviará "Olá, Mundo!" para o navegador do usuário em texto simples
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Olá, Mundo!";
});
```



## JSON

Flight fornece suporte para o envio de respostas JSON e JSONP. Para enviar uma resposta JSON, você passa alguns dados a serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

### JSONP

Para solicitações JSONP, opcionalmente pode passar o nome do parâmetro de consulta que está utilizando para definir sua função de retorno de chamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Portanto, ao fazer uma solicitação GET usando `?q=my_func`, você deve receber a saída:

```javascript
my_func({"id":123});
```

Se não passar um nome de parâmetro de consulta, ele será padrão para `jsonp`.

## Redirecionar para outra URL

Você pode redirecionar a solicitação atual usando o método `redirect()` e passando
uma nova URL:

```php
Flight::redirect('/nova/localizacao');
```

Por padrão, o Flight envia um código de status HTTP 303 ("Veja Outro"). Opcionalmente, é possível definir um
código personalizado:

```php
Flight::redirect('/nova/localizacao', 401);
```

## Parando

É possível parar o framework em qualquer ponto chamando o método `halt`:

```php
Flight::halt();
```

Também é possível especificar um código de status `HTTP` e uma mensagem opcional:

```php
Flight::halt(200, 'Volto já...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até esse ponto. Se deseja interromper
o framework e exibir a resposta atual, use o método `stop`:

```php
Flight::stop();
```

## Caching HTTP

Flight fornece suporte integrado para o cache de nível HTTP. Se a condição de cache
for atendida, o Flight retornará uma resposta `304 Not Modified` HTTP. Na próxima vez que o
cliente solicitar o mesmo recurso, será solicitado a usar sua versão em cache local.

### Cache no Nível da Rota

Se deseja armazenar em cache toda a resposta, pode usar o método `cache()` e passar o tempo para armazenar em cache.

```php

// Isto armazenará em cache a resposta por 5 minutos
Flight::route('/noticias', function () {
  Flight::cache(time() + 300);
  echo 'Este conteúdo será armazenado em cache.';
});

// Alternativamente, pode usar uma string que passaria
// para o método strtotime()
Flight::route('/noticias', function () {
  Flight::cache('+5 minutes');
  echo 'Este conteúdo será armazenado em cache.';
});
```

### Última Modificação

Pode usar o método `lastModified` e passar um carimbo de data UNIX para definir a data
e hora em que uma página foi modificada pela última vez. O cliente continuará a usar seu cache até
o valor da última modificação ser alterado.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este conteúdo será armazenado em cache.';
});
```

### ETag

O cache de `ETag` é semelhante ao de `Última Modificação`, exceto que pode especificar qualquer identidade
desejada para o recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('meu-id-único');
  echo 'Este conteúdo será armazenado em cache.';
});
```

Lembre-se de que chamar `lastModified` ou `etag` definirá e verificará o
valor de cache. Se o valor de cache for o mesmo entre as solicitações, o Flight enviará imediatamente
uma resposta `HTTP 304` e interromperá o processamento.