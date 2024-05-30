# Carregamento Automático

O carregamento automático é um conceito em PHP onde você especifica um diretório ou diretórios para carregar classes. Isso é muito mais benéfico do que usar `require` ou `include` para carregar classes. Também é um requisito para usar pacotes do Composer.

Por padrão, qualquer classe `Flight` é carregada automaticamente para você graças ao composer. No entanto, se você deseja carregar suas próprias classes, pode usar o método `Flight::path` para especificar um diretório para carregar classes.

## Exemplo Básico

Vamos supor que temos uma árvore de diretórios como a seguinte:

```text
# Caminho de Exemplo
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contém os controladores para este projeto
│   ├── translations
│   ├── UTILS - contém classes apenas para esta aplicação (em maiúsculas de propósito para um exemplo posterior)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Você pode especificar cada diretório para carregar da seguinte forma:

```php

/**
 * public/index.php
 */

// Adicione um caminho ao carregador automático
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// nenhum espaço de nomes necessário

// Todas as classes carregadas automaticamente são recomendadas para serem Pascal Case (cada palavra em maiúscula, sem espaços)
// A partir da versão 3.7.2, você pode usar Pascal_Snake_Case para os nomes de suas classes executando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faça algo
	}
}
```

## Espaços de Nomes

Se você tiver espaços de nomes, na verdade se torna muito fácil implementar isso. Você deve usar o método `Flight::path()` para especificar o diretório raiz (não o diretório do documento ou a pasta `public/`) de sua aplicação.

```php

/**
 * public/index.php
 */

// Adicione um caminho ao carregador automático
Flight::path(__DIR__.'/../');
```

Agora é assim que o seu controlador pode ser parecido. Observe o exemplo abaixo, mas preste atenção nos comentários para informações importantes.

```php
/**
 * app/controllers/MyController.php
 */

// espaços de nomes são necessários
// os espaços de nomes são iguais à estrutura de diretórios
// os espaços de nomes devem seguir o mesmo caso que a estrutura de diretórios
// os espaços de nomes e diretórios não podem ter nenhum sublinhado (a menos que Loader::setV2ClassLoading(false) seja definido)
namespace app\controllers;

// Todas as classes carregadas automaticamente são recomendadas para ser Pascal Case (cada palavra em maiúscula, sem espaços)
// A partir da versão 3.7.2, você pode usar Pascal_Snake_Case para os nomes de suas classes executando Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faça algo
	}
}
```

E se você quisesse carregar automaticamente uma classe em seu diretório de utils, você faria basicamente a mesma coisa:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// o espaço de nomes deve corresponder à estrutura e ao caso do diretório (observe que o diretório UTILS está em maiúsculas
//     como na árvore de arquivos acima)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faça algo
	}
}
```

## Sublinhados em Nomes de Classes

A partir da versão 3.7.2, você pode usar Pascal_Snake_Case para os nomes de suas classes executando `Loader::setV2ClassLoading(false);`. Isso permitirá que você use sublinhados em seus nomes de classes. Isso não é recomendado, mas está disponível para aqueles que precisam.

```php

/**
 * public/index.php
 */

// Adicione um caminho ao carregador automático
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// nenhum espaço de nomes necessário

class My_Controller {

	public function index() {
		// faça algo
	}
}
```