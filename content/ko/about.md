# Flight이란 무엇인가요?

Flight은 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 매우 다재다능하며 어떠한 종류의 웹 애플리케이션도 구축하는 데 사용할 수 있습니다. 간결함을 염두에 두고 작성되어 있어 이해와 사용이 쉽습니다.

Flight은 PHP에 익숙해지고 웹 애플리케이션을 구축하는 방법을 배우고 싶은 초심자에게 좋은 프레임워크입니다. 빠르고 쉽게 웹 애플리케이션을 구축하고 싶은 경험丰심한 개발자들에게도 좋은 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 고안되었습니다.

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

간단하죠? [Flight에 대해 더 알아보기!](learn)

## 빠른 시작
Flight Framework로 시작할 수 있는 예제 앱이 있습니다. 시작하는 방법에 대한 지침은 [flightphp/skeleton](https://github.com/flightphp/skeleton)을 방문해주세요! 또한 Flight로 할 수 있는 몇 가지 작업에 대한 영감을 얻을 수 있는 [examples](examples) 페이지도 방문할 수 있습니다.

# 커뮤니티

우리는 Matrix에 있습니다! [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)로 채팅해주세요.

# 기여

Flight에 기여할 수 있는 두 가지 방법이 있습니다:

1. [코어 저장소](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여할 수 있습니다.
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅되어 있습니다. 오류를 발견하거나 더 나은 내용을 보충하고 싶다면 수정하여 풀 요청을 제출해주세요! 업데이트와 언어 번역은 환영합니다.

# 요구 사항

Flight은 PHP 7.4 이상을 필요로 합니다.

**참고:** 현재 기록 시점(2024년)에서 PHP 7.4는 일부 LTS Linux 배포판의 기본 버전이기 때문에 PHP 7.4를 지원합니다. PHP 8보다 높은 버전으로 이동하게 되면 해당 사용자들에게 많은 어려움을 야기할 것입니다. 프레임워크는 또한 PHP 8을 지원합니다.

# 라이선스

Flight은 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스에 따라 출시됩니다.