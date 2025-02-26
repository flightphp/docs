# 멋진 플러그인들

Flight는 매우 확장성이 뛰어납니다. Flight 애플리케이션에 기능을 추가하기 위해 사용할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에 의해 공식적으로 지원되며, 일부는 시작하는 데 도움을 주기 위한 마이크로/라이트 라이브러리입니다.

## API 문서

API 문서는 모든 API에 매우 중요합니다. 개발자가 API와 상호작용하는 방법과 기대할 수 있는 것을 이해하는 데 도움이 됩니다. Flight 프로젝트를 위한 API 문서를 생성하는 데 도움이 되는 몇 가지 도구가 있습니다.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - FlightPHP와 함께 OpenAPI Generator를 사용하여 API 문서를 생성하는 방법에 대한 Daniel Schreiber의 블로그 게시물.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI는 Flight 프로젝트를 위한 API 문서를 생성하는 데 도움이 되는 훌륭한 도구입니다. 사용이 매우 쉽고 필요에 맞게 사용자 정의할 수 있습니다. Swagger 문서를 생성하는 데 도움을 주는 PHP 라이브러리입니다.

## 인증/권한 부여

인증 및 권한 부여는 누가 무엇에 접근할 수 있을지 제어해야 하는 모든 애플리케이션에 매우 중요합니다.

- [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight 권한 라이브러리. 이 라이브러리는 애플리케이션에 사용자 및 애플리케이션 수준의 권한을 추가하는 간단한 방법입니다.

## 캐싱

캐싱은 애플리케이션을 빠르게 하는 훌륭한 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- [flightphp/cache](/awesome-plugins/php-file-cache) - 가벼운, 간단하고 독립적인 PHP 인파일 캐싱 클래스

## CLI

CLI 애플리케이션은 애플리케이션과 상호작용하는 훌륭한 방법입니다. 이를 사용하여 컨트롤러를 생성하고, 모든 경로를 표시하며, 그 이상을 수행할 수 있습니다.

- [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터를 저장하는 tuyệtgreat한 방법입니다. 이를 사용하여 사용자 선호도, 애플리케이션 설정 등을 저장할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP 쿠키는 쿠키를 관리하는 간단하고 효과적인 방법을 제공하는 PHP 라이브러리입니다.

## 디버깅

디버깅은 로컬 환경에서 개발할 때 매우 중요합니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 풀 기능의 오류 처리기입니다. 애플리케이션을 디버그하는 데 도움이 되는 여러 패널이 있습니다. 또한 매우 쉽게 확장하고 자신의 패널을 추가할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되며, Flight 프로젝트에 특히 유용한 디버깅을 위한 몇 가지 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 이는 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 쿼리를 작성하는 래퍼일 뿐이며, 일부는 완전한 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 코어의 일부인 공식 Flight PDO 래퍼입니다. 이는 쿼리를 작성하고 실행하는 과정을 단순화하는 데 도움이 되는 간단한 래퍼입니다. ORM이 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/매퍼입니다. 데이터베이스에서 데이터를 쉽게 검색하고 저장하는 데 유용한 작은 라이브러리입니다.
- [byjg/php-migration](/awesome-plugins/migrations) - 프로젝트의 모든 데이터베이스 변경 사항을 추적하는 플러그인입니다.

## 암호화

암호화는 민감한 데이터를 저장하는 모든 애플리케이션에 매우 중요합니다. 데이터를 암호화하고 복호화하는 것은 그리 어렵지 않지만, 암호화 키를 적절하게 저장하는 것은 [어렵](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [을](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [수도](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key) 있습니다. 가장 중요한 것은 암호화 키를 공개 디렉터리에 저장하거나 코드 저장소에 커밋하지 않는 것입니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 복호화하는 데 사용할 수 있는 라이브러리입니다. 데이터를 암호화하고 복호화하는 데 필요한 초기 설정은 꽤 간단합니다.

## 세션

세션은 API에는 실제로 유용하지 않지만 웹 애플리케이션을 구축하는 데는 상태 및 로그인 정보를 유지하는 데 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자 (논블로킹, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/복호화에 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 코어의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 페이지가 2개 이상인 경우 사용하지 않는 것이 좋습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 사용하기 매우 쉬운 완전한 기능의 템플릿 엔진으로, Twig나 Smarty보다 PHP 구문에 더 가깝게 느껴집니다. 또한 자신의 필터와 함수를 쉽게 추가하고 확장할 수 있습니다.

## 기여하기

공유하고 싶은 플러그인이 있나요? 목록에 추가하기 위해 풀 리퀘스트를 제출해주세요!