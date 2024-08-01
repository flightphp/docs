# 요청

Flight은 HTTP 요청을 하나의 객체로 캡슐화하여 다음을 통해 액세스할 수 있습니다:

```php
$request = Flight::request();
```

## 전형적인 사용 사례

웹 응용 프로그램에서 요청을 처리할 때, 일반적으로 헤더를 추출하거나 `$_GET` 또는 `$_POST` 매개변수를 가져 오거나 심지어 원시 요청 바디를 가져 와야 할 수도 있습니다. Flight는 이러한 작업을 수행하기 위한 간단한 인터페이스를 제공합니다.

다음은 쿼리 문자열 매개변수를 가져오는 예시입니다:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "다음을 찾고 있습니다: $keyword";
	// $keyword로 데이터베이스 또는 다른 것에 쿼리
});
```

아마도 POST 방식의 폼이 있는 경우의 예시입니다:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "제출한 내용: $name, $email";
	// $name과 $email로 데이터베이스 또는 다른 곳에 저장
});
```

## 요청 객체 속성

요청 객체는 다음 속성을 제공합니다:

- **body** - 원시 HTTP 요청 바디
- **url** - 요청된 URL
- **base** - URL의 부모 하위 디렉터리
- **method** - 요청 메서드 (GET, POST, PUT, DELETE)
- **referrer** - referrer URL
- **ip** - 클라이언트의 IP 주소
- **ajax** - 요청이 AJAX 요청인지 여부
- **scheme** - 서버 프로토콜 (http, https)
- **user_agent** - 브라우저 정보
- **type** - 콘텐츠 유형
- **length** - 콘텐츠 길이
- **query** - 쿼리 문자열 매개변수
- **data** - 포스트 데이터 또는 JSON 데이터
- **cookies** - 쿠키 데이터
- **files** - 업로드된 파일
- **secure** - 연결이 안전한지 여부
- **accept** - HTTP accept 매개변수
- **proxy_ip** - 클라이언트의 프록시 IP 주소. `$_SERVER` 배열에서 `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`를 스캔합니다.
- **host** - 요청 호스트 이름

`query`, `data`, `cookies`, `files` 속성에 배열 또는 객체로 액세스할 수 있습니다.

따라서 쿼리 문자열 매개변수를 얻으려면 다음을 수행할 수 있습니다:

```php
$id = Flight::request()->query['id'];
```

또는 다음을 수행할 수도 있습니다:

```php
$id = Flight::request()->query->id;
```

## RAW 요청 바디

예를 들어 PUT 요청을 처리할 때와 같이 원시 HTTP 요청 바디를 가져 오려면 다음을 수행할 수 있습니다:

```php
$body = Flight::request()->getBody();
```

## JSON 입력

타입이 `application/json`이고 데이터가 `{"id": 123}`인 요청을 보낸 경우, 해당 데이터는 `data` 속성에서 사용할 수 있습니다:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`query` 속성을 통해 `$_GET` 배열에 액세스할 수 있습니다:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`data` 속성을 통해 `$_POST` 배열에 액세스할 수 있습니다:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`cookies` 속성을 통해 `$_COOKIE` 배열에 액세스할 수 있습니다:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`getVar()` 메서드를 통해 `$_SERVER` 배열에 액세스할 수 있는 단축키가 있습니다:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## `$_FILES`를 통한 업로드된 파일

`files` 속성을 통해 업로드된 파일에 액세스할 수 있습니다:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## 요청 헤더

`getHeader()` 또는 `getHeaders()` 메서드를 사용하여 요청 헤더에 액세스할 수 있습니다:

```php

// 인증 헤더가 필요한 경우
$host = Flight::request()->getHeader('Authorization');
// 또는
$host = Flight::request()->header('Authorization');

// 모든 헤더를 가져와야하는 경우
$headers = Flight::request()->getHeaders();
// 또는
$headers = Flight::request()->headers();
```

## 요청 바디

`getBody()` 메서드를 사용하여 원시 요청 바디에 액세스할 수 있습니다:

```php
$body = Flight::request()->getBody();
```

## 요청 메서드

`method` 속성 또는 `getMethod()` 메서드를 사용하여 요청 메서드에 액세스할 수 있습니다:

```php
$method = Flight::request()->method; // 실제로 getMethod()를 호출합니다
$method = Flight::request()->getMethod();
```

**참고:** `getMethod()` 메서드는 먼저 `$_SERVER['REQUEST_METHOD']`에서 메서드를 가져오고, 존재하는 경우 `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` 또는 `$_REQUEST['_method']`에 의해 덮어쓰일 수 있습니다.

## 요청 URL

편의를 위해 URL 부분을 합쳐주는 여러 도우미 메서드가 있습니다.

### 전체 URL

`getFullUrl()` 메서드를 사용하여 전체 요청 URL에 액세스할 수 있습니다:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### 기본 URL

`getBaseUrl()` 메서드를 사용하여 기본 URL에 액세스할 수 있습니다:

```php
$url = Flight::request()->getBaseUrl();
// 주목, 슬래시 없음.
// https://example.com
```

## 쿼리 구문 분석

쿼리 문자열을 연관 배열로 구문 분석하려면 `parseQuery()` 메서드에 URL을 전달할 수 있습니다:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```