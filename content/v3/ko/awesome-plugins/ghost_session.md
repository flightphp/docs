# Ghostff/Session

PHP 세션 관리자 (비동기식, 플래시, 세그먼트, 세션 암호화). PHP open_ssl을 사용하여 세션 데이터를 선택적으로 암호화/복호화합니다. File, MySQL, Redis, 및 Memcached를 지원합니다.

클릭 [여기](https://github.com/Ghostff/Session) 코드를 보기 위해.

## 설치

Composer를 사용하여 설치하세요.

```bash
composer require ghostff/session
```

## 기본 구성

기본 설정을 사용하려면 아무것도 전달할 필요가 없습니다. 더 많은 설정에 대해서는 [Github Readme](https://github.com/Ghostff/Session)를 참조하세요.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 한 가지 기억할 점은 각 페이지 로드 시 세션을 커밋해야 한다는 것입니다
// 또는 구성에서 auto_commit을 실행해야 합니다. 
```

## 간단한 예제

사용 방법의 간단한 예제입니다.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 여기에 로그인 로직을 수행하세요
	// 비밀번호를 검증하는 등

	// 로그인 성공 시
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 작성할 때마다 의도적으로 커밋해야 합니다.
	$session->commit();
});

// 이 검사는 제한된 페이지 로직에서 수행되거나 미들웨어로 래핑될 수 있습니다.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 여기에서 제한된 페이지 로직을 수행하세요
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

사용 방법의 더 복잡한 예제입니다.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 세션 구성 파일의 사용자 정의 경로를 첫 번째 인자로 설정하세요
// 또는 사용자 정의 배열을 제공하세요
$app->register('session', Session::class, [ 
	[
		// 세션 데이터를 데이터베이스에 저장하려는 경우 (예: "모든 장치에서 로그아웃" 기능과 같은 것)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 이것을 다른 것으로 변경하세요
		Session::CONFIG_AUTO_COMMIT   => true, // 이것이 필요하거나 커밋()하기가 어렵다면만 수행하세요.
												// 추가적으로 Flight::after('start', function() { Flight::session()->commit(); });을 사용할 수 있습니다.
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # PDO dns용 데이터베이스 드라이버(예: mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # 데이터베이스 호스트
			'db_name'   => 'my_app_database',   # 데이터베이스 이름
			'db_table'  => 'sessions',          # 데이터베이스 테이블
			'db_user'   => 'root',              # 데이터베이스 사용자 이름
			'db_pass'   => '',                  # 데이터베이스 비밀번호
			'persistent_conn'=> false,          # 스크립트가 데이터베이스와 통신할 때마다 새로운 연결을 설정하는 오버헤드를 피합니다. 단점은 스스로 찾아보세요
		]
	] 
]);
```

## 도움! 내 세션 데이터가 유지되지 않아요!

세션 데이터를 설정했는데 요청 사이에 유지되지 않나요? 세션 데이터를 커밋하는 것을 잊었을 수 있습니다. 세션 데이터를 설정한 후 `$session->commit()`을 호출하세요.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 여기에 로그인 로직을 수행하세요
	// 비밀번호를 검증하는 등

	// 로그인 성공 시
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 세션에 작성할 때마다 의도적으로 커밋해야 합니다.
	$session->commit();
});
```

이것을 해결하는 다른 방법은 세션 서비스를 설정할 때 구성에 `auto_commit`을 `true`로 설정하는 것입니다. 이렇게 하면 각 요청 후 세션 데이터를 자동으로 커밋합니다.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

또한 `Flight::after('start', function() { Flight::session()->commit(); });`을 사용하여 각 요청 후 세션 데이터를 커밋할 수 있습니다.

## 문서

전체 문서를 위해 [Github Readme](https://github.com/Ghostff/Session)를 방문하세요. 구성 옵션은 [default_config.php 파일](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 자체에 잘 문서화되어 있습니다. 이 패키지를 직접 살펴보고 싶다면 코드는 이해하기 쉽습니다.