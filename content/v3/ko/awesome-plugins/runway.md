# Runway

Runway는 Flight 애플리케이션을 관리하는 데 도움을 주는 CLI 애플리케이션입니다. 컨트롤러를 생성하고, 모든 경로를 표시하며, 그 외 더 많은 기능을 제공합니다. 이는 우수한 [adhocore/php-cli](https://github.com/adhocore/php-cli) 라이브러리를 기반으로 합니다.

코드 보기 [여기](https://github.com/flightphp/runway) 클릭.

## Installation

composer를 사용하여 설치하세요.

```bash
composer require flightphp/runway
```

## Basic Configuration

Runway를 처음 실행할 때, 설정 프로세스를 안내하고 프로젝트 루트에 `.runway.json` 설정 파일을 생성합니다. 이 파일은 Runway가 제대로 작동하기 위한 필요한 설정을 포함합니다.

## Usage

Runway에는 Flight 애플리케이션을 관리하는 데 사용할 수 있는 여러 명령어가 있습니다. Runway를 사용하는 두 가지 쉬운 방법이 있습니다.

1. 스켈레톤 프로젝트를 사용 중이라면, 프로젝트 루트에서 `php runway [command]`를 실행할 수 있습니다.
1. composer를 통해 설치된 패키지로 Runway를 사용 중이라면, 프로젝트 루트에서 `vendor/bin/runway [command]`를 실행할 수 있습니다.

모든 명령어에 대해 `--help` 플래그를 전달하여 명령어 사용 방법에 대한 더 많은 정보를 얻을 수 있습니다.

```bash
php runway routes --help
```

다음은 몇 가지 예시입니다:

### Generate a Controller

`.runway.json` 파일의 설정에 따라, 기본 위치에 `app/controllers/` 디렉토리에서 컨트롤러를 생성합니다.

```bash
php runway make:controller MyController
```

### Generate an Active Record Model

`.runway.json` 파일의 설정에 따라, 기본 위치에 `app/records/` 디렉토리에서 컨트롤러를 생성합니다.

```bash
php runway make:record users
```

예를 들어 `users` 테이블에 다음 스키마가 있는 경우: `id`, `name`, `email`, `created_at`, `updated_at`, `app/records/UserRecord.php` 파일에 다음과 유사한 파일이 생성됩니다:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 테이블에 대한 ActiveRecord 클래스.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // $relations 배열에 관계를 정의한 후 여기에 관계를 추가할 수도 있습니다
 * @property CompanyRecord $company 관계 예시
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations 모델에 대한 관계 설정
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

### Display All Routes

이 명령어는 현재 Flight에 등록된 모든 경로를 표시합니다.

```bash
php runway routes
```

특정 경로만 보려면 플래그를 전달하여 경로를 필터링할 수 있습니다.

```bash
# GET 경로만 표시
php runway routes --get

# POST 경로만 표시
php runway routes --post

# 등.
```

## Customizing Runway

Flight용 패키지를 생성하거나 프로젝트에 사용자 지정 명령어를 추가하려면, 프로젝트/패키지에 `src/commands/`, `flight/commands/`, `app/commands/`, 또는 `commands/` 디렉토리를 생성할 수 있습니다. 추가 커스터마이징이 필요하다면 아래 설정 섹션을 참조하세요.

명령어를 생성하려면 `AbstractBaseCommand` 클래스를 확장하고, 최소 `__construct` 메서드와 `execute` 메서드를 구현하면 됩니다.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * 생성자
     *
     * @param array<string,mixed> $config .runway-config.json에서 가져온 JSON 설정
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', '문서에 대한 예시 생성', $config);
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

		// 여기에 작업 수행

		$io->ok('예시 생성 완료!');
	}
}
```

Flight 애플리케이션에 사용자 지정 명령어를 빌드하는 방법에 대한 자세한 정보는 [adhocore/php-cli Documentation](https://github.com/adhocore/php-cli)을 참조하세요!

### Configuration

Runway 설정을 커스터마이징해야 한다면, 프로젝트 루트에 `.runway-config.json` 파일을 생성할 수 있습니다. 아래는 설정할 수 있는 추가 설정입니다:

```js
{

	// 애플리케이션 디렉토리가 위치한 곳
	"app_root": "app/",

	// 루트 인덱스 파일이 위치한 디렉토리
	"index_root": "public/",

	// 다른 프로젝트의 루트 경로
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// 기본 경로는 대부분 설정할 필요가 없지만, 필요 시 여기에 있음
	"base_paths": {
		"/includes/libs/vendor", // vendor 디렉토리에 특이한 경로가 있는 경우 등
	},

	// 최종 경로는 프로젝트 내 명령어 파일을 검색할 위치
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// 전체 경로를 추가하려면 그대로 진행 (프로젝트 루트에 대한 절대 또는 상대 경로)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```