Tracy Flight Painel de Extensões
=====

Isto é um conjunto de extensões para tornar o trabalho com Flight um pouco mais rico.

- Flight - Analise todas as variáveis Flight.
- Database - Analise todas as consultas que foram executadas na página (se você iniciar corretamente a conexão com o banco de dados)
- Request - Analise todas as variáveis `$_SERVER` e examine todas as cargas úteis globais (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analise todas as variáveis `$_SESSION` se as sessões estiverem ativas.

Este é o Painel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

E cada painel exibe informações muito úteis sobre sua aplicação!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Clique [here](https://github.com/flightphp/tracy-extensions) para ver o código.

Instalação
-------
Execute `composer require flightphp/tracy-extensions --dev` e você estará pronto!

Configuração
-------
Há pouca configuração necessária para começar. Você precisará iniciar o depurador Tracy antes de usar isto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de bootstrap
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Você pode precisar especificar seu ambiente com Debugger::enable(Debugger::DEVELOPMENT)

// se você usar conexões de banco de dados no seu app, há um
// wrapper PDO obrigatório para usar SOMENTE EM DESENVOLVIMENTO (não em produção, por favor!)
// Ele tem os mesmos parâmetros que uma conexão PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou se você anexar isso ao framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// agora, sempre que você fizer uma consulta, ela capturará o tempo, a consulta e os parâmetros

// Isso conecta os pontos
if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou Tracy não pode renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// mais código

Flight::start();
```

## Configuração Adicional

### Dados de Sessão
Se você tiver um manipulador de sessão personalizado (como ghostff/session), você pode passar um array de dados de sessão para Tracy e ele automaticamente os exibirá. Você passa isso com a chave `session_data` no segundo parâmetro do construtor de `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// ou use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou Tracy não pode renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// rotas e outras coisas...

Flight::start();
```

### Latte

Se você tiver Latte instalado no seu projeto, você pode usar o painel Latte para analisar seus templates. Você pode passar a instância Latte para o construtor de `TracyExtensionLoader` com a chave `latte` no segundo parâmetro.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// aqui é onde você adiciona o Painel Latte ao Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou Tracy não pode renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```