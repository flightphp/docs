# PdoWrapper PDO 도우미 클래스

Flight은 PDO를 위한 도우미 클래스와 함께 제공됩니다. 이를 통해 데이터베이스를 쉽게 쿼리할 수 있으며, 모든 준비/실행/fetchAll() 관련 기능이 포함되어 있습니다. 데이터베이스 쿼리를 간단하게 만들어줍니다. 각 행 결과는 Flight Collection 클래스로 반환되며, 배열 구문이나 객체 구문을 사용하여 데이터에 액세스할 수 있습니다.

## PDO 도우미 클래스 등록

```php
// PDO 도우미 클래스 등록
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## 사용법
이 객체는 PDO를 확장하므로 모든 일반 PDO 메서드를 사용할 수 있습니다. 데이터베이스 쿼리를 보다 쉽게 만들기 위해 다음 메서드가 추가되었습니다:

### `runQuery(string $sql, array $params = []): PDOStatement`
INSERT, UPDATE 또는 while 루프에서 SELECT를 사용할 경우에 사용합니다

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// 또는 데이터베이스에 쓰기
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
쿼리에서 첫 번째 필드를 가져옵니다

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
쿼리에서 한 행을 가져옵니다

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// 또는
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
쿼리에서 모든 행을 가져옵니다

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// 또는
	echo $row->name;
}
```

## `IN()` 구문과 관련된 참고 사항
이 도우미에는 `IN()` 문을 위한 유용한 래퍼도 포함되어 있습니다. `IN()`에 대한 플레이스홀더로 물음표 하나만 전달하고 값을 배열로 전달할 수 있습니다. 이를 이용하면 다음과 같이 보일 수 있습니다:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## 전체 예제

```php
// 예제 경로 및 이 래퍼를 사용하는 방법
Flight::route('/users', function () {
	// 모든 사용자 가져오기
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// 모든 사용자 스트림
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// 또는 echo $user->name;
	}

	// 단일 사용자 가져오기
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// 단일 값 가져오기
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// 도움이 되는 IN() 구문 (IN이 대문자인지 확인해주세요)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// 아래와 같이도 할 수 있습니다
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// 새로운 사용자 삽입
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// 사용자 업데이트
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// 사용자 삭제
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// 영향 받은 행 수 가져오기
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```  