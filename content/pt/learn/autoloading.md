# Carregamento Automático

O carregamento automático é um conceito em PHP onde você especifica um diretório ou diretórios para carregar classes. Isso é muito mais benéfico do que usar `require` ou `include` para carregar classes. Também é um requisito para usar pacotes do Composer.

Por padrão, qualquer classe `Flight` é carregada automaticamente para você graças ao composer. No entanto, se você quiser carregar automaticamente suas próprias classes, pode usar o método `Flight::path` para especificar um diretório para carregar as classes.

## Exemplo Básico

Vamos supor que temos uma estrutura de diretórios como a seguinte:

```text
# Caminho de Exemplo
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contém os controladores para este projeto
│   ├── translations
│   ├── UTILS - contém classes apenas para esta aplicação (isso está em letras maiúsculas de propósito para um exemplo posterior)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Você pode especificar cada diretório a ser carregado da seguinte maneira:

```php

/**
 * public/index.php
 */

// Adicione um caminho para o carregador automático
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// nenhum espaço de nome necessário

// Todas as classes carregadas automaticamente são recomendadas para serem Pascal Case (cada palavra capitalizada, sem espaços)
// É um requisito que você não pode ter um sublinhado no nome da sua classe
class MyController {

	public function index() {
		// faça algo
	}
}
```

## Espaços de Nomes

Se você tiver espaços de nomes, na verdade se torna muito fácil de implementar isso. Você deve usar o método `Flight::path()` para especificar o diretório raiz (não o diretório do documento ou a pasta `public/`) de sua aplicação.

```php

/**
 * public/index.php
 */

// Adicione um caminho para o carregador automático
Flight::path(__DIR__.'/../');
```

Agora é assim que o seu controlador pode parecer. Olhe o exemplo abaixo, mas preste atenção nos comentários para informações importantes.

```php
/**
 * app/controllers/MyController.php
 */

// espaços de nomes são obrigatórios
// os espaços de nomes são os mesmos que a estrutura de diretórios
// os espaços de nomes devem seguir o mesmo caso que a estrutura de diretórios
// espaços de nomes e diretórios não podem ter sublinhados
namespace app\controllers;

// Todas as classes carregadas automaticamente são recomendadas para serem Pascal Case (cada palavra capitalizada, sem espaços)
// É um requisito que você não pode ter um sublinhado no nome da sua classe
class MyController {

	public function index() {
		// faça algo
	}
}
```

E se você deseja carregar automaticamente uma classe em seu diretório de utilitários, você faria basicamente a mesma coisa:

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// o espaço de nomes deve corresponder à estrutura de diretórios e ao caso (observe que o diretório UTILS está em maiúsculas
//     como na estrutura de arquivos acima)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faça algo
	}
}
```