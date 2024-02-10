# 보안

웹 애플리케이션에는 보안이 매우 중요합니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight는 웹 애플리케이션의 보안을 돕기 위한 여러 기능을 제공합니다.

## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF)는 악의적인 웹 사이트가 사용자 브라우저에 요청을 보내도록 하는 유형의 공격입니다. Flight에는 내장된 CSRF 보호 메커니즘이 제공되지 않지만 미들웨어를 사용하여 손쉽게 자체 CSRF 보호 메커니즘을 구현할 수 있습니다.

먼저 CSRF 토큰을 생성하고 사용자 세션에 저장해야 합니다. 이후 이 토큰을 폼에서 사용하고 폼이 제출될 때 확인할 수 있습니다.

```php
// CSRF 토큰 생성 및 사용자 세션에 저장
// (Flight에 세션 객체를 만들어 연결했다고 가정)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- 폼에서 CSRF 토큰 사용 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 다른 폼 필드들 -->
</form>
```

그런 다음 이벤트 필터를 사용하여 CSRF 토큰을 확인할 수 있습니다.

```php
// 이 미들웨어는 요청이 POST 요청인지 확인하고 유효한 CSRF 토큰인지 확인합니다.
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 csrf 토큰을 캡처합니다.
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '유효하지 않은 CSRF 토큰');
		}
	}
});
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS)는 악의적인 웹 사이트가 코드를 주입하는 유형의 공격입니다. 대부분의 기회는 최종 사용자가 작성할 폼 값에서 나오게 됩니다. 사용자의 출력을 절대 신뢰해서는 안됩니다! 항상 그들을 세계 최고의 해커로 가정하십시오. 악의적인 JavaScript 또는 HTML을 페이지에 주입할 수 있습니다. 이 코드는 사용자의 정보를 탈취하거나 웹 사이트에서 조치를 취할 수 있습니다. Flight의 뷰 클래스를 사용하여 출력을 이스케이프하여 XSS 공격을 예방할 수 있습니다.

```php
// 사용자가 이름으로 이것을 사용하려고 한다고 가정합니다.
$name = '<script>alert("XSS")</script>';

// 이 코드는 출력을 이스케이프합니다.
Flight::view()->set('name', $name);
// 이렇게 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// View 클래스로 등록된 Latte와 같은 것을 사용한다면 자동으로 이스케이프됩니다.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection은 악의적인 사용자가 데이터베이스에 SQL 코드를 주입하는 유형의 공격입니다. 이를 통해 데이터베이스에서 정보를 도난하거나 데이터베이스에서 조치를 취할 수 있습니다. 다시 한번 강조하지만 사용자의 입력을 절대로 신뢰해서는 안됩니다! 항상 그들이 피할 수 있는 악의를 품고 있다고 가정하십시오. `PDO` 객체에서 준비된 문장을 사용하여 SQL Injection을 방지할 수 있습니다.

```php
// Flight::db()를 PDO 객체로 등록한 것으로 가정합니다.
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하면 이 작업을 쉽게 한 줄로 처리할 수 있습니다.
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// PDO 객체에서 ? 플레이스홀더를 사용하여 동일한 작업을 수행할 수 있습니다.
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 절대로 이런 식으로 하지 않겠다고 약속하세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 만약 $username = "' OR 1=1; -- "; 라면 쿼리가 생성된 후 이렇게 보입니다.
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 이상해 보일 수 있지만 정상적으로 작동하는 유효한 쿼리입니다. 실제로,
// 이것은 모든 사용자를 반환할 매우 흔한 SQL Injection 공격입니다.
```

## CORS

교차 출처 자원 공유 (CORS)는 웹 페이지의 여러 리소스 (글꼴, JavaScript 등)가 원본 도메인 외부의 다른 도메인에서 요청될 수 있도록 하는 메커니즘입니다. Flight에는 내장된 기능이 없지만 CORS를 처리하는 것은 CSRF와 유사하게 미들웨어나 이벤트 필터로 쉽게 처리할 수 있습니다.

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

// index.php 또는 라우트를 가지고 있는 곳
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 결론

보안은 매우 중요하며 웹 애플리케이션이 안전한지 확인하는 것이 중요합니다. Flight는 웹 애플리케이션을 안전하게 보호하는 데 도움이 되는 여러 기능을 제공하지만 항상 조심하고 사용자 데이터를 안전하게 유지할 수 있는 모든 방법을 사용하는 것이 중요합니다. 최악의 상황을 가정하고 사용자의 입력을 신뢰하지 마십시오. 출력을 이스케이프하고 SQL Injection을 방지하기 위해 준비된 문장을 사용하십시오. CSRF 및 CORS 공격으로부터 라우트를 보호하기 위해 항상 미들웨어를 사용하십시오. 이 모든 것을 수행한다면 안전한 웹 애플리케이션을 구축하는 길에 있을 것입니다.