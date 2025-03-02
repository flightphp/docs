# 오류 처리

## 오류 및 예외

모든 오류와 예외는 Flight에서 잡혀 `error` 메서드로 전달됩니다.
기본 동작은 일반적인 `HTTP 500 내부 서버 오류` 응답을 함께 일부 오류 정보를 보내는 것입니다.

사용자 정의로이 동작을 재정의 할 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 이를 활성화하여 변경할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

## 찾을 수 없음

URL을 찾을 수 없을 때, Flight는 `notFound` 메서드를 호출합니다. 
기본 동작은 간단한 메시지와 함께 `HTTP 404 찾을 수 없음` 응답을 보내는 것입니다.

사용자 정의로이 동작을 재정의 할 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```