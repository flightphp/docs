# Wruczek/PHP-File-Cache

가벼우면서 간단하며 독립적인 PHP 인 파일 캐싱 클래스

**장점**
- 가벼우면서 독립적이며 간단함
- 모든 코드가 하나의 파일에 있음 - 무의미한 드라이버가 없음.
- 보안 - 생성된 모든 캐시 파일에는 php 헤더가 포함되어 있어 직접 액세스가 불가능함 (누군가 경로를 알고 있더라도 서버가 제대로 구성되어 있지 않으면)
- 잘 문서화되어 있으며 테스트됨
- flock를 통해 동시성을 올바르게 처리함
- PHP 5.4.0 - 7.1+를 지원함
- MIT 라이선스 하에 무료로 제공됨

코드를 보려면 [here](https://github.com/Wruczek/PHP-File-Cache) 클릭하세요.

## 설치

컴포저를 통해 설치하십시오:

```bash
composer require wruczek/php-file-cache
```

## 사용법

사용법은 매우 간단합니다.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// 캐시가 저장될 디렉토리를 생성자로 전달합니다
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// 이렇게 함으로써 캐시는 프로덕션 모드에서만 사용됨을 보장합니다
	// ENVIRONMENT는 부트스트랩 파일이나 앱의 다른 곳에서 설정된 상수입니다.
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

그런 다음 다음과 같이 코드에서 사용할 수 있습니다:

```php

// 캐시 인스턴스 가져오기
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 캐시될 데이터 반환
}, 10); // 10초

// 또는
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10초
}
```

## 문서

자세한 문서를 보려면 [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)을 방문하고 [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) 폴더를 확인해주세요.