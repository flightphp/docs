# Flight PHP Framework

Flight는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다—빨리 일을 처리하고 싶어하는 개발자들을 위해, 제로 번거로움으로 만들어졌습니다. 고전적인 웹 앱을 구축하든, 초고속 API를 만들든, 또는 최신 AI 기반 도구를 실험하든, Flight의 낮은 메모리 사용량과 직관적인 설계가 완벽한 선택입니다. Flight는 가벼움을 목표로 하지만, 엔터프라이즈 아키텍처 요구사항도 처리할 수 있습니다.

## 왜 Flight를 선택하나요?

- **초보자 친화적:** Flight는 새로운 PHP 개발자들에게 훌륭한 출발점입니다. 명확한 구조와 간단한 구문이 보일러플레이트에 헤매지 않고 웹 개발을 배우는 데 도움을 줍니다.
- **전문가들이 사랑함:** 경험이 풍부한 개발자들은 Flight의 유연성과 제어성을 사랑합니다. 작은 프로토타입에서 완전한 기능을 가진 앱으로 확장할 수 있으며, 프레임워크를 바꿀 필요가 없습니다.
- **하위 호환성:** 우리는 당신의 시간을 소중히 여깁니다. Flight v3는 v2의 확장으로, 거의 모든 API를 동일하게 유지합니다. 우리는 혁명이 아닌 진화를 믿습니다—주요 버전이 나올 때마다 "세계를 깨는" 일은 더 이상 없습니다.
- **제로 의존성:** Flight의 코어는 완전히 의존성 없이 작동합니다—폴리필 없음, 외부 패키지 없음, 심지어 PSR 인터페이스도 없습니다. 이는 공격 벡터를 줄이고, 더 작은 메모리 사용량, 그리고 업스트림 의존성으로 인한 예상치 못한 변경을 피할 수 있습니다. 선택적 플러그인은 의존성을 포함할 수 있지만, 코어는 항상 가볍고 안전하게 유지됩니다.
- **AI 중심:** Flight의 최소 오버헤드와 깔끔한 아키텍처가 AI 도구와 API 통합에 이상적입니다. 스마트 챗봇을 구축하든, AI 기반 대시보드를 만들든, 또는 실험만 하든, Flight는 방해하지 않고 중요한 일에 집중할 수 있게 합니다. [skeleton app](https://github.com/flightphp/skeleton)은 주요 AI 코딩 어시스턴트에 대한 사전 구축 지침 파일을 기본으로 제공합니다! [Flight와 AI 사용에 대해 더 알아보기](/learn/ai)

## 비디오 개요

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">충분히 간단하죠?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">문서에서 Flight에 대해 더 알아보기</a>!
    </div>
  </div>
</div>

## 빠른 시작

빠른 기본 설치의 경우 Composer로 설치하세요:

```bash
composer require flightphp/core
```

또는 저장소의 zip 파일을 [여기](https://github.com/flightphp/core)에서 다운로드할 수 있습니다. 그런 다음 다음과 같은 기본 `index.php` 파일을 가지게 됩니다:

```php
<?php

// composer로 설치한 경우
require 'vendor/autoload.php';
// 또는 zip 파일로 수동 설치한 경우
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

그게 전부입니다! 기본 Flight 애플리케이션이 있습니다. 이제 `php -S localhost:8000`으로 이 파일을 실행하고 브라우저에서 `http://localhost:8000`을 방문하여 출력을 확인할 수 있습니다.

## Skeleton/Boilerplate App

Flight로 프로젝트를 시작하는 데 도움이 되는 예제 앱이 있습니다. 구조화된 레이아웃, 기본 설정이 모두 준비되어 있고, Composer 스크립트를 바로 사용할 수 있습니다! 준비된 프로젝트를 위해 [flightphp/skeleton](https://github.com/flightphp/skeleton)을 확인하거나, 영감을 위해 [examples](examples) 페이지를 방문하세요. AI가 어떻게 맞는지 보고 싶으신가요? [AI 기반 예제 탐색](/learn/ai).

## Skeleton App 설치

충분히 쉽습니다!

```bash
# 새 프로젝트 생성
composer create-project flightphp/skeleton my-project/
# 새 프로젝트 디렉토리로 이동
cd my-project/
# 바로 시작하기 위해 로컬 개발 서버 실행!
composer start
```

프로젝트 구조를 생성하고, 필요한 파일을 설정하며, 바로 사용할 수 있게 됩니다!

## 높은 성능

Flight는 가장 빠른 PHP 프레임워크 중 하나입니다. 가벼운 코어가 오버헤드를 줄이고 속도를 높여—전통적인 앱과 현대적인 AI 기반 프로젝트 모두에 완벽합니다. 모든 벤치마크는 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks)에서 확인할 수 있습니다.

아래는 다른 인기 있는 PHP 프레임워크와의 벤치마크입니다.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
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


## Flight와 AI

AI를 어떻게 처리하는지 궁금하시나요? [발견하세요](/learn/ai) Flight가 좋아하는 코딩 LLM과 작업하기 쉽게 만드는 방법!

## 안정성과 하위 호환성

우리는 당신의 시간을 소중히 여깁니다. 몇 년마다 완전히 재발명되어 개발자들을 깨진 코드와 비용이 많이 드는 마이그레이션으로 남기는 프레임워크를 모두 본 적이 있습니다. Flight는 다릅니다. Flight v3는 v2의 확장으로 설계되었으며, 이는 당신이 알고 사랑하는 API가 제거되지 않았다는 의미입니다. 사실, 대부분의 v2 프로젝트는 v3에서 변경 없이 작동합니다. 

우리는 Flight를 안정적으로 유지하여 앱 구축에 집중할 수 있도록 하고, 프레임워크 수리에 시간을 낭비하지 않도록 합니다.

# 커뮤니티

우리는 Matrix Chat에 있습니다

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

그리고 Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 기여하기

Flight에 기여하는 두 가지 방법이 있습니다:

1. [core repository](https://github.com/flightphp/core)를 방문하여 코어 프레임워크에 기여하세요.
2. 문서를 더 좋게 만드세요! 이 문서 웹사이트는 [Github](https://github.com/flightphp/docs)에 호스팅됩니다. 오류를 발견하거나 개선하고 싶다면, 풀 리퀘스트를 제출하세요. 우리는 업데이트와 새로운 아이디어—특히 AI와 새로운 기술 주변의 것—를 사랑합니다!

# 요구사항

Flight는 PHP 7.4 이상을 요구합니다.

**참고:** PHP 7.4는 지원되는데, 작성 시점(2024)에서 일부 LTS Linux 배포판의 기본 버전이기 때문입니다. PHP >8로 강제 이동하면 해당 사용자들에게 많은 불편을 초래할 것입니다. 프레임워크는 PHP >8도 지원합니다.

# 라이선스

Flight는 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 라이선스로 배포됩니다.