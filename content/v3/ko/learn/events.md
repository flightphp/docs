# Flight PHP의 이벤트 시스템 (v3.15.0+)

Flight PHP는 애플리케이션에서 사용자 정의 이벤트를 등록하고 트리거할 수 있는 경량화되고 직관적인 이벤트 시스템을 소개합니다. `Flight::onEvent()` 및 `Flight::triggerEvent()`의 추가로, 이제 애플리케이션의 주요 생명 주기 순간에 연결하거나 코드를 더욱 모듈화하고 확장할 수 있도록 자체 이벤트를 정의할 수 있습니다. 이러한 메서드는 Flight의 **구성 가능한 메서드**의 일부로, 필요에 따라 동작을 재정의할 수 있습니다.

이 가이드는 이벤트와 관련하여 알아야 할 모든 것을 다루며, 그 가치, 사용 방법 및 초보자가 그 힘을 이해하도록 돕는 실제 예제를 포함합니다.

## 왜 이벤트를 사용할까요?

이벤트는 애플리케이션의 서로 다른 부분을 분리하여 서로에게 지나치게 의존하지 않도록 할 수 있습니다. 이러한 분리는 종종 **디커플링**이라고 불리며, 코드 업데이트, 확장 또는 디버깅을 쉽게 만듭니다. 모든 것을 하나의 큰 덩어리로 작성하는 대신, 특정 행동(이벤트)에 반응하는 더 작고 독립적인 조각으로 논리를 분리할 수 있습니다.

블로그 앱을 만들고 있다고 상상해보세요:
- 사용자가 댓글을 게시할 때, 다음을 수행할 수 있습니다:
  - 댓글을 데이터베이스에 저장합니다.
  - 블로그 소유자에게 이메일을 보냅니다.
  - 보안을 위해 작업을 기록합니다.

이벤트 없이 모든 작업을 하나의 함수에 밀어넣어야 합니다. 이벤트가 있으면 이들을 나누어 하나의 파트가 댓글을 저장하고, 또 다른 파트가 `'comment.posted'`와 같은 이벤트를 트리거하며, 별도의 리스너가 이메일과 로깅을 처리합니다. 이를 통해 코드를 더 깔끔하게 유지하고, 코어 논리를 건드리지 않고도 알림과 같은 기능을 추가하거나 제거할 수 있습니다.

### 일반적인 용도
- **로깅**: 로그인 또는 오류와 같은 작업을 기록하여 기본 코드를 혼잡하게 만들지 않습니다.
- **알림**: 어떤 일이 발생할 때 이메일이나 경고를 보냅니다.
- **업데이트**: 캐시를 새로 고치거나 다른 시스템에 변경 사항을 알립니다.

## 이벤트 리스너 등록

이벤트를 듣기 위해 `Flight::onEvent()`를 사용하세요. 이 메서드는 이벤트가 발생할 때 어떤 일이 발생해야 하는지를 정의할 수 있게 해줍니다.

### 구문
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: 이벤트 이름 (예: `'user.login'`).
- `$callback`: 이벤트가 트리거될 때 실행할 함수.

### 작동 방식
이벤트에 "구독"하려면 Flight에 발생했을 때 수행할 작업을 알립니다. 콜백은 이벤트 트리거에서 전달된 인수를 받을 수 있습니다.

Flight의 이벤트 시스템은 동기식으로, 이는 각 이벤트 리스너가 순차적으로 실행됨을 의미합니다. 이벤트를 트리거할 때, 해당 이벤트에 등록된 모든 리스너가 완료될 때까지 실행됩니다. 이는 비동기 이벤트 시스템과 다르기 때문에 이해하는 것이 중요합니다.

### 간단한 예제
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```
여기서 `'user.login'` 이벤트가 트리거되면 사용자의 이름으로 인사합니다.

### 주요 사항
- 같은 이벤트에 여러 리스너를 추가할 수 있으며, 등록한 순서대로 실행됩니다.
- 콜백은 함수, 익명 함수 또는 클래스의 메서드가 될 수 있습니다.

## 이벤트 트리거하기

이벤트가 발생하도록 하려면 `Flight::triggerEvent()`를 사용하세요. 이 메서드는 Flight에 등록된 모든 리스너를 실행하도록 지시하며, 제공하는 데이터를 전달합니다.

### 구문
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: 트리거하려는 이벤트 이름(등록된 이벤트와 일치해야 함).
- `...$args`: 리스너에 전달할 선택적 인수 (아무 개수의 인수가 될 수 있음).

### 간단한 예제
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
이것은 `'user.login'` 이벤트를 트리거하고, 이전에 정의한 리스너에 `'alice'`를 전달하여 `Welcome back, alice!`를 출력합니다.

### 주요 사항
- 리스너가 등록되지 않으면 아무 일도 발생하지 않습니다—당신의 앱은 깨지지 않습니다.
- 스프레드 연산자(`...`)를 사용하여 여러 인수를 유연하게 전달합니다.

### 이벤트 리스너 등록

...

**추가 리스너 중지**:
리스너가 `false`를 반환하면 해당 이벤트의 추가 리스너는 실행되지 않습니다. 이는 특정 조건에 따라 이벤트 체인을 중단할 수 있게 해줍니다. 리스너의 순서가 중요하며, `false`를 반환하는 첫 번째 리스너가 나머지 실행을 중지합니다.

**예제**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 후속 리스너 중단
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // 이는 결코 보내지지 않음
});
```

## 이벤트 메서드 재정의

`Flight::onEvent()` 및 `Flight::triggerEvent()`는 [확장 가능](/learn/extending)하며, 작동 방식을 재정의할 수 있습니다. 이는 로그 추가나 이벤트 배포 방식을 변경하고 싶어하는 고급 사용자를 위한 훌륭한 기능입니다.

### 예제: `onEvent` 사용자 정의
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // 모든 이벤트 등록 로그
    error_log("새 이벤트 리스너가 추가되었습니다: $event");
    // 기본 동작 호출 (내부 이벤트 시스템 가정)
    Flight::_onEvent($event, $callback);
});
```
이제 이벤트를 등록할 때마다 로그를 남기고 진행합니다.

### 왜 재정의할까요?
- 디버깅 또는 모니터링 추가.
- 특정 환경에서 이벤트 제한 (예: 테스트 중 비활성화).
- 다른 이벤트 라이브러리와 통합.

## 이벤트를 어디에 두어야 하나요

초보자로서 궁금할 수 있습니다: *내 앱에서 모든 이벤트를 어디에 등록해야 할까요?* Flight의 단순함은 엄격한 규칙이 없다는 의미입니다—프로젝트에 적합한 곳에 배치하면 됩니다. 그러나 정리된 상태를 유지하는 것은 앱이 성장함에 따라 코드를 유지 관리하는 데 도움이 됩니다. 다음은 Flight의 경량 속성에 맞춘 몇 가지 실제 선택과 모범 사례입니다.

### 옵션 1: 기본 `index.php`에
작은 앱이나 빠른 프로토타입의 경우 `index.php` 파일 내에서 경로와 함께 이벤트를 등록할 수 있습니다. 이것은 모든 것을 한 곳에 두어 단순함이 우선일 때 잘 작동합니다.

```php
require 'vendor/autoload.php';

// 이벤트 등록
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// 라우트 정의
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **장점**: 간단함, 추가 파일 없음, 작은 프로젝트에 적합.
- **단점**: 앱이 커지면서 더 많은 이벤트와 경로가 생기면 혼잡해질 수 있음.

### 옵션 2: 별도의 `events.php` 파일
약간 더 큰 앱의 경우 이벤트 등록을 `app/config/events.php`와 같은 전용 파일로 이동하는 것을 고려하세요. 이 파일을 `index.php`에서 라우트 전에 포함하세요. 이는 Flight 프로젝트에서 라우트가 종종 `app/config/routes.php`에 조직되는 방식과 유사합니다.

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
- **장점**: `index.php`를 라우팅에 집중하게 하고, 이벤트를 논리적으로 조직하여 찾고 편집하기 쉬움.
- **단점**: 약간의 구조를 추가하며, 매우 작은 앱에서는 과한 느낌이 들 수 있음.

### 옵션 3: 트리거되는 위치 가까이
또 다른 접근법은 이벤트가 트리거되는 곳 근처에 이벤트를 등록하는 것입니다. 예를 들어, 컨트롤러나 경로 정의 내부에 배치하는 것입니다. 이 방법은 이벤트가 앱의 특정 부분에만 해당할 경우 잘 작동합니다.

```php
Flight::route('/signup', function () {
    // 여기에서 이벤트 등록
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **장점**: 연관된 코드를 함께 유지, 격리된 기능에 좋음.
- **단점**: 이벤트 등록이 흩어질 수 있어 모든 이벤트를 한눈에 보기 어려워지고 조심하지 않으면 중복 등록 위험도 있음.

### Flight를 위한 모범 사례
- **간단히 시작**: 작은 앱의 경우 `index.php`에 이벤트를 두세요. 빠르고 Flight의 최소화와 일치합니다.
- **스마트하게 성장**: 앱이 확장될 때(예: 이벤트가 5-10개 이상), `app/config/events.php` 파일을 사용하세요. 이는 라우트를 조직하는 것처럼 자연스러운 단계이며, 복잡한 프레임워크 없이 코드를 깔끔하게 유지합니다.
- **과도한 공학 피하기**: 앱이 커지지 않는 한 전체 "이벤트 관리자" 클래스나 디렉토리를 만들지 마세요—Flight는 단순함을 중시하므로 경량으로 유지하세요.

### 팁: 목적별 그룹화
`events.php`에서 관련 이벤트(예: 모든 사용자 관련 이벤트)를 주석으로 그룹화하여 명확성을 유지하세요:

```php
// app/config/events.php
// 사용자 이벤트
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// 페이지 이벤트
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

이 구조는 잘 확장되고 초보자 친화적입니다.

## 초보자를 위한 예제

이벤트가 어떻게 작동하고 왜 도움이 되는지를 보여주기 위해 몇 가지 실제 시나리오를 살펴보겠습니다.

### 예제 1: 사용자 로그인 로깅
```php
// 1단계: 리스너 등록
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// 2단계: 앱에서 트리거
Flight::route('/login', function () {
    $username = 'bob'; // 폼에서 오는 것처럼 가정
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**유용한 이유**: 로그인 코드는 로깅에 대해 알 필요가 없습니다—그저 이벤트를 트리거할 뿐입니다. 나중에 더 많은 리스너(예: 환영 이메일 전송)를 추가할 수 있으며 라우트를 변경할 필요가 없습니다.

### 예제 2: 신규 사용자 알림
```php
// 신규 등록 리스너
Flight::onEvent('user.registered', function ($email, $name) {
    // 이메일 전송 시뮬레이션
    echo "Email sent to $email: Welcome, $name!";
});

// 누군가가 가입할 때 트리거
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**유용한 이유**: 가입 로직은 사용자를 생성하는 데 집중하고, 이벤트는 알림을 처리합니다. 나중에 더 많은 리스너(예: 가입 기록)를 추가할 수 있습니다.

### 예제 3: 캐시 지우기
```php
// 캐시 지우기 리스너
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // 적용 가능한 경우 세션 캐시 지우기
    echo "Cache cleared for page $pageId.";
});

// 페이지가 편집될 때 트리거
Flight::route('/edit-page/(@id)', function ($pageId) {
    // 페이지가 업데이트되었다고 가정
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**유용한 이유**: 편집 코드는 캐시에 대해 알 필요가 없습니다—그저 업데이트를 신호 보낼 뿐입니다. 앱의 다른 부분은 필요에 따라 반응할 수 있습니다.

## 모범 사례

- **이벤트 명확하게 이름 짓기**: `'user.login'` 또는 `'page.updated'`와 같은 구체적인 이름을 사용하여 어떤 일을 하는지 명확하게 하세요.
- **리스너를 간단하게 유지**: 느리거나 복잡한 작업을 리스너에 두지 마세요—앱을 빠르게 유지하세요.
- **이벤트 테스트**: 수동으로 트리거하여 리스너가 예상대로 작동하는지 확인하세요.
- **이벤트를 현명하게 사용하세요**: 이벤트는 디커플링에 훌륭하지만, 너무 많이 사용하면 코드를 이해하기 어려워질 수 있습니다—상황에 맞게 사용하세요.

`Flight::onEvent()`과 `Flight::triggerEvent()`를 통해 Flight PHP의 이벤트 시스템은 유연한 애플리케이션 구축을 위한 단순하면서도 강력한 방법을 제공합니다. 애플리케이션의 서로 다른 부분이 이벤트를 통해 소통할 수 있도록 하여 코드를 조직적이고 재사용 가능하며 쉽게 확장할 수 있도록 합니다. 로그 작업 수행, 알림 전송 또는 업데이트 관리와 관계없이 이벤트는 논리를 얽히게 하지 않고 이를 처리하는 데 도움을 줍니다. 또한 이러한 메서드를 재정의 할 수 있는 능력을 통해 필요에 맞게 시스템을 조정할 수 있는 자유를 누릴 수 있습니다. 단일 이벤트로 작게 시작하고, 그것이 앱 구조를 어떻게 변형시키는지 지켜보세요!

## 내장 이벤트

Flight PHP는 프레임워크의 생명 주기에서 특정 시점에 후킹할 수 있는 몇 가지 내장 이벤트를 제공합니다. 이러한 이벤트는 요청/응답 주기의 특정 지점에서 트리거되어 특정 작업이 발생할 때 사용자 정의 논리를 실행할 수 있게 해줍니다.

### 내장 이벤트 목록
- `flight.request.received`: 요청이 수신, 구문 분석 및 처리될 때 트리거됨.
- `flight.route.middleware.before`: 이전 미들웨어가 실행된 후 트리거됨.
- `flight.route.middleware.after`: 이후 미들웨어가 실행된 후 트리거됨.
- `flight.route.executed`: 라우트가 실행되고 처리된 후 트리거됨.
- `flight.response.sent`: 응답이 클라이언트에 전송된 후 트리거됨.