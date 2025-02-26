# Flight란 무엇인가요?

Flight는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 매우 다재다능하며 모든 종류의 웹 애플리케이션 구축에 사용할 수 있습니다. 간결함을 염두에 두고 설계되었으며 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

Flight는 PHP에 새로운 사람들에게 훌륭한 초 보 프레임워크이며 웹 애플리케이션 구축 방법을 배우고자 하는 사람들에게 적합합니다. 또한 웹 애플리케이션에 대한 더 많은 제어를 원하시는 경험 많은 개발자에게도 훌륭한 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 및 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 설계되었습니다.

## 빠른 시작

```php
<?php

// composer로 설치한 경우
require 'vendor/autoload.php';
// 혹은 zip 파일로 수동 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '안녕하세요, 세계!';
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
      <span class="fligth-title-video">충분히 간단하죠?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">문서에서 Flight에 대해 더 알아보세요!</a>

    </div>
  </div>
</div>

### 스켈레톤/보일러플레이트 앱

Flight 프레임워크를 시작하는 데 도움이 되는 예제 앱이 있습니다. 시작 방법에 대한 안내는 [flightphp/skeleton](https://github.com/flightphp/skeleton)으로 가세요! Flight를 사용하여 할 수 있는 일들에 대한 영감을 얻으려면 [examples](examples) 페이지도 방문하세요.

# 커뮤니티

우리는 Matrix에서 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)와 채팅하고 있습니다.

# 기여하기

Flight에 기여할 수 있는 두 가지 방법이 있습니다: 

1. [코어 리포지토리](https://github.com/flightphp/core)에 방문하여 코어 프레임워크에 기여할 수 있습니다. 
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에서 호스팅됩니다. 오류를 발견하거나 더 나은 내용을 추가하고 싶다면 이를 수정하여 풀 리퀘스트를 제출해 주세요! 우리는 항상 최신 정보를 유지하려고 노력하지만, 업데이트와 언어 번역은 환영합니다.

# 요구 사항

Flight는 PHP 7.4 이상이 필요합니다.

**참고:** PHP 7.4가 지원되는 이유는 현재 작성 시점(2024)에서 PHP 7.4가 일부 LTS 리눅스 배포판의 기본 버전이기 때문입니다. PHP >8로 강제로 이동하면 해당 사용자에게 많은 불편을 초래할 것입니다. 프레임워크는 또한 PHP >8을 지원합니다.

# 라이센스

Flight는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이센스에 따라 배포됩니다.