# Flight란 무엇인가?

Flight는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 웹 애플리케이션을 만드는 데 사용할 수 있는 매우 다재다능한 도구입니다. 간단함을 염두에 두고 설계되었으며 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

Flight는 PHP에 익숙하지 않은 초보자들이 웹 애플리케이션을 만드는 방법을 배우기에 훌륭한 프레임워크입니다. 또한 웹 애플리케이션에 대한 더 많은 제어를 원하는 경험이 풍부한 개발자들에게도 훌륭한 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 설계되었습니다.

## 빠른 시작

```php
<?php

// composer로 설치한 경우
require 'vendor/autoload.php';
// zip 파일로 수동 설치한 경우
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
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">간단하죠?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">문서에서 Flight에 대해 더 알아보세요!</a>

    </div>
  </div>
</div>

### 스켈레톤/보일러플레이트 앱

Flight 프레임워크를 시작하는 데 도움이 되는 예제 앱이 있습니다. [flightphp/skeleton](https://github.com/flightphp/skeleton)으로 이동하여 시작하는 방법에 대한 지침을 확인하세요! 또한 [examples](examples) 페이지를 방문하여 Flight로 할 수 있는 다양한 영감을 얻을 수 있습니다.

# 커뮤니티

Matrix에서 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)로 저희와 대화하세요.

# 기여하기

Flight에 기여할 수 있는 두 가지 방법이 있습니다: 

1. [코어 리포지토리](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여할 수 있습니다. 
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에서 호스팅됩니다. 오류를 발견하거나 더 나은 내용을 추가하고 싶다면 자유롭게 수정하고 풀 리퀘스트를 제출하세요! 저희는 항상 최신 정보를 유지하려고 노력하지만, 업데이트와 언어 번역은 환영합니다.

# 요구사항

Flight는 PHP 7.4 이상을 요구합니다.

**참고:** PHP 7.4는 현재 작성 시점(2024)에서 일부 LTS 리눅스 배포판의 기본 버전이기 때문에 지원됩니다. PHP >8로 강제로 이동하는 것은 해당 사용자들에게 많은 불편을 초래할 것입니다. 이 프레임워크는 또한 PHP >8을 지원합니다.

# 라이선스

Flight는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스하에 출시됩니다.