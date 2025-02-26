# flightphp/cache

가볍고 간단하며 독립적인 PHP 인파일 캐싱 클래스

**장점**
- 가볍고 독립적이며 간단함
- 모든 코드가 하나의 파일에 있음 - 불필요한 드라이버 없음.
- 안전함 - 생성된 모든 캐시 파일은 die가 포함된 php 헤더를 가지고 있어, 경로를 알고 서버가 제대로 설정되지 않았더라도 직접 접근할 수 없음
- 잘 문서화되고 테스트됨
- flock을 통해 동시성을 제대로 처리함
- PHP 7.4+ 지원
- MIT 라이센스 하에 무료

이 문서 사이트는 각 페이지를 캐시하기 위해 이 라이브러리를 사용하고 있습니다!

코드를 보려면 [여기](https://github.com/flightphp/cache)를 클릭하세요.

## 설치

composer를 통해 설치:

```bash
composer require flightphp/cache
```

## 사용법

사용법은 매우 간단합니다. 이것은 캐시 디렉토리에 캐시 파일을 저장합니다.

```php
use flight\Cache;

$app = Flight::app();

// 캐시가 저장될 디렉토리를 생성자에 전달합니다
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// 이는 캐시가 프로덕션 모드에 있을 때만 사용되도록 보장합니다
	// ENVIRONMENT는 부트스트랩 파일이나 앱의 다른 곳에서 설정된 상수입니다
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

그런 다음 코드를 다음과 같이 사용할 수 있습니다:

```php

// 캐시 인스턴스를 가져옵니다
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 캐시될 데이터를 반환함
}, 10); // 10 초

// 또는
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 초
}
```

## 문서

전체 문서를 보려면 [https://github.com/flightphp/cache](https://github.com/flightphp/cache)를 방문하고 [예제](https://github.com/flightphp/cache/tree/master/examples) 폴더도 확인하세요.