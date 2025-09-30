# 의존성 주입 컨테이너

## 개요

의존성 주입 컨테이너(DIC)는 애플리케이션의 의존성을 관리할 수 있게 해주는 강력한 확장 기능입니다.

## 이해하기

의존성 주입(DI)은 현대 PHP 프레임워크의 핵심 개념으로, 객체의 인스턴스화와 구성을 관리하는 데 사용됩니다. DIC 라이브러리의 예로는 [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), [league/container](https://container.thephpleague.com/) 등이 있습니다.

DIC는 클래스 생성과 관리를 중앙화된 위치에서 허용하는 멋진 방법입니다. 이는 동일한 객체를 여러 클래스(예: 컨트롤러나 미들웨어)에 전달해야 할 때 유용합니다.

## 기본 사용법

기존 방식은 다음과 같을 수 있습니다:
```php

require 'vendor/autoload.php';

// 데이터베이스에서 사용자 관리 클래스
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

// routes.php 파일에서

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// 다른 UserController 라우트...

Flight::start();
```

위 코드에서 새로운 `PDO` 객체를 생성하고 `UserController` 클래스에 전달하는 것을 볼 수 있습니다. 작은 애플리케이션에서는 괜찮지만, 애플리케이션이 성장함에 따라 동일한 `PDO` 객체를 여러 곳에서 생성하거나 전달해야 한다는 것을 알게 될 것입니다. 여기서 DIC가 유용합니다.

Dice를 사용한 DIC 예제는 다음과 같습니다:
```php

require 'vendor/autoload.php';

// 위와 동일한 클래스. 변경 없음
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

// 컨테이너가 PDO 객체를 생성하는 방법을 알려주는 규칙 추가
// 아래처럼 자신에게 재할당하는 것을 잊지 마세요!
$container = $container->addRule('PDO', [
	// shared는 동일한 객체가 매번 반환된다는 의미
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Flight가 이를 사용하도록 컨테이너 핸들러 등록
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 이제 컨테이너를 사용하여 UserController 생성
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

이 예제에 많은 추가 코드가 들어간 것 같다고 생각할 수 있습니다.
마법은 `PDO` 객체가 필요한 다른 컨트롤러가 있을 때 발생합니다.

```php

// 모든 컨트롤러가 PDO 객체를 필요로 하는 생성자를 가진다면
// 아래 라우트들은 자동으로 주입됩니다!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

DIC를 사용하는 추가 이점은 단위 테스트가 훨씬 쉬워진다는 것입니다. 모의 객체를 생성하여 클래스에 전달할 수 있습니다. 이는 애플리케이션 테스트를 작성할 때 큰 이점입니다!

### 중앙화된 DIC 핸들러 생성

앱을 [확장](/learn/extending)하여 services 파일에서 중앙화된 DIC 핸들러를 생성할 수 있습니다. 예제는 다음과 같습니다:

```php
// services.php

// 새로운 컨테이너 생성
$container = new \Dice\Dice;
// 아래처럼 자신에게 재할당하는 것을 잊지 마세요!
$container = $container->addRule('PDO', [
	// shared는 동일한 객체가 매번 반환된다는 의미
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 이제 모든 객체를 생성하는 매핑 가능한 메서드 생성
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// 컨트롤러/미들웨어에 Flight가 이를 사용하도록 컨테이너 핸들러 등록
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// 생성자에서 PDO 객체를 받는 샘플 클래스라고 가정
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// 이메일 보내는 코드
	}
}

// 마지막으로 의존성 주입을 사용하여 객체 생성
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight에는 의존성 주입을 처리하기 위해 사용할 수 있는 간단한 PSR-11 준수 컨테이너를 제공하는 플러그인이 있습니다. 사용 예제는 다음과 같습니다:

```php

// 예: index.php
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
	// 올바르게 출력됩니다!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### flightphp/container의 고급 사용법

의존성을 재귀적으로 해결할 수도 있습니다. 예제는 다음과 같습니다:

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
    // 구현 ...
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

자신의 DIC 핸들러를 생성할 수도 있습니다. PSR-11이 아닌(Dice) 사용자 지정 컨테이너를 사용하고 싶을 때 유용합니다. 이를 수행하는 방법은 
[기본 사용법](#basic-usage) 섹션을 참조하세요.

또한 Flight를 사용할 때 생활을 더 쉽게 만들어주는 몇 가지 유용한 기본값이 있습니다.

#### Engine 인스턴스

컨트롤러/미들웨어에서 `Engine` 인스턴스를 사용하는 경우, 다음과 같이 구성할 수 있습니다:

```php

// 부트스트랩 파일 어딘가에서
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// 여기서 인스턴스를 전달합니다
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// 이제 컨트롤러/미들웨어에서 Engine 인스턴스 사용 가능

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### 다른 클래스 추가

컨테이너에 추가하고 싶은 다른 클래스가 있다면, Dice에서는 컨테이너가 자동으로 해결해주므로 쉽습니다. 예제는 다음과 같습니다:

```php

$container = new \Dice\Dice;
// 클래스에 의존성을 주입할 필요가 없다면
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

### PSR-11

Flight는 PSR-11 준수 컨테이너를 사용할 수도 있습니다. 이는 PSR-11 인터페이스를 구현하는 모든 컨테이너를 사용할 수 있다는 의미입니다. League의 PSR-11 컨테이너를 사용한 예제는 다음과 같습니다:

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

이전 Dice 예제보다 약간 장황할 수 있지만, 동일한 이점으로 작업을 수행합니다!

## 관련 자료
- [Flight 확장](/learn/extending) - 프레임워크를 확장하여 자신의 클래스에 의존성 주입을 추가하는 방법을 배우세요.
- [구성](/learn/configuration) - 애플리케이션에 Flight를 구성하는 방법을 배우세요.
- [라우팅](/learn/routing) - 애플리케이션의 라우트를 정의하는 방법과 컨트롤러와의 의존성 주입 작동 방식을 배우세요.
- [미들웨어](/learn/middleware) - 애플리케이션에 미들웨어를 생성하는 방법과 미들웨어와의 의존성 주입 작동 방식을 배우세요.

## 문제 해결
- 컨테이너에 문제가 있다면, 컨테이너에 올바른 클래스 이름을 전달하는지 확인하세요.

## 변경 로그
- v3.7.0 - Flight에 DIC 핸들러 등록 기능을 추가했습니다.