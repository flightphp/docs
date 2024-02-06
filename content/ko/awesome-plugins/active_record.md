# FlightPHP 액티브 레코드

액티브 레코드는 데이터베이스 엔티티를 PHP 객체로 매핑하는 것을 말합니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있다면 해당 테이블의 행을 `User` 클래스 및 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하십시오.

## 기본 예제

다음과 같은 테이블이 있다고 가정해 봅시다:

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
 * 액티브 레코드 클래스는 일반적으로 단수형입니다
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
		// 또는 이렇게 설정할 수도 있습니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 시작됩니다!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이것은 예제일 뿐이며 일반적으로 실제 데이터베이스 연결을 사용할 것입니다

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli용
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반 생성이 아닌 mysqli용
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
// 여기서 $user->save()를 사용할 수 없습니다. 그렇게 하면 업데이트로 인식될 것입니다!

echo $user->id; // 2
```

새 사용자를 추가하는 것이 이렇게 쉽습니다! 이제 데이터베이스에 사용자 행이 있으므로 어떻게 가져올까요?

```php
$user->find(1); // 데이터베이스에서 id = 1인 레코드를 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾고 싶다면?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 게 보이시나요? 설치해 보고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이것은 독립 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 당신의 몫입니다.

### 독립

생성자에 PDO 연결을 전달해야합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예제일 뿐이며 일반적으로 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크

Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있습니다(하지만 꼭 해야 할 필요는 없습니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## API 참조
### CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 개체에 할당합니다. 지정된 `$id` 값으로 기본 키에서 조회를 수행합니다. 아무 값도 전달되지 않으면 테이블에서 첫 번째 레코드만 찾습니다.

더하여 테이블을 쿼리하는 다른 도우미 메서드를 전달할 수도 있습니다.

```php
// 이전에 일부 조건으로 레코드를 찾습니다
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드를 찾습니다
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정된 테이블의 모든 레코드를 찾습니다.

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

#### `dirty(array  $dirty = []): ActiveRecord`

변경된 데이터를 가리킵니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서 아무것도 "dirty"되지 않았습니다.

$user->email = 'test@example.com'; // 지금 이메일은 "dirty"로 간주되어 변경되었음.
$user->update();
// 이제 데이터베이스에 업데이트되어 변경되는 데이터가 없으므로 "dirty" 데이터가 없습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 암호가 모두 업데이트됩니다.
```

### SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

테이블에서 일부 열만 선택할 수 있습니다(많은 열이있는 넓은 테이블에서 더 성능이 뛰어납니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

다른 테이블을 선택할 수도 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다(이 where 문에 파라미터를 설정할 수 없습니다)

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 주의** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`과 같은 작업을 하고 싶을 수 있습니다. 이렇게 하지 마십시오!!! 이는 SQL 주입 공격에 취약합니다. 이 라이브러리를 사용할 때는 이러한 `where()` 방식 대신 `$user->eq('id', $id)->eq('name', $name)->find();`과 같이 더 나은 방법이 있습니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건에 따라 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

특정 방식으로 반환된 쿼리를 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드의 수를 제한합니다. 두 번째 int가 주어지면 SQL과 같은 오프셋, 제한으로 작동합니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`에 대한 where 조건입니다.

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`에 대한 where 조건입니다.

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`에 대한 where 조건입니다.

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`에 대한 where 조건입니다.

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`에 대한 where 조건입니다.

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`에 대한 where 조건입니다.

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`에 대한 where 조건입니다.

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`에 대한 where 조건입니다.

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 또는 `field NOT LIKE $value`에 대한 where 조건입니다.

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` 또는 `field NOT IN($value)`에 대한 where 조건입니다.

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1`에 대한 where 조건입니다.

```php
$user->between('id', [1, 2])->find();
```

### 관계

이 라이브러리를 사용하여 여러 가지 종류의 관계를 설정할 수 있습니다. 테이블 간에 one->many 및 one->one 관계를 설정할 수 있습니다. 이전에 클래스에서 약간의 추가 설정이 필요합니다.

`$relations` 배열을 설정하는 것이 어렵지는 않지만 올바른 구문을 추측하는 것이 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키를 원하는 대로 지을 수 있습니다. ActiveRecord의 이름이 좋을 수도 있습니다. 예: user, contact, client
	'whatever_active_record' => [
		// 필수
		self::HAS_ONE, // 관계 유형입니다

		// 필수
		'Some_Class', // 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		'local_key', // 결합을 참조하는 로컬 키입니다.
		// 참고로, "다른" 모델의 주 키에만 결합됩니다

		// 선택 사항
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 실행하려는 사용자 지정 메서드들입니다. 사용하지 않으려면 [] 사용

		// 선택 사항
		'back_reference_name' // 이 관계를 자체로 돌아오는 back reference로 만들고 싶다면
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord{
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

이제 참조가 설정되었으므로 매우 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 가장 최근 사용자 찾기.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대로 이동할 수도 있습니다.
$contact = new Contact();

// 하나의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것은 사용자 이름입니다
```

정말 멋지죠?

### 사용자 정의 데이터 설정
가끔은 템플릿에 전달하기 쉽게 사용자 정의 계산과 같이 ActiveRecord에 고유한 것을 첨부해야 할 수 있습니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드로 사용자 지정 데이터를 첨부합니다.

```php
$user->setCustomData('page_view_count', $page_view_count);
```

그런 다음 간단히 일반 객체 속성처럼 참조하면 됩니다.

```php
echo $user->page_view_count;
```

### 이벤트

이 라이브러리의 하나 더 놀라운 기능은 이벤트입니다. 이벤트는 특정 메서드를 호출하는 시간과 시점에 따라 트리거됩니다. 데이터를 자동으로 설정하는 데 매우 도움이 되는 기능입니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

이는 기본 연결을 설정해야하는 경우에 매우 유용합니다.

```php
// index.php 또는 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // & 참조를 잊지 마십시오
		// 연결을 자동으로 설정할 수 있습니다
		$config['connection'] = Flight::db();
		// 또는 이렇게
		$self->transformAndPersistConnection(Flight::db());
		
		// 이렇게도 테이블 이름을 설정할 수 있습니다.
		$config['table'] = 'users';
	} 
}
```

```

# FlightPHP 액티브 레코드

액티브 레코드는 데이터베이스 엔티티를 PHP 객체로 매핑하는 것을 말합니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있다면 해당 테이블의 행을 `User` 클래스 및 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하십시오.

## 기본 예제

다음과 같은 테이블이 있다고 가정해 봅시다:

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
 * 액티브 레코드 클래스는 일반적으로 단수형입니다
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
		// 또는 이렇게 설정할 수도 있습니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 시작됩니다!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이것은 예제일 뿐이며 일반적으로 실제 데이터베이스 연결을 사용할 것입니다

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli용
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반 생성이 아닌 mysqli용
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
// 여기서 $user->save()를 사용할 수 없습니다. 그렇게 하면 업데이트로 인식될 것입니다!

echo $user->id; // 2
```

새 사용자를 추가하는 것이 이렇게 쉽습니다! 이제 데이터베이스에 사용자 행이 있으므로 어떻게 가져올까요?

```php
$user->find(1); // 데이터베이스에서 id = 1인 레코드를 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾고 싶다면?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 게 보이시나요? 설치해 보고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이것은 독립 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 당신의 몫입니다.

### 독립

생성자에 PDO 연결을 전달해야합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예제일 뿐이며 일반적으로 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크

Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있습니다(하지만 꼭 해야 할 필요는 없습니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## API 참조
### CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 개체에 할당합니다. 지정된 `$id` 값으로 기본 키에서 조회를 수행합니다. 아무 값도 전달되지 않으면 테이블에서 첫 번째 레코드만 찾습니다.

더하여 테이블을 쿼리하는 다른 도우미 메서드를 전달할 수도 있습니다.

```php
// 이전에 일부 조건으로 레코드를 찾습니다
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드를 찾습니다
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정된 테이블의 모든 레코드를 찾습니다.

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

#### `dirty(array  $dirty = []): ActiveRecord`

변경된 데이터를 가리킵니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서 아무것도 "dirty"되지 않았습니다.

$user->email = 'test@example.com'; // 지금 이메일은 "dirty"로 간주되어 변경되었음.
$user->update();
// 이제 데이터베이스에 업데이트되어 변경되는 데이터가 없으