# 멋진 플러그인

Flight은 믿을 수 없을 만큼 확장성이 뛰어나다. Flight 응용 프로그램에 기능을 추가할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에서 공식적으로 지원하고 있고, 다른 일부는 시작하는 데 도움이 되는 미니 라이브러리입니다.

## 캐싱

캐싱은 응용 프로그램을 가속화하는 좋은 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 가벼우며 간단하며 독립적인 PHP 파일 캐싱 클래스

## CLI

CLI 응용 프로그램은 응용 프로그램과 상호 작용하는 훌륭한 방법입니다. 컨트롤러를 생성하거나 모든 경로를 표시하는 데 사용할 수 있습니다.

- [flightphp/runway](/awesome-plugins/runway) - Runway는 Flight 응용 프로그램을 관리하는 데 도움이 되는 CLI 응용 프로그램입니다.

## 쿠키

쿠키는 클라이언트 측에 작은 데이터 조각을 저장하는 좋은 방법입니다. 사용자 기본 설정, 응용 프로그램 설정 등을 저장하는 데 사용할 수 있습니다.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie는 쿠키를 간단하고 효과적으로 관리하는 PHP 라이브러리입니다.

## 디버깅

로컬 환경에서 개발하는 경우 디버깅이 중요합니다. 디버깅 경험을 향상시킬 수 있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 기능이 풍부한 에러 핸들러입니다. 응용 프로그램을 디버깅하는 데 도움이 되는 여러 패널이 있습니다. 또한 매우 쉽게 확장하고 새로운 패널을 추가할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 에러 핸들러와 함께 사용되는 이 플러그인은 Flight 프로젝트의 디버깅을 돕기 위해 몇 가지 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 응용 프로그램의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하고 실행하기 위한 래퍼일 뿐이며, 일부는 완벽한 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 핵심 부분인 공식 Flight PDO Wrapper입니다. 이것은 쿼리를 작성하고 실행하는 프로세스를 단순화하기 위한 간단한 래퍼입니다. ORM은 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - Flight 공식 ActiveRecord ORM/Mapper입니다. 데이터를 쉽게 검색하고 저장할 수 있는 훌륭한 라이브러리입니다.

## 암호화

민감한 데이터를 저장하는 모든 응용 프로그램에는 암호화가 중요합니다. 데이터를 암호화하고 해독하는 것은 어렵지 않지만, 암호화 키를 올바르게 저장하는 것은 어려울 수 있습니다. 암호화 키를 공개 디렉토리에 저장하거나 코드 리포지토리에 커밋하지 않는 것이 가장 중요합니다.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 데이터를 암호화하고 해독하는 데 사용할 수 있는 라이브러리입니다. 데이터를 암호화하고 해독을 시작하는 것은 매우 간단합니다.

## 세션

세션은 API에 대해서는 그리 유용하지 않지만, 웹 응용 프로그램을 개발하는 데 있어서 상태와 로그인 정보를 유지하는 데 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). 세션 데이터의 암호화/해독을 위해 PHP open_ssl을 사용합니다.

## 템플릿

UI가 있는 모든 웹 응용 프로그램에는 템플릿이 필요합니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 핵심 부분인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 몇 페이지 이상이 있는 경우 사용을 권장하지는 않습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 매우 사용하기 쉬우며 PHP 구문에 더 가깝게 느껴지는 풀 기능의 템플릿 엔진입니다. 또한 매우 쉽게 확장하여 사용자 정의 필터와 기능을 추가할 수 있습니다.

## 기여

공유하고 싶은 플러그인이 있나요? 목록에 추가하기 위해 풀 리퀘스트를 제출하세요!