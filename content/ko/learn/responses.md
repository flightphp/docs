# 응답

Flight는 응답 헤더의 일부를 생성하도록 도와주지만, 사용자가 응답으로 보내는 내용에 대한 대부분의 제어권은 사용자가 가지고 있습니다. 때때로 `Response` 객체에 직접 접근할 수 있지만, 대부분의 경우 `Flight` 인스턴스를 사용하여 응답을 보냅니다.

## 기본 응답 보내기

Flight는 ob_start()를 사용하여 출력을 버퍼링합니다. 이는 `echo` 또는 `print`를 사용하여 사용자에게 응답을 보낼 수 있으며, Flight는 이를 캡처하여 적절한 헤더와 함께 사용자에게 다시 보낼 것임을 의미합니다.

```php

// 이 코드는 사용자의 브라우저에 "Hello, World!"를 보냅니다.
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

대안으로, `write()` 메서드를 호출하여 본문에 추가할 수도 있습니다.

```php

// 이 코드는 사용자의 브라우저에 "Hello, World!"를 보냅니다.
Flight::route('/', function() {
	// 장황하지만, 필요할 때 작업을 수행합니다.
	Flight::response()->write("Hello, World!");

	// 이 시점에서 설정한 본문을 검색하려면
	// 다음과 같이 할 수 있습니다.
	$body = Flight::response()->getBody();
});
```

## 상태 코드

`status` 메서드를 사용하여 응답의 상태 코드를 설정할 수 있습니다:

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

현재 상태 코드를 가져오고자 할 경우, 인수 없이 `status` 메서드를 사용하면 됩니다:

```php
Flight::response()->status(); // 200
```

## 응답 본문 설정

`write` 메서드를 사용하여 응답 본문을 설정할 수 있지만, `echo` 또는 `print`를 통해 무언가를 출력하면, 
이는 캡처되어 출력 버퍼링을 통해 응답 본문으로 전송됩니다.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 이는 다음과 동일합니다.

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### 응답 본문 삭제

응답 본문을 지우고자 한다면 `clearBody` 메서드를 사용할 수 있습니다:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### 응답 본문에서 콜백 실행

`addResponseBodyCallback` 메서드를 사용하여 응답 본문에서 콜백을 실행할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 이 코드는 모든 라우트의 응답을 gzip으로 압축합니다.
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

여러 콜백을 추가할 수 있으며, 추가된 순서에 따라 실행됩니다. 이는 [callable](https://www.php.net/manual/en/language.types.callable.php)를 수용할 수 있기 때문에, 클래스 배열 `[ $class, 'method' ]`, 클로저 `$strReplace = function($body) { str_replace('hi', 'there', $body); };` 또는 함수 이름 `'minify'`와 같은 다른 형식도 수용할 수 있습니다.

**참고:** `flight.v2.output_buffering` 구성 옵션을 사용하는 경우 라우트 콜백이 작동하지 않습니다.

### 특정 라우트 콜백

이 콜백이 특정 라우트에만 적용되도록 하려면 라우트 자체에 콜백을 추가할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 이 코드는 이 라우트에 대한 응답만 gzip으로 압축합니다.
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### 미들웨어 옵션

미들웨어를 사용하여 콜백을 모든 라우트에 적용할 수 있습니다:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// 응답() 객체에 대한 콜백을 여기에 적용합니다.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 본문을 어떤 방식으로든 최소화합니다.
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## 응답 헤더 설정

`header` 메서드를 사용하여 응답의 콘텐츠 유형과 같은 헤더를 설정할 수 있습니다:

```php

// 이 코드는 사용자의 브라우저에 일반 텍스트로 "Hello, World!"를 보냅니다.
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 또는
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight는 JSON 및 JSONP 응답을 보내는 것을 지원합니다. JSON 응답을 보내려면 JSON으로 인코딩할 데이터를 전달합니다:

```php
Flight::json(['id' => 123]);
```

> **참고:** 기본적으로 Flight는 응답과 함께 `Content-Type: application/json` 헤더를 보냅니다. JSON 인코딩 시 `JSON_THROW_ON_ERROR` 및 `JSON_UNESCAPED_SLASHES` 상수를 사용합니다.

### 상태 코드가 있는 JSON

두 번째 인수로 상태 코드를 전달할 수도 있습니다:

```php
Flight::json(['id' => 123], 201);
```

### 예쁘게 출력된 JSON

마지막 위치에 인수를 전달하여 예쁘게 출력할 수 있습니다:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()`에 전달되는 옵션을 변경하고 간단한 구문을 원한다면 
JSON 메서드를 다시 매핑할 수 있습니다:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
});

// 이제 다음과 같이 사용할 수 있습니다.
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 및 실행 중지 (v3.10.0)

JSON 응답을 보내고 실행을 중지하려면 `jsonHalt` 메서드를 사용할 수 있습니다. 이는 특정 권한 확인을 수행하는 경우에 유용하며, 사용자에게 권한이 없는 경우 즉시 JSON 응답을 보내고 기존 본문 내용을 지우고 실행을 중지할 수 있습니다.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 권한이 있는지 확인합니다.
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 라우트의 나머지 부분을 계속 진행합니다.
});
```

v3.10.0 이전에는 다음과 같이 해야 했습니다:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 권한이 있는지 확인합니다.
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 라우트의 나머지 부분을 계속 진행합니다.
});
```

### JSONP

JSONP 요청의 경우, 콜백 함수를 정의하는 데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

따라서 `?q=my_func`를 사용하여 GET 요청을 할 때, 다음과 같은 출력을 받아야 합니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본적으로 `jsonp`로 설정됩니다.

## 다른 URL로 리디렉션

`redirect()` 메서드를 사용하여 현재 요청을 리디렉션할 수 있으며, 새 URL을 전달하면 됩니다:

```php
Flight::redirect('/new/location');
```

기본적으로 Flight는 HTTP 303("See Other") 상태 코드를 보냅니다. 사용자 정의 코드를 설정할 수 있습니다:

```php
Flight::redirect('/new/location', 401);
```

## 중지

`halt` 메서드를 호출하여 언제든지 프레임워크를 중지할 수 있습니다:

```php
Flight::halt();
```

선택적으로 `HTTP` 상태 코드와 메시지를 지정할 수도 있습니다:

```php
Flight::halt(200, '잠시만 기다려 주세요...');
```

`halt`를 호출하면 그 시점까지의 모든 응답 내용이 폐기됩니다. 프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메서드를 사용합니다:

```php
Flight::stop();
```

## 응답 데이터 지우기

`clear()` 메서드를 사용하여 응답 본문과 헤더를 지울 수 있습니다. 이렇게 하면 응답에 할당된 모든 헤더가 지워지고, 응답 본문이 지워지며, 상태 코드가 `200`으로 설정됩니다.

```php
Flight::response()->clear();
```

### 응답 본문만 지우기

응답 본문만 지우고자 한다면 `clearBody()` 메서드를 사용할 수 있습니다:

```php
// 이 코드는 여전히 응답() 객체에 설정된 모든 헤더를 유지합니다.
Flight::response()->clearBody();
```

## HTTP 캐싱

Flight는 HTTP 수준 캐싱을 위한 기본 지원을 제공합니다. 캐싱 조건이 충족되면 Flight는 HTTP `304 Not Modified` 응답을 반환합니다. 클라이언트가 동일한 리소스를 요청할 때마다 로컬 캐시된 버전을 사용하도록 프롬프트됩니다.

### 라우트 수준 캐싱

응답 전체를 캐싱하려면 `cache()` 메서드를 사용하여 캐시할 시간을 전달할 수 있습니다.

```php

// 이 코드는 응답을 5분 동안 캐싱합니다.
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '이 콘텐츠는 캐시됩니다.';
});

// 또는 strtotime() 메서드에 전달할 문자열을 사용할 수 있습니다.
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '이 콘텐츠는 캐시됩니다.';
});
```

### 마지막 수정

`lastModified` 메서드를 사용하고 UNIX 타임스탬프를 전달하여 페이지가 마지막으로 수정된 날짜와 시간을 설정할 수 있습니다. 클라이언트는 마지막 수정 값이 변경될 때까지 캐시를 계속 사용합니다.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '이 콘텐츠는 캐시됩니다.';
});
```

### ETag

`ETag` 캐싱은 `Last-Modified`와 유사하며, 리소스에 원하는 아이디를 지정할 수 있습니다:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '이 콘텐츠는 캐시됩니다.';
});
```

`lastModified` 또는 `etag`를 호출하면 모두 캐시 값을 설정하고 확인합니다. 요청 간에 캐시 값이 동일하면 Flight는 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.

## 파일 다운로드 (v3.12.0)

파일을 다운로드하기 위한 헬퍼 메서드가 있습니다. `download` 메서드를 사용하고 경로를 전달할 수 있습니다.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```