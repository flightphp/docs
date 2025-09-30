# Extensões do Painel Tracy Flight
=====

Este é um conjunto de extensões para tornar o trabalho com o Flight um pouco mais rico.

- Flight - Analisar todas as variáveis do Flight.
- Database - Analisar todas as consultas que foram executadas na página (se você iniciar corretamente a conexão com o banco de dados)
- Request - Analisar todas as variáveis `$_SERVER` e examinar todos os payloads globais (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analisar todas as variáveis `$_SESSION` se as sessões estiverem ativas.

Este é o Painel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

E cada painel exibe informações muito úteis sobre sua aplicação!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Clique [aqui](https://github.com/flightphp/tracy-extensions) para ver o código.

## Instalação
-------
Execute `composer require flightphp/tracy-extensions --dev` e você está pronto para começar!

## Configuração
-------
Há pouca configuração que você precisa fazer para começar. Você precisará iniciar o depurador Tracy antes de usar isso [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Você pode precisar especificar seu ambiente com Debugger::enable(Debugger::DEVELOPMENT)

// se você usar conexões com banco de dados em sua aplicação, há um
// wrapper PDO obrigatório para usar APENAS EM DESENVOLVIMENTO (não em produção, por favor!)
// Ele tem os mesmos parâmetros que uma conexão PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou se você anexar isso ao framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// agora, sempre que você fizer uma consulta, ela capturará o tempo, a consulta e os parâmetros

// Isso conecta os pontos
if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou o Tracy não consegue renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Configuração Adicional

### Dados de Sessão
Se você tiver um manipulador de sessão personalizado (como ghostff/session), você pode passar qualquer array de dados de sessão para o Tracy e ele os exibirá automaticamente para você. Você o passa com a chave `session_data` no segundo parâmetro do construtor do `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// ou use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou o Tracy não consegue renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_O PHP 8.1+ é necessário para esta seção._

Se você tiver o Latte instalado em seu projeto, o Tracy tem uma integração nativa com o Latte para analisar seus templates. Você simplesmente registra a extensão com sua instância do Latte.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// only add the extension if Tracy Debug Bar is enabled
	if(Debugger::$showBar === true) {
		// this is where you add the Latte Panel to Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```