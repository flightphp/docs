# 설치

### 1. 파일 다운로드

[Composer](https://getcomposer.org)를 사용하고 있다면, 다음 명령어를 실행할 수 있습니다:

```bash
composer require flightphp/core
```

또는 [다운로드](https://github.com/flightphp/core/archive/master.zip)하여 웹 디렉토리에 직접 추출할 수 있습니다.

### 2. 웹서버 구성

*Apache*의 경우, 다음과 함께 `.htaccess` 파일을 편집하세요:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **참고**: 하위 디렉토리에서 flight를 사용해야 한다면, `RewriteEngine On` 바로 뒤에
> `RewriteBase /subdir/`를 추가하세요.
> **참고**: db 또는 env 파일과 같은 모든 서버 파일을 보호하려면
> 이 내용을 `.htaccess` 파일에 넣으세요:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

*Nginx*의 경우, 서버 선언에 다음을 추가하세요:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. `index.php` 파일 생성

먼저 프레임워크를 포함하세요.

```php
require 'flight/Flight.php';
```

Composer를 사용하고 있다면, 대신 자동 로더를 실행하세요.

```php
require 'vendor/autoload.php';
```

그런 다음 경로를 정의하고 요청을 처리할 함수를 할당하세요.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

마지막으로 프레임워크를 시작하세요.

```php
Flight::start();
```