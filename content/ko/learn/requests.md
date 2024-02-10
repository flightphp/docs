# 요청

Flight은 HTTP 요청을 단일 객체로 캡슐화하며 다음을 통해 액세스할 수 있습니다:

```php
$request = Flight::request();
```

요청 객체는 다음 속성을 제공합니다:

- **body** - 원시 HTTP 요청 본문
- **url** - 요청된 URL입니다.
- **base** - URL의 상위 하위 디렉토리
- **method** - 요청 방법 (GET, POST, PUT, DELETE)
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
- **proxy_ip** - 클라이언트의 프록시 IP 주소
- **host** - 요청 호스트 이름

`query`, `data`, `cookies`, `files` 속성에는 배열이나 객체로 액세스할 수 있습니다.

따라서 쿼리 문자열 매개변수를 가져오려면 다음을 수행할 수 있습니다:

```php
$id = Flight::request()->query['id'];
```

또는 다음을 수행할 수 있습니다:

```php
$id = Flight::request()->query->id;
```

## 원시 요청 본문

예를 들어 PUT 요청을 처리할 때 원시 HTTP 요청 본문을 가져오려면 다음을 수행할 수 있습니다:

```php
$body = Flight::request()->getBody();
```

## JSON 입력

유형이 `application/json`이고 데이터가 `{"id": 123}` 인 요청을 보내면 `data` 속성에서 사용할 수 있습니다:

```php
$id = Flight::request()->data->id;
```

## `$_SERVER` 액세스

`getVar()` 메소드를 통해 `$_SERVER` 배열에 액세스하는 바로 가기가 있습니다:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## 요청 해더 액세스

`getHeader()` 또는 `getHeaders()` 메소드를 사용하여 요청 헤더에 액세스할 수 있습니다:

```php

// 인증 헤더가 필요한 경우
$host = Flight::request()->getHeader('Authorization');

// 모든 헤더를 가져와아 하는 경우
$headers = Flight::request()->getHeaders();
```