# 보안

웹 애플리케이션의 경우 보안은 중요한 문제입니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight는 웹 애플리케이션의 보안을 돕는 여러 기능을 제공합니다.

## CSRF(Cross Site Request Forgery)

CSRF(Cross Site Request Forgery)는 악의적인 웹 사이트가 사용자의 브라우저를 통해 웹 사이트로 요청을 보낼 수 있는 공격 유형입니다. 이를 통해 사용자의 동의 없이 웹 사이트에서 조작을 수행할 수 있습니다. Flight는 내장 CSRF 보호 메커니즘을 제공하지 않지만 미들웨어를 사용하여 손쉽게 직접 구현할 수 있습니다.

다음은 이벤트 필터를 사용하여 CSRF 보호를 구현하는 예시입니다:

```php

// 이 미들웨어는 요청이 POST 요청인지 확인하고 유효한 CSRF 토큰인지 확인합니다
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 폼 값에서 csrf 토큰을 캡처합니다
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, '잘못된 CSRF 토큰');
		}
	}
});
```

## XSS(Cross Site Scripting)

XSS(Cross Site Scripting)는 악의적인 웹 사이트가 코드를 웹 사이트에 삽입할 수 있는 공격 유형입니다. 대부분의 기회는 사용자가 작성한 폼 값에서 옵니다. 사용자의 출력을 절대로 믿지 말아야 합니다! 항상 모든 사용자를 세계 최고의 해커로 가정하십시오. 악의적인 JavaScript 또는 HTML을 삽입할 수 있습니다. 이 코드는 사용자의 정보를 도난당하거나 웹 사이트에서 조치를 취할 수 있습니다. Flight의 view 클래스를 사용하여 XSS 공격을 방지하기 위해 출력을 쉽게 이스케이프할 수 있습니다.

```php

// 사용자가 이름으로 이를 시도한다고 가정해 봅시다.
$name = '<script>alert("XSS")</script>';

// 이것은 출력을 이스케이프합니다
Flight::view()->set('name', $name);
// 이것은 출력됩니다: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Latte를 뷰 클래스로 사용하는 경우 자동 이스케이프도 됩니다.
Flight::view()->render('template', ['name' => $name]);
```

## SQL Injection

SQL Injection은 악의적인 사용자가 데이터베이스에 SQL 코드를 삽입할 수 있는 공격 유형입니다. 이를 통해 데이터베이스에서 정보를 도난하거나 데이터베이스에서 조치를 취할 수 있습니다. 다시 한 번 사용자의 입력을 절대 믿지 마십시오! 항상 사용자가 위협을 가하고 있다고 가정하십시오. `PDO` 객체에서 준비된 문을 사용하여 SQL Injection을 방지할 수 있습니다.

```php

// Flight::db()를 PDO 객체로 등록한 것이라고 가정합니다
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// PdoWrapper 클래스를 사용하는 경우 한 줄로 쉽게 수행할 수 있습니다
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// ? 플레이스홀더를 사용하여 PDO 객체로도 동일한 작업을 수행할 수 있습니다
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 다 저런 일은 절대로 하지 않을 것을 약속해 주세요...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}'");
// 왜냐하면 $username = "' OR 1=1;";인 경우, 쿼리가 다음과 같이 만들어집니다
// SELECT * FROM users WHERE username = '' OR 1=1;
// 이상하게 보일 수 있지만 작동하는 유효한 쿼리입니다. 사실,
// 그것은 모든 사용자를 반환하는 매우 일반적인 SQL Injection 공격입니다.
```

## CORS(Cross-Origin Resource Sharing)

CORS(Cross-Origin Resource Sharing)는 웹 페이지에서 다른 도메인에서 많은 리소스(예: 글ꔼ暼 끤ꁼ근祼ች, JavaScript 등)을 요청할 수 있는 메커니즘입니다. Flight는 빌트인 기능이 없지만 CORS를 처리하는 미들웨어나 이벤트 필터를 사용하여 쉽게 처리할 수 있습니다.

```php

Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## 결론

보안은 중요하며 웹 애플리케이션이 안전한지 확인하는 것이 중요합니다. Flight는 웹 애플리케이션을 보호하기 위해 여러 기능을 제공하지만 항상 경계를 기울이고 사용자 데이터를 안전하게 유지할 수 있도록 최선을 다하는 것이 중요합니다. 언제나 최악의 상황을 가정하고 사용자 입력을 믿지 마십시오. 출력을 이스케이프하고 SQL Injection을 방지하기 위해 준비된 문을 사용하십시오. CSRF 및 CORS 공격으로부터 라우트를 보호하기 위해 미들웨어를 사용하십시오. 이 모든 사항들을 수행한다면 안전한 웹 애플리케이션을 구축하는 길에 있을 것입니다.