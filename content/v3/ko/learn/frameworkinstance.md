# 프레임워크 인스턴스

글로벌 정적 클래스로 Flight를 실행하는 대신 선택적으로 객체 인스턴스로 실행할 수 있습니다.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo '안녕 세계!';
});

$app->start();
```

정적 메서드를 호출하는 대신 Engine 객체의 동일한 이름을 가진 인스턴스 메서드를 호출합니다.