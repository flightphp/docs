# 쿠키

[overclokk/cookie](https://github.com/overclokk/cookie)은 앱 내에서 쿠키를 관리하는 간단한 라이브러리입니다.

## 설치

컴포저를 사용하여 설치가 간단합니다.

```bash
composer require overclokk/cookie
```

## 사용

사용법은 Flight 클래스에 새로운 메소드를 등록하는 것만큼 간단합니다.

```php
use Overclokk\Cookie\Cookie;

/*
 * 부트스트랩 또는 public/index.php 파일에서 설정
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// 쿠키 설정

		// 새로운 인스턴스를 받으려면 false로 설정해야 합니다
		// 자동완성을 위해 아래 주석을 사용하려면
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // 쿠키의 이름
			'1', // 설정할 값
			86400, // 쿠키가 유지될 시간(초)
			'/', // 쿠키가 유효한 경로
			'example.com', // 쿠키가 유효한 도메인
			true, // 안전한 HTTPS 연결로만 쿠키를 전송
			true // HTTP 프로토콜을 통해서만 쿠키에 접근 가능
		);

		// 선택적으로, 기본값을 유지하고 오랫동안 쿠키를 설정하려는 경우
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// 쿠키가 있는지 확인
		if (Flight::cookie()->has('stay_logged_in')) {
			// 예를 들어 사용자를 대시보드 영역에 넣습니다.
			Flight::redirect('/dashboard');
		}
	}
}