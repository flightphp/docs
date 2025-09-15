# 응답

Flight은 응답 헤더의 일부를 생성하는 데 도움을 주지만, 사용자가 보내는 내용에 대한 대부분의 제어권을 가지고 있습니다. 때때로 `Response` 객체에 직접 접근할 수 있지만, 대부분의 경우 `Flight` 인스턴스를 사용하여 응답을 보냅니다.

## 기본 응답 보내기

Flight은 ob_start()를 사용하여 출력을 버퍼링합니다. 이는 `echo` 또는 `print`를 사용하여 사용자로 응답을 보내고, Flight이 이를 캡처하여 적절한 헤더와 함께 사용자에게 반환한다는 의미입니다.

```php
// 이것은 "Hello, World!"를 사용자의 브라우저에 보낼 것입니다
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
// 이것은 "Hello, World!"를 사용자의 브라우저에 보낼 것입니다
Flight::route('/', function() {
	// 자세한 설명이지만, 필요할 때 사용됩니다
	Flight::response()->write("Hello, World!");

	// 이 시점에서 설정된 본문을 검색하려면
	// 이렇게 할 수 있습니다
	$body = Flight::response()->getBody();
});
```

## 상태 코드

응답의 상태 코드를 설정하려면 `status` 메서드를 사용하세요:

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

현재 상태 코드를 가져오려면 인수를 지정하지 않고 `status` 메서드를 사용하세요:

```php
Flight::response()->status(); // 200
```

## 응답 본문 설정

응답 본문을 설정하려면 `write` 메서드를 사용하세요. 그러나 `echo`나 `print`를 사용하면, 출력 버퍼링을 통해 응답 본문으로 보내집니다.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 동일한 효과

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### 응답 본문 지우기

응답 본문을 지우려면 `clearBody` 메서드를 사용하세요:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### 응답 본문에 콜백 실행

응답 본문에 콜백을 실행하려면 `addResponseBodyCallback` 메서드를 사용하세요:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 이것은 모든 경로의 응답을 gzip으로 압축할 것입니다
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

여러 콜백을 추가할 수 있으며, 추가된 순서대로 실행됩니다. 이는 [callable](https://www.php.net/manual/en/language.types.callable.php)을 허용하므로, 클래스 배열 `[ $class, 'method' ]`, 클로저 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, 또는 예를 들어 HTML 코드를 최소화하는 함수 이름 `'minify'`를 사용할 수 있습니다.

**노트:** 라우트 콜백은 `flight.v2.output_buffering` 구성 옵션을 사용하는 경우 작동하지 않습니다.

### 특정 라우트 콜백

이것을 특정 라우트에만 적용하려면 라우트 자체에 콜백을 추가하세요:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 이것은 이 라우트의 응답만 gzip으로 압축할 것입니다
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### 미들웨어 옵션

미들웨어를 사용하여 모든 라우트에 콜백을 적용할 수도 있습니다:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// 여기서 response() 객체에 콜백을 적용합니다.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 본문을 최소화하는 방법
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

응답의 헤더, 예를 들어 콘텐츠 유형을 설정하려면 `header` 메서드를 사용하세요:

```php
// 이것은 "Hello, World!"를 사용자의 브라우저에 일반 텍스트로 보낼 것입니다
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 또는
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight은 JSON 및 JSONP 응답을 보내는 지원을 제공합니다. JSON 응답을 보내기 위해 JSON으로 인코딩할 데이터를 전달하세요:

```php
Flight::json(['id' => 123]);
```

> **노트:** 기본적으로 Flight은 응답과 함께 `Content-Type: application/json` 헤더를 보냅니다. 또한 JSON 인코딩 시 `JSON_THROW_ON_ERROR` 및 `JSON_UNESCAPED_SLASHES` 상수를 사용합니다.

### JSON과 상태 코드

두 번째 인수로 상태 코드를 전달할 수 있습니다:

```php
Flight::json(['id' => 123], 201);
```

### JSON과 미려한 출력

마지막 위치에 인수를 전달하여 미려한 출력을 활성화할 수 있습니다:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()`에 전달되는 옵션을 변경하고 더 간단한 구문을 원하면 JSON 메서드를 재매핑할 수 있습니다:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 이제 이렇게 사용할 수 있습니다
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON과 실행 중지 (v3.10.0)

JSON 응답을 보내고 실행을 중지하려면 `jsonHalt()` 메서드를 사용하세요. 이는 승인 확인과 같은 경우에 유용하며, 사용자가 승인되지 않으면 즉시 JSON 응답을 보내고 기존 본문 내용을 지우고 실행을 중지합니다.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 승인되었는지 확인
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 라우트의 나머지 부분 계속
});
```

v3.10.0 이전에는 이렇게 해야 했습니다:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 승인되었는지 확인
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 라우트의 나머지 부분 계속
});
```

### JSONP

JSONP 요청의 경우, 콜백 함수를 정의하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

GET 요청을 `?q=my_func`으로 보낼 때, 출력은 다음과 같아야 합니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본값으로 `jsonp`이 됩니다.

## 다른 URL로 리디렉션

현재 요청을 리디렉션하려면 `redirect()` 메서드를 사용하고 새 URL을 전달하세요:

```php
Flight::redirect('/new/location');
```

기본적으로 Flight은 HTTP 303 ("See Other") 상태 코드를 보냅니다. 선택적으로 사용자 정의 코드를 설정할 수 있습니다:

```php
Flight::redirect('/new/location', 401);
```

## 중지

프레임워크를 언제든지 중지하려면 `halt` 메서드를 호출하세요:

```php
Flight::halt();
```

선택적으로 HTTP 상태 코드와 메시지를 지정할 수 있습니다:

```php
Flight::halt(200, 'Be right back...');
```

`halt`를 호출하면 그 시점까지의 응답 내용을 버립니다. 프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메서드를 사용하세요:

```php
Flight::stop($httpStatusCode = null);
```

> **노트:** `Flight::stop()`은 응답을 출력하지만 스크립트 실행을 계속하는 등의 이상한 동작을 보일 수 있습니다. 추가 실행을 방지하기 위해 `exit` 또는 `return`을 사용하거나, 일반적으로 `Flight::halt()`를 사용하는 것이 좋습니다.

## 응답 데이터 지우기

응답 본문과 헤더를 지우려면 `clear()` 메서드를 사용하세요. 이는 응답에 할당된 헤더를 지우고, 응답 본문을 지우며, 상태 코드를 `200`으로 설정합니다.

```php
Flight::response()->clear();
```

### 응답 본문만 지우기

응답 본문을만 지우려면 `clearBody()` 메서드를 사용하세요:

```php
// 이것은 response() 객체에 설정된 헤더를 유지합니다.
Flight::response()->clearBody();
```

## HTTP 캐싱

Flight은 HTTP 수준 캐싱을 내장 지원합니다. 캐싱 조건이 충족되면 Flight은 HTTP `304 Not Modified` 응답을 반환합니다. 클라이언트가 동일한 리소스를 다음에 요청할 때, 로컬 캐시 버전을 사용하도록 유도됩니다.

### 라우트 수준 캐싱

전체 응답을 캐싱하려면 `cache()` 메서드를 사용하고 캐싱 시간을 전달하세요.

```php
// 이것은 응답을 5분 동안 캐싱할 것입니다
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// 대안으로, strtotime() 메서드에 전달할 문자열을 사용할 수 있습니다
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

페이지가 마지막으로 수정된 날짜와 시간을 설정하려면 `lastModified` 메서드를 사용하고 UNIX 타임스탬프를 전달하세요. 클라이언트는 last modified 값이 변경될 때까지 캐시를 계속 사용합니다.

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

`lastModified` 또는 `etag`을 호출하면 캐시 값을 설정하고 확인합니다. 요청 간에 캐시 값이 동일하면 Flight은 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.

## 파일 다운로드 (v3.12.0)

파일을 다운로드하는 헬퍼 메서드가 있습니다. `download` 메서드를 사용하고 경로를 전달하세요.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```