Tracy Flight Painel Extensões
=====

Este é um conjunto de extensões para tornar o trabalho com o Flight um pouco mais rico.

- Flight - Analise todas as variáveis do Flight.
- Banco de Dados - Analise todas as consultas que foram executadas na página (se você iniciar corretamente a conexão com o banco de dados)
- Requisição - Analise todas as variáveis `$_SERVER` e examine todos os payloads globais (`$_GET`, `$_POST`, `$_FILES`)
- Sessão - Analise todas as variáveis `$_SESSION` se as sessões estiverem ativas.

Este é o Painel

![Barra do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

E cada painel exibe informações muito úteis sobre a sua aplicação!

![Dados do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Banco de Dados do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Requisição do Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalação
-------
Execute `composer require flightphp/tracy-extensions --dev` e você está pronto para começar!

Configuração
-------
Há muito pouca configuração que você precisa fazer para começar. Você precisará iniciar o depurador Tracy antes de usar isso [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de inicialização
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Talvez você precise especificar seu ambiente com Debugger::enable(Debugger::DEVELOPMENT)

// se você usar conexões de banco de dados em seu aplicativo, há um
// invólucro PDO necessário a ser usado APENAS NO DESENVOLVIMENTO (não em produção, por favor!)
// Tem os mesmos parâmetros de uma conexão PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'usuário', 'senha');
// ou se você anexar isso ao framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'usuário', 'senha']);
// agora, sempre que você fizer uma consulta, ela capturará o tempo, a consulta e os parâmetros

// Isto conecta os pontos
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// mais código

Flight::start();
```