# 보안

웹 애플리케이션에 대한 보안은 매우 중요합니다. 애플리케이션이 안전하고 사용자의 데이터가 안전한지 확인해야 합니다. Flight는 웹 애플리케이션을 보호하는 데 도움이 되는 여러 기능을 제공합니다.

## 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 이러한 헤더를 애플리케이션에 추가하는 방법은 여러 가지가 있습니다.

헤더의 보안을 확인하기 위한 두 가지 훌륭한 웹사이트는 [securityheaders.com](https://securityheaders.com/)와 
[observatory.mozilla.org](https://observatory.mozilla.org/)입니다.

### 수동 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// 클릭재킹을 방지하기 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS를 방지하기 위해 Content-Security-Policy 헤더 설정
// 참고: 이 헤더는 매우 복잡해질 수 있으므로
// 귀하의 애플리케이션을 위한 인터넷의 예제를 참조하세요.
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS를 방지하기 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑을 방지하기 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 리퍼러 정보가 얼마나 많이 전송되는지 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS를 강제하기 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 어떤 기능과 API를 사용할 수 있는지 제어하기 위해 Permissions-Policy 헤더 설정
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

이들은 `bootstrap.php` 또는 `index.php` 파일의 상단에 추가할 수 있습니다.

### 필터로 추가

다음과 같이 필터/후크에서 추가할 수도 있습니다: 

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

미들웨어 클래스로도 추가할 수 있습니다. 이는 코드를 깔끔하고 체계적으로 유지하는 좋은 방법입니다.

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

// index.php 또는 경로가 있는 곳
// 참고로, 이 빈 문자열 그룹은 모든 경로에 대한 글로벌 미들웨어 역할을 합니다.
// 물론 동일한 작업을 수행하고 특정 경로에만 추가할 수도 있습니다.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 경로
}, [ new SecurityHeadersMiddleware() ]);
```


## 교차 사이트 요청 변조 (CSRF)

교차 사이트 요청 변조 (CSRF)는 악성 웹사이트가 사용자의 브라우저를 이용해 당신의 웹사이트에 요청을 보낼 수 있는 공격 유형입니다. 이를 통해 사용자의 지식 없이 웹사이트에서 작업을 수행할 수 있습니다. Flight는 내장된 CSRF 보호 기능을 제공하지 않지만, 미들웨어를 사용하여 쉽게 직접 구현할 수 있습니다.

### 설정

먼저 CSRF 토큰을 생성하고 이를 사용자의 세션에 저장해야 합니다. 그런 다음 이 토큰을 양식에서 사용하고 양식이 제출될 때 확인할 수 있습니다.

```php
// CSRF 토큰을 생성하고 사용자의 세션에 저장
// (세션 객체를 생성하고 Flight에 연결했다고 가정)
// 추가 정보는 세션 문서를 참조하세요
Flight::register('session', \Ghostff\Session\Session::class);

// 세션당 하나의 토큰만 생성하면 됩니다(그래야 여러 탭과 요청에서 작동합니다)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- 양식에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 기타 양식 필드 -->
</form>
```

#### Latte 사용하기

Latte 템플릿에서 CSRF 토큰을 출력하는 사용자 정의 함수를 설정할 수도 있습니다.

```php
// CSRF 토큰을 출력하는 사용자 정의 함수 설정
// 참고: 뷰가 Latte를 뷰 엔진으로 구성되어 있습니다
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

이제 Latte 템플릿에서 `csrf()` 함수를 사용하여 CSRF 토큰을 출력할 수 있습니다.

```html
<form method="post">
	{csrf()}
	<!-- 기타 양식 필드 -->
</form>
```

짧고 간단하죠?

### CSRF 토큰 확인

이벤트 필터를 사용하여 CSRF 토큰을 확인할 수 있습니다:

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고, 그렇다면 CSRF 토큰이 유효한지 확인합니다.
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 양식 값에서 csrf 토큰 캡처
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '유효하지 않은 CSRF 토큰');
			// 또는 JSON 응답을 위해
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

// index.php 또는 경로가 있는 곳
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 더 많은 경로
}, [ new CsrfMiddleware() ]);
```

## 교차 사이트 스크립팅 (XSS)

교차 사이트 스크립팅 (XSS)은 악성 웹사이트가 당신의 웹사이트에 코드를 주입할 수 있는 공격 유형입니다. 이러한 기회는 대부분 최종 사용자가 작성할 양식 값에서 발생합니다. 사용자의 출력을 **절대** 신뢰하지 마세요! 모든 사용자가 세계 최고의 해커라고 가정하세요. 그들은 당신의 페이지에 악성 JavaScript나 HTML을 주입할 수 있습니다. 이 코드는 사용자의 정보를 훔치거나 웹사이트에서 작업을 수행하는 데 사용될 수 있습니다. Flight의 뷰 클래스를 사용하면 XSS 공격을 방지하기 위해 출력을 쉽게 이스케이프할 수 있습니다.

```php
// 사용자가 자신을 이름으로 표시하려고 시도한다고 가정
$name = '<script>alert("XSS")</script>';

// 이는 출력을 이스케이프합니다
Flight::view()->set('name', $name);
// 이는 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 뷰 클래스로 등록된 Latte를 사용하면 자동으로 이스케이프 처리됩니다.
Flight::view()->render('template', ['name' => $name]);
```

## SQL 인젝션

SQL 인젝션은 악성 사용자가 SQL 코드를 데이터베이스에 주입할 수 있는 공격 유형입니다. 이를 통해 데이터베이스에서 정보를 훔치거나 데이터베이스에서 작업을 수행할 수 있습니다. 다시 말하지만, 사용자의 입력을 **절대** 신뢰하지 마세요! 항상 그들이 악의적이라고 가정하세요. `PDO` 객체에서 prepared statements를 사용하면 SQL 인젝션을 방지할 수 있습니다.

```php
// Flight::db()가 PDO 객체로 등록되어 있다고 가정
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하면 쉽게 한 줄로 수행할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? 자리 표시자가 있는 PDO 객체로 동일한 작업을 수행할 수 있습니다
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 결코 이런 식으로 하겠다고 약속해 주세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 만약 $username = "' OR 1=1; -- "; 라면
// 쿼리가 구성된 후 이런 모습이 됩니다
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 이상하게 보일 수 있지만 유효한 쿼리로 작동합니다. 사실,
// 이는 모든 사용자를 반환하는 매우 일반적인 SQL 인젝션 공격입니다.
```

## CORS

교차 출처 리소스 공유 (CORS)는 웹 페이지의 많은 리소스(예: 폰트, JavaScript 등)가 리소스가 출처한 도메인 외부에서 요청되도록 허용하는 메커니즘입니다. Flight는 기본 제공 기능이 없지만, `Flight::start()` 메서드가 호출되기 전에 실행할 후크를 사용하여 쉽게 처리할 수 있습니다.

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
		// 허용된 호스트를 여기에서 사용자 정의합니다.
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

// 이는 start가 실행되기 전에 실행되어야 합니다.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## 오류 처리
생산 환경에서 공격자에게 정보를 유출하지 않도록 민감한 오류 세부정보를 숨깁니다.

```php
// bootstrap.php 또는 index.php에서

// flightphp/skeleton에서는 app/config/config.php에 있습니다.
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // 오류 표시 비활성화
    ini_set('log_errors', 1);     // 대신 오류 기록
    ini_set('error_log', '/path/to/error.log');
}

// 경로 또는 컨트롤러에서
// Flight::halt()를 사용하여 제어된 오류 응답을 제공합니다.
Flight::halt(403, '접근 거부');
```

## 입력 정리
사용자 입력을 신뢰하지 마세요. 악의적인 데이터가 침투하지 않도록 처리 전에 정리하세요.

```php

// $_POST['input']와 $_POST['email']이 있는 $_POST 요청을 가정합니다.

// 문자열 입력 정리
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// 이메일 정리
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## 비밀번호 해싱
비밀번호를 안전하게 저장하고 PHP의 내장 기능을 사용하여 안전하게 검증하세요.

```php
$password = Flight::request()->data->password;
// 저장할 때 비밀번호 해싱 (예: 등록 중)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 비밀번호 검증 (예: 로그인 중)
if (password_verify($password, $stored_hash)) {
    // 비밀번호 일치
}
```

## 속도 제한
캐시를 사용하여 요청 속도를 제한함으로써 무차별 공격을 방지합니다.

```php
// flightphp/cache가 설치되어 등록되어 있다고 가정합니다
// 미들웨어에서 flightphp/cache 사용
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, '요청이 너무 많습니다');
    }
    
    $cache->set($key, $attempts + 1, 60); // 60초 후 재설정
});
```

## 결론

보안은 중요하며 웹 애플리케이션이 안전한지 확인하는 것이 중요합니다. Flight는 웹 애플리케이션을 보호하는 데 도움이 되는 여러 기능을 제공하지만, 항상 주의하고 사용자의 데이터를 안전하게 유지하기 위해 할 수 있는 모든 일을 해야 합니다. 항상 최악을 가정하고 사용자의 입력을 신뢰하지 마세요. 항상 출력을 이스케이프하고 SQL 인젝션을 방지하기 위해 준비된 문을 사용하세요. 항상 미들웨어를 사용하여 CSRF 및 CORS 공격으로부터 경로를 보호하세요. 이러한 모든 일을 한다면, 안전한 웹 애플리케이션을 구축하는 데 큰 도움을 받을 수 있을 것입니다.