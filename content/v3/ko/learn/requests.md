# 요청

## 개요

Flight는 HTTP 요청을 단일 객체로 캡슐화하며, 이를 다음과 같이 접근할 수 있습니다:

```php
$request = Flight::request();
```

## 이해

HTTP 요청은 HTTP 수명 주기를 이해하는 데 핵심적인 측면 중 하나입니다. 사용자가 웹 브라우저나 HTTP 클라이언트에서 작업을 수행하면, 헤더, 본문, URL 등을 프로젝트로 보냅니다. 이러한 헤더(브라우저 언어, 처리할 수 있는 압축 유형, 사용자 에이전트 등)를 캡처하고, Flight 애플리케이션으로 전송된 본문과 URL을 캡처할 수 있습니다. 이러한 요청은 앱이 다음에 무엇을 할지 이해하는 데 필수적입니다.

## 기본 사용법

PHP에는 `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES`, `$_COOKIE`와 같은 여러 슈퍼 글로벌이 있습니다. Flight는 이를 편리한 [Collections](/learn/collections)로 추상화합니다. `query`, `data`, `cookies`, `files` 속성을 배열 또는 객체로 접근할 수 있습니다.

> **참고:** 프로젝트에서 이러한 슈퍼 글로벌을 사용하는 것은 **강력히** 권장되지 않으며, `request()` 객체를 통해 참조해야 합니다.

> **참고:** `$_ENV`에 대한 추상화는 제공되지 않습니다.

### `$_GET`

`query` 속성을 통해 `$_GET` 배열에 접근할 수 있습니다:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// 또는
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// $keyword로 데이터베이스 쿼리 또는 다른 작업 수행
});
```

### `$_POST`

`data` 속성을 통해 `$_POST` 배열에 접근할 수 있습니다:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// 또는
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// $name과 $email로 데이터베이스 저장 또는 다른 작업 수행
});
```

### `$_COOKIE`

`cookies` 속성을 통해 `$_COOKIE` 배열에 접근할 수 있습니다:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// 또는
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// 실제로 저장되었는지 확인하고, 저장되었다면 자동 로그인
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

새 쿠키 값 설정에 대한 도움은 [overclokk/cookie](/awesome-plugins/php-cookie)를 참조하세요.

### `$_SERVER`

`getVar()` 메서드를 통해 `$_SERVER` 배열에 접근하는 단축 방법이 있습니다:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

`files` 속성을 통해 업로드된 파일에 접근할 수 있습니다:

```php
// $_FILES 속성에 대한 직접 접근. 권장 접근법은 아래 참조
$uploadedFile = Flight::request()->files['myFile']; 
// 또는
$uploadedFile = Flight::request()->files->myFile;
```

더 많은 정보는 [Uploaded File Handler](/learn/uploaded-file)를 참조하세요.

#### 파일 업로드 처리

_v3.12.0_

프레임워크의 도우미 메서드를 사용하여 파일 업로드를 처리할 수 있습니다. 기본적으로 요청에서 파일 데이터를 가져와 새 위치로 이동하는 것입니다.

```php
Flight::route('POST /upload', function(){
	// <input type="file" name="myFile">와 같은 입력 필드가 있는 경우
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

여러 파일이 업로드된 경우, 이를 반복할 수 있습니다:

```php
Flight::route('POST /upload', function(){
	// <input type="file" name="myFiles[]">와 같은 입력 필드가 있는 경우
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **보안 참고:** 파일 업로드를 다룰 때는 항상 사용자 입력을 검증하고 정제하세요. 업로드할 허용 확장 유형을 검증하는 것뿐만 아니라, 파일의 "매직 바이트"를 검증하여 사용자가 주장하는 파일 유형인지 확인해야 합니다. 이에 대한 [기사](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [및](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [라이브러리](https://github.com/RikudouSage/MimeTypeDetector)가 도움이 됩니다.

### 요청 본문

POST/PUT 요청을 다룰 때와 같은 원시 HTTP 요청 본문을 가져오려면 다음을 할 수 있습니다:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// 전송된 XML로 작업 수행.
});
```

### JSON 본문

콘텐츠 유형이 `application/json`이고 예시 데이터가 `{"id": 123}`인 요청을 받으면, `data` 속성에서 사용할 수 있습니다:

```php
$id = Flight::request()->data->id;
```

### 요청 헤더

`getHeader()` 또는 `getHeaders()` 메서드를 사용하여 요청 헤더에 접근할 수 있습니다:

```php
// Authorization 헤더가 필요한 경우
$host = Flight::request()->getHeader('Authorization');
// 또는
$host = Flight::request()->header('Authorization');

// 모든 헤더를 가져와야 하는 경우
$headers = Flight::request()->getHeaders();
// 또는
$headers = Flight::request()->headers();
```

### 요청 메서드

`method` 속성 또는 `getMethod()` 메서드를 사용하여 요청 메서드에 접근할 수 있습니다:

```php
$method = Flight::request()->method; // 실제로는 getMethod()로 채워짐
$method = Flight::request()->getMethod();
```

**참고:** `getMethod()` 메서드는 먼저 `$_SERVER['REQUEST_METHOD']`에서 메서드를 가져온 다음, `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`가 존재하면 이를 덮어쓰거나 `$_REQUEST['_method']`가 존재하면 이를 사용합니다.

## 요청 객체 속성

요청 객체는 다음 속성을 제공합니다:

- **body** - 원시 HTTP 요청 본문
- **url** - 요청된 URL
- **base** - URL의 상위 서브디렉토리
- **method** - 요청 메서드 (GET, POST, PUT, DELETE)
- **referrer** - 참조 URL
- **ip** - 클라이언트 IP 주소
- **ajax** - AJAX 요청인지 여부
- **scheme** - 서버 프로토콜 (http, https)
- **user_agent** - 브라우저 정보
- **type** - 콘텐츠 유형
- **length** - 콘텐츠 길이
- **query** - 쿼리 문자열 매개변수
- **data** - POST 데이터 또는 JSON 데이터
- **cookies** - 쿠키 데이터
- **files** - 업로드된 파일
- **secure** - 연결이 보안인지 여부
- **accept** - HTTP accept 매개변수
- **proxy_ip** - 클라이언트의 프록시 IP 주소. `$_SERVER` 배열에서 `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`를 순서대로 스캔합니다.
- **host** - 요청 호스트 이름
- **servername** - `$_SERVER`의 SERVER_NAME

## 도우미 메서드

URL의 일부를 조합하거나 특정 헤더를 다루기 위한 몇 가지 도우미 메서드가 있습니다.

### 전체 URL

`getFullUrl()` 메서드를 사용하여 전체 요청 URL에 접근할 수 있습니다:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### 기본 URL

`getBaseUrl()` 메서드를 사용하여 기본 URL에 접근할 수 있습니다:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// 주의: 후행 슬래시 없음.
```

## 쿼리 파싱

`parseQuery()` 메서드에 URL을 전달하여 쿼리 문자열을 연관 배열로 파싱할 수 있습니다:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## 콘텐츠 Accept 유형 협상

_v3.17.2_

클라이언트가 보낸 `Accept` 헤더를 기반으로 응답할 최적의 콘텐츠 유형을 결정하기 위해 `negotiateContentType()` 메서드를 사용할 수 있습니다.

```php
// 예시 Accept 헤더: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// 아래는 지원하는 유형을 정의합니다.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// JSON 응답 제공
} elseif ($typeToServe === 'application/xml') {
	// XML 응답 제공
} else {
	// 다른 기본값 사용 또는 오류 발생
}
```

> **참고:** `Accept` 헤더에 사용 가능한 유형 중 하나가 없으면 메서드는 `null`을 반환합니다. `Accept` 헤더가 정의되지 않은 경우, 메서드는 `$availableTypes` 배열의 첫 번째 유형을 반환합니다.

## 관련 자료
- [Routing](/learn/routing) - 라우트를 컨트롤러에 매핑하고 뷰를 렌더링하는 방법.
- [Responses](/learn/responses) - HTTP 응답을 사용자 지정하는 방법.
- [Why a Framework?](/learn/why-frameworks) - 요청이 전체 그림에 어떻게 맞는지.
- [Collections](/learn/collections) - 데이터 컬렉션 작업.
- [Uploaded File Handler](/learn/uploaded-file) - 파일 업로드 처리.

## 문제 해결
- `request()->ip`와 `request()->proxy_ip`는 웹 서버가 프록시, 로드 밸런서 등 뒤에 있는 경우 다를 수 있습니다.

## 변경 로그
- v3.17.2 - negotiateContentType() 추가
- v3.12.0 - 요청 객체를 통해 파일 업로드 처리 기능 추가.
- v1.0 - 초기 릴리스.