# 멋진 플러그인

Flight은 매우 확장 가능합니다. Flight 애플리케이션에 기능을 추가하기 위해 사용할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에서 공식적으로 지원하고, 다른 일부는 시작하기 위한 마이크로/라이트 라이브러리입니다.

## API 문서화

API 문서화는 모든 API에 필수적입니다. 개발자들이 API와 상호작용하는 방법을 이해하고 반환되는 것을 예상할 수 있도록 도와줍니다. Flight 프로젝트에 API 문서를 생성하는 데 도움이 되는 몇 가지 도구가 있습니다.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber가 작성한 블로그 게시물로, OpenAPI 사양을 FlightPHP와 함께 사용하여 API 우선 접근 방식을 통해 API를 구축하는 방법에 대해 설명합니다.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI는 Flight 프로젝트에 API 문서를 생성하는 데 훌륭한 도구입니다. 사용하기 매우 쉽고 필요에 맞게 맞춤화할 수 있습니다. 이는 Swagger 문서를 생성하는 데 도움이 되는 PHP 라이브러리입니다.

## 애플리케이션 성능 모니터링 (APM)

애플리케이션 성능 모니터링 (APM)은 모든 애플리케이션에 필수적입니다. 애플리케이션의 성능을 이해하고 병목 현상을 파악하는 데 도움이 됩니다. Flight와 함께 사용할 수 있는 여러 APM 도구가 있습니다.
- <span class="badge bg-info">베타</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM은 Flight 애플리케이션을 모니터링하는 간단한 APM 라이브러리입니다. 애플리케이션의 성능을 모니터링하고 병목 현상을 식별하는 데 사용할 수 있습니다.

## 인증/인가

인증과 인가는 누구든 무엇에 접근할 수 있는지 제어를 요구하는 모든 애플리케이션에 필수적입니다.

- <span class="badge bg-primary">공식</span> [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight Permissions 라이브러리입니다. 이 라이브러리는 애플리케이션에 사용자 및 애플리케이션 수준의 권한을 추가하는 간단한 방법입니다.

## 캐싱

캐싱은 애플리케이션 속도를 높이는 훌륭한 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- <span class="badge bg-primary">공식</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 가볍고, 간단하며, 독립적인 PHP 인-파일 캐싱 클래스

## CLI

CLI 애플리케이션은 애플리케이션과 상호작용하는 훌륭한 방법입니다. 컨트롤러를 생성하거나 모든 경로를 표시하는 등에 사용할 수 있습니다.

- <span class="badge bg-primary">공식</span> [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 훌륭한 방법입니다. 사용자 선호도, 애플리케이션 설정 등을 저장하는 데 사용할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie는 쿠키를 관리하는 간단하고 효과적인 PHP 라이브러리입니다.

## 디버깅

디버깅은 로컬 환경에서 개발할 때 필수적입니다. 디버깅 경험을 향상시키는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - 이는 Flight와 함께 사용할 수 있는 완전한 기능의 오류 처리기입니다. 애플리케이션을 디버그하는 데 도움이 되는 여러 패널을 가지고 있습니다. 확장하기 쉽고 자체 패널을 추가할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되며, Flight 프로젝트에 대한 디버깅을 위해 몇 가지 추가 패널을 제공합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 이를 통해 데이터를 저장하고 검색합니다. 일부 데이터베이스 라이브러리는 쿼리를 작성하는 단순한 래퍼이고, 일부는 완전한 ORMs입니다.

- <span class="badge bg-primary">공식</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 공식 Flight PDO Wrapper로, 코어의 일부입니다. 쿼리를 작성하고 실행하는 과정을 단순화하는 데 도움이 되는 간단한 래퍼입니다. ORM이 아닙니다.
- <span class="badge bg-primary">공식</span> [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/Mapper입니다. 데이터베이스에서 데이터를 쉽게 검색하고 저장하는 훌륭한 작은 라이브러리입니다.
- [byjg/php-migration](/awesome-plugins/migrations) - 프로젝트의 모든 데이터베이스 변경 사항을 추적하는 플러그인입니다.

## 암호화

암호화는 민감한 데이터를 저장하는 모든 애플리케이션에 필수적입니다. 데이터를 암호화하고 복호화하는 것은 그리 어렵지 않지만, 암호화 키를 적절히 저장하는 [것](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [은](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [어려울](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) 수 있습니다. 가장 중요한 것은 암호화 키를 공용 디렉터리에 저장하거나 코드 저장소에 커밋하지 않는 것입니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 이는 데이터를 암호화하고 복호화하는 데 사용할 수 있는 라이브러리입니다. 데이터를 암호화하고 복호화하기 시작하는 것은 상당히 간단합니다.

## 작업 큐

작업 큐는 비동기적으로 작업을 처리하는 데 매우 유용합니다. 이메일 보내기, 이미지 처리 등 실시간으로 처리할 필요가 없는 모든 작업에 사용할 수 있습니다.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue는 작업을 비동기적으로 처리하는 라이브러리입니다. beanstalkd, MySQL/MariaDB, SQLite 및 PostgreSQL과 함께 사용할 수 있습니다.

## 세션

세션은 API에는 크게 유용하지 않지만, 웹 애플리케이션을 구축할 때는 상태 유지 및 로그인 정보를 위해 필수적일 수 있습니다.

- <span class="badge bg-primary">공식</span> [flightphp/session](/awesome-plugins/session) - 공식 Flight Session 라이브러리입니다. PHP의 내장 세션 처리를 사용하는 간단한 세션 라이브러리로, 세션 데이터를 저장하고 검색하는 데 사용할 수 있습니다.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터를 선택적으로 암호화/복호화하기 위해 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- <span class="badge bg-warning">폐기됨</span> [flightphp/core View](/learn#views) - 이는 코어의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 페이지가 두 개 이상 있다면 사용하지 않는 것이 좋습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 사용하기 매우 쉽고 Twig이나 Smarty보다 PHP 구문에 더 가깝게 느껴지는 완전한 기능의 템플릿 엔진입니다. 확장하기 쉽고 자체 필터와 함수를 추가할 수 있습니다.

## 기여

공유하고 싶은 플러그인이 있나요? 목록에 추가하기 위해 풀 리퀘스트를 제출하세요!