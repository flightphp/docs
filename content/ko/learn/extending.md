# 확장

Flight는 확장 가능한 프레임워크로 설계되었습니다. 프레임워크에는 기본 메서드 및 구성 요소 세트가 함께 제공되지만, 사용자 정의 메서드를 매핑하거나 자체 클래스를 등록하거나 기존 클래스 및 메서드를 재정의할 수 있습니다.

만약 DIC (의존성 주입 컨테이너)이 필요하다면 [의존성 주입 컨테이너](dependency-injection-container) 페이지로 이동하십시오.

## 메서드 매핑

자체 단순한 사용자 정의 메서드를 매핑하려면 `map` 함수를 사용합니다:

```php
// 메서드 매핑
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 사용자 정의 메서드 호출
Flight::hello('Bob');
```

이는 예상 값이 반환되도록 메서드로 변수를 전달해야 할 때 더 많이 사용됩니다. 아래와 같이 `register()` 메서드를 사용하여 구성을 전달하고 사전 구성된 클래스를 호출하는 것은 구성을 전달하고 사전 구성된 클래스를 호출하는 데 더 적합합니다.

## 클래스 등록

자체 클래스를 등록하고 구성하려면 `register` 함수를 사용합니다:

```php
// 클래스 등록
Flight::register('user', User::class);

// 클래스의 인스턴스 가져오기
$user = Flight::user();
```

`register` 메서드를 통해서도 클래스 생성자에 매개 변수를 전달할 수 있습니다. 따라서 사용자 정의 클래스를 로드할 때 사전 초기화된 클래스로 제공됩니다. 추가 배열을 전달하여 생성자 매개 변수를 정의할 수 있습니다. 여기에 데이터베이스 연결을 로드하는 예제가 있습니다:

```php
// 생성자 매개 변수로 클래스 등록
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 클래스의 인스턴스 가져오기
// 정의된 매개 변수로 객체가 생성됩니다
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 이후 코드에서 필요한 경우, 같은 메서드를 다시 호출하면 됩니다
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

추가 콜백 매개 변수를 전달하면, 클래스 구성 후에 즉시 실행됩니다. 이를 통해 새 객체에 대한 설정 절차를 수행할 수 있습니다. 콜백 함수는 새 객체의 인스턴스를 매개 변수로 사용합니다.

```php
// 생성된 객체가 전달됩니다
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를 로드할 때마다 공유된 인스턴스가 반환됩니다. 클래스의 새 인스턴스를 얻으려면 매개 변수로 `false`를 전달하면 됩니다:

```php
// 클래스의 공유된 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

매핑된 메서드는 등록된 클래스에 우선권을 제공합니다. 동일한 이름으로 둘 다 선언하는 경우, 매핑된 메서드만 호출됩니다.

## 프레임워크 메서드 재정의

Flight를 사용하면 코드를 수정하지 않고도 자신의 필요에 맞게 기본 기능을 재정의할 수 있습니다.

예를 들어, Flight가 URL을 경로와 일치시키지 못할 때 `notFound` 메서드를 호출하여 일반적인 `HTTP 404` 응답을 보냅니다. 이 동작을 `map` 메서드를 사용하여 재정의할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지 표시
  include 'errors/404.html';
});
```

Flight를 사용하면 프레임워크의 핵심 구성 요소를 변경할 수도 있습니다. 예를 들어, 기본 Router 클래스를 사용자 지정 클래스로 대체할 수 있습니다:

```php
// 사용자 정의 클래스를 등록
Flight::register('router', MyRouter::class);

// Flight가 Router 인스턴스를 로드할 때, 사용자의 클래스를 로드합니다
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메서드는 재정의할 수 없습니다. 이를 시도하면 오류가 발생합니다.