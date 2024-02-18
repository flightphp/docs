# 보안

웹 애플리케이션에서 보안은 매우 중요합니다. 애플리케이션이 안전하고 사용자 데이터가 안전한지 확인해야 합니다. Flight은 웹 애플리케이션을 안전하게 보호하는 데 도움이 되는 여러 기능을 제공합니다.

## 헤더

HTTP 헤더는 웹 애플리케이션을 보호하는 가장 쉬운 방법 중 하나입니다. 헤더를 사용하여 클릭재킹, XSS 및 기타 공격을 방지할 수 있습니다. 이러한 헤더를 애플리케이션에 추가하는 여러 방법이 있습니다.

### 수동으로 추가

`Flight\Response` 객체의 `header` 메서드를 사용하여 이러한 헤더를 수동으로 추가할 수 있습니다.
```php
// 클릭재킹 방지를 위해 X-Frame-Options 헤더 설정
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// XSS 방지를 위해 Content-Security-Policy 헤더 설정
// 참고: 이 헤더는 매우 복잡할 수 있으므로
//  애플리케이션에 대한 인터넷 예시를 참조해야 합니다
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// XSS 방지를 위해 X-XSS-Protection 헤더 설정
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// MIME 스니핑 방지를 위해 X-Content-Type-Options 헤더 설정
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 레퍼러 정보 전송 양을 제어하기 위해 Referrer-Policy 헤더 설정
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// HTTPS 강제를 위해 Strict-Transport-Security 헤더 설정
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

이러한 헤더를 `bootstrap.php` 또는 `index.php` 파일 상단에 추가할 수 있습니다.

### 필터로 추가

다음과 같이 필터/후크에 추가할 수도 있습니다:

```php
// 필터에 헤더 추가
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### 미들웨어로 추가

미들웨어 클래스로도 추가할 수 있습니다. 코드를 깔끔하고 정리된 상태로 유지하는 좋은 방법입니다.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
...
...
...
요청이 많지만 각각을 중요하게 처리해야 합니다. 여러분이 이 모든 것을 수행하면 안전한 웹 애플리케이션을 구축하는 길에 올바르게 나아갈 것입니다.