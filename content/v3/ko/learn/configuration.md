# 설정

Flight을 통해 설정 값을 설정함으로써 Flight의 특정 동작을 사용자 정의할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

## 사용 가능한 구성 설정

다음은 모든 사용 가능한 구성 설정 목록입니다:

- **flight.base_url** `?string` - 요청의 베이스 URL을 재정의합니다. (기본값: null)
- **flight.case_sensitive** `bool` - URL에 대한 대소문자 구분 매칭. (기본값: false)
- **flight.handle_errors** `bool` - Flight이 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
- **flight.log_errors** `bool` - 오류를 웹 서버의 오류 로그 파일에 기록합니다. (기본값: false)
- **flight.views.path** `string` - 뷰 템플릿 파일이 포함된 디렉토리. (기본값: ./views)
- **flight.views.extension** `string` - 뷰 템플릿 파일 확장자. (기본값: .php)
- **flight.content_length** `bool` - `Content-Length` 헤더를 설정합니다. (기본값: true)
- **flight.v2.output_buffering** `bool` - 레거시 출력 버퍼링 사용. [v3로 이주하기](migrating-to-v3) 참조 (기본값: false)

## 로더 구성

로더에 대한 추가 구성 설정이 있습니다. 이를 통해 클래스 이름에 `_`가 포함된 클래스를 자동으로 로드할 수 있습니다.

```php
// 밑줄(_)을 사용한 클래스 로딩 활성화
// 기본적으로 true로 설정됩니다
Loader::$v2ClassLoading = false;
```

## 변수

Flight은 변수를 저장하여 애플리케이션 어디에서나 사용할 수 있습니다.

```php
// 변수 저장
Flight::set('id', 123);

// 애플리케이션의 다른 위치에서
$id = Flight::get('id');
```

변수가 설정되었는지 확인하려면 다음을 수행할 수 있습니다:

```php
if (Flight::has('id')) {
  // 무언가 수행
}
```

다음을 통해 변수를 지울 수 있습니다:

```php
// id 변수 지우기
Flight::clear('id');

// 모든 변수 지우기
Flight::clear();
```

Flight은 구성 목적을 위해 또한 변수를 사용합니다.

```php
Flight::set('flight.log_errors', true);
```

## 오류 처리

### 오류 및 예외

모든 오류와 예외는 Flight에서 포착되어 `error` 메소드로 전달됩니다.
기본 동작은 약간의 오류 정보를 포함한 일반적인 `HTTP 500 Internal Server Error` 응답을 보내는 것입니다.

사용자 정의를 위해 이 동작을 무시할 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 이를 활성화하려면
설정을 변경할 수 있습니다:

```php
Flight::set('flight.log_errors', true);
```

### 찾을 수 없음

URL을 찾을 수 없을 때, Flight가 `notFound` 메소드를 호출합니다. 기본적으로
`HTTP 404 Not Found` 응답을 보내며 간단한 메시지를 표시합니다.

사용자 정의를 위해 이 동작을 무시할 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```