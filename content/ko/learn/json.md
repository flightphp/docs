# JSON

Flight은 JSON 및 JSONP 응답을 보내는 데 지원을 제공합니다. JSON 응답을 보내려면 JSON으로 인코딩 할 데이터를 전달하면 됩니다:

```php
Flight::json(['id' => 123]);
```

JSONP 요청의 경우, 콜백 함수를 정의하는 데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

```php
Flight::jsonp(['id' => 123], 'q');
```

따라서 `?q=my_func`를 사용하여 GET 요청을 보낼 때 아래 출력을 받아야 합니다:

```javascript
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 `jsonp`로 기본 설정됩니다.