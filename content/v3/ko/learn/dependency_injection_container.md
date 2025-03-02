# 의존성 주입 컨테이너

## 소개

의존성 주입 컨테이너 (DIC)는 응용 프로그램의 의존성을 관리할 수 있는 강력한 도구입니다. 최신 PHP 프레임워크에서 중요한 개념으로 사용되며 객체의 인스턴스화와 구성을 관리하는 데 사용됩니다. DIC 라이브러리 예시로는 [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/) 및 [league/container](https://container.thephpleague.com/) 등이 있습니다.

DIC는 클래스를 중앙 집중식으로 생성하고 관리할 수 있다는 멋진 방법입니다. 이것은 동일한 객체를 여러 클래스(예: 컨트롤러)에 전달해야 할 때 유용합니다. 간단한 예제가 이를 더 이해하기 쉽게 만들 수 있습니다.

## 기본 예제

과거에는 다음과 같이 작업하였을 수 있습니다:

```php

require 'vendor/autoload.php';

// 데이터베이스에서 사용자를 관리하는 클래스
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

위 코드에서 새로운 `PDO` 객체를 생성하고 해당 객체를 `UserController` 클래스에 전달하는 것을 볼 수 있습니다. 이는 작은 응용 프로그램에 대해서는 괜찮지만 응용 프로그램이 성장함에 따라 동일한 `PDO` 객체를 여러 곳에서 생성해야 한다는 문제가 발생할 수 있습니다. 이때 DIC가 유용합니다.

다음은 DIC(다이스(Dice) 사용)를 사용한 동일한 예제입니다:

```php

require 'vendor/autoload.php';

// 위와 동일한 클래스. 아무것도 변경되지 않았습니다
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

// 새로운 컨테이너 생성
$container = new \Dice\Dice;
// 아래와 같이 다시 할당하는 것을 잊지 마세요!
$container = $container->addRule('PDO', [
	// shared는 매번 동일한 객체가 반환됨을 의미합니다
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 이것은 Flight가 이를 사용하도록 알도록 컨테이너 핸들러를 등록합니다.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 이제 컨테이너를 사용하여 UserController를 생성할 수 있습니다
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// 또는 대안적으로 아래와 같이 라우트를 정의할 수도 있습니다
Flight::route('/user/@id', 'UserController->view');
// 또는
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

이 예제에 추가 코드가 많이 추가되었다고 생각할 수 있습니다. 마법은 `PDO` 객체가 필요한 다른 컨트롤러가 있는 경우에 나타납니다. 

```php

// 모든 컨트롤러가 PDO 객체를 필요로 하는 생성자를 가진 경우
// 아래 라우트마다 자동으로 주입됩니다!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

DIC를 활용하면 단위 테스트가 훨씬 쉬워집니다. 모의 객체를 생성하고 클래스에 전달할 수 있습니다. 응용 프로그램에 대한 테스트를 작성할 때 이것은 거대한 이점이 됩니다!

## PSR-11

Flight는 PSR-11 호환 컨테이너도 사용할 수 있습니다. 이는 PSR-11 인터페이스를 구현하는 어떤 컨테이너든 사용할 수 있다는 것을 의미합니다. 다음은 League의 PSR-11 컨테이너를 사용하는 예제입니다:

```php

require 'vendor/autoload.php';

// 위와 동일한 UserController 클래스

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

이전 Dice 예제보다 조금 더 세부적이지만 동일한 이점을 가지고 작동합니다!

## 사용자 지정 DIC 핸들러

사용자 지정 DIC 핸들러를 만들 수도 있습니다. PSR-11이 아닌 사용자 지정 컨테이너를 사용하려는 경우 유용합니다. [기본 예제](#basic-example)에서 이를 수행하는 방법을 확인하세요.

추가로, Flight를 사용할 때 일반적인 설정을 보다 쉽게 만들어주는 유용한 기본값들도 있습니다.

### Engine 인스턴스

컨트롤러/미들웨어에서 `Engine` 인스턴스를 사용 중이라면 다음과 같이 구성할 수 있습니다:

```php

// 부트스트랩 파일의 어딘가에서
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// 여기에 인스턴스를 전달하세요
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 이제 컨트롤러/미들웨어에서 Engine 인스턴스를 사용할 수 있습니다

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### 다른 클래스 추가

컨테이너에 추가하려는 다른 클래스가 있는 경우, Dice를 사용할 때는 컨테이너에서 자동으로 해결되므로 간단합니다. 다음은 예시입니다:

```php

$container = new \Dice\Dice;
// 클래스에 아무것도 주입할 필요가 없다면
// 아무것도 정의할 필요가 없습니다!
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