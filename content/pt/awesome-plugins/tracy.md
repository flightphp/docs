# Tracy

Tracy é um incrível manipulador de erros que pode ser usado com o Flight. Possui várias telas que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar suas próprias telas. A Equipe Flight criou algumas telas especificamente para projetos Flight com o [plugin flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Instalação

Instale com o composer. E você realmente vai querer instalar isso sem a versão de desenvolvimento, pois o Tracy vem com um componente de tratamento de erros de produção.

```bash
composer require tracy/tracy
```

## Configuração Básica

Existem algumas opções de configuração básicas para começar. Você pode saber mais sobre elas na [Documentação do Tracy](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Habilitar o Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // às vezes é necessário ser explícito (também Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // você também pode fornecer um array de endereços IP

// Aqui é onde os erros e exceções serão registrados. Certifique-se de que este diretório exista e seja gravável.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // mostrar todos os erros
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // todos os erros exceto avisos deprecados
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // se a barra do Debugger estiver visível, então o comprimento do conteúdo não pode ser definido pelo Flight

	// Isso é específico para a Extensão Tracy para Flight se você tiver incluído isso
	// caso contrário, comente isso.
	new TracyExtensionLoader($app);
}
```

## Dicas Úteis

Ao depurar seu código, existem algumas funções muito úteis para exibir dados para você.

- `bdump($var)` - Isso irá despejar a variável na Barra Tracy em uma tela separada.
- `dumpe($var)` - Isso irá despejar a variável e em seguida parar imediatamente.