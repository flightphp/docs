# 설치 지침

Flight를 설치하기 전에 몇 가지 기본 전제 조건이 있습니다. 구체적으로 다음을 수행해야 합니다:

1. [시스템에 PHP 설치](#php-설치)
2. 최고의 개발자 경험을 위해 [Composer 설치](https://getcomposer.org).

## 기본 설치

[Composer](https://getcomposer.org)를 사용 중이라면 다음
명령어를 실행할 수 있습니다:

```bash
composer require flightphp/core
```

이것은 시스템에 Flight 코어 파일만 설치합니다. 프로젝트 구조 정의, [레이아웃](/learn/templates), [의존성](/learn/dependency-injection-container), [설정](/learn/configuration), [자동 로딩](/learn/autoloading) 등을 정의해야 합니다. 이 방법은 Flight 외에 다른 의존성을 설치하지 않도록 보장합니다.

또는 [파일 다운로드](https://github.com/flightphp/core/archive/master.zip)
를 직접 수행하고 웹 디렉토리에 추출할 수도 있습니다.

## 권장 설치

새 프로젝트의 경우 [flightphp/skeleton](https://github.com/flightphp/skeleton) 앱으로 시작하는 것을 강력히 권장합니다. 설치는 매우 간단합니다.

```bash
composer create-project flightphp/skeleton my-project/
```

이것은 프로젝트 구조를 설정하고, 네임스페이스와 함께 자동 로딩을 구성하며, [Tracy](/awesome-plugins/tracy), [Tracy Extensions](/awesome-plugins/tracy-extensions), [Runway](/awesome-plugins/runway)와 같은 다른 도구를 제공합니다.

## 웹 서버 구성

### 내장 PHP 개발 서버

이것은 실행을 시작하는 가장 간단한 방법입니다. 내장 서버를 사용하여 애플리케이션을 실행할 수 있으며, 심지어 데이터베이스로 SQLite를 사용할 수도 있습니다 (시스템에 sqlite3가 설치되어 있는 한) 그리고 거의 아무것도 필요하지 않습니다! PHP가 설치된 후 다음 명령어를 실행하세요:

```bash
php -S localhost:8000
# 또는 skeleton 앱과 함께
composer start
```

그런 다음 브라우저를 열고 `http://localhost:8000`로 이동하세요.

프로젝트의 문서 루트를 다른 디렉토리로 만들고 싶다면 (예: 프로젝트가 `~/myproject`이지만 문서 루트가 `~/myproject/public/`인 경우), `~/myproject` 디렉토리에 있는 경우 다음 명령어를 실행할 수 있습니다:

```bash
php -S localhost:8000 -t public/
# skeleton 앱과 함께, 이는 이미 구성되어 있습니다
composer start
```

그런 다음 브라우저를 열고 `http://localhost:8000`로 이동하세요.

### Apache

시스템에 Apache가 이미 설치되어 있는지 확인하세요. 그렇지 않다면, 시스템에 Apache를 설치하는 방법을 구글링하세요.

Apache의 경우, 다음으로 `.htaccess` 파일을 편집하세요:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **참고**: 하위 디렉토리에서 flight를 사용해야 하는 경우 `RewriteEngine On` 바로 다음에
> `RewriteBase /subdir/` 줄을 추가하세요.

> **참고**: 서버 파일을 모두 보호하고 싶다면, db나 env 파일처럼.
> `.htaccess` 파일에 다음을 넣으세요:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

시스템에 Nginx가 이미 설치되어 있는지 확인하세요. 그렇지 않다면, 시스템에 Nginx를 설치하는 방법을 구글링하세요.

Nginx의 경우, 서버 선언에 다음을 추가하세요:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## `index.php` 파일 생성

기본 설치를 수행 중이라면 시작할 수 있는 코드가 필요합니다.

```php
<?php

// Composer를 사용 중이라면, 자동 로더를 요구하세요.
require 'vendor/autoload.php';
// Composer를 사용하지 않는다면, 프레임워크를 직접 로드하세요
// require 'flight/Flight.php';

// 그런 다음 라우트를 정의하고 요청을 처리할 함수를 할당하세요.
Flight::route('/', function () {
  echo 'hello world!';
});

// 마지막으로, 프레임워크를 시작하세요.
Flight::start();
```

skeleton 앱과 함께라면, 이는 이미 `app/config/routes.php` 파일에서 구성되고 처리됩니다. 서비스는 `app/config/services.php`에서 구성됩니다.

## PHP 설치

시스템에 이미 `php`가 설치되어 있다면, 이 지침을 건너뛰고 [다운로드 섹션](#download-the-files)으로 이동하세요.

### **macOS**

#### **Homebrew를 사용한 PHP 설치**

1. **Homebrew 설치** (이미 설치되어 있지 않은 경우):
   - 터미널을 열고 실행:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **PHP 설치**:
   - 최신 버전 설치:
     ```bash
     brew install php
     ```
   - 특정 버전을 설치하려면, 예를 들어 PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **PHP 버전 전환**:
   - 현재 버전을 언링크하고 원하는 버전을 링크:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - 설치된 버전 확인:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **PHP 수동 설치**

1. **PHP 다운로드**:
   - [PHP for Windows](https://windows.php.net/download/)를 방문하여 최신 버전 또는 특정 버전 (예: 7.4, 8.0)을 비스레드-세이프 zip 파일로 다운로드하세요.

2. **PHP 추출**:
   - 다운로드한 zip 파일을 `C:\php`로 추출하세요.

3. **시스템 PATH에 PHP 추가**:
   - **시스템 속성** > **환경 변수**로 이동.
   - **시스템 변수**에서 **Path**를 찾아 **편집**을 클릭.
   - `C:\php` 경로 (또는 PHP를 추출한 위치)를 추가.
   - 모든 창을 닫기 위해 **확인**을 클릭.

4. **PHP 구성**:
   - `php.ini-development`를 `php.ini`로 복사.
   - `php.ini`를 편집하여 PHP를 필요에 따라 구성 (예: `extension_dir` 설정, 확장 활성화).

5. **PHP 설치 확인**:
   - 명령 프롬프트를 열고 실행:
     ```cmd
     php -v
     ```

#### **여러 버전의 PHP 설치**

1. **위 단계를 반복** 각 버전에 대해, 각각 별도의 디렉토리에 배치 (예: `C:\php7`, `C:\php8`).

2. **버전 전환** 시스템 PATH 변수를 원하는 버전 디렉토리를 가리키도록 조정하여.

### **Ubuntu (20.04, 22.04 등)**

#### **apt를 사용한 PHP 설치**

1. **패키지 목록 업데이트**:
   - 터미널을 열고 실행:
     ```bash
     sudo apt update
     ```

2. **PHP 설치**:
   - 최신 PHP 버전 설치:
     ```bash
     sudo apt install php
     ```
   - 특정 버전을 설치하려면, 예를 들어 PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **추가 모듈 설치** (선택):
   - 예를 들어 MySQL 지원 설치:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **PHP 버전 전환**:
   - `update-alternatives` 사용:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **설치된 버전 확인**:
   - 실행:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **yum/dnf를 사용한 PHP 설치**

1. **EPEL 저장소 활성화**:
   - 터미널을 열고 실행:
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
   - 특정 버전을 설치하려면, 예를 들어 PHP 7.4:
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

- 개발 환경의 경우, 프로젝트 요구 사항에 따라 PHP 설정을 구성하는 것이 중요합니다.
- PHP 버전을 전환할 때, 사용할 특정 버전에 대한 모든 관련 PHP 확장이 설치되어 있는지 확인하세요.
- PHP 버전을 전환하거나 구성을 업데이트한 후 변경 사항을 적용하기 위해 웹 서버(Apache, Nginx 등)를 재시작하세요.