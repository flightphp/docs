# 보안

## 개요

웹 애플리케이션에서 보안은 매우 중요한 문제입니다. 애플리케이션이 안전하고 사용자 데이터가 보호되도록 해야 합니다. Flight는 웹 애플리케이션을 보호하는 데 도움이 되는 여러 기능을 제공합니다.

## 이해

웹 애플리케이션을 구축할 때 인지해야 할 일반적인 보안 위협이 여러 가지 있습니다. 가장 일반적인 위협 중 일부는 다음과 같습니다:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates)는 XSS를 방지하기 위해 기본적으로 출력을 이스케이프하므로 이를 기억할 필요가 없습니다. [Sessions](/awesome-plugins/session)은 아래에 설명된 대로 사용자의 세션에 CSRF 토큰을 저장하여 CSRF를 방지하는 데 도움이 됩니다. PDO와 함께 준비된 문장을 사용하면 SQL 인젝션 공격을 방지할 수 있습니다(또는 [PdoWrapper](/learn/pdo-wrapper) 클래스에서 편리한 메서드를 사용). CORS는 `Flight::start()`가 호출되기 전에 간단한 훅으로 처리할 수 있습니다.

이러한 모든 방법이 함께 작동하여 웹 애플리케이션을 안전하게 유지하는 데 도움이 됩니다. 보안 모범 사례를 배우고 이해하는 것이 항상 최우선이 되어야 합니다.

## 기본 사용법

### 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 클릭재킹, XSS 및 기타 공격을 방지하기 위해 헤더를 사용할 수 있습니다. 
이러한 헤더를 애플리케이션에 추가하는 방법은 여러 가지가 있습니다.

헤더의 보안을 확인할 수 있는 훌륭한 웹사이트 두 곳은 [securityheaders.com](https://securityheaders.com/)과 
[observatory.mozilla.org](https://observatory.mozilla.org)입니다. 아래 코드를 설정한 후, 이 두 웹사이트에서 헤더가 작동하는지 쉽게 확인할 수 있습니다.

#### 수동 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더 설정
// 주의: 이 헤더는 매우 복잡할 수 있으므로, 애플리케이션에 맞는 인터넷 예제를 참조하세요
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 참조자 정보 전송을 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS 강제 설정을 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 사용 가능한 기능과 API를 제어하기 위해 Permissions-Policy 헤더 설정
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이것들은 `routes.php` 또는 `index.php` 파일의 상단에 추가할 수 있습니다.

#### 필터로 추가

다음과 같은 필터/훅에서 추가할 수도 있습니다: 

```php
// 필터에서 헤더 추가
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

이것들을 미들웨어 클래스에 추가할 수도 있습니다. 이는 어떤 경로에 적용할지 가장 큰 유연성을 제공합니다. 일반적으로 이러한 헤더는 모든 HTML 및 API 응답에 적용되어야 합니다.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

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
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php 또는 경로가 있는 곳
// 참고: 이 빈 문자열 그룹은 모든 경로에 대한 전역 미들웨어로 작동합니다.
// 물론 특정 경로에만 추가할 수도 있습니다.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 경로
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF)는 악성 웹사이트가 사용자의 브라우저를 통해 웹사이트에 요청을 보내는 공격 유형입니다. 
이것은 사용자의 지식 없이 웹사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight는 내장된 CSRF 보호 메커니즘을 제공하지 않지만, 미들웨어를 사용하여 쉽게 구현할 수 있습니다.

#### 설정

먼저 CSRF 토큰을 생성하고 사용자의 세션에 저장해야 합니다. 그런 다음 이 토큰을 폼에 사용하고 폼이 제출될 때 확인할 수 있습니다. 세션을 관리하기 위해 [flightphp/session](/awesome-plugins/session) 플러그인을 사용하겠습니다.

```php
// CSRF 토큰 생성 및 사용자 세션에 저장
// (Flight에 세션 객체를 생성하고 연결했다고 가정)
// 더 많은 정보는 세션 문서를 참조하세요
Flight::register('session', flight\Session::class);

// 세션당 하나의 토큰만 생성하면 됩니다 (같은 사용자의 여러 탭 및 요청에서 작동)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### 기본 PHP Flight 템플릿 사용

```html
<!-- 폼에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 다른 폼 필드 -->
</form>
```

##### Latte 사용

Latte 템플릿에서 CSRF 토큰을 출력하는 사용자 정의 함수를 설정할 수도 있습니다.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// 다른 구성...

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
	<!-- 다른 폼 필드 -->
</form>
```

#### CSRF 토큰 확인

여러 방법으로 CSRF 토큰을 확인할 수 있습니다.

##### 미들웨어

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

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

// index.php 또는 경로가 있는 곳
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 경로
}, [ CsrfMiddleware::class ]);
```

##### 이벤트 필터

```php
// 이 미들웨어는 요청이 POST인지 확인하고, 그렇다면 CSRF 토큰이 유효한지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 CSRF 토큰 캡처
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// 또는 JSON 응답의 경우
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS)는 악성 폼 입력이 웹사이트에 코드를 주입할 수 있는 공격 유형입니다. 이러한 기회는 대부분 최종 사용자가 채우는 폼 값에서 발생합니다. 사용자 출력은 **절대** 신뢰하지 마세요! 그들이 세계 최고의 해커라고 항상 가정하세요. 그들은 페이지에 악성 JavaScript 또는 HTML을 주입할 수 있습니다. 이 코드는 사용자 정보를 훔치거나 웹사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight의 뷰 클래스나 [Latte](/awesome-plugins/latte)와 같은 다른 템플릿 엔진을 사용하면 출력을 이스케이프하여 XSS 공격을 쉽게 방지할 수 있습니다.

```php
// 사용자가 영리하게 이름으로 이것을 사용하려고 한다고 가정
$name = '<script>alert("XSS")</script>';

// 이 출력은 이스케이프됩니다
Flight::view()->set('name', $name);
// 출력: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 뷰 클래스로 등록된 Latte와 같은 것을 사용하면 이것도 자동으로 이스케이프됩니다.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection은 악성 사용자가 데이터베이스에 SQL 코드를 주입할 수 있는 공격 유형입니다. 이것은 데이터베이스에서 정보를 훔치거나 데이터베이스에서 작업을 수행하는 데 사용될 수 있습니다. 다시 사용자 입력은 **절대** 신뢰하지 마세요! 그들이 피를 갈구한다고 항상 가정하세요. `PDO` 객체에서 준비된 문장을 사용하면 SQL 인젝션을 방지할 수 있습니다.

```php
// Flight::db()가 PDO 객체로 등록되어 있다고 가정
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하면 한 줄로 쉽게 할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? 플레이스홀더로 PDO 객체와 동일한 작업을 할 수 있습니다
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### 비보안 예제

아래는 SQL 준비된 문장을 사용하여 다음과 같은 무해한 예로부터 보호하는 이유입니다:

```php
// 최종 사용자가 웹 폼을 채웁니다.
// 폼 값으로 해커가 이런 것을 입력합니다:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// 쿼리가 빌드된 후 이렇게 보입니다
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 이상해 보이지만 유효한 쿼리이며 작동합니다. 실제로,
// 모든 사용자를 반환하는 매우 일반적인 SQL 인젝션 공격입니다.

var_dump($users); // 데이터베이스의 모든 사용자를 덤프합니다, 단일 사용자 이름만이 아닙니다
```

### CORS

Cross-Origin Resource Sharing (CORS)는 웹 페이지에서 리소스(예: 폰트, JavaScript 등)가 원본 도메인 외부의 다른 도메인에서 요청될 수 있도록 하는 메커니즘입니다. Flight에는 내장된 기능이 없지만, `Flight::start()` 메서드가 호출되기 전에 실행되는 훅으로 쉽게 처리할 수 있습니다.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
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
		// 허용된 호스트를 여기에 사용자 지정하세요.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php 또는 경로가 있는 곳
$CorsUtil = new CorsUtil();

// start가 실행되기 전에 실행되어야 합니다.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### 오류 처리
프로덕션에서 민감한 오류 세부 정보를 숨겨 공격자에게 정보를 유출하지 마세요. 프로덕션에서 `display_errors`를 `0`으로 설정하고 오류를 표시하는 대신 로그를 기록하세요.

```php
// bootstrap.php 또는 index.php에서

// app/config/config.php에 이것을 추가하세요
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // 오류 표시 비활성화
    ini_set('log_errors', 1);     // 오류 로그 기록
    ini_set('error_log', '/path/to/error.log');
}

// 경로 또는 컨트롤러에서
// 제어된 오류 응답을 위해 Flight::halt() 사용
Flight::halt(403, 'Access denied');
```

### 입력 정제
사용자 입력을 절대 신뢰하지 마세요. 악성 데이터가 스며드는 것을 방지하기 위해 처리 전에 [filter_var](https://www.php.net/manual/en/function.filter-var.php)를 사용하여 정제하세요.

```php

// $_POST 요청에 $_POST['input']과 $_POST['email']이 있다고 가정

// 문자열 입력 정제
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// 이메일 정제
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### 비밀번호 해싱
비밀번호를 안전하게 저장하고 PHP의 내장 함수인 [password_hash](https://www.php.net/manual/en/function.password-hash.php)와 [password_verify](https://www.php.net/manual/en/function.password-verify.php)를 사용하여 안전하게 확인하세요. 비밀번호는 평문으로 저장해서는 안 되며, 가역적인 방법으로 암호화해서도 안 됩니다. 해싱은 데이터베이스가 손상되더라도 실제 비밀번호가 보호되도록 합니다.

```php
$password = Flight::request()->data->password;
// 저장 시(예: 등록 중) 비밀번호 해싱
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 비밀번호 확인(예: 로그인 중)
if (password_verify($password, $stored_hash)) {
    // 비밀번호 일치
}
```

### 속도 제한
캐시를 사용하여 요청 속도를 제한하여 무차별 대입 공격이나 서비스 거부 공격으로부터 보호하세요.

```php
// flightphp/cache가 설치되고 등록되어 있다고 가정
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

## 관련 항목
- [Sessions](/awesome-plugins/session) - 사용자 세션을 안전하게 관리하는 방법.
- [Templates](/learn/templates) - 출력을 자동 이스케이프하여 XSS를 방지하는 템플릿 사용.
- [PDO Wrapper](/learn/pdo-wrapper) - 준비된 문장으로 간단한 데이터베이스 상호작용.
- [Middleware](/learn/middleware) - 보안 헤더 추가 과정을 단순화하기 위한 미들웨어 사용 방법.
- [Responses](/learn/responses) - 보안 헤더로 HTTP 응답 사용자 지정 방법.
- [Requests](/learn/requests) - 사용자 입력 처리 및 정제 방법.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 입력 정제를 위한 PHP 함수.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 안전한 비밀번호 해싱을 위한 PHP 함수.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - 해싱된 비밀번호 확인을 위한 PHP 함수.

## 문제 해결
- Flight Framework 구성 요소와 관련된 문제에 대한 문제 해결 정보는 위의 "관련 항목" 섹션을 참조하세요.

## 변경 로그
- v3.1.0 - CORS, 오류 처리, 입력 정제, 비밀번호 해싱 및 속도 제한 섹션 추가.
- v2.0 - XSS 방지를 위한 기본 뷰 이스케이핑 추가.