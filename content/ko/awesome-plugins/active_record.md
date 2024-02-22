# FlightPHP 액티브 레코드

액티브 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말하면, 데이터베이스에 사용자 테이블이 있는 경우 해당 테이블의 행을 `User` 클래스와 코드베이스의 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

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
 * 액티브 레코드 클래스는 보통 단수형으로 표기됩니다
 * 
 * 여기에 테이블의 속성을 설명하는 주석을 추가하는 것이 매우 권장됩니다
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 다음과 같이 설정할 수 있습니다
		parent::__construct($database_connection, 'users');
		// 또는 이와 같이 설정할 수 있습니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 일어납니다!

```php
// sqlite의 경우
$database_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며 실제 데이터베이스 연결을 사용할 것입니다

// mysql의 경우
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli의 경우
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반이 아닌 mysqli의 경우
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// 또는 $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// 여기서 $user->save()를 사용할 수 없습니다. 그렇게 하면 업데이트로 인식됩니다!

echo $user->id; // 2
```

그저 새로운 사용자를 추가하는 것이 이렇게 쉽습니다! 이제 데이터베이스에 사용자 행이 있으므로 이를 어떻게 가져오나요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾고 싶다면요?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 걸 확인하셨나요? 설치하고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이것은 독립형 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 사용하는 방법은 여러분의 선택입니다.

### 독립형
생성자에 PDO 연결을 전달하면 됩니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (하지만 사실 할 필요는 없습니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그리고 이와 같이 컨트롤러 또는 함수에서 사용할 수 있습니다.

Flight::user()->find(1);
```

## API 참조
### CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. 특정 `$id`를 전달하면 해당 값의 기본 키에 대한 조회를 수행합니다. 아무 것도 전달하지 않으면 테이블에서 첫 번째 레코드만 찾습니다.

또한 테이블을 쿼리하는 데 도움이 되는 다른 헬퍼 메서드를 전달할 수 있습니다.

```php
// 사전에 일부 조건으로 레코드 찾기
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

#### `insert(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에서 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

사전에 검색한 다음 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Dirty" 데이터는 레코드에서 변경된 데이터를 나타냅니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서 아무 것도 "dirty"되지 않습니다.

$user->email = 'test@example.com'; // 이제 이메일은 변경되어 "dirty"로 간주됩니다.
$user->update();
// 데이터가 "dirty"되었기 때문에 업데이트되고 데이터베이스에 저장되었습니다.

$user->password = password_hash()'newpassword'); // 이제 이것이 "dirty"입니다
$user->dirty(); // 아무것도 "dirty"로 기록되지 않았기 때문에 아무 것도 업데이트되지 않을 것입니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 초기화합니다. 이것은 루프 유형의 동작에서 사용하기에 좋습니다.
`true`를 전달하면 현재 객체를 찾을 때 사용된 쿼리 데이터도 재설정됩니다 (기본 동작).

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

### SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 경우 테이블의 일부 열만 선택할 수 있습니다 (매우 많은 열을 가진 넓은 테이블에서 성능이 향상됩니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

원하는 경우 다른 테이블도 선택할 수 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스에서 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다 (이 where 문에서는 매개변수를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 참고** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`과 같은 작업을 할 가능성이 있습니다. 이를 사용하지 마십시오!!! 이렇게 하면 SQL Injection 공격의 대상이 됩니다. 온라인에서 많은 기사를 찾을 수 있으니 "sql injection attacks php"를 Google에서 검색하세요. 이 라이브러리로 이를 처리하는 올바른 방법은 `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 하는 것입니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건으로 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

쿼리의 반환을 특정 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드 수를 제한합니다. 두 번째 int를 제공하면 SQL과 마찬가지로 offset, limit이 적용됩니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = value`와 같은 where 조건입니다.

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> value`와 같은 where 조건입니다.

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`과 같은 where 조건입니다.

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`과 같은 where 조건입니다.

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > value`와 같은 where 조건입니다.

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < value`와 같은 where 조건입니다.

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= value`와 같은 where 조건입니다.

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= value`와 같은 where 조건입니다.

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE value` 또는 `field NOT LIKE value`와 같은 where 조건입니다.

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN(value)` 또는 `field NOT IN(value)`와 같은 where 조건입니다.

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN value AND value`와 같은 where 조건입니다.

```php
$user->between('id', [1, 2])->find();
```

### 관계
이 라이브러리를 사용하여 다양한 종류의 관계를 설정할 수 있습니다. 테이블 간의 일대다 및 일대일 관계를 설정할 수 있습니다. 이는 클래스에 사전 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만 올바른 구문을 추측하는 것이 혼란스럽습니다.

```php
protected array $relations = [
	// 키 이름을 자유롭게 지정할 수 있습니다. ActiveRecord의 이름이 좋을 것입니다. 예: user, contact, client
	'whatever_active_record' => [
		// 필수
		self::HAS_ONE, // 이것은 관계 유형입니다

		// 필수
		'Some_Class', // 이것은 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		'local_key', // 조인을 참조하는 local_key입니다
		// 이것은 "다른" 모델의 기본 키에만 조인합니다.

		// 선택 사항
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 실행하려는 사용자 정의 메서드. 필요하지 않은 경우 []
        
		// 선택 사항
		'back_reference_name' // 이 관계를 자기 자신으로 다시 참조하려면 이를 추가합니다. 예컨대 $user->contact->user;
	];
];
```

```php
class User extends ActiveRecord {
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord {
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'contacts');
	}
}
```

이제 참조를 설정했으므로 매우 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 최신 사용자 찾기.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 역방향으로 갈 수도 있습니다.
$contact = new Contact();

// 한 개의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것이 사용자 이름입니다
```

정말 멋지죠?

### 사용자 정의 데이터 설정
가끔씩 고유한 것을 ActiveRecord에 첨부해야 할 수도 있습니다. 예를 들어 템플릿에 전달하기 쉬운 사용자 정의 계산을 첨부하는 경우입니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드로 사용자 정의 데이터를 첨부합니다.
```php
$user->setCustomData('page# FlightPHP 액티브 레코드

액티브 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말하면, 데이터베이스에 사용자 테이블이 있는 경우 해당 테이블의 행을 `User` 클래스와 코드베이스의 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

## 기본 예제

다음과 같은 테이블이 있다고 가정합니다:

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
 * 액티브 레코드 클래스는 보통 단수형으로 표기됩니다
 * 
 * 여기에 테이블의 속성을 설명하는 주석을 추가하는 것이 매우 권장됩니다
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 다음과 같이 설정할 수 있습니다
		parent::__construct($database_connection, 'users');
		// 또는 이와 같이 설정할 수 있습니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 일어납니다!

```php
// sqlite의 경우
$database_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며 실제 데이터베이스 연결을 사용할 것입니다

// mysql의 경우
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli의 경우
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반 아닌 mysqli의 경우
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// 또는 $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// 여기서 $user->save()를 사용할 수 없습니다. 그렇게 하면 업데이트로 인식됩니다!

echo $user->id; // 2
```

그저 새로운 사용자를 추가하는 것이 이렇게 쉽습니다! 이제 데이터베이스에 사용자 행이 있으므로 이를 어떻게 가져오나요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾고 싶다면요?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 걸 확인하셨나요? 설치하고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이것은 독립형 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 사용하는 방법은 여러분의 선택입니다.

### 독립형
생성자에 PDO 연결을 전달하면 됩니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (하지만 사실 할 필요는 없습니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그리고 이와 같이 컨트롤러 또는 함수에서 사용할 수 있습니다.

Flight::user()->find(1);
```

## API 참조
### CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. 특정 `$id`를 전달하면 해당 값의 기본 키에 대한 조회를 수행합니다. 아무 것도 전달하지 않으면 테이블에서 첫 번째 레코드만 찾습니다.

또한 테이블을 쿼리하는 데 도움이 되는 다른 헬퍼 메서드를 전달할 수 있습니다.

```php
// 사전에 일부 조건으로 레코드 찾기
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

#### `insert(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에서 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

사전에 검색한 다음 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Dirty" 데이터는 레코드에서 변경된 데이터를 나타냅니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서 아무 것도 "dirty"되지 않습니다.

$user->email = 'test@example.com'; // 이제 이메일은 변경되어 "dirty"로 간주됩니다.
$user->update();
// 데이터가 "dirty"되었기 때문에 업데이트되고 데이터베이스에 저장되었습니다.

$user->password = password_hash()'newpassword'); // 이제 이것이 "dirty"입니다
$user->dirty(); // 아무것도 "dirty"로 기록되지 않았기 때문에 아무 것도 업데이트되지 않을 것입니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 초기화합니다. 이것은 루프 유형의 동작에서 사용하기에 좋습니다.
`true`를 전달하면 현재 객체를 찾을 때 사용된 쿼리 데이터도 재설정됩니다 (기본 동작).

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

### SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 경우 테이블의 일부 열만 선택할 수 있습니다 (매우 많은 열을 가진 넓은 테이블에서 성능이 향상됩니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

원하는 경우 다른 테이블도 선택할 수 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스에서 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다 (이 where 문에서는 매개변수를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 참고** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`과 같은 작업을 할 가능성이 있습니다. 이를 사용하지 마십시오!!! 이렇게 하면 SQL Injection 공격의 대상이 됩니다. 온라인에서 많은 기사를 찾을 수 있으니 "sql injection attacks php"를 Google에서 검색하세요. 이 라이브러리로 이를 처리하는 올바른 방법은 `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 하는 것입니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건으로 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

쿼리의 반환을 특정 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드 수를 제한합니다. 두 번째 int를 제공하면 SQL과 마찬가지로 offset, limit이 적용됩니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = value`와 같은 where 조건입니다.

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> value`와 같은 where 조건입니다.

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`와 같은 where 조건입니다.

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`와 같은 where 조건입니다.

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > value`와 같은 where 조건입니다.

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < value`와 같은 where 조건입니다.

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= value`와 같은 where 조건입니다.

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= value`와 같은 where 조건입니다.

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE value` 또는 `field NOT LIKE value`와 같은 where 조건입니다.

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN(value)` 또는 `field NOT IN(value)`와 같은 where 조건입니다.

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN value AND value`와 같은 where 조건입니다.

```php
$user->between('id', [1, 2])->find();
```

### 관계
이 라이브러리를 사용하여 다양한 종류의 관계를 설정할 수 있습니다. 테이블 간의 일대다 및 일대일 관계를 설정할 수 있습니다. 이는 클래스에 사전 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만 올바른 구문을 추측하는 것이 혼란스럽습니다.

```php
protected array $relations = [
	// 키 이름을 자유롭게 지정할 수 있습니다. ActiveRecord의 이름이 좋을 것입니다. 예: user, contact, client
	'whatever_active_record' => [
		// 필수
		self::HAS_ONE, // 이것은 관계 유형입니다

		// 필수
		'Some_Class', // 이것은 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		'local_key', // 조인을 참조하는 local_key입니다
		// 이것은 "다른" 모델의 기본 키에만 조인합니다.

		// 선택 사항
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 실행하려는 사용자 정의 메서드. 필요하지 않은 경우 []
        
		// 선택 사항
		'back_reference_name' // 이 관계를 자기 자신으로 다시 참조하려면 이를 추가합니다. 예컨대 $user->contact->user;
	];
];
```

```php
class User extends ActiveRecord {
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord {
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'contacts');
	}
}
```

이제 참조를 설정했으므로 매우 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 최신 사용자 찾기.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 역방향으로 갈 수도 있습니다.
$contact = new Contact();

// 한 개의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것이 사용자 이름입니다
```

정말 멋지죠?

### 사용자 정의 데이터 설정
가끔씩 고유한 것을 ActiveRecord에 첨부해야 할 수도 있습니다. 예를 들어 템플릿에 전달하기 쉬운 사용자 정의 계산을 첨부하는 경우입니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드로 사용자 정의 데이터를 첨부합니다.
```php
$user->setCustomData('page_view_count',$page_view_count);```

그럼 이상입니다!