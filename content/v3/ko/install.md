# 설치

## 파일 다운로드

만약 [Composer](https://getcomposer.org)를 사용하고 있다면, 다음 명령어를 실행할 수 있습니다:

```bash
composer require flightphp/core
```

또는 파일을 직접 [다운로드](https://github.com/flightphp/core/archive/master.zip)하여 웹 디렉토리에 압축을 푸십시오.

## 웹서버 구성

### Apache
Apache를 위해, 다음과 같이 `.htaccess` 파일을 편집하십시오:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **참고**: flight를 하위 디렉토리에서 사용해야 한다면, `RewriteEngine On` 다음에 줄을 추가하세요.
> `RewriteBase /subdir/`

> **참고**: db 또는 환경 파일과 같은 모든 서버 파일을 보호해야 한다면, 아래 내용을 `.htaccess` 파일에 넣으십시오:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Nginx를 위해, 다음을 서버 선언에 추가하십시오:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## `index.php` 파일 생성

```php
<?php

// 만약 Composer를 사용 중이라면, 오토로더를 요구하세요.
require 'vendor/autoload.php';
// 만약 Composer를 사용 중이 아니라면, 프레임워크를 직접 로드하세요
// require 'flight/Flight.php';

// 그런 다음 경로를 정의하고 요청을 처리할 함수를 할당하세요.
Flight::route('/', function () {
  echo 'hello world!';
});

// 마지막으로, 프레임워크를 시작하세요.
Flight::start();
```