# FlightPHP 세션 - 경량 파일 기반 세션 핸들러

이것은 [Flight PHP Framework](https://docs.flightphp.com/)를 위한 경량 파일 기반 세션 핸들러 플러그인입니다. 세션 관리를 위한 간단하면서도 강력한 솔루션을 제공하며, 비차단 세션 읽기, 선택적 암호화, 자동 커밋 기능 및 개발을 위한 테스트 모드와 같은 기능이 포함되어 있습니다. 세션 데이터는 파일에 저장되어 데이터베이스가 필요 없는 애플리케이션에 이상적입니다.

데이터베이스를 사용하고자 한다면, 데이터베이스 백엔드를 사용하면서도 많은 유사한 기능을 가진 [ghostff/session](/awesome-plugins/ghost-session) 플러그인을 확인해 보세요.

전체 소스 코드와 세부 정보는 [Github 저장소](https://github.com/flightphp/session)를 방문하세요.

## 설치

Composer를 통해 플러그인을 설치하세요:

```bash
composer require flightphp/session
```

## 기본 사용법

다음은 Flight 애플리케이션에서 `flightphp/session` 플러그인을 사용하는 간단한 예시입니다:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// 세션 서비스 등록
$app->register('session', Session::class);

// 세션 사용 예시가 있는 경로
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // 출력: johndoe
    echo $session->get('preferences', 'default_theme'); // 출력: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => '사용자가 로그인했습니다!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // 모든 세션 데이터 지우기
    Flight::json(['message' => '성공적으로 로그아웃했습니다']);
});

Flight::start();
```

### 주요 포인트
- **비차단**: 기본적으로 `read_and_close`를 사용해 세션 시작, 세션 잠금 문제를 예방합니다.
- **자동 커밋**: 기본값으로 활성화되어 있어 비활성화하지 않는 한 종료 시 자동으로 변경 사항이 저장됩니다.
- **파일 저장소**: 세션은 기본적으로 `/flight_sessions` 아래의 시스템 임시 디렉토리에 저장됩니다.

## 구성

등록 시 옵션 배열을 전달하여 세션 핸들러를 사용자 정의할 수 있습니다:

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // 세션 파일 디렉토리
    'encryption_key' => 'a-secure-32-byte-key-here',   // 암호화 활성화 (AES-256-CBC에 대해 32바이트 권장)
    'auto_commit' => false,                            // 수동 제어를 위해 자동 커밋 비활성화
    'start_session' => true,                           // 자동으로 세션 시작 (기본값: true)
    'test_mode' => false                               // 개발을 위한 테스트 모드 활성화
]);
```

### 구성 옵션
| 옵션              | 설명                                          | 기본값                              |
|-------------------|-----------------------------------------------|-------------------------------------|
| `save_path`       | 세션 파일이 저장되는 디렉토리              | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | AES-256-CBC 암호화를 위한 키 (선택 사항)   | `null` (암호화 없음)                |
| `auto_commit`     | 종료 시 세션 데이터 자동 저장                | `true`                              |
| `start_session`   | 세션을 자동으로 시작                        | `true`                              |
| `test_mode`       | PHP 세션에 영향을 주지 않고 테스트 모드 실행 | `false`                             |
| `test_session_id` | 테스트 모드를 위한 사용자 정의 세션 ID (선택 사항) | 설정하지 않으면 무작위로 생성      |

## 고급 사용법

### 수동 커밋
자동 커밋을 비활성화하면 변경 사항을 수동으로 커밋해야 합니다:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // 변경 사항을 명시적으로 저장
});
```

### 암호화된 세션 보안
민감한 데이터에 대해 암호화를 활성화하세요:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // 자동으로 암호화됨
    echo $session->get('credit_card'); // 검색 시 복호화됨
});
```

### 세션 재생성
보안을 위해 세션 ID를 재생성하세요 (예: 로그인 후):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // 새로운 ID, 데이터 유지
    // 또는
    $session->regenerate(true); // 새로운 ID, 기존 데이터 삭제
});
```

### 미들웨어 예시
세션 기반 인증으로 경로 보호:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => '관리자 패널에 오신 것을 환영합니다']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, '접근 거부');
    }
});
```

이것은 미들웨어에서 사용하는 방법의 간단한 예시일 뿐입니다. 더 심층적인 예시는 [미들웨어](/learn/middleware) 문서를 참조하세요.

## 메서드

`Session` 클래스는 다음 메서드를 제공합니다:

- `set(string $key, $value)`: 세션에 값을 저장합니다.
- `get(string $key, $default = null)`: 값을 검색하며, 키가 존재하지 않을 경우 선택적 기본 값을 제공합니다.
- `delete(string $key)`: 세션에서 특정 키를 제거합니다.
- `clear()`: 모든 세션 데이터를 삭제합니다.
- `commit()`: 현재 세션 데이터를 파일 시스템에 저장합니다.
- `id()`: 현재 세션 ID를 반환합니다.
- `regenerate(bool $deleteOld = false)`: 세션 ID를 재생성하며, 필요 시 이전 데이터를 삭제합니다.

`get()` 및 `id()`를 제외한 모든 메서드는 체인을 위한 `Session` 인스턴스를 반환합니다.

## 이 플러그인을 사용해야 하는 이유?

- **경량**: 외부 종속성이 없으며, 파일만 있습니다.
- **비차단**: 기본적으로 `read_and_close`로 세션 잠금을 회피합니다.
- **안전한**: 민감한 데이터를 위한 AES-256-CBC 암호화를 지원합니다.
- **유연성**: 자동 커밋, 테스트 모드 및 수동 제어 옵션을 제공합니다.
- **Flight 네이티브**: Flight 프레임워크를 위해 특별히 구축되어 있습니다.

## 기술 세부사항

- **저장 형식**: 세션 파일은 `sess_`로 접두사가 붙고 구성된 `save_path`에 저장됩니다. 암호화된 데이터는 `E` 접두사를 사용하고, 일반 텍스트는 `P`를 사용합니다.
- **암호화**: `encryption_key`가 제공될 경우, 세션 쓰기 시 무작위 IV를 사용하는 AES-256-CBC를 사용합니다.
- **가비지 수집**: 만료된 세션을 정리하기 위해 PHP의 `SessionHandlerInterface::gc()`를 구현합니다.

## 기여하기

기여를 환영합니다! [저장소](https://github.com/flightphp/session)를 포크하고, 변경 사항을 적용한 후 풀 요청을 제출하세요. 버그를 신고하거나 기능을 제안하려면 Github 이슈 트래커를 통해 연락하세요.

## 라이선스

이 플러그인은 MIT 라이선스에 따라 라이선스가 부여됩니다. 세부 정보는 [Github 저장소](https://github.com/flightphp/session)를 참조하세요.