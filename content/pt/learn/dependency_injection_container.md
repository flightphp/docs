# Contentor de Injeção de Dependência

## Introdução

O Contêiner de Injeção de Dependência (DIC) é uma ferramenta poderosa que permite que você gerencie as dependências da sua aplicação. É um conceito-chave nos frameworks modernos de PHP e é usado para gerenciar a instanciação e configuração de objetos. Alguns exemplos de bibliotecas DIC são: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/) e [league/container](https://container.thephpleague.com/).

Um DIC é uma forma sofisticada de dizer que permite que você crie e gerencie suas classes em um local centralizado. Isso é útil quando você precisa passar o mesmo objeto para múltiplas classes (como seus controladores). Um exemplo simples pode ajudar a entender melhor isso.

## Exemplo Básico

A forma antiga de fazer as coisas pode se parecer com isso:
```php

require 'vendor/autoload.php';

// classe para gerenciar usuários do banco de dados
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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

Dá para ver no código acima que estamos criando um novo objeto `PDO` e passando para nossa classe `UserController`. Isso é bom para uma aplicação pequena, mas à medida que sua aplicação cresce, você verá que está criando o mesmo objeto `PDO` em vários lugares. Aqui é onde um DIC se torna útil.

Aqui está o mesmo exemplo usando um DIC (usando Dice):
```php

require 'vendor/autoload.php';

// mesma classe que acima. Nada mudou
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

// criar um novo contêiner
$container = new \Dice\Dice;
// não se esqueça de reatribuir a ele mesmo como abaixo!
$container = $container->addRule('PDO', [
	// shared significa que o mesmo objeto será retornado sempre
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Isso registra o manipulador do contêiner para que o Flight saiba utilizá-lo.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// agora podemos usar o contêiner para criar nosso UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// ou alternativamente você pode definir a rota assim
Flight::route('/user/@id', 'UserController->view');
// ou
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Aposto que você pode estar pensando que foi adicionado muito código extra ao exemplo. A magia acontece quando você tem outro controlador que necessita do objeto `PDO`.

```php

// Se todos os seus controladores têm um construtor que precisa de um objeto PDO
// cada uma das rotas abaixo terá automaticamente ele injetado!!!
Flight::route('/empresa/@id', 'CompanyController->view');
Flight::route('/organizacao/@id', 'OrganizationController->view');
Flight::route('/categoria/@id', 'CategoryController->view');
Flight::route('/configuracoes', 'SettingsController->view');
```

O benefício adicional de utilizar um DIC é que os testes de unidade se tornam muito mais fáceis. Você pode criar um objeto simulado e passá-lo para sua classe. Isso é um grande benefício ao escrever testes para sua aplicação!

## PSR-11

O Flight também pode utilizar qualquer contêiner compatível com PSR-11. Isso significa que você pode usar qualquer contêiner que implemente a interface PSR-11. Aqui está um exemplo usando o contêiner PSR-11 da League:

```php

require 'vendor/autoload.php';

// mesma classe de UserController como acima

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

Apesar de ser um pouco mais verboso que o exemplo anterior com Dice, ainda assim faz o trabalho com os mesmos benefícios!

## Manipulador de DIC Personalizado

Você também pode criar seu próprio manipulador DIC. Isso é útil se você tiver um contêiner personalizado que deseja usar que não seja PSR-11 (Dice). Veja o [exemplo básico](#exemplo-básico) para saber como fazer isso.

Adicionalmente, existem algumas configurações padrão que facilitarão sua vida ao usar o Flight.

### Instância do Motor

Se você estiver utilizando a instância do `Engine` em seus controladores/middlewares, aqui está como você configuraria:

```php

// Em algum lugar do seu arquivo de inicialização
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Aqui é onde você passa a instância
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Agora você pode usar a instância do Engine em seus controladores/middlewares

class MeuControlador {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}

```

### Adicionando Outras Classes

Se você tiver outras classes que deseja adicionar ao contêiner, com Dice é fácil, pois serão resolvidas automaticamente pelo contêiner. Aqui está um exemplo:

```php

$container = new \Dice\Dice;
// Se você não precisa injetar nada na sua classe
// você não precisa definir nada!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MinhaClassePersonalizada {
	public function parseThing() {
		return 'coisa';
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