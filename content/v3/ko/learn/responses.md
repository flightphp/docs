# 응답

## 개요

Flight는 응답 헤더의 일부를 생성하는 데 도움을 주지만, 사용자에게 다시 보내는 내용에 대한 대부분의 제어를 사용자가 가집니다. 대부분의 경우 `response()` 객체에 직접 접근하지만, Flight는 일부 응답 헤더를 설정하는 데 도움을 주는 헬퍼 메서드를 제공합니다.

## 이해

사용자가 [요청](/learn/requests) 요청을 애플리케이션에 보낸 후, 사용자에게 적절한 응답을 생성해야 합니다. 그들은 선호하는 언어, 특정 유형의 압축 처리 가능 여부, 사용자 에이전트 등과 같은 정보를 보냈으며, 모든 것을 처리한 후 적절한 응답을 다시 보내는 시간입니다. 이는 헤더 설정, HTML 또는 JSON 본문 출력, 또는 페이지로 리디렉션하는 것을 포함할 수 있습니다.

## 기본 사용법

### 응답 본문 보내기

Flight는 출력을 버퍼링하기 위해 `ob_start()`를 사용합니다. 이는 `echo` 또는 `print`를 사용하여 사용자에게 응답을 보낼 수 있으며, Flight가 이를 캡처하여 적절한 헤더와 함께 사용자에게 다시 보내는 것을 의미합니다.

```php
// 이는 "Hello, World!"를 사용자의 브라우저로 보냅니다
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

대안으로, 본문에 추가하기 위해 `write()` 메서드를 호출할 수도 있습니다.

```php
// 이는 "Hello, World!"를 사용자의 브라우저로 보냅니다
Flight::route('/', function() {
	// 때때로 필요할 때 작업을 수행하지만, 장황합니다
	Flight::response()->write("Hello, World!");

	// 이 시점에서 설정한 본문을 검색하려면
	// 다음과 같이 할 수 있습니다
	$body = Flight::response()->getBody();
});
```

### JSON

Flight는 JSON 및 JSONP 응답을 보내는 지원을 제공합니다. JSON 응답을 보내기 위해 JSON으로 인코딩할 데이터를 전달합니다:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// 예를 들어 데이터베이스에서 사용자를 가져오는 방식으로
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* more users */ ]
```

> **참고:** 기본적으로 Flight는 응답과 함께 `Content-Type: application/json` 헤더를 보냅니다. JSON을 인코딩할 때 `JSON_THROW_ON_ERROR` 및 `JSON_UNESCAPED_SLASHES` 플래그도 사용합니다.

#### 상태 코드와 함께 JSON

두 번째 인수로 상태 코드를 전달할 수도 있습니다:

```php
Flight::json(['id' => 123], 201);
```

#### 예쁘게 출력된 JSON

마지막 위치에 인수를 전달하여 예쁘게 출력(pretty printing)을 활성화할 수도 있습니다:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### JSON 인수 순서 변경

`Flight::json()`은 매우 레거시 메서드이지만, Flight의 목표는 프로젝트의 하위 호환성을 유지하는 것입니다. 인수의 순서를 다시 하고 더 간단한 구문을 사용하려면, 다른 Flight 메서드처럼 JSON 메서드를 재매핑하면 됩니다 [/learn/extending]:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// 이제 json() 메서드를 사용할 때 `true, 'utf-8'`를 할 필요가 없습니다!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 이제 다음과 같이 사용할 수 있습니다
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON과 실행 중지

_v3.10.0_

JSON 응답을 보내고 실행을 중지하려면 `jsonHalt()` 메서드를 사용할 수 있습니다.
이는 권한 부여 유형을 확인하는 경우에 유용하며, 사용자가 권한이 없으면 즉시 JSON 응답을 보내고 기존 본문 내용을 지우고 실행을 중지할 수 있습니다.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 권한이 있는지 확인
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// 여기에 exit;가 필요 없습니다.
	}

	// 나머지 라우트와 계속
});
```

v3.10.0 이전에는 다음과 같이 해야 했습니다:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 권한이 있는지 확인
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 나머지 라우트와 계속
});
```

### 응답 본문 지우기

응답 본문을 지우려면 `clearBody` 메서드를 사용할 수 있습니다:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

위 사용 사례는 흔하지 않을 수 있지만, [미들웨어](/learn/middleware)에서 사용되면 더 흔할 수 있습니다.

### 응답 본문에 콜백 실행

`addResponseBodyCallback` 메서드를 사용하여 응답 본문에 콜백을 실행할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 이는 모든 라우트의 응답을 gzip으로 압축합니다
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

여러 콜백을 추가할 수 있으며, 추가된 순서대로 실행됩니다. 이는 [callable](https://www.php.net/manual/en/language.types.callable.php)을 허용하므로 클래스 배열 `[ $class, 'method' ]`, 클로저 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, 또는 HTML 코드를 압축하는 함수가 있다면 `'minify'`와 같은 함수 이름을 허용할 수 있습니다.

**참고:** `flight.v2.output_buffering` 구성 옵션을 사용 중이라면 라우트 콜백이 작동하지 않습니다.

#### 특정 라우트 콜백

이것이 특정 라우트에만 적용되도록 하려면 라우트 자체에 콜백을 추가할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 이는 이 라우트의 응답만 gzip으로 압축합니다
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### 미들웨어 옵션

[미들웨어](/learn/middleware)를 사용하여 모든 라우트에 콜백을 미들웨어를 통해 적용할 수도 있습니다:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// 여기에 response() 객체에 콜백을 적용합니다.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 본문을 압축하는 방식으로
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### 상태 코드

응답의 상태 코드를 설정하려면 `status` 메서드를 사용합니다:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

현재 상태 코드를 가져오려면 인수 없이 `status` 메서드를 사용할 수 있습니다:

```php
Flight::response()->status(); // 200
```

### 응답 헤더 설정

`header` 메서드를 사용하여 응답의 콘텐츠 유형과 같은 헤더를 설정할 수 있습니다:

```php
// 이는 "Hello, World!"를 사용자의 브라우저에 일반 텍스트로 보냅니다
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 또는
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### 리디렉션

현재 요청을 `redirect()` 메서드를 사용하여 새 URL로 리디렉션할 수 있습니다:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // 아래 기능이 실행되지 않도록 필요합니다
	}

	// 새 사용자 추가...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **참고:** 기본적으로 Flight는 HTTP 303 ("See Other") 상태 코드를 보냅니다. 선택적으로 사용자 지정 코드를 설정할 수 있습니다:

```php
Flight::redirect('/new/location', 301); // 영구적
```

### 라우트 실행 중지

프레임워크를 중지하고 즉시 종료하려면 `halt` 메서드를 호출할 수 있습니다:

```php
Flight::halt();
```

선택적 `HTTP` 상태 코드와 메시지를 지정할 수도 있습니다:

```php
Flight::halt(200, 'Be right back...');
```

`halt`를 호출하면 그 시점까지의 모든 응답 내용을 버리고 모든 실행을 중지합니다. 
프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메서드를 사용합니다:

```php
Flight::stop($httpStatusCode = null);
```

> **참고:** `Flight::stop()`은 응답을 출력하지만 스크립트 실행을 계속하는 이상한 동작이 있습니다. 이는 원하는 바가 아닐 수 있습니다. `Flight::stop()` 호출 후 `exit` 또는 `return`을 사용하여 추가 실행을 방지할 수 있지만, 일반적으로 `Flight::halt()`를 사용하는 것이 권장됩니다. 

이는 헤더 키와 값을 응답 객체에 저장합니다. 요청 수명 주기 끝에서 헤더를 빌드하고 응답을 보냅니다.

## 고급 사용법

### 헤더 즉시 보내기

헤더에 사용자 지정 작업을 수행해야 하고 작업 중인 코드 라인에서 헤더를 보내야 하는 경우가 있을 수 있습니다. [스트리밍 라우트](/learn/routing)를 설정 중이라면 이것이 필요합니다. 이는 `response()->setRealHeader()`를 통해 달성할 수 있습니다.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

JSONP 요청의 경우, 콜백 함수를 정의하는 데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

따라서 `?q=my_func`를 사용한 GET 요청을 하면 다음과 같은 출력을 받아야 합니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본값으로 `jsonp`를 사용합니다.

> **참고:** 2025년 이후에도 JSONP 요청을 계속 사용 중이라면 채팅에 참여하여 이유를 알려주세요! 우리는 좋은 전투/공포 이야기를 듣는 것을 좋아합니다!

### 응답 데이터 지우기

`clear()` 메서드를 사용하여 응답 본문과 헤더를 지울 수 있습니다. 이는 응답에 할당된 모든 헤더를 지우고, 응답 본문을 지우며, 상태 코드를 `200`으로 설정합니다.

```php
Flight::response()->clear();
```

#### 응답 본문만 지우기

응답 본문만 지우려면 `clearBody()` 메서드를 사용할 수 있습니다:

```php
// 이는 response() 객체에 설정된 헤더를 유지합니다.
Flight::response()->clearBody();
```

### HTTP 캐싱

Flight는 HTTP 수준 캐싱에 대한 내장 지원을 제공합니다. 캐싱 조건이 충족되면 Flight는 HTTP `304 Not Modified` 응답을 반환합니다. 클라이언트가 동일한 리소스를 다시 요청할 때 로컬에 캐시된 버전을 사용하도록 안내됩니다.

#### 라우트 수준 캐싱

전체 응답을 캐싱하려면 `cache()` 메서드를 사용하고 캐싱할 시간을 전달할 수 있습니다.

```php
// 이는 응답을 5분 동안 캐싱합니다
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// 또는 strtotime() 메서드에 전달할 문자열을 사용할 수 있습니다
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

`lastModified` 메서드를 사용하고 UNIX 타임스탬프를 전달하여 페이지가 마지막으로 수정된 날짜와 시간을 설정할 수 있습니다. 클라이언트는 마지막 수정 값이 변경될 때까지 캐시를 계속 사용합니다.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` 캐싱은 `Last-Modified`와 유사하지만, 리소스에 원하는 ID를 지정할 수 있습니다:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

`lastModified` 또는 `etag`를 호출하면 둘 다 캐시 값을 설정하고 확인합니다. 요청 간에 캐시 값이 동일하면 Flight는 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.

### 파일 다운로드

_v3.12.0_

최종 사용자에게 파일을 스트리밍하는 헬퍼 메서드가 있습니다. `download` 메서드를 사용하고 경로를 전달할 수 있습니다.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## 관련 항목
- [라우팅](/learn/routing) - 라우트를 컨트롤러에 매핑하고 뷰를 렌더링하는 방법.
- [요청](/learn/requests) - 들어오는 요청을 처리하는 방법 이해.
- [미들웨어](/learn/middleware) - 인증, 로깅 등에 라우트와 함께 미들웨어 사용.
- [왜 프레임워크인가?](/learn/why-frameworks) - Flight와 같은 프레임워크를 사용하는 이점 이해.
- [확장](/learn/extending) - Flight를 자체 기능으로 확장하는 방법.

## 문제 해결
- 리디렉션이 작동하지 않는 문제라면 메서드에 `return;`을 추가했는지 확인하세요.
- `stop()`과 `halt()`는 동일한 것이 아닙니다. `halt()`는 실행을 즉시 중지하지만, `stop()`은 실행을 계속할 수 있습니다.

## 변경 로그
- v3.12.0 - downloadFile 헬퍼 메서드 추가.
- v3.10.0 - `jsonHalt` 추가.
- v1.0 - 초기 릴리스.