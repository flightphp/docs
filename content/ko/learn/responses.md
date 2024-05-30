# 응답

Flight은 사용자에게 응답 헤더의 일부를 생성하는 데 도와주지만, 대부분의 제어권은 여러분이 사용자에게 다시 보내는 내용에 대해 가지고 있습니다. 때로는 `Response` 객체에 직접 액세스할 수 있지만, 대부분의 경우에는 응답을 보내기 위해 `Flight` 인스턴스를 사용할 것입니다.

## 기본 응답 보내기

Flight는 출력을 버퍼링하기 위해 ob_start()를 사용합니다. 이는 `echo` 또는 `print`를 사용하여 사용자에게 응답을 보낼 수 있음을 의미하며, Flight가 이를 캡처하여 적절한 헤더와 함께 사용자에게 다시 전송할 것입니다.

```php

// 이것은 "Hello, World!"를 사용자 브라우저로 보냅니다
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

대안으로, `write()` 메서드를 호출하여 본문에 내용을 추가할 수도 있습니다.

```php

// 이것은 "Hello, World!"를 사용자 브라우저로 보냅니다
Flight::route('/', function() {
	// verbose, but gets the job sometimes when you need it
	Flight::response()->write("Hello, World!");

	// if you want to retrieve the body that you've set at this point
	// you can do so like this
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

현재 상태 코드를 얻고 싶다면, 인수 없이 `status` 메소드를 사용할 수 있습니다:

```php
Flight::response()->status(); // 200
```

## 응답 본문에서 콜백 실행

`addResponseBodyCallback` 메서드를 사용하여 응답 본문에서 콜백을 실행할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 이것은 모든 루트의 모든 응답을 gzip으로 만듭니다
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

여러 개의 콜백을 추가할 수 있으며, 추가된 순서대로 실행됩니다. 이는 모든 [callable](https://www.php.net/manual/en/language.types.callable.php)을 수용할 수 있기 때문에, 클래스 배열 `[ $class, 'method' ]`, 클로저 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, 또는 함수 이름 `'minify'`을 수용할 수 있습니다. 예를 들어 html 코드를 최소화하는 함수가 있다면, `'minify'`라는 함수 이름을 받을 수 있습니다.

**참고:** `flight.v2.output_buffering` 구성 옵션을 사용하는 경우 라우트 콜백이 작동되지 않을 것입니다.

### 특정 루트 콜백

특정 루트에만 적용하려면, 루트 내에서 콜백을 추가할 수 있습니다:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 이것은 이 루트의 응답만 gzip으로 만듭니다
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### 미들웨어 옵션

모든 루트에 콜백을 적용하려면 미들웨어를 사용할 수도 있습니다:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// This is a 
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minify the body
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

// 이것은 "Hello, World!"를 사용자에게 일반 텍스트로 보냅니다
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight은 JSON 및 JSONP 응답을 보낼 수 있는 지원을 제공합니다. JSON 응답을 보내려면 JSON으로 인코딩할 데이터를 전달하면 됩니다:

```php
Flight::json(['id' => 123]);
```

### 상태 코드가 있는 JSON

두 번째 인수로 상태 코드를 전달할 수도 있습니다:

```php
Flight::json(['id' => 123], 201);
```

### 예쁘게 출력된 JSON

마지막 위치에 전달인자를 전달하여 예쁘게 출력을 활성화할 수도 있습니다:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()`에 전달된 옵션을 변경하고 더 간단한 구문을 사용하고 싶다면, JSON 메소드를 다시 매핑할 수 있습니다:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 이제 이렇게 사용할 수 있습니다
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 및 실행 중지

JSON 응답을 보내고 실행을 중지하려면, `jsonHalt` 메소드를 사용할 수 있습니다. 이는 어떤 종류의 인증 확인을 확인하거나 사용자가 인증되지 않은 경우, 즉시 JSON 응답을 보내고 기존 본문 내용을 지우고 실행을 중지하는 데 유용합니다.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 사용자가 인증되었는지 확인
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 나머지 루트를 계속합니다
});
```

### JSONP

JSONP 요청을 위해, 콜백 함수를 정의하는데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

이렇게 하면 `?q=my_func`를 사용하여 GET 요청을 할 때 다음 출력을 받아볼 수 있습니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본값은 `jsonp`가 됩니다.

## 다른 URL로 리디렉션

`redirect()` 메소드를 사용하여 현재 요청을 새 URL로 리디렉션할 수 있습니다:

```php
Flight::redirect('/new/location');
```

Flight는 기본적으로 HTTP 303 ("다른 곳 보기") 상태 코드를 보냅니다. 원한다면 사용자 정의 코드를 설정할 수도 있습니다:

```php
Flight::redirect('/new/location', 401);
```

## 중지

`halt` 메소드를 호출하여 언제든지 프레임워크를 중지시킬 수 있습니다:

```php
Flight::halt();
```

선택적으로 `HTTP` 상태 코드 및 메시지를 지정할 수도 있습니다:

```php
Flight::halt(200, '곧 돌아오겠습니다...');
```

`halt`를 호출하면 해당 지점까지의 모든 응답 콘텐츠가 폐기됩니다. 프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메소드를 사용하세요:

```php
Flight::stop();
```

## HTTP 캐싱

Flight는 HTTP 수준 캐싱을 지원합니다. 캐싱 조건이 충족되면, Flight는 HTTP `304 변경되지 않음` 응답을 반환할 것입니다. 클라이언트가 동일한 리소스를 다시 요청할 때, 로컬로 캐시된 버전을 사용하도록 요구될 것입니다.

### 루트 수준 캐싱

전체 응답을 캐시하려면 `cache()` 메소드를 사용하고 캐시할 시간을 전달하면 됩니다.

```php

// 이것은 응답을 5분 동안 캐시합니다
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '이 내용이 캐시됩니다.';
});

// 또는 strtotime() 메소드에 전달할 문자열을 사용할 수도 있습니다
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '이 내용이 캐시됩니다.';
});
```

### 최근 수정된

`lastModified` 메소드를 사용하여 수정된 날짜 및 시간을 설정할 수 있습니다. 클라이언트는 마지막으로 수정된 값이 변경될 때까지 캐시를 계속 사용하게 됩니다.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '이 내용이 캐시됩니다.';
});
```

### ETag

`ETag` 캐싱은 `Last-Modified`와 유사하지만 리소스에 원하는 임의의 id를 지정할 수 있습니다:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '이 내용이 캐시됩니다.';
});
```

`lastModified` 또는 `etag`를 호출하면 캐시 값을 설정하고 확인합니다. 요청 사이에 캐시 값이 동일한 경우, Flight는 즉시 `HTTP 304` 응답을 보내고 처리를 중지할 것입니다.