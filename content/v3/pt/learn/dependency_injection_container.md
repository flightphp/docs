# Contentor de Injeção de Dependência

## Introdução

O Contentor de Injeção de Dependência (CID) é uma ferramenta poderosa que permite gerenciar
as dependências de sua aplicação. É um conceito-chave nos frameworks PHP modernos e é
usado para gerenciar a instanciação e configuração de objetos. Alguns exemplos de bibliotecas CID
são: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/) e [league/container](https://container.thephpleague.com/).

Um CID é uma forma sofisticada de dizer que permite criar e gerenciar suas classes em um
local centralizado. Isso é útil quando você precisa passar o mesmo objeto para
múltiplas classes (como seus controladores). Um exemplo simples pode ajudar a tornar isso mais claro.

## Exemplo Básico

A maneira antiga de fazer as coisas poderia parecer assim:
```php

require 'vendor/autoload.php';

// classe para gerenciar usuários no banco de dados
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

Você pode ver a partir do código acima que estamos criando um novo objeto `PDO` e passando-o
para nossa classe `UserController`. Isso é bom para uma aplicação pequena, mas à medida que
a aplicação cresce, você descobrirá que está criando o mesmo objeto `PDO` em múltiplos
lugares. É aqui que um CID se torna útil.

Aqui está o mesmo exemplo usando um CID (usando Dice):
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

// criar um novo contentor
$container = new \Dice\Dice;
// não se esqueça de reatribuí-lo a si mesmo como abaixo!
$container = $container->addRule('PDO', [
	// shared significa que o mesmo objeto será retornado toda vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Isso registra o manipulador do contentor para que o Flight saiba usá-lo.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// agora podemos usar o contentor para criar nosso UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// ou alternativamente você pode definir a rota assim
Flight::route('/user/@id', 'UserController->view');
// ou
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Aposto que você pode estar pensando que houve muito código extra adicionado ao exemplo.
A magia acontece quando você tem outro controlador que precisa do objeto `PDO`.

```php

// Se todos os seus controladores têm um construtor que precisa de um objeto PDO
// cada uma das rotas abaixo terá automaticamente ele injetado!!!
Flight::route('/empresa/@id', 'CompanyController->view');
Flight::route('/organizacao/@id', 'OrganizationController->view');
Flight::route('/categoria/@id', 'CategoryController->view');
Flight::route('/configuracoes', 'SettingsController->view');
```

O benefício adicional de utilizar um CID é que os testes unitários se tornam muito mais fáceis. Você pode
criar um objeto falso e passá-lo para sua classe. Isso é um grande benefício ao escrever testes para sua aplicação!

## PSR-11

O Flight também pode usar qualquer contentor compatível com o PSR-11. Isso significa que você pode usar qualquer
contentor que implemente a interface PSR-11. Aqui está um exemplo usando o contentor PSR-11 da League:

```php

require 'vendor/autoload.php';

// mesma classe UserController que acima

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

Embora isso possa ser um pouco mais verboso do que o exemplo anterior com Dice,
ainda faz o trabalho com os mesmos benefícios!

## Manipulador CID Personalizado

Você também pode criar seu próprio manipulador CID. Isso é útil se você tiver um contentor personalizado
que deseja usar que não seja PSR-11 (Dice). Veja o
[exemplo básico](#basic-example) de como fazer isso.

Além disso, existem alguns padrões úteis que facilitarão sua vida ao usar o Flight.

### Instância do Motor

Se você estiver usando a instância `Engine` em seus controladores/funções intermediárias, aqui está
como você configuraria:

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

// Agora você pode usar a instância do Engine em seus controladores/funções intermediárias

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

Se você tem outras classes que deseja adicionar ao contentor, com Dice é fácil, pois elas serão automaticamente resolvidas pelo contentor. Aqui está um exemplo:

```php

$container = new \Dice\Dice;
// Se você não precisa injetar nada em sua classe
// você não precisa definir nada!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MinhaClassePersonalizada {
	public function parseCoisa() {
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