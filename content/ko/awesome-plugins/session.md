# Ghostff/Session

PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). PHP open_ssl을 사용하여 세션 데이터의 선택적 암호화/복호화를 제공합니다. 파일, MySQL, Redis 및 Memcached를 지원합니다.

## 설치

컴포저로 설치합니다.

```bash
composer require ghostff/session
```

## 기본 구성

세션을 사용하기 위해 기본 설정을 전달할 필요가 없습니다. 세션에서 더 많은 설정에 대해 [Github Readme](https://github.com/Ghostff/Session)에서 읽을 수 있습니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 각 페이지 로드마다 세션을 커밋해야 한다는 것을 기억해야 합니다
// 또는 구성에서 auto_commit을 실행해야 합니다.
```

## 간단한 예제

이렇게 사용할 수 있는 간단한 예제입니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 로그인 로직을 여기에 실행합니다
	// 비밀번호 유효성 검사 등

	// 로그인 성공 시
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 명시적으로 커밋해야 합니다.
	$session->commit();
});

// 이 확인 작업은 제한된 페이지 논리에 있을 수도 있고 미들웨어로 래핑될 수도 있습니다.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 제한된 페이지 논리를 실행합니다
});

// 미들웨어 버전
Flight::route('/some-restricted-page', function() {
	// 일반 페이지 논리
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

// 세션 구성 파일에 사용자 정의 경로 설정 및 세션 id를 무작위 문자열로 지정합니다
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 또는 구성 옵션을 수동으로 재정의할 수 있습니다
		$session->updateConfiguration([
			// 세션 데이터를 데이터베이스에 저장하려면(예: "내 디바이스 모두에서 로그아웃" 기능 같은 것을 원할 경우 유용합니다)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 이것을 다른 값으로 변경해주세요
			Session::CONFIG_AUTO_COMMIT   => true, // 이것이 필요하거나 세션을 명시적으로 커밋하기 어려울 때에만 실행합니다.
												// 또한 Flight::after('start', function() { Flight::session()->commit(); });를 실행할 수도 있습니다.
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns의 데이터베이스 드라이버 예(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 데이터베이스 호스트
				'db_name'   => 'my_app_database',   # 데이터베이스 이름
				'db_table'  => 'sessions',          # 데이터베이스 테이블
				'db_user'   => 'root',              # 데이터베이스 사용자명
				'db_pass'   => '',                  # 데이터베이스 암호
				'persistent_conn'=> false,          # 데이터베이스에 매번 새로운 연결을 설정하는 오버헤드를 피하면 더 빠른 웹 애플리케이션이 됩니다. 뒷면을 스스로 찾으세요
			]
		]);
	}
);
```

## 문서

전체 문서를 보려면 [Github Readme](https://github.com/Ghostff/Session)를 방문하세요. 구성 옵션은 [default_config.php에서 자세히 문서화되어 있습니다](https://github.com/Ghostff/Session/blob/master/src/default_config.php). 이 패키지를 직접 살펴보고 싶을 경우 코드가 이해하기 쉽습니다.