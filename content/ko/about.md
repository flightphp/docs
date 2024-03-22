# 비행이란 무엇인가?

비행은 PHP용 빠르고 간단하며 확장 가능한 프레임워크입니다. 매우 다양하며 어떤 종류의 웹 애플리케이션을 구축하는 데 사용할 수 있습니다. 이는 간결함을 염두에 두고 작성되어 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

비행은 PHP에 익숙하지 않고 웹 애플리케이션을 구축하는 방법을 배우려는 초보자들에게 좋은 프레임워크입니다. 웹 애플리케이션에 대해 더 많은 제어를 원하는 경험 많은 개발자들에게도 좋은 프레임워크입니다. 이것은 RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축하기 위해 만들어졌습니다.

## 빠른 시작

```php
<?php

// 만약 컴포저로 설치한 경우
require 'vendor/autoload.php';
// 수동으로 zip 파일로 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '안녕, 세계!';
});

Flight::route('/json', function() {
  Flight::json(['안녕' => '세계']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube 비디오 플레이어" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

단순하죠? [Flight에 대해 더 알아보세요!](learn)

### 스켈레톤/보일러플레이트 앱

비행 프레임워크로 시작할 수 있는 예제 앱이 있습니다. 시작하는 방법에 대한 지침은 [flightphp/skeleton](https://github.com/flightphp/skeleton)에서 확인할 수 있습니다! 또한 [examples](examples) 페이지를 방문하여 Flight로 수행할 수 있는 몇 가지 기능에 대한 영감을 얻을 수 있습니다.

# 커뮤니티

우리는 Matrix에 있습니다! [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)에서 채팅해보세요.

# 기여

Flight에 기여할 수 있는 두 가지 방법이 있습니다:

1. [핵심 저장소](https://github.com/flightphp/core)를 방문하여 핵심 프레임워크에 기여할 수 있습니다.
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅되어 있습니다. 오류를 발견하거나 더 나은 내용을 구체화하고 싶다면 자유롭게 수정하여 풀 리퀘스트를 제출해주세요! 우리는 최신 정보를 유지하려 노력하지만 업데이트 및 언어 번역은 환영합니다.

# 요구 사항

Flight는 PHP 7.4 이상을 필요로 합니다.

**참고:** PHP 7.4는 현재 작성 시점(2024년)에 일부 LTS Linux 배포판의 기본 버전이기 때문에 지원됩니다. PHP >8로 이동을 강제하면 해당 사용자들에게 많은 불편을 끼칠 것입니다. 이 프레임워크는 또한 PHP >8을 지원합니다.

# 라이선스

비행은 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스에 따라 공개됩니다.