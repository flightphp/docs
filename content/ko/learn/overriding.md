# 오버라이딩

Flight는 코드를 수정하지 않고도 기본 기능을 사용자 정의하는 데 필요한 기능을 제공하여 여러분이 필요에 맞게 재정의할 수 있습니다.

예를 들어, Flight가 URL을 경로와 일치시키지 못하는 경우 `notFound` 메소드를 호출하여 일반적인 `HTTP 404` 응답을 보냅니다. 여러분은 다음과 같이 `map` 메소드를 사용하여 이 동작을 재정의할 수 있습니다:

```php
Flight::map('notFound', function() {
  // 사용자 정의 404 페이지를 표시합니다.
  include 'errors/404.html';
});
```

Flight는 또한 프레임워크의 핵심 구성 요소를 대체할 수 있도록 합니다.
예를 들어, 여러분은 기본 라우터 클래스를 사용자 정의 클래스로 대체할 수 있습니다:

```php
// 사용자 정의 클래스를 등록합니다.
Flight::register('router', MyRouter::class);

// Flight가 Router 인스턴스를 로드할 때 여러분의 클래스를 로드합니다.
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메소드는 재정의할 수 없습니다. 그렇게 시도하면 오류가 발생합니다.