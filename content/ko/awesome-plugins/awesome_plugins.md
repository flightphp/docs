# 멋진 플러그인

Flight는 놀랍도록 확장 가능합니다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에서 공식적으로 지원하고 있고, 다른 것은 시작하기를 도와주는 마이크로/라이트 라이브러리입니다.

## 인증/권한

인증 및 권한은 누가 무엇에 액세스할 수 있는지 제어해야 하는 모든 애플리케이션에 중요합니다.

- [flightphp/permissions](/awesome-plugins/permissions) - 공식 Flight 권한 라이브러리. 이 라이브러리는 사용자 및 애플리케이션 수준의 권한을 애플리케이션에 추가하는 간단한 방법입니다.

## 캐싱

캐싱은 애플리케이션을 가속화하는 훌륭한 방법입니다. Flight와 함께 사용할 수 있는 여러 개의 캐싱 라이브러리가 있습니다.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 가벼우며 간단하며 독립적인 PHP 파일 캐싱 클래스

## CLI

CLI 애플리케이션은 애플리케이션과 상호 작용하는 훌륭한 방법입니다. 컨트롤러를 생성하거나 모든 라우트를 표시하는 데 사용할 수 있습니다.

- [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 애플리케이션을 관리하는 데 도움이 되는 CLI 애플리케이션입니다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 훌륭한 방법입니다. 사용자 환경 설정, 애플리케이션 설정 등을 저장하는 데 사용할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie는 쿠키를 관리하는 간단하고 효과적인 방법을 제공하는 PHP 라이브러리입니다.

## 디버깅

로컬 환경에서 개발할 때 디버깅은 중요합니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 완전한 기능의 에러 핸들러입니다. 응용 프로그램을 디버깅하는 데 도움이 되는 여러 패널이 있습니다. 또한 사용자 정의 패널을 쉽게 추가하고 확장할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 에러 핸들러와 함께 사용되며, Flight 프로젝트에 특히 디버깅을 돕기 위해 몇 가지 추가 패널을 추가하는 플러그인입니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하고 실행하기 위한 래퍼이고, 일부는 완전한 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Flight 공식 PDO 래퍼인 핵심 구성요소입니다. 쿼리 작성 및 실행 과정을 단순화하는 간단한 래퍼입니다. ORM은 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/Mapper. 데이터를 쉽게 검색하고 저장할 수 있는 훌륭한 라이브러리입니다.

## 암호화

민감한 데이터를 저장하는 애플리케이션에 암호화는 중요합니다. 데이터를 암호화하고 해독하는 것은 어렵지 않지만, 암호화 키를 올바르게 저장하는 것은 어렵습니다. 암호화 키를 공개 디렉토리에 저장하거나 코드 저장소에 커밋하지 않도록 주의해야 합니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 해독하는 데 사용할 수 있는 라이브러리입니다. 데이터를 암호화하고 해독하기 시작하는 것은 꽤 간단합니다.

## 세션

세션은 API에는 실제로 유용하지 않지만, 웹 애플리케이션을 구축할 때 상태와 로그인 정보를 유지하는 데 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터를 암호화/해독하기 위해 PHP open_ssl을 사용합니다.

## 템플릿화

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 핵심의 매우 기본적인 템플릿 엔진입니다. 프로젝트에 몇 페이지 이상이 있는 경우는 권장되지 않습니다.
- [latte/latte](/awesome-plugins/latte) - PHP 구문에 더 가까운 사용하기 쉬운 완전한 기능의 템플릿 엔진입니다. Twig나 Smarty보다 확장하고 사용자 정의 필터 및 함수를 추가하기도 매우 쉽습니다.

## 기여

공유하고 싶은 플러그인이 있으신가요? 목록에 추가하려면 풀 리퀘스트를 제출하세요!