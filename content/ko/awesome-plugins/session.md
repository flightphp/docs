# Ghostff/Session

PHP 세션 관리자 (블로킹되지 않는, 플래시, 세그먼트, 세션 암호화). 선택적으로 세션 데이터의 암호화/복호화를 위해 PHP open_ssl을 사용합니다. 파일, MySQL, Redis 및 Memcached를 지원합니다.

## 설치

컴포저로 설치합니다.

```bash
composer require ghostff/session
```

## 기본 구성

기본 설정을 사용하려면 아무것도 전달할 필요가 없습니다. 세션 설정에 대해 더 읽어보려면 [Github Readme](https://github.com/Ghostff/Session)를 확인하세요.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 각 페이지 로드마다 세션을 커밋해야 한다는 것을 기억해야 합니다
// 아니면 구성에서 auto_commit을 실행해야 합니다.
```

## 간단한 예제

이렇게 사용할 수 있는 간단한 예제입니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 로그인 로직을 여기에 구현합니다
	// 비밀번호 유효성 검사 등

	// 로그인이 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 명시적으로 커밋해야 합니다.
	$session->commit();
});

// 이 확인은 제한된 페이지 로직에서 수행하거나 미들웨어로 래핑할 수 있습니다.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 제한된 페이지 로직을 여기에서 구현합니다
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

더 복잡한 예제입니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 사용자 지정 경로 설정 파일로 세션 구성 파일을 설정하고 세션 ID에 대한 무작위 문자열을 제공합니다
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 또는 구성 옵션을 수동으로 재정의할 수 있습니다
		$session->updateConfiguration([
			// 세션 데이터를 데이터베이스에 저장하려면 (예: "모든 장치에서 로그아웃하기" 기능이 필요한 경우 좋음)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', '내-슈퍼-비밀-salt'), // 이것을 다른 것으로 변경하세요
			Session::CONFIG_AUTO_COMMIT   => true, // 이렇게 설정하려면 필요한 경우에만 하고/세션을 커밋하기 어려운 경우에만 수행하세요.
												   // 또 Flight::after('start', function() { Flight::session()->commit(); });을 수행할 수 있습니다.
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns의 데이터베이스 드라이버 예(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 데이터베이스 호스트
				'db_name'   => 'my_app_database',   # 데이터베이스 이름
				'db_table'  => 'sessions',          # 데이터베이스 테이블
				'db_user'   => 'root',              # 데이터베이스 사용자명
				'db_pass'   => '',                  # 데이터베이스 암호
				'persistent_conn'=> false,          # 각 스크립트가 데이터베이스와 통신할 때마다 새로운 연결을 설정하는 오버헤드를 피할 수 있으므로 웹 애플리케이션을 더 빠르게 만듭니다. 직접 찾으세요
			]
		]);
	}
);
```

## 도움이 필요한가요? 세션 데이터가 유지되지 않는 경우!

세션 데이터를 설정했지만 요청 간에 유지되지 않습니까? 세션 데이터를 커밋하는 것을 잊었을 수 있습니다. 세션 데이터를 설정한 후 `$session->commit()`를 호출하여 수행할 수 있습니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 로그인 로직을 여기에 구현합니다
	// 비밀번호 유효성 검사 등

	// 로그인이 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 명시적으로 커밋해야 합니다.
	$session->commit();
});
```

이 문제를 해결하는 다른 방법은 세션 서비스를 설정할 때 구성에서 `auto_commit`를 `true`로 설정해야 합니다. 이렇게 하면 각 요청 후에 세션 데이터가 자동으로 커밋됩니다.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

또한 `Flight::after('start', function() { Flight::session()->commit(); });`를 사용하여 각 요청 후에 세션 데이터를 커밋할 수 있습니다.

## 문서

전체 문서에 대해서는 [Github Readme](https://github.com/Ghostff/Session)를 참조하세요. 구성 옵션은 [default_config.php 파일에 잘 문서화되어 있습니다](https://github.com/Ghostff/Session/blob/master/src/default_config.php). 이 패키지 자체를 살펴본다면 코드를 이해하는 것이 간단합니다.