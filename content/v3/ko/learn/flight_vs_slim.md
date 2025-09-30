# Flight vs Slim

## Slim이란?
[Slim](https://slimframework.com)은 PHP 마이크로 프레임워크로, 간단하면서도 강력한 웹 애플리케이션과 API를 빠르게 작성하는 데 도움을 줍니다.

Flight의 v3 기능 중 일부는 실제로 Slim에서 영감을 얻었습니다. 라우트 그룹화와 미들웨어를 특정 순서로 실행하는 기능은 Slim에서 영감을 받은 두 가지 기능입니다. Slim v3는 단순성을 목표로 출시되었지만, v4에 대해서는 
[혼합된 평가](https://github.com/slimphp/Slim/issues/2770)가 있었습니다.

## Flight와 비교한 장점

- Slim은 더 큰 개발자 커뮤니티를 가지고 있으며, 이는 개발자들이 바퀴를 재발명하지 않도록 돕는 유용한 모듈을 만듭니다.
- Slim은 PHP 커뮤니티에서 일반적인 인터페이스와 표준을 많이 따르며, 이는 상호 운용성을 높입니다.
- Slim은 프레임워크를 배우는 데 사용할 수 있는 괜찮은 문서와 튜토리얼을 가지고 있습니다 (Laravel이나 Symfony에 비할 바는 아니지만요).
- Slim은 프레임워크를 배우는 데 사용할 수 있는 YouTube 튜토리얼과 온라인 기사 같은 다양한 자원을 가지고 있습니다.
- Slim은 PSR-7 준수로 핵심 라우팅 기능을 처리하기 위해 원하는 구성 요소를 사용할 수 있게 합니다.

## Flight와 비교한 단점

- 놀랍게도, Slim은 마이크로 프레임워크로 생각하는 만큼 빠르지 않습니다. 자세한 내용은 
  [TechEmpower 벤치마크](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3)를 
  참조하세요.
- Flight는 가볍고 빠르며 사용하기 쉬운 웹 애플리케이션을 구축하려는 개발자를 대상으로 합니다.
- Flight는 의존성이 없지만, [Slim은 몇 가지 의존성](https://github.com/slimphp/Slim/blob/4.x/composer.json)을 설치해야 합니다.
- Flight는 단순성과 사용 편의성을 목표로 합니다.
- Flight의 핵심 기능 중 하나는 이전 버전과의 호환성을 최대한 유지하려 한다는 것입니다. Slim v3에서 v4로의 변경은 호환성을 깨는 변화였습니다.
- Flight는 프레임워크 세계에 처음 발을 들이는 개발자를 위한 것입니다.
- Flight는 엔터프라이즈 수준의 애플리케이션도 할 수 있지만, Slim만큼 많은 예제와 튜토리얼이 없습니다.
  개발자가 조직화되고 잘 구조화된 상태를 유지하기 위해 더 많은 규율이 필요할 것입니다.
- Flight는 개발자에게 애플리케이션에 대한 더 많은 제어를 제공하지만, Slim은 뒤에서 일부 마법을 숨길 수 있습니다.
- Flight는 데이터베이스와 상호 작용하는 데 사용할 수 있는 간단한 [PdoWrapper](/learn/pdo-wrapper)를 가지고 있습니다. Slim은 타사 라이브러리를 사용해야 합니다.
- Flight는 애플리케이션을 보호하는 데 사용할 수 있는 [permissions 플러그인](/awesome-plugins/permissions)을 가지고 있습니다. Slim은 타사 라이브러리를 사용해야 합니다.
- Flight는 데이터베이스와 상호 작용하는 데 사용할 수 있는 [active-record](/awesome-plugins/active-record)라는 ORM을 가지고 있습니다. Slim은 타사 라이브러리를 사용해야 합니다.
- Flight는 명령줄에서 애플리케이션을 실행하는 데 사용할 수 있는 [runway](/awesome-plugins/runway)라는 CLI 애플리케이션을 가지고 있습니다. Slim은 그렇지 않습니다.