# Flight이란 무엇인가요?

Flight은 PHP를 위한 빠르고 간편하며 확장 가능한 프레임워크입니다. Flight은 매우 다재다능하며 어떠한 종류의 웹 애플리케이션 구축에도 사용할 수 있습니다. 간결함을 염두에 두고 작성되어 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

PHP에 익숙하지 않은 초보자들이 웹 애플리케이션을 구축하는 방법을 배우고 싶어하는 경우 Flight은 좋은 초보자용 프레임워크입니다. 또한 경험이丰해진 개발자들이 웹 애플리케이션에 대해 더 많은 제어를 원하는 경우에도 좋은 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 설계되었습니다.

## 빠른 시작

```php
<?php

// composer로 설치한 경우
require 'vendor/autoload.php';
// 또는 zip 파일을 수동으로 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::start();
```

쉽죠? 자세한 내용은 [Flight 문서에서 알아보세요!](learn)

### 스켈레톤/보일러플레이트 앱

Flight 프레임워크로 시작하는 데 도움이 되는 예제 앱이 있습니다. 시작 방법에 대한 지침은 [flightphp/skeleton](https://github.com/flightphp/skeleton)을 참조하세요! 또한 Flight로 할 수 있는 일에 대한 영감을 얻을 수 있는 [예제](examples) 페이지도 방문할 수 있습니다.

# 커뮤니티

우리는 Matrix에 있습니다! [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)에서 채팅해보세요.

# 기여

Flight에 기여할 수 있는 두 가지 방법이 있습니다:

1. [코어 저장소](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여할 수 있습니다.
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅되어 있습니다. 오류를 발견하거나 더 나은 내용을 작성하고 싶은 경우 자유롭게 수정하여 풀 리퀘스트를 제출해주세요! 우리는 업데이트와 언어 번역을 환영합니다.

# 요구 사항

Flight은 PHP 7.4 이상을 필요로합니다.

**참고:** PHP 7.4가 일부 LTS Linux 배포판의 기본 버전인 2024년 작성 시점에서 PHP 7.4가 지원됩니다. PHP >8로 변경하는 것은 사용자들에게 많은 불편을 줄 수 있습니다. 이 프레임워크는 또한 PHP >8을 지원합니다.

# 라이선스

Flight은 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스로 배포됩니다.