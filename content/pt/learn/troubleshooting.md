```pt
# Solução de problemas

Esta página irá ajudá-lo a resolver problemas comuns que você pode encontrar ao usar o Flight.

## Problemas Comuns

### 404 Não Encontrado ou Comportamento de Rota Inesperado

Se você estiver vendo um erro 404 Não Encontrado (mas jura pela sua vida que está realmente lá e não é um erro de digitação) isso poderia 
realmente ser um problema com você retornando um valor no ponto final da sua rota em vez de apenas ecoá-lo. A razão para isso é intencional, 
mas pode pegar alguns desenvolvedores de surpresa.

```php

Flight::route('/hello', function(){
	// Isso pode causar um erro 404 Não Encontrado
	return 'Olá Mundo';
});

// O que você provavelmente quer
Flight::route('/hello', function(){
	echo 'Olá Mundo';
});

```

A razão para isso é por causa de um mecanismo especial incorporado no roteador que trata a saída de retorno como um sinal para "ir para a próxima rota". 
Você pode ver o comportamento documentado na seção de [Roteamento](/learn/routing#passing).

### Classe Não Encontrada (autoload não funcionando)

Podem haver algumas razões para isso não estar funcionando. Abaixo estão alguns exemplos, mas certifique-se também de verificar a seção de [autoload](/learn/autoloading).

#### Nome do Arquivo Incorreto
O mais comum é que o nome da classe não corresponda ao nome do arquivo.

Se você tem uma classe chamada `MyClass`, então o arquivo deve ser chamado `MyClass.php`. Se você tem uma classe chamada `MyClass` e o arquivo é chamado `myclass.php` 
então o autoload não será capaz de encontrá-lo.

#### Namespace Incorreto
Se estiver usando namespaces, então o namespace deve corresponder à estrutura de diretórios.

```php
// código

// se o seu MyController estiver no diretório app/controllers e estiver em um namespace
// isso não funcionará
Flight::route('/hello', 'MyController->hello');

// você precisará escolher uma dessas opções
Flight::route('/hello', 'app\controllers\MyController->hello');
// ou se você tiver um comando use no topo

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// também pode ser escrito
Flight::route('/hello', MyController::class.'->hello');
// também...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` não definido

No aplicativo skeleton, isso é definido dentro do arquivo `config.php`, mas para que suas classes sejam encontradas, você precisa garantir que o método `path()`
esteja definido (provavelmente na raiz do seu diretório) antes de tentar usá-lo.

```php

// Adicione um caminho ao autoload
Flight::path(__DIR__.'/../');

```