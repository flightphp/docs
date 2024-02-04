# Tracy Flight Panel Extensions
=====

Este é um conjunto de extensões para tornar o trabalho com o Flight um pouco mais completo.

- Flight - Analisar todas as variáveis do Flight.
- Banco de Dados - Analisar todas as consultas que foram executadas na página (se você iniciar corretamente a conexão com o banco de dados)
- Solicitação - Analisar todas as variáveis `$_SERVER` e examinar todos os payloads globais (`$_GET`, `$_POST`, `$_FILES`)
- Sessão - Analisar todas as variáveis `$_SESSION` se as sessões estiverem ativas.

Este é o Painel

![Barra de Voo](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

E cada painel exibe informações muito úteis sobre sua aplicação!

![Dados do Voo](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Banco de Dados do Voo](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Requisição do Voo](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalação
-------
Execute `composer require flightphp/tracy-extensions --dev` e você está no caminho certo!

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
// Você pode precisar especificar seu ambiente com Debugger::enable(Debugger::DEVELOPMENT)

// se você usar conexões de banco de dados em seu aplicativo, há um
// wrapper PDO necessário para uso APENAS NO DESENVOLVIMENTO (não na produção por favor!)
// Ele tem os mesmos parâmetros que uma conexão PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'usuário', 'senha');
// ou se você anexar isso ao framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'usuário', 'senha']);
// agora sempre que você fizer uma consulta, ele irá capturar o tempo, consulta e parâmetros

// Isso conecta os pontos
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// mais código

Flight::start();
```