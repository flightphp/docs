# 확장 / 컨테이너

Flight은 확장 가능한 프레임워크로 설계되었습니다. 이 프레임워크에는 기본 메소드 및 구성 요소 세트가 함께 제공되지만, 사용자 정의 메소드를 매핑하거나 자체 클래스를 등록하거나 기존 클래스와 메소드를 재정의할 수 있습니다.

## 메소드 매핑

자체 사용자 정의 메소드를 매핑하려면 `map` 함수를 사용합니다:

```php
// 당신의 메소드 매핑
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 사용자 정의 메소드 호출
Flight::hello('Bob');
```

## 클래스 등록 / 컨테이너화

자체 클래스를 등록하려면 `register` 함수를 사용합니다:

```php
// 당신의 클래스 등록
Flight::register('user', User::class);

// 당신의 클래스의 인스턴스 가져오기
$user = Flight::user();
```

등록 메소드를 사용하면 클래스 생성자에 매개변수를 전달할 수도 있습니다. 사용자 정의 클래스를 로드할 때 미리 초기화된 상태로 제공됩니다. 생성자 매개변수를 정의하려면 추가 배열을 전달합니다. 다음은 데이터베이스 연결을 로드하는 예시입니다:

```php
// 생성자 매개변수와 함께 클래스 등록
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 클래스의 인스턴스 가져오기
// 정의된 매개변수로 개체를 만듭니다.
//
// new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
//
$db = Flight::db();
```

추가적인 콜백 매개변수를 전달하면 클래스 생성 후 즉시 실행됩니다. 이를 통해 새로운 객체에 대해 설정 프로시저를 수행할 수 있습니다. 콜백 함수는 새로운 객체의 인스턴스를 나타내는 하나의 매개변수를 받습니다.

```php
// 콜백은 생성된 객체가 전달됩니다.
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를 로드할 때마다 공유된 인스턴스를 받게 됩니다.
새로운 클래스 인스턴스를 가져오려면 매개변수로 `false`를 전달하면 됩니다:

```php
// 클래스의 공유된 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

동일한 이름으로 둘 다 선언하면 매핑된 메소드가 등록된 클래스보다 우선권을 갖습니다.
