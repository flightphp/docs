# Flight PHP의 이벤트 시스템 (v3.15.0+)

Flight PHP는 애플리케이션에서 사용자 정의 이벤트를 등록하고 트리거할 수 있는 경량의 직관적인 이벤트 시스템을 도입합니다. `Flight::onEvent()` 및 `Flight::triggerEvent()`의 추가로, 이제 앱의 생명 주기의 주요 순간에 훅을 걸거나 코드를 보다 모듈화하고 확장 가능하도록 자신만의 이벤트를 정의할 수 있습니다. 이러한 메서드는 Flight의 **맵 가능한 메서드**의 일부로, 필요에 맞게 동작을 재정의할 수 있습니다.

이 가이드는 이벤트를 시작하는 데 필요한 모든 내용을 다룹니다. 여기에는 이벤트가 왜 중요한지, 어떻게 사용하는지, 초보자가 그 힘을 이해하는 데 도움이 되는 실용적인 예제가 포함됩니다.

## 왜 이벤트를 사용해야 할까요?

이벤트는 애플리케이션의 다양한 부분을 분리하여 서로에게 지나치게 의존하지 않도록 허용합니다. 이러한 분리—종종 **디커플링**이라고 불림—는 코드를 업데이트, 확장 또는 디버그하기 쉽게 만듭니다. 모든 것을 하나의 큰 덩어리로 쓰는 대신, 특정 행동(이벤트)에 응답하는 더 작고 독립적인 조각으로 논리를 나눌 수 있습니다.

블로그 앱을 구축하고 있다고 상상해 보세요:
- 사용자가 댓글을 게시할 때, 다음을 원할 수 있습니다:
  - 데이터베이스에 댓글 저장.
  - 블로그 소유자에게 이메일 전송.
  - 보안을 위해 행동 기록.

이벤트 없이 모든 것을 하나의 함수에 담을 수 있지만, 이벤트를 사용하면 나누어 처리할 수 있습니다: 한 부분은 댓글을 저장하고, 다른 부분은 `'comment.posted'`와 같은 이벤트를 트리거하며, 별도의 리스너가 이메일과 로깅을 처리합니다. 이렇게 하면 코드를 깨끗하게 유지하면서 알림과 같은 기능을 추가하거나 제거할 수 있습니다.

### 일반적인 사용 사례
- **로깅**: 로그인이나 오류와 같은 행동을 기록하되, 기본 코드를 혼잡하게 하지 않습니다.
- **알림**: 어떤 일이 발생할 때 이메일이나 경고를 보냅니다.
- **업데이트**: 캐시를 새로 고치거나 다른 시스템에 변경 사항을 알립니다.

## 이벤트 리스너 등록하기

이벤트를 수신하려면 `Flight::onEvent()`를 사용하십시오. 이 메서드는 이벤트가 발생할 때 어떤 일이 일어나야 하는지 정의할 수 있게 해줍니다.

### 구문
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: 이벤트에 대한 이름 (예: `'user.login'`).
- `$callback`: 이벤트가 트리거될 때 실행할 함수입니다.

### 작동 방식
이벤트에 "구독"함으로써 Flight에 일이 발생할 때 무엇을 해야 하는지 알려줍니다. 콜백은 이벤트 트리거에서 전달된 인수를 받을 수 있습니다.

Flight의 이벤트 시스템은 동기식입니다. 즉, 각 이벤트 리스너는 순차적으로 하나씩 실행됩니다. 이벤트를 트리거하면 해당 이벤트에 등록된 모든 리스너가 완료될 때까지 실행됩니다. 이는 비동기 이벤트 시스템과 다르므로 중요한 이해가 필요합니다. 비동기 시스템에서는 리스너가 병렬로 또는 나중에 실행될 수 있습니다.

### 간단한 예
```php
Flight::onEvent('user.login', function ($username) {
    echo "다시 오신 것을 환영합니다, $username!";
});
```
여기서 `'user.login'` 이벤트가 트리거될 때, 사용자의 이름으로 인사합니다.

### 주요 사항
- 동일한 이벤트에 여러 리스너를 추가할 수 있으며, 등록한 순서대로 실행됩니다.
- 콜백은 함수, 익명 함수, 또는 클래스의 메서드일 수 있습니다.

## 이벤트 트리거하기

이벤트를 발생시키려면 `Flight::triggerEvent()`를 사용하세요. 이는 Flight에게 해당 이벤트에 등록된 모든 리스너를 실행하도록 알리며, 제공한 데이터를 전달합니다.

### 구문
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: 트리거할 이벤트 이름(등록된 이벤트와 일치해야 함).
- `...$args`: 리스너에 전송할 선택적 인수(임의의 수의 인수를 보낼 수 있음).

### 간단한 예
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
이것은 `'user.login'` 이벤트를 트리거하고 이전에 정의한 리스너에 `'alice'`를 전달하여 출력합니다: `다시 오신 것을 환영합니다, alice!`.

### 주요 사항
- 리스너가 등록되어 있지 않으면 아무 일도 일어나지 않으며, 앱이 고장 나지 않습니다.
- 스프레드 연산자(`...`)를 사용하여 여러 인수를 유연하게 전달하십시오.

### 이벤트 리스너 등록하기

...

**추가 리스너 중지**:
리스너가 `false`를 반환하면, 해당 이벤트에 대해 추가 리스너가 실행되지 않습니다. 이는 특정 조건에 따라 이벤트 체인을 중지할 수 있습니다. 리스너의 순서가 중요하므로, 첫 번째 리스너가 `false`를 반환하면 나머지가 실행되지 않습니다.

**예**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 후속 리스너 중지
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // 이건 전송되지 않음
});
```

## 이벤트 메서드 재정의하기

`Flight::onEvent()` 및 `Flight::triggerEvent()`는 [확장 가능](/learn/extending)하므로, 작동 방식을 재정의할 수 있습니다. 이는 이벤트 시스템을 사용자 정의하고자 하는 고급 사용자에게 유용합니다. 예를 들어 로깅을 추가하거나 이벤트가 전달되는 방식을 변경할 수 있습니다.

### 예: `onEvent` 사용자 정의
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // 모든 이벤트 등록을 기록합니다
    error_log("추가된 새로운 이벤트 리스너: $event");
    // 기본 동작을 호출합니다 (내부 이벤트 시스템을 가정)
    Flight::_onEvent($event, $callback);
});
```
이제 이벤트를 등록할 때마다 로그가 기록되고 진행됩니다.

### 왜 재정의하나요?
- 디버깅 또는 모니터링 추가.
- 특정 환경에서 이벤트 제한 (예: 테스트 중 비활성화).
- 다른 이벤트 라이브러리와 통합.

## 이벤트를 어디에 두어야 할까요?

초보자로서 궁금할 수 있습니다: *애플리케이션에서 모든 이벤트를 어디에 등록하나요?* Flight의 단순성은 엄격한 규칙이 없음을 의미합니다—프로젝트에 맞는 곳에 두시면 됩니다. 그러나 제외하면 이벤트를 정리하면 코드 유지 관리가 용이해집니다. 다음은 Flight의 경량 특성에 맞춘 몇 가지 실용적인 옵션 및 모범 사례입니다.

### 옵션 1: 메인 `index.php`에
작은 앱이나 빠른 프로토타입의 경우 `index.php` 파일에 이벤트를 등록할 수 있습니다. 이는 단순성이 우선인 경우 모든 것을 한 곳에 유지할 수 있습니다.

```php
require 'vendor/autoload.php';

// 이벤트 등록
Flight::onEvent('user.login', function ($username) {
    error_log("$username의 로그인 시간: " . date('Y-m-d H:i:s'));
});

// 경로 정의
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "로그인되었습니다!";
});

Flight::start();
```
- **장점**: 간단함, 추가 파일 없음, 작은 프로젝트에 적합.
- **단점**: 이벤트와 경로가 많아짐에 따라 복잡해질 수 있음.

### 옵션 2: 별도의 `events.php` 파일
조금 더 큰 앱의 경우, 이벤트 등록을 `app/config/events.php`와 같은 전용 파일로 이동하는 것을 고려하세요. 이 파일을 `index.php`에 경로 정의 전에 포함하십시오. 이는 Flight 프로젝트의 `app/config/routes.php`에서 경로를 구성하는 방식과 유사합니다.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username의 로그인 시간: " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "$email로 이메일 전송: $name님, 환영합니다!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "로그인되었습니다!";
});

Flight::start();
```
- **장점**: `index.php`가 라우팅에 집중하도록 유지, 이벤트를 논리적으로 구성, 쉽게 찾고 수정 가능.
- **단점**: 사소한 구조가 추가됨, 아주 작은 앱에는 과할 수 있음.

### 옵션 3: 발생시키는 위치 근처에
또 다른 접근 방법은 이벤트를 발생시키는 위치, 즉 컨트롤러나 경로 정의 내에서 등록하는 것입니다. 이는 특정 앱의 한 부분에 이벤트가 독립적일 경우 잘 작동합니다.

```php
Flight::route('/signup', function () {
    // 여기에서 이벤트 등록
    Flight::onEvent('user.registered', function ($email) {
        echo "$email로 환영 이메일이 전송되었습니다!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "가입되었습니다!";
});
```
- **장점**: 관련 코드를 함께 유지, 고립된 기능에 적합.
- **단점**: 이벤트 등록이 흩어져 있어 모든 이벤트를 한 번에 보기 어려움; 조심하지 않으면 중복 등록의 위험.

### Flight의 모범 사례
- **단순하게 시작하기**: 아주 작은 앱의 경우 `index.php`에 이벤트를 두세요. 빠르고 Flight의 단순성에 맞아떨어집니다.
- **스마트하게 성장하기**: 앱이 확장됨에 따라(예: 5-10개 이상의 이벤트), `app/config/events.php` 파일을 사용하세요. 라우트를 정리하는 자연스러운 단계이며, 복잡한 프레임워크 없이 코드를 깔끔하게 유지할 수 있습니다.
- **과도한 설계 피하기**: 앱이 거대해지지 않는 한 완전한 “이벤트 관리자” 클래스를 만들지 마십시오—Flight는 단순성에서 발전하는 것이므로 경량화된 것을 유지하세요.

### 팁: 목적별로 그룹화하기
`events.php`에서 관련 이벤트를 그룹화(예: 모든 사용자 관련 이벤트를 함께 두기)하여 명확성을 위한 주석을 추가하세요:

```php
// app/config/events.php
// 사용자 이벤트
Flight::onEvent('user.login', function ($username) {
    error_log("$username 로그인");
});
Flight::onEvent('user.registered', function ($email) {
    echo "$email님을 환영합니다!";
});

// 페이지 이벤트
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

이러한 구조는 잘 확장되며 초보자 친화성을 유지합니다.

## 초보자를 위한 예제

이벤트가 어떻게 작동하는지, 그리고 왜 유용한지 보여주기 위해 몇 가지 실세계 시나리오를 살펴봅시다.

### 예제 1: 사용자 로그인 로깅
```php
// 1단계: 리스너 등록
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username 로그인 시간: $time");
});

// 2단계: 앱에서 트리거
Flight::route('/login', function () {
    $username = 'bob'; // 이건 폼에서 오는 것이라고 가정합니다.
    Flight::triggerEvent('user.login', $username);
    echo "안녕하세요, $username!";
});
```
**이것이 유용한 이유**: 로그인 코드는 로깅에 대해 알 필요가 없으며, 단지 이벤트를 트리거할 뿐입니다. 나중에 더 많은 리스너(예: 환영 이메일 보내기)를 추가해도 경로를 변경할 필요가 없습니다.

### 예제 2: 새로운 사용자 알림
```php
// 새 등록자를 위한 리스너
Flight::onEvent('user.registered', function ($email, $name) {
    // 이메일 발송 시뮬레이션
    echo "$email로 이메일 전송: $name님, 환영합니다!";
});

// 누군가 가입할 때 트리거
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "가입 감사합니다!";
});
```
**이것이 유용한 이유**: 가입 논리는 사용자를 생성하는 것에 집중하며, 이벤트는 알림을 처리합니다. 나중에 더 많은 리스너(예: 가입 기록)를 추가할 수 있습니다.

### 예제 3: 캐시 지우기
```php
// 캐시 지우기 리스너
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // 해당하는 경우 세션 캐시 지우기
    echo "페이지 $pageId의 캐시가 지워졌습니다.";
});

// 페이지가 편집될 때 트리거
Flight::route('/edit-page/(@id)', function ($pageId) {
    // 페이지가 수정되었다고 가정
    Flight::triggerEvent('page.updated', $pageId);
    echo "페이지 $pageId가 업데이트되었습니다.";
});
```
**이것이 유용한 이유**: 편집 코드는 캐시에 대해 알 필요가 없으며, 단지 업데이트를 신호합니다. 앱의 다른 부분은 필요에 따라 반응할 수 있습니다.

## 모범 사례

- **이벤트 이름을 명확하게**: `'user.login'` 또는 `'page.updated'`와 같은 구체적인 이름을 사용하세요. 무엇을 하는지 명확하게 하세요.
- **리스너를 단순하게 유지**: 느리거나 복잡한 작업을 리스너에 두지 마세요—앱을 빠르게 유지하세요.
- **이벤트를 테스트하세요**: 수동으로 트리거하여 리스너가 예상대로 작동하는지 확인하세요.
- **이벤트를 현명하게 사용하세요**: 이벤트는 디커플링에 좋지만, 너무 많으면 코드를 이해하기 어려워질 수 있습니다—의미가 있는 경우에만 사용하세요.

`Flight::onEvent()` 및 `Flight::triggerEvent()`를 통해 Flight PHP의 이벤트 시스템은 유연한 애플리케이션을 구축할 수 있는 간단하면서도 강력한 방법을 제공합니다. 애플리케이션의 서로 다른 부분이 이벤트를 통해 서로 대화할 수 있게 함으로써 코드를 조직적이고 재사용 가능하며 쉽게 확장할 수 있습니다. 행동을 로깅하고, 알림을 전송하거나, 업데이트를 관리하는 데 이벤트를 통해 논리를 얽히지 않게 유지할 수 있습니다. 게다가 이러한 메서드를 재정의할 수 있으므로 필요에 맞게 시스템을 사용자 정의할 수 있습니다. 단일 이벤트로 작게 시작하고 앱 구조가 어떻게 변할 수 있는지 지켜보세요!

## 내장 이벤트

Flight PHP는 프레임워크의 생명주기에서 후킹할 수 있는 몇 가지 내장 이벤트를 제공합니다. 이러한 이벤트는 요청/응답 주기의 특정 지점에서 트리거되어 특정 작업이 발생했을 때 사용자 정의 로직을 실행할 수 있도록 합니다.

### 내장 이벤트 목록
- **flight.request.received**: `function(Request $request)` 요청이 수신, 구문 분석 및 처리될 때 트리거됩니다.
- **flight.error**: `function(Throwable $exception)` 요청 생명 주기 중 오류가 발생할 때 트리거됩니다.
- **flight.redirect**: `function(string $url, int $status_code)` 리디렉션이 시작될 때 트리거됩니다.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 특정 키에 대한 캐시가 확인될 때 트리거됩니다.
- **flight.middleware.before**: `function(Route $route)` before 미들웨어가 실행된 후 트리거됩니다.
- **flight.middleware.after**: `function(Route $route)` after 미들웨어가 실행된 후 트리거됩니다.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 모든 미들웨어가 실행된 후 트리거됩니다.
- **flight.route.matched**: `function(Route $route)` 경로가 일치했지만 아직 실행되지 않았을 때 트리거됩니다.
- **flight.route.executed**: `function(Route $route, float $executionTime)` 경로가 실행되고 처리된 후 트리거됩니다. `$executionTime`은 경로를 실행하는 데 걸린 시간입니다(컨트롤러 호출 등).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` 뷰가 렌더링된 후 트리거됩니다. `$executionTime`은 템플릿을 렌더링하는 데 걸린 시간입니다. **참고: `render` 메서드를 재정의하면 이 이벤트를 재트리거해야 합니다.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` 응답이 클라이언트에 전송된 후 트리거됩니다. `$executionTime`은 응답을 빌드하는 데 소요된 시간입니다.