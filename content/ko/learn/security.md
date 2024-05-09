# 보안

웹 응용 프로그램에 대한 보안은 중요합니다. 응용 프로그램이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight은 웹 응용 프로그램의 보안을 지원하기 위한 여러 기능을 제공합니다.

## 헤더

HTTP 헤더는 웹 응용 프로그램을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 이러한 헤더를 응용 프로그램에 추가하는 방법은 여러 가지가 있습니다.

보안 헤더를 확인할 수 있는 두 가지 좋은 웹 사이트는 [securityheaders.com](https://securityheaders.com/)과 [observatory.mozilla.org](https://observatory.mozilla.org/)입니다.

### 수동으로 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더를 설정합니다
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더를 설정합니다
// 참고: 이 헤더는 매우 복잡해질 수 있으므로
//  응용 프로그램에 대한 인터넷의 예제를 참고하시기 바랍니다
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더를 설정합니다
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더를 설정합니다
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 리퍼러 정보를 전송하는 방식을 제어하기 위해 Referrer-Policy 헤더를 설정합니다
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS를 강제하기 위해 Strict-Transport-Security 헤더를 설정합니다
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 사용할 수 있는 기능 및 API를 제어하기 위해 Permissions-Policy 헤더를 설정합니다
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이것들은 `bootstrap.php` 또는 `index.php` 파일의 맨 위에 추가할 수 있습니다.

### 필터로 추가

다음과 같이 필터/훅에 추가할 수도 있습니다:

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

미들웨어 클래스로도 추가할 수 있습니다. 코드를 깔끔하고 구성되어 있게 유지하는 좋은 방법입니다.

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

// index.php 또는 라우트가 있는 곳
// FYI, 이 빈 문자열 그룹은 모든 라우트에 대한 전역 미들웨어로 작동합니다.
// 물론 동일한 작업을 수행하거나 특정 라우트에만 추가할 수도 있습니다.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 라우트
}, [ new SecurityHeadersMiddleware() ]);
```


## 교차 사이트 요청 위조 (CSRF)

교차 사이트 요청 위조 (CSRF)는 악의적인 웹 사이트가 사용자 브라우저로 요청을 보내도록하는 공격 유형입니다. 이를 통해 사용자의 동의 없이 웹 사이트에서 작업을 수행할 수 있습니다. Flight에는 기본 CSRF 방어 메커니즘이 제공되지 않지만 미들웨어를 사용하여 쉽게 직접 구현할 수 있습니다.

### 설정

먼저 CSRF 토큰을 생성하고 사용자 세션에 저장해야 합니다. 그런 다음 이 토큰을 양식에서 사용하고 양식을 제출할 때 확인할 수 있습니다.

```php
// CSRF 토큰을 생성하고 사용자 세션에 저장합니다
// (Flight에 세션 객체를 생성했다고 가정)
// 세션당 한 번의 토큰만 생성하면 됩니다 (같은 사용자의 여러 탭과 요청에서 작동합니다)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- 양식에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 다른 양식 필드 -->
</form>
```

#### Latte 사용

Latte 템플릿에서 CSRF 토큰을 출력하기 위해 사용자 지정 함수를 설정할 수도 있습니다.

```php
// CSRF 토큰을 출력하는 사용자 지정 함수 설정
// 참고: 뷰는 뷰 엔진으로 Latte로 구성되어 있습니다
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

이제 Latte 템플릿에서 `csrf()` 함수를 사용하여 CSRF 토큰을 출력할 수 있습니다.

```html
<form method="post">
	{csrf()}
	<!-- 다른 양식 필드 -->
</form>
```

간단하고 명료하지요?

### CSRF 토큰 확인

이벤트 필터를 사용하여 CSRF 토큰을 확인할 수 있습니다:

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고 그렇다면 CSRF 토큰이 유효한지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 양식 값에서 csrf 토큰을 가져옵니다
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '잘못된 CSRF 토큰');
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
				Flight::halt(403, '잘못된 CSRF 토큰');
			}
		}
	}
}

// index.php 또는 라우트가 있는 곳
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 라우트
}, [ new CsrfMiddleware() ]);
```


## XSS (교차 사이트 스크립팅)

교차 사이트 스크립팅 (XSS)은 악성 웹 사이트가 코드를 웹 사이트에 주입할 수 있는 공격 유형입니다. 대부분은 사용자가 작성하는 양식 값에서 가능합니다. 사용자의 출력을 절대로 신뢰해서는 안 됩니다! 항상 사용자를 세계 최고의 해커로 간주하십시오. 악성 JavaScript 또는 HTML을 페이지에 주입하여 사용자의 정보를 도용하거나 웹 사이트에서 작업을 수행할 수 있습니다. Flight의 view 클래스를 사용하여 XSS 공격을 방지하기 위해 쉽게 출력을 이스케이프할 수 있습니다.

```php
// 사용자가 이를 이름으로 사용하려고 시도하는 경우를 가정해 봅시다
$name = '<script>alert("XSS")</script>';

// 이렇게 출력이 이스케이프됩니다
Flight::view()->set('name', $name);
// 이렇게 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 뷰 클래스로 Latte와 같은 것을 사용하면 이도 자동으로 이스케이프됩니다.
Flight::view()->render('template', ['name' => $name]);
```

## SQL 인젝션

SQL 인젝션은 악성 사용자가 데이터베이스에 SQL 코드를 주입할 수 있는 공격 유형입니다. 이를 통해 데이터베이스에서 정보를 도용하거나 데이터베이스에서 작업을 수행할 수 있습니다. 다시 한 번 사용자로부터의 입력을 절대로 신뢰해서는 안 됩니다! 항상 사용자가 극악한 짓을 한다고 가정하십시오. `PDO` 객체에서 준비된 문을 사용하면 SQL 인젝션을 방지할 수 있습니다.

```php
// Flight::db()를 PDO 객체로 등록 했다고 가정하면
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하면 한 줄로 간단하게 수행할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? 자리 표시자를 가진 PDO 객체로도 똑같이 할 수 있습니다
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 이렇게 한다는 것을 절대로 약속해주세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 왜냐하면 $username = "' OR 1=1; -- "; 일 수 있으니까요
// 쿼리가 이렇게 구성됩니다
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 이상하게 보일 수 있지만 동작하는 유효한 쿼리입니다. 실제로
// 이것은 모든 사용자를 반환하는 매우 흔한 SQL 인젝션 공격입니다.
```

## CORS

교차 출처 리소스 공유 (CORS)는 웹 페이지의 많은 리소스 (예: 글꼴, JavaScript 등)를 자신과 다른 도메인에서 요청할 수 있는 메커니즘입니다. Flight에 기본 기능은 없지만 CSRF와 유사하게 미들웨어나 이벤트 필터를 사용하여 쉽게 처리할 수 있습니다.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers",
					$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// 여기서 허용된 호스트를 사용자 정의합니다.
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $_SERVER['HTTP_ORIGIN']);
		}
	}
}

// index.php 또는 라우트가 있는 곳
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 결론

보안은 중요하며 웹 응용 프로그램을 안전하게 유지하는 것이 중요합니다. Flight은 웹 응용 프로그램을 안전하게 유지하는 데 도움이 되는 여러 가지 기능을 제공하지만 항상 주의해야 하며 사용자 데이터를 안전하게 유지할 수 있도록 최선을 다해야 합니다. 최악을 가정하고 사용자 입력을 신뢰해서는 안 됩니다. 출력을 이스케이프하고 SQL 인젝션을 방지하기 위해 준비된 문을 사용하십시오. CSRF 및 CORS 공격으로부터 라우트를 보호하기 위해 항상 미들웨어를 사용하십시오. 모든 이러한 것들을 수행한다면 안전한 웹 응용 프로그램을 구축하는 길에 올라설 수 있을 것입니다.