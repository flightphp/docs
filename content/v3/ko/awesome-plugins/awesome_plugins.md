# 멋진 플러그인

Flight는 놀라울 정도로 확장 가능합니다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에서 공식적으로 지원하며, 다른 일부는 시작하는 데 도움이 되는 마이크로/라이트 라이브러리입니다.

## API 문서화

API 문서화는 모든 API에 필수적입니다. 개발자들이 API와 상호 작용하는 방법을 이해하고 반환되는 것을 예상할 수 있도록 돕습니다. Flight 프로젝트에 대한 API 문서화를 생성하는 데 도움이 되는 몇 가지 도구가 있습니다.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber가 작성한 블로그 포스트로, OpenAPI 사양을 FlightPHP와 함께 사용하여 API 우선 접근 방식을 통해 API를 구축하는 방법에 대해 설명합니다.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI는 Flight 프로젝트에 대한 API 문서화를 생성하는 데 도움이 되는 훌륭한 도구입니다. 사용하기 매우 쉽고 필요에 맞게 사용자 지정할 수 있습니다. 이는 Swagger 문서화를 생성하는 데 도움이 되는 PHP 라이브러리입니다.

## 애플리케이션 성능 모니터링 (APM)

애플릭케이션 성능 모니터링 (APM)은 모든 애플리케이션에 필수적입니다. 애플리케이션이 어떻게 작동하는지 이해하고 병목 현상이 어디에 있는지 파악하는 데 도움이 됩니다. Flight와 함께 사용할 수 있는 여러 APM 도구가 있습니다.
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM은 Flight 애플리케이션을 모니터링하는 데 사용할 수 있는 간단한 APM 라이브러리입니다. 애플리케이션의 성능을 모니터링하고 병목 현상을 식별하는 데 도움이 됩니다.

## Async

Flight는 이미 빠른 프레임워크이지만, 터보 엔진을 장착하면 모든 것이 더 재미있고 (도전적) 됩니다!

- [flightphp/async](/awesome-plugins/async) - 공식 Flight Async 라이브러리. 이 라이브러리는 애플리케이션에 비동기 처리를 추가하는 간단한 방법입니다. Swoole/Openswoole을 내부적으로 사용하여 작업을 비동기적으로 실행하는 간단하고 효과적인 방법을 제공합니다.

## 권한 부여/권한

권한 부여와 권한은 누가 무엇에 접근할 수 있는지에 대한 제어가 필요한 모든 애플리케이션에 필수적입니다.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight Permissions 라이브러리. 이 라이브러리는 애플리케이션에 사용자 및 애플리케이션 수준의 권한을 추가하는 간단한 방법입니다. 

## 인증

인증은 사용자 ID를 확인하고 API 엔드포인트를 보호해야 하는 애플리케이션에 필수적입니다.

- [firebase/php-jwt](/awesome-plugins/jwt) - PHP용 JSON Web Token (JWT) 라이브러리. Flight 애플리케이션에 토큰 기반 인증을 구현하는 간단하고 안전한 방법입니다. 상태 비저장 API 인증, 미들웨어를 사용한 경로 보호, OAuth 스타일의 권한 부여 흐름 구현에 완벽합니다.

## 캐싱

캐싱은 애플리케이션을 가속화하는 훌륭한 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 가볍고 간단하며 독립적인 PHP 인파일 캐싱 클래스

## CLI

CLI 애플리케이션은 애플리케이션과 상호 작용하는 훌륭한 방법입니다. 컨트롤러 생성, 모든 경로 표시 등에 사용할 수 있습니다.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 훌륭한 방법입니다. 사용자 선호도, 애플리케이션 설정 등을 저장하는 데 사용할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie는 쿠키를 관리하는 간단하고 효과적인 방법을 제공하는 PHP 라이브러리입니다.

## 디버깅

디버깅은 로컬 환경에서 개발할 때 필수적입니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 완전한 기능을 갖춘 오류 핸들러입니다. 애플리케이션을 디버깅하는 데 도움이 되는 여러 패널이 있습니다. 확장하기 매우 쉽고 자체 패널을 추가할 수도 있습니다.
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 핸들러와 함께 사용되며, 이 플러그인은 Flight 프로젝트에 특화된 디버깅을 돕기 위해 몇 가지 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 쿼리를 작성하는 단순한 래퍼이고, 일부는 완전한 ORMs입니다.

- <span class="badge bg-primary">official</span> [flightphp/core SimplePdo](/learn/simple-pdo) - 코어의 일부인 공식 Flight PDO 도우미. `insert()`, `update()`, `delete()`, `transaction()`과 같은 편리한 도우미 메서드를 가진 현대적인 래퍼로 데이터베이스 작업을 단순화합니다. 모든 결과는 유연한 배열/객체 접근을 위해 컬렉션으로 반환됩니다. ORM이 아니며, PDO를 더 잘 작업하는 방법일 뿐입니다.
- <span class="badge bg-warning">deprecated</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - 코어의 일부인 공식 Flight PDO 래퍼 (v3.18.0부터 deprecated). 대신 SimplePdo를 사용하세요.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/Mapper. 데이터베이스에서 데이터를 쉽게 검색하고 저장하는 훌륭한 작은 라이브러리입니다.
- [byjg/php-migration](/awesome-plugins/migrations) - 프로젝트의 모든 데이터베이스 변경 사항을 추적하는 플러그인.

## 암호화

암호화는 민감한 데이터를 저장하는 모든 애플리케이션에 필수적입니다. 데이터를 암호화하고 복호화하는 것은 어렵지 않지만, 암호화 키를 적절히 저장하는 [것은](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [어렵](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [습니다](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). 가장 중요한 것은 암호화 키를 공개 디렉토리에 저장하지 않거나 코드 저장소에 커밋하지 않는 것입니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 복호화하는 데 사용할 수 있는 라이브러리입니다. 데이터를 암호화하고 복호화하기 시작하는 것이 상당히 간단합니다.

## 작업 큐

작업 큐는 작업을 비동기적으로 처리하는 데 매우 도움이 됩니다. 이메일 보내기, 이미지 처리 또는 실시간으로 수행할 필요가 없는 모든 작업에 사용할 수 있습니다.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue는 작업을 비동기적으로 처리하는 데 사용할 수 있는 라이브러리입니다. beanstalkd, MySQL/MariaDB, SQLite, PostgreSQL과 함께 사용할 수 있습니다.

## 세션

세션은 API에는 정말 유용하지 않지만 웹 애플리케이션을 구축할 때 상태와 로그인 정보를 유지하는 데 필수적일 수 있습니다.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 공식 Flight Session 라이브러리. 세션 데이터를 저장하고 검색하는 데 사용할 수 있는 간단한 세션 라이브러리입니다. PHP의 내장 세션 처리를 사용합니다.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/복호화를 위해 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - 코어의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 몇 페이지 이상이 있으면 사용하지 않는 것이 좋습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 사용하기 매우 쉽고 Twig이나 Smarty보다 PHP 구문에 더 가까운 느낌의 완전한 기능을 갖춘 템플릿 엔진입니다. 확장하기 매우 쉽고 자체 필터와 함수를 추가할 수도 있습니다.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate는 에셋 컴파일, 템플릿 상속, 변수 처리 기능을 가진 강력한 PHP 템플릿 엔진입니다. 자동 CSS/JS 최소화, 캐싱, Base64 인코딩, 선택적 Flight PHP 프레임워크 통합을 특징으로 합니다.

## WordPress 통합

WordPress 프로젝트에서 Flight를 사용하고 싶으신가요? 이를 위한 편리한 플러그인이 있습니다!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - 이 WordPress 플러그인은 Flight를 WordPress와 함께 실행할 수 있게 합니다. WordPress 사이트에 사용자 지정 API, 마이크로서비스 또는 전체 앱을 Flight 프레임워크를 사용하여 추가하는 데 완벽합니다. 두 세계의 장점을 모두 누리고 싶다면 매우 유용합니다!

## 기여

공유하고 싶은 플러그인이 있나요? 목록에 추가하기 위해 풀 리퀘스트를 제출하세요!