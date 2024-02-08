# 설정

Flight을 통해 일부 동작을 사용자 정의할 수 있습니다. 설정 값을 설정 메서드를 통해 사용자 정의할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

## 사용 가능한 설정

다음은 모든 사용 가능한 설정 목록입니다:

- **flight.base_url** - 요청의 기본 URL을 덮어쓰기합니다. (기본값: null)
- **flight.case_sensitive** - URL의 대소문자를 구분하여 일치시킵니다. (기본값: false)
- **flight.handle_errors** - Flight이 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
- **flight.log_errors** - 오류를 웹 서버의 오류 로그 파일에 기록합니다. (기본값: false)
- **flight.views.path** - 뷰 템플릿 파일이 있는 디렉토리입니다. (기본값: ./views)
- **flight.views.extension** - 뷰 템플릿 파일 확장자입니다. (기본값: .php)

## 변수

Flight을 사용하면 응용 프로그램 어디에서든 사용할 수 있도록 변수를 저장할 수 있습니다.

```php
// 변수 저장
Flight::set('id', 123);

// 응용 프로그램 다른 곳에서
$id = Flight::get('id');
```
변수가 설정되어 있는지 확인하려면 다음을 수행할 수 있습니다:

```php
if (Flight::has('id')) {
  // 무언가 수행
}
```

변수를 지우려면 다음을 수행합니다:

```php
// id 변수 지우기
Flight::clear('id');

// 모든 변수 지우기
Flight::clear();
```

Flight은 구성 목적으로도 변수를 사용합니다.

```php
Flight::set('flight.log_errors', true);
```

## 오류 처리

### 오류 및 예외

모든 오류와 예외는 Flight에서 잡혀 `error` 메서드로 전달됩니다. 기본 동작은 일부 오류 정보와 함께 일반적인 `HTTP 500 Internal Server Error` 응답을 보내는 것입니다.

개인적인 필요에 따라 이 동작을 재정의할 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 이를 활성화하려면 구성을 변경할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

### 찾을 수 없음

URL을 찾을 수 없을 때, Flight는 `notFound` 메서드를 호출합니다. 기본 동작은 간단한 메시지와 함께 `HTTP 404 Not Found` 응답을 보내는 것입니다.

개인적인 필요에 따라 이 동작을 재정의할 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없는 핸들링
});
```