# Flight Active Record 

활성 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있다면, 해당 테이블의 행을 `User` 클래스와 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

## 기본 예제

다음 테이블이 있다고 가정해 봅시다:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

이제 이 테이블을 나타내는 새로운 클래스를 설정할 수 있습니다:

```php
/**
 * ActiveRecord 클래스는 일반적으로 단수형으로 지정됩니다
 * 
 * 여기 테이블의 속성을 주석으로 추가하는 것이 매우 권장됩니다
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 이 방법으로 설정할 수 있습니다
		parent::__construct($database_connection, 'users');
		// 또는 이 방법으로도 가능합니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 벌어집니다!

```php
// sqlite인 경우
$database_connection = new PDO('sqlite:test.db'); // 이 예시에만 해당합니다, 실제 데이터베이스 연결을 사용할 것입니다

// mysql인 경우
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli인 경우
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체를 사용하지 않는 mysqli인 경우
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
// 여기서 $user->save()를 사용하면 업데이트로 인식합니다!

echo $user->id; // 2
```

그리고 이렇게 간단하게 새로운 사용자를 추가했습니다! 이제 데이터베이스에 사용자 행이 있으므로, 어떻게 데이터를 검색할까요?

```php
$user->find(1); // 데이터베이스에서 id = 1인 것을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾고 싶다면?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 것을 보셨나요? 설치하고 시작해 봅시다!

## 설치

Composer를 사용하여 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이 라이브러리는 독립적으로 또는 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 당신의 몫입니다.

### 독립적으로
생성자에 PDO 연결을 전달하기만 하면 됩니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이 예시에만 해당합니다, 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우, ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (하지만 사실 필수는 아닙니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

레코드 하나를 찾아 현재 객체에 할당합니다. 특정 `$id`를 전달하면 해당 값의 기본 키를 기준으로 조회를 수행합니다. 아무 것도 전달되지 않으면 테이블에서 첫 번째 레코드를 찾습니다.

추가로 다른 도우미 메서드를 통해 테이블을 쿼리할 수 있습니다.

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

검색 전에 검색하여 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

더티 데이터는 레코드에서 변경된 데이터를 의미합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 여기까지는 "더티"가 없습니다.

$user->email = 'test@example.com'; // 이제 이메일은 변경되어 "더티"로 간주됩니다.
$user->update();
// 이제 업데이트되어 데이터베이스에 유지되어 더티 데이터가 없어집니다.

$user->password = password_hash()'newpassword'); // 이제 더티됩니다
$user->dirty(); // 아무것도 전달하지 않으면 모든 더티 항목이 지워집니다.
$user->update(); // 더티로 캡처된 것이 없으므로 아무 변화도 일어나지 않습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 재설정합니다. 이는 반복 유형의 동작에서 사용하면 좋습니다.
`true`를 전달하면 현재 레코드를 찾을 때 사용된 쿼리 데이터도 재설정합니다 (기본 동작).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 새로 시작
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

## SQL 질의 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 컬럼만 선택할 수 있습니다 (많은 컬럼이 있는 넓은 테이블에서 성능면에서 유리합니다)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

원하는 다른 테이블을 선택할 수 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블과 조인할 수 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 지정 where 인수를 설정할 수 있습니다 (이 where 문에서는 매개변수를 설정할 수 없습니다)

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 고지** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`와 같이 하려는 경우가 있을 수 있습니다. 절대로 이렇게 하지 마십시오!!! 이것은 SQL Injection 공격에 노출될 수 있습니다. 이 라이브러리를 사용할 때는 이러한 `where()` 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 하세요.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건에 따라 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

쿼리 결과를 특정한 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드 수를 제한합니다. 두 번째 인수가 주어지면 오프셋, 제한이 SQL과 동일하게 적용됩니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`에 해당하는 지점을 선택합니다.

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`에 해당하는 지점을 선택합니다.

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`에 해당하는 지점을 선택합니다.

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`에 해당하는 지점을 선택합니다.

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`에 해당하는 지점을 선택합니다.

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`에 해당하는 지점을 선택합니다.

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`에 해당하는 지점을 선택합니다.

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`에 해당하는 지점을 선택합니다.

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 또는 `field NOT LIKE $value`에 해당하는 지점을 선택합니다.

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` 또는 `field NOT IN($value)`에 해당하는 지점을 선택합니다.

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1`에 해당하는 지점을 선택합니다.

```php
$user->between('id', [1, 2])->find();
```

## 관계
이 라이브러리를 사용하여 여러 종류의 관계를 설정할 수 있습니다. 테이블 간에 일대다 및 일대일 관계를 설정할 수 있습니다. 이를 위해 미리 클래스에서 약간의 추가 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만 올바른 구문을 추측하는 것이 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키의 이름은 원하는 대로 지정할 수 있습니다. 해당 ActiveRecord의 이름이 좋을 겁니다. 예: user, contact, client
	'user' => [
		// 필수
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 관계 유형

		// 필수
		'Some_Class', // 이것은 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		// 관계 유형에 따라 다름
		// self::HAS_ONE = 조인을 참조하는 외래 키
		// self::HAS_MANY = 조인을 참조하는 외래 키
		// self::BELONGS_TO = 조인을 참조하는 로컬 키
		'local_or_foreign_key',
		// 참고로 여기서도 기본 모델의 주 키에만 조인합니다

		// 선택적
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 관계를 조인할 때 추가 조건
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 선택적
		'back_reference_name' // 이것은 관계를 다시 참조할 때 사용합니다. 예: $user->contact->user;
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

이제 참조를 설정했으므로 아주 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 가장 최근 사용자 찾기.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대로 할 수 있습니다.
$contact = new Contact();

// 하나의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것이 사용자 이름입니다
```

정말 멋진 기능이죠?

## 사용자 정의 데이터 설정
가끔씩 템플릿에 전달하기 위해 레코드에 고유한 것을 첨부해야```markdown
# Flight Active Record 

활성 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있다면, 해당 테이블의 행을 `User` 클래스와 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하세요.

## 기본 예제

다음 테이블이 있다고 가정해 봅시다:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

이제 이 테이블을 나타내는 새로운 클래스를 설정할 수 있습니다:

```php
/**
 * ActiveRecord 클래스는 일반적으로 단수형으로 지정됩니다
 * 
 * 여기 테이블의 속성을 주석으로 추가하는 것이 매우 권장됩니다
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 이 방법으로 설정할 수 있습니다
		parent::__construct($database_connection, 'users');
		// 또는 이 방법으로도 가능합니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 벌어집니다!

```php
// sqlite인 경우
$database_connection = new PDO('sqlite:test.db'); // 이 예시에만 해당합니다, 실제 데이터베이스 연결을 사용할 것입니다

// mysql인 경우
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli인 경우
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체를 사용하지 않는 mysqli인 경우
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
// 여기서 $user->save()를 사용하면 업데이트로 인식합니다!

echo $user->id; // 2
```

그리고 이렇게 간단하게 새로운 사용자를 추가했습니다! 이제 데이터베이스에 사용자 행이 있으므로, 어떻게 데이터를 검색할까요?

```php
$user->find(1); // 데이터베이스에서 id = 1인 것을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾고 싶다면?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 것을 보셨나요? 설치하고 시작해 봅시다!

## 설치

Composer를 사용하여 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이 라이브러리는 독립적으로 또는 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 당신의 몫입니다.

### 독립적으로
생성자에 PDO 연결을 전달하기만 하면 됩니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이 예시에만 해당합니다, 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우, ActiveRecord 클래스를 서비스로 등록할 수 있습니다 (하지만 사실 필수는 아닙니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

레코드 하나를 찾아 현재 객체에 할당합니다. 특정 `$id`를 전달하면 해당 값의 기본 키를 기준으로 조회를 수행합니다. 아무 것도 전달되지 않으면 테이블에서 첫 번째 레코드를 찾습니다.

추가로 다른 도우미 메서드를 통해 테이블을 쿼리할 수 있습니다.

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

검색 전에 검색하여 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

더티 데이터는 레코드에서 변경된 데이터를 의미합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 여기까지는 "더티"가 없습니다.

$user->email = 'test@example.com'; // 이제 이메일은 변경되어 "더티"로 간주됩니다.
$user->update();
// 이제 업데이트되어 데이터베이스에 유지되어 더티 데이터가 없어집니다.

$user->password = password_hash()'newpassword'); // 이제 더티됩니다
$user->dirty(); // 아무것도 전달하지 않으면 모든 더티 항목이 지워집니다.
$user->update(); // 더티로 캡처된 것이 없으