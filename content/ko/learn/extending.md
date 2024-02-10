# 확장 / 컨테이너

Flight은 확장 가능한 프레임워크로 설계되었습니다. 이 프레임워크는 일련의 기본 메서드 및 구성 요소가 제공되지만, 사용자 정의 메서드를 매핑하거나 사용자 정의 클래스를 등록하거나 기존 클래스 및 메서드를 재정의할 수 있습니다.

## 메서드 매핑

사용자 정의 간단한 메서드를 매핑하려면 `map` 함수를 사용합니다:

```php
// 메서드 매핑
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 사용자 정의 메서드 호출
Flight::hello('Bob');
```

이는 예상 값을 얻기 위해 변수를 메서드에 전달해야 할 때 더 많이 사용됩니다. 아래와 같이 `register()` 메서드를 사용하는 것은 구성을 전달하고 사전 구성된 클래스를 호출할 때 더 많이 사용됩니다.

## 클래스 등록 / 컨테이너화

사용자 정의 클래스를 등록하고 구성하려면 `register` 함수를 사용합니다:

```php
// 클래스 등록
Flight::register('user', User::class);

// 클래스의 인스턴스 가져오기
$user = Flight::user();
```

등록 메서드는 또한 클래스 생성자에 매개변수를 전달할 수 있습니다. 사용자 지정 클래스를로드할 때 사전 초기화됩니다.
추가 배열을 전달하여 생성자 매개변수를 정의할 수 있습니다.
다음은 데이터베이스 연결을로드하는 예입니다.

```php
// 생성자 매개변수와 함께 클래스 등록
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 클래스의 인스턴스 가져오기
// 정의된 매개변수로 객체가 생성됩니다
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 그리고 코드에서 나중에 필요한 경우, 동일한 메서드를 호출하여 가져올 수 있습니다
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

추가적인 콜백 매개변수를 전달하면 클래스 생성 후 즉시 실행됩니다.
이를 통해 새 객체의 설정 절차를 수행할 수 있습니다. 콜백 함수는 생성된 새 객체의 인스턴스를 가져야 합니다.

```php
// 생성된 객체가 전달될 콜백
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를로드할 때마다 공유 인스턴스가 제공됩니다.
새 클래스의 새 인스턴스를 받으려면 매개변수로 `false`를 전달하면 됩니다:

```php
// 클래스의 공유 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

매핑된 메서드는 등록된 클래스보다 우선합니다. 동일한 이름을 사용하여 둘 다 선언하면 매핑된 메서드만 호출됩니다.

## 재정의

Flight을 통해 기본 기능을 사용자 정의하고 코드를 수정하지 않고 필요에 맞게 재정의할 수 있습니다.

예를 들어, Flight이 URL을 경로에 매핑할 수 없을 때 `notFound` 메서드를 호출하여 일반적인 `HTTP 404` 응답을 보냅니다. 이 동작을 `map` 메서드를 사용하여 재정의할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지 표시
  include 'errors/404.html';
});
```

Flight은 또한 프레임워크의 핵심 구성 요소를 교체할 수 있습니다.
예를 들어 기본 Router 클래스를 사용자 정의 클래스로 교체할 수 있습니다:

```php
// 사용자 정의 클래스 등록
Flight::register('router', MyRouter::class);

// Flight이 Router 인스턴스를로드 할 때 사용자 정의 클래스가 로드됩니다
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메서드는 재정의할 수 없습니다. 그렇게 시도하면 오류가 발생합니다.