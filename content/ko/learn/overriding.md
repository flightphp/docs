# 재정의

Flight는 코드를 수정하지 않고도 기본 기능을 사용자 정의하여 필요에 맞게 재정의할 수 있도록 합니다.

예를 들어, Flight가 URL을 경로에 매핑할 수 없는 경우, `notFound` 메서드를 호출하여 일반적인 `HTTP 404` 응답을 보냅니다. 이 동작은 `map` 메서드를 사용하여 재정의할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지 표시
  include 'errors/404.html';
});
```

또한 Flight는 프레임워크의 핵심 구성 요소를 교체할 수 있도록 합니다.
예를 들어 기본 Router 클래스를 사용자 정의 클래스로 교체할 수 있습니다:

```php
// 사용자 정의 클래스를 등록
Flight::register('router', MyRouter::class);

// Flight가 Router 인스턴스를 로드할 때, 사용자 정의 클래스가 로드됩니다
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메서드는 재정의할 수 없습니다. 이를 시도하면 오류가 발생합니다.