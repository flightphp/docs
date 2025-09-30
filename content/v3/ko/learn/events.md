# 이벤트 관리자

_v3.15.0 기준_

## 개요

이벤트는 애플리케이션에서 사용자 지정 동작을 등록하고 트리거할 수 있게 합니다. `Flight::onEvent()`와 `Flight::triggerEvent()`의 추가로, 앱의 라이프사이클의 주요 순간에 후크하거나 알림 및 이메일과 같은 사용자 지정 이벤트를 정의하여 코드를 더 모듈화하고 확장 가능하게 만들 수 있습니다. 이러한 메서드는 Flight의 [mappable methods](/learn/extending) 일부로, 필요에 맞게 동작을 재정의할 수 있습니다.

## 이해하기

이벤트는 애플리케이션의 서로 다른 부분을 분리하여 서로에게 너무 의존하지 않게 합니다. 이 분리—종종 **디커플링**이라고 불림—는 코드를 업데이트, 확장 또는 디버그하기 쉽게 만듭니다. 모든 것을 하나의 큰 덩어리로 작성하는 대신, 논리를 특정 작업(이벤트)에 응답하는 더 작고 독립적인 조각으로 분할할 수 있습니다.

블로그 앱을 구축한다고 상상해 보세요:
- 사용자가 댓글을 게시할 때, 다음을 원할 수 있습니다:
  - 댓글을 데이터베이스에 저장.
  - 블로그 소유자에게 이메일 보내기.
  - 보안을 위해 작업 로그 기록.

이벤트 없이, 모든 것을 하나의 함수에 넣게 됩니다. 이벤트와 함께라면 분할할 수 있습니다: 한 부분은 댓글을 저장하고, 다른 부분은 `'comment.posted'`와 같은 이벤트를 트리거하며, 별도의 리스너가 이메일과 로깅을 처리합니다. 이는 코드를 더 깨끗하게 유지하고, 코어 논리를 건드리지 않고 기능(예: 알림)을 추가하거나 제거할 수 있게 합니다.

### 일반적인 사용 사례

대부분의 경우, 이벤트는 선택적이지만 시스템의 절대적인 코어 부분이 아닌 것에 적합합니다. 예를 들어 다음은 좋지만, 어떤 이유로 실패하더라도 애플리케이션이 여전히 작동해야 합니다:

- **로깅**: 로그인이나 오류와 같은 작업을 기록하면서 메인 코드를 어지럽히지 않음.
- **알림**: 무언가가 발생할 때 이메일이나 경고 보내기.
- **캐시 업데이트**: 캐시 새로 고침 또는 변경 사항에 대해 다른 시스템 알림.

그러나 비밀번호를 잊었을 때 기능을 생각해 보세요. 이는 코어 기능의 일부여야 하며 이벤트가 아닙니다. 왜냐하면 그 이메일이 발송되지 않으면 사용자가 비밀번호를 재설정하지 못하고 애플리케이션을 사용할 수 없기 때문입니다.

## 기본 사용법

Flight의 이벤트 시스템은 두 가지 주요 메서드 `Flight::onEvent()`(이벤트 리스너 등록)와 `Flight::triggerEvent()`(이벤트 발화)를 중심으로 구축됩니다. 다음과 같이 사용할 수 있습니다:

### 이벤트 리스너 등록

이벤트에 대한 리스닝을 위해 `Flight::onEvent()`를 사용합니다. 이 메서드는 이벤트 발생 시 무엇이 일어나야 하는지 정의할 수 있게 합니다.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: 이벤트 이름 (예: `'user.login'`).
- `$callback`: 이벤트가 트리거될 때 실행할 함수.

Flight에 이벤트 발생 시 무엇을 할지 알려 "구독"합니다. 콜백은 이벤트 트리거에서 전달된 인수를 받을 수 있습니다.

Flight의 이벤트 시스템은 동기적입니다. 즉, 각 이벤트 리스너가 순차적으로 하나씩 실행됩니다. 이벤트를 트리거하면, 해당 이벤트에 등록된 모든 리스너가 완료될 때까지 코드가 계속되지 않습니다. 이는 비동기 이벤트 시스템(리스너가 병렬로 또는 나중에 실행될 수 있음)과 다르기 때문에 이해하는 것이 중요합니다.

#### 간단한 예제
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";

	// you can send an email if the login is from a new location
});
```
여기서 `'user.login'` 이벤트가 트리거되면, 사용자 이름을 이용해 인사하고 필요 시 이메일을 보내는 로직을 포함할 수 있습니다.

> **참고:** 콜백은 함수, 익명 함수 또는 클래스 메서드가 될 수 있습니다.

### 이벤트 트리거

이벤트가 발생하도록 하려면 `Flight::triggerEvent()`를 사용합니다. 이는 해당 이벤트에 등록된 모든 리스너를 실행하고 제공된 데이터를 전달합니다.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: 트리거할 이벤트 이름(등록된 이벤트와 일치해야 함).
- `...$args`: 리스너에 보낼 선택적 인수(임의 수의 인수).

#### 간단한 예제
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
이는 `'user.login'` 이벤트를 트리거하고 `'alice'`를 이전에 정의한 리스너에 보내 출력: `Welcome back, alice!`.

- 등록된 리스너가 없으면 아무 일도 일어나지 않음—앱이 깨지지 않음.
- 여러 인수를 유연하게 전달하기 위해 스프레드 연산자(`...`) 사용.

### 이벤트 중지

리스너가 `false`를 반환하면 해당 이벤트의 추가 리스너가 실행되지 않습니다. 이는 특정 조건에 따라 이벤트 체인을 중지할 수 있게 합니다. 리스너의 순서가 중요하다는 점을 기억하세요. 첫 번째로 `false`를 반환하는 리스너가 나머지를 중지합니다.

**예제**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stops subsequent listeners
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // this is never sent
});
```

### 이벤트 메서드 재정의

`Flight::onEvent()`와 `Flight::triggerEvent()`는 [확장](/learn/extending) 가능하므로, 동작 방식을 재정의할 수 있습니다. 이는 로깅 추가나 이벤트 디스패치 방식 변경과 같은 이벤트 시스템을 사용자 지정하려는 고급 사용자에게 유용합니다.

#### 예제: `onEvent` 사용자 지정
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Log every event registration
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)
    Flight::_onEvent($event, $callback);
});
```
이제 이벤트를 등록할 때마다 로그를 기록한 후 진행합니다.

#### 왜 재정의하나?
- 디버깅이나 모니터링 추가.
- 특정 환경(예: 테스트 중)에서 이벤트 제한.
- 다른 이벤트 라이브러리와 통합.

### 이벤트를 어디에 배치할까

프로젝트에서 이벤트 개념에 익숙하지 않다면, *앱에서 이 모든 이벤트를 어디에 등록하나?*라고 궁금할 수 있습니다. Flight의 단순함 덕분에 엄격한 규칙은 없으며, 프로젝트에 맞게 어디든 배치할 수 있습니다. 그러나 앱이 성장함에 따라 코드를 유지하기 위해 체계적으로 유지하는 것이 도움이 됩니다. Flight의 가벼운 특성에 맞춘 실용적인 옵션과 모범 사례는 다음과 같습니다:

#### 옵션 1: 메인 `index.php`에
작은 앱이나 빠른 프로토타입의 경우, `index.php` 파일에 이벤트 등록과 라우트를 함께 배치할 수 있습니다. 모든 것을 한 곳에 유지하여 단순함을 우선할 때 적합합니다.

```php
require 'vendor/autoload.php';

// Register events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Define routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **장점**: 단순, 추가 파일 없음, 작은 프로젝트에 좋음.
- **단점**: 앱이 성장함에 따라 이벤트와 라우트가 많아지면 어지러워짐.

#### 옵션 2: 별도의 `events.php` 파일
조금 더 큰 앱의 경우, 이벤트 등록을 `app/config/events.php`와 같은 전용 파일로 이동하는 것을 고려하세요. `index.php`에서 라우트 전에 이 파일을 포함합니다. 이는 Flight 프로젝트에서 `app/config/routes.php`에 라우트를 조직하는 방식을 모방합니다.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **장점**: `index.php`를 라우팅에 집중, 이벤트를 논리적으로 조직, 찾고 편집하기 쉬움.
- **단점**: 매우 작은 앱에는 과도한 구조처럼 느껴질 수 있음.

#### 옵션 3: 트리거되는 곳 근처에
또 다른 접근은 컨트롤러나 라우트 정의 내부와 같이 트리거되는 곳 근처에 이벤트를 등록하는 것입니다. 이는 이벤트가 앱의 한 부분에 특정할 때 잘 작동합니다.

```php
Flight::route('/signup', function () {
    // Register event here
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **장점**: 관련 코드를 함께 유지, 고립된 기능에 좋음.
- **단점**: 이벤트 등록이 산재되어 모든 이벤트를 한 번에 보기 어려움; 주의하지 않으면 중복 등록 위험.

#### Flight를 위한 모범 사례
- **단순하게 시작**: 작은 앱의 경우 `index.php`에 이벤트를 배치. Flight의 미니멀리즘에 맞음.
- **스마트하게 성장**: 앱이 확장됨(예: 5-10개 이상의 이벤트)에 `app/config/events.php` 파일 사용. 라우트 조직처럼 자연스러운 단계로, 복잡한 프레임워크 없이 코드를 깔끔하게 유지.
- **과도한 엔지니어링 피하기**: 앱이 거대해지지 않는 한 전체 "이벤트 관리자" 클래스나 디렉토리를 만들지 마세요—Flight는 단순함으로 번성하므로 가볍게 유지.

#### 팁: 목적별 그룹화
`events.php`에서 관련 이벤트(예: 모든 사용자 관련 이벤트)를 함께 그룹화하고 명확성을 위해 주석 사용:

```php
// app/config/events.php
// User Events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Page Events
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

이 구조는 잘 확장되며 초보자 친화적입니다.

### 실제 세계 예제

이벤트가 어떻게 작동하고 왜 유용한지 보여주기 위해 실제 시나리오를 살펴보겠습니다.

#### 예제 1: 사용자 로그인 로깅
```php
// Step 1: Register a listener
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Step 2: Trigger it in your app
Flight::route('/login', function () {
    $username = 'bob'; // Pretend this comes from a form
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**왜 유용한가**: 로그인 코드는 로깅에 대해 알 필요가 없음—그냥 이벤트를 트리거. 나중에 더 많은 리스너(예: 환영 이메일 보내기)를 라우트를 변경하지 않고 추가할 수 있음.

#### 예제 2: 새 사용자 알림
```php
// Listener for new registrations
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email
    echo "Email sent to $email: Welcome, $name!";
});

// Trigger it when someone signs up
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**왜 유용한가**: 가입 논리는 사용자 생성에 집중하고, 이벤트는 알림 처리. 나중에 더 많은 리스너(예: 가입 로그) 추가 가능.

#### 예제 3: 캐시 지우기
```php
// Listener to clear a cache
Flight::onEvent('page.updated', function ($pageId) {
	// if using the flightphp/cache plugin
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Trigger when a page is edited
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**왜 유용한가**: 편집 코드는 캐싱에 신경 쓰지 않음—업데이트 신호만. 앱의 다른 부분이 필요에 따라 반응할 수 있음.

### 모범 사례

- **이벤트 명확히 이름 짓기**: `'user.login'` 또는 `'page.updated'`와 같이 구체적인 이름 사용으로 무엇을 하는지 명확.
- **리스너 단순하게 유지**: 느리거나 복잡한 작업을 리스너에 넣지 말고 앱을 빠르게 유지.
- **이벤트 테스트**: 예상대로 리스너가 작동하는지 수동으로 트리거.
- **이벤트 현명하게 사용**: 디커플링에 좋지만, 너무 많으면 코드 추적이 어려움—필요할 때 사용.

Flight PHP의 이벤트 시스템은 `Flight::onEvent()`와 `Flight::triggerEvent()`로 단순하면서도 강력한 유연한 애플리케이션 구축 방식을 제공합니다. 앱의 다른 부분이 이벤트 통해 서로 소통하게 함으로써 코드를 조직화, 재사용 가능하고 확장하기 쉽게 유지할 수 있습니다. 작업 로깅, 알림 보내기 또는 업데이트 관리 시, 이벤트는 논리를 엉키지 않게 도와줍니다. 게다가 이러한 메서드를 재정의할 수 있어 시스템을 필요에 맞게 조정할 자유가 있습니다. 단일 이벤트로 작게 시작해 앱 구조가 어떻게 변하는지 지켜보세요!

### 내장 이벤트

Flight PHP에는 프레임워크 라이프사이클에 후크할 수 있는 몇 가지 내장 이벤트가 있습니다. 이러한 이벤트는 요청/응답 사이클의 특정 지점에서 트리거되어 특정 작업 발생 시 사용자 지정 논리를 실행할 수 있게 합니다.

#### 내장 이벤트 목록
- **flight.request.received**: `function(Request $request)` 요청이 수신, 파싱 및 처리될 때 트리거.
- **flight.error**: `function(Throwable $exception)` 요청 라이프사이클 중 오류 발생 시 트리거.
- **flight.redirect**: `function(string $url, int $status_code)` 리다이렉트가 시작될 때 트리거.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 특정 키에 대한 캐시 확인 시 트리거, 캐시 히트 또는 미스 여부.
- **flight.middleware.before**: `function(Route $route)` 비포 미들웨어 실행 후 트리거.
- **flight.middleware.after**: `function(Route $route)` 애프터 미들웨어 실행 후 트리거.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 미들웨어가 실행된 후 트리거.
- **flight.route.matched**: `function(Route $route)` 라우트가 매치되었지만 아직 실행되지 않았을 때 트리거.
- **flight.route.executed**: `function(Route $route, float $executionTime)` 라우트가 실행 및 처리된 후 트리거. `$executionTime`은 라우트 실행(컨트롤러 호출 등)에 걸린 시간.
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` 뷰가 렌더링된 후 트리거. `$executionTime`은 템플릿 렌더링에 걸린 시간. **참고: `render` 메서드를 재정의하면 이 이벤트를 다시 트리거해야 함.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` 응답이 클라이언트에 전송된 후 트리거. `$executionTime`은 응답 빌드에 걸린 시간.

## 관련 자료
- [Extending Flight](/learn/extending) - Flight의 코어 기능을 확장하고 사용자 지정하는 방법.
- [Cache](/awesome-plugins/php_file_cache) - 페이지 업데이트 시 이벤트를 사용해 캐시 지우기 예제.

## 문제 해결
- 이벤트 리스너가 호출되지 않으면, 이벤트를 트리거하기 전에 등록했는지 확인. 등록 순서가 중요합니다.

## 변경 로그
- v3.15.0 - Flight에 이벤트 추가.