# 플라이트란 무엇인가요?

플라이트는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 매우 다재다능하며 모든 종류의 웹 애플리케이션 구축에 사용할 수 있습니다. 단순성을 염두에 두고 작성되어 이해하고 사용하기 쉬운 방식으로 만들어졌습니다.

플라이트는 PHP에 익숙하지 않은 초보자들이 웹 애플리케이션을 구축하는 방법을 배우고 싶어하는 경우에 좋은 초보자용 프레임워크입니다. 빠르고 쉽게 웹 애플리케이션을 구축하고 싶은 숙련된 개발자들에게도 좋은 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 공학적으로 설계되었습니다.

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::start();
```

충분히 쉽죠? [플라이트에 대해 더 알아보기!](learn)

## 빠른 시작
플라이트 프레임워크로 시작할 수 있는 예제 앱이 있습니다. 시작하는 방법에 대한 지침은 [flightphp/skeleton](https://github.com/flightphp/skeleton)을 방문하세요! 또한 [examples](examples) 페이지에서 플라이트로 수행할 수 있는 일부 내용에 영감을 받을 수 있습니다.

# 커뮤니티

우리는 매트릭스에 있습니다! [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)에서 채팅하세요.

# 기여

플라이트에 기여할 수 있는 두 가지 방법이 있습니다:

1. [코어 저장소](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여할 수 있습니다.
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에서 호스팅됩니다. 오류를 발견하거나 더 나은 내용을 개선하고 싶으면 자유롭게 수정하고 풀 리퀘스트를 제출해주세요! 우리는 업데이트와 언어 번역을 환영합니다.

# 요구 사항

플라이트는 PHP 7.4 이상을 필요로 합니다.

**참고:** PHP 7.4는 현재 작성 시(2024년) 일부 LTS Linux 배포판의 기본 버전이기 때문에 지원됩니다. PHP >8로의 이동은 해당 사용자들에게 많은 골칫거리를 야기할 것입니다. 또한 이 프레임워크는 PHP >8도 지원합니다.

# 라이선스

플라이트는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스 하에 출시됩니다.