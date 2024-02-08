# PHP 활성 레코드

활성 레코드는 데이터베이스 엔터티를 PHP 객체에 매핑하는 것입니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있으면 해당 테이블의 행을 `User` 클래스와 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하세요.

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
 * ActiveRecord 클래스는 보통 단수로 명명됩니다
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
		// 다음과 같이 설정할 수 있습니다.
		parent::__construct($database_connection, 'users');
		// 또는 이렇게 설정할 수도 있습니다.
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 벌어집니다!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이 예제만을 위한 것입니다. 실제 데이터베이스 연결을 사용할 것입니다.

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli용
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반의 mysqli 사용
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
// 여기서 $user->save()를 사용할 수 없습니다. 그렇게 되면 업데이트로 간주됩니다!

echo $user->id; // 2
```

그저 이렇게하여 새 사용자를 추가하는 것이 간단했습니다! 이제 데이터베이스에 사용자 행이 있는 상태에서 어떻게 데이터를 가져올까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

모든 사용자를 찾는 방법은?

```php
$users = $user->findAll();
```

특정 조건이 있는 경우는 어떨까요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 걸 보세요? 설치하고 시작합시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용

이것은 독립형 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 선택은 여러분의 것입니다.

### 독립형
생성자에 PDO 연결을 전달해야합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이 예제만을 위한 것입니다. 실제 데이터베이스 연결을 사용할 것입니다.

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우에는 ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (하지만 사실 필수는 아닙니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 이를 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## API 참조
### CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. 어떤 종류의 `$id`를 전달하면 해당 값의 기본 키에서 조회를 수행합니다. 값이 전달되지 않으면 테이블에서 첫 번째 레코드를 찾습니다.

또한 테이블을 쿼리하는 데 도움이 되는 다른 도우미 메서드를 전달할 수도 있습니다.

```php
// 이전에 일부 조건으로 레코드 찾기
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드 찾기
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정된 테이블에서 모든 레코드를 찾습니다.

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

더티 데이터는 레코드에서 변경된 데이터를 나타냅니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

//이 시점에서는 아무것도 "더티"가 아닙니다.

$user->email = 'test@example.com'; //지금 'email'은 변경되었으므로 "더티"로 간주됩니다.
$user->update();
//이제 데이터베이스에 업데이트되고 보존되었으므로 더티 데이터가 없습니다.

$user->password = password_hash()'newpassword'); //이제 이것이 더티 상태입니다
$user->dirty(); // 아무것도 전달되지 않으면 모든 더티 항목을 지울 수 있습니다.
$user->update(); // 더티 항목이 캡처되지 않았기 때문에 아무것도 업데이트되지 않습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

### SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 경우 테이블의 일부 열만 선택할 수 있습니다(열이 많은 넓은 테이블에서 성능이 더 좋습니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

원하는 경우 다른 테이블도 선택할 수 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

일부 사용자 지정 where 인수를 설정할 수 있습니다(이 where 문에서 매개변수를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 노트** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`과 같은 작업을 하려는 유혹을 느낄 수 있습니다. 이렇게 하지 마세요!!! 이런 방식은 SQL Injection 공격에 취약합니다. 많은 온라인 문서가 있으니 "sql injection attacks php"를 구글링하여 이 주제에 대한 많은 기사를 찾을 수 있습니다. 이 라이브러리를 사용할 때 이 where() 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 해결하는 것이 올바른 방법입니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

결과를 특정 조건으로 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

일정한 방식으로 반환된 쿼리를 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드의 수를 제한합니다. 두 번째 int가 주어지면 SQL과 마찬가지로 오프셋, 한도를 지정합니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` 조건

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` 조건

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL` 조건

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL` 조건

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value` 조건

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value` 조건

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value` 조건

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value` 조건

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 또는 `field NOT LIKE $value` 조건

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` 또는 `field NOT IN($value)` 조건

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1` 조건

```php
$user->between('id', [1, 2])->find();
```

### 관계
이 라이브러리를 사용하여 여러 종류의 관계를 설정할 수 있습니다. 테이블 사이의 일대다 및 일대일 관계를 설정할 수 있습니다. 이전에 클래스에서 조금의 추가 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만 올바른 구문을 추측하는 것은 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키를 원하는 대로 지을 수 있습니다. ActiveRecord의 이름이 좋을 수도 있습니다. 예: user, contact, client
	'whatever_active_record' => [
		// 필수
		self::HAS_ONE, // 이것은 관계의 유형입니다

		// 필수
		'Some_Class', // 참조할 "다른" ActiveRecord 클래스입니다.

		// 필수
		'local_key', // 참조하는 로컬 키입니다. 
		// FYI: 이는 "다른" 모델의 기본 키에만 조인합니다.

		// 선택 사항
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 실행하려는 사용자 지정 메서드. 원하지 않을 경우 []
		
		// 선택 사항
		'back_reference_name' // 이것은 이 관계를 자체로 다시 참조하려면 필요합니다. 예: $user->contact->user;
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

// 가장 최근 사용자 찾기
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대 방향으로 갈 수 있습니다.
$contact = new Contact();

// 하나의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것이 사용자 이름입니다.
```

정말 멋지죠?

### 사용자 정의 데이터 설정
때로는 템플릿에 전달이 쉬운 사용자 정의 계산(예: 커스텀 계산)과 같은 것을 레코드에 첨부해야 할 수도 있습니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드를 사용하여 사용자 지정 데이터를 첨부합니다.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

그럼 일반 객체 속성처럼 참조할 수 있습니다.

```php
echo $user->page_view_count;
```

### 이벤트

이 라이브러리의 또 다른 멋진 기능은 이벤트입니다. 이벤트는 일정한 메서드에 기반하여 특정 시기에 트리거되며 자동으로 데이터를 설정하는 데 매우 유용합니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

기본 연결을 설정해야 하는 경우 이것은 정말 유용합니다.

```php
// index.php 또는 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected# PHP 활성 레코드

활성 레코드는 데이터베이스 엔터티를 PHP 객체에 매핑하는 것입니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있으면 해당 테이블의 행을 `User` 클래스와 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하세요.

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
 * ActiveRecord 클래스는 보통 단수로 명명됩니다
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
		// 다음과 같이 설정할 수 있습니다.
		parent::__construct($database_connection, 'users');
		// 또는 이렇게 설정할 수도 있습니다.
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 벌어집니다!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이 예제만을 위한 것입니다. 실제 데이터베이스 연결을 사용할 것입니다.

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli용
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반의 mysqli 사용
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
// 여기서 $user->save()를 사용할 수 없습니다. 그렇게 되면 업데이트로 간주됩니다!

echo $user->id; // 2
```

그저 이렇게하여 새 사용자를 추가하는 것이 간단했습니다! 이제 데이터베이스에 사용자 행이 있는 상태에서 어떻게 데이터를 가져올까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

모든 사용자를 찾는 방법은?

```php
$users = $user->findAll();
```

특정 조건이 있는 경우는 어떨까요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 걸 보세요? 설치하고 시작합시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용

이것은 독립형 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 선택은 여러분의 것입니다.

### 독립형
생성자에 PDO 연결을 전달해야합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이 예제만을 위한 것입니다. 실제 데이터베이스 연결을 사용할 것입니다.

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우에는 ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (하지만 사실 필수는 아닙니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 이를 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## API 참조
### CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. 어떤 종류의 `$id`를 전달하면 해당 값의 기본 키에서 조회를 수행합니다. 값이 전달되지 않으면 테이블에서 첫 번째 레코드를 찾습니다.

또한 테이블을 쿼리하는 데 도움이 되는 다른 도우미 메서드를 전달할 수도 있습니다.

```php
// 이전에 일부 조건으로 레코드 찾기
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드 찾기
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정된 테이블에서 모든 레코드를 찾습니다.

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

더티 데이터는 레코드에서 변경된 데이터를 나타냅니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

//이 시점에서는 아무것도 "더티"가 아닙니다.

$user->email = 'test@example.com'; //지금 'email'은 변경되었으므로 "더티"로 간주됩니다.
$user->update();
//이제 데이터베이스에 업데이트되고 보존되었으므로 더티 데이터가 없습니다.

$user->password = password_hash()'newpassword'); //이제 이것이 더티 상태입니다
$user->dirty(); // 아무것도 전달되지 않으면 모든 더티 항목을 지울 수 있습니다.
$user->update(); // 더티 항목이 캡처되지 않았기 때문에 아무것도 업데이트되지 않습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

### SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 경우 테이블의 일부 열만 선택할 수 있습니다(열이 많은 넓은 테이블에서 성능이 더 좋습니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

원하는 경우 다른 테이블도 선택할 수 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

일부 사용자 지정 where 인수를 설정할 수 있습니다(이 where 문에서 매개변수를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 노트** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`과 같은 작업을 하려는 유혹을 느낄 수 있습니다. 이렇게 하지 마세요!!! 이런 방식은 SQL Injection 공격에 취약합니다. 많은 온라인 문서가 있으니 "sql injection attacks php"를 구글링하여 이 주제에 대한 많은 기사를 찾을 수 있습니다. 이 라이브러리를 사용할 때 이 where() 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 해결하는 것이 올바른 방법입니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

결과를 특정 조건으로 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

일정한 방식으로 반환된 쿼리를 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드의 수를 제한합니다. 두 번째 int가 주어지면 SQL과 마찬가지로 오프셋, 한도를 지정합니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` 조건

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` 조건

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL` 조건

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL` 조건

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value` 조건

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value` 조건

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value` 조건

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value` 조건

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 또는 `field NOT LIKE $value` 조건

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` 또는 `field NOT IN($value)` 조건

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1` 조건

```php
$user->between('id', [1, 2])->find();
```

### 관계
이 라이브러리를 사용하여 여러 종류의 관계를 설정할 수 있습니다. 테이블 사이의 일대다 및 일대일 관계를 설정할 수 있습니다. 이전에 클래스에서 조금의 추가 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만 올바른 구문을 추측하는 것은 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키를 원하는 대로 지을 수 있습니다. ActiveRecord의 이름이 좋을 수도 있습니다. 예: user, contact, client
	'whatever_active_record' => [
		// 필수
		self::HAS_ONE, // 이것은 관계의 유형입니다

		// 필수
		'Some_Class', // 참조할 "다른" ActiveRecord 클래스입니다.

		// 필수
		'local_key', // 참조하는 로컬 키입니다. 
		// FYI: 이는 "다른" 모델의 기본 키에만 조인합니다.

		// 선택 사항
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 실행하려는 사용자 지정 메서드. 원하지 않을 경우 []
		
		// 선택 사항
		'back_reference_name' // 이것은 이 관계를 자체로 다시 참조하려면 필요합니다. 예: $user->contact->user;
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

// 가장 최근 사용자 찾기
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대 방향으로 갈 수 있습니다.
$contact = new Contact();

// 하나의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것이 사용자 이름입니다.
```

정말 멋지죠?

### 사용자 정의 데이터 설정
때로는 템플릿에 전달이 쉬운 사용자 정의 계산(예: 커스텀 계산)과 같은 것을 레코드에 첨부해야 할 수도 있습니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드를 사용하여 사용자 지정 데이터를 첨부합니다.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

그럼 일반 객체 속성처럼 참조할 수 있습니다.

```php
echo $user->page_view_count;
```

### 이벤트

이 라이브러리의 또 다른 멋진 기능은 이벤트입니다. 이벤트는 일정한 메서드에 기반하여 특정 시기에 트리거되며 자동으로 데이터를 설정하는 데 매우 유용합니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

기본 연결을 설정해야 하는 경우 이것은 정말 유용합니다.

```php
// index.php 또는 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protectedfunction onConstruct(self $self, array &$config) {
	// default connection 설정이 필요한 경우 여기에 설정합니다.
	$config['connection'] = Flight::db();
	// 또는 다음을 수행할 수도 있습니다.
	$self->transformAndPersistConnection(Flight::db());
	
	// 이렇게도 테이블 이름을 설정할 수 있습니다.
	$config['table'] = 'users';
} 
```