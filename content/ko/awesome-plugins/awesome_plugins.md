# 멋진 플러그인

Flight는 매우 확장 가능합니다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수 있는 여러 플러그인이 있습니다. 일부는 공식적으로 Flight 팀에서 지원하며, 다른 것들은 시작하는 데 도움이 되는 마이크로/라이트 라이브러리입니다.

## 캐싱

캐싱은 응용 프로그램을 빠르게 만드는 좋은 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 가벼우며 간단하며 독립적인 PHP 내부 파일 캐싱 클래스

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 좋은 방법입니다. 사용자 기본 설정, 응용 프로그램 설정 등을 저장하는 데 사용할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP 쿠키는 쿠키를 관리하는 간단하고 효율적인 방법을 제공하는 PHP 라이브러리입니다.

## 디버깅

로컬 환경에서 개발할 때 디버깅은 중요합니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 다양한 패널이 있는 full-featured 오류 핸들러. 응용 프로그램을 디버깅하는 데 도움이 되는 여러 패널이 있습니다. 확장하고 자체 패널을 추가하는 것도 매우 쉽습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 핸들러와 함께 사용되며, 이 플러그인은 Flight 프로젝트에 특히 디버깅을 도와주는 몇 가지 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 응용 프로그램의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하고 실행하기 위한 래퍼에 불과하며, 일부는 완전한 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 핵심의 일부인 공식 Flight PDO 래퍼. 이것은 쿼리를 작성하고 실행하는 과정을 단순화하는 데 도움이 되는 간단한 래퍼입니다. ORM은 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight 액티브 레코드 ORM/매퍼. 데이터를 쉽게 검색하고 저장할 수 있는 훌륭한 라이브러리입니다.

## 암호화

민감한 데이터를 저장하는 모든 애플리케이션에 암호화는 중요합니다. 데이터를 암호화하고 해독하는 것은 어렵지 않지만, 암호화 키를 올바르게 저장하는 것은 어려울 수 있습니다. 가장 중요한 것은 암호화 키를 공개 디렉터리에 저장하거나 코드 리포지토리에 커밋하지 않는 것입니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 해독하는 데 사용할 수 있는 라이브러리입니다. 데이터를 암호화하고 해독하기 시작하는 것은 상당히 간단합니다.

## 세션

세션은 API에는 실용적이지 않지만, 웹 애플리케이션을 개발할 때는 상태 및 로그인 정보를 유지하는 데 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자 (블로킹 없음, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 선택적 암호화/해독을 위해 PHP open_ssl을 사용합니다.

## 템플릿

UI가 있는 모든 웹 애플리케이션에는 템플릿이 근본적입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 핵심의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 몇 페이지 이상 있는 경우 권장되지 않습니다.
- [latte/latte](/awesome-plugins/latte) - 매우 쉽게 사용할 수 있는 전체 기능의 템플릿 엔진으로, Twig 또는 Smarty보다 PHP 구문에 더 가깝습니다. 자체 필터 및 함수를 쉽게 확장하고 추가할 수도 있습니다.

## 기여

공유하고 싶은 플러그인이 있으신가요? 리스트에 추가하려면 풀 리퀘스트를 제출해주세요!