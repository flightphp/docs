# Flight vs Laravel

## Laravel이란?
[Laravel](https://laravel.com)은 모든 종소리와 장식을 갖춘 풀 기능 프레임워크로, 놀라운 개발자 중심 생태계를 가지고 있지만, 성능과 복잡성 측면에서 비용이 듭니다. Laravel의 목표는 개발자가 최고 수준의 생산성을 가지도록 하고, 일반적인 작업을 쉽게 만드는 것입니다. Laravel은 풀 기능의 엔터프라이즈 웹 애플리케이션을 구축하려는 개발자에게 훌륭한 선택입니다. 이는 성능과 복잡성 측면에서 일부 trade-off를 동반합니다. Laravel의 기초를 배우는 것은 쉽지만, 프레임워크에 숙달되는 데는 시간이 걸릴 수 있습니다.

또한 Laravel 모듈이 너무 많아서 개발자들은 문제를 해결하는 유일한 방법이 이러한 모듈을 통하는 것처럼 느껴지지만, 실제로는 다른 라이브러리를 사용하거나 직접 코드를 작성할 수 있습니다.

## Flight와 비교한 장점

- Laravel은 일반적인 문제를 해결하는 데 사용할 수 있는 개발자와 모듈의 **거대한 생태계**를 가지고 있습니다.
- Laravel은 데이터베이스와 상호 작용하는 데 사용할 수 있는 풀 기능 ORM을 가지고 있습니다.
- Laravel은 프레임워크를 배우는 데 사용할 수 있는 _엄청난_ 양의 문서와 튜토리얼을 가지고 있습니다. 이는 세부 사항을 파고드는 데 좋을 수 있지만, 지나치게 많아서 나쁠 수도 있습니다.
- Laravel은 애플리케이션을 보호하는 데 사용할 수 있는 내장 인증 시스템을 가지고 있습니다.
- Laravel은 프레임워크를 배우는 데 사용할 수 있는 팟캐스트, 컨퍼런스, 미팅, 비디오 및 기타 리소스를 가지고 있습니다.
- Laravel은 풀 기능의 엔터프라이즈 웹 애플리케이션을 구축하려는 숙련된 개발자를 대상으로 합니다.

## Flight와 비교한 단점

- Laravel은 Flight보다 후드 아래에서 훨씬 더 많은 일이 일어나며, 이는 성능 측면에서 **극적인** 비용을 초래합니다. 자세한 내용은 [TechEmpower 벤치마크](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3)를 참조하세요.
- Flight는 가볍고 빠르며 사용하기 쉬운 웹 애플리케이션을 구축하려는 개발자를 대상으로 합니다.
- Flight는 단순성과 사용 용이성을 목표로 합니다.
- Flight의 핵심 기능 중 하나는 이전 버전 호환성을 최대한 유지하려 한다는 것입니다. Laravel은 주요 버전 간에 [많은 좌절](https://www.google.com/search?q=laravel+breaking+changes+major+version+complaints&sca_esv=6862a9c407df8d4e&sca_upv=1&ei=t72pZvDeI4ivptQP1qPMwQY&ved=0ahUKEwiwlurYuNCHAxWIl4kEHdYRM2gQ4dUDCBA&uact=5&oq=laravel+breaking+changes+major+version+complaints&gs_lp=Egxnd3Mtd2l6LXNlcnAiMWxhcmF2ZWwgYnJlYWtpbmcgY2hhbmdlcyBtYWpvciB2ZXJzaW9uIGNvbXBsYWludHMyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEdIjAJQAFgAcAF4AZABAJgBAKABAKoBALgBA8gBAJgCAaACB5gDAIgGAZAGCJIHATGgBwA&sclient=gws-wiz-serp)를 초래합니다.
- Flight는 프레임워크의 세계에 처음 발을 들이는 개발자를 위한 것입니다.
- Flight는 의존성이 없지만, [Laravel은 끔찍할 정도로 많은 의존성](https://github.com/laravel/framework/blob/12.x/composer.json)을 가지고 있습니다.
- Flight도 엔터프라이즈 수준 애플리케이션을 할 수 있지만, Laravel만큼 보일러플레이트 코드가 많지 않습니다.
  개발자가 조직화하고 잘 구조화된 상태를 유지하기 위해 더 많은 규율이 필요할 것입니다.
- Flight는 개발자에게 애플리케이션에 대한 더 많은 제어를 제공하지만, Laravel은 장면 뒤에 많은 마법이 있어서 좌절스러울 수 있습니다.