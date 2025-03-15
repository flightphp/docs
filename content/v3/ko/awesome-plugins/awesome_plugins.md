# 멋진 플러그인

Flight는 믿을 수 없을 정도로 확장 가능하다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수 있는 여러 플러그인이 있다. 일부는 Flight 팀에서 공식적으로 지원하며, 다른 일부는 시작하는 데 도움이 되는 마이크로/라이트 라이브러리이다.

## API 문서화

API 문서화는 모든 API에 필수적이다. 이는 개발자가 API와 상호작용하는 방법과 반환할 내용을 이해하는 데 도움을 준다. Flight 프로젝트를 위한 API 문서를 생성하는 데 도움을 줄 수 있는 몇 가지 도구가 있다.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - API 우선 접근 방식을 사용하여 FlightPHP로 API를 구축하는 방법에 대해 Daniel Schreiber가 작성한 블로그 게시물이다.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI는 Flight 프로젝트를 위한 API 문서를 생성하는 데 도움이 되는 훌륭한 도구이다. 사용이 매우 쉽고 필요에 맞게 사용자 정의할 수 있다. Swagger 문서를 생성하는 데 도움이 되는 PHP 라이브러리이다.

## 애플리케이션 성능 모니터링 (APM)

애플리케이션 성능 모니터링(APM)은 모든 애플리케이션에 필수적이다. 이는 애플리케이션의 성능을 이해하고 병목 지점을 찾는 데 도움을 준다. Flight와 함께 사용할 수 있는 여러 APM 도구가 있다.
- <span class="badge bg-info">beta</span>[flightphp/flight-apm](/awesome-plugins/apm) - Flight APM은 Flight 애플리케이션을 모니터링하기 위해 사용할 수 있는 간단한 APM 라이브러리이다. 애플리케이션의 성능을 모니터링하고 병목 현상을 식별하는 데 도움을 줄 수 있다.

## 인증/권한 부여

인증과 권한 부여는 어떤 애플리케이션에서 접근할 수 있는 것을 제어해야 하는 필수 요소이다.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight 권한 라이브러리. 이 라이브러리는 애플리케이션에 사용자 및 애플리케이션 수준의 권한을 추가하는 간단한 방법이다.

## 캐싱

캐싱은 애플리케이션의 속도를 높이는 훌륭한 방법이다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있다.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 경량, 간단하고 독립적인 PHP 인파일 캐싱 클래스.

## CLI

CLI 애플리케이션은 애플리케이션과 상호작용하는 훌륭한 방법이다. 이를 사용하여 컨트롤러를 생성하고, 모든 경로를 표시하고, 기타 작업을 수행할 수 있다.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션이다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 훌륭한 방법이다. 사용자 기본 설정, 애플리케이션 설정 등을 저장하는 데 사용할 수 있다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie는 쿠키를 관리하는 간단하고 효과적인 방법을 제공하는 PHP 라이브러리이다.

## 디버깅

디버깅은 로컬 환경에서 개발할 때 필수적이다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 다기능 오류 처리기이다. 애플리케이션을 디버깅하는 데 도움을 줄 수 있는 여러 패널이 있다. 또한 쉽게 확장하고 자신만의 패널을 추가할 수 있다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되며, Flight 프로젝트를 위해 디버깅에 도움이 되는 몇 가지 추가 패널을 추가한다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심이다. 이는 데이터를 저장하고 검색하는 방법이다. 일부 데이터베이스 라이브러리는 쿼리를 작성하기 위한 래퍼일 뿐이고, 일부는 완전한 ORM이다.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 핵심의 일부인 공식 Flight PDO 래퍼. 이는 쿼리를 작성하고 실행하는 과정을 간소화하는 간단한 래퍼이다. ORM이 아니다.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/매퍼. 데이터베이스에서 데이터를 쉽게 검색하고 저장하는 데 유용한 작은 라이브러리이다.
- [byjg/php-migration](/awesome-plugins/migrations) - 프로젝트의 모든 데이터베이스 변경 사항을 추적하는 플러그인이다.

## 암호화

암호화는 민감한 데이터를 저장하는 애플리케이션에서 필수적이다. 데이터를 암호화하고 복호화하는 것은 그렇게 어렵지 않지만, 암호화 키를 적절하게 저장하는 것은 [어렵다](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [어려운](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [부분이](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) 있다. 가장 중요한 것은 암호화 키를 공용 디렉토리에 저장하거나 코드 저장소에 커밋하지 않는 것이다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 복호화하는 데 사용할 수 있는 라이브러리이다. 데이터의 암호화 및 복호화를 시작하기 위해 설정하는 것은 매우 간단하다.

## 작업 큐

작업 큐는 비동기적으로 작업을 처리하는 데 매우 유용하다. 이는 이메일을 보내거나, 이미지를 처리하거나, 실시간으로 수행할 필요가 없는 작업을 처리하는 데 사용할 수 있다.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue는 작업을 비동기적으로 처리하는 데 사용할 수 있는 라이브러리이다. beanstalkd, MySQL/MariaDB, SQLite, PostgreSQL과 함께 사용할 수 있다.

## 세션

세션은 API에는 그다지 유용하지 않지만, 웹 애플리케이션을 구축하는 데 있어 상태 및 로그인 정보를 유지하는 데 필수적일 수 있다.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 공식 Flight 세션 라이브러리. 이는 세션 데이터를 저장하고 검색하는 데 사용할 수 있는 간단한 세션 라이브러리이다. PHP의 내장 세션 처리를 사용한다.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 세션 관리자(비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/복호화를 위해 PHP open_ssl을 사용한다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심이다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있다.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - 이는 핵심의 일부분인 매우 기본적인 템플릿 엔진이다. 프로젝트에 페이지가 몇 개 이상 있다면 사용하지 않는 것이 좋다.
- [latte/latte](/awesome-plugins/latte) - Latte는 사용하기 매우 쉬운 전체 기능을 갖춘 템플릿 엔진으로, Twig 또는 Smarty보다 PHP 문법에 더 가깝다. 또한 확장하기 쉽고 자신만의 필터와 함수를 추가할 수 있다.

## 기여

공유하고 싶은 플러그인이 있으신가요? 목록에 추가하기 위해 풀 리퀘스트를 제출하세요!