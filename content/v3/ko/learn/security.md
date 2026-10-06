# 보안

## 개요

보안은 웹 애플리케이션에 있어 매우 중요한 문제입니다. 애플리케이션을 안전하게 유지하고 사용자 데이터를 안전하게 보호해야 합니다. Flight는 웹 애플리케이션의 보안을 강화하는 여러 기능을 제공합니다.

공식 [skeleton](https://github.com/flightphp/skeleton)에는 전용 **`SECURITY.md`** 와 보안 헤더 미들웨어도 포함되어 있어, [AI 코딩 도구](/learn/ai)(및 개발자)가 비밀값, 헤더, XSS/SQL 규칙을 `AGENTS.md`의 일반 코딩 스타일과 별도로 한곳에 명확히 보관할 수 있습니다.

## 이해하기

웹 애플리케이션을 구축할 때 알고 있어야 할 일반적인 보안 위협이 몇 가지 있습니다. 가장 흔한 위협은 다음과 같습니다.

- 사이트 간 요청 위조 (CSRF)
- 사이트 간 스크립팅 (XSS)
- SQL 인젝션
- 교차 출처 리소스 공유 (CORS)

[템플릿](/learn/templates)은 기본적으로 출력을 이스케이프하여 XSS를 방지하는 데 도움을 줍니다(Twig와 Latte가 이렇게 동작하므로 그 이점을 활용하세요). [세션](/awesome-plugins/session)은 아래 설명된 대로 사용자 세션에 CSRF 토큰을 저장하여 CSRF를 방지하는 데 도움을 줄 수 있습니다. [SimplePdo](/learn/simple-pdo)의 PDO 또는 헬퍼와 함께 준비된 문(프리페어드 스테이트먼트)을 사용하면 SQL 인젝션을 예방할 수 있습니다. CORS는 `Flight::start()`가 호출되기 전에 간단한 훅으로 처리할 수 있습니다.

이 모든 방법은 함께 작동하여 웹 애플리케이션의 보안을 유지하는 데 도움을 줍니다. 보안 모범 사례를 배우고 이해하는 것은 항상 최우선 순위에 두어야 합니다. AI 비서에게 "CSP를 비활성화"하거나 트레이드오프를 이해하지 않고 페이지를 로드하기 위해 헤더를 약화시키라고 요청하지 마세요.

## 기본 사용법

### 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 애플리케이션에 이러한 헤더를 추가하는 방법은 여러 가지가 있습니다.

헤더 보안을 확인할 수 있는 훌륭한 웹사이트 두 곳은 [securityheaders.com](https://securityheaders.com/)과 [observatory.mozilla.org](https://observatory.mozilla.org/)입니다. 아래 코드를 설정한 후 이 두 웹사이트를 통해 헤더가 제대로 작동하는지 쉽게 확인할 수 있습니다.

스켈레톤에는 `App\Middleware\SecurityHeadersMiddleware`(요청별 nonce를 사용하는 CSP, 프레임 옵션, HSTS 등)가 포함되어 있습니다. 헤더를 끄는 것보다 의도적으로 확장하는 것을 선호하세요.

#### 직접 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.

```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더를 설정합니다
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더를 설정합니다
// 참고: 이 헤더는 매우 복잡해질 수 있으므로
//  애플리케이션에 맞는 예제를 인터넷에서 참조하는 것이 좋습니다
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더를 설정합니다
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더를 설정합니다
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 전송되는 리퍼러 정보의 양을 제어하기 위해 Referrer-Policy 헤더를 설정합니다
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS를 강제하기 위해 Strict-Transport-Security 헤더를 설정합니다
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 사용할 수 있는 기능과 API를 제어하기 위해 Permissions-Policy 헤더를 설정합니다
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이 헤더들은 `routes.php` 또는 `index.php` 파일 상단에 추가할 수 있습니다.

#### 필터로 추가

다음과 같은 필터/훅에서도 추가할 수 있습니다.

```php
// 필터에 헤더를 추가합니다
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

또한 미들웨어 클래스로 추가할 수 있으며, 이를 적용할 라우트에 대해 가장 큰 유연성을 제공합니다. 일반적으로 이러한 헤더는 모든 HTML 및 API 응답에 적용되어야 합니다.

스켈레톤 스타일 경로 및 네임스페이스(**폴더 대소문자는 `App\Middleware`와 일치해야 합니다**):

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
		// 인라인 스크립트가 있는 경우 부트스트랩의 CSP nonce를 사용하는 것이 좋습니다 (스켈레톤은 csp_nonce를 설정합니다)
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

// app/config/routes.php — 빈 문자열 그룹은 모든 라우트에 대한 전역 미들웨어입니다
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// more routes
}, [SecurityHeadersMiddleware::class]);
```

이전 프로젝트에서는 여전히 `app/middlewares`와 `app\middlewares`를 사용할 수 있습니다. 폴더가 일치하면 작동합니다. 새로운 스켈레톤 앱은 **`app/Middleware/`** 및 **`App\Middleware`**를 사용합니다. [Autoloading](/learn/autoloading)을 참조하세요.

### 사이트 간 요청 위조 (CSRF)

사이트 간 요청 위조(CSRF)는 악성 웹사이트가 사용자의 브라우저를 통해 여러분의 웹사이트에 요청을 보내도록 만드는 공격 유형입니다. 이를 통해 사용자가 인지하지 못하는 사이에 웹사이트에서 작업을 수행할 수 있습니다. Flight는 내장된 CSRF 보호 메커니즘을 제공하지 않지만, 미들웨어를 사용하여 쉽게 직접 구현할 수 있습니다.

#### 설정

먼저 CSRF 토큰을 생성하여 사용자 세션에 저장해야 합니다. 그런 다음 이 토큰을 폼에서 사용하고 폼이 제출될 때 확인할 수 있습니다. 세션 관리를 위해 [flightphp/session](/awesome-plugins/session) 플러그인을 사용하겠습니다.

```php
// CSRF 토큰을 생성하고 사용자 세션에 저장합니다
// (세션 객체를 생성하여 Flight에 연결했다고 가정합니다)
// 자세한 내용은 세션 문서를 참조하세요
Flight::register('session', flight\Session::class);

// 세션당 하나의 토큰만 생성하면 됩니다 (동일 사용자의 여러 탭과 요청에서도 작동)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### 기본 PHP Flight 템플릿 사용

```html
<!-- 폼에서 CSRF 토큰을 사용합니다 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 기타 폼 필드 -->
</form>
```

##### Twig 사용(스켈레톤 기본값)

Twig 함수를 등록하거나 토큰을 모든 폼 뷰에 전달하세요. 전역 + 폼 필드를 사용하는 최소 예제:

```php
// Twig를 구성할 때 (예: services.php)
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

	// CSRF 토큰을 출력하는 사용자 정의 함수를 설정합니다
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
	<!-- 기타 폼 필드 -->
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
	// more routes
}, [CsrfMiddleware::class]);
```

##### 이벤트 필터

```php
// 이 미들웨어는 요청이 POST인지 확인하고, POST인 경우 CSRF 토큰이 유효한지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 csrf 토큰을 가져옵니다
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// 또는 JSON 응답의 경우
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### 사이트 간 스크립팅 (XSS)

사이트 간 스크립팅(XSS)은 악성 폼 입력이 웹사이트에 코드를 주입할 수 있는 공격 유형입니다. 이러한 기회의 대부분은 최종 사용자가 작성하는 폼 값에서 발생합니다. 사용자의 출력을 **절대** 신뢰하지 마세요! 항상 모든 사용자가 최고의 해커라고 가정하세요. 사용자는 페이지에 악성 JavaScript나 HTML을 주입할 수 있습니다. 이 코드는 사용자의 정보를 훔치거나 웹사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight의 뷰 클래스나 [Twig](/awesome-plugins/twig) 또는 [Latte](/awesome-plugins/latte) 같은 템플릿 엔진을 사용하면 XSS 공격을 방지하기 위해 출력을 쉽게 이스케이프할 수 있습니다.

```php
// 사용자가 교묘하게 이 값을 이름으로 사용하려고 한다고 가정해 보겠습니다
$name = '<script>alert("XSS")</script>';

// 이렇게 하면 출력이 이스케이프됩니다
Flight::view()->set('name', $name);
// 출력 결과는 다음과 같습니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig(스켈레톤 기본값)와 Latte는 기본적으로 자동 이스케이프됩니다 — 원시 PHP echo보다 이들을 선호하세요
Flight::render('template', ['name' => $name]);
// Twig: {{ name }} → 이스케이프됨
// 콘텐츠가 완전히 신뢰할 수 있는 경우가 아니라면 |raw / 이스케이프되지 않은 출력을 피하세요
```

### SQL 인젝션

SQL 인젝션은 악성 사용자가 데이터베이스에 SQL 코드를 주입할 수 있는 공격 유형입니다. 이를 통해 데이터베이스에서 정보를 훔치거나 데이터베이스에서 작업을 수행할 수 있습니다. 다시 말하지만 사용자의 입력을 **절대** 신뢰하지 마세요! 항상 사용자가 피를 원한다고 가정하세요. 준비된 문(프리페어드 스테이트먼트)을 사용하세요. [SimplePdo](/learn/simple-pdo) 헬퍼는 이를 기본 경로로 만듭니다.

```php
// Flight::db()가 SimplePdo로 등록되었다고 가정합니다 (또는 컨트롤러에 SimplePdo를 주입)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (권장) — 바인딩된 파라미터를 사용하는 한 줄 처리
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? 자리 표시자를 사용한 동일한 방식
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

스켈레톤 스타일 컨트롤러에서는 `Flight::db()`보다는 생성자 주입을 통해 `SimplePdo`를 주입하는 것을 선호하세요. 테스트와 AI 생성 코드가 일관성을 유지할 수 있습니다 ([DIC](/learn/dependency-injection-container)).

#### 안전하지 않은 예제

아래는 왜 SQL 준비된 문을 사용하여 아래와 같은 무해해 보이는 예제로부터 보호하는지 보여주는 이유입니다.

```php
// 최종 사용자가 웹 폼을 작성합니다.
// 폼 값에 대해 해커가 다음과 같은 값을 넣습니다.
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// 쿼리가 작성된 후 다음과 같이 보입니다.
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 이상해 보이지만 작동하는 유효한 쿼리입니다. 실제로
// 이것은 모든 사용자를 반환하는 매우 흔한 SQL 인젝션 공격입니다.

var_dump($users); // 이것은 데이터베이스의 모든 사용자를 덤프하며, 단일 사용자 이름만 덤프하지 않습니다
```

### 비밀값 및 구성

- 비밀값은 커밋된 `config.php` 샘플이 아닌 **`.env`**(또는 실제 환경)에 넣으세요.
- 스켈레톤 규칙: `config.php`에는 리터럴 기본값을 두고 부트스트랩에서 env를 병합합니다. 컨트롤러 내부에서 `$_ENV`를 읽지 마세요. 대신 구성을 주입하세요. [Configuration](/learn/configuration)을 참조하세요.
- API 키, DB 비밀번호, 세션 암호화 키를 커밋하지 마세요. AI 도구가 안전하지 않은 지름길을 만들지 않도록 **`SECURITY.md`**를 참조하게 하세요.

### JSONP 콜백 검증

Flight의 `Flight::jsonp()` 메서드를 사용하는 경우, Flight가 JSONP 콜백 파라미터 이름을 엄격한 허용 목록 정규식(`/^[A-Za-z_$][\w$.]{0,127}$/`)으로 검증한다는 점에 유의하세요. 이 패턴과 일치하지 않는 콜백 이름은 Flight가 예외를 발생시켜 악성 콜백 값을 통한 임의 JavaScript 주입을 방지합니다.

이 검증은 내장되어 있으며 추가 구성이 필요하지 않지만, JSONP 엔드포인트에서 예기치 않은 오류를 디버깅할 때 알아두면 유용합니다.

### CORS

교차 출처 리소스 공유(CORS)는 웹 페이지의 많은 리소스(예: 글꼴, JavaScript 등)가 리소스가 제공된 도메인 외부의 다른 도메인에서 요청될 수 있도록 하는 메커니즘입니다. Flight에는 내장된 기능이 없지만, `Flight::start()` 메서드가 호출되기 전에 실행되는 훅으로 쉽게 처리할 수 있습니다.

```php
// app/Utils/CorsUtil.php (스켈레톤: PascalCase Utils 폴더 → App\Utils)

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
		// 허용할 호스트를 여기에 사용자 지정하세요.
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

Flight에는 보안에 직접적인 영향을 미치는 몇 가지 엔진 설정이 있습니다. 이를 올바르게 설정하는 것은 애플리케이션을 강화하는 가장 쉬운 방법 중 하나입니다.

#### `flight.allow_method_override`

기본적으로 Flight는 `X-HTTP-Method-Override` 헤더 또는 POST 본문의 `_method` 필드를 사용하여 클라이언트가 요청의 HTTP 메서드를 재정의할 수 있도록 허용합니다. 이는 `GET`/`POST`만 보낼 수 있는 HTML 폼에 유용하지만, 예상하지 못한 경우 위험할 수 있습니다. 공격자는 일반 폼을 통해 `DELETE` 또는 `PUT` 요청을 위조할 수 있습니다.

애플리케이션이 이 동작에 의존하지 않는 경우(예: 모든 HTTP 동사를 보낼 수 있는 최신 클라이언트 또는 JavaScript 프런트엔드가 사용하는 API를 구축하는 경우), 이 기능을 비활성화해야 합니다.

```php
// index.php 또는 부트스트랩 파일에서 Flight::start() 호출 전에
Flight::set('flight.allow_method_override', false);
```

기본값은 이전 버전과의 호환성을 위해 `true`이지만, 재정의 기능을 명시적으로 필요로 하지 않는 애플리케이션에는 **`false`로 설정하는 것을 강력히 권장합니다**.

#### `flight.debug`

Flight에는 처리되지 않은 예외가 발생할 때 브라우저에서 자세한 오류 정보(예외 메시지, 코드, 전체 스택 추적)를 렌더링할지 제어하는 `flight.debug` 설정이 있습니다. 기본값은 `false`이며, 이 경우 일반적인 `500 Internal Server Error` 메시지만 표시되고 내부 세부 정보는 클라이언트에 유출되지 않습니다.

프로덕션 서버에서는 절대 활성화하지 마세요. 로컬 또는 스테이징 환경에서만 사용하세요.

```php
// 로컬 개발에서만 안전 — 프로덕션에서는 절대 사용 금지
Flight::set('flight.debug', true);
```

`flight.debug`가 `false`(기본값)인 경우에도 `flight.log_errors`를 활성화하여 오류를 캡처할 수 있습니다.

```php
// 오류를 클라이언트에 노출하지 않고 서버 측에 기록합니다
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight의 내장 `View` 클래스는 절대 경로 또는 `flight.views.path`를 벗어나는 상대 이름(예: `../`)을 사용하는 템플릿도 자유롭게 포함합니다. 이는 의도적으로 폴더 간에 템플릿을 공유하는 애플리케이션을 위한 것이지만, 템플릿 이름이 신뢰할 수 없는 입력에서 비롯된다면 경로 탐색 위험이 될 수도 있습니다.

`flight.views.restrict_to_path`는 **기본적으로 꺼져 있으므로** 기존 앱이 계속 작동합니다. 문서화된 이유가 없는 한 이 기능을 켜세요.

```php
// index.php 또는 부트스트랩 파일에서 Flight::start() 호출 전에
Flight::set('flight.views.restrict_to_path', true);
```

Engine은 뷰가 생성될 때 이 설정을 `View::$restrictToPath`에 적용합니다(`flight.views.path` 및 `flight.views.extension`과 동일한 패턴). 이 기능을 켜면:

- `render()` 및 `fetch()`는 실제 경로가 구성된 뷰 디렉터리 안에 있는 파일만 포함합니다(외부를 가리키는 심볼릭 링크도 거부됨).
- `exists()`는 동일한 경로에 대해 예외를 던지는 대신 `false`를 반환합니다.
- `getTemplate()` 자체는 변경되지 않으며 항상 그랬던 것처럼 경로를 반환합니다.
- 차단된 파일은 `Template file is outside the views path.` 예외를 던집니다. 누락된 파일은 기존의 `Template file not found: ...` 메시지를 계속 던집니다.

Twig 또는 Latte를 뷰 디렉터리를 가리키는 자체 파일 시스템 로더와 함께 사용하는 경우 해당 엔진에는 이미 해당 루트에 대한 템플릿이 포함되어 있습니다. 그래도 Flight 네이티브 `View`에 대해 이 기능을 켜서 `Flight::view()->render()` / `fetch()`를 호출하는 모든 코드가 동일한 보호를 받도록 하세요. 공식 [skeleton](https://github.com/flightphp/skeleton)은 부트스트랩에서 이 기능을 활성화합니다.

#### 권장 프로덕션 구성

```php
// index.php 또는 앱 구성 / 부트스트랩에서 적용
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### 오류 처리

프로덕션 환경에서는 공격자에게 정보를 노출하지 않도록 민감한 오류 세부 정보를 숨기세요. 프로덕션에서는 `display_errors`를 `0`으로 설정하고 오류를 표시하는 대신 기록하세요.

```php
// bootstrap.php 또는 index.php에서

// 이것을 app/config/config.php에 추가하세요
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // 오류 표시 비활성화
    ini_set('log_errors', 1);     // 대신 오류를 기록
    ini_set('error_log', '/path/to/error.log');
}

// 라우트 또는 컨트롤러에서
// 제어된 오류 응답에는 Flight::halt()를 사용하세요
Flight::halt(403, 'Access denied');
```

### 입력 정화

사용자 입력을 절대 신뢰하지 마세요. 악성 데이터가 들어오는 것을 방지하기 위해 처리 전에 [filter_var](https://www.php.net/manual/en/function.filter-var.php)를 사용하여 정화하세요. 앱 코드에서 원시 `$_GET` / `$_POST` 대신 `$app->request()`(또는 `Flight::request()`)를 통해 입력을 읽는 것을 선호하세요.

```php
// $_POST['input']과 $_POST['email']이 포함된 $_POST 요청을 가정해 봅니다

// 문자열 입력을 정화합니다
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// 이메일을 정화합니다
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### 비밀번호 해싱

PHP 내장 함수인 [password_hash](https://www.php.net/manual/en/function.password-hash.php)와 [password_verify](https://www.php.net/manual/en/function.password-verify.php)를 사용하여 비밀번호를 안전하게 저장하고 안전하게 검증하세요. 비밀번호는 평문으로 저장해서는 안 되며, 복호화 가능한 방식으로 암호화해서도 안 됩니다. 해싱은 데이터베이스가 유출되더라도 실제 비밀번호가 보호되도록 보장합니다.

```php
$password = Flight::request()->data->password;
// 비밀번호를 저장할 때 해시합니다 (예: 등록 시)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 비밀번호를 확인합니다 (예: 로그인 시)
if (password_verify($password, $stored_hash)) {
    // 비밀번호가 일치합니다
}
```

### 요청 속도 제한

캐시를 사용하여 요청 속도를 제한함으로써 무차별 대입 공격이나 서비스 거부 공격을 방지하세요.

```php
// flightphp/cache가 설치되고 등록되어 있다고 가정
// 필터에서 flightphp/cache를 사용
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

## 참고 항목

- [Sessions](/awesome-plugins/session) - 사용자 세션을 안전하게 관리하는 방법.
- [Templates](/learn/templates) - Twig/Latte 자동 이스케이프 및 XSS.
- [SimplePdo](/learn/simple-pdo) - 준비된 문(프리페어드 스테이트먼트)을 사용하는 데이터베이스 헬퍼.
- [PdoWrapper](/learn/pdo-wrapper) - 더 이상 사용되지 않음; 새 코드에서는 SimplePdo를 사용하세요.
- [Middleware](/learn/middleware) - 보안 헤더 추가 과정을 단순화하기 위해 미들웨어를 사용하는 방법.
- [Configuration](/learn/configuration) - `.env` 대 리터럴 구성, 프로덕션 플래그.
- [AI & Developer Experience](/learn/ai) - 에이전트를 위해 보안 정책을 `SECURITY.md`에 유지하세요.
- [Responses](/learn/responses) - 보안 헤더로 HTTP 응답을 사용자 지정하는 방법.
- [Requests](/learn/requests) - 사용자 입력을 처리하고 정화하는 방법.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 입력 정화를 위한 PHP 함수.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 안전한 비밀번호 해싱을 위한 PHP 함수.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - 해시된 비밀번호 확인을 위한 PHP 함수.

## 문제 해결

- Flight Framework 구성 요소 관련 문제에 대한 문제 해결 정보는 위의 "참고 항목" 섹션을 참조하세요.
- CSP가 스크립트를 차단하는 경우 nonce(스켈레톤 패턴)를 추가하거나 특정 출처를 허용 목록에 추가하세요. 계획 없이 `script-src *`를 설정하지 마세요.

## 변경 로그

- 문서 – 스켈레톤 `App\Middleware`, Twig CSRF/XSS 메모, SimplePdo, 비밀값/`.env`, AI 친화적 프로젝트를 위한 `SECURITY.md`
- 문서 – Flight 구성 강화 아래 `flight.views.restrict_to_path` 문서화 (네이티브 뷰에 대한 선택적 경로 포함)
- v3.18.1 - `flight.allow_method_override`, `flight.debug`, JSONP 콜백 검증을 다루는 Flight 구성 강화 섹션 추가.
- v3.1.0 - CORS, 오류 처리, 입력 정화, 비밀번호 해싱, 요청 속도 제한 섹션 추가.
- v2.0 - XSS 방지를 위해 기본 뷰에 이스케이프 추가.