# 응답

Flight은 사용자에게 응답 헤더의 일부를 생성하는 데 도움을 줍니다. 그러나 사용자에게 다시 전송할 내용에 대한 대부분의 제어권을 보유하고 있습니다. 때로는 `Response` 객체에 직접 액세스할 수도 있지만, 대부분의 경우 `Flight` 인스턴스를 사용하여 응답을 전송합니다.

## 기본 응답 보내기

Flight은 출력을 버퍼링하기 위해 ob_start()를 사용합니다. 이는 사용자에게 응답을 전송할 때 `echo`나 `print`를 사용할 수 있고, Flight가 이를 캡처하여 적절한 헤더와 함께 사용자에게 전송합니다.

```php

// 이것은 "Hello, World!"를 사용자의 브라우저에 전송합니다
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

대안으로, `write()` 메서드를 호출하여 본문을 추가할 수도 있습니다.

```php

// 이것은 "Hello, World!"를 사용자의 브라우저에 전송합니다
Flight::route('/', function() {
	// 다소 장황하지만 필요할 때 작업을 수행하는 데 사용할 수 있습니다
	Flight::response()->write("Hello, World!");

	// 여기까지 설정한 본문을 검색하려면
	// 다음과 같이 할 수 있습니다
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

현재 상태 코드를 가져오려면 인수 없이 `status` 메서드를 사용할 수 있습니다:

```php
Flight::response()->status(); // 200
```

## 응답 본문 설정

`write` 메서드를 사용하여 응답 본문을 설정할 수 있지만, `echo` 또는 `print`로 내용을 출력하는 경우,
이는 캡처되어 출력 버퍼링을 통해 응답 본문으로 전송됩니다.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 아래와 같습니다

Flight::route('/', function() {
	echo "Hello, World!";
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

### 응답 본문에서 콜백 실행

`addResponseBodyCallback` 메서드를 사용하여 응답 본문에 대한 콜백을 실행할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 이것은 모든 경로에 대해 모든 응답을 gzip 처리합니다
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

여러 콜백을 추가할 수 있으며, 추가된 순서대로 실행됩니다. 이는 모든 [callable](https://www.php.net/manual/en/language.types.callable.php)을 수락할 수 있기 때문에, 클래스 배열 `[ $class, 'method' ]`, 클로저 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, 또는 함수 이름 `'minify'`과 같은 함수를 사용하여 html 코드를 최소화하는 함수를 수행할 수 있습니다.

**참고:** `flight.v2.output_buffering` 구성 옵션을 사용하는 경우 라우트 콜백이 작동하지 않습니다.

### 특정 경로 콜백

특정 경로에만 적용되도록하려면 해당 경로에 콜백을 추가할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 이것은이 경로의 응답에만 압축을 수행합니다
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### 미들웨어 옵션

모든 경로에 콜백을 적용하려면 미들웨어를 사용할 수도 있습니다:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// 여기에서 응답() 객체에 콜백을 적용합니다.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 본문을 어떻게 최소화할 수 있습니다.
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

`header` 메서드를 사용하여 응답의 컨텐츠 유형과 같은 헤더를 설정할 수 있습니다:

```php

// 이것은 "Hello, World!"를 사용자의 브라우저에 일반 텍스트로 전송합니다
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 또는
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight은 JSON 및 JSONP 응답을 보내는 데 지원을 제공합니다. JSON 응답을 보내려면
JSON으로 인코딩할 데이터를 전달하면 됩니다:

```php
Flight::json(['id' => 123]);
```

### 상태 코드가 포함된 JSON

두 번째 인수로 상태 코드를 전달할 수도 있습니다:

```php
Flight::json(['id' => 123], 201);
```

### 예쁘게 출력된 JSON

마지막 위치에 인자를 전달하여 예쁘게 출력하도록 할 수도 있습니다:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()`로 전달된 옵션을 변경하고 더 간단한 구문을 원한다면,
단순히 JSON 메서드를 다시 매핑하면 됩니다:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 이제 다음과 같이 사용할 수 있습니다
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 및 실행 중단 (v3.10.0)

JSON 응답을 보내고 실행을 중지하려면 `jsonHalt` 메서드를 사용할 수 있습니다.
사용자가 권한을 확인하거나 권한이 없을 경우 즉시 JSON 응답을 보내고, 기존 본문 내용을 지우고 실행을 중지하는 경우에 유용합니다.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자의 권한이 있는지 확인
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 경로의 나머지 부분 계속하기
});
```

v3.10.0 이전에는 다음과 같이 수행해야합니다:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자의 권한이 있는지 확인
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 경로의 나머지 부분 계속하기
});
```

### JSONP

JSONP 요청의 경우, 콜백 함수를 정의하는 데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

`?q=my_func`를 사용하여 GET 요청을 수행하면 다음 출력을 수신해야합니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본값은 `jsonp`로 설정됩니다.

## 다른 URL로 리디렉션

`redirect()` 메서드를 사용하여 현재 요청을 새 URL로 리디렉션할 수 있습니다.

```php
Flight::redirect('/new/location');
```

Flight는 기본적으로 HTTP 303 ("See Other") 상태 코드를 전송합니다. 원하는 경우 사용자 정의 코드를 설정할 수도 있습니다:

```php
Flight::redirect('/new/location', 401);
```

## 중단

`halt` 메서드를 호출하여 언제든지 프레임워크를 중지할 수 있습니다:

```php
Flight::halt();
```

선택적으로 `HTTP` 상태 코드와 메시지를 지정할 수도 있습니다:

```php
Flight::halt(200, '곧 돌아갑니다...');
```

`halt`를 호출하면 해당 시점까지의 모든 응답 내용이 삭제됩니다. 프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메서드를 사용하세요:

```php
Flight::stop();
```

## 응답 데이터 지우기

`clear()` 메서드를 사용하여 응답 본문과 헤더를 지울 수 있습니다. 이렇게 하면
응답에 할당된 모든 헤더가 삭제되고, 응답 본문이 지워지며 상태 코드가 `200`으로 설정됩니다.

```php
Flight::response()->clear();
```

### 응답 본문만 지우기

응답 본문만 지우려면 `clearBody()` 메서드를 사용할 수 있습니다:

```php
// 응답() 객체에 설정된 모든 헤더를 유지할 것입니다.
Flight::response()->clearBody();
```

## HTTP 캐싱

Flight은 HTTP 수준의 캐싱을 위한 내장 지원을 제공합니다. 캐싱 조건이
충족되면 Flight가 HTTP `304 Not Modified` 응답을 반환합니다. 클라이언트가 다음 번에
동일한 리소스를 요청하면 로컬로 캐시된 버전을 사용하도록 요청됩니다.

### 라우트 수준 캐싱

응답 전체를 캐시하려면 `cache()` 메서드를 사용하고 캐시할 시간을 전달하면 됩니다.

```php

// 이것은 응답을 5분 동안 캐시합니다
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '이 콘텐츠는 캐시됩니다.';
});

// 또는 strtotime()에 전달할 문자열을 사용할 수도 있습니다
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '이 콘텐츠는 캐시됩니다.';
});
```

### 최종 수정

`lastModified` 메서드를 사용하여 페이지가 마지막으로 수정된 날짜와 시간을 설정할 수 있습니다. 클라이언트는
마지막으로 수정된 값이 변경되기 전까지 캐시를 계속 사용합니다.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '이 콘텐츠는 캐시됩니다.';
});
```

### ETag

`ETag` 캐싱은 `Last-Modified`와 유사하지만 리소스에 임의의 id를 지정할 수 있습니다:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '이 콘텐츠는 캐시됩니다.';
});
```

`lastModified` 또는 `etag`을 호출하면 둘 다 캐시 값을 설정하고 확인합니다. 요청 간에 캐시 값이 동일하면, 
Flight는 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.

### 파일 다운로드

파일을 다운로드하는 데 도움이 되는 메서드가 있습니다. `download` 메서드를 사용하여 경로를 전달할 수 있습니다.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```