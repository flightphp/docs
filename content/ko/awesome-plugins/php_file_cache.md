# Wruczek/PHP-File-Cache

가벼우며 간단하고 독립형 PHP 파일 캐싱 클래스

**장점**
- 가벼우며 독립형이며 간단합니다.
- 모든 코드가 한 파일에 있으며, 무의미한 드라이버가 없습니다.
- 안전합니다 - 생성된 모든 캐시 파일에는 php 헤더가 있어 직접 액세스를 차단하므로 누군가가 경로를 알아도 서버가 제대로 구성되지 않았다면 액세스할 수 없습니다.
- 잘 문서화되어 있으며 테스트되었습니다.
- flock를 통해 동시성을 올바르게 처리합니다.
- PHP 5.4.0 - 7.1+을 지원합니다.
- MIT 라이선스에 따라 무료로 제공됩니다.

## 설치

컴포저를 통해 설치하세요:

```bash
composer require wruczek/php-file-cache
```

## 사용법

사용법은 꽤 간단합니다.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// 캐시가 저장될 디렉토리를 생성자에 전달합니다.
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// 이렇게 하면 캐시가 프로덕션 모드일 때만 사용됨을 보장합니다.
	// ENVIRONMENT는 부트스트랩 파일이나 앱의 다른 곳에서 설정된 상수입니다.
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

그런 다음 코드에서 다음과 같이 사용할 수 있습니다:

```php

// 캐시 인스턴스 가져오기
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 캐시할 데이터 반환
}, 10); // 10초

// 또는
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10초
}
```

## 문서

전체 문서를 보려면 [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)를 방문하고 [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) 폴더를 확인하세요.