# Flight이란 무엇인가?

Flight은 빠르고, 간단하며, 확장 가능한 PHP 프레임워크로, 빠르게 일을 처리하고 싶어하는 개발자를 위해 만들어졌습니다. 복잡한 부분 없이 클래식 웹 앱, 빠른 API, 또는 최신 AI 기반 도구 실험을 할 때, Flight의 낮은 자원 사용량과 직관적인 설계가 완벽하게 맞습니다.

## Flight을 왜 선택하나요?

- **초보자 친화적:** Flight은 새로운 PHP 개발자를 위한 훌륭한 출발점입니다. 명확한 구조와 간단한 구문을 통해 불필요한 코드에 빠지지 않고 웹 개발을 배울 수 있습니다.
- **전문가들이 사랑함:** 경험이 풍부한 개발자들은 Flight의 유연성과 제어성을 좋아합니다. 작은 프로토타입에서 완전한 기능을 갖춘 앱으로 확장할 수 있어 프레임워크를 바꿀 필요가 없습니다.
- **AI 친화적:** Flight의 최소한의 오버헤드와 깨끗한 아키텍처는 AI 도구와 API를 통합하기에 이상적입니다. 스마트 채팅봇, AI 기반 대시보드 제작, 또는 실험을 할 때, Flight은 방해가 되지 않고 본질에 집중할 수 있게 합니다. [AI와 Flight 사용에 대해 자세히 알아보기](/learn/ai)

## 빠른 시작

먼저, Composer로 설치하세요:

```bash
composer require flightphp/core
```

또는 저장소 ZIP 파일을 [여기](https://github.com/flightphp/core)에서 다운로드하세요. 그런 다음 기본 `index.php` 파일은 다음과 같습니다:

```php
<?php

// 컴포저로 설치한 경우
require 'vendor/autoload.php';
// 또는 ZIP 파일로 수동 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

이게 전부입니다! 기본 Flight 애플리케이션이 완성되었습니다. 이제 `php -S localhost:8000` 명령으로 파일을 실행하고 브라우저에서 `http://localhost:8000`을 방문하여 출력을 확인할 수 있습니다.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">그렇게 간단하죠?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Flight에 대해 문서에서 더 알아보세요!</a>
      <br>
      <a href="/learn/ai" class="btn btn-primary mt-3">Flight이 AI를 쉽게 만드는 방법을 발견하세요</a>
    </div>
  </div>
</div>

## 빠른가요?

물론입니다! Flight은 가장 빠른 PHP 프레임워크 중 하나입니다. 가벼운 코어 덕분에 오버헤드가 적고 속도가 빠르며, 전통적인 앱과 현대적인 AI 기반 프로젝트에 적합합니다. 모든 벤치마크는 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)에서 확인할 수 있습니다.

아래는 다른 인기 PHP 프레임워크와의 벤치마크입니다.

| 프레임워크 | 일반 텍스트 요청/초 | JSON 요청/초 |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## 스켈레톤/보일러플레이트 앱

Flight 시작을 돕는 예제 앱이 있습니다. 준비된 프로젝트는 [flightphp/skeleton](https://github.com/flightphp/skeleton)을 확인하세요, 또는 [예제](examples) 페이지를 참고하세요. AI를 어떻게 적용할지 궁금하시면? [AI 기반 예제 탐색하기](/learn/ai).

# 커뮤니티

우리는 Matrix 채팅에 있습니다

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

그리고 Discord에도 있습니다

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 기여

Flight에 기여하는 두 가지 방법이 있습니다:

1. 코어 프레임워크에 기여하려면 [코어 저장소](https://github.com/flightphp/core)를 방문하세요.
2. 문서를 개선하세요! 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅됩니다. 오류를 발견하거나 개선할 점이 있으면 풀 리퀘스트를 제출하세요. 우리는 업데이트와 새로운 아이디어, 특히 AI와 신기술 관련 아이디어를 좋아합니다!

# 요구사항

Flight은 PHP 7.4 이상을 필요로 합니다.

**노트:** PHP 7.4는 작성 시점(2024년)에서 일부 LTS Linux 배포판의 기본 버전이기 때문에 지원됩니다. PHP >8로 강제 이동하면 사용자들에게 불편을 초래할 수 있으므로, 프레임워크는 PHP >8도 지원합니다.

# 라이선스

Flight은 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스 under로 배포됩니다.