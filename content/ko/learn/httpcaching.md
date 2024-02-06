# HTTP 캐싱

Flight은 HTTP 레벨 캐싱을 내장 지원합니다. 캐싱 조건이 충족되면, Flight은 HTTP `304 Not Modified` 응답을 반환합니다. 클라이언트가 동일한 리소스를 요청할 때, 그들은 로컬로 캐시된 버전을 사용하라는 메시지를 받게 됩니다.

## Last-Modified

`lastModified` 메서드를 사용하여 UNIX 타임스탬프를 전달하여 페이지가 마지막으로 수정된 날짜와 시간을 설정할 수 있습니다. 클라이언트는 마지막 수정 값이 변경될 때까지 캐시를 계속 사용합니다.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '이 컨텐츠는 캐시될 것입니다.';
});
```

## ETag

`ETag` 캐싱은 `Last-Modified`와 유사하지만 리소스에 대해 원하는 아이디를 지정할 수 있습니다:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '이 컨텐츠는 캐시될 것입니다.';
});
```

`lastModified` 또는 `etag` 중 하나를 호출하면 캐시 값을 설정하고 확인합니다. 요청 사이에 캐시 값이 동일하면, Flight은 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.