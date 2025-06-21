# FlightPHP 세션 - 경량 파일 기반 세션 핸들러

이것은 [Flight PHP Framework](https://docs.flightphp.com/)의 경량 파일 기반 세션 핸들러 플러그인입니다. 비동기 세션 읽기, 선택적 암호화, 자동 커밋 기능, 개발을 위한 테스트 모드 등의 기능을 제공하는 간단하면서도 강력한 세션 관리 솔루션입니다. 세션 데이터는 파일에 저장되므로 데이터베이스를 필요하지 않은 애플리케이션에 이상적입니다.

데이터베이스를 사용하고 싶다면, 이와 유사한 기능이 있지만 데이터베이스 백엔드를 가진 [ghostff/session](/awesome-plugins/ghost-session) 플러그인을 확인하세요.

전체 소스 코드와 세부 사항을 위해 [Github 저장소](https://github.com/flightphp/session)를 방문하세요.

## 설치

플러그인을 Composer를 통해 설치하세요:

```bash
composer require flightphp/session
```

## 기본 사용법

Flight 애플리케이션에서 `flightphp/session` 플러그인을 사용하는 간단한 예제입니다:

```php
require 'vendor/autoload.php';

use flight\Session;  // 세션 서비스를 등록합니다

$app = Flight::app();

// 세션 서비스를 등록합니다
$app->register('session', Session::class);

// 세션 사용 예제 라우트
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // johndoe를 출력합니다
    echo $session->get('preferences', 'default_theme'); // default_theme를 출력합니다

    if ($session->get('user_id')) {
        Flight::json(['message' => '사용자가 로그인되었습니다!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear();  // 모든 세션 데이터를 지웁니다
    Flight::json(['message' => '로그아웃되었습니다']);
});

Flight::start();
```

### 주요 포인트
- **비동기**: 기본적으로 `read_and_close`를 사용하여 세션 잠금 문제를 방지합니다.
- **자동 커밋**: 기본으로 활성화되어 종료 시 변경 사항이 자동으로 저장됩니다.
- **파일 저장**: 세션은 기본적으로 `/flight_sessions` 아래 시스템 임시 디렉터리에 저장됩니다.

## 구성

등록할 때 배열 옵션을 전달하여 세션 핸들러를 사용자 정의할 수 있습니다:

```php
// 예, 이중 배열입니다 :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // 세션 파일을 저장할 디렉터리
	'prefix' => 'myapp_',                              // 세션 파일의 접두사
    'encryption_key' => 'a-secure-32-byte-key-here',   // AES-256-CBC 암호화를 활성화 (32바이트 추천)
    'auto_commit' => false,                            // 자동 커밋을 비활성화하여 수동 제어
    'start_session' => true,                           // 세션을 자동으로 시작 (기본: true)
    'test_mode' => false,                              // 개발을 위한 테스트 모드 활성화
    'serialization' => 'json',                         // 직렬화 방법: 'json' (기본) 또는 'php' (레거시)
] ]);
```

### 구성 옵션
| 옵션            | 설명                                      | 기본 값                     |
|-----------------|-------------------------------------------|-----------------------------|
| `save_path`       | 세션 파일이 저장되는 디렉터리         | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | 저장된 세션 파일의 접두사                | `sess_`                           |
| `encryption_key`  | AES-256-CBC 암호화 키 (선택적)        | `null` (암호화 없음)            |
| `auto_commit`     | 종료 시 세션 데이터 자동 저장               | `true`                            |
| `start_session`   | 세션을 자동으로 시작                  | `true`                            |
| `test_mode`       | PHP 세션을 영향을 주지 않는 테스트 모드 실행  | `false`                           |
| `test_session_id` | 테스트 모드의 사용자 정의 세션 ID (선택적)       | 설정되지 않으면 무작위 생성     |
| `serialization`   | 직렬화 방법: 'json' (기본, 안전) 또는 'php' (레거시, 객체 허용) | `'json'` |

## 직렬화 모드

기본적으로 이 라이브러리는 **JSON 직렬화**를 사용하여 세션 데이터를 처리하며, 이는 안전하고 PHP 객체 주입 취약점을 방지합니다. 세션에 PHP 객체를 저장해야 하는 경우 (대부분의 앱에서는 권장되지 않음) 레거시 PHP 직렬화를 선택할 수 있습니다:

- `'serialization' => 'json'` (기본):
  - 세션 데이터에 배열과 기본 형식만 허용합니다.
  - 더 안전: PHP 객체 주입에 면역.
  - 파일은 `J` (일반 JSON) 또는 `F` (암호화된 JSON)로 접두사 붙임.
- `'serialization' => 'php'`:
  - PHP 객체 저장 허용 (주의해서 사용).
  - 파일은 `P` (일반 PHP 직렬화) 또는 `E` (암호화된 PHP 직렬화)로 접두사 붙임.

**노트:** JSON 직렬화를 사용하면 객체를 저장하려고 시도하면 예외가 발생합니다.

## 고급 사용법

### 수동 커밋
자동 커밋을 비활성화하면 변경 사항을 수동으로 커밋해야 합니다:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit();  // 변경 사항을 명시적으로 저장합니다
});
```

### 암호화된 세션 보안
민감한 데이터를 위해 암호화를 활성화하세요:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111');  // 자동으로 암호화됩니다
    echo $session->get('credit_card');  // 검색 시 복호화됩니다
});
```

### 세션 재생성
보안상 세션 ID를 재생성하세요 (예: 로그인 후):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate();  // 새 ID, 데이터 유지
    // 또는
    $session->regenerate(true);  // 새 ID, 기존 데이터 삭제
});
```

### 미들웨어 예제
세션 기반 인증으로 라우트를 보호하세요:

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

이것은 미들웨어에서 사용하는 간단한 예제입니다. 더 자세한 예제는 [middleware](/learn/middleware) 문서를 참조하세요.

## 메서드

`Session` 클래스는 다음 메서드를 제공합니다:

- `set(string $key, $value)`: 세션에 값을 저장합니다.
- `get(string $key, $default = null)`: 값을 검색하며, 키가 존재하지 않으면 기본 값을 사용합니다.
- `delete(string $key)`: 세션에서 특정 키를 제거합니다.
- `clear()`: 모든 세션 데이터를 삭제하지만 동일한 파일 이름을 유지합니다.
- `commit()`: 현재 세션 데이터를 파일 시스템에 저장합니다.
- `id()`: 현재 세션 ID를 반환합니다.
- `regenerate(bool $deleteOldFile = false)`: 세션 ID를 재생성하며 새 세션 파일을 생성합니다. 기존 데이터는 유지되며, `$deleteOldFile`이 `true`이면 기존 파일을 삭제합니다.
- `destroy(string $id)`: 지정된 ID의 세션을 파괴하고 세션 파일을 시스템에서 삭제합니다. 이는 `SessionHandlerInterface`의 일부이며 `$id`가 필요합니다. 일반적인 사용법은 `$session->destroy($session->id())`입니다.
- `getAll()` : 현재 세션의 모든 데이터를 반환합니다.

`get()`과 `id()`를 제외한 모든 메서드는 체이닝을 위해 `Session` 인스턴스를 반환합니다.

## 이 플러그인을 사용하는 이유?

- **경량**: 외부 종속성 없음 - 단지 파일만 사용합니다.
- **비동기**: 기본적으로 `read_and_close`로 세션 잠금을 피합니다.
- **보안**: 민감한 데이터에 AES-256-CBC 암호화를 지원합니다.
- **유연성**: 자동 커밋, 테스트 모드, 수동 제어 옵션.
- **Flight-네이티브**: Flight 프레임워크를 위해 특별히 제작되었습니다.

## 기술 세부 사항

- **저장 형식**: 세션 파일은 구성된 `save_path`에 `sess_`로 접두사 붙여 저장됩니다. 파일 내용 접두사:
  - `J`: 일반 JSON (기본, 암호화 없음)
  - `F`: 암호화된 JSON (기본, 암호화 있음)
  - `P`: 일반 PHP 직렬화 (레거시, 암호화 없음)
  - `E`: 암호화된 PHP 직렬화 (레거시, 암호화 있음)
- **암호화**: `encryption_key`가 제공되면 세션 작성 시마다 무작위 IV와 함께 AES-256-CBC를 사용합니다. JSON과 PHP 직렬화 모드 모두에서 작동합니다.
- **직렬화**: JSON이 기본이며 가장 안전한 방법입니다. PHP 직렬화는 레거시/고급 사용을 위해 사용 가능하지만 덜 안전합니다.
- **가비지 수집**: 만료된 세션을 정리하기 위해 PHP의 `SessionHandlerInterface::gc()`를 구현합니다.

## 기여

기여를 환영합니다! [저장소](https://github.com/flightphp/session)를 포크하여 변경 사항을 만들고 풀 요청을 제출하세요. 버그 보고나 기능 제안은 Github 이슈 트래커를 통해 하세요.

## 라이선스

이 플러그인은 MIT 라이선스 under입니다. 자세한 내용은 [Github 저장소](https://github.com/flightphp/session)를 참조하세요.