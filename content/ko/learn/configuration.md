# 설정

Flight의 특정 동작을 사용자 정의할 수 있습니다. `set` 메서드를 통해 구성 값을 설정할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

## 사용 가능한 구성 설정

다음은 모든 사용 가능한 구성 설정 목록입니다:

- **flight.base_url** `?string` - 요청의 기본 URL을 재정의합니다. (기본값: null)
- **flight.case_sensitive** `bool` - URL에 대해 대/소문자를 구분합니다. (기본값: false)
- **flight.handle_errors** `bool` - Flight가 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
- **flight.log_errors** `bool` - 오류를 웹 서버의 오류 로그 파일에 기록합니다. (기본값: false)
- **flight.views.path** `string` - 뷰 템플릿 파일이 포함된 디렉토리입니다. (기본값: ./views)
- **flight.views.extension** `string` - 뷰 템플릿 파일 확장자입니다. (기본값: .php)
- **flight.content_length** `bool` - `Content-Length` 헤더를 설정합니다. (기본값: true)
- **flight.v2.output_buffering** `bool` - 레거시 출력 버퍼링을 사용합니다. [v3로 이관](migrating-to-v3) 참조하세요. (기본값: false)

## 변수

Flight를 통해 변수를 저장하여 응용 프로그램의 어디서든 사용할 수 있습니다.

```php
// 변수 저장
Flight::set('id', 123);

// 응용 프로그램의 다른 위치에서
$id = Flight::get('id');
```
변수가 설정되었는지 확인하려면 다음을 수행할 수 있습니다:

```php
if (Flight::has('id')) {
  // 무언가 수행
}
```

변수를 지우려면 다음을 수행합니다:

```php
// id 변수 지움
Flight::clear('id');

// 모든 변수 지움
Flight::clear();
```

Flight는 구성 목적으로도 변수를 사용합니다.

```php
Flight::set('flight.log_errors', true);
```

## 오류 처리

### 오류 및 예외

모든 오류 및 예외는 Flight에 의해 잡히고 `error` 메서드로 전달됩니다.
기본 동작은 일반적인 `HTTP 500 Internal Server Error` 응답과 오류 정보를 보내는 것입니다.

사용자 정의로 이 동작을 덮어쓸 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 이를 활성화하려면
구성을 변경할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

### 찾을 수 없음

URL을 찾을 수 없을 때, Flight는 `notFound` 메서드를 호출합니다. 기본 동작은 `HTTP 404 Not Found` 응답과 간단한 메시지를 보내는 것입니다.

사용자 정의로 이 동작을 덮어쓸 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```