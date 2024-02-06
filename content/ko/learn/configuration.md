# 구성

Flight의 특정 동작을 구성 값을 설정하여 사용자 정의할 수 있습니다
`set` 메소드를 통해.

```php
Flight::set('flight.log_errors', true);
```

다음은 모든 가능한 구성 설정 목록입니다:

- **flight.base_url** - 요청의 기본 URL을 무시합니다. (기본값: null)
- **flight.case_sensitive** - URL에 대해 대소문자를 구분합니다. (기본값: false)
- **flight.handle_errors** - Flight가 모든 오류를 내부적으로 처리할 수 있도록 합니다. (기본값: true)
- **flight.log_errors** - 오류를 웹 서버의 오류 로그 파일에 기록합니다. (기본값: false)
- **flight.views.path** - 뷰 템플릿 파일이 있는 디렉토리입니다. (기본값: ./views)
- **flight.views.extension** - 뷰 템플릿 파일 확장자입니다. (기본값: .php)