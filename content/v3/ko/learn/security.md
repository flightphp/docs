# 보안

## 개요

보안은 웹 애플리케이션에서 매우 중요한 문제입니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight는 웹 애플리케이션을 보호하는 데 도움이 되는 여러 기능을 제공합니다.

공식 [스켈레톤](https://github.com/flightphp/skeleton)에는 전용 **`SECURITY.md`**와 보안 헤더 미들웨어도 포함되어 있어 [AI 코딩 도구](/learn/ai)(및 사람)가 시크릿, 헤더, XSS/SQL 규칙을 위한 의도적인 단일 장소를 갖게 합니다—`AGENTS.md`의 일반적인 코딩 스타일과는 별도로.

## 이해

웹 애플리케이션을 구축할 때 알아야 할 일반적인 보안 위협이 여러 가지 있습니다. 가장 흔한 위협은 다음과 같습니다:
- 사이트 간 요청 위조(CSRF)
- 사이트 간 스크립팅(XSS)
- SQL 인젝션
- 교차 출처 리소스 공유(CORS)

[템플릿](/learn/templates)은 기본적으로 출력을 이스케이프하여 XSS에 도움을 줍니다(Twig와 Latte가 이렇게 합니다; 이 장점을 활용하세요). [세션](/awesome-plugins/session)은 아래에 설명된 대로 사용자 세션에 CSRF 토큰을 저장하여 CSRF에 도움을 줄 수 있습니다. PDO와 함께 준비된 문을 사용하거나 [SimplePdo](/learn/simple-pdo)의 헬퍼를 사용하면 SQL 인젝션을 예방하는 데 도움이 됩니다. CORS는 `Flight::start()`가 호출되기 전에 간단한 훅으로 처리할 수 있습니다.

이 모든 방법이 함께 작동하여 웹 애플리케이션을 안전하게 유지하는 데 도움이 됩니다. 보안 모범 사례를 배우고 이해하는 것은 항상 최우선으로 생각해야 합니다. 트레이드오프를 이해하지 않은 채 페이지가 로드되게 하기 위해 AI 비서에게 "CSP를 비활성화"하거나 헤더를 약화하도록 요청하지 마세요.

## 기본 사용법

### 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 애플리케이션에 이러한 헤더를 추가하는 방법에는 여러 가지가 있습니다.

헤더의 보안을 확인할 수 있는 훌륭한 웹사이트 두 곳은 [securityheaders.com](https://securityheaders.com/)과 [observatory.mozilla.org](https://observatory.mozilla.org/)입니다. 아래 코드를 설정한 후 해당 두 웹사이트로 헤더가 제대로 작동하는지 쉽게 확인할 수 있습니다.

스켈레톤에는 `App\Middleware\SecurityHeadersMiddleware`(요청별 nonce가 있는 CSP, 프레임 옵션, HSTS 등)가 포함되어 있습니다. 헤더를 끄기보다는 이를 의도적으로 확장하는 것을 선호하세요.

#### 수동으로 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더 설정
// 참고: 이 헤더는 매우 복잡해질 수 있으므로
//  애플리케이션에 맞게 인터넷의 예시를 참고하는 것이 좋습니다
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 전송되는 referrer 정보의 양을 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS를 강제하기 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 사용할 수 있는 기능과 API를 제어하기 위해 Permissions-Policy 헤더 설정
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

`routes.php` 또는 `index.php` 파일의 맨 위에 추가할 수 있습니다.

#### 필터로 추가

다음과 같이 필터/훅에 추가할 수도 있습니다:

```php
// 필터에 헤더 추가
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

#### 미들웨어로 추가

어떤 라우트에 적용할지 가장 유연하게 지정할 수 있는 미들웨어 클래스로 추가할 수도 있습니다. 일반적으로 이러한 헤더는 모든 HTML 및 API 응답에 적용해야 합니다.

스켈레톤 스타일 경로와 네임스페이스(**폴더 대소문자가 `App\Middleware`와 일치**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		// 인라인 스크립트가 있을 때는 부트스트랩의 CSP nonce를 선호하세요 (스켈레톤은 csp_nonce를 설정합니다)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — 빈 문자열 그룹 = 모든 라우트에 대한 전역 미들웨어
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// 추가 라우트
}, [SecurityHeadersMiddleware::class]);
```

이전 프로젝트는 여전히 `app/middlewares`와 `app\middlewares`를 사용할 수 있습니다. 폴더가 일치하면 작동합니다. 새 스켈레톤 앱은 **`app/Middleware/`**와 **`App\Middleware`**를 사용합니다. [오토로딩](/learn/autoloading)을 참고하세요.

### 사이트 간 요청 위조(CSRF)

사이트 간 요청 위조(CSRF)는 악성 웹사이트가 사용자의 브라우저로 하여금 귀하의 웹사이트에 요청을 보내게 할 수 있는 공격 유형입니다. 이는 사용자가 모르는 사이에 웹사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight는 내장 CSRF 보호 메커니즘을 제공하지 않지만, 미들웨어를 사용하여 쉽게 직접 구현할 수 있습니다.

#### 설정

먼저 CSRF 토큰을 생성하여 사용자 세션에 저장해야 합니다. 그런 다음 양식에서 이 토큰을 사용하고 양식이 제출될 때 검사할 수 있습니다. 세션 관리를 위해 [flightphp/session](/awesome-plugins/session) 플러그인을 사용하겠습니다.

```php
// CSRF 토큰을 생성하여 사용자 세션에 저장
// (세션 객체를 생성하고 Flight에 연결했다고 가정합니다)
// 자세한 내용은 세션 문서를 참고하세요
Flight::register('session', flight\Session::class);

// 세션당 단일 토큰만 생성하면 됩니다 (따라서 동일한 사용자의
// 여러 탭과 요청에서 작동합니다)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### 기본 PHP Flight 템플릿 사용

```html
<!-- 양식에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 기타 양식 필드 -->
</form>
```

##### Twig 사용(스켈레톤 기본값)

Twig 함수를 등록하거나 모든 양식 뷰에 토큰을 전달하세요. 전역 + 양식 필드를 사용한 최소 예시:

```php
// Twig 구성 시 (예: services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# 기타 필드 #}
</form>
```

##### Latte 사용

Latte 템플릿에서 CSRF 토큰을 출력하는 사용자 정의 함수를 설정할 수도 있습니다.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// 기타 구성...

	// CSRF 토큰을 출력하는 사용자 정의 함수 설정
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

이제 Latte 템플릿에서 `csrf()` 함수를 사용하여 CSRF 토큰을 출력할 수 있습니다.

```html
<form method="post">
	{csrf()}
	<!-- 기타 양식 필드 -->
</form>
```

#### CSRF 토큰 확인

여러 방법으로 CSRF 토큰을 확인할 수 있습니다.

##### 미들웨어

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// 추가 라우트
}, [CsrfMiddleware::class]);
```

##### 이벤트 필터

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고, 그렇다면 CSRF 토큰이 유효한지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 양식 값에서 csrf 토큰 캡처
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// 또는 JSON 응답의 경우
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### 사이트 간 스크립팅(XSS)

사이트 간 스크립팅(XSS)은 악성 양식 입력이 웹사이트에 코드를 주입할 수 있는 공격 유형입니다. 이러한 기회의 대부분은 최종 사용자가 작성하는 양식 값에서 비롯됩니다. 사용자의 출력을 **절대** 신뢰해서는 안 됩니다! 항상 모든 사용자가 세계 최고의 해커라고 가정하세요. 그들은 페이지에 악성 JavaScript나 HTML을 주입할 수 있습니다. 이 코드는 사용자의 정보를 훔치거나 웹사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight의 뷰 클래스나 [Twig](/awesome-plugins/twig) 또는 [Latte](/awesome-plugins/latte)와 같은 템플릿 엔진을 사용하면 출력을 쉽게 이스케이프하여 XSS 공격을 방지할 수 있습니다.

```php
// 사용자가 이름으로 이것을 사용하려고 할 정도로 영리하다고 가정해 봅시다
$name = '<script>alert("XSS")</script>';

// 출력을 이스케이프합니다
Flight::view()->set('name', $name);
// 출력 결과: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig(스켈레톤 기본값)와 Latte는 기본적으로 자동 이스케이프합니다 — 원시 PHP echo보다 이를 선호하세요
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → 이스케이프됨
// 콘텐츠가 완전히 신뢰할 수 있는 경우가 아니면 |raw / 이스케이프되지 않은 출력을 피하세요
```

### SQL 인젝션

SQL 인젝션은 악성 사용자가 데이터베이스에 SQL 코드를 주입할 수 있는 공격 유형입니다. 이는 데이터베이스에서 정보를 훔치거나 데이터베이스에서 작업을 수행하는 데 사용될 수 있습니다. 다시 말하지만 사용자의 입력을 **절대** 신뢰해서는 안 됩니다! 항상 그들이 피를 흘리려고 한다고 가정하세요. 준비된 문을 사용하세요—[SimplePdo](/learn/simple-pdo) 헬퍼가 이를 기본 경로로 만듭니다.

```php
// Flight::db()가 SimplePdo로 등록되어 있다고 가정합니다 (또는 컨트롤러에 SimplePdo를 주입하세요)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo(선호) — 바인딩된 매개변수를 사용하는 한 줄 코드
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? 플레이스홀더를 사용한 동일한 방식
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

스켈레톤 스타일 컨트롤러에서는 테스트와 AI 생성 코드가 일관되게 유지되도록 `Flight::db()`보다 `SimplePdo`의 생성자 주입을 선호하세요([DIC](/learn/dependency-injection-container)).

#### 안전하지 않은 예시

아래는 다음과 같은 순진한 예시로부터 보호하기 위해 SQL 준비된 문을 사용하는 이유입니다:

```php
// 최종 사용자가 웹 양식을 작성합니다.
// 양식 값으로 해커가 다음과 같은 것을 입력합니다:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// 쿼리가 구성된 후에는 다음과 같습니다
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 이상해 보이지만 작동하는 유효한 쿼리입니다. 사실,
// 모든 사용자를 반환하는 매우 일반적인 SQL 인젝션 공격입니다.

var_dump($users); // 단일 사용자 이름뿐만 아니라 데이터베이스의 모든 사용자를 덤프합니다
```

### 시크릿과 구성

- 시크릿은 커밋된 `config.php` 샘플이 아니라 **`.env`**(또는 실제 환경)에 넣으세요.
- 스켈레톤 규칙: `config.php`에는 리터럴 기본값; 부트스트랩에서 env를 병합; 컨트롤러 내부에서 `$_ENV`를 **읽지** 말고 구성(config)을 주입하세요. [구성](/learn/configuration)을 참고하세요.
- API 키, DB 비밀번호 또는 세션 암호화 키를 절대 커밋하지 마세요. AI 도구가 안전하지 않은 지름길을 만들어내지 않도록 **`SECURITY.md`**를 가리키세요.

### JSONP 콜백 검증

Flight의 `Flight::jsonp()` 메서드를 사용하는 경우, Flight가 JSONP 콜백 매개변수 이름을 엄격한 허용 목록 정규식(`/^[A-Za-z_$][\w$.]{0,127}$/`)에 대해 검증한다는 점에 유의하세요. 이 패턴과 일치하지 않는 콜백 이름은 Flight가 예외를 발생시켜 악성 콜백 값을 통한 임의 JavaScript 주입을 방지합니다.

이 검증은 기본으로 제공되며 추가 구성이 필요하지 않지만, JSONP 엔드포인트에서 예상치 못한 오류를 디버깅할 때 알아두면 좋습니다.

### CORS

교차 출처 리소스 공유(CORS)는 웹 페이지의 많은 리소스(예: 글꼴, JavaScript 등)가 리소스가 시작된 도메인 외부의 다른 도메인에서 요청될 수 있도록 하는 메커니즘입니다. Flight에는 내장 기능이 없지만, `Flight::start()` 메서드가 호출되기 전에 실행되는 훅으로 쉽게 처리할 수 있습니다.

```php
// app/Utils/CorsUtil.php  (스켈레톤: PascalCase Utils 폴더 → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// 허용할 호스트를 여기에서 사용자 정의하세요.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// 부트스트랩 / 라우트 — start 전에 실행
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight 구성 강화

Flight는 직접적인 보안 영향을 미치는 여러 엔진 설정을 노출합니다. 이를 올바르게 설정하는 것은 애플리케이션을 강화하는 가장 쉬운 방법 중 하나입니다.

#### `flight.allow_method_override`

기본적으로 Flight는 클라이언트가 `X-HTTP-Method-Override` 헤더 또는 POST 본문의 `_method` 필드를 사용하여 요청의 HTTP 메서드를 재정의할 수 있도록 허용합니다. 이는 `GET`/`POST`만 보낼 수 있는 HTML 양식에는 편리하지만, 예상하지 못한 경우 위험할 수 있습니다—공격자가 일반 양식을 통해 `DELETE` 또는 `PUT` 요청을 위조할 수 있습니다.

애플리케이션이 이 동작에 의존하지 않는 경우(예: 최신 클라이언트나 모든 HTTP 동사를 보낼 수 있는 JavaScript 프런트엔드가 사용하는 API를 구축하는 경우), 이를 비활성화해야 합니다:

```php
// index.php 또는 부트스트랩 파일에서 Flight::start() 전에
Flight::set('flight.allow_method_override', false);
```

기본값은 하위 호환성을 위해 `true`이지만, 재정의 기능이 명시적으로 필요하지 않은 애플리케이션에는 **`false`로 설정하는 것을 강력히 권장합니다**.

#### `flight.debug`

Flight에는 처리되지 않은 예외가 발생할 때 자세한 오류 정보(예외 메시지, 코드 및 전체 스택 추적)가 브라우저에 렌더링되는지 여부를 제어하는 `flight.debug` 설정이 있습니다. 기본값은 `false`이므로 일반적인 `500 Internal Server Error` 메시지만 표시됩니다—내부 세부 정보가 클라이언트에 유출되지 않습니다.

프로덕션 서버에서는 절대 활성화하지 마세요. 로컬 또는 스테이징 환경에서만 사용하세요:

```php
// 로컬 개발에만 안전 — 프로덕션에서는 절대 사용하지 마세요
Flight::set('flight.debug', true);
```

`flight.debug`가 `false`(기본값)인 경우에도 `flight.log_errors`를 활성화하여 오류를 캡처할 수 있습니다:

```php
// 클라이언트에 노출하지 않고 서버 측에서 오류 기록
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight의 내장 `View` 클래스는 절대 경로의 템플릿을 포함하거나 `flight.views.path` 밖으로 올라가는 상대 이름(예: `../` 사용)의 템플릿을 포함합니다. 이는 폴더 간에 템플릿을 의도적으로 공유하는 앱에는 의도된 동작이지만, 템플릿 이름이 신뢰할 수 없는 입력에서 오는 경우 경로 순회 위험이기도 합니다.

`flight.views.restrict_to_path`는 기존 앱이 계속 작동하도록 **기본적으로 꺼져 있습니다**. 문서화된 이유가 없는 한 켜세요:

```php
// index.php 또는 부트스트랩 파일에서 Flight::start() 전에
Flight::set('flight.views.restrict_to_path', true);
```

Engine은 뷰가 생성될 때 이 설정을 `View::$restrictToPath`에 적용합니다(`flight.views.path` 및 `flight.views.extension`과 동일한 패턴). 활성화하면:

- `render()` 및 `fetch()`는 실제 경로가 구성된 views 디렉터리 내부에 있는 파일만 포함합니다(외부를 가리키는 심볼릭 링크도 거부됨).
- `exists()`는 동일한 경로에 대해 예외를 발생시키는 대신 `false`를 반환합니다.
- `getTemplate()` 자체는 변경되지 않습니다—항상 그랬던 것처럼 경로를 반환합니다.
- 차단된 파일은 `Template file is outside the views path.`를 발생시킵니다. 누락된 파일은 기존 `Template file not found: ...` 메시지를 계속 발생시킵니다.

views 디렉터리를 가리키는 자체 파일 시스템 로더와 함께 Twig 또는 Latte를 사용하는 경우, 해당 엔진은 이미 해당 루트에 템플릿을 포함합니다. 그래도 Flight의 네이티브 `View`에 대해 이것을 켜서 `Flight::view()->render()` / `fetch()`를 호출하는 모든 코드가 동일한 보호를 받도록 하세요. 공식 [스켈레톤](https://github.com/flightphp/skeleton)은 부트스트랩에서 이를 활성화합니다.

#### 권장 프로덕션 구성

```php
// index.php 또는 앱 구성 / 부트스트랩에서 적용
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### 오류 처리
공격자에게 정보를 유출하지 않도록 프로덕션에서는 민감한 오류 세부 정보를 숨기세요. 프로덕션에서는 `display_errors`를 `0`으로 설정하여 오류를 표시하는 대신 기록하세요.

```php
// bootstrap.php 또는 index.php에서

// app/config/config.php에 추가
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // 오류 표시 비활성화
    ini_set('log_errors', 1);     // 대신 오류 기록
    ini_set('error_log', '/path/to/error.log');
}

// 라우트 또는 컨트롤러에서
// 제어된 오류 응답에는 Flight::halt() 사용
Flight::halt(403, 'Access denied');
```

### 입력 새니타이징
사용자 입력을 절대 신뢰하지 마세요. 악성 데이터가 몰래 들어오는 것을 방지하기 위해 처리하기 전에 [filter_var](https://www.php.net/manual/en/function.filter-var.php)를 사용하여 새니타이징하세요. 앱 코드에서 원시 `$_GET` / `$_POST`보다 `$app->request()`(또는 `Flight::request()`)를 통해 입력을 읽는 것을 선호하세요.

```php

// $_POST['input'] 및 $_POST['email']이 있는 $_POST 요청을 가정합니다

// 문자열 입력 새니타이징
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// 이메일 새니타이징
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### 비밀번호 해싱
[password_hash](https://www.php.net/manual/en/function.password-hash.php) 및 [password_verify](https://www.php.net/manual/en/function.password-verify.php)와 같은 PHP 내장 함수를 사용하여 비밀번호를 안전하게 저장하고 안전하게 검증하세요. 비밀번호는 일반 텍스트로 저장해서는 안 되며, 되돌릴 수 있는 방법으로 암호화해서도 안 됩니다. 해싱은 데이터베이스가 침해되더라도 실제 비밀번호가 보호되도록 보장합니다.

```php
$password = Flight::request()->data->password;
// 저장 시 비밀번호 해시 (예: 등록 중)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 비밀번호 검증 (예: 로그인 중)
if (password_verify($password, $stored_hash)) {
    // 비밀번호 일치
}
```

### 속도 제한
캐시로 요청 속도를 제한하여 무차별 대입 공격 또는 서비스 거부 공격을 방지하세요.

```php
// flightphp/cache가 설치 및 등록되어 있다고 가정합니다
// 필터에서 flightphp/cache 사용
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // 60초 후 재설정
});
```

## 같이 보기
- [세션](/awesome-plugins/session) - 사용자 세션을 안전하게 관리하는 방법.
- [템플릿](/learn/templates) - Twig/Latte 자동 이스케이프 및 XSS.
- [SimplePdo](/learn/simple-pdo) - 준비된 문을 사용하는 데이터베이스 헬퍼.
- [PdoWrapper](/learn/pdo-wrapper) - 더 이상 사용되지 않음; 새 코드에는 SimplePdo를 사용하세요.
- [미들웨어](/learn/middleware) - 보안 헤더 추가 프로세스를 단순화하기 위해 미들웨어를 사용하는 방법.
- [구성](/learn/configuration) - `.env` 대 리터럴 구성, 프로덕션 플래그.
- [AI 및 개발자 경험](/learn/ai) - 에이전트를 위해 보안 정책을 `SECURITY.md`에 유지하세요.
- [응답](/learn/responses) - 보안 헤더로 HTTP 응답을 사용자 정의하는 방법.
- [요청](/learn/requests) - 사용자 입력을 처리하고 새니타이징하는 방법.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 입력 새니타이징을 위한 PHP 함수.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 안전한 비밀번호 해싱을 위한 PHP 함수.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - 해시된 비밀번호를 검증하기 위한 PHP 함수.

## 문제 해결
- Flight 프레임워크 구성 요소 관련 문제 해결 정보는 위의 "같이 보기" 섹션을 참고하세요.
- CSP가 스크립트를 차단하는 경우 nonce(스켈레톤 패턴)를 추가하거나 특정 출처를 허용 목록에 추가하세요—계획 없이 `script-src *`를 설정하지 마세요.

## 변경 내역
- 문서 – 스켈레톤 `App\Middleware`, Twig CSRF/XSS 노트, SimplePdo, 시크릿/`.env`, AI 친화적 프로젝트를 위한 `SECURITY.md`.
- 문서 – Flight 구성 강화 아래 `flight.views.restrict_to_path` 문서화(네이티브 뷰에 대한 선택적 경로 포함).
- v3.18.1 - `flight.allow_method_override`, `flight.debug`, JSONP 콜백 검증을 다루는 Flight 구성 강화 섹션 추가.
- v3.1.0 - CORS, 오류 처리, 입력 새니타이징, 비밀번호 해싱, 속도 제한에 대한 섹션 추가.
- v2.0 - XSS를 방지하기 위해 기본 뷰에 대한 이스케이프 추가.