# 설정

Flight의 특정 동작을 사용자 지정하기 위해 설정 값을 `set` 메소드를 통해 설정할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

## 사용 가능한 설정

다음은 모든 사용 가능한 설정 목록입니다:

- **flight.base_url** - 요청의 기본 URL을 덮어쓰기. (기본: null)
- **flight.case_sensitive** - URL에 대해 대/소문자를 구분하여 매칭. (기본: false)
- **flight.handle_errors** - Flight가 모든 오류를 내부적으로 처리할 수 있도록 함. (기본: true)
- **flight.log_errors** - 오류를 웹 서버의 오류 로그 파일에 기록. (기본: false)
- **flight.views.path** - 뷰 템플릿 파일이 포함된 디렉토리. (기본: ./views)
- **flight.views.extension** - 뷰 템플릿 파일 확장자. (기본: .php)

## 변수

Flight를 통해 변수를 저장하여 애플리케이션 어디에서나 사용할 수 있습니다.

```php
// 변수 저장
Flight::set('id', 123);

// 애플리케이션 다른 곳에서
$id = Flight::get('id');
```
변수가 설정되었는지 확인하려면 다음을 수행할 수 있습니다:

```php
if (Flight::has('id')) {
  // 뭔가 수행
}
```

변수를 지우려면 다음을 수행합니다:

```php
// id 변수 제거
Flight::clear('id');

// 모든 변수 제거
Flight::clear();
```

Flight는 설정 목적으로도 변수를 사용합니다.

```php
Flight::set('flight.log_errors', true);
```

## 오류 처리

### 오류 및 예외

모든 오류와 예외는 Flight에 의해 잡히고 `error` 메소드로 전달됩니다.
기본 동작은 몇 가지 오류 정보와 함께 일반적인 `HTTP 500 Internal Server Error` 응답을 보내는 것입니다.

자신의 요구에 따라 이 동작을 덮어쓸 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 이를 활성화하려면 설정을 변경할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

### 찾을 수 없음

URL을 찾을 수 없을 때, Flight는 `notFound` 메소드를 호출합니다. 기본 동작은 간단한 메시지와 함께 `HTTP 404 Not Found` 응답을 보내는 것입니다.

자신의 요구에 따라 이 동작을 덮어쓸 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```