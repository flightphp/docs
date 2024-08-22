# 요청

Flight는 HTTP 요청을 단일 객체로 캡슐화하여 접근할 수 있습니다:

```php
$request = Flight::request();
```

## 일반 사용 사례

웹 애플리케이션에서 요청 작업을 할 때, 일반적으로 헤더, `$_GET` 또는 `$_POST` 매개변수를 가져오거나, 심지어 원시 요청 본문을 얻고 싶을 것입니다. Flight는 이 모든 것을 할 수 있는 간단한 인터페이스를 제공합니다.

쿼리 문자열 매개변수를 가져오는 예는 다음과 같습니다:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "검색 중인 키워드: $keyword";
	// $keyword로 데이터베이스 또는 다른 작업 수행
});
```

POST 방법을 사용하는 폼의 예는 다음과 같습니다:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "제출한 내용: $name, $email";
	// $name과 $email로 데이터베이스 또는 다른 작업 수행
});
```

## 요청 객체 속성

요청 객체는 다음 속성을 제공합니다:

- **body** - 원시 HTTP 요청 본문
- **url** - 요청된 URL
- **base** - URL의 상위 하위 디렉토리
- **method** - 요청 방법 (GET, POST, PUT, DELETE)
- **referrer** - 참조 URL
- **ip** - 클라이언트의 IP 주소
- **ajax** - 요청이 AJAX 요청인지 여부
- **scheme** - 서버 프로토콜 (http, https)
- **user_agent** - 브라우저 정보
- **type** - 콘텐츠 유형
- **length** - 콘텐츠 길이
- **query** - 쿼리 문자열 매개변수
- **data** - POST 데이터 또는 JSON 데이터
- **cookies** - 쿠키 데이터
- **files** - 업로드된 파일
- **secure** - 연결이 안전한지 여부
- **accept** - HTTP 수락 매개변수
- **proxy_ip** - 클라이언트의 프록시 IP 주소. `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`에 대해 `$_SERVER` 배열을 스캔합니다.
- **host** - 요청 호스트 이름

`query`, `data`, `cookies`, 및 `files` 속성을 배열 또는 객체로 접근할 수 있습니다.

쿼리 문자열 매개변수를 가져오려면 다음과 같이 하면 됩니다:

```php
$id = Flight::request()->query['id'];
```

또는 다음과 같이 할 수 있습니다:

```php
$id = Flight::request()->query->id;
```

## 원시 요청 본문

예를 들어 PUT 요청을 처리할 때 원시 HTTP 요청 본문을 가져오려면 다음과 같이 하면 됩니다:

```php
$body = Flight::request()->getBody();
```

## JSON 입력

`application/json` 유형과 데이터 `{"id": 123}`로 요청을 전송하면 `data` 속성에서 이용 가능합니다:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`query` 속성을 통해 `$_GET` 배열에 접근할 수 있습니다:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`data` 속성을 통해 `$_POST` 배열에 접근할 수 있습니다:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`cookies` 속성을 통해 `$_COOKIE` 배열에 접근할 수 있습니다:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`getVar()` 메서드를 통해 `$_SERVER` 배열에 접근하는 단축키가 있습니다:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## `$_FILES`를 통한 업로드 파일 접근

`files` 속성을 통해 업로드된 파일에 접근할 수 있습니다:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## 파일 업로드 처리

프레임워크에서 도우미 메서드를 사용하여 파일 업로드를 처리할 수 있습니다. 기본적으로 요청에서 파일 데이터를 가져와서 새로운 위치로 이동시키는 것입니다.

```php
Flight::route('POST /upload', function(){
	// <input type="file" name="myFile">와 같은 입력 필드가 있는 경우
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

업로드된 파일이 여러 개인 경우 이를 순회할 수 있습니다:

```php
Flight::route('POST /upload', function(){
	// <input type="file" name="myFiles[]">와 같은 입력 필드가 있는 경우
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **보안 노트:** 사용자 입력을 항상 검증하고 정리하세요, 특히 파일 업로드를 처리할 때. 업로드할 허용되는 확장자 유형을 항상 검증해야 하며, 사용자 주장하는 파일 유형이 실제로 맞는지 확인하기 위해 파일의 "매직 바이트"도 검증해야 합니다. 이를 돕기 위한 [기사들](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [및](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [라이브러리](https://github.com/RikudouSage/MimeTypeDetector)가 있습니다.

## 요청 헤더

`getHeader()` 또는 `getHeaders()` 메서드를 사용하여 요청 헤더에 접근할 수 있습니다:

```php

// Authorization 헤더가 필요할 수 있습니다
$host = Flight::request()->getHeader('Authorization');
// 또는
$host = Flight::request()->header('Authorization');

// 모든 헤더를 가져오려면
$headers = Flight::request()->getHeaders();
// 또는
$headers = Flight::request()->headers();
```

## 요청 본문

`getBody()` 메서드를 사용하여 원시 요청 본문에 접근할 수 있습니다:

```php
$body = Flight::request()->getBody();
```

## 요청 방법

`method` 속성 또는 `getMethod()` 메서드를 사용하여 요청 방법에 접근할 수 있습니다:

```php
$method = Flight::request()->method; // 실제로는 getMethod()를 호출합니다
$method = Flight::request()->getMethod();
```

**노트:** `getMethod()` 메서드는 먼저 `$_SERVER['REQUEST_METHOD']`에서 방법을 가져오고, 그 다음 `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`가 존재하는 경우 해당 값으로 덮어쓸 수 있으며, 또는 `$_REQUEST['_method']`가 존재하는 경우 덮어 쓸 수 있습니다.

## 요청 URL

URL의 구성 요소를 조합할 수 있는 몇 가지 도우미 메서드가 있습니다.

### 전체 URL

`getFullUrl()` 메서드를 사용하여 전체 요청 URL에 접근할 수 있습니다:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### 기본 URL

`getBaseUrl()` 메서드를 사용하여 기본 URL에 접근할 수 있습니다:

```php
$url = Flight::request()->getBaseUrl();
// 주의: 슬래시가 없습니다.
// https://example.com
```

## 쿼리 파싱

`parseQuery()` 메서드에 URL을 전달하여 쿼리 문자열을 연관 배열로 파싱할 수 있습니다:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```