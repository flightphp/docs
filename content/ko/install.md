# 설치

## 파일 다운로드

만약 [Composer](https://getcomposer.org)를 사용하고 있다면, 아래 명령어를 실행할 수 있습니다:

```bash
composer require flightphp/core
```

또는 [파일을 다운로드](https://github.com/flightphp/core/archive/master.zip)하여 직접 웹 디렉토리에 압축을 푸십시오.

## 웹서버 구성

### Apache

Apache를 위해, 다음 내용을 포함한 `.htaccess` 파일을 수정하십시오:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **참고**: flight를 서브디렉토리에서 사용해야 하는 경우, `RewriteEngine On` 바로 뒤에 `RewriteBase /subdir/` 라인을 추가하십시오.

> **참고**: db 또는 env 파일과 같은 모든 서버 파일을 보호해야 하는 경우, 아래 내용을 `.htaccess` 파일에 추가하십시오:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Nginx를 위해, 다음 내용을 서버 선언에 추가하십시오:

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

// 만약 Composer를 사용하고 있다면, 오토로더를 요구하십시오.
require 'vendor/autoload.php';
// 만약 Composer를 사용하고 있지 않다면, 프레임워크를 직접로드하십시오
// require 'flight/Flight.php';

// 그런 다음 라우트를 정의하고 요청을 처리하기 위한 함수를 할당하십시오.
Flight::route('/', function () {
  echo 'hello world!';
});

// 마지막으로, 프레임워크를 시작하십시오.
Flight::start();
```