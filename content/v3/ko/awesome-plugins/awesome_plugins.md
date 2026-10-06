# 멋진 플러그인

Flight는 매우 확장성이 뛰어납니다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀이 공식적으로 지원하며, 다른 일부는 시작하는 데 도움이 되는 마이크로/라이트 라이브러리입니다.

## AI 도구

Flight는 AI 기반 플러그인으로 더욱 멋져질 수 있습니다.

- [Flight MCP](/awesome-plugins/mcp) - MCP(모델 제어 프로토콜)를 Flight와 통합하여 원활한 AI 기반 기능을 제공하는 플러그인입니다. 주로 문서 페이지에 중점을 두며, Flight 프로젝트에 대한 최신 정보를 제공하여 토큰 비용을 절감하는 데 도움을 줍니다.
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - HTTP 및 stdio를 지원하는 FlightPHP MCP 서버 스켈레톤으로, 도구, 프롬프트 및 리소스의 자동 검색 기능도 포함합니다.

## API 문서

API 문서는 모든 API에 매우 중요합니다. 개발자가 API와 상호 작용하는 방법과 기대할 수 있는 응답을 이해하는 데 도움이 됩니다. Flight 프로젝트용 API 문서를 생성하는 데 도움이 되는 몇 가지 도구가 있습니다.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber가 작성한 블로그 게시물로, API 우선 접근 방식을 사용하여 FlightPHP와 함께 OpenAPI Spec을 활용해 API를 구축하는 방법을 다룹니다.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI는 Flight 프로젝트의 API 문서를 생성하는 데 유용한 도구입니다. 사용하기 매우 쉽고 필요에 맞게 사용자 지정할 수 있습니다. Swagger 문서를 생성하는 데 도움이 되는 PHP 라이브러리입니다.

## 애플리케이션 성능 모니터링(APM)

애플리케이션 성능 모니터링(APM)은 모든 애플리케이션에 매우 중요합니다. 애플리케이션의 성능과 병목 지점을 파악하는 데 도움이 됩니다. Flight와 함께 사용할 수 있는 여러 APM 도구가 있습니다.
- <span class="badge bg-primary">공식</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM은 Flight 애플리케이션을 모니터링하는 데 사용할 수 있는 간단한 APM 라이브러리입니다. 애플리케이션의 성능을 모니터링하고 병목 지점을 식별하는 데 도움이 됩니다.

## 비동기

Flight는 이미 빠른 프레임워크이지만 여기에 터보 엔진을 장착하면 모든 것이 더 재미있어집니다(그리고 도전적이기도 합니다)!

- [flightphp/async](/awesome-plugins/async) - 공식 Flight Async 라이브러리입니다. 이 라이브러리는 애플리케이션에 비동기 처리를 추가하는 간단한 방법입니다. 내부적으로 Swoole/Openswoole을 사용하여 작업을 비동기적으로 실행하는 간단하고 효과적인 방법을 제공합니다.

## 인가/권한

인가와 권한은 누가 무엇에 접근할 수 있는지에 대한 제어가 필요한 모든 애플리케이션에 매우 중요합니다.

- <span class="badge bg-primary">공식</span> [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight 권한 라이브러리입니다. 이 라이브러리는 애플리케이션에 사용자 및 애플리케이션 수준의 권한을 추가하는 간단한 방법입니다.

## 인증

인증은 사용자 ID를 확인하고 API 엔드포인트를 보호해야 하는 애플리케이션에 필수적입니다.

- [firebase/php-jwt](/awesome-plugins/jwt) - PHP용 JSON 웹 토큰(JWT) 라이브러리입니다. Flight 애플리케이션에서 토큰 기반 인증을 구현하는 간단하고 안전한 방법입니다. 상태 비저장 API 인증, 미들웨어로 경로 보호, OAuth 스타일 인증 흐름 구현에 적합합니다.

## 캐싱

캐싱은 애플리케이션 속도를 높이는 좋은 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- <span class="badge bg-primary">공식</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 가볍고 간단하며 독립적인 PHP 파일 내 캐싱 클래스

## CLI

CLI 애플리케이션은 애플리케이션과 상호 작용하는 좋은 방법입니다. 컨트롤러 생성, 모든 경로 표시 등을 수행할 수 있습니다.

- <span class="badge bg-primary">공식</span> [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다.

## 쿠키

쿠키는 클라이언트 측에 소량의 데이터를 저장하는 좋은 방법입니다. 사용자 기본 설정, 애플리케이션 설정 등을 저장하는 데 사용할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie는 쿠키를 관리하는 간단하고 효과적인 방법을 제공하는 PHP 라이브러리입니다.

## 디버깅

디버깅은 로컬 환경에서 개발할 때 매우 중요합니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 완전한 기능을 갖춘 오류 처리기입니다. 애플리케이션을 디버깅하는 데 도움이 되는 여러 패널이 있습니다. 또한 확장하고 자체 패널을 추가하기 매우 쉽습니다.
- <span class="badge bg-primary">공식</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되는 이 플러그인은 특히 Flight 프로젝트의 디버깅을 돕기 위해 몇 가지 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하기 위한 래퍼이고 일부는 완전한 기능을 갖춘 ORM입니다.

- <span class="badge bg-primary">공식</span> [flightphp/core SimplePdo](/learn/simple-pdo) - 핵심의 일부인 공식 Flight PDO 헬퍼입니다. 데이터베이스 작업을 단순화하는 `insert()`, `update()`, `delete()`, `transaction()`과 같은 편리한 헬퍼 메서드를 제공하는 현대적인 래퍼입니다. 모든 결과는 유연한 배열/객체 접근을 위해 컬렉션으로 반환됩니다. ORM이 아니라 PDO로 작업하는 더 나은 방법입니다.
- <span class="badge bg-warning">사용 중단됨</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - 핵심의 일부인 공식 Flight PDO 래퍼(v3.18.0부터 사용 중단됨). SimplePdo를 대신 사용하세요.
- <span class="badge bg-primary">공식</span> [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/매퍼입니다. 데이터베이스에서 데이터를 쉽게 검색하고 저장할 수 있는 훌륭한 소형 라이브러리입니다.
- [byjg/php-migration](/awesome-plugins/migrations) - 프로젝트의 모든 데이터베이스 변경 사항을 추적하는 플러그인.
- [knifelemon/easy-query](/awesome-plugins/easy-query) - 준비된 문을 위한 SQL과 매개변수를 생성하는 가볍고 유창한 SQL 쿼리 빌더입니다. [SimplePdo](/learn/simple-pdo)와 잘 작동합니다.

## 암호화

암호화는 민감한 데이터를 저장하는 모든 애플리케이션에 중요합니다. 데이터를 암호화하고 해독하는 것은 그리 어렵지 않지만 암호화 키를 적절히 저장하는 것은 [어려울](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [수](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [있습니다](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). 가장 중요한 것은 암호화 키를 공개 디렉토리에 저장하거나 코드 저장소에 커밋하지 않는 것입니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 복호화하는 데 사용할 수 있는 라이브러리입니다. 암호화 및 복호화를 시작하는 것은 매우 간단합니다.

## 이메일

이메일 전송은 대부분의 웹 애플리케이션에서 핵심적인 요구 사항입니다 - 환영 메시지, 비밀번호 재설정, 알림 등. 이러한 라이브러리는 전달성을 확실히 유지하면서 이 과정을 수월하게 만들어 줍니다.

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail은 Symfony Mailer를 Flight 친화적인 유창한 API로 래핑합니다. 간단한 DSN 문자열을 통해 SMTP 또는 주요 제공업체로 전송하고, 메시지별로 다른 제공업체를 라우팅하며, Twig 또는 Latte 템플릿으로 본문을 렌더링할 수 있습니다. 이는 Flight의 비공식 플러그인이며 Flight 팀이 유지 관리하지 않습니다.

## 작업 큐

작업 큐는 작업을 비동기적으로 처리하는 데 정말 유용합니다. 이메일 전송, 이미지 처리 또는 실시간으로 수행할 필요가 없는 모든 작업이 해당될 수 있습니다.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue는 작업을 비동기적으로 처리하는 데 사용할 수 있는 라이브러리입니다. beanstalkd, MySQL/MariaDB, SQLite 및 PostgreSQL과 함께 사용할 수 있습니다.

## 세션

세션은 API에는 실제로 유용하지 않지만 웹 애플리케이션을 구축할 때 상태 및 로그인 정보를 유지하는 데 중요할 수 있습니다.

- <span class="badge bg-primary">공식</span> [flightphp/session](/awesome-plugins/session) - 공식 Flight 세션 라이브러리입니다. 세션 데이터를 저장하고 검색하는 데 사용할 수 있는 간단한 세션 라이브러리입니다. PHP의 내장 세션 처리를 사용합니다.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 세션 관리자(비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/복호화를 위해 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- <span class="badge bg-warning">사용 중단됨</span> [flightphp/core View](/learn#views) - 핵심의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 페이지가 몇 개 이상 있다면 사용하지 않는 것이 좋습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 사용하기 매우 쉽고 Twig나 Smarty보다 PHP 구문에 더 가깝게 느껴지는 완전한 기능을 갖춘 템플릿 엔진입니다. 또한 확장하고 자체 필터와 함수를 추가하기 매우 쉽습니다.
- [twig/twig](/awesome-plugins/twig) - Twig는 유연하고 빠르며 안전한 템플릿 엔진입니다(Symfony에서 사용하는 것과 동일). AI 도구와 많은 PHP 개발자들이 잘 알고 있으며, 기본적으로 출력을 자동 이스케이프하고 방대한 확장 생태계를 갖추고 있습니다.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate은 자산 컴파일, 템플릿 상속 및 변수 처리를 지원하는 강력한 PHP 템플릿 엔진입니다. 자동 CSS/JS 축소, 캐싱, Base64 인코딩 및 선택적 Flight PHP 프레임워크 통합 기능을 제공합니다.

## WordPress 통합

Flight를 WordPress 프로젝트에서 사용하고 싶으신가요? 그에 맞는 편리한 플러그인이 있습니다!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - 이 WordPress 플러그인을 사용하면 Flight를 WordPress와 함께 바로 실행할 수 있습니다. Flight 프레임워크를 사용하여 WordPress 사이트에 사용자 정의 API, 마이크로서비스 또는 전체 앱을 추가하는 데 적합합니다. 두 세계의 장점을 모두 원한다면 매우 유용합니다!

## 기여하기

공유하고 싶은 플러그인이 있나요? 목록에 추가하려면 풀 리퀘스트를 제출하세요!