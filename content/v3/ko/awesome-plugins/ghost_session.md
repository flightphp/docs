# Ghostff/Session

PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). 선택적 세션 데이터 암호화/복호화를 위해 PHP open_ssl을 사용합니다. 파일, MySQL, Redis 및 Memcached를 지원합니다.

코드를 보려면 [여기](https://github.com/Ghostff/Session)를 클릭하세요.

## 설치

composer를 사용하여 설치합니다.

```bash
composer require ghostff/session
```

## 기본 구성

세션에 기본 설정을 사용하려면 아무것도 전달할 필요가 없습니다. [Github Readme](https://github.com/Ghostff/Session)에서 더 많은 설정에 대해 읽을 수 있습니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 기억해야 할 한 가지는 각 페이지 로드에서 세션을 커밋해야 한다는 것입니다.
// 그렇지 않으면 구성에서 auto_commit을 실행해야 합니다.
```

## 간단한 예

이것을 어떻게 사용할 수 있는지에 대한 간단한 예제입니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 여기에서 로그인 논리를 수행하세요.
	// 비밀번호 확인 등.

	// 로그인에 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 의도적으로 커밋해야 합니다.
	$session->commit();
});

// 이 체크는 제한된 페이지 로직에 있을 수 있으며, 미들웨어로 감싸질 수 있습니다.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 여기에서 제한된 페이지 논리를 수행하세요.
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

## 더 복잡한 예

이것을 어떻게 사용할 수 있는지에 대한 더 복잡한 예제입니다.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 세션 구성 파일에 대한 사용자 지정 경로를 설정하고 세션 ID에 무작위 문자열을 제공합니다.
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 또는 구성 옵션을 수동으로 오버라이드할 수 있습니다.
		$session->updateConfiguration([
			// 데이터베이스에 세션 데이터를 저장하고 싶다면 (모든 장치에서 로그아웃 기능과 같은 것 원할 경우 좋습니다)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 이것은 다른 것으로 변경하세요
			Session::CONFIG_AUTO_COMMIT   => true, // 필요할 경우에만 하세요, 그리고 세션을 commit()하기 어렵다면.
												   // 추가로 Flight::after('start', function() { Flight::session()->commit(); });를 사용할 수 있습니다.
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns를 위한 데이터베이스 드라이버 예 (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 데이터베이스 호스트
				'db_name'   => 'my_app_database',   # 데이터베이스 이름
				'db_table'  => 'sessions',          # 데이터베이스 테이블
				'db_user'   => 'root',              # 데이터베이스 사용자 이름
				'db_pass'   => '',                  # 데이터베이스 비밀번호
				'persistent_conn'=> false,          # 스크립트가 데이터베이스와 통신할 때마다 새로운 연결을 설정하는 오버헤드를 피하여 더 빠른 웹 애플리케이션을 만듭니다. 뒤쪽은 스스로 찾아보세요.
			]
		]);
	}
);
```

## 도움말! 제 세션 데이터가 유지되지 않습니다!

세션 데이터를 설정했는데 요청 간에 유지되지 않나요? 세션 데이터를 커밋하는 것을 잊으셨을 수 있습니다. 세션 데이터를 설정한 후 `$session->commit()`을 호출하여 할 수 있습니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 여기에서 로그인 논리를 수행하세요.
	// 비밀번호 확인 등.

	// 로그인에 성공하면
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 쓸 때마다 의도적으로 커밋해야 합니다.
	$session->commit();
});
```

이 방법 외에도 세션 서비스를 설정할 때 구성에서 `auto_commit`을 `true`로 설정해야 합니다. 그러면 각 요청 후에 세션 데이터가 자동으로 커밋됩니다.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

또한 `Flight::after('start', function() { Flight::session()->commit(); });`을 사용하여 각 요청 후에 세션 데이터를 커밋할 수 있습니다.

## 문서화

전체 문서는 [Github Readme](https://github.com/Ghostff/Session)에서 확인하십시오. 구성 옵션은 [default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 파일 자체에 잘 문서화되어 있습니다. 이 패키지를 직접 보기 원하신다면 코드도 이해하기 쉽게 작성되어 있습니다.