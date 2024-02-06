# Wruczek/PHP-File-Cache

가벼운 PHP 파일 캐싱 클래스

**장점** 
- 가벼우며 독립적이고 간단
- 모든 코드가 하나의 파일에 있습니다 - 쓸모없는 드라이버 없음.
- 안전 - 생성된 모든 캐시 파일은 PHP 헤더와 die가 있는 상태로 보호되어 직접 액세스가 불가능합니다. 누군가가 경로를 알고 서버가 제대로 구성되지 않아도 직접 액세스 할 수 없습니다.
- 잘 문서화되어 있고 테스트되었습니다
- flock를 통해 동시성을 올바르게 처리
- PHP 5.4.0 - 7.1+ 지원
- MIT 라이선스 하에 무료

## 설치

컴포저를 통해 설치:

```bash
composer require wruczek/php-file-cache
```

## 사용법

사용법은 매우 간단합니다.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// 캐시가 저장될 디렉토리를 생성자에 전달합니다
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// 이것은 캐시가 프로덕션 모드일 때에만 사용되도록 보장합니다
	// ENVIRONMENT는 부트스트랩 파일이나 앱 다른 곳에서 설정되는 상수입니다
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

그럼 이제 이렇게 코드에서 사용할 수 있습니다:

```php

// 캐시 인스턴스 얻기
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // 캐시할 데이터 반환
}, 10); // 10 초

// 또는
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 초
}
```

## 문서

자세한 문서를 보려면 [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)를 방문하고 [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) 폴더를 확인하세요.