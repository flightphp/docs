# Flight란 무엇인가요?

Flight는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 웹 애플리케이션을 구축하는 데 사용할 수 있는 매우 다양한 기능을 갖추고 있습니다. 단순성을 염두에 두고 설계되었으며 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

Flight는 PHP에 처음 접하는 사람들에게 웹 애플리케이션을 구축하는 방법을 배우고자 하는 좋은 초급 프레임워크입니다. 또한, 웹 애플리케이션에 대한 더 많은 제어를 원하시는 경험이 있는 개발자들에게도 훌륭한 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 설계되었습니다.

## 빠른 시작

```php
<?php

// composer로 설치된 경우
require 'vendor/autoload.php';
// zip 파일로 수동으로 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube 비디오 플레이어" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">간단하죠?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">문서에서 Flight에 대해 더 알아보세요!</a>

    </div>
  </div>
</div>

### 스켈레톤/보일러플레이트 앱

Flight 프레임워크를 시작하는 데 도움이 되는 샘플 앱이 있습니다. 시작하는 방법에 대한 지침은 [flightphp/skeleton](https://github.com/flightphp/skeleton)을 방문하세요! Flight로 할 수 있는 것에 대한 영감을 얻으려면 [예제](examples) 페이지도 방문할 수 있습니다.

# 커뮤니티

우리는 Matrix에서 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)와 함께 채팅하고 있습니다.

# 기여하기

Flight에 기여할 수 있는 방법은 두 가지가 있습니다:

1. [코어 리포지토리](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여할 수 있습니다.
2. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에서 호스팅됩니다. 오류를 발견하거나 더 나은 것을 보강하고 싶다면 자유롭게 수정하고 풀 리퀘스트를 제출하세요! 우리는 최선을 다해 유지하려고 하지만, 업데이트 및 언어 번역은 언제든지 환영합니다.

# 요구 사항

Flight는 PHP 7.4 이상이 필요합니다.

**참고:** PHP 7.4는 현재 작성 시점(2024)에서 일부 LTS 리눅스 배포판의 기본 버전이기 때문에 지원됩니다. PHP >8로 강제로 이동하면 해당 사용자들에게 많은 문제를 일으킬 수 있습니다. 프레임워크는 또한 PHP >8을 지원합니다.

# 라이센스

Flight는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이센스하에 배포됩니다.