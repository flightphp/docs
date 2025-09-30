# 구성

## 개요

Flight는 프레임워크의 다양한 측면을 애플리케이션의 필요에 맞게 구성할 수 있는 간단한 방법을 제공합니다. 일부는 기본값으로 설정되어 있지만, 필요에 따라 이를 재정의할 수 있습니다. 또한 애플리케이션 전체에서 사용할 수 있는 자체 변수를 설정할 수도 있습니다.

## 이해

Flight의 특정 동작을 사용자 지정하려면 `set` 메서드를 통해 구성 값을 설정할 수 있습니다.

```php
Flight::set('flight.log_errors', true);
```

`app/config/config.php` 파일에서 사용할 수 있는 모든 기본 구성 변수를 확인할 수 있습니다.

## 기본 사용법

### Flight 구성 옵션

다음은 사용 가능한 모든 구성 설정 목록입니다:

- **flight.base_url** `?string` - Flight가 하위 디렉토리에서 실행 중인 경우 요청의 기본 URL을 재정의합니다. (기본값: null)
- **flight.case_sensitive** `bool` - URL에 대한 대소문자 구분 매칭. (기본값: false)
- **flight.handle_errors** `bool` - Flight가 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
  - Flight가 기본 PHP 동작 대신 오류를 처리하도록 하려면 이 값을 true로 설정해야 합니다.
  - [Tracy](/awesome-plugins/tracy)를 설치한 경우, Tracy가 오류를 처리할 수 있도록 이 값을 false로 설정해야 합니다.
  - [APM](/awesome-plugins/apm) 플러그인을 설치한 경우, APM이 오류를 로깅할 수 있도록 이 값을 true로 설정해야 합니다.
- **flight.log_errors** `bool` - 웹 서버의 오류 로그 파일에 오류를 로깅합니다. (기본값: false)
  - [Tracy](/awesome-plugins/tracy)를 설치한 경우, Tracy는 Tracy 구성에 따라 오류를 로깅하며 이 구성은 사용되지 않습니다.
- **flight.views.path** `string` - 뷰 템플릿 파일이 포함된 디렉토리. (기본값: ./views)
- **flight.views.extension** `string` - 뷰 템플릿 파일 확장자. (기본값: .php)
- **flight.content_length** `bool` - `Content-Length` 헤더를 설정합니다. (기본값: true)
  - [Tracy](/awesome-plugins/tracy)를 사용하는 경우, Tracy가 제대로 렌더링될 수 있도록 이 값을 false로 설정해야 합니다.
- **flight.v2.output_buffering** `bool` - 레거시 출력 버퍼링을 사용합니다. [v3로 마이그레이션](migrating-to-v3)을 참조하세요. (기본값: false)

### 로더 구성

로더에 대한 추가 구성 설정이 있습니다. 이는 클래스 이름에 `_`가 포함된 클래스를 자동 로드할 수 있게 합니다.

```php
// 언더스코어를 사용한 클래스 로딩 활성화
// 기본값은 true
Loader::$v2ClassLoading = false;
```

### 변수

Flight는 애플리케이션의 어디서나 사용할 수 있도록 변수를 저장할 수 있게 합니다.

```php
// 변수 저장
Flight::set('id', 123);

// 애플리케이션의 다른 곳에서
$id = Flight::get('id');
```
변수가 설정되었는지 확인하려면 다음을 할 수 있습니다:

```php
if (Flight::has('id')) {
  // 무언가 수행
}
```

변수를 지우려면 다음을 할 수 있습니다:

```php
// id 변수를 지움
Flight::clear('id');

// 모든 변수 지움
Flight::clear();
```

> **참고:** 변수를 설정할 수 있다고 해서 반드시 설정해야 한다는 의미는 아닙니다. 이 기능을 절제해서 사용하세요. 이유는 여기에 저장된 모든 것이 전역 변수가 되기 때문입니다. 전역 변수는 애플리케이션의 어디서나 변경될 수 있어 버그를 추적하기 어렵게 만듭니다. 또한 [단위 테스트](/guides/unit-testing)와 같은 작업을 복잡하게 만들 수 있습니다.

### 오류 및 예외

모든 오류와 예외는 Flight에 의해 포착되어 `error` 메서드로 전달됩니다. `flight.handle_errors`가 true로 설정된 경우입니다.

기본 동작은 오류 정보와 함께 일반적인 `HTTP 500 Internal Server Error` 응답을 보내는 것입니다.

이 동작을 [재정의](/learn/extending)하여 필요에 맞게 사용할 수 있습니다:

```php
Flight::map('error', function (Throwable $error) {
  // 오류 처리
  echo $error->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 로깅되지 않습니다. 이를 활성화하려면 구성을 변경하세요:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Not Found

URL을 찾을 수 없을 때 Flight는 `notFound` 메서드를 호출합니다. 기본 동작은 간단한 메시지와 함께 `HTTP 404 Not Found` 응답을 보내는 것입니다.

이 동작을 [재정의](/learn/extending)하여 필요에 맞게 사용할 수 있습니다:

```php
Flight::map('notFound', function () {
  // 찾을 수 없음 처리
});
```

## 관련 자료
- [Flight 확장](/learn/extending) - Flight의 핵심 기능을 확장하고 사용자 지정하는 방법.
- [단위 테스트](/guides/unit-testing) - Flight 애플리케이션에 대한 단위 테스트 작성 방법.
- [Tracy](/awesome-plugins/tracy) - 고급 오류 처리 및 디버깅을 위한 플러그인.
- [Tracy 확장](/awesome-plugins/tracy_extensions) - Tracy를 Flight와 통합하기 위한 확장.
- [APM](/awesome-plugins/apm) - 애플리케이션 성능 모니터링 및 오류 추적을 위한 플러그인.

## 문제 해결
- 구성의 모든 값을 확인하는 데 문제가 있는 경우 `var_dump(Flight::get());`을 실행할 수 있습니다.

## 변경 로그
- v3.5.0 - 레거시 출력 버퍼링 동작을 지원하기 위해 `flight.v2.output_buffering` 구성을 추가했습니다.
- v2.0 - 핵심 구성 추가.