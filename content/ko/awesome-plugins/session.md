# 고스트/세션

PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). PHP open_ssl을 사용하여 세션 데이터의 선택적 암호화/해독을 지원합니다. 파일, MySQL, Redis, 및 Memcached을 지원합니다.

## 설치

Composer로 설치합니다.

```bash
composer require ghostff/session
```

## 기본 구성

세션을 사용하여 기본 설정을 변경할 필요는 없습니다. 세션에 대한 자세한 설정은 [Github Readme](https://github.com/Ghostff/Session)에서 확인할 수 있습니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 각 페이지 로드마다 세션을 커밋해야 합니다.
// 또는 구성에서 auto_commit을 실행해야 합니다.
```

## 간단한 예제

이렇게 사용할 수 있는 간단한 예제입니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 로그인 로직 실행
	// 비밀번호 확인 등

	// 로그인이 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 명시적으로 커밋해야 합니다.
	$session->commit();
});

// 이 체크는 제한된 페이지 로직에 있거나 미들웨어로 래핑될 수 있습니다.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 제한된 페이지 로직 실행
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

이렇게 사용할 수 있는 더 복잡한 예제입니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 세션 구성 파일에 사용자 지정 경로 설정 및 임의의 문자열로 세션 id 설정
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 또는 구성 옵션을 수동으로 재정의할 수 있습니다.
		$session->updateConfiguration([
			// 세션 데이터를 데이터베이스에 저장하려면 (예: "모든 디바이스에서 로그아웃" 기능과 같은 것이 필요한 경우 유용)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 이 값을 변경해야 합니다
			Session::CONFIG_AUTO_COMMIT   => true, // 필요하거나 세션을 커밋하는 것이 어려운 경우에만 수행하세요.
												// 또한 Flight::after('start', function() { Flight::session()->commit(); }); 사용 가능
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns에 대한 데이터베이스 드라이버 예(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 데이터베이스 호스트
				'db_name'   => 'my_app_database',   # 데이터베이스 이름
				'db_table'  => 'sessions',          # 데이터베이스 테이블
				'db_user'   => 'root',              # 데이터베이스 사용자명
				'db_pass'   => '',                  # 데이터베이스 비밀번호
				'persistent_conn'=> false,          # 매번 새로운 연결을 설정하는 오버헤드를 피하면서 빠른 웹 응용 프로그램을 얻으려면 false로 설정
			]
		]);
	}
);
```

## 문서

자세한 문서는 [Github Readme](https://github.com/Ghostff/Session)를 방문하세요. 구성 옵션은 [default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 파일에서 잘 문서화되어 있습니다. 패키지를 직접 살펴보고 싶다면 이 코드는 이해하기 쉽습니다.