# 구성

## 개요

Flight는 애플리케이션 요구 사항에 맞게 프레임워크의 다양한 측면을 구성하는 간단한 방법을 제공합니다. 일부는 기본적으로 설정되어 있지만 필요에 따라 재정의할 수 있습니다. 또한 애플리케이션 전체에서 사용할 자체 변수를 설정할 수도 있습니다.

명확하고 계층화된 구성(파일 기본값 + 환경 비밀 값)은 [AI 코딩 도구](/learn/ai)에도 도움이 됩니다: 에이전트는 컨트롤러 내부에서 `$_ENV` 읽기를 직접 만들지 않고 리터럴을 위한 한 곳과 비밀 값을 위한 한 곳을 알 수 있습니다.

## 이해

`set` 메서드를 통해 구성 값을 설정하여 Flight의 특정 동작을 사용자 지정할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

구조화된 앱([스켈레톤](https://github.com/flightphp/skeleton) 포함)에서는 일반적으로 `app/config/config.php`에서 프로젝트 설정을 불러온 다음 관련 키를 엔진에 적용합니다(예: `flight.base_url`, `flight.views.path`). 또한 모든 곳에서 전역 변수를 읽는 대신 작은 구성 객체를 컨트롤러에 주입할 수도 있습니다. 이는 테스트와 `AGENTS.md`를 따르는 에이전트에 더 친숙합니다.

## 기본 사용법

### Flight 구성 옵션

다음은 사용 가능한 모든 구성 설정 목록입니다.

- **flight.base_url** `?string` - Flight가 하위 디렉터리에서 실행 중인 경우 요청의 기본 URL을 재정의합니다. (기본값: null)
- **flight.case_sensitive** `bool` - URL에 대해 대소문자를 구분하여 일치시킵니다. (기본값: false)
- **flight.handle_errors** `bool` - Flight가 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
  - 기본 PHP 동작 대신 Flight가 오류를 처리하려면 이 값을 true로 설정해야 합니다.
  - [Tracy](/awesome-plugins/tracy)를 설치한 경우 오류를 Tracy가 처리할 수 있도록 이 값을 false로 설정하는 것이 좋습니다.
  - [APM](/awesome-plugins/apm) 플러그인을 설치한 경우 APM이 오류를 기록할 수 있도록 이 값을 true로 설정하는 것이 좋습니다.
- **flight.log_errors** `bool` - 웹 서버의 오류 로그 파일에 오류를 기록합니다. (기본값: false)
  - [Tracy](/awesome-plugins/tracy)를 설치한 경우 Tracy는 이 구성이 아닌 Tracy 구성에 따라 오류를 기록합니다.
- **flight.debug** `bool` - 오류 발생 시 브라우저에 자세한 오류 정보(예외 메시지, 코드, 스택 추적)를 출력합니다. (기본값: false)
  - **프로덕션에서는 절대 활성화하지 마세요.** 내부 애플리케이션 세부 정보가 유출됩니다. 로컬 개발 또는 스테이징에서만 사용하세요.
  - `false`인 경우 일반적인 `500 Internal Server Error`가 대신 표시됩니다. 서버 측에서 오류를 캡처하려면 `flight.log_errors`와 함께 사용하세요.
- **flight.allow_method_override** `bool` - `X-HTTP-Method-Override` 요청 헤더 또는 POST 본문의 `_method` 필드를 통해 HTTP 메서드를 재정의할 수 있게 합니다. (기본값: true)
  - HTML 폼 기반 메서드 스푸핑이 필요 없는 애플리케이션의 경우 **이 값을 `false`로 설정하는 것이 좋습니다.** 클라이언트가 일반 POST 폼을 통해 `DELETE` 또는 `PUT` 요청을 위조하는 것을 방지합니다.
  - 자세한 내용은 [보안](/learn/security#flight-configuration-hardening)을 참조하세요.
- **flight.views.path** `string` - 뷰 템플릿 파일이 포함된 디렉터리입니다. (기본값: ./views)
- **flight.views.extension** `string` - 뷰 템플릿 파일 확장자입니다. (기본값: `.php`; 공식 스켈레톤은 Twig를 사용할 때 `.twig`로 설정합니다)
- **flight.views.restrict_to_path** `bool` - `true`로 설정하면 Flight 기본 `View`는 `flight.views.path` 내부에 있는 템플릿 파일만 허용합니다. (기본값: `false`). 기본 뷰를 사용하는 앱에서는 **이 기능을 켜세요.** [보안](/learn/security#flightviewsrestrict_to_path)을 참조하세요.
- **flight.content_length** `bool` - `Content-Length` 헤더를 설정합니다. (기본값: true)
  - [Tracy](/awesome-plugins/tracy)를 사용하는 경우 Tracy가 제대로 렌더링되도록 이 값을 false로 설정해야 합니다.
- **flight.v2.output_buffering** `bool` - 레거시 출력 버퍼링을 사용합니다. [v3로 마이그레이션](migrating-to-v3)을 참조하세요. (기본값: false)

### 로더 구성

로더에는 추가로 또 다른 구성 설정이 있습니다. 클래스 이름에 `_`가 있는 클래스를 자동 로드할 수 있게 해줍니다.

```php
// 밑줄(_)이 있는 클래스 로딩 활성화
// 기본값은 true
Loader::$v2ClassLoading = false;
```

자동 로딩은 네임스페이스와 일치하는 **폴더 대소문자**에도 의존한다는 점을 기억하세요. 특히 스켈레톤의 `App\` + `app/Controller/` 레이아웃에서 그렇습니다.

### 프로젝트 구성 및 `.env` (스켈레톤 패턴)

Flight 코어는 `.env` 파일을 요구하지 않습니다. 많은 앱은 PHP 구성 배열만 사용합니다. 공식 스켈레톤은 구성을 계층화하여 비밀 값이 git에 남지 않도록 하면서 Runway가 **리터럴** 구성을 안전하게 다시 쓸 수 있게 합니다:

1. **`.env` / 실제 환경** — 비밀 값 및 배포 시 재정의(git에서 제외됨).
2. **`app/config/config.php`** — 리터럴 PHP 배열 기본값(`config_sample.php`에서 복사). 이 파일 내에는 `$_ENV[...]` 표현식을 **사용하지 않는** 것이 좋습니다. `runway config:set` 같은 도구가 이 파일을 정적 값으로 다시 작성하면서 비밀 값을 파일에 박아 넣을 수 있습니다.
3. **부트스트랩 시 병합** — 매핑된 키에 대해 env가 우선합니다. 앱 코드는 컨트롤러에서 `$_ENV`를 읽지 않고 구성 객체 또는 `$app->get()`을 읽습니다.

`config_sample.php` / `config.php`의 예시 형태(간략화):

```php
<?php
// 리터럴만 사용 — 비밀 값은 스켈레톤 워크플로에서 .env에 있어야 함
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

이러한 분리는 [AI 친화적인 프로젝트](/learn/ai)를 위해 의도된 것입니다. 지침에서 '기본값은 `config.php`에, 비밀 값은 `.env`에, Config / Engine을 주입하고 컨트롤러에서 env 접근을 직접 만들지 말 것'이라고 말할 수 있습니다. 기존 앱은 `.env`를 완전히 무시하고 단일 구성 파일을 유지할 수 있습니다.

### 변수

Flight를 사용하면 애플리케이션 어디에서나 사용할 수 있도록 변수를 저장할 수 있습니다.

```php
// 변수 저장
Flight::set('id', 123);

// 애플리케이션의 다른 곳에서
$id = Flight::get('id');
```

변수가 설정되었는지 확인하려면 다음을 수행할 수 있습니다:

```php
if (Flight::has('id')) {
  // 어떤 작업 수행
}
```

다음과 같이 변수를 지울 수 있습니다:

```php
// id 변수 지우기
Flight::clear('id');

// 모든 변수 지우기
Flight::clear();
```

> **참고:** 변수를 설정할 수 있다고 해서 꼭 사용해야 하는 것은 아닙니다. 이 기능은 드물게 사용하세요. 그 이유는 여기에 저장된 모든 것이 전역 변수가 되기 때문입니다. 전역 변수는 애플리케이션 어디에서나 변경될 수 있어 버그를 추적하기 어렵게 만들기 때문에 좋지 않습니다. 또한 이는 [단위 테스트](/guides/unit-testing)와 같은 것을 복잡하게 만들 수 있습니다. 컨트롤러에 필요한 서비스와 구성은 생성자 주입(스켈레톤 + Dice 설정에서처럼)을 선호하세요.

### 오류 및 예외

`flight.handle_errors`가 true로 설정된 경우 모든 오류와 예외는 Flight에 의해 포착되어 `error` 메서드로 전달됩니다.

기본 동작은 일부 오류 정보와 함께 일반적인 `HTTP 500 Internal Server Error` 응답을 보내는 것입니다.

자신의 필요에 따라 이 동작을 [재정의](/learn/extending)할 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 구성을 변경하여 이 기능을 활성화할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

#### 404 찾을 수 없음

URL을 찾을 수 없으면 Flight는 `notFound` 메서드를 호출합니다. 기본 동작은 간단한 메시지와 함께 `HTTP 404 Not Found` 응답을 보내는 것입니다.

자신의 필요에 따라 이 동작을 [재정의](/learn/extending)할 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```

## 참고 항목
- [설치](/install) - 스켈레톤 구성, `.env` 및 부트스트랩 레이아웃.
- [자동 로딩](/learn/autoloading) - 네임스페이스와 폴더 대소문자.
- [Flight 확장](/learn/extending) - Flight의 핵심 기능을 확장하고 사용자 지정하는 방법.
- [단위 테스트](/guides/unit-testing) - Flight 애플리케이션에 대한 단위 테스트 작성 방법.
- [AI 및 개발자 경험](/learn/ai) - `AGENTS.md` 및 일관된 프로젝트 지침.
- [Tracy](/awesome-plugins/tracy) - 고급 오류 처리 및 디버깅을 위한 플러그인.
- [Tracy 확장](/awesome-plugins/tracy_extensions) - Tracy를 Flight와 통합하기 위한 확장 기능.
- [APM](/awesome-plugins/apm) - 애플리케이션 성능 모니터링 및 오류 추적을 위한 플러그인.
- [보안](/learn/security) - 보안 강화 플래그 및 비밀 값 처리.

## 문제 해결
- 구성의 모든 값을 확인하는 데 문제가 있는 경우 `var_dump(Flight::get());`를 실행할 수 있습니다.
- Runway 또는 배포 도구가 `config.php`를 다시 작성한 경우 비밀 값이 커밋되지 않았는지 확인하세요. 스켈레톤 패턴을 사용할 때는 비밀 값을 `.env` 또는 실제 환경에 유지하세요.

## 변경 로그
- 문서 – 뷰 경로 설정 옆에 `flight.views.restrict_to_path` 표기.
- 문서 – 스켈레톤 스타일 구성 / `.env` 계층화 및 새 프로젝트의 Twig 뷰 확장자 기본값 문서화.
- v3.18.1 - `flight.debug` 및 `flight.allow_method_override` 구성 옵션 추가.
- v3.5.0 - 레거시 출력 버퍼링 동작을 지원하도록 `flight.v2.output_buffering` 구성 추가.
- v2.0 - 핵심 구성 추가.