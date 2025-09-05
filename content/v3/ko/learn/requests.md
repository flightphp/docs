# 요청

Flight은 HTTP 요청을 하나의 객체로 캡슐화하며, 이를 다음처럼 접근할 수 있습니다:

```php
$request = Flight::request();
```

## 일반적인 사용 사례

웹 애플리케이션에서 요청을 처리할 때, 일반적으로 헤더를 가져오거나 `$_GET` 또는 `$_POST` 매개변수를 가져오거나, 원시 요청 본문을 가져올 수 있습니다. Flight은 이러한 작업을 간단한 인터페이스로 제공합니다.

쿼리 문자열 매개변수를 가져오는 예시:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// 데이터베이스를 쿼리하거나 $keyword로 다른 작업을 수행합니다
});
```

POST 메서드를 사용하는 폼 예시:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// 데이터베이스에 저장하거나 $name과 $email로 다른 작업을 수행합니다
});
```

## 요청 객체 속성

요청 객체는 다음과 같은 속성을 제공합니다:

- **body** - 원시 HTTP 요청 본문
- **url** - 요청되는 URL
- **base** - URL의 상위 하위 디렉터리
- **method** - 요청 메서드 (GET, POST, PUT, DELETE)
- **referrer** - 리퍼러 URL
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
- **accept** - HTTP accept 매개변수
- **proxy_ip** - 클라이언트의 프록시 IP 주소. `$_SERVER` 배열에서 `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`를 순서대로 스캔합니다.
- **host** - 요청 호스트 이름
- **servername** - `$_SERVER`의 SERVER_NAME

`query`, `data`, `cookies`, 및 `files` 속성은 배열 또는 객체로 접근할 수 있습니다.

쿼리 문자열 매개변수를 가져오는 방법:

```php
$id = Flight::request()->query['id'];
```

또는:

```php
$id = Flight::request()->query->id;
```

## 원시 요청 본문

PUT 요청과 같이 원시 HTTP 요청 본문을 가져올 때:

```php
$body = Flight::request()->getBody();
```

## JSON 입력

`application/json` 유형과 데이터 `{"id": 123}`으로 요청을 보내면 `data` 속성에서 사용할 수 있습니다:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`$_GET` 배열은 `query` 속성을 통해 접근할 수 있습니다:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`$_POST` 배열은 `data` 속성을 통해 접근할 수 있습니다:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`$_COOKIE` 배열은 `cookies` 속성을 통해 접근할 수 있습니다:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`$_SERVER` 배열은 `getVar()` 메서드를 통해 접근할 수 있습니다:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## 업로드된 파일 접근 (`$_FILES`)

업로드된 파일은 `files` 속성을 통해 접근할 수 있습니다:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## 파일 업로드 처리 (v3.12.0)

프레임워크를 사용하여 파일 업로드를 처리할 수 있습니다. 이는 요청에서 파일 데이터를 가져와 새 위치로 이동시키는 작업입니다.

```php
Flight::route('POST /upload', function(){
	// 입력 필드가 <input type="file" name="myFile">와 같다면
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

여러 파일을 업로드한 경우 반복문을 사용할 수 있습니다:

```php
Flight::route('POST /upload', function(){
	// 입력 필드가 <input type="file" name="myFiles[]">와 같다면
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **보안 노트:** 사용자 입력, 특히 파일 업로드 시 항상 유효성 검사와 세정을 수행하세요. 업로드 허용 확장자를 유효성 검사해야 하며, 파일의 "마법 바이트"를 확인하여 사용자가 주장하는 파일 유형이 실제로 맞는지 검증하세요. [기사](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [및](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [라이브러리](https://github.com/RikudouSage/MimeTypeDetector)가 이를 도울 수 있습니다.

## 요청 헤더

요청 헤더는 `getHeader()` 또는 `getHeaders()` 메서드를 사용하여 접근할 수 있습니다:

```php
// 예를 들어 Authorization 헤더가 필요하다면
$host = Flight::request()->getHeader('Authorization');
// 또는
$host = Flight::request()->header('Authorization');

// 모든 헤더를 가져올 때
$headers = Flight::request()->getHeaders();
// 또는
$headers = Flight::request()->headers();
```

## 요청 본문

원시 요청 본문을 `getBody()` 메서드를 사용하여 접근할 수 있습니다:

```php
$body = Flight::request()->getBody();
```

## 요청 메서드

요청 메서드는 `method` 속성 또는 `getMethod()` 메서드를 사용하여 접근할 수 있습니다:

```php
$method = Flight::request()->method; // 실제로 getMethod()를 호출합니다
$method = Flight::request()->getMethod();
```

**노트:** `getMethod()` 메서드는 먼저 `$_SERVER['REQUEST_METHOD']`에서 메서드를 가져오며, 존재하면 `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` 또는 `$_REQUEST['_method']`로 덮어쓸 수 있습니다.

## 요청 URL

URL의 일부를 조합하는 데 편리한 헬퍼 메서드가 있습니다.

### 전체 URL

전체 요청 URL은 `getFullUrl()` 메서드를 사용하여 접근할 수 있습니다:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### 기본 URL

기본 URL은 `getBaseUrl()` 메서드를 사용하여 접근할 수 있습니다:

```php
$url = Flight::request()->getBaseUrl();
// 후행 슬래시 없음.
// https://example.com
```

## 쿼리 파싱

URL을 `parseQuery()` 메서드에 전달하여 쿼리 문자열을 연관 배열로 파싱할 수 있습니다:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```