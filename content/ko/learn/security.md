# 보안

웹 애플리케이션에서 보안은 매우 중요합니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight는 웹 애플리케이션을 보안하는 데 도움이 되는 여러 기능을 제공합니다.

## 헤더

HTTP 헤더는 웹 애플리케이션을 보안하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 이러한 헤더를 응용 프로그램에 추가하는 여러 방법이 있습니다.

보안 헤더를 확인할 수 있는 두 가지 훌륭한 웹 사이트는 [securityheaders.com](https://securityheaders.com/) 및 [observatory.mozilla.org](https://observatory.mozilla.org/)입니다.

### 수동으로 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.

```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더 설정
// 참고: 이 헤더는 매우 복잡해질 수 있으므로
//  응용 프로그램에 대한 인터넷의 예제를 참고해야 합니다
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Referrer 정보를 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS를 강제하기 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 어떤 기능 및 API를 사용할 수 있는지 제어하기 위해 Permissions-Policy 헤더 설정
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이것들은 `bootstrap.php` 또는 `index.php` 파일 상단에 추가할 수 있습니다.

### 필터로 추가

다음과 같이 필터/훅에 추가할 수도 있습니다.

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

### 미들웨어로 추가

이것들을 미들웨어 클래스로도 추가할 수 있습니다. 이것은 코드를 깔끔하고 조직화된 상태로 유지하는 좋은 방법입니다.

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

// index.php 또는 라우팅이 있는 위치
// FYI, 이 빈 문자열 그룹은 모든 라우트에 대한 전역 미들웨어 역할을 하며
// 물론 마찬가지로 특정 라우트에만 추가할 수 있습니다.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 라우트
}, [ new SecurityHeadersMiddleware() ]);
```


## CSRF(Cross Site Request Forgery)

CSRF(Cross Site Request Forgery)는 악성 웹 사이트가 사용자 브라우저로 요청을 보내도록 유도하는 유형의 공격입니다. 이를 통해 사용자의 동의 없이 사이트에서 작업을 수행할 수 있습니다. Flight는 내장 CSRF 보호 메커니즘을 제공하지는 않지만 미들웨어를 사용하여 쉽게 직접 구현할 수 있습니다.

### 설정

먼저 CSRF 토큰을 생성하고 사용자 세션에 저장해야 합니다. 그런 다음 이 토큰을 폼에서 사용하고 제출될 때 확인할 수 있습니다.

```php
// CSRF 토큰 생성 및 사용자 세션에 저장
// (Flight에 세션 객체를 만들었다고 가정)
// 세션당 단일 토큰만 생성하면 됩니다 (동일 사용자에 대한 여러 탭 및 요청에서 작동하므로)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- 폼에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 다른 폼 필드들 -->
</form>
```

#### 라떼(Latte) 사용

라떼(Latte) 템플릿에서 CSRF 토큰을 출력하는 사용자 정의 함수를 설정할 수도 있습니다.

```php
// CSRF 토큰을 출력하는 사용자 정의 함수 설정
// 참고: View는 뷰 엔진으로 Latte가 설정되어 있음
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

이제 라템플릿(Latte)에서 `csrf()` 함수를 사용하여 CSRF 토큰을 출력할 수 있습니다.

```html
<form method="post">
	{csrf()}
	<!-- 다른 폼 필드들 -->
</form>
```

간단하고 간결하죠?

### CSRF 토큰 확인

이벤트 필터를 사용하여 CSRF 토큰을 확인할 수 있습니다.

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고,
// 맞는 경우 CSRF 토큰이 유효한지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 csrf 토큰 가져오기
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '잘못된 CSRF 토큰');
		}
	}
});
```

또는 미들웨어 클래스를 사용할 수도 있습니다.

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

// index.php 또는 라우팅이 있는 위치
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 라우트
}, [ new CsrfMiddleware() ]);
```

## XSS(Cross Site Scripting)

XSS(Cross Site Scripting)는 악성 웹 사이트가 코드를 사이트에 삽입할 수 있는 유형의 공격입니다. 대부분의 기회는 최종 사용자가 작성한 폼 값에서 올 수 있습니다. 사용자의 출력을 절대로 신뢰해서는 안 됩니다! 항상 모든 사용자가 세계 최고의 해커라고 가정하십시오. 그들은 악의적인 JavaScript 또는 HTML을 사이트에 삽입할 수 있습니다. 이 코드는 사용자 정보를 도용하거나 사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight의 view 클래스를 사용하면 XSS 공격을 방지하기 위해 쉽게 출력을 이스케이프할 수 있습니다.

```php
// 사용자가 이름으로 이것을 사용하려고 시도하는 것처럼 가정
$name = '<script>alert("XSS")</script>';

// 이것은 출력을 이스케이프합니다
Flight::view()->set('name', $name);
// 이것이 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 라템플릿(Latte)을 뷰 클래스로 사용한다면 이또한 자동으로 이스케이프됩니다.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection은 악성 사용자가 데이터베이스에 SQL 코드를 삽입할 수 있는 유형의 공격입니다. 이것은 데이터베이스에서 정보를 도용하거나 데이터베이스에서 작업을 수행하는 데 사용될 수 있습니다. 다시 한번 사용자 입력을 절대로 신뢰해서는 안 됩니다! 항상 그들이 악의를 품고 있다고 가정하십시오. `PDO` 객체에서 준비된 문을 사용하여 SQL Injection을 방지할 수 있습니다.

```php
// Flight::db()를 PDO 객체로 등록한 경우
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하면 한 줄로 쉽게 수행할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// PDO 객체에 ? 자리 표시자를 사용하여 동일한 작업을 수행할 수 있습니다.
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 다음과 같이 결코 이러한 것을 수행하지 않겠다 약속해 주세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// $username = "' OR 1=1; -- "; 가 있다면 어떻게 될까요?
// 쿼리가 생성된 후 다음과 같이 보입니다.
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 이상하게 보이지만 작동하는 유효한 쿼리입니다. 사실,
// 이것은 모든 사용자를 반환하는 매우 일반적인 SQL Injection 공격입니다.
```

## CORS

교차 출처 리소스 공유(Cross-Origin Resource Sharing, CORS)는 웹 페이지의 많은 리소스(글꼴, JavaScript 등)가 원본 세계와 다른 도메인에서 요청할 수 있는 메커니즘입니다. Flight에 내장된 기능은 없지만 CSRF와 유사하게 미들웨어나 이벤트 필터를 사용하여 쉽게 처리할 수 있습니다.

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
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// 여기서 허용된 호스트를 사용자 지정합니다.
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
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php 또는 라우팅이 있는 위치
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 결론

보안은 매우 중요하며 웹 애플리케이션이 안전한지 확인하는 것이 중요합니다. Flight는 웹 애플리케이션을 안전하게 만들기 위한 다양한 기능을 제공하지만 사용자 데이터를 안전하게 지킬 수 있도록 항상 경계를 지키고 최선을 다해야 합니다. 최악의 상황을 가정하고 사용자 입력을 절대 신뢰하지 마십시오. 모든 출력을 이스케이프하고 SQL Injection을 방지하려면 준비된 문을 사용하십시오. CSRF 및 CORS 공격으로부터 라우트를 보호하기 위해 항상 미들웨어를 사용하십시오. 이 모든 것을 수행한다면 안전한 웹 애플리케이션을 구축하는 길에 가까워질 것입니다.