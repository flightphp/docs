# Flight란 무엇인가요?

Flight는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다. 매우 다목적으로 사용될 수 있으며, 모든 종류의 웹 애플리케이션을 구축하는 데 사용될 수 있습니다. 간단함을 염두에 두고 제작되었으며, 이해하고 사용하기 쉬운 방식으로 작성되었습니다.

Flight는 PHP에 처음 접하는 분들에게 훌륭한 초보자 프레임워크입니다. 웹 애플리케이션 구축 방법을 배우고자 하는 경우에 좋습니다. 또한 웹 애플리케이션에 대한 더 많은 컨트롤을 원하는 경험이 있는 개발자에게도 훌륭한 프레임워크입니다. RESTful API, 간단한 웹 애플리케이션 또는 복잡한 웹 애플리케이션을 쉽게 구축할 수 있도록 설계되었습니다.

## 빠른 시작

먼저 Composer로 설치하세요

```bash
composer require flightphp/core
```

또는 [여기](https://github.com/flightphp/core)에서 zip 파일을 다운로드할 수 있습니다. 그러면 다음과 같은 기본 `index.php` 파일을 가질 수 있습니다:

```php
<?php

// composer로 설치한 경우
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

그게 전부입니다! 기본 Flight 애플리케이션이 생성되었습니다. 이제 `php -S localhost:8000`로 이 파일을 실행하고 브라우저에서 `http://localhost:8000`에 방문하여 출력을 확인할 수 있습니다.

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

## 빠른가요?

네! Flight는 빠릅니다. 현재 제공되는 가장 빠른 PHP 프레임워크 중 하나입니다. 모든 벤치마크를 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)에서 확인할 수 있습니다.

아래 벤치마크는 일부 다른 인기 있는 PHP 프레임워크와 비교한 것입니다.

| 프레임워크 | 일반 요청/초 | JSON 요청/초 |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## 스켈레톤/보일러플레이트 애플리케이션

Flight 프레임워크로 시작하는 데 도움이 되는 예제 애플리케이션이 있습니다. [flightphp/skeleton](https://github.com/flightphp/skeleton)에서 시작하는 방법에 대한 지침을 확인하세요! 또한 [예제](examples) 페이지를 방문하여 Flight로 할 수 있는 일에 대한 영감을 얻을 수 있습니다.

# 커뮤니티

우리는 Matrix Chat에 있습니다.

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

그리고 Discord에도 있습니다.

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 기여하기

Flight에 기여하는 방법은 두 가지가 있습니다:

1. [코어 리포지토리](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여할 수 있습니다. 
1. 문서에 기여할 수 있습니다. 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅되고 있습니다. 오류를 발견하거나 더 나은 내용을 보완하고 싶다면 언제든지 수정하고 풀 리퀘스트를 제출하세요! 우리는 사안을 잘 관리하려고 노력하지만 업데이트 및 언어 번역은 언제든지 환영합니다.

# 요구 사항

Flight는 PHP 7.4 이상이 필요합니다.

**참고:** PHP 7.4는 현재 작성 시점(2024)의 많은 LTS 리눅스 배포판에서 기본 버전이기 때문에 지원되고 있습니다. PHP >8로 강제로 전환하는 것은 그런 사용자들에게 많은 불편을 초래할 것입니다. 이 프레임워크는 또한 PHP >8도 지원합니다.

# 라이센스

Flight는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이센스 하에 릴리스됩니다.