# 멋진 플러그인

Flight는 믿을 수 없을 정도로 확장 가능합니다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에서 공식적으로 지원하며, 다른 일부는 시작하는 데 도움을 주기 위한 마이크로/라이트 라이브러리입니다.

## API 문서

API 문서는 모든 API에 매우 중요합니다. 이는 개발자가 API와 상호작용하는 방법과 반환되는 것을 이해하는 데 도움을 줍니다. Flight 프로젝트에 대한 API 문서를 생성하는 데 도움이 되는 몇 가지 도구가 있습니다.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - FlightPHP와 함께 OpenAPI 사양을 사용하여 API를 구축하는 방법에 대해 Daniel Schreiber가 쓴 블로그 게시물입니다.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI는 Flight 프로젝트에 대한 API 문서를 생성하는 데 유용한 도구입니다. 사용하기 매우 간단하며 필요에 맞게 사용자 정의할 수 있습니다. Swagger 문서를 생성하는 데 도움이 되는 PHP 라이브러리입니다.

## 인증/권한 부여

인증 및 권한 부여는 접근할 수 있는 것을 제어해야 하는 모든 애플리케이션에 매우 중요합니다.

- [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight 권한 라이브러리입니다. 이 라이브러리는 애플리케이션에 사용자 및 애플리케이션 수준의 권한을 추가하는 간단한 방법입니다.

## 캐싱

캐싱은 애플리케이션을 빠르게 만드는 훌륭한 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- [flightphp/cache](/awesome-plugins/php-file-cache) - 가볍고 간단하며 독립적인 PHP 인파일 캐싱 클래스입니다.

## CLI

CLI 애플리케이션은 애플리케이션과 상호작용하는 훌륭한 방법입니다. 이를 사용하여 컨트롤러를 생성하고, 모든 경로를 표시하는 등 여러 작업을 수행할 수 있습니다.

- [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 훌륭한 방법입니다. 이를 사용하여 사용자 기본 설정, 애플리케이션 설정 등을 저장할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP 쿠키는 쿠키를 관리하는 간단하고 효과적인 방법을 제공하는 PHP 라이브러리입니다.

## 디버깅

디버깅은 로컬 환경에서 개발할 때 매우 중요합니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 기능이 완전한 오류 처리기입니다. 애플리케이션을 디버깅하는 데 도움이 되는 여러 패널을 제공합니다. 또한 매우 쉽게 확장하고 자신의 패널을 추가할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되며, Flight 프로젝트의 디버깅을 돕기 위해 몇 가지 추가 패널을 추가하는 플러그인입니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 데이터 저장 및 검색 방법입니다. 일부 데이터베이스 라이브러리는 쿼리를 작성하기 위한 단순 래퍼이며, 일부는 완전한 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 코어의 일부인 공식 Flight PDO 래퍼입니다. 쿼리를 작성하고 실행하는 과정을 단순화하는 데 도움이 되는 간단한 래퍼입니다. ORM이 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/매퍼입니다. 데이터베이스에서 데이터를 쉽게 검색하고 저장하는 데 유용한 작은 라이브러리입니다.
- [byjg/php-migration](/awesome-plugins/migrations) - 프로젝트의 모든 데이터베이스 변경 사항을 추적하는 플러그인입니다.

## 암호화

암호화는 민감한 데이터를 저장하는 모든 애플리케이션에 매우 중요합니다. 데이터를 암호화하고 복호화하는 것은 그리 어렵지 않지만, 암호화 키를 적절히 저장하는 것은 어렵습니다. 가장 중요한 것은 암호화 키를 공개 디렉토리에 저장하거나 코드 저장소에 커밋하지 않는 것입니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터 암호화 및 복호화에 사용할 수 있는 라이브러리입니다. 데이터 암호화 및 복호화를 시작하는 것은 상당히 간단합니다.

## 작업 대기열

작업 대기열은 비동기적으로 작업을 처리하는 데 매우 유용합니다. 이는 이메일 전송, 이미지 처리, 또는 실시간으로 처리할 필요 없는 모든 작업일 수 있습니다.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue는 비동기적으로 작업을 처리하는 데 사용할 수 있는 라이브러리입니다. beanstalkd, MySQL/MariaDB, SQLite 및 PostgreSQL과 함께 사용할 수 있습니다.

## 세션

세션은 API에는 그리 유용하지 않지만 웹 애플리케이션을 구축하는 데 있어 상태 및 로그인 정보를 유지하는 데 매우 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자(비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/복호화에는 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 코어의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 페이지가 두 개 이상인 경우 사용하지 않는 것이 좋습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 매우 사용하기 쉬우며 Twig나 Smarty보다 PHP 구문에 더 가깝습니다. 또한 매우 쉽게 확장하고 자신의 필터와 함수를 추가할 수 있습니다.

## 기여

공유하고 싶은 플러그인이 있습니까? 목록에 추가하려면 풀 리퀘스트를 제출하세요!