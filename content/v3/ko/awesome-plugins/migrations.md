# 마이그레이션

프로젝트의 마이그레이션은 프로젝트에 관련된 모든 데이터베이스 변경 사항을 추적합니다.  
[byjg/php-migration](https://github.com/byjg/php-migration) 는 시작하는 데 매우 유용한 기본 라이브러리입니다.

## 설치

### PHP 라이브러리

프로젝트에 PHP 라이브러리만 사용하고 싶다면:

```bash
composer require "byjg/migration"
```

### 명령 줄 인터페이스

명령 줄 인터페이스는 독립형이며 프로젝트와 함께 설치할 필요가 없습니다.

글로벌로 설치하고 심볼릭 링크를 생성할 수 있습니다.

```bash
composer require "byjg/migration-cli"
```

Migration CLI에 대한 더 많은 정보를 얻으려면 [byjg/migration-cli](https://github.com/byjg/migration-cli)를 방문해 주세요.

## 지원되는 데이터베이스

| 데이터베이스      | 드라이버                                                                          | 연결 문자열                                        |
| --------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                   |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database         |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |

## 작동 방식은?

데이터베이스 마이그레이션은 데이터베이스 버전 관리를 위해 PURE SQL을 사용합니다.  
작동하게 하려면 다음이 필요합니다:

* SQL 스크립트 생성
* 명령 줄 또는 API를 사용하여 관리

### SQL 스크립트

스크립트는 세 가지 세트로 나뉩니다:

* BASE 스크립트는 새 데이터베이스를 생성하기 위한 모든 SQL 명령을 포함합니다;
* UP 스크립트는 데이터베이스 버전을 "업"하기 위한 모든 SQL 마이그레이션 명령을 포함합니다;
* DOWN 스크립트는 데이터베이스 버전을 "다운"하거나 되돌리기 위한 모든 SQL 마이그레이션 명령을 포함합니다;

스크립트 디렉토리는 다음과 같습니다:

```text
 <root dir>
     |
     +-- base.sql
     |
     +-- /migrations
              |
              +-- /up
                   |
                   +-- 00001.sql
                   +-- 00002.sql
              +-- /down
                   |
                   +-- 00000.sql
                   +-- 00001.sql
```

* "base.sql"은 기본 스크립트입니다.
* "up" 폴더는 버전을 업그레이드하는 스크립트를 포함합니다.  
  예를 들어: 00002.sql은 데이터베이스를 버전 '1'에서 '2'로 이동하는 스크립트입니다.
* "down" 폴더는 버전을 다운그레이드하는 스크립트를 포함합니다.  
  예를 들어: 00001.sql은 데이터베이스를 버전 '2'에서 '1'로 이동하는 스크립트입니다.  
  "down" 폴더는 선택 사항입니다.

### 다중 개발 환경

여러 개발자와 여러 브랜치에서 작업하는 경우 다음 숫자를 결정하기가 너무 어렵습니다.

그 경우에는 버전 번호 뒤에 "-dev" 접미사를 추가해야 합니다.

시나리오는 다음과 같습니다:

* 개발자 1은 브랜치를 생성하고 가장 최근 버전이 예를 들어 42입니다.
* 개발자 2는 동시에 브랜치를 생성하고 동일한 데이터베이스 버전 번호를 가지고 있습니다.

두 경우 모두 개발자는 43-dev.sql이라는 파일을 생성할 것입니다. 두 개발자는 문제 없이 업그레이드하고 다운그레이드를 수행할 것이며, 로컬 버전은 43이 될 것입니다.

하지만 개발자 1이 변경 사항을 병합하고 최종 버전 43.sql을 생성했습니다(`git mv 43-dev.sql 43.sql`). 개발자 2가 로컬 브랜치를 업데이트하면 그는 43.sql(개발자 1의 파일)과 43-dev.sql를 갖게 됩니다.  
그가 업그레이드하거나 다운그레이드를 시도하면 마이그레이션 스크립트는 다운되고 두 개의 버전 43이 있다고 경고합니다. 그 경우 개발자 2는 자신의 파일을 44-dev.sql로 업데이트하고 병합할 때까지 작업을 계속해야 합니다.

## PHP API 사용 및 프로젝트에 통합하기

기본 사용법은 다음과 같습니다:

* ConnectionManagement 객체와의 연결을 생성합니다. 더 많은 정보는 "byjg/anydataset" 구성 요소를 참조하세요.
* 이 연결과 SQL 스크립트가 위치한 폴더로 Migration 객체를 생성합니다.
* 마이그레이션 스크립트를 "reset", "up" 또는 "down"을 위해 적절한 명령을 사용합니다.

예제를 보세요:

```php
<?php
// 연결 URI 생성
// 추가 정보: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// 데이터베이스 또는 데이터베이스를 등록할 수 있습니다:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Migration 인스턴스 생성
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 실행 중 정보를 받기 위한 콜백 진행 함수를 추가합니다.
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// "base.sql" 스크립트를 사용하여 데이터베이스를 복원합니다
// 그리고 데이터베이스 버전을 최신 버전으로 업그레이드하기 위해 모든 기존 스크립트를 실행합니다
$migration->reset();

// 현재 버전에서 시작하여 $version 번호까지 데이터베이스 버전을 업그레이드하거나 다운그레이드하기 위해 모든 기존 스크립트를 실행합니다;
// 버전 번호가 지정되지 않으면 마지막 데이터베이스 버전까지 마이그레이션
$migration->update($version = null);
```

Migration 객체는 데이터베이스 버전을 관리합니다.

### 프로젝트에서 버전 관리 생성

```php
<?php
// 데이터베이스 또는 데이터베이스를 등록할 수 있습니다:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Migration 인스턴스 생성
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 이 명령은 데이터베이스에 버전 테이블을 생성합니다
$migration->createVersion();
```

### 현재 버전 가져오기

```php
<?php
$migration->getCurrentVersion();
```

### 진행 상황 제어를 위한 콜백 추가

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "명령 실행 중: $command, 버전 $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Db 드라이버 인스턴스 가져오기

```php
<?php
$migration->getDbDriver();
```

사용하려면 다음을 방문해 주세요: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### 부분 마이그레이션 방지 (MySQL에 대해 사용할 수 없음)

부분 마이그레이션은 마이그레이션 스크립트가 오류 또는 수동 중단으로 인해 프로세스 중간에 중단되는 경우입니다.

마이그레이션 테이블은 `partial up` 또는 `partial down` 상태이므로 다시 마이그레이션하기 전에 수동으로 수정해야 합니다. 

이 상황을 피하기 위해 마이그레이션이 트랜잭션 컨텍스트 내에서 실행되도록 명시할 수 있습니다.  
마이그레이션 스크립트가 실패하면 트랜잭션이 롤백되고 마이그레이션 테이블은 `complete`로 표시되며, 버전은 오류를 일으킨 스크립트 이전의 가장 최근 버전이 될 것입니다.

이 기능을 사용하려면 `withTransactionEnabled` 메서드를 호출하고 `true`를 매개변수로 전달해야 합니다:

```php
<?php
$migration->withTransactionEnabled(true);
```

**참고: 이 기능은 MySQL에서 사용할 수 없습니다. MySQL은 트랜잭션 내에서 DDL 명령을 지원하지 않기 때문입니다.**  
이 메서드를 MySQL에서 사용하면 Migration은 그를 무시합니다.  
자세한 내용: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Postgres에 대한 SQL 마이그레이션 작성 팁

### 트리거 및 SQL 함수 생성 시

```sql
-- DO
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empname과 salary가 주어졌는지 확인
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null'; -- 이 주석이 비어 있든 지 여부는 중요하지 않음
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname; --
        END IF; --

        -- 누가 우리를 위해 일하며 언제 대가를 지불해야 하는가?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname; --
        END IF; --

        -- 누가 언제 급여를 변경했는지 기억
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- DON'T
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empname과 salary가 주어졌는지 확인
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname cannot be null';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% cannot have null salary', NEW.empname;
        END IF;

        -- 누가 우리를 위해 일하며 언제 대가를 지불해야 하는가?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% cannot have a negative salary', NEW.empname;
        END IF;

        -- 누가 언제 급여를 변경했는지 기억
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

`PDO` 데이터베이스 추상화 계층은 SQL 문 전체를 배치로 실행할 수 없기 때문에, `byjg/migration`이 마이그레이션 파일을 읽을 때 SQL 파일의 전체 내용을 세미콜론에서 나누고 명령을 하나씩 실행해야 합니다. 그러나 하나의 종류의 명령은 본체에 여러 개의 세미콜론을 가질 수 있습니다: 함수입니다.

함수를 올바르게 파싱할 수 있도록 하기 위해, `byjg/migration` 2.1.0에서는 마이그레이션 파일을 단순히 세미콜론 대신 `세미콜론 + EOL` 시퀀스에서 나누도록 변경되었습니다. 이렇게 하면 각 함수 정의의 내부 세미콜론 뒤에 빈 주석을 추가하면 `byjg/migration`이 올바르게 파싱할 수 있습니다.

불행히도 이러한 주석을 추가하는 것을 잊으면 라이브러리는 `CREATE FUNCTION` 문을 여러 부분으로 나누고 마이그레이션이 실패하게 됩니다.

### 콜론 문자(`:`) 피하기

```sql
-- DO
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- DON'T
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

`PDO`는 준비된 문에서 이름 있는 매개변수를 접두사로 붙이는 데 콜론 문자를 사용하므로, 다른 컨텍스트에서 이 사용이 문제를 발생시킬 수 있습니다.

예를 들어, PostgreSQL에서는 값을 타입 간에 캐스팅할 때 `::`를 사용할 수 있습니다. 반면, `PDO`는 이를 잘못된 컨텍스트에서 잘못된 이름 있는 매개변수로 읽고 실행하려고 할 때 실패합니다.

이 inconsistency를 수정하는 유일한 방법은 콜론을 완전히 피하는 것입니다 (이 경우 PostgreSQL은 대안 구문인 `CAST(value AS type)`도 제공합니다).

### SQL 편집기 사용

마지막으로, 수동 SQL 마이그레이션 작성은 힘들 수 있지만 SQL 문법을 이해할 수 있는 편집기를 사용하면 훨씬 쉬워집니다.  
자동 완성, 현재 데이터베이스 스키마 탐색 및/또는 코드 자동 포맷팅을 제공하는 편집기를 사용하는 것이 좋습니다.

## 동일한 스키마 내에서 서로 다른 마이그레이션 처리하기

동일한 스키마 내에서 서로 다른 마이그레이션 스크립트와 버전을 생성해야 하는 경우 가능하지만 너무 위험하며 **전혀 추천하지 않습니다.**

이렇게 하려면 생성자에 매개변수를 전달하여 서로 다른 "마이그레이션 테이블"을 만들어야 합니다.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

보안상의 이유로 이 기능은 명령 줄에서 사용할 수 없지만, 환경 변수 `MIGRATION_VERSION`을 사용하여 이름을 저장할 수 있습니다.

이 기능을 사용하지 않는 것을 강력히 권장합니다. 권장 사항은 하나의 스키마에 대해 하나의 마이그레이션입니다.

## 단위 테스트 실행

기본 단위 테스트는 다음과 같이 실행할 수 있습니다:

```bash
vendor/bin/phpunit
```

## 데이터베이스 테스트 실행

통합 테스트를 실행하려면 데이터베이스가 시작되고 실행 중이어야 합니다.  
우리는 테스트를 위해 데이터베이스를 시작하는 데 사용할 수 있는 기본 `docker-compose.yml`을 제공했습니다.

### 데이터베이스 실행하기

```bash
docker-compose up -d postgres mysql mssql
```

### 테스트 실행하기

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

선택적으로 단위 테스트에서 사용하는 호스트와 비밀번호를 설정할 수 있습니다.

```bash
export MYSQL_TEST_HOST=localhost     # 기본값은 localhost
export MYSQL_PASSWORD=newpassword    # 비밀번호가 null이면 '.' 사용
export PSQL_TEST_HOST=localhost      # 기본값은 localhost
export PSQL_PASSWORD=newpassword     # 비밀번호가 null이면 '.' 사용
export MSSQL_TEST_HOST=localhost     # 기본값은 localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # 기본값은 /tmp/test.db
```