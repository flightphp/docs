# 마이그레이션

프로젝트에 대한 마이그레이션은 프로젝트에 관련된 모든 데이터베이스 변경 사항을 추적하는 것입니다.
[byjg/php-migration](https://github.com/byjg/php-migration)은 시작하는 데 매우 유용한 핵심 라이브러리입니다.

## 설치

### PHP 라이브러리

프로젝트에서 PHP 라이브러리만 사용하고 싶다면:

```bash
composer require "byjg/migration"
```

### 명령줄 인터페이스

명령줄 인터페이스는 독립형이며 프로젝트와 함께 설치할 필요가 없습니다.

글로벌로 설치하고 심볼릭 링크를 생성할 수 있습니다.

```bash
composer require "byjg/migration-cli"
```

마이그레이션 CLI에 대한 추가 정보를 보려면 [byjg/migration-cli](https://github.com/byjg/migration-cli)를 방문하세요.

## 지원되는 데이터베이스

| 데이터베이스      | 드라이버                                                                          | 연결 문자열                                        |
| --------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite        | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///path/to/file                                   |
| MySql/MariaDb | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://username:password@hostname:port/database         |
| Postgres      | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://username:password@hostname:port/database         |
| Sql Server    | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://username:password@hostname:port/database         |
| Sql Server    | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://username:password@hostname:port/database        |

## 작동 방식?

데이터베이스 마이그레이션은 순수 SQL을 사용하여 데이터베이스 버전을 관리합니다.
작동하려면 다음을 수행해야 합니다:

* SQL 스크립트 생성
* 명령줄 또는 API를 사용하여 관리.

### SQL 스크립트

스크립트는 세 개의 스크립트 세트로 나뉘어 있습니다:

* BASE 스크립트에는 새로운 데이터베이스를 만들기 위한 모든 SQL 명령이 포함되어 있습니다;
* UP 스크립트에는 데이터베이스 버전을 "업"그리기 위한 모든 SQL 마이그레이션 명령이 포함되어 있습니다;
* DOWN 스크립트에는 데이터베이스 버전을 "다운"하거나 되돌리기 위한 모든 SQL 마이그레이션 명령이 포함되어 있습니다;

디렉터리 스크립트는 다음과 같습니다:

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
* "up" 폴더에는 버전을 업그레이드하는 스크립트가 포함됩니다.
   예를 들어: 00002.sql은 데이터베이스를 버전 '1'에서 '2'로 이동하는 스크립트입니다.
* "down" 폴더에는 버전을 다운그레이드하는 스크립트가 포함됩니다.
   예를 들어: 00001.sql은 데이터베이스를 버전 '2'에서 '1'로 이동하는 스크립트입니다.
   "down" 폴더는 선택적입니다.

### 다중 개발 환경

여러 개발자와 여러 브랜치와 함께 작업할 경우 다음 번호를 결정하기가 어렵습니다.

이 경우 버전 번호 뒤에 "-dev" 접미사를 붙입니다.

다음 시나리오를 보세요:

* 개발자 1이 브랜치를 만들고 가장 최근 버전이 예를 들어 42입니다.
* 개발자 2가 동시에 브랜치를 만들고 동일한 데이터베이스 버전 번호를 가집니다.

두 경우 모두 개발자는 43-dev.sql이라는 파일을 생성할 것입니다. 두 개발자 모두 문제 없이 업그레이드 및 다운그레이드를 수행할 수 있으며 로컬 버전은 43이 됩니다.

그러나 개발자 1이 변경 사항을 병합하고 최종 버전 43.sql을 생성했습니다 (`git mv 43-dev.sql 43.sql`). 개발자 2가 로컬 브랜치를 업데이트하면 그는 43.sql(개발자 1의 것)과 43-dev.sql 파일을 가지게 됩니다.
그가 업그레이드 또는 다운그레이드를 시도하면,
마이그레이션 스크립트는 다운되고 두 개의 버전 43이 있음을 알립니다. 이 경우 개발자 2는 자신의 파일을 44-dev.sql로 업데이트하고 변경 사항을 병합하여 최종 버전을 생성할 때까지 작업을 계속해야 합니다.

## PHP API 사용 및 프로젝트에 통합하기

기본 사용법은 다음과 같습니다:

* ConnectionManagement 객체에 연결 생성. 자세한 정보는 "byjg/anydataset" 구성 요소를 참조하세요.
* 이 연결과 SQL 스크립트가 위치한 폴더와 함께 마이그레이션 객체를 생성합니다.
* 마이그레이션 스크립트를 "초기화", "업" 또는 "다운"할 때 적절한 명령을 사용합니다.

예제를 보세요:

```php
<?php
// 연결 URI 생성
// 자세한 내용: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// 데이터베이스 또는 URI를 처리할 수 있는 데이터베이스 등록:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// 마이그레이션 인스턴스 생성
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 실행 정보 수신을 위한 콜백 진행 함수 추가
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// "base.sql" 스크립트를 사용하여 데이터베이스 복원
// 그리고 데이터베이스 버전을 최신 버전으로 업그레이드하기 위해 모든 기존 스크립트를 실행합니다.
$migration->reset();

// 현재 버전부터 $version 번호까지 데이터베이스 버전을 업그레이드하거나 다운그레이드하는 모든 기존 스크립트를 실행합니다;
// 버전 번호가 지정되지 않은 경우 마지막 데이터베이스 버전까지 마이그레이션합니다.
$migration->update($version = null);
```

마이그레이션 객체는 데이터베이스 버전을 제어합니다.

### 프로젝트에서 버전 관리를 생성하기

```php
<?php
// 데이터베이스 또는 데이터베이스를 처리할 수 있는 데이터베이스 등록:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// 마이그레이션 인스턴스 생성
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// 이 명령은 데이터베이스에 버전 테이블을 생성합니다.
$migration->createVersion();
```

### 현재 버전 가져오기

```php
<?php
$migration->getCurrentVersion();
```

### 진행 상황 제어를 위해 콜백 추가하기

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "명령 실행 중: $command 버전 $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### DB 드라이버 인스턴스 가져오기

```php
<?php
$migration->getDbDriver();
```

사용하려면 방문해 주세요: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### 부분적 마이그레이션 방지 (MySQL에서 사용 불가)

부분적 마이그레이션은 오류나 수동 중단으로 인해 마이그레이션 스크립트가 프로세스 중간에 중단되는 경우입니다.

마이그레이션 테이블은 상태 `partial up` 또는 `partial down`으로 표시되며, 다시 마이그레이션하기 전에 수동으로 수정해야 합니다.

이 상황을 피하기 위해 마이그레이션이 트랜잭션 컨텍스트에서 실행되도록 지정할 수 있습니다.
마이그레이션 스크립트가 실패하면 트랜잭션이 롤백되고 마이그레이션 테이블은 `완료됨`으로 표시되며, 버전은 오류를 유발한 스크립트 이전의 바로 전 버전이 됩니다.

이 기능을 활성화하려면 `withTransactionEnabled` 메서드를 호출하여 `true`를 매개변수로 전달해야 합니다:

```php
<?php
$migration->withTransactionEnabled(true);
```

**참고: 이 기능은 MySQL에 대해 사용 불가능합니다. MySQL은 트랜잭션 내에서 DDL 명령을 지원하지 않습니다.**
이 방법을 MySQL과 함께 사용하면 마이그레이션은 조용히 무시할 것입니다. 
자세한 정보: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Postgres에 대한 SQL 마이그레이션 작성 팁

### 트리거 및 SQL 함수 만들기

```sql
-- 수행
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empname 및 급여가 주어졌는지 확인합니다
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname은 null일 수 없습니다'; -- 이 주석이 비어 있든지 관계없음
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '%는 null 급여를 가질 수 없습니다', NEW.empname; --
        END IF; --

        -- 누가 언제 급여를 지급해야 할까요?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '%는 부정적인 급여를 가질 수 없습니다', NEW.empname; --
        END IF; --

        -- Payroll을 변경한 사람과 언제 변경되었는지 기록합니다
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- 하지 마십시오
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- empname 및 급여가 주어졌는지 확인합니다
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname은 null일 수 없습니다';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '%는 null 급여를 가질 수 없습니다', NEW.empname;
        END IF;

        -- 누가 언제 급여를 지급해야 할까요?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '%는 부정적인 급여를 가질 수 없습니다', NEW.empname;
        END IF;

        -- Payroll을 변경한 사람과 언제 변경되었는지 기록합니다
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

`PDO` 데이터베이스 추상화 계층은 SQL 문 배치를 실행할 수 없기 때문에,
`byjg/migration`는 마이그레이션 파일을 읽을 때 세미콜론에서 전체 SQL 파일의 내용을 분할해야 하며 문장을 하나씩 실행해야 합니다. 그러나 하나의 문은 본문 사이에 여러 세미콜론을 가질 수 있습니다: 함수.

함수를 올바르게 구문 분석할 수 있도록 하려면 `byjg/migration` 2.1.0은 마이그레이션 파일을 세미콜론 + EOL 시퀀스에서 분할하기 시작했습니다. 이렇게 하면 함수 정의의 각 내부 세미콜론 뒤에 비어 있는 주석을 추가하면 `byjg/migration`이 이를 구문 분석할 수 있게 됩니다.

불행히도 이러한 주석을 추가하지 않으면 라이브러리는 `CREATE FUNCTION` 문을 여러 부분으로 분할하고 마이그레이션이 실패할 것입니다.

### 콜론 문자(`:`) 사용 피하기

```sql
-- 수행
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- 하지 마십시오
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

`PDO`는 준비된 문에서 명명된 매개변수를 접두어로 사용하기 위해 콜론 문자를 사용하므로,
다른 컨텍스트에서 사용하면 문제가 발생합니다.

예를 들어, PostgreSQL 문은 `::`를 사용하여 값을 유형 간에 캐스팅할 수 있습니다. 반면 `PDO`는 이를 잘못된 컨텍스트에서 잘못된 명명된 매개변수로 읽으며 실행할 때 실패합니다.

이 불일치를 해결하는 유일한 방법은 콜론을 전혀 사용하지 않는 것입니다(이 경우 PostgreSQL에는 대체 구문이 있습니다: `CAST(value AS type)`).

### SQL 편집기 사용

마지막으로, 수동 SQL 마이그레이션을 작성하는 것은 힘들지만
SQL 구문을 이해하고 자동 완성, 현재 데이터베이스 스키마에 대한 탐색 및/또는 코드 자동 포맷팅 기능을 가진 편집기를 사용하면 훨씬 쉬워집니다.

## 하나의 스키마 안에서 다양한 마이그레이션 처리하기

동일한 스키마에서 서로 다른 마이그레이션 스크립트와 버전을 생성해야 하는 경우 가능하지만 너무 위험하며 절대 추천하지 않습니다.

이렇게 하려면 생성자에 매개변수를 전달하여 서로 다른 "마이그레이션 테이블"을 만들어야 합니다.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

보안상의 이유로 이 기능은 명령줄에서 사용 불가능하지만, 환경 변수 `MIGRATION_VERSION`을 사용하여 이름을 저장할 수 있습니다.

이 기능을 사용하지 않는 것을 강력히 추천합니다. 하나의 스키마에 대한 하나의 마이그레이션이 권장됩니다.

## 단위 테스트 실행

기본 단위 테스트는 다음과 같이 실행할 수 있습니다:

```bash
vendor/bin/phpunit
```

## 데이터베이스 테스트 실행

통합 테스트를 실행하려면 데이터베이스가 실행 중이어야 합니다. 우리는 기본 `docker-compose.yml`을 제공하며, 이를 사용하여 테스트를 위해 데이터베이스를 시작할 수 있습니다.

### 데이터베이스 실행

```bash
docker-compose up -d postgres mysql mssql
```

### 테스트 실행

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

선택적으로 단위 테스트에서 사용할 호스트와 암호를 설정할 수 있습니다.

```bash
export MYSQL_TEST_HOST=localhost     # 기본값은 localhost
export MYSQL_PASSWORD=newpassword    # null 비밀번호를 원할 경우 '.' 사용
export PSQL_TEST_HOST=localhost      # 기본값은 localhost
export PSQL_PASSWORD=newpassword     # null 비밀번호를 원할 경우 '.' 사용
export MSSQL_TEST_HOST=localhost     # 기본값은 localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # 기본값은 /tmp/test.db
```