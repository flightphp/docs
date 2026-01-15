# SimplePdo PDO 헬퍼 클래스

## 개요

Flight의 `SimplePdo` 클래스는 PDO를 사용하여 데이터베이스 작업을 수행하기 위한 현대적이고 기능이 풍부한 헬퍼입니다. 이는 `PdoWrapper`를 확장하며, `insert()`, `update()`, `delete()` 및 트랜잭션과 같은 일반적인 데이터베이스 작업을 위한 편리한 헬퍼 메서드를 추가합니다. 데이터베이스 작업을 단순화하고, 결과를 쉽게 접근할 수 있도록 [Collections](/learn/collections)로 반환하며, 고급 사용 사례를 위해 쿼리 로깅과 애플리케이션 성능 모니터링(APM)을 지원합니다.

## 이해

`SimplePdo` 클래스는 PHP에서 데이터베이스 작업을 훨씬 쉽게 만들도록 설계되었습니다. 준비된 문장, 가져오기 모드 및 장황한 SQL 작업을 다루는 대신, 일반적인 작업을 위한 깨끗하고 간단한 메서드를 얻을 수 있습니다. 모든 행은 Collection으로 반환되므로 배열 표기법(`$row['name']`)과 객체 표기법(`$row->name`)을 모두 사용할 수 있습니다.

이 클래스는 `PdoWrapper`의 상위 집합으로, `PdoWrapper`의 모든 기능을 포함하며 코드가 더 깨끗하고 유지보수하기 쉽게 만드는 추가 헬퍼 메서드를 제공합니다. 현재 `PdoWrapper`를 사용 중이라면 `SimplePdo`로 업그레이드하는 것이 간단합니다. 왜냐하면 이는 `PdoWrapper`를 확장하기 때문입니다.

Flight에서 `SimplePdo`를 공유 서비스로 등록할 수 있으며, 앱의 어디서나 `Flight::db()`를 통해 사용할 수 있습니다.

## 기본 사용법

### SimplePdo 등록

먼저 Flight에 `SimplePdo` 클래스를 등록합니다:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **노트**
>
> `PDO::ATTR_DEFAULT_FETCH_MODE`를 지정하지 않으면 `SimplePdo`가 자동으로 `PDO::FETCH_ASSOC`로 설정합니다.

이제 어디서나 `Flight::db()`를 사용하여 데이터베이스 연결을 얻을 수 있습니다.

### 쿼리 실행

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

INSERT, UPDATE 또는 결과를 수동으로 가져오고 싶을 때 사용합니다:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row는 배열입니다
}
```

쓰기 작업에도 사용할 수 있습니다:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

데이터베이스에서 단일 값을 가져옵니다:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): ?Collection`

단일 행을 Collection(배열/객체 접근)으로 가져옵니다:

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// 또는
echo $user->name;
```

> **팁**
>
> `SimplePdo`는 `fetchRow()` 쿼리에 `LIMIT 1`이 이미 없으면 자동으로 추가하여 쿼리를 더 효율적으로 만듭니다.

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

모든 행을 Collection 배열로 가져옵니다:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // 또는
    echo $user->name;
}
```

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

단일 열을 배열로 가져옵니다:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// 반환: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

결과를 키-값 쌍(첫 번째 열을 키로, 두 번째를 값으로)으로 가져옵니다:

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// 반환: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### `IN()` 플레이스홀더 사용

`IN()` 절에서 단일 `?`를 사용하고 배열을 전달할 수 있습니다:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## 헬퍼 메서드

`SimplePdo`의 `PdoWrapper`에 대한 주요 장점 중 하나는 일반적인 데이터베이스 작업을 위한 편리한 헬퍼 메서드의 추가입니다.

### `insert()`

`function insert(string $table, array $data): string`

하나 이상의 행을 삽입하고 마지막 삽입 ID를 반환합니다.

**단일 삽입:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**대량 삽입:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

행을 업데이트하고 영향을 받은 행 수를 반환합니다:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **노트**
>
> SQLite의 `rowCount()`는 데이터가 실제로 변경된 행 수를 반환합니다. 이미 동일한 값으로 행을 업데이트하면 `rowCount()`가 0을 반환합니다. 이는 `PDO::MYSQL_ATTR_FOUND_ROWS`를 사용할 때 MySQL의 동작과 다릅니다.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

행을 삭제하고 삭제된 행 수를 반환합니다:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

트랜잭션 내에서 콜백을 실행합니다. 트랜잭션은 성공 시 자동 커밋되거나 오류 시 롤백됩니다:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

콜백 내에서 예외가 발생하면 트랜잭션이 자동으로 롤백되고 예외가 다시 발생합니다.

## 고급 사용법

### 쿼리 로깅 & APM

쿼리 성능을 추적하려면 등록 시 APM 추적을 활성화합니다:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* PDO 옵션 */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

쿼리를 실행한 후 수동으로 로깅할 수 있지만, 활성화되면 APM이 자동으로 로깅합니다:

```php
Flight::db()->logQueries();
```

이것은 연결 및 쿼리 메트릭과 함께 이벤트(`flight.db.queries`)를 트리거하며, Flight의 이벤트 시스템을 사용하여 이를 수신할 수 있습니다.

### 전체 예제

```php
Flight::route('/users', function () {
    // 모든 사용자 가져오기
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // 모든 사용자 스트림
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // 단일 사용자 가져오기
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // 단일 값 가져오기
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // 단일 열 가져오기
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // 키-값 쌍 가져오기
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // 특수 IN() 구문
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // 새 사용자 삽입
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // 대량 사용자 삽입
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // 사용자 업데이트
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // 사용자 삭제
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // 트랜잭션 사용
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## PdoWrapper에서 마이그레이션

현재 `PdoWrapper`를 사용 중이라면 `SimplePdo`로 마이그레이션하는 것이 간단합니다:

1. **등록 업데이트:**
   ```php
   // 이전
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // 새
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **기존 `PdoWrapper` 메서드가 `SimplePdo`에서 모두 작동** - 변경 사항이 없습니다. 기존 코드가 계속 작동합니다.

3. **새 헬퍼 메서드 선택적으로 사용** - 코드를 단순화하기 위해 `insert()`, `update()`, `delete()`, `transaction()`을 사용하기 시작하세요.

## 관련 자료

- [Collections](/learn/collections) - 쉬운 데이터 접근을 위한 Collection 클래스 사용법을 배웁니다.
- [PdoWrapper](/learn/pdo-wrapper) - 레거시 PDO 헬퍼 클래스 (사용 중단됨).

## 문제 해결

- 데이터베이스 연결 오류가 발생하면 DSN, 사용자 이름, 비밀번호 및 옵션을 확인하세요.
- 모든 행은 Collection으로 반환됩니다—일반 배열이 필요하면 `$collection->getData()`를 사용하세요.
- `IN (?)` 쿼리의 경우 배열을 전달했는지 확인하세요.
- 장기 실행 프로세스에서 쿼리 로깅으로 인한 메모리 문제를 겪는다면 `maxQueryMetrics` 옵션을 조정하세요.

## 변경 로그

- v3.18.0 - insert, update, delete 및 트랜잭션 헬퍼 메서드가 포함된 SimplePdo의 초기 릴리스.