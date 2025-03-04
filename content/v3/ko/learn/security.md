# 보안

웹 애플리케이션에서는 보안이 매우 중요합니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight는 웹 애플리케이션을 보호하는 데 도움이 되는 다양한 기능을 제공합니다.

## 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 clickjacking, XSS 및 기타 공격을 방지할 수 있습니다. 이러한 헤더를 애플리케이션에 추가하는 여러 가지 방법이 있습니다.

보안 헤더를 확인할 수 있는 두 가지 훌륭한 웹 사이트는 [securityheaders.com](https://securityheaders.com/) 및 [observatory.mozilla.org](https://observatory.mozilla.org/)입니다.

### 수동 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// clickjacking을 방지하기 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더 설정
// 참고: 이 헤더는 매우 복잡해질 수 있습니다. 따라서 응용 프로그램에 대한 인터넷의 예제를 참고해야 합니다.
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 리퍼러 정보 전송 양을 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS를 강제 적용하기 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 사용할 수 있는 기능 및 API를 제어하기 위해 Permissions-Policy 헤더 설정
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이를 `bootstrap.php` 또는 `index.php` 파일 상단에 추가할 수 있습니다.

### 필터로 추가

다음과 같이 필터/후크에 추가할 수도 있습니다:

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

### 미들웨어로 추가

미들웨어 클래스로도 추가할 수 있습니다. 이는 코드를 깔끔하고 구조화된 상태로 유지하는 좋은 방법입니다.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php 또는 라우트가 있는 위치
// 참고: 이 빈 문자열 그룹은
// 모든 라우트에 대한 전역 미들웨어 역할을 합니다. 물론 동일한 작업을 수행하고 특정 라우트에만 이를 추가할 수도 있습니다.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 추가 라우트
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF)는 악성 웹 사이트가 사용자 브라우저로 해당 웹 사이트로 요청을 보내도록 유도하는 공격 유형입니다. 이를 통해 사용자의 동의 없이 해당 웹 사이트에서 작업을 수행할 수 있습니다. Flight에 내장된 CSRF 방지 메커니즘은 제공하지 않지만 미들웨어를 사용하여 손쉽게 직접 구현할 수 있습니다.

### 설정

먼저 CSRF 토큰을 생성하고 사용자 세션에 저장해야 합니다. 그런 다음 이 토큰을 폼에서 사용하고 폼이 제출될 때 확인할 수 있습니다.

```php
// CSRF 토큰을 생성하고 사용자 세션에 저장
// (Flight에 세션 객체를 생성하여 연결한 것으로 가정)
// 자세한 내용은 세션 문서를 참조하십시오.
Flight::register('session', \Ghostff\Session\Session::class);

// 세션 당 단일 토큰만 생성하면 됩니다 (동일한 사용자의 여러 탭 및 요청에서 작동하도록 함)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- 폼에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 다른 폼 필드 -->
</form>
```

#### Latte 사용

Latte 템플릿에서 CSRF 토큰을 출력하기 위한 사용자 정의 함수를 설정할 수도 있습니다.

```php
// 출력되는 CSRF 토큰을 위한 사용자 정의 함수 설정
// 참고: 뷰 엔진으로 Latte가 구성된 경우
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

그리고 Latte 템플릿에서 `csrf()` 함수를 사용하여 CSRF 토큰을 출력할 수 있습니다.

```html
<form method="post">
	{csrf()}
	<!-- 다른 폼 필드 -->
</form>
```

간단하고 명료한 내용이 맞죠?

### CSRF 토큰 확인

이벤트 필터를 사용하여 CSRF 토큰을 확인할 수 있습니다:

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고, POST 요청인 경우 CSRF 토큰을 유효성 검사합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 csrf 토큰을 가져옵니다
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '유효하지 않은 CSRF 토큰');
			// 또는 JSON 응답의 경우
			Flight::jsonHalt(['error' => '유효하지 않은 CSRF 토큰'], 403);
		}
	}
});
```

또는 미들웨어 클래스를 사용할 수 있습니다:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, '유효하지 않은 CSRF 토큰');
			}
		}
	}
}

// index.php 또는 라우트가 있는 위치
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 추가 라우트
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS)는 악성 웹 사이트가 코드를 웹사이트에 삽입할 수 있는 공격 유형입니다. 대부분의 기회는 엔드 유저가 제출할 서식 값에서 나옵니다. 사용자들로부터 전달되는 출력을 신뢰해서는 **안 됩니다**! 언제나 그들이 최고의 해커인 것으로 가정하십시오. 악성 JavaScript 또는 HTML을 페이지에 삽입하여 사용자 정보를 탈취하거나 웹사이트에서 작업을 수행할 수 있습니다. Flight의 뷰 클래스를 사용하면 XSS 공격을 방지하기 위해 출력을 쉽게 이스케이프할 수 있습니다.

```php
// 사용자가 이름으로 다음을 시도하고 있다고 가정해 봅니다
$name = '<script>alert("XSS")</script>';

// 출력을 이스케이프합니다
Flight::view()->set('name', $name);
// 이렇게 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 뷰 클래스로 등록된 Latte와 같은 것을 사용하면 이 또한 자동으로 이스케이프됩니다.
Flight::view()->render('템플릿', ['name' => $name]);
```

## SQL Injection

SQL Injection은 악성 사용자가 데이터베이스로 SQL 코드를 삽입할 수 있는 공격 유형입니다. 이를 통해 데이터베이스에서 정보를 탈취하거나 데이터베이스에서 작업을 수행할 수 있습니다. 다시 한 번 사용자로부터의 입력을 **신뢰해서는 안 됩니다**! 항상 그들이 극악하다고 가정하십시오. `PDO` 객체에서 준비된 문을 사용하여 SQL Injection을 방지할 수 있습니다.

```php
// Flight::db()가 PDO 객체로 등록된 상황을 가정합니다
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용한다면 한 줄로 간단히 수행할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// PDO 객체에서 ? 플레이스홀더를 사용하여 동일한 작업을 수행할 수 있습니다
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 단지 절대 이런 일을 하지 않겠다고 약속해 주세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 왜냐하면 만약 $username = "' OR 1=1; -- "; 이라면 어떻게 될까요?
// 쿼리가 생성된 후 다음과 같이 보입니다
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 이상해 보일 수 있지만, 동작할 유효한 쿼리입니다. 실제로
// 이는 모든 사용자를 반환하는 매우 일반적인 SQL Injection 공격입니다.
```

## CORS

Cross-Origin Resource Sharing (CORS)는 웹 페이지에서 다른 원본의 많은 리소스(글꼴, JavaScript 등)를 요청할 수 있는 메커니즘입니다. Flight에 내장된 기능은 없지만 `Flight::start()` 메서드가 호출되기 전에 실행되는 후크로 쉽게 처리할 수 있습니다.

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
		// 허용하는 호스트를 여기에 맞춤화하세요.
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

// index.php 또는 라우트가 있는 위치
$CorsUtil = new CorsUtil();

// start가 실행되기 전에 실행되어야 합니다.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## 결론

보안은 매우 중요하며 웹 애플리케이션이 안전한지 확인하는 것이 중요합니다. Flight는 웹 애플리케이션을 안전하게 보호하는 데 도움이 되는 다양한 기능을 제공하지만 항상 경계를 효율적으로 유지하고 사용자 데이터를 안전하게 유지할 수 있도록 최선을 다해야 합니다. 늘 가장 나쁜 결과를 가정하고 사용자 입력을 신뢰하지 말아야 합니다. 출력을 항상 이스케이프하고 SQL Injection을 방지하기 위해 준비된 문을 사용해야 합니다. 라우트를 CSRF 및 CORS 공격으로부터 보호하기 위해 항상 미들웨어를 사용해야 합니다. 이러한 모든 조치를 취한다면 안전한 웹 애플리케이션을 개발하는 길을 획득하게 될 것입니다.