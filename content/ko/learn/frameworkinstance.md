# Framework Instance

Flight을 전역 정적 클래스로 실행하는 대신, 옵션으로 객체 인스턴스로 실행할 수 있습니다.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  // 'hello world!'를 출력합니다.
});

$app->start();
```

정적 메소드를 호출하는 대신, 엔진 객체의 동일한 이름을 가진 인스턴스 메소드를 호출합니다.