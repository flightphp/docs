# PHP 활성 레코드

활성 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말해, 데이터베이스에 사용자 테이블이 있다면 해당 테이블의 한 행을 `User` 클래스와 코드베이스의 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하십시오.

## 기본 예제

다음 테이블이 있다고 가정해 봅시다:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

이제 이 테이블을 나타내는 새 클래스를 설정할 수 있습니다:

```php
/**
 * 활성 레코드 클래스는 일반적으로 단수형입니다
 * 
 * 여기에 테이블의 속성을 주석으로 추가하는 것이 매우 권장됩니다
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 이렇게 설정할 수 있습니다
		parent::__construct($database_connection, 'users');
		// 또는 이렇게도 가능합니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 벌어집니다!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며, 실제 데이터베이스 연결을 사용할 것입니다

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli로
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반이 아닌 mysqli
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = '보비 테이블스';
$user->password = password_hash('멋진 비밀번호');
$user->insert();
// 또는 $user->save();

echo $user->id; // 1

$user->name = '조셉 맘마';
$user->password = password_hash('다시 멋진 비밀번호!!!');
$user->insert();
// 여기서는 $user->save()를 사용할 수 없습니다!

echo $user->id; // 2
```

새 사용자를 추가하는 것이 그냥 쉬웠죠! 이제 데이터베이스에 사용자 행이 있으므로 어떻게 빼낼까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아서 반환합니다.
echo $user->name; // '보비 테이블스'
```

그리고 모든 사용자를 찾으려면 어떻게 해야 할까요?

```php
$users = $user->findAll();
```

특정 조건으로 찾을 때는 어떻게 할까요?

```php
$users = $user->like('name', '%맘마%')->findAll();
```

어떤 재미가 있어 보이나요? 설치하고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이 라이브러리는 독립형 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 당신의 선택입니다.

### 독립형
생성자에 PDO 연결을 전달해야 합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며, 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (그럴 필요는 없지만).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 이렇게 컨트롤러나 함수 등에서 사용할 수 있습니다.

Flight::user()->find(1);
```

## CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. 어떤 `$id`를 전달하면 해당 값을 가진 기본 키로 조회를 수행합니다. 아무 것도 전달하지 않으면 테이블의 첫 번째 레코드를 찾을 것입니다.

또한 테이블을 쿼리하기 전에 다른 헬퍼 메서드를 전달할 수 있습니다.

```php
// 이전에 일부 조건으로 레코드 찾기
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드 찾기
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정한 테이블에서 모든 레코드를 찾습니다.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

현재 레코드가 추출되었는지 확인합니다.

```php
$user->find(1);
// 데이터가 포함된 레코드가 있다면...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = '데모';
$user->password = md5('데모');
$user->insert();
```

##### 텍스트 기반 기본 키

텍스트 기반 기본 키(예: UUID)가 있는 경우 삽입 전에 기본 키 값을 설정할 수 있습니다.

두 가지 방법 중 하나로 삽입하기 전에 기본 키 값을 설정할 수 있습니다.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = '일부 uuid';
$user->name = '데모';
$user->password = md5('데모');
$user->insert(); // 또는 $user->save();
```

또는 이벤트를 통해 기본 키를 자동 생성할 수 있습니다.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 위의 배열 대신 이렇게도 primaryKey를 설정할 수 있습니다.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 또는 고유한 ID를 생성하는 방법에 따라 달라집니다
	}
}
```

삽입 전에 기본 키를 설정하지 않으면 `rowid`로 설정되며 데이터베이스가 생성하지만 테이블에 없을 수 있기 때문에 영구적이지 않습니다. 이러한 경우에는 이를 자동으로 처리하는 이벤트를 사용하는 것이 권장되므로 테이블에 존재하지 않는다고 할지라도 데이터베이스가 생성된다.

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에서 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입하거나 업데이트합니다. 레코드에 id가 있으면 업데이트되고 그렇지 않으면 삽입됩니다.

```php
$user = new User($pdo_connection);
$user->name = '데모';
$user->password = md5('데모');
$user->save();
```

**참고:** 클래스에 정의된 관계가 있다면, 정의되고 인스턴스화되었으며 업데이트할 데이터가 있는 경우 해당 관계를 재귀적으로 업데이트할 것입니다. (v0.4.0 이상)

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

조회 후 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

변경되었던 데이터를 나타냅니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서는 아무 것도 "dirty"가 아닙니다.

$user->email = 'test@example.com'; // 이제 email이 변경되어 "dirty"로 간주됩니다.
$user->update();
// 현재 데이터가 dirty인 것이 없습니다. 업데이트되었고 데이터베이스에 지속되었기 때문입니다.

$user->password = password_hash()'새로운 비밀번호'); // 이제 dirty 상태입니다
$user->dirty(); // 아무 것도 dirty로 캡처되지 않기 때문에 전달하지 않습니다.
$user->update(); // dirty로 캡처된 것이 없으므로 업데이트되지 않습니다.

$user->dirty([ 'name' => '무언가', 'password' => password_hash('다른 비밀번호') ]);
$user->update(); // name과 password가 업데이트됩니다.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

이는 `dirty()` 메서드의 별칭입니다. 수행할 작업이 더 분명하세요.

```php
$user->copyFrom([ 'name' => '무언가', 'password' => password_hash('다른 비밀번호') ]);
$user->update(); // name과 password가 업데이트됩니다.
```

#### `isDirty(): boolean` (v0.4.0)

현재 레코드가 변경되었는지 확인합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 재설정합니다. 반복 타입 동작에서 사용하기에 매우 좋습니다.
`true`를 전달하면 현재 객체를 찾는 데 사용된 쿼리 데이터도 재설정합니다(기본 동작).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 깔끔한 상태로 시작
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, 또는 `save()` 메서드를 실행한 후 빌드된 SQL을 얻어와 디버깅 목적으로 사용할 수 있습니다.

## SQL 쿼리 메서드

#### `select(string $field1 [, string $field2 ... ])`

테이블의 일부 칼럼만 선택할 수 있습니다(매우 넓은 테이블에게 맢).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

기술적으로 다른 테이블을 선택할 수도 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다(이 where 문에서는 params를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 관련 참고사항** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`와 같이 작성하려는 유혹을 느낄 수 있습니다. 이렇게 사용하지 마십시오! 이것은 SQL 인젝션 공격에 노출되기 쉽습니다. 온라인 더 많은 정보를 얻으려면 구글에서 "sql 인젝션 공격 php"를 검색하면 많은 기사를 볼 수 있습니다. 이 라이브러리와 함께는 이 `where()` 메서드 대신, `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 좀 더 안전한 방식으로 처리해야합니다. 반드시 이를 해야 하는 경우 `PDO` 라이브러리에 `$pdo->quote($var)`를 사용하여 이스케이프해야합니다. `quote()`를 사용한 후에 `where()` 문에서 사용할 수 있습니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건으로 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

특정 방법으로 반환된 쿼리를 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환된 레코드 수를 제한합니다. 두 번째 int이 주어지면 오프셋, SQL과 동일하게 한정, 제한합니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 조건

#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 또는 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn progress...