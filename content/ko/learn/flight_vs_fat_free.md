# Flight vs Fat-Free

## 펫-프리란 무엇인가요?
[펫-프리](https://fatfreeframework.com) (애칭으로 **F3**로도 불립니다)은 빠르게 동적이고 탄탄한 웹 애플리케이션을 구축하는 데 도움이 되는 강력하면서 쉽게 사용할 수 있는 PHP 마이크로 프레임워크입니다.

플라이트는 펫-프리와 여러 면에서 비교되며 기능과 간단함 측면에서 아마 가장 가까운 친척일 것입니다. 펫-프리에는 플라이트에 없는 기능들이 많이 있지만, 플라이트에는 없는 기능들도 많습니다. 펫-프리는 나이가 드러나기 시작하고 한때 그랬던 만큼 인기가 떨어졌습니다.

업데이트가 점차 적어지고 커뮤니티도 한때처럼 활발하지는 않습니다. 코드는 충분히 간단하지만, 때로는 문법의 엄격성 부족으로 읽고 이해하기 어려울 수 있습니다. PHP 8.3에서 작동하지만 코드 자체는 여전히 PHP 5.3에서 작성된 것처럼 보입니다.

## Flight와 비교한 장점들

- 펫-프리에는 GitHub에서 더 많은 별이 달렸습니다.
- 펫-프리에는 일부 명확하지 않은 영역들을 제외하고 어느 정도 괜찮은 문서가 있습니다.
- 펫-프리에는 프레임워크를 배울 수 있는 YouTube 튜토리얼과 온라인 기사 같은 여러 리소스들이 있습니다.
- 펫-프리에는 때때로 유용한 [일부 도움이 되는 플러그인들](https://fatfreeframework.com/3.8/api-reference)이 내장되어 있습니다.
- 펫-프리에는 데이터베이스와 상호 작용할 수 있는 Mapper라는 내장 ORM이 있습니다. 플라이트에는 [액티브 레코드](/awesome-plugins/active-record)가 있습니다.
- 펫-프리에는 세션, 캐싱 및 지역화가 내장되어 있습니다. 플라이트는 제3자 라이브러리를 사용해야 하지만, 이는 [문서](/awesome-plugins)에서 다룹니다.
- 펫-프리에는 프레임워크를 확장할 수 있는 일부 [커뮤니티 생성 플러그인](https://fatfreeframework.com/3.8/development#Community)이 있습니다. Flight는 [문서](/awesome-plugins)와 [예제](/examples) 페이지에 명시되어 있습니다.
- 펫-프리와 마찬가지로 플라이트는 의존성이 없습니다.
- 펫-프리와 마찬가지로 플라이트는 개발자가 애플리케이션을 제어하고 간단한 개발 경험을 제공하는 데 초점을 맞춘 것입니다.
- 펫-프리는 업데이트가 [좀 더 적어진다](https://github.com/bcosca/fatfree/releases)는 걸 빼고 플라이트와 마찬가지로 하위 호환성을 유지하고 있습니다.
- 펫-프리와 마찬가지로 플라이트는 프레임워크의 세계로 처음 발을 딛는 개발자들을 위한 것입니다.
- 펫-프리에는 플라이트의 템플릿 엔진보다 강력한 내장된 템플릿 엔진이 있습니다. 플라이트는 이를 수행하기 위해 [Latte](/awesome-plugins/latte)를 권장합니다.
- 펫-프리에는 CLI 유형의 "루트" 명령어가 있어 펫-프리 자체에서 CLI 앱을 구축하고 이를 `GET` 요청과 유사하게 다룰 수 있습니다. 플라이트는 이를 [runway](/awesome-plugins/runway)로 수행합니다.

## Flight와 비교한 단점들

- 펫-프리에는 몇 가지 구현 테스트가 있으며 매우 기본적인 자체 [테스트](https://fatfreeframework.com/3.8/test) 클래스가 있습니다. 그러나, 플라이트만큼 100%의 단위 테스트가 되지는 않습니다.
- 실제로 문서 사이트를 검색하려면 Google과 같은 검색 엔진을 사용해야 합니다.
- 플라이트에는 문서 사이트에 다크 모드가 있습니다. (마이크 드랍)
- 펫-프리에는 유지보수가 소홀한 모듈들이 있습니다.
- 플라이트에는 펫-프리의 내장 `DB\SQL` 클래스보다 조금 더 간단한 [PdoWrapper](/awesome-plugins/pdo-wrapper)가 있습니다.
- 플라이트에는 애플리케이션을 보호하는 데 사용할 수 있는 [권한 플러그인](/awesome-plugins/permissions)이 있습니다. Slim은 제3자 라이브러리를 사용해야 합니다.
- 플라이트에는 펫-프리의 Mapper보다 ORM으로서 더 느껴지는 [액티브 레코드](/awesome-plugins/active-record)가 있습니다. `active-record`의 추가적인 이점은 레코드 간의 관계를 정의하여 자동 조인을 수행할 수 있다는 점인데, 펫-프리의 Mapper는 [SQL 뷰를 생성](https://fatfreeframework.com/3.8/databases#ProsandCons)해야 합니다.
- 놀랍게도, 펫-프리에는 루트 네임스페이스가 없습니다. 플라이트는 코드 충돌을 방지하기 위해 완전히 네임스페이스가 지정되어 있습니다. 여기서 `Cache` 클래스가 가장 큰 문제입니다.
- 펫-프리에는 미들웨어가 없습니다. 대신, 컨트롤러에서 요청과 응답을 필터링하는 데 사용되는 `beforeroute`와 `afterroute` 후크가 있습니다.
- 펫-프리는 라우트를 그룹화할 수 없습니다.
- 펫-프리에는 의존성 주입 컨테이너 핸들러가 있지만, 사용 방법에 대한 문서가 매우 부족합니다.
- 디버깅은 ['HIVE'](https://fatfreeframework.com/3.8/quick-reference)라고 불리는 곳에 거의 모든 것이 저장되기 때문에 약간 까다로울 수 있습니다.