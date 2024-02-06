Tracy Flight Painel Extensions
=====

Este é um conjunto de extensões para tornar o trabalho com o Flight um pouco mais rico.

- Flight - Analisar todas as variáveis do Flight.
- Database - Analisar todas as consultas que foram executadas na página (se você inicializar corretamente a conexão com o banco de dados)
- Request - Analisar todas as variáveis `$_SERVER` e examinar todos os payloads globais (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analisar todas as variáveis `$_SESSION` se as sessões estiverem ativas.

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
Há muito pouca configuração que você precisa fazer para começar. Você precisará inicializar o depurador Tracy antes de usar isso [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de inicialização
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Talvez seja necessário especificar seu ambiente com Debugger::enable(Debugger::DEVELOPMENT)

// se você usar conexões de banco de dados em seu aplicativo, há um
// wrapper PDO necessário para uso SOMENTE NO DESENVOLVIMENTO (não na produção, por favor!)
// Ele possui os mesmos parâmetros que uma conexão PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou se você conectar isso ao framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// agora, toda vez que você fizer uma consulta, ele capturará o tempo, a consulta e os parâmetros

// Isso conecta os pontos
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// mais código

Flight::start();
```  