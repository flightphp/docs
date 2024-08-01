# 플라이트가 무엇인가요?

플라이트는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 매우 다재다능하며 모든 종류의 웹 애플리케이션을 구축하는 데 사용할 수 있습니다. 이는 간소함을 염두에 두고 작성되어 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

플라이트는 PHP에 익숙하지 않은 초보자들을 위한 훌륭한 프레임워크이며 웹 애플리케이션을 구축하는 방법을 배우고 싶어하는 사람들에게 적합합니다. 또한 웹 애플리케이션에 대한 더 많은 제어를 원하는 경험 많은 개발자들에게 좋은 프레임워크입니다. 이는 RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 설계되었습니다.

## 빠른 시작

```php
<?php

// 만약 컴포저로 설치한 경우
require 'vendor/autoload.php';
// 수동으로 zip 파일로 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '안녕, 세상!';
});

Flight::route('/json', function() {
  Flight::json(['안녕' => '세상']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

간단하죠? [문서에서 플라이트에 대해 더 알아보기!](learn)

### 스켈레톤/보일러플레이트 앱

플라이트 프레임워크로 시작하는 데 도움이 되는 예제 앱이 있습니다. 시작 방법에 대한 지침은 [flightphp/skeleton](https://github.com/flightphp/skeleton)에서 확인할 수 있습니다! 또한 플라이트로 수행할 수 있는 일부 작업에 대한 영감을 얻을 수 있는 [예제](examples) 페이지를 방문할 수도 있습니다.

# 커뮤니티

[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)에서 Matrix 채팅을 통해 교류할 수 있습니다.

# 기여

플라이트에 기여하는 두 가지 방법이 있습니다:

1. [코어 레포지토리](https://github.com/flightphp/core)를 방문하여 핵심 프레임워크에 기여할 수 있습니다.
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅되어 있습니다. 오류를 발견하거나 더 나은 내용을 작성하고 싶다면 자유롭게 수정하여 풀 리퀘스트를 제출해 주세요! 우리는 업데이트와 언어 번역을 환영합니다.

# 요구 사항

플라이트는 PHP 7.4 이상을 필요로 합니다.

**참고:** PHP 7.4는 현재 작성 시점(2024년)에 일부 LTS Linux 배포판에서 기본 버전으로 지원되기 때문에 지원됩니다. PHP >8로 강제 이전하면 이러한 사용자들에게 많은 어려움을 야기할 수 있습니다. 프레임워크는 또한 PHP >8을 지원합니다.

# 라이센스

플라이트는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이센스에 따라 출시됩니다.