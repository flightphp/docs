# Runway

Runway는 Flight 애플리케이션을 관리하는 데 도움을 주는 CLI 애플리케이션입니다. 컨트롤러를 생성하고, 모든 경로를 표시하며, 그 이상의 기능을 제공합니다. 이는 훌륭한 [adhocore/php-cli](https://github.com/adhocore/php-cli) 라이브러리를 기반으로 합니다.

코드 보기 [여기](https://github.com/flightphp/runway)를 클릭하세요.

## 설치

Composer를 사용하여 설치하세요.

```bash
composer require flightphp/runway
```

## 기본 구성

Runway를 처음 실행할 때, `app/config/config.php`의 `'runway'` 키를 통해 `runway` 구성을 찾으려고 시도합니다.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **참고** - **v1.2.0**부터 `.runway-config.json`은 더 이상 사용되지 않습니다. 구성을 `app/config/config.php`로 이전하세요. `php runway config:migrate` 명령어를 사용하여 쉽게 할 수 있습니다.

### 프로젝트 루트 감지

Runway는 하위 디렉토리에서 실행하더라도 프로젝트의 루트를 감지할 만큼 똑똑합니다. `composer.json`, `.git`, 또는 `app/config/config.php`와 같은 지표를 찾아 프로젝트 루트가 어디인지 결정합니다. 이는 프로젝트의 어디서든 Runway 명령어를 실행할 수 있음을 의미합니다!

## 사용법

Runway에는 Flight 애플리케이션을 관리하는 데 사용할 수 있는 여러 명령어가 있습니다. Runway를 사용하는 두 가지 쉬운 방법이 있습니다.

1. 스켈레톤 프로젝트를 사용 중이라면, 프로젝트 루트에서 `php runway [command]`를 실행할 수 있습니다.
1. Composer를 통해 설치된 패키지로 Runway를 사용 중이라면, 프로젝트 루트에서 `vendor/bin/runway [command]`를 실행할 수 있습니다.

### 명령어 목록

`php runway` 명령어를 실행하여 사용 가능한 모든 명령어 목록을 볼 수 있습니다.

```bash
php runway
```

### 명령어 도움말

어떤 명령어든 `--help` 플래그를 전달하여 명령어를 사용하는 방법에 대한 더 많은 정보를 얻을 수 있습니다.

```bash
php runway routes --help
```

다음은 몇 가지 예시입니다:

### 컨트롤러 생성

`runway.app_root`의 구성에 기반하여, `app/controllers/` 디렉토리에 컨트롤러를 생성합니다.

```bash
php runway make:controller MyController
```

### Active Record 모델 생성

먼저 [Active Record](/awesome-plugins/active-record) 플러그인이 설치되었는지 확인하세요. `runway.app_root`의 구성에 기반하여, `app/records/` 디렉토리에 레코드를 생성합니다.

```bash
php runway make:record users
```

예를 들어, `users` 테이블에 다음 스키마가 있는 경우: `id`, `name`, `email`, `created_at`, `updated_at`, `app/records/UserRecord.php` 파일에 다음과 유사한 파일이 생성됩니다:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 테이블을 위한 ActiveRecord 클래스.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // $relations 배열에서 정의한 후 관계를 여기에 추가할 수도 있습니다
 * @property CompanyRecord $company 관계 예시
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations 모델의 관계 설정
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * 생성자
     * @param mixed $databaseConnection 데이터베이스 연결
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### 모든 경로 표시

이것은 현재 Flight에 등록된 모든 경로를 표시합니다.

```bash
php runway routes
```

특정 경로만 보려면, 경로를 필터링하는 플래그를 전달할 수 있습니다.

```bash
# GET 경로만 표시
php runway routes --get

# POST 경로만 표시
php runway routes --post

# 등.
```

## Runway에 사용자 지정 명령어 추가

Flight용 패키지를 생성하거나 프로젝트에 사용자 지정 명령어를 추가하려면, 프로젝트/패키지에 `src/commands/`, `flight/commands/`, `app/commands/`, 또는 `commands/` 디렉토리를 생성하여 할 수 있습니다. 추가 사용자 정의가 필요하다면, 아래 구성 섹션을 참조하세요.

명령어를 생성하려면, `AbstractBaseCommand` 클래스를 확장하고 최소한 `__construct` 메서드와 `execute` 메서드를 구현하면 됩니다.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * 생성자
     *
     * @param array<string,mixed> $config app/config/config.php의 구성
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', '문서화를 위한 예시 생성', $config);
        $this->argument('<funny-gif>', '재미있는 GIF의 이름');
    }

	/**
     * 함수 실행
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('예시 생성 중...');

		// 여기에 무언가를 수행

		$io->ok('예시가 생성되었습니다!');
	}
}
```

Flight 애플리케이션에 사용자 지정 명령어를 구축하는 방법에 대한 자세한 정보는 [adhocore/php-cli 문서](https://github.com/adhocore/php-cli)를 참조하세요!

## 구성 관리

`v1.2.0`부터 구성이 `app/config/config.php`로 이동되었으므로, 구성을 관리하는 몇 가지 도우미 명령어가 있습니다.

### 이전 구성 마이그레이션

이전 `.runway-config.json` 파일이 있다면, 다음 명령어로 `app/config/config.php`로 쉽게 마이그레이션할 수 있습니다:

```bash
php runway config:migrate
```

### 구성 값 설정

`config:set` 명령어를 사용하여 구성 값을 설정할 수 있습니다. 파일을 열지 않고 구성 값을 업데이트하려는 경우 유용합니다.

```bash
php runway config:set app_root "app/"
```

### 구성 값 가져오기

`config:get` 명령어를 사용하여 구성 값을 가져올 수 있습니다.

```bash
php runway config:get app_root
```

## 모든 Runway 구성

Runway의 구성을 사용자 정의해야 한다면, `app/config/config.php`에 이러한 값을 설정할 수 있습니다. 아래는 설정할 수 있는 추가 구성입니다:

```php
<?php
// app/config/config.php
return [
    // ... 다른 구성 값들 ...

    'runway' => [
        // 애플리케이션 디렉토리가 위치한 곳
        'app_root' => 'app/',

        // 루트 인덱스 파일이 위치한 디렉토리
        'index_root' => 'public/',

        // 다른 프로젝트의 루트 경로들
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // 기본 경로는 대부분 구성할 필요가 없지만, 필요하다면 여기에 있습니다
        'base_paths' => [
            '/includes/libs/vendor', // 벤더 디렉토리에 정말 독특한 경로가 있는 경우 등
        ],

        // 최종 경로는 프로젝트 내 명령어 파일을 검색할 위치입니다
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // 전체 경로를 추가하려면, 그대로 진행하세요 (프로젝트 루트에 대한 절대 또는 상대 경로)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### 구성 접근

구성 값을 효과적으로 접근해야 한다면, `__construct` 메서드나 `app()` 메서드를 통해 접근할 수 있습니다. 또한 `app/config/services.php` 파일이 있는 경우, 해당 서비스도 명령어에서 사용할 수 있습니다.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // 구성 접근
    $app_root = $this->config['runway']['app_root'];
    
    // 데이터베이스 연결과 같은 서비스 접근
    $database = $this->config['database']
    
    // ...
}
```

## AI 도우미 래퍼

Runway에는 AI가 명령어를 생성하기 쉽게 하는 몇 가지 도우미 래퍼가 있습니다. Symfony Console과 유사하게 `addOption`과 `addArgument`를 사용할 수 있습니다. AI 도구를 사용하여 명령어를 생성하는 경우 유용합니다.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', '문서화를 위한 예시 생성', $config);
    
    // name 옵션은 null로 선택적이며 완전히 선택적입니다
    $this->addOption('name', '예시의 이름', null);
}
```