# 의존성 주입 컨테이너

## 소개

의존성 주입 컨테이너(DIC)는 응용 프로그램의 의존성을 관리하는 강력한 도구입니다. 이는 현대 PHP 프레임워크의 핵심 개념이며 객체의 인스턴스화 및 구성을 관리하는 데 사용됩니다. 일부 DIC 라이브러리 예시로는: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/), 그리고 [league/container](https://container.thephpleague.com/)이 있습니다.

DIC는 클래스를 중앙 위치에서 생성하고 관리할 수 있게 해준다는 멋진 방법입니다. 이것은 동일한 객체를 여러 클래스(예: 컨트롤러)에 전달해야 할 때 유용합니다. 간단한 예제가 이를 더 잘 이해하게 도와줄 수 있습니다.

## 기본 예제

과거의 방식은 다음과 같이 보일 수 있습니다:
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

위의 코드에서 새로운 `PDO` 객체를 만들고 `UserController` 클래스에 전달하고 있는 것을 볼 수 있습니다. 이는 작은 응용 프로그램에 적합하지만 응용 프로그램이 성장함에 따라 동일한 `PDO` 객체를 여러 곳에서 생성하게 되는 것을 발견할 것입니다. 이것이 DIC가 유용한 이유입니다.

다음은 동일한 예제를 DIC(Dice를 사용하여)를 사용하여 하는 방법입니다:
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
// 아래와 같이 자신에게 다시 할당하는 것을 잊지 마세요!
$container = $container->addRule('PDO', [
	// shared는 동일한 객체가 매번 반환됨을 의미합니다
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 이것은 Flight가 사용하도록 컨테이너 핸들러를 등록합니다.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 이제 컨테이너를 사용하여 UserController를 생성할 수 있습니다
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// 또는 대안적으로 아래처럼 라우트를 정의할 수 있습니다
Flight::route('/user/@id', 'UserController->view');
// 또는
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

아마도 예제에 많은 추가 코드가 추가되었다고 생각할 수 있습니다. 마법은 `PDO` 객체가 필요한 다른 컨트롤러가 있는 경우입니다.

```php

// 모든 컨트롤러가 PDO 객체를 필요로 하는 생성자를 가지고 있다면
// 아래의 각 라우트는 자동으로 주입할 것입니다!!!
Flight::route('/company/@id', 'CompanyController->view');
Flight::route('/organization/@id', 'OrganizationController->view');
Flight::route('/category/@id', 'CategoryController->view');
Flight::route('/settings', 'SettingsController->view');
```

DIC를 활용하면 유닛 테스트가 훨씬 쉬워집니다. 모의 객체를 생성하고 클래스에 전달할 수 있습니다. 이것은 응용 프로그램의 테스트를 작성할 때 엄청난 이점이 됩니다!

## PSR-11

Flight는 PSR-11 호환 컨테이너도 사용할 수 있습니다. 이는 PSR-11 인터페이스를 구현하는 모든 컨테이너를 사용할 수 있다는 것을 의미합니다. 다음은 League의 PSR-11 컨테이너를 사용하는 예제입니다:

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

이는 이전의 Dice 예제보다 조금 더 많은 내용으로 되어 있을 수 있지만 동일한 이점을 유지합니다!

## 사용자 정의 DIC 핸들러

사용자 정의 DIC 핸들러를 만들 수도 있습니다. 이는 PSR-11이 아닌 사용자 지정 컨테이너를 사용하고 싶은 경우에 유용합니다. [기본 예제](#basic-example)를 확인하면 어떻게 하는지 자세히 알 수 있습니다.

게다가, Flight를 사용할 때 일상적으로 사용하면 삶이 더욱 편리해지는 몇 가지 유용한 기본값이 있습니다.

### Engine 인스턴스

컨트롤러/미들웨어에서 `Engine` 인스턴스를 사용 중이라면, 다음과 같이 구성할 수 있습니다:

```php

// 부트스트랩 파일 어딘가에서
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// 여기에 인스턴스를 전달합니다
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

컨테이너에 추가하려는 다른 클래스가 있는 경우, Dice를 사용하면 자동으로 해결될 것입니다. 다음은 예시입니다:

```php

$container = new \Dice\Dice;
// 클래스에 아무것도 주입할 필요가 없는 경우
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