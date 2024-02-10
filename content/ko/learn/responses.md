## 응답

Flight는 응답 헤더의 일부를 생성하는 데 도움을 주지만 사용자에게 다시 전송할 내용에 대한 대부분의 제어를 유지합니다. 때로는 `Response` 객체에 직접 액세스할 수 있지만, 대부분의 경우에는 응답을 보낼 때 `Flight` 인스턴스를 사용할 것입니다.

## 기본 응답 보내기

Flight는 출력을 버퍼링하는 데 ob_start()를 사용합니다. 이는 `echo` 또는 `print`를 사용하여 사용자에게 응답을 보낼 수 있고 Flight가 해당 내용을 캡처하여 적절한 헤더와 함께 사용자에게 다시 전송합니다.

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

대안으로 `write()` 메소드를 호출하여 본문에 추가할 수도 있습니다.

```php

// 이것은 "Hello, World!"를 사용자 브라우저로 보냅니다
Flight::route('/', function() {
	// 필요할 때 사용 가능하지만 조금 많은 코드
	Flight::response()->write("Hello, World!");

	// 이 시점에서 설정한 본문을 검색하려면
	// 다음과 같이 수행할 수 있습니다
	$body = Flight::response()->getBody();
});
```

## 상태 코드

`status` 메소드를 사용하여 응답의 상태 코드를 설정할 수 있습니다:

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

현재 상태 코드를 가져오려면 인수없이 `status` 메소드를 사용할 수 있습니다:

```php
Flight::response()->status(); // 200
```

## 응답 헤더 설정

`header` 메소드를 사용하여 응답의 콘텐츠 유형과 같은 헤더를 설정할 수 있습니다:

```php

// 이것은 "Hello, World!"를 사용자 브라우저에 평문으로 보냅니다
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```



## JSON

Flight는 JSON 및 JSONP 응답을 보내는 데 지원을 제공합니다. JSON 응답을 보내려면
JSON으로 인코딩할 데이터를 전달하면 됩니다:

```php
Flight::json(['id' => 123]);
```

### JSONP

JSONP 요청의 경우, 콜백 함수를 정의하는 데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

따라서 `?q=my_func`를 사용하여 GET 요청을 보낼 때 출력을 받아야합니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본값은 `jsonp`로 설정됩니다.

## 다른 URL로 리디렉션

`redirect()` 메소드를 사용하여 현재 요청을 새 URL로 리디렉션할 수 있습니다.

```php
Flight::redirect('/new/location');
```

Flight는 기본적으로 HTTP 303 ("다른 곳 보기") 상태 코드를 보냅니다. 옵션으로 사용자 지정 코드를 설정할 수도 있습니다:

```php
Flight::redirect('/new/location', 401);
```

## 중지

`halt` 메소드를 호출하여 언제든지 프레임워크를 중지할 수 있습니다:

```php
Flight::halt();
```

선택적으로 `HTTP` 상태 코드 및 메시지를 지정할 수도 있습니다:

```php
Flight::halt(200, '곧 돌아올게요...');
```

`halt`를 호출하면 해당 지점까지의 모든 응답 내용이 폐기됩니다. 프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메소드를 사용하십시오:

```php
Flight::stop();
```

## HTTP 캐싱

Flight는 HTTP 수준 캐싱을 위한 내장 지원을 제공합니다. 캐싱 조건이
충족되면, Flight는 HTTP `304 Not Modified` 응답을 반환합니다. 다음에
클라이언트가 동일한 리소스를 요청하면 로컬로
캐시된 버전을 사용하도록 요청됩니다.

### 라우트 수준 캐싱

전체 응답을 캐시하려면 `cache()` 메소드를 사용하여 캐시할 시간을 전달할 수 있습니다.

```php

// 이것은 응답을 5분 동안 캐시합니다
Flight::route('/news', function () {
  Flight::cache(time() + 300);
  echo '이 콘텐츠는 캐시됩니다.';
});

// 또는 strtotime() 메소드에 전달할 문자열을 사용할 수도 있습니다
Flight::route('/news', function () {
  Flight::cache('+5 minutes');
  echo '이 콘텐츠는 캐시됩니다.';
});
```

### 마지막 수정된 날짜

`lastModified` 메소드를 사용하여 페이지가 마지막으로 수정된 날짜와 시간을 설정할 수 있습니다.
클라이언트는 마지막 수정된 값이 변경될 때까지
그들의 캐시를 계속 사용할 것입니다.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '이 콘텐츠는 캐시됩니다.';
});
```

### ETag

`ETag` 캐싱은 `Last-Modified`과 유사하지만 리소스에
원하는 임의의 ID를 지정할 수 있습니다:

```php
Flight::route('/news', function () {
  Flight::etag('내-고유-ID');
  echo '이 콘텐츠는 캐시됩니다.';
});
```

`lastModified` 또는 `etag` 중 하나를 호출하면 캐시 값을 설정하고 확인합니다. 요청 사이에 캐시 값이 동일하면
Flight는 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.