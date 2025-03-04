# 멋진 플러그인들

Flight는 믿을 수 없을 만큼 확장 가능합니다. Flight 애플리케이션에 기능을 추가할 수 있는 여러 플러그인이 있습니다. 일부는 Flight 팀에서 공식적으로 지원하고 있고, 다른 것들은 시작하기를 도와주는 마이크로/라이트 라이브러리입니다.

## 캐싱

캐싱은 애플리케이션을 빠르게 만드는 좋은 방법입니다. Flight와 함께 사용할 수 있는 여러 캐싱 라이브러리가 있습니다.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 가벼우며 간단하며 독립적인 PHP in-file 캐싱 클래스

## 디버깅

로컬 환경에서 개발할 때 디버깅은 중요합니다. 몇 가지 플러그인을 이용하여 디버깅 경험을 높일 수 있습니다.

- [tracy/tracy](/awesome-plugins/tracy) - 이는 Flight와 함께 사용할 수 있는 완전한 기능을 갖춘 에러 핸들러입니다. 여러 패널을 제공하여 애플리케이션 디버깅을 도와줍니다. 또한 매우 쉽게 확장하고 자체 패널을 추가할 수 있습니다.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - [Tracy](/awesome-plugins/tracy) 에러 핸들러와 함께 사용되며, 이 플러그인은 Flight 프로젝트를 위해 디버깅을 돕는 몇 가지 추가 패널을 제공합니다.

## 데이터베이스

데이터베이스는 대부분의 애플리케이션의 핵심입니다. 데이터를 저장하고 검색하는 방법입니다. 일부 데이터베이스 라이브러리는 단순히 쿼리를 작성하고 실행하는 래퍼일 뿐이며, 일부는 전체 ORM입니다.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 핵심 부분인 공식 Flight PDO 래퍼입니다. 이것은 쿼리 작성 및 실행 프로세스를 단순화하기 위한 간단한 래퍼입니다. ORM이 아닙니다.
- [flightphp/active-record](/awesome-plugins/active-record) - 공식 Flight ActiveRecord ORM/Mapper입니다. 데이터를 쉽게 검색하고 저장할 수 있는 훌륭한 작은 라이브러리입니다.

## 세션

세션은 API에는 그리 유용하지 않지만, 웹 애플리케이션을 구축할 때, 상태 및 로그인 정보를 유지하는 데 필수적일 수 있습니다.

- [Ghostff/Session](/awesome-plugins/session) - PHP 세션 관리자 (비차단, 플래시, 세그먼트, 세션 암호화). PHP open_ssl을 사용하여 세션 데이터의 선택적 암호화/복호화를 수행합니다.

## 템플릿

템플릿은 UI가 있는 모든 웹 애플리케이션의 핵심입니다. Flight와 함께 사용할 수 있는 여러 템플릿 엔진이 있습니다.

- [flightphp/core View](/learn#views) - 핵심의 매우 기본적인 템플릿 엔진입니다. 프로젝트에 여러 페이지가 있는 경우 권장되지 않습니다.
- [latte/latte](/awesome-plugins/latte) - Latte는 매우 쉽게 사용할 수 있으며 PHP 구문에 더 가까운 템플릿 엔진입니다. Twig나 Smarty보다 확장하고 자체 필터와 함수를 추가하기도 매우 쉽습니다.
  
## 기여

공유하고 싶은 플러그인이 있나요? 이것을 목록에 추가하려면 풀 리퀘스트를 제출하세요!