# 구성

## 개요

Flight는 애플리케이션 요구 사항에 맞게 프레임워크의 다양한 측면을 구성할 수 있는 간단한 방법을 제공합니다. 일부는 기본적으로 설정되어 있지만 필요에 따라 재정의할 수 있습니다. 애플리케이션 전체에서 사용할 자체 변수를 설정할 수도 있습니다.

명확하고 계층화된 구성(파일 기본값 + 환경 비밀값)은 [AI 코딩 도구](/learn/ai)에도 도움이 됩니다. 에이전트는 컨트롤러 내부에서 `$_ENV` 읽기를 만들어내는 대신, 리터럴은 한 곳에, 비밀값은 한 곳에 있다는 것을 학습합니다.

## 이해

`set` 메서드를 통해 구성 값을 설정하여 Flight의 특정 동작을 사용자 정의할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

구조화된 앱(스켈레톤 포함)에서는 일반적으로 `app/config/config.php`에서 프로젝트 설정을 로드한 다음 관련 키를 Engine에 적용합니다(예: `flight.base_url`, `flight.views.path`). 또한 여기저기서 전역 값을 읽는 대신 작은 구성 객체를 컨트롤러에 주입할 수 있습니다. 이는 테스트와 `AGENTS.md`를 따르는 에이전트에 더 친화적입니다.

## 기본 사용법

### Flight 구성 옵션

사용 가능한 모든 구성 설정 목록은 다음과 같습니다:

- **flight.base_url** `?string` - Flight가 하위 디렉터리에서 실행되는 경우 요청의 기본 URL을 재정의합니다. (기본값: null)
- **flight.case_sensitive** `bool` - URL에 대한 대소문자 구분 일치를 설정합니다. (기본값: false)
- **flight.handle_errors** `bool` - Flight가 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
  - Flight가 기본 PHP 동작 대신 오류를 처리하도록 하려면 이 값을 true로 설정해야 합니다.
  - [Tracy](/awesome-plugins/tracy)가 설치되어 있으면 Tracy가 오류를 처리할 수 있도록 이 값을 false로 설정해야 합니다.
  - [APM](/awesome-plugins/apm) 플러그인이 설치되어 있으면 APM이 오류를 기록할 수 있도록 이 값을 true로 설정해야 합니다.
- **flight.log_errors** `bool` - 오류를 웹 서버의 오류 로그 파일에 기록합니다. (기본값: false)
  - [Tracy](/awesome-plugins/tracy)가 설치되어 있으면 Tracy는 이 구성이 아니라 Tracy 구성에 따라 오류를 기록합니다.
- **flight.debug** `bool` - 오류가 발생할 때 브라우저에 상세한 오류 정보(예외 메시지, 코드, 스택 추적)를 출력합니다. (기본값: false)
  - **프로덕션에서는 절대 활성화하지 마세요** — 내부 애플리케이션 세부 정보가 노출됩니다. 로컬 개발 또는 스테이징에서만 사용하세요.
  - `false`인 경우 일반적인 `500 Internal Server Error`가 대신 표시됩니다. 서버 측에서 오류를 캡처하려면 `flight.log_errors`와 함께 사용하세요.
- **flight.allow_method_override** `bool` - `X-HTTP-Method-Override` 요청 헤더 또는 POST 본문의 `_method` 필드를 통해 HTTP 메서드를 재정의할 수 있도록 허용합니다. (기본값: true)
  - HTML 양식 기반 메서드 스푸핑이 필요 없는 애플리케이션에서는 **이 값을 `false`로 설정하는 것이 권장됩니다**. 표준 POST 양식을 통해 클라이언트가 `DELETE` 또는 `PUT` 요청을 위조하는 것을 방지하기 때문입니다.
  - 자세한 내용은 [보안](/learn/security#flight-configuration-hardening)을 참조하세요.
- **flight.views.path** `string` - 뷰 템플릿 파일이 포함된 디렉터리입니다. (기본값: ./views)
- **flight.views.extension** `string` - 뷰 템플릿 파일 확장자입니다. (기본값: `.php`; 공식 스켈레톤은 Twig 사용 시 이를 `.twig`로 설정합니다)
- **flight.views.restrict_to_path** `bool` - `true`이면 Flight의 기본 `View`는 `flight.views.path` 내부로 확인되는 템플릿 파일만 허용합니다. (기본값: `false`). 기본 뷰를 사용하는 앱에서는 **이 옵션을 켜세요**. [보안](/learn/security#flightviewsrestrict_to_path)을 참조하세요.
- **flight.content_length** `bool` - `Content-Length` 헤더를 설정합니다. (기본값: true)
  - [Tracy](/awesome-plugins/tracy)를 사용하는 경우 Tracy가 올바르게 렌더링할 수 있도록 이 값을 false로 설정해야 합니다.
- **flight.v2.output_buffering** `bool` - 레거시 출력 버퍼링을 사용합니다. [v3로 마이그레이션](migrating-to-v3)을 참조하세요. (기본값: false)

### 로더 구성

로더에는 추가 구성 설정이 하나 더 있습니다. 이를 통해 클래스 이름에 `_`가 포함된 클래스를 자동으로 로드할 수 있습니다.

```php
// 밑줄이 있는 클래스 로딩 활성화
// 기본값은 true
Loader::$v2ClassLoading = false;
```

[자동 로딩](/learn/autoloading)은 네임스페이스와 일치하는 **폴더 대소문자**에도 의존한다는 점을 기억하세요. 특히 스켈레톤의 `App\` + `app/Controller/` 레이아웃에서 그렇습니다.

### 프로젝트 구성 및 `.env`(스켈레톤 패턴)

Flight 코어는 `.env` 파일을 요구하지 않습니다. 많은 앱은 PHP 구성 배열만 사용합니다. 공식 스켈레톤은 구성을 계층화하여 비밀값이 git에 남지 않으면서도 Runway가 **리터럴** 구성을 안전하게 다시 작성할 수 있도록 합니다:

1. **`.env` / 실제 환경** — 비밀값 및 배포 재정의(gitignore됨).
2. **`app/config/config.php`** — 리터럴 PHP 배열 기본값(`config_sample.php`에서 복사됨). 이 파일 안에는 `$_ENV[...]` 표현식을 **사용하지 않는** 것이 좋습니다. `runway config:set` 같은 도구가 이를 정적 값으로 다시 작성할 수 있으며, 비밀값을 파일에 각인시킬 수 있습니다.
3. **부트스트랩 시 병합** — 매핑된 키에 대해서는 env가 우선합니다. 앱 코드는 컨트롤러에서 `$_ENV`가 아니라 구성 객체 또는 `$app->get()`을 읽습니다.

`config_sample.php` / `config.php`의 예시 형태(간단화):

```php
<?php
// 리터럴만 — 비밀값은 스켈레톤 워크플로에서는 .env에 속합니다
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // 또는 mysql, 또는 비활성화하려면 ''
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (스켈레톤)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

이 분리는 [AI 친화적인 프로젝트](/learn/ai)를 위해 의도된 것입니다. 지침에서는 “기본값은 `config.php`에, 비밀값은 `.env`에, Config / Engine을 주입하라—컨트롤러에서 env 접근을 만들어내지 말라”고 말할 수 있습니다. 기존 앱은 `.env`를 완전히 무시하고 단일 구성 파일을 유지할 수 있습니다.

### 변수

Flight를 사용하면 애플리케이션 어디에서나 사용할 수 있도록 변수를 저장할 수 있습니다.

```php
// 변수 저장
Flight::set('id', 123);

// 애플리케이션의 다른 곳에서
$id = Flight::get('id');
```
변수가 설정되었는지 확인하려면 다음과 같이 하세요:

```php
if (Flight::has('id')) {
  // 무언가 수행
}
```

다음과 같이 변수를 지울 수 있습니다:

```php
// id 변수 지우기
Flight::clear('id');

// 모든 변수 지우기
Flight::clear();
```

> **참고:** 변수를 설정할 수 있다고 해서 반드시 설정해야 하는 것은 아닙니다. 이 기능은 드물게 사용하세요. 그 이유는 여기에 저장된 모든 것이 전역 변수가 되기 때문입니다. 전역 변수는 애플리케이션 어디에서나 변경될 수 있어 버그를 추적하기 어렵게 만들기 때문에 좋지 않습니다. 또한 [단위 테스트](/guides/unit-testing)와 같은 작업을 복잡하게 만들 수 있습니다. 컨트롤러에 필요한 서비스와 구성에는 (스켈레톤 + Dice 설정에서처럼) 생성자 주입을 선호하세요.

### 오류 및 예외

모든 오류와 예외는 Flight에 의해 포착되어 `error` 메서드로 전달됩니다. 단, `flight.handle_errors`가 true로 설정된 경우입니다.

기본 동작은 일부 오류 정보와 함께 일반적인 `HTTP 500 Internal Server Error` 응답을 보내는 것입니다.

필요에 따라 이 동작을 [재정의](/learn/extending)할 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 구성을 변경하여 활성화할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

#### 404 찾을 수 없음

URL을 찾을 수 없으면 Flight는 `notFound` 메서드를 호출합니다. 기본 동작은 간단한 메시지와 함께 `HTTP 404 Not Found` 응답을 보내는 것입니다.

필요에 따라 이 동작을 [재정의](/learn/extending)할 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```

## 함께 보기
- [설치](/install) - 스켈레톤 구성, `.env`, 부트스트랩 레이아웃.
- [자동 로딩](/learn/autoloading) - 네임스페이스와 폴더 대소문자.
- [Flight 확장](/learn/extending) - Flight의 핵심 기능을 확장하고 사용자 정의하는 방법.
- [단위 테스트](/guides/unit-testing) - Flight 애플리케이션의 단위 테스트를 작성하는 방법.
- [AI 및 개발자 경험](/learn/ai) - `AGENTS.md` 및 일관된 프로젝트 지침.
- [Tracy](/awesome-plugins/tracy) - 고급 오류 처리 및 디버깅을 위한 플러그인.
- [Tracy 확장](/awesome-plugins/tracy_extensions) - Tracy를 Flight와 통합하기 위한 확장.
- [APM](/awesome-plugins/apm) - 애플리케이션 성능 모니터링 및 오류 추적을 위한 플러그인.
- [보안](/learn/security) - 강화 플래그 및 비밀값 처리.

## 문제 해결
- 구성의 모든 값을 확인하는 데 문제가 있으면 `var_dump(Flight::get());`를 실행할 수 있습니다.
- Runway 또는 배포 도구가 `config.php`를 다시 작성한 경우 비밀값이 커밋되지 않았는지 확인하세요. 스켈레톤 패턴을 사용할 때는 비밀값을 `.env` 또는 실제 환경에 유지하세요.

## 변경 로그
- 문서 – 뷰 경로 설정 옆에 `flight.views.restrict_to_path`를 명시했습니다.
- 문서 – 스켈레톤 스타일 구성 / `.env` 계층화 및 새 프로젝트의 Twig 뷰 확장자 기본값을 문서화했습니다.
- v3.18.1 - `flight.debug` 및 `flight.allow_method_override` 구성 옵션을 추가했습니다.
- v3.5.0 - 레거시 출력 버퍼링 동작을 지원하기 위해 `flight.v2.output_buffering` 구성을 추가했습니다.
- v2.0 - 핵심 구성이 추가되었습니다.