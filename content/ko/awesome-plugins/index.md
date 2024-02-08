# 멋진 플러그인들

Flight는 믿을 수 없을 만큼 확장 가능합니다. Flight 애플리케이션에 기능을 추가하는 데 사용할 수있는 여러 플러그인이 있습니다. 일부는 FlightPHP 팀에서 공식적으로 지원하며 다른 것들은 시작하는 데 도움이 되는 micro/lite 라이브러리입니다.

## 캐싱

캐싱은 애플리케이션의 속도를 높이는 좋은 방법입니다. Flight와 함께 사용할 수있는 여러 캐싱 라이브러리가 있습니다.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 가벼우며 간단한 독립형 PHP 파일 캐싱 클래스

## 디버깅

로컬 환경에서 개발할 때 디버깅은 중요합니다. 디버깅 경험을 높일 수있는 몇 가지 플러그인이 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - Flight와 함께 사용할 수있는 완전 기능을 갖춘 오류 처리기입니다. 응용 프로그램을 디버깅하는 데 도움이되는 여러 패널이 있습니다. 또한 매우 쉽게 확장하고 자체 패널을 추가 할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 오류 처리기와 함께 사용되는이 플러그인은 Flight 프로젝트의 디버깅에 특히 유용한 몇 가지 추가 패널을 추가합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 이것은 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하고 쓰기 위한 래퍼이며 일부는 완전한 ORMs입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Flight의 공식 PDO 래퍼는 코어의 일부입니다. 이것은 쿼리를 작성하고 실행하는 프로세스를 단순화하는 데 도움이되는 간단한 래퍼입니다. ORM은 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식적인 Flight Active Record ORM/Mapper입니다. 데이터를 쉽게 검색하고 저장하는 작은 라이브러리입니다.

## 세션

세션은 API에는 실제로 유용하지 않지만 웹 애플리케이션을 구축하는 데는 상태 및 로그인 정보를 유지하는 데 중요할 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자 (블로킹되지 않음, 플래시, 세그먼트, 세션 암호화). 옵션으로 세션 데이터의 암호화/복호화에 PHP open_ssl을 사용합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 이것은 코어의 일부인 매우 기본적인 템플릿 엔진입니다. 프로젝트에 몇 페이지 이상이 있다면 사용을 권장하지 않습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 매우 사용하기 쉽고 Twig 또는 Smarty보다 PHP 구문에 더 가까운 완전 기능의 템플릿 엔진입니다. 또한 매우 쉽게 확장하고 필터와 함수를 추가 할 수 있습니다.

## 기여

공유하고 싶은 플러그인이 있으십니까? 목록에 추가하려면 풀 리퀘스트를 제출하십시오!