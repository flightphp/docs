# 확장

Flight은 확장 가능한 프레임워크로 설계되었습니다. 이 프레임워크에는 기본 메소드와 구성 요소 세트가 함께 제공되지만, 여러분은 자신의 메소드를 매핑하거나, 클래스를 등록하거나, 심지어 기존 클래스와 메소드를 오버라이드할 수 있습니다.

DIC(의존성 주입 컨테이너)을 찾고 있다면 [의존성 주입 컨테이너](dependency-injection-container) 페이지로 이동하세요.

## 메소드 매핑

자신만의 간단한 사용자 정의 메소드를 매핑하려면 `map` 함수를 사용합니다:

```php
// 여러분의 메소드를 매핑
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 여러분의 커스텀 메소드 호출
Flight::hello('Bob');
```

단순한 사용자 정의 메소드를 만들 수는 있지만, PHP에서는 표준 함수를 만드는 것이 좋습니다. 이는 IDE에서 자동 완성 기능을 제공하며 가독성이 더 좋습니다.
위 코드의 동등한 것은 다음과 같습니다:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

여러분의 메소드에 변수를 전달하여 예상 값을 얻고 싶을 때 주로 사용됩니다. 아래와 같이 `register()` 메소드를 사용하는 것은 설정을 전달하고 사전 구성된 클래스를 호출할 때 더 많이 사용됩니다.

## 클래스 등록

자신의 클래스를 등록하고 구성하려면 `register` 함수를 사용합니다:

```php
// 클래스를 등록
Flight::register('user', User::class);

// 여러분의 클래스 인스턴스를 가져옵니다
$user = Flight::user();
```

등록 메소드는 또한 클래스 생성자에 매개 변수를 전달할 수 있습니다. 따라서 사용자 정의 클래스를로드할 때 미리 초기화된 상태로 가져올 수 있습니다.
추가 배열을 전달함으로써 생성자 매개 변수를 정의할 수 있습니다.
아래는 데이터베이스 연결을로드하는 예입니다:

```php
// 생성자 매개 변수로 클래스를 등록
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 여러분의 클래스 인스턴스를 가져옵니다
// 정의된 매개 변수로 객체가 생성됩니다
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 그리고 이후에 나중에 필요한 경우에는 동일한 메소드를 호출하기만 하면 됩니다.
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

추가 콜백 매개 변수를 전달하면 클래스 구성을 바로 생성 후에 실행됩니다.
이를 통해 새로운 객체에 대한 설정 절차를 수행할 수 있습니다. 콜백 함수는 새 객체의 인스턴스를 나타내는 매개 변수 하나를 사용합니다.

```php
// 생성된 객체가 전달됩니다.
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를로드할 때마다 공유된 인스턴스를 얻습니다.
클래스의 새 인스턴스를 얻으려면 단순히 매개 변수로 `false`를 전달하면 됩니다.

```php
// 클래스의 공유 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

주의할 점은 매핑된 메소드가 등록된 클래스보다 우선합니다. 동일한 이름으로 둘 다 선언하는 경우 매핑된 메소드만 호출됩니다.

## 프레임워크 메소드 오버라이딩

Flight을 사용하면 기본 기능을 수정하여 여러분의 요구에 맞게 오버라이드할 수 있습니다.
코드를 수정하지 않고도 사용 가능합니다. 오버라이드할 수 있는 모든 메소드를 [여기](/learn/api)에서 확인할 수 있습니다.

예를 들어, Flight이 URL을 라우트에 매칭할 수 없을 때 `notFound` 메소드를 호출하고 일반적인 `HTTP 404` 응답을 보냅니다. 이 동작을 바꾸려면 `map` 메소드를 사용할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지 표시
  include 'errors/404.html';
});
```

Flight은 또한 프레임워크의 핵심 구성 요소를 교체하는 것을 허용합니다.
예를 들어 기본 Router 클래스를 여러분의 사용자 정의 클래스로 교체할 수 있습니다.

```php
// 사용자 정의 클래스를 등록합니다
Flight::register('router', MyRouter::class);

// Flight이 Router 인스턴스를로드할 때 여러분의 클래스를 로드합니다
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메소드는 오버라이드할 수 없습니다. 시도하면 오류 메시지가 표시됩니다.