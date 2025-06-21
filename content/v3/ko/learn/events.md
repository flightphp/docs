# 이벤트 시스템 in Flight PHP (v3.15.0+)

Flight PHP는 가벼우면서 직관적인 이벤트 시스템을 도입하여, 애플리케이션에서 사용자 정의 이벤트를 등록하고 트리거할 수 있게 합니다. `Flight::onEvent()`와 `Flight::triggerEvent()`를 추가하여, 앱의 라이프사이클 주요 순간에 연결하거나 자신만의 이벤트를 정의하여 코드를 더 모듈화하고 확장성 있게 만들 수 있습니다. 이러한 메서드는 Flight의 **mappable methods**에 해당하므로, 필요에 따라 동작을 재정의할 수 있습니다.

이 가이드에서는 이벤트의 시작 방법, 그 가치, 사용 방법, 그리고 초보자가 그 힘을 이해할 수 있도록 실용적인 예제를 포함하여 모든 것을 다룹니다.

## 왜 이벤트를 사용하는가?

이벤트는 애플리케이션의 다른 부분을 분리하여 서로 과도하게 의존하지 않게 합니다. 이 분리—종종 **decoupling**이라고 불림—은 코드를 업데이트, 확장하거나 디버그하기 쉽게 만듭니다. 모든 것을 하나의 큰 덩어리로 작성하는 대신, 특정 작업(이벤트)에 응답하는 더 작고 독립적인 부분으로 로직을 분할할 수 있습니다.

블로그 앱을 만드는 것을 상상해 보세요:
- 사용자가 댓글을 게시할 때:
  - 댓글을 데이터베이스에 저장합니다.
  - 블로그 소유자에게 이메일을 보냅니다.
  - 보안을 위해 작업을 기록합니다.

이벤트 없이 모든 것을 하나의 함수에 집어넣어야 합니다. 이벤트가 있으면 이를 분할할 수 있습니다: 한 부분은 댓글을 저장하고, 다른 부분은 `'comment.posted'`와 같은 이벤트를 트리거하며, 별도의 리스너가 이메일과 기록을 처리합니다. 이렇게 하면 코드를 더 깨끗하게 유지하고, 핵심 로직을 건드리지 않고 기능(예: 알림)을 추가하거나 제거할 수 있습니다.

### 일반적인 용도
- **Logging**: 주요 코드에 방해가 되지 않게 로그인이나 오류와 같은 작업을 기록합니다.
- **Notifications**: 무언가가 발생할 때 이메일이나 알림을 보냅니다.
- **Updates**: 캐시를 새로 고치거나 변경 사항에 대해 다른 시스템에 알립니다.

## 이벤트 리스너 등록

이벤트에 대한 리스너를 등록하려면 `Flight::onEvent()`를 사용합니다. 이 메서드를 통해 이벤트가 발생할 때 어떤 작업이 수행되어야 하는지 정의합니다.

### 구문
```php
Flight::onEvent(string $event, callable $callback): void  // $event: 이벤트 이름 (예: 'user.login'). // $callback: 이벤트가 트리거될 때 실행될 함수.
```
- `$event`: 이벤트 이름 (예: `'user.login'`).
- `$callback`: 이벤트가 트리거될 때 실행될 함수.

### 작동 원리
이벤트가 발생할 때 수행할 작업을 Flight에 "구독"하는 것입니다. 콜백은 이벤트 트리거에서 전달된 인수를 받을 수 있습니다.

Flight의 이벤트 시스템은 동기식입니다. 즉, 각 이벤트 리스너는 순서대로 하나씩 실행되며, 이벤트가 트리거되면 등록된 모든 리스너가 완료된 후에 코드가 계속 진행됩니다. 이는 비동기 이벤트 시스템과 다르며, 리스너가 병렬로 실행되거나 나중에 실행되지 않습니다.

### 간단한 예제
```php
Flight::onEvent('user.login', function ($username) {  // 사용자 로그인 이벤트가 트리거될 때 사용자 이름을 출력합니다.
    echo "Welcome back, $username!";
});
```
여기서 `'user.login'` 이벤트가 트리거되면 사용자를 이름으로 인사합니다.

### 주요 포인트
- 동일한 이벤트에 여러 리스너를 추가할 수 있으며, 등록된 순서대로 실행됩니다.
- 콜백은 함수, 익명 함수, 또는 클래스의 메서드가 될 수 있습니다.

## 이벤트 트리거

이벤트가 발생하게 하려면 `Flight::triggerEvent()`를 사용합니다. 이는 해당 이벤트에 등록된 모든 리스너를 실행하고 제공된 데이터를 전달합니다.

### 구문
```php
Flight::triggerEvent(string $event, ...$args): void  // $event: 트리거할 이벤트 이름 (등록된 이벤트와 일치해야 함). // ...$args: 리스너에 전달할 선택적 인수.
```
- `$event`: 트리거할 이벤트 이름 (등록된 이벤트와 일치해야 함).
- `...$args`: 리스너에 전달할 선택적 인수 (인수의 개수 제한 없음).

### 간단한 예제
```php
$username = 'alice';  // 사용자 이름을 설정합니다.
Flight::triggerEvent('user.login', $username);  // 'user.login' 이벤트를 트리거하고 'alice'를 리스너에 전달합니다.
```
이는 `'user.login'` 이벤트를 트리거하고 `'alice'`를 이전에 정의한 리스너에 전달하여 출력: `Welcome back, alice!`.

### 주요 포인트
- 등록된 리스너가 없으면 아무 일도 발생하지 않습니다—앱이 중단되지 않습니다.
- 여러 인수를 유연하게 전달하려면 확산 연산자 (`...`)를 사용합니다.

### 이벤트 리스너 등록

...

**추가 리스너 중지**:
리스너가 `false`를 반환하면 해당 이벤트의 나머지 리스너가 실행되지 않습니다. 이는 특정 조건에 따라 이벤트 체인을 중지할 수 있게 합니다. 리스너의 순서가 중요합니다. 첫 번째로 `false`를 반환하는 리스너가 나머지를 중지합니다.

**예제**:
```php
Flight::onEvent('user.login', function ($username) {  // 사용자가 금지된 경우 로그아웃하고 후속 리스너를 중지합니다.
    if (isBanned($username)) {
        logoutUser($username);
        return false;  // 후속 리스너 중지
    }
});
Flight::onEvent('user.login', function ($username) {  // 이 리스너는 금지된 사용자에게는 실행되지 않습니다.
    sendWelcomeEmail($username);  // 이 부분은 절대 보내지지 않음
});
```

## 이벤트 메서드 재정의

`Flight::onEvent()`와 `Flight::triggerEvent()`는 [extended](/learn/extending)할 수 있으므로, 동작을 재정의할 수 있습니다. 이는 이벤트 시스템을 커스터마이징하고 싶거나 로깅을 추가하거나 디스패치 방식을 변경하고 싶은 고급 사용자에게 유용합니다.

### 예제: `onEvent` 커스터마이징
```php
Flight::map('onEvent', function (string $event, callable $callback) {  // 모든 이벤트 등록을 로깅합니다.
    // Log every event registration  // 이벤트 등록을 로깅합니다.
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)  // 내부 이벤트 시스템의 기본 동작을 호출합니다.
    Flight::_onEvent($event, $callback);
});
```
이제 이벤트가 등록될 때마다 로깅됩니다.

### 왜 재정의하는가?
- 디버깅이나 모니터링을 추가합니다.
- 특정 환경에서 이벤트를 제한합니다 (예: 테스트에서 비활성화).
- 다른 이벤트 라이브러리와 통합합니다.

## 이벤트를 어디에 배치할까

초보자로서, 앱에서 이러한 이벤트를 어디에 등록해야 할지 궁금할 수 있습니다. Flight의 단순함 때문에 엄격한 규칙은 없지만, 이벤트를 체계적으로 유지하면 앱이 성장할 때 코드를 유지보수하기 쉽습니다. 다음은 Flight의 가벼운 특성을 고려한 실용적인 옵션과 최선의 관행입니다:

### 옵션 1: 주요 `index.php`에 배치
작은 앱이나 빠른 프로토타입의 경우, 이벤트를 라우트와 함께 `index.php` 파일에 등록할 수 있습니다. 이는 모든 것을 한 곳에 유지하므로 단순함이 우선시될 때 적합합니다.

```php
require 'vendor/autoload.php';  // 의존성을 로드합니다.

// Register events  // 이벤트를 등록합니다.
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));  // 로그인 시간을 기록합니다.
});

// Define routes  // 라우트를 정의합니다.
Flight::route('/login', function () {
    $username = 'bob';  // 폼에서 온다고 가정합니다.
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **장점**: 간단하고 추가 파일이 없으며, 작은 프로젝트에 적합합니다.
- **단점**: 앱이 커지면 이벤트와 라우트가 많아지면서 복잡해질 수 있습니다.

### 옵션 2: 별도의 `events.php` 파일
조금 더 큰 앱의 경우, 이벤트 등록을 `app/config/events.php`와 같은 전용 파일로 옮기는 것을 고려하세요. 이 파일을 `index.php`에서 라우트 전에 포함합니다. 이는 Flight 프로젝트에서 라우트를 `app/config/routes.php`에 구성하는 방식과 유사합니다.

```php
// app/config/events.php  // 사용자 관련 이벤트를 등록합니다.
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));  // 로그인 시간을 기록합니다.
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";  // 환영 이메일을 보냅니다.
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';  // 이벤트를 로드합니다.

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **장점**: `index.php`를 라우팅에 집중하게 하며, 이벤트를 논리적으로 구성하고 쉽게 찾고 편집할 수 있습니다.
- **단점**: 아주 작은 앱에서는 과도한 구조처럼 느껴질 수 있습니다.

### 옵션 3: 트리거되는 곳 근처에 배치
또 다른 접근법은 이벤트를 트리거되는 곳 근처, 예를 들어 컨트롤러나 라우트 정의 안에 등록하는 것입니다. 이는 앱의 특정 부분에만 관련된 이벤트에 적합합니다.

```php
Flight::route('/signup', function () {  // 회원 가입 라우트에서 이벤트를 등록합니다.
    // Register event here  // 이벤트를 여기에 등록합니다.
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";  // 환영 이메일을 보냅니다.
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **장점**: 관련된 코드를 함께 유지하고, 고립된 기능에 적합합니다.
- **단점**: 이벤트 등록이 흩어지면 모든 이벤트를 한 번에 보기 어려워지며, 주의하지 않으면 중복 등록 위험이 있습니다.

### Flight의 최선의 관행
- **간단하게 시작하세요**: 작은 앱의 경우 `index.php`에 이벤트를 넣으세요. 이는 Flight의 미니멀리즘과 잘 맞습니다.
- **스마트하게 성장하세요**: 앱이 커지면 (예: 5-10개 이상의 이벤트) `app/config/events.php` 파일을 사용하세요. 라우트를 구성하는 것처럼 자연스러운 단계이며, 코드를 깔끔하게 유지합니다.
- **과도한 설계를 피하세요**: 앱이 아주 커지지 않는 한, "이벤트 관리자" 클래스나 디렉터리를 만들지 마세요—Flight는 단순함을 강조합니다.

### 팁: 목적에 따라 그룹화
`events.php`에서 관련된 이벤트를 함께 그룹화하고 (예: 모든 사용자 관련 이벤트를 함께) 명확성을 위해 주석을 추가하세요:

```php
// app/config/events.php  // 사용자 이벤트를 그룹화합니다.
// User Events  // 사용자 이벤트
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");  // 로그인 기록합니다.
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";  // 환영 메시지 전송합니다.
});

// Page Events  // 페이지 이벤트
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // 세션 캐시를 지웁니다.
});
```

이 구조는 잘 확장되며 초보자에게 친근합니다.

## 초보자를 위한 예제

이벤트가 어떻게 작동하고 왜 유용한지 보여주기 위해 실제 시나리오를 살펴보겠습니다.

### 예제 1: 사용자 로그인 기록
```php
// Step 1: Register a listener  // 리스너 등록
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');  // 현재 시간을 가져옵니다.
    error_log("$username logged in at $time");  // 로그를 기록합니다.
});

// Step 2: Trigger it in your app  // 앱에서 트리거합니다.
Flight::route('/login', function () {
    $username = 'bob';  // 폼에서 온다고 가정합니다.
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**왜 유용한가**: 로그인 코드는 기록에 대해 알 필요가 없습니다—단지 이벤트를 트리거합니다. 나중에 더 많은 리스너(예: 환영 이메일)를 추가할 수 있습니다.

### 예제 2: 새 사용자 알림
```php
// Listener for new registrations  // 새 등록 리스너
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email  // 이메일 전송 시뮬레이션
    echo "Email sent to $email: Welcome, $name!";  // 이메일을 보냅니다.
});

// Trigger it when someone signs up  // 회원 가입 시 트리거
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**왜 유용한가**: 회원 가입 로직은 사용자 생성에 집중하고, 이벤트가 알림을 처리합니다. 나중에 더 많은 리스너(예: 로그)를 추가할 수 있습니다.

### 예제 3: 캐시 지우기
```php
// Listener to clear a cache  // 캐시 지우기 리스너
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // 세션 캐시를 지웁니다.
    echo "Cache cleared for page $pageId.";  // 캐시 지운 것을 출력합니다.
});

// Trigger when a page is edited  // 페이지 편집 시 트리거
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page  // 페이지 업데이트를 가정합니다.
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**왜 유용한가**: 편집 코드는 캐시에 대해 신경 쓰지 않습니다—단지 업데이트를 알립니다. 앱의 다른 부분이 필요에 따라 반응할 수 있습니다.

## 최선의 관행

- **이벤트 이름을 명확히 하세요**: `'user.login'`이나 `'page.updated'`처럼 구체적인 이름을 사용하세요.
- **리스너를 간단하게 유지하세요**: 느리거나 복잡한 작업을 리스너에 넣지 마세요—앱의 속도를 유지하세요.
- **이벤트를 테스트하세요**: 수동으로 트리거하여 리스너가 예상대로 작동하는지 확인하세요.
- **이벤트를 현명하게 사용하세요**: 분리를 위해 좋지만, 너무 많으면 코드를 이해하기 어려워질 수 있습니다—필요할 때만 사용하세요.

Flight PHP의 이벤트 시스템, `Flight::onEvent()`와 `Flight::triggerEvent()`를 통해 간단하면서도 강력한 방식으로 유연한 애플리케이션을 구축할 수 있습니다. 이벤트를 통해 앱의 다른 부분이 서로 소통하게 하여 코드를 정리하고 재사용 가능하며 쉽게 확장할 수 있습니다. 작업 기록, 알림 전송, 또는 업데이트 관리 등에서 이벤트를 사용하면 로직이 얽히지 않습니다. 게다가 이러한 메서드를 재정의할 수 있어 시스템을 맞춤형으로 조정할 수 있습니다. 하나의 이벤트부터 시작하여 앱의 구조가 어떻게 변화하는지 지켜보세요!

## 내장 이벤트

Flight PHP는 프레임워크의 라이프사이클 특정 지점에서 연결할 수 있는 몇 가지 내장 이벤트를 제공합니다. 이러한 이벤트는 요청/응답 주기에서 특정 시점에 트리거되어 사용자 정의 로직을 실행할 수 있습니다.

### 내장 이벤트 목록
- **flight.request.received**: `function(Request $request)` 요청이 수신, 파싱 및 처리된 후 트리거됩니다.
- **flight.error**: `function(Throwable $exception)` 요청 라이프사이클 동안 오류가 발생할 때 트리거됩니다.
- **flight.redirect**: `function(string $url, int $status_code)` 리디렉트가 시작될 때 트리거됩니다.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 특정 키에 대한 캐시가 확인되고 히트 여부가 확인된 후 트리거됩니다.
- **flight.middleware.before**: `function(Route $route)` before 미들웨어가 실행된 후 트리거됩니다.
- **flight.middleware.after**: `function(Route $route)` after 미들웨어가 실행된 후 트리거됩니다.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 모든 미들웨어가 실행된 후 트리거됩니다.
- **flight.route.matched**: `function(Route $route)` 라우트가 매치되었으나 아직 실행되지 않은 후 트리거됩니다.
- **flight.route.executed**: `function(Route $route, float $executionTime)` 라우트가 실행되고 처리된 후 트리거됩니다. `$executionTime`은 라우트 실행 시간입니다.
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` 뷰가 렌더링된 후 트리거됩니다. `$executionTime`은 템플릿 렌더링 시간입니다. **Note: If you override the `render` method, you will need to re-trigger this event.**  // render 메서드를 재정의하면 이 이벤트를 다시 트리거해야 합니다.
- **flight.response.sent**: `function(Response $response, float $executionTime)` 응답이 클라이언트에 전송된 후 트리거됩니다. `$executionTime`은 응답 빌드 시간입니다.