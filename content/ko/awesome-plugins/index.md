# 멋진 플러그인들

Flight은 굉장히 확장 가능합니다. Flight 애플리케이션에 기능을 추가할 수 있는 여러 플러그인이 있습니다. 일부는 FlightPHP 팀에 의해 공식 지원되며, 다른 일부는 시작하는 데 도움이 되는 마이크로/라이트 라이브러리입니다.

## 캐싱

캐싱은 애플리케이션 속도를 높이는 좋은 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 가벼우며 간단한 PHP 인 파일 캐싱 클래스

## 디버깅

로컬 환경에서 개발할 때 디버깅은 중요합니다. 디버깅 경험을 향상시켜주는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수 있는 완전한 기능의 오류 처리기. 응용 프로그램을 디버깅하는 데 도움이 되는 여러 패널이 있습니다. 또한 매우 쉽게 확장하고 자체 패널을 추가할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되며, 이 플러그인은 Flight 프로젝트의 디버깅에 특히 도움이 되는 여러 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하고 실행하는 래퍼이며, 일부는 완전한 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 핵심 부분인 공식 Flight PDO 래퍼. 이것은 단순한 래퍼로 쿼리 작성과 실행 과정을 간소화하는 데 도움이 됩니다. ORM이 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight Active Record ORM/Mapper. 데이터를 쉽게 검색하고 저장할 수 있는 훌륭한 작은 라이브러리입니다.

## 세션

세션은 API에는 그렇게 유용하지 않지만, 웹 애플리케이션을 구축할 때 상태와 로그인 정보를 유지하는 데 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 매니저 (논블로킹, 플래시, 세그먼트, 세션 암호화). 선택적으로 세션 데이터를 암호화/복호화하기 위해 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 핵심 부분인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 몇 페이지 이상이 있는 경우 사용하지 않는 것이 좋습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 매우 사용하기 쉽고 PHP 구문에 더 가깝게 느껴지는 완전한 기능의 템플릿 엔진입니다. 또한 매우 쉽게 확장하고 자체 필터와 함수를 추가할 수 있습니다.

## 기여

공유하고 싶은 플러그인이 있나요? 목록에 추가하기 위해 풀 리퀘스트를 제출해주세요!