# 프레임워크 API 메서드

Flight는 사용하기 쉽고 이해하기 쉽게 설계되었습니다. 다음은 프레임워크의 완전한
메서드 집합입니다. 일반적인 정적 메서드인 핵심 메서드와 필터링하거나 재정의할 수 있는 매핑 메서드인 확장 가능한 메서드로 구성됩니다.

## 핵심 메서드

이 메서드는 프레임워크의 핵심이며 재정의할 수 없습니다.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 사용자 정의 프레임워크 메서드를 생성합니다.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 프레임워크 메서드에 클래스를 등록합니다.
Flight::unregister(string $name) // 프레임워크 메서드에 클래스를 등록 해제합니다.
Flight::before(string $name, callable $callback) // 프레임워크 메서드 이전에 필터를 추가합니다.
Flight::after(string $name, callable $callback) // 프레임워크 메서드 이후에 필터를 추가합니다.
Flight::path(string $path) // 클래스 자동 로딩을 위한 경로를 추가합니다.
Flight::get(string $key) // Flight::set()에 의해 설정된 변수를 가져옵니다.
Flight::set(string $key, mixed $value) // Flight 엔진 내에서 변수를 설정합니다.
Flight::has(string $key) // 변수가 설정되었는지 확인합니다.
Flight::clear(array|string $key = []) // 변수를 지웁니다.
Flight::init() // 프레임워크를 기본 설정으로 초기화합니다.
Flight::app() // 애플리케이션 객체 인스턴스를 가져옵니다.
Flight::request() // 요청 객체 인스턴스를 가져옵니다.
Flight::response() // 응답 객체 인스턴스를 가져옵니다.
Flight::router() // 라우터 객체 인스턴스를 가져옵니다.
Flight::view() // 뷰 객체 인스턴스를 가져옵니다.
```

## 확장 가능한 메서드

```php
Flight::start() // 프레임워크를 시작합니다.
Flight::stop() // 프레임워크를 중지하고 응답을 보냅니다.
Flight::halt(int $code = 200, string $message = '') // 선택적 상태 코드 및 메시지와 함께 프레임워크를 중지합니다.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // URL 패턴을 콜백에 매핑합니다.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // POST 요청 URL 패턴을 콜백에 매핑합니다.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PUT 요청 URL 패턴을 콜백에 매핑합니다.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PATCH 요청 URL 패턴을 콜백에 매핑합니다.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // DELETE 요청 URL 패턴을 콜백에 매핑합니다.
Flight::group(string $pattern, callable $callback) // URL의 그룹화를 생성합니다. 패턴은 문자열이어야 합니다.
Flight::getUrl(string $name, array $params = []) // 경로 별칭을 기반으로 URL을 생성합니다.
Flight::redirect(string $url, int $code) // 다른 URL로 리디렉션합니다.
Flight::download(string $filePath) // 파일을 다운로드합니다.
Flight::render(string $file, array $data, ?string $key = null) // 템플릿 파일을 렌더링합니다.
Flight::error(Throwable $error) // HTTP 500 응답을 보냅니다.
Flight::notFound() // HTTP 404 응답을 보냅니다.
Flight::etag(string $id, string $type = 'string') // ETag HTTP 캐싱을 수행합니다.
Flight::lastModified(int $time) // 마지막 수정된 HTTP 캐싱을 수행합니다.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON 응답을 보냅니다.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONP 응답을 보냅니다.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON 응답을 보내고 프레임워크를 중지합니다.
Flight::onEvent(string $event, callable $callback) // 이벤트 리스너를 등록합니다.
Flight::triggerEvent(string $event, ...$args) // 이벤트를 트리거합니다.
```

`map` 및 `register`로 추가된 사용자 정의 메서드는 필터링할 수도 있습니다. 이러한 메서드를 매핑하는 방법에 대한 예는 [Flight 확장하기](/learn/extending) 가이드를 참조하십시오.