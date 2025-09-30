# Flight vs Fat-Free

## Fat-Free란 무엇인가?
[Fat-Free](https://fatfreeframework.com) (애정 어린 별칭으로 **F3**로 알려짐)는 동적이고 강력한 웹 애플리케이션을 빠르게 구축하는 데 도움을 주기 위해 설계된 강력하면서도 사용하기 쉬운 PHP 마이크로 프레임워크입니다!

Flight는 기능과 간단함 측면에서 Fat-Free와 많은 면에서 비교되며, 아마도 가장 가까운 사촌일 것입니다. Fat-Free에는 Flight에 없는 많은 기능이 있지만, Flight에도 있는 많은 기능이 있습니다. Fat-Free는 나이를 드러내기 시작했으며, 예전만큼 인기가 없습니다.

업데이트가 점점 덜 빈번해지고 있으며, 커뮤니티도 예전만큼 활발하지 않습니다. 코드는 충분히 간단하지만, 때때로 구문 규율의 부족으로 인해 읽고 이해하기 어려울 수 있습니다. PHP 8.3에서 작동하지만, 코드 자체는 여전히 PHP 5.3에 살고 있는 것처럼 보입니다.

## Flight와 비교한 장점

- Fat-Free는 GitHub에서 Flight보다 약간 더 많은 별점을 가지고 있습니다.
- Fat-Free는 괜찮은 문서를 가지고 있지만, 일부 영역에서 명확성이 부족합니다.
- Fat-Free는 프레임워크를 배우는 데 사용할 수 있는 YouTube 튜토리얼과 온라인 기사 같은 희귀한 리소스를 가지고 있습니다.
- Fat-Free는 때때로 도움이 되는 [일부 유용한 플러그인](https://fatfreeframework.com/3.8/api-reference)을 내장하고 있습니다.
- Fat-Free는 데이터베이스와 상호 작용하는 데 사용할 수 있는 Mapper라는 내장 ORM을 가지고 있습니다. Flight에는 [active-record](/awesome-plugins/active-record)가 있습니다.
- Fat-Free는 세션, 캐싱 및 지역화를 내장하고 있습니다. Flight는 타사 라이브러리를 사용해야 하지만, [문서](/awesome-plugins)에서 다루어집니다.
- Fat-Free는 프레임워크를 확장하는 데 사용할 수 있는 [커뮤니티 제작 플러그인](https://fatfreeframework.com/3.8/development#Community)의 작은 그룹을 가지고 있습니다. Flight는 [문서](/awesome-plugins)와 [예제](/examples) 페이지에서 일부를 다루고 있습니다.
- Fat-Free는 Flight처럼 의존성이 없습니다.
- Fat-Free는 Flight처럼 개발자에게 애플리케이션에 대한 제어를 주고 간단한 개발자 경험을 제공하는 방향으로 설계되었습니다.
- Fat-Free는 Flight처럼 이전 호환성을 유지합니다 (부분적으로 업데이트가 [덜 빈번해지는](https://github.com/bcosca/fatfree/releases) 이유 때문입니다).
- Fat-Free는 Flight처럼 프레임워크 세계로 처음 진입하는 개발자를 위한 것입니다.
- Fat-Free는 Flight의 템플릿 엔진보다 더 강력한 내장 템플릿 엔진을 가지고 있습니다. Flight는 이를 위해 [Latte](/awesome-plugins/latte)를 권장합니다.
- Fat-Free는 독특한 CLI 유형 "route" 명령어를 가지고 있어서, Fat-Free 자체 내에서 CLI 앱을 구축하고 `GET` 요청처럼 취급할 수 있습니다. Flight는 [runway](/awesome-plugins/runway)로 이를 구현합니다.

## Flight와 비교한 단점

- Fat-Free는 일부 구현 테스트를 가지고 있으며, 매우 기본적인 자체 [test](https://fatfreeframework.com/3.8/test) 클래스를 가지고 있습니다. 그러나 Flight처럼 100% 단위 테스트가 되지 않았습니다. 
- 문서 사이트를 실제로 검색하려면 Google 같은 검색 엔진을 사용해야 합니다.
- Flight는 문서 사이트에 다크 모드를 가지고 있습니다. (마이크 드롭)
- Fat-Free는 유지보수가 심각하게 부족한 일부 모듈을 가지고 있습니다.
- Flight는 Fat-Free의 내장 `DB\SQL` 클래스보다 약간 더 간단한 [PdoWrapper](/learn/pdo-wrapper)를 가지고 있습니다.
- Flight는 애플리케이션을 보호하는 데 사용할 수 있는 [permissions 플러그인](/awesome-plugins/permissions)을 가지고 있습니다. Fat-Free는 타사 라이브러리를 사용해야 합니다.
- Flight는 [active-record](/awesome-plugins/active-record)라는 ORM을 가지고 있어서, Fat-Free의 Mapper보다 ORM처럼 느껴집니다.
  `active-record`의 추가 이점은 레코드 간 관계를 정의하여 자동 조인을 할 수 있다는 점이며, Fat-Free의 Mapper는 [SQL 뷰](https://fatfreeframework.com/3.8/databases#ProsandCons)를 생성해야 합니다.
- 놀랍게도 Fat-Free는 루트 네임스페이스가 없습니다. Flight는 자신의 코드와 충돌하지 않도록 끝까지 네임스페이싱되어 있습니다.
  `Cache` 클래스가 여기서 가장 큰 범죄자입니다.
- Fat-Free는 미들웨어가 없습니다. 대신 컨트롤러에서 요청과 응답을 필터링하는 데 사용할 수 있는 `beforeroute`와 `afterroute` 훅이 있습니다.
- Fat-Free는 라우트를 그룹화할 수 없습니다.
- Fat-Free는 의존성 주입 컨테이너 핸들러를 가지고 있지만, 사용 방법에 대한 문서가 매우 희박합니다.
- 디버깅은 기본적으로 모든 것이 [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)라고 불리는 곳에 저장되어 있어서 약간 까다로울 수 있습니다.