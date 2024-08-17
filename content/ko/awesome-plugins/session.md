# Ghostff/Session

PHP 세션 관리자 (블로킹되지 않는, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/복호화를 위해 PHP open_ssl을 사용합니다. 파일, MySQL, Redis 및 Memcached를 지원합니다.

[여기](https://github.com/Ghostff/Session)를 클릭하여 코드를 확인하세요.

## 설치

컴포저로 설치합니다.

```bash
composer require ghostff/session
```

## 기본 구성

세션을 사용하려면 기본 설정을 전달할 필요가 없습니다. [Github Readme](https://github.com/Ghostff/Session)에서 더 많은 설정에 대해 읽을 수 있습니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 각 페이지 로드마다 세션을 커밋해야 한다는 것을 기억해야 합니다
// 그렇지 않으면 구성에서 auto_commit을 실행해야 합니다.
```

## 간단한 예제

이렇게 사용할 수 있는 간단한 예제입니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 로그인 로직을 실행합니다
	// 비밀번호 확인 등

	// 로그인에 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 명시적으로 커밋해야 합니다
	$session->commit();
});

// 제한된 페이지 논리 내에서 이 확인이 있을 수 있습니다. 또는 미들웨어로 둘러싸일 수 있습니다.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 제한된 페이지 논리를 실행합니다
});

// 미들웨어 버전
Flight::route('/some-restricted-page', function() {
	// 일반 페이지 로직
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## 더 복잡한 예제

이렇게 사용할 수 있는 보다 복잡한 예제입니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 세션 구성 파일을 사용자 정의 경로로 설정하고 세션 id에 무작위 문자열을 지정합니다
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 또는 구성 옵션을 수동으로 재정의할 수 있습니다
		$session->updateConfiguration([
			// 데이터베이스에 세션 데이터를 저장하려는 경우(예: "모든 장치에서 로그아웃")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 이 부분을 다른 값으로 변경하십시오
			Session::CONFIG_AUTO_COMMIT   => true, // 요구되거나 세션을 명시적으로 커밋하기 어려운 경우에만 실행하세요
												   // 또한 Flight::after('start', function() { Flight::session()->commit(); });를 실행할 수도 있습니다
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns에 대한 데이터베이스 드라이버 예(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 데이터베이스 호스트
				'db_name'   => 'my_app_database',   # 데이터베이스 이름
				'db_table'  => 'sessions',          # 데이터베이스 테이블
				'db_user'   => 'root',              # 데이터베이스 사용자 이름
				'db_pass'   => '',                  # 데이터베이스 암호
				'persistent_conn'=> false,          # 데이터베이스와 스크립트 간 통신할 때 새로운 연결을 설정하는 오버헤드를 피하면서 빠른 웹 애플리케이션을 만듭니다. 역면은 스스로 찾으십시오
			]
		]);
	}
);
```

## 도움이 필요한가요? 세션 데이터가 지속되지 않는가요!

세션 데이터를 설정했지만 요청 간에 유지되지 않습니까? 세션 데이터를 커밋하는 것을 잊으셨을 수 있습니다. 세션 데이터를 설정한 후 `$session->commit()`를 호출하여 해결할 수 있습니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 로그인 로직을 실행합니다
	// 비밀번호 확인 등

	// 로그인에 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 명시적으로 커밋해야 합니다
	$session->commit();
});
```

이 문제를 해결하는 또 다른 방법은 세션 서비스를 설정할 때 구성에서 `auto_commit`을 `true`로 설정해야 합니다. 이렇게 하면 각 요청 후 자동으로 세션 데이터를 커밋합니다.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

또한 `Flight::after('start', function() { Flight::session()->commit(); });`를 실행하여 각 요청 후 세션 데이터를 커밋할 수 있습니다.

## 문서

전체 문서를 보려면 [Github Readme](https://github.com/Ghostff/Session)를 방문하세요. 구성 옵션은 [default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 파일 자체에 잘 문서화되어 있습니다. 이 패키지를 직접 살펴보고 싶다면 코드가 이해하기 쉽습니다.