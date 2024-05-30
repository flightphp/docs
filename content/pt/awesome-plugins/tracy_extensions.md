## Tracy Flight Painel de Extensões
=====

Este é um conjunto de extensões para tornar o trabalho com o Flight um pouco mais rico.

- Flight - Analisar todas as variáveis do Flight.
- Banco de Dados - Analisar todas as consultas que foram executadas na página (se você iniciar corretamente a conexão com o banco de dados)
- Requisição - Analisar todas as variáveis `$_SERVER` e examinar todos os payloads globais (`$_GET`, `$_POST`, `$_FILES`)
- Sessão - Analisar todas as variáveis `$_SESSION` se as sessões estiverem ativas.

Este é o Painel

![Barra do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

E cada painel exibe informações muito úteis sobre sua aplicação!

![Dados do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Banco de Dados do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Requisição do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalação
-------
Execute `composer require flightphp/tracy-extensions --dev` e você está pronto!

Configuração
-------
Há muito pouca configuração que você precisa fazer para começar. Você precisará iniciar o depurador Tracy antes de usar isso [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código bootstrap
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Você pode precisar especificar seu ambiente com Debugger::enable(Debugger::DEVELOPMENT)

// se você usa conexões de banco de dados em seu aplicativo, há um
// wrapper PDO necessário para usar APENAS NO DESENVOLVIMENTO (por favor, não em produção!)
// Tem os mesmos parâmetros de uma conexão PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou se você conectar isso ao framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// agora sempre que você fizer uma consulta, irá capturar o tempo, consulta e parâmetros

// Isso conecta os pontos
if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou Tracy não pode realmente renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// mais código

Flight::start();
```

## Configuração Adicional

### Dados da Sessão
Se você tiver um manipulador de sessão personalizado (como ghostff/session), você pode passar qualquer array de dados de sessão para Tracy e ele os exibirá automaticamente para você. Você passa com a chave `session_data` no segundo parâmetro do construtor `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou Tracy não pode realmente renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// rotas e outras coisas...

Flight::start();
```

### Latte

Se você tiver o Latte instalado em seu projeto, você pode usar o painel Latte para analisar seus modelos. Você pode passar a instância do Latte para o construtor `TracyExtensionLoader` com a chave `latte` no segundo parâmetro.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// aqui é onde você adiciona o Painel do Latte ao Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Isso precisa ser falso ou Tracy não pode realmente renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
