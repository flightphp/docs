# 리다이렉트

새 URL을 전달하여 `redirect` 메서드를 사용하여 현재 요청을 리다이렉트할 수 있습니다:

```php
Flight::redirect('/new/location');
```

기본적으로 Flight는 HTTP 303 상태 코드를 보냅니다. 선택적으로 사용자 정의 코드를 설정할 수 있습니다:

```php
Flight::redirect('/new/location', 401);
```