# PdoWrapper PDO 도우미 클래스

## 개요

Flight의 `PdoWrapper` 클래스는 PDO를 사용하여 데이터베이스 작업을 수행하는 데 친근한 도우미입니다. 일반적인 데이터베이스 작업을 간소화하고, 결과를 가져오는 데 유용한 메서드를 추가하며, 결과를 쉽게 접근할 수 있도록 [Collections](/learn/collections)로 반환합니다. 또한 고급 사용 사례를 위해 쿼리 로깅과 애플리케이션 성능 모니터링(APM)을 지원합니다.

## 이해하기

PHP에서 데이터베이스를 사용하는 것은 PDO를 직접 사용할 때 특히 장황할 수 있습니다. `PdoWrapper`는 PDO를 확장하여 쿼리, 가져오기, 결과 처리 작업을 훨씬 쉽게 만드는 메서드를 추가합니다. 준비된 문장과 가져오기 모드를 다루는 대신, 일반적인 작업에 대한 간단한 메서드를 사용하며, 모든 행이 Collection으로 반환되므로 배열 또는 객체 표기법을 사용할 수 있습니다.

Flight에서 `PdoWrapper`를 공유 서비스로 등록한 후, 앱의 어디서나 `Flight::db()`를 통해 사용할 수 있습니다.

## 기본 사용법

### PDO 도우미 등록

먼저 Flight에 `PdoWrapper` 클래스를 등록합니다:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

이제 어디서나 `Flight::db()`를 사용하여 데이터베이스 연결을 가져올 수 있습니다.

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

`function fetchRow(string $sql, array $params = []): Collection`

단일 행을 Collection(배열/객체 접근)으로 가져옵니다:

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// 또는
echo $user->name;
```

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

### `IN()` 플레이스홀더 사용

`IN()` 절에서 단일 `?`를 사용하고 배열 또는 쉼표로 구분된 문자열을 전달할 수 있습니다:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// 또는
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## 고급 사용법

### 쿼리 로깅 & APM

쿼리 성능을 추적하려면 등록 시 APM 추적을 활성화합니다:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* 옵션 */], true // 마지막 매개변수가 APM을 활성화합니다
]);
```

쿼리를 실행한 후 수동으로 로깅할 수 있지만, 활성화된 경우 APM이 자동으로 로깅합니다:

```php
Flight::db()->logQueries();
```

이것은 연결 및 쿼리 메트릭과 함께 이벤트(`flight.db.queries`)를 발생시키며, Flight의 이벤트 시스템을 사용하여 이를 수신할 수 있습니다.

### 전체 예제

```php
Flight::route('/users', function () {
    // 모든 사용자 가져오기
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // 모든 사용자 스트리밍
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // 단일 사용자 가져오기
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // 단일 값 가져오기
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // 특수 IN() 구문
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // 새 사용자 삽입
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // 사용자 업데이트
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // 사용자 삭제
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // 영향을 받은 행 수 가져오기
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## 관련 항목

- [Collections](/learn/collections) - 쉬운 데이터 접근을 위한 Collection 클래스 사용법을 알아보세요.

## 문제 해결

- 데이터베이스 연결 오류가 발생하면 DSN, 사용자 이름, 비밀번호 및 옵션을 확인하세요.
- 모든 행은 Collection으로 반환됩니다. 일반 배열이 필요하면 `$collection->getData()`를 사용하세요.
- `IN (?)` 쿼리의 경우 배열 또는 쉼표로 구분된 문자열을 전달했는지 확인하세요.

## 변경 로그

- v3.2.0 - 기본 쿼리 및 가져오기 메서드가 포함된 PdoWrapper의 초기 릴리스.