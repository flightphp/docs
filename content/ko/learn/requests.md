# 요청

Flight은 HTTP 요청을 단일 객체로 캡슐화하며 다음을 통해 액세스할 수 있습니다:

```php
$request = Flight::request();
```

요청 객체는 다음 속성을 제공합니다:

- **url** - 요청된 URL
- **base** - URL의 상위 하위 디렉터리
- **method** - 요청 방법 (GET, POST, PUT, DELETE)
- **referrer** - 참조 URL
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
- **accept** - HTTP Accept 매개변수
- **proxy_ip** - 클라이언트의 프록시 IP 주소
- **host** - 요청 호스트 이름

`query`, `data`, `cookies`, 그리고 `files` 속성에는 배열 또는 객체로 액세스할 수 있습니다.

그래서 쿼리 문자열 매개변수를 얻으려면 다음을 수행할 수 있습니다:

```php
$id = Flight::request()->query['id'];
```

또는 다음을 할 수 있습니다:

```php
$id = Flight::request()->query->id;
```

## 원시 요청 본문

예를 들어 PUT 요청을 다룰 때와 같이 원시 HTTP 요청 본문을 얻으려면 다음을 수행할 수 있습니다:

```php
$body = Flight::request()->getBody();
```

## JSON 입력

`application/json` 유형 및 데이터 `{"id": 123}`를 보낸 요청의 경우 `data` 속성에서 사용할 수 있습니다:

```php
$id = Flight::request()->data->id;
```