# 보안

웹 애플리케이션에서 보안은 중요한 문제입니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight은 웹 애플리케이션의 보안을 돕기 위한 여러 기능을 제공합니다.

## 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 이러한 헤더를 애플리케이션에 추가하는 방법이 여러 가지 있습니다.

헤더의 보안을 확인할 수 있는 두 가지 훌륭한 웹 사이트는 [securityheaders.com](https://securityheaders.com/)과 [observatory.mozilla.org](https://observatory.mozilla.org/)입니다.

### 수동 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더를 설정
// 참고: 이 헤더는 매우 복잡해질 수 있으므로
// 인터넷에서 응용 프로그램에 대한 예제를 참고해야 합니다
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 리퍼러 정보가 전송되는 정도를 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS 강제를 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 어떤 기능과 API를 사용할지 제어하기 위해 Permissions-Policy 헤더 설정
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이러한 헤더는 `bootstrap.php` 또는 `index.php` 파일 상단에 추가할 수 있습니다.

### 필터로 추가

다음과 같이 필터/훅에 추가할 수도 있습니다.

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

미들웨어 클래스로도 추가할 수 있습니다. 이렇게 하면 코드를 깔끔하게 유지할 수 있습니다.

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

// index.php 또는 라우트가 있는 위치에
// FYI, 이 빈 문자열 그룹은 모든 라우트에 대한 전역 미들웨어로 작동합니다. 물론, 특정 라우트에만 추가할 수도 있습니다.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 라우트
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF)는 악의적인 웹 사이트가 사용자의 브라우저에 요청을 보내게 하는 유형의 공격입니다. 이를 통해 사용자의 동의 없이 웹 사이트에서 작업을 수행할 수 있습니다. Flight에는 내장된 CSRF 보호 메커니즘이 없지만 미들웨어를 사용하여 손쉽게 직접 구현할 수 있습니다.

### 설정

먼저 CSRF 토큰을 생성하고 사용자 세션에 저장해야 합니다. 그런 다음 이 토큰을 폼에서 사용하고 제출될 때 확인할 수 있습니다.

```php
// CSRF 토큰을 생성하고 사용자 세션에 저장
// (Flight에 세션 객체를 만들고 연결했다고 가정)
// 세션 당 단일 토큰만 생성하면 됩니다 (동일한 사용자의 여러 탭과 요청에 대해 작동함)
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

Latte 템플릿에서 CSRF 토큰을 출력하는 사용자 지정 함수를 설정할 수도 있습니다.

```php
// CSRF 토큰을 출력하는 사용자 지정 함수 설정
// 참고: 뷰는 뷰 엔진으로 Latte가 구성되었습니다
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

이제 Latte 템플릿에서 `csrf()` 함수를 사용하여 CSRF 토큰을 출력할 수 있습니다.

```html
<form method="post">
	{csrf()}
	<!-- 다른 폼 필드 -->
</form>
```

간단하고 간결하죠?

### CSRF 토큰 확인

이벤트 필터를 사용하여 CSRF 토큰을 확인할 수 있습니다.

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고 그렇다면 CSRF 토큰이 유효한지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 csrf 토큰을 캡처합니다
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '유효하지 않은 CSRF 토큰');
		}
	}
});
```

또는 미들웨어 클래스를 사용할 수 있습니다.

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

// index.php 또는 라우트가 있는 위치에
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 라우트
}, [ new CsrfMiddleware() ]);
```


## Cross Site Scripting (XSS)

Cross Site Scripting (XSS)는 악의적인 웹 사이트가 웹 사이트에 코드를 삽입할 수 있는 공격 유형입니다. 대부분의 기회는 사용자가 작성하는 폼 값에서 발생합니다. 사용자 출력을 신뢰해서는 안 됩니다! 사용자를 세계 최고의 해커로 가정하세요! 악의적인 JavaScript 또는 HTML을 페이지에 삽입할 수 있습니다. 이 코드는 사용자로부터 정보를 도난하거나 웹 사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight의 뷰 클래스를 사용하여 XSS 공격을 방지하기 위해 출력을 쉽게 이스케이프할 수 있습니다.

```php
// 사용자가 이를 이름으로 사용하려고 시도할 정도로 똑똑하다고 가정합시다.
$name = '<script>alert("XSS")</script>';

// 이것은 출력을 이스케이프합니다
Flight::view()->set('name', $name);
// 이것이 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 뷰 클래스로 등록된 Latte 등을 사용하면 이 코드도 자동으로 이스케이프됩니다.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection은 악의적인 사용자가 데이터베이스에 SQL 코드를 삽입할 수 있는 공격 유형입니다. 이는 데이터베이스로부터 정보를 도난하거나 데이터베이스에 작업을 수행하기 위해 사용될 수 있습니다. 다시 말씀드립니다. 사용자 입력을 절대로 신뢰해서는 안 됩니다! 사용자가 피할 것이라고 가정하세요. `PDO` 객체의 준비된 문을 사용하면 SQL Injection을 방지할 수 있습니다.

```php
// Flight::db()를 PDO 객체로 등록했다고 가정
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하면 이를 한 줄로 간단히 수행할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 동일한 일을 ? 자리 표시자를 사용하여 PDO 객체로 수행할 수도 있습니다
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 다음 같은 것을 절대로... 하지 말아주세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 이유는 $username = "' OR 1=1; -- "; 라고 가정하면
// 쿼리가 다음과 같이 만들어집니다
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 이상하게 보일 수 있지만 동작하는 유효한 쿼리입니다. 실제로,
// 이것은 사용자 전체를 반환하는 매우 흔한 SQL Injection 공격입니다.
```

## CORS

Cross-Origin Resource Sharing (CORS)는 웹 페이지에서 다른 도메인에서 많은 리소스(글꼴, JavaScript 등)를 요청할 수 있는 메커니즘입니다. Flight에는 내장된 기능이 없지만 `Flight::start()` 메서드가 호출되기 전에 실행되는 훅으로 쉽게 처리할 수 있습니다.

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
		// 여기서 허용되는 호스트를 사용자 정의로 지정합니다.
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

// index.php 또는 라우트가 있는 위치에
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```

## 결론

보안은 중요하며 웹 애플리케이션을 안전하게 유지해야 합니다. Flight은 웹 애플리케이션을 보호하기 위한 여러 기능을 제공하지만 항상 주의를 기울이고 사용자 데이터를 안전하게 유지할 수 있도록 최선을 다하도록 해야 합니다. 최악의 상황을 상정하고 사용자 입력을 절대로 신뢰하지 마십시오. 항상 출력을 이스케이프하고 SQL Injection을 방지하기 위해 준비된 문을 사용하세요. CSRF와 CORS 공격으로부터 라우트를 보호하기 위해 미들웨어를 항상 사용하세요. 이 모든 것을 수행한다면 안전한 웹 애플리케이션을 구축하는 길을 걷고 있을 것입니다.