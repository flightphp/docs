# 설치

## 파일 다운로드

시스템에 PHP가 설치되어 있는지 확인하십시오. 아니라면 시스템에 설치하는 방법에 대한 설명을 보려면 [여기](#installing-php)를 클릭하십시오.

만약 [Composer](https://getcomposer.org)를 사용 중이라면 다음 명령어를 실행할 수 있습니다:

```bash
composer require flightphp/core
```

또는 파일을 직접 [다운로드](https://github.com/flightphp/core/archive/master.zip)하여 웹 디렉토리에 압축을 풉니다.

## 웹 서버 구성

### 내장 PHP 개발 서버

가장 간단한 방법으로 설치하고 실행할 수 있습니다. 내장 서버를 사용하여 응용 프로그램을 실행하고 sqlite3이 시스템에 설치되어 있다면 데이터베이스로 SQLite를 사용하고 많은 것을 필요로하지 않습니다! PHP를 설치한 후 다음 명령어를 실행하십시오.

```bash
php -S localhost:8000
```

그런 다음 브라우저를 열고 `http://localhost:8000`로 이동하십시오.

프로젝트의 문서 루트를 다른 디렉터리로 만들려는 경우 (예: 프로젝트가 `~/myproject`이지만 문서 루트가 `~/myproject/public/`임) `~/myproject` 디렉토리에 들어간 후 다음 명령어를 실행할 수 있습니다.

```bash
php -S localhost:8000 -t public/
```

그런 다음 브라우저를 열고 `http://localhost:8000`로 이동하십시오.

### Apache

시스템에 Apache가 설치되어 있는지 확인하십시오. 그렇지 않은 경우 시스템에 Apache를 설치하는 방법을 검색하십시오.

Apache의 경우 다음과 같이 `.htaccess` 파일을 수정하십시오:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **참고**: 만약 서브디렉토리에서 flight를 사용해야 하는 경우
> `RewriteBase /subdir/` 라인을 `RewriteEngine On` 바로 다음에 추가하십시오.

> **참고**: DB 또는 env 파일과 같은 모든 서버 파일을 보호해야 하는 경우
> `.htaccess` 파일에 다음을 추가하십시오:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

시스템에 Nginx가 설치되어 있는지 확인하십시오. 그렇지 않은 경우 시스템에 Nginx Apache를 설치하는 방법을 검색하십시오.

Nginx의 경우 서버 선언에 다음을 추가하십시오:

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

// Composer를 사용 중이라면 오토로더를 요구하십시오.
require 'vendor/autoload.php';
// Composer를 사용하지 않는 경우는 프레임워크를 직접로드하십시오.
// require 'flight/Flight.php';

// 그런 다음 라우트를 정의하고 요청을 처리하는 함수를 할당하십시오.
Flight::route('/', function () {
  echo 'hello world!';
});

// 마지막으로 프레임워크를 시작하십시오.
Flight::start();
```

## PHP 설치

이미 시스템에 `php`가 설치되어 있다면 이 지침을 건너뛰고 [파일 다운로드 섹션](#download-the-files)으로 이동하십시오

그럼! macOS, Windows 10/11, Ubuntu 및 Rocky Linux에 PHP를 설치하는 지침이 있습니다. PHP의 다른 버전을 설치하는 방법에 대한 자세한 내용도 포함하겠습니다.

### **macOS**

#### **Homebrew를 사용하여 PHP 설치**

1. **Homebrew 설치** (이미 설치되어 있지 않은 경우):
   - 터미널 열고 다음을 실행하십시오:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **PHP 설치**:
   - 최신 버전 설치:
     ```bash
     brew install php
     ```
   - 특정 버전 설치, 예를 들어, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **PHP 버전 전환**:
   - 현재 버전 unlink하고 원하는 버전을 link하십시오:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - 설치된 버전 확인:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **수동으로 PHP 설치하기**

1. **PHP 다운로드**:
   - [PHP for Windows](https://windows.php.net/download/) 방문하고 최신 또는 특정 버전 (예: 7.4, 8.0)을 스레드되지 않은 zip 파일로 다운로드하십시오.

2. **PHP 압축해제**:
   - 다운로드한 zip 파일을 `C:\php`에 압축해제하십시오.

3. **시스템 PATH에 PHP 추가**:
   - **시스템 속성** > **환경 변수**로 이동하십시오.
   - **시스템 변수**에서 **Path**를 찾아 **편집**을 클릭하십시오.
   - `C:\php` 경로 (또는 PHP를 압축해제한 위치)를 추가하십시오.
   - 모든 창을 닫으려면 **확인**을 클릭하십시오.

4. **PHP 구성**:
   - `php.ini-development`을 `php.ini`로 복사하십시오.
   - 필요에 따라 PHP를 구성하려면 `php.ini`를 편집하십시오 (예: `extension_dir` 설정, 확장 기능 활성화).

5. **PHP 설치 확인**:
   - 명령 프롬프트 열고 실행:
     ```cmd
     php -v
     ```

#### **여러 버전의 PHP 설치**

1. 각 버전에 대해 위의 단계를 반복하되 각 버전을 별도의 디렉토리에 둡니다 (예: `C:\php7`, `C:\php8`).

2. 원하는 버전의 디렉토리로 시스템 PATH 변수를 조정하여 버전 간에 전환하십시오.

### **Ubuntu (20.04, 22.04 등)**

#### **apt를 사용하여 PHP 설치**

1. **패키지 목록 업데이트**:
   - 터미널 열고 다음을 실행하십시오:
     ```bash
     sudo apt update
     ```

2. **PHP 설치**:
   - 최신 PHP 버전 설치:
     ```bash
     sudo apt install php
     ```
   - 특정 버전 설치, 예를 들어, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **추가 모듈 설치** (옵션):
   - 예를 들어, MySQL 지원을 설치하려면:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **PHP 버전 전환**:
   - `update-alternatives`를 사용하십시오:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **설치된 버전 확인**:
   - 실행:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **yum/dnf를 사용하여 PHP 설치**

1. **EPEL 저장소 활성화**:
   - 터미널 열고 다음을 실행하십시오:
     ```bash
     sudo dnf install epel-release
     ```

2. **Remi 저장소 설치**:
   - 실행:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **PHP 설치**:
   - 기본 버전 설치:
     ```bash
     sudo dnf install php
     ```
   - 특정 버전 설치, 예를 들어, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **PHP 버전 전환**:
   - `dnf` 모듈 명령 사용:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **설치된 버전 확인**:
   - 실행:
     ```bash
     php -v
     ```

### **일반 참고 사항**

- 개발 환경에서는 프로젝트 요구 사항에 맞게 PHP 설정을 구성하는 것이 중요합니다.
- PHP 버전을 전환할 때 해당 버전에 필요한 모든 관련 PHP 확장 기능이 설치되어 있는지 확인하십시오.
- PHP 버전을 전환하거나 환경을 업데이트 한 후 변경사항을 적용하려면 웹 서버 (Apache, Nginx 등)를 다시 시작하십시오.