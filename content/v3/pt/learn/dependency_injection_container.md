# Contêiner de Injeção de Dependência

## Visão Geral

O Contêiner de Injeção de Dependência (DIC) é uma melhoria poderosa que permite gerenciar
as dependências da sua aplicação.

## Entendendo

A Injeção de Dependência (DI) é um conceito chave em frameworks PHP modernos e é
usada para gerenciar a instanciação e configuração de objetos. Alguns exemplos de bibliotecas DIC
são: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), e [league/container](https://container.thephpleague.com/).

Um DIC é uma forma elegante de permitir que você crie e gerencie suas classes em um
local centralizado. Isso é útil quando você precisa passar o mesmo objeto para
múltiplas classes (como seus controladores ou middleware, por exemplo).

## Uso Básico

A forma antiga de fazer as coisas pode parecer assim:
```php

require 'vendor/autoload.php';

// class to manage users from the database
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// in your routes.php file

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// other UserController routes...

Flight::start();
```

Você pode ver no código acima que estamos criando um novo objeto `PDO` e passando-o
para nossa classe `UserController`. Isso é bom para uma aplicação pequena, mas à medida que sua
aplicação cresce, você descobrirá que está criando ou passando o mesmo objeto `PDO` 
em múltiplos lugares. É aí que um DIC se torna útil.

Aqui está o mesmo exemplo usando um DIC (usando Dice):
```php

require 'vendor/autoload.php';

// same class as above. Nothing changed
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// create a new container
$container = new \Dice\Dice;

// add a rule to tell the container how to create a PDO object
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// This registers the container handler so Flight knows to use it.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// now we can use the container to create our UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

Aposto que você pode estar pensando que houve muito código extra adicionado ao exemplo.
A mágica vem quando você tem outro controlador que precisa do objeto `PDO`.

```php

// If all your controllers have a constructor that needs a PDO object
// each of the routes below will automatically have it injected!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

O bônus adicional de utilizar um DIC é que os testes unitários se tornam muito mais fáceis. Você pode
criar um objeto mock e passá-lo para sua classe. Isso é um grande benefício quando você está
escrevendo testes para sua aplicação!

### Criando um manipulador DIC centralizado

Você pode criar um manipulador DIC centralizado no seu arquivo de serviços estendendo sua app. Aqui está um exemplo:

```php
// services.php

// create a new container
$container = new \Dice\Dice;
// don't forget to reassign it to itself like below!
$container = $container->addRule('PDO', [
	// shared means that the same object will be returned each time
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// now we can create a mappable method to create any object. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// This registers the container handler so Flight knows to use it for controllers/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// lets say we have the following sample class that takes a PDO object in the constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// code that sends an email
	}
}

// And finally you can create objects using dependency injection
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight tem um plugin que fornece um contêiner simples compatível com PSR-11 que você pode usar para gerenciar
sua injeção de dependência. Aqui está um exemplo rápido de como usá-lo:

```php

// index.php for example
require 'vendor/autoload.php';

use flight\Container;

$container = new Container;

$container->set(PDO::class, fn(): PDO => new PDO('sqlite::memory:'));

Flight::registerContainerHandler([$container, 'get']);

class TestController {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function index() {
    var_dump($this->pdo);
	// will output this correctly!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Uso Avançado de flightphp/container

Você também pode resolver dependências recursivamente. Aqui está um exemplo:

```php
<?php

require 'vendor/autoload.php';

use flight\Container;

class User {}

interface UserRepository {
  function find(int $id): ?User;
}

class PdoUserRepository implements UserRepository {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function find(int $id): ?User {
    // Implementation ...
    return null;
  }
}

$container = new Container;

$container->set(PDO::class, static fn(): PDO => new PDO('sqlite::memory:'));
$container->set(UserRepository::class, PdoUserRepository::class);

$userRepository = $container->get(UserRepository::class);
var_dump($userRepository);

/*
object(PdoUserRepository)#4 (1) {
  ["pdo":"PdoUserRepository":private]=>
  object(PDO)#3 (0) {
  }
}
 */
```

### DICE

Você também pode criar seu próprio manipulador DIC. Isso é útil se você tiver um contêiner personalizado
que deseja usar que não é PSR-11 (Dice). Veja a 
[seção de uso básico](#uso-básico) para como fazer isso.

Além disso, há
alguns padrões úteis que facilitarão sua vida ao usar Flight.

#### Instância Engine

Se você estiver usando a instância `Engine` em seus controladores/middleware, aqui está
como você configuraria:

```php

// Somewhere in your bootstrap file
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// This is where you pass in the instance
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Now you can use the Engine instance in your controllers/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Adicionando Outras Classes

Se você tiver outras classes que deseja adicionar ao contêiner, com Dice é fácil, pois elas serão resolvidas automaticamente pelo contêiner. Aqui está um exemplo:

```php

$container = new \Dice\Dice;
// If you don't need to inject any dependencies into your classes
// you don't need to define anything!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'thing';
	}
}

class UserController {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/user', 'UserController->index');
```

### PSR-11

Flight também pode usar qualquer contêiner compatível com PSR-11. Isso significa que você pode usar qualquer
contêiner que implemente a interface PSR-11. Aqui está um exemplo usando o contêiner PSR-11 da League:

```php

require 'vendor/autoload.php';

// same UserController class as above

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('user')
	->addArgument('pass');
Flight::registerContainerHandler($container);

Flight::route('/user', [ 'UserController', 'view' ]);

Flight::start();
```

Isso pode ser um pouco mais verboso do que o exemplo anterior com Dice, mas ainda
cumpre o trabalho com os mesmos benefícios!

## Veja Também
- [Extending Flight](/learn/extending) - Aprenda como você pode adicionar injeção de dependência às suas próprias classes estendendo o framework.
- [Configuration](/learn/configuration) - Aprenda como configurar Flight para sua aplicação.
- [Routing](/learn/routing) - Aprenda como definir rotas para sua aplicação e como a injeção de dependência funciona com controladores.
- [Middleware](/learn/middleware) - Aprenda como criar middleware para sua aplicação e como a injeção de dependência funciona com middleware.

## Solução de Problemas
- Se você estiver tendo problemas com seu contêiner, certifique-se de que está passando os nomes de classe corretos para o contêiner.

## Changelog
- v3.7.0 - Adicionada a capacidade de registrar um manipulador DIC no Flight.