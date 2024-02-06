# Tracy

Tracy é um manipulador de erros incrível que pode ser usado com Flight. Tem várias telas que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar suas próprias telas. A equipe do Flight criou algumas telas especificamente para projetos do Flight com o plugin [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Instalação

Instale com o composer. E você realmente vai querer instalar isso sem a versão de desenvolvimento, já que o Tracy vem com um componente de tratamento de erros de produção.

```bash
composer require tracy/tracy
```

## Configuração Básica

Existem algumas opções de configuração básicas para começar. Você pode ler mais sobre elas na [Documentação do Tracy](https://tracy.nette.org/pt/configurando).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Habilitar o Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // às vezes você tem que ser explícito (também Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // você também pode fornecer um array de endereços IP

// Aqui é onde os erros e exceções serão registrados. Certifique-se de que este diretório exista e tenha permissão de escrita.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // exibir todos os erros
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // todos os erros exceto notificações obsoletas
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // se a barra do Debugger estiver visível, então o comprimento do conteúdo não pode ser definido pelo Flight

	// Isto é específico para a Extensão Tracy para o Flight se você a incluiu
	// caso contrário, comente isto.
	new TracyExtensionLoader($app);
}
```

## Dicas Úteis

Quando você estiver depurando seu código, existem algumas funções muito úteis para exibir dados para você.

- `bdump($var)` - Isso irá despejar a variável na Barra Tracy em uma tela separada.
- `dumpe($var)` - Isso irá despejar a variável e então encerrar imediatamente.