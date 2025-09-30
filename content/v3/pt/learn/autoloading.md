# Autoloading

## Visão Geral

Autoloading é um conceito no PHP onde você especifica um diretório ou diretórios para carregar classes. Isso é muito mais benéfico do que usar `require` ou `include` para carregar classes. Também é um requisito para usar pacotes do Composer.

## Entendendo

Por padrão, qualquer classe `Flight` é autoloaded automaticamente para você graças ao composer. No entanto, se você quiser autoload suas próprias classes, pode usar o método `Flight::path()` para especificar um diretório para carregar classes.

Usar um autoloader pode ajudar a simplificar seu código de forma significativa. Em vez de ter arquivos começando com uma infinidade de declarações `include` ou `require` no topo para capturar todas as classes usadas nesse arquivo, você pode chamar dinamicamente suas classes e elas serão incluídas automaticamente.

## Uso Básico

Vamos assumir que temos uma árvore de diretórios como a seguinte:

```text
# Exemplo de caminho
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contém os controllers para este projeto
│   ├── translations
│   ├── UTILS - contém classes apenas para esta aplicação (isso está em maiúsculas de propósito para um exemplo posterior)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Você pode ter notado que esta é a mesma estrutura de arquivos deste site de documentação.

Você pode especificar cada diretório para carregar assim:

```php

/**
 * public/index.php
 */

// Adicione um caminho ao autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// sem namespacing necessário

// Todas as classes autoloaded são recomendadas para serem Pascal Case (cada palavra capitalizada, sem espaços)
class MyController {

	public function index() {
		// faça algo
	}
}
```

## Namespaces

Se você tiver namespaces, na verdade fica muito fácil implementar isso. Você deve usar o método `Flight::path()` para especificar o diretório raiz (não o document root ou pasta `public/`) da sua aplicação.

```php

/**
 * public/index.php
 */

// Adicione um caminho ao autoloader
Flight::path(__DIR__.'/../');
```

Agora é assim que o seu controller pode parecer. Olhe o exemplo abaixo, mas preste atenção nos comentários para informações importantes.

```php
/**
 * app/controllers/MyController.php
 */

// namespaces são necessários
// namespaces são os mesmos que a estrutura de diretórios
// namespaces devem seguir o mesmo case que a estrutura de diretórios
// namespaces e diretórios não podem ter underscores (a menos que Loader::setV2ClassLoading(false) seja definido)
namespace app\controllers;

// Todas as classes autoloaded são recomendadas para serem Pascal Case (cada palavra capitalizada, sem espaços)
// A partir de 3.7.2, você pode usar Pascal_Snake_Case para os nomes das suas classes executando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faça algo
	}
}
```

E se você quisesse autoload uma classe no seu diretório utils, você faria basicamente a mesma coisa:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// namespace deve corresponder à estrutura de diretórios e case (note que o diretório UTILS está em maiúsculas
//     como na árvore de arquivos acima)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faça algo
	}
}
```

## Underscores nos Nomes de Classes

A partir de 3.7.2, você pode usar Pascal_Snake_Case para os nomes das suas classes executando `Loader::setV2ClassLoading(false);`. 
Isso permitirá que você use underscores nos nomes das suas classes. 
Isso não é recomendado, mas está disponível para aqueles que precisam.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Adicione um caminho ao autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// sem namespacing necessário

class My_Controller {

	public function index() {
		// faça algo
	}
}
```

## Veja Também
- [Routing](/learn/routing) - Como mapear rotas para controllers e renderizar views.
- [Por que um Framework?](/learn/why-frameworks) - Entendendo os benefícios de usar um framework como Flight.

## Solução de Problemas
- Se você não conseguir descobrir por que suas classes com namespaces não estão sendo encontradas, lembre-se de usar `Flight::path()` para o diretório raiz no seu projeto, não o seu diretório `app/` ou `src/` ou equivalente.

### Classe Não Encontrada (autoloading não funcionando)

Pode haver alguns motivos para isso não acontecer. Abaixo estão alguns exemplos, mas certifique-se de verificar também a seção [autoloading](/learn/autoloading).

#### Nome de Arquivo Incorreto
O mais comum é que o nome da classe não corresponda ao nome do arquivo.

Se você tiver uma classe chamada `MyClass`, o arquivo deve ser chamado `MyClass.php`. Se você tiver uma classe chamada `MyClass` e o arquivo for chamado `myclass.php` 
então o autoloader não conseguirá encontrá-lo.

#### Namespace Incorreto
Se você estiver usando namespaces, o namespace deve corresponder à estrutura de diretórios.

```php
// ...código...

// se o seu MyController estiver no diretório app/controllers e estiver namespaced
// isso não funcionará.
Flight::route('/hello', 'MyController->hello');

// você precisará escolher uma dessas opções
Flight::route('/hello', 'app\controllers\MyController->hello');
// ou se você tiver uma declaração use no topo

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// também pode ser escrito
Flight::route('/hello', MyController::class.'->hello');
// também...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` não definido

No app skeleton, isso é definido dentro do arquivo `config.php`, mas para que suas classes sejam encontradas, você precisa garantir que o método `path()`
seja definido (provavelmente para a raiz do seu diretório) antes de tentar usá-lo.

```php
// Adicione um caminho ao autoloader
Flight::path(__DIR__.'/../');
```

## Changelog
- v3.7.2 - Você pode usar Pascal_Snake_Case para os nomes das suas classes executando `Loader::setV2ClassLoading(false);`
- v2.0 - Funcionalidade de autoload adicionada.