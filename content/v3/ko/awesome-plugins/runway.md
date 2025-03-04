# 런웨이

런웨이는 Flight 어플리케이션을 관리하는 데 도움이 되는 CLI 어플리케이션입니다. 컨트롤러를 생성하고 모든 라우트를 표시할 수 있습니다. 이 라이브러리는 훌륭한 [adhocore/php-cli](https://github.com/adhocore/php-cli) 라이브러리를 기반으로 합니다.

[여기](https://github.com/flightphp/runway)를 클릭하여 코드를 확인하십시오.

## 설치

컴포저로 설치합니다.

```bash
composer require flightphp/runway
```

## 기본 구성

런웨이를 처음 실행하면 설정 프로세스를 진행하고 프로젝트 루트에 `.runway.json` 구성 파일을 생성합니다. 이 파일에는 런웨이가 올바르게 작동하기 위한 일부 필수 구성이 포함되어 있습니다.

## 사용법

런웨이에는 Flight 어플리케이션을 관리하는 데 사용할 수 있는 여러 명령이 있습니다. 런웨이를 사용하는 두 가지 쉬운 방법이 있습니다.

1. 스켈레톤 프로젝트를 사용하는 경우 프로젝트의 루트에서 `php runway [command]`를 실행할 수 있습니다.
1. 컴포저를 통해 설치된 패키지로 런웨이를 사용하는 경우 프로젝트의 루트에서 `vendor/bin/runway [command]`를 실행할 수 있습니다.

어떤 명령이든 `--help` 플래그를 전달하여 명령어 사용 방법에 대한 자세한 정보를 얻을 수 있습니다.

```bash
php runway routes --help
```

다음은 몇 가지 예시입니다:

### 컨트롤러 생성

`.runway.json` 파일의 구성에 따라 기본 위치는 `app/controllers/` 디렉토리에 컨트롤러를 생성합니다.

```bash
php runway make:controller MyController
```

### Active Record 모델 생성

`.runway.json` 파일의 구성에 따라 기본 위치는 `app/records/` 디렉토리에 컨트롤러를 생성합니다.

```bash
php runway make:record users
```

예를 들어 `users` 테이블에 다음 스키마가 있는 경우: `id`, `name`, `email`, `created_at`, `updated_at`, `app/records/UserRecord.php` 파일에 다음과 유사한 파일이 생성됩니다:

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
 * // 관계를 정의한 후 $relations 배열에 정의할 수 있습니다.
 * @property CompanyRecord $company 관계의 예시
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
     * @param mixed $databaseConnection 데이터베이스에 대한 연결
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### 모든 라우트 표시

현재 Flight에 등록된 모든 라우트를 표시합니다.

```bash
php runway routes
```

특정 라우트만 보고 싶은 경우 플래그를 전달하여 라우트를 필터링할 수 있습니다.

```bash
# GET 라우트만 표시
php runway routes --get

# POST 라우트만 표시
php runway routes --post

# 등.
```

## 런웨이 사용자 정의

Flight용 패키지를 생성하거나 프로젝트에 사용자 정의 명령을 추가하려면 프로젝트/패키지에 `src/commands/`, `flight/commands/`, `app/commands/`, 또는 `commands/` 디렉토리를 만들어야 합니다.

명령을 만들려면 `AbstractBaseCommand` 클래스를 확장하고 최소한 `__construct` 메서드와 `execute` 메서드를 구현하면 됩니다.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * 생성자
     *
     * @param array<string,mixed> $config .runway-config.json에서의 JSON 구성
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', '문서에 예제 생성', $config);
        $this->argument('<funny-gif>', '웃긴 GIF의 이름');
    }

	/**
     * 함수 실행
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('예제 생성 중...');

		// 여기에 코드 추가

		$io->ok('예제가 생성되었습니다!');
	}
}
```

Flight 어플리케이션에 사용자 정의 명령어를 구축하는 방법에 대해 자세히 알아보려면 [adhocore/php-cli 문서](https://github.com/adhocore/php-cli)를 참조하십시오!