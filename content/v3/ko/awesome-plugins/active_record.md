# Flight Active Record 

액티브 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말해, 데이터베이스에 users 테이블이 있으면 테이블의 행을 코드베이스의 `User` 클래스와 `$user` 객체로 "번역"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

GitHub의 저장소를 보려면 [여기](https://github.com/flightphp/active-record)를 클릭하세요.

## 기본 예제

다음과 같은 테이블이 있다고 가정해 보겠습니다:

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
 * ActiveRecord 클래스는 보통 단수형입니다
 * 
 * 테이블의 속성을 주석으로 여기에 추가하는 것이 강력히 권장됩니다
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
		// 또는 이렇게
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 일어나는 것을 보세요!

```php
// sqlite의 경우
$database_connection = new PDO('sqlite:test.db'); // 이것은 예제일 뿐입니다. 실제 데이터베이스 연결을 사용할 가능성이 큽니다

// mysql의 경우
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반이 아닌 mysqli 생성
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
// 여기서 $user->save()를 사용할 수 없으며, 업데이트로 인식할 것입니다!

echo $user->id; // 2
```

새 사용자를 추가하는 것이 이렇게 쉬웠습니다! 이제 데이터베이스에 사용자 행이 있으므로, 이를 어떻게 가져오나요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

모든 사용자를 찾고 싶다면?

```php
$users = $user->findAll();
```

특정 조건으로?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

얼마나 재미있나요? 설치하고 시작해 보세요!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이것은 독립 라이브러리로 사용하거나 Flight PHP Framework와 함께 사용할 수 있습니다. 완전히 당신의 선택입니다.

### 독립 사용
생성자에 PDO 연결을 전달하기만 하세요.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예제일 뿐입니다. 실제 데이터베이스 연결을 사용할 가능성이 큽니다

$User = new User($pdo_connection);
```

> 생성자에서 항상 데이터베이스 연결을 설정하지 않으려면? [데이터베이스 연결 관리](#database-connection-management)를 참조하세요!

### Flight에서 메서드로 등록
Flight PHP Framework를 사용 중이라면, ActiveRecord 클래스를 서비스로 등록할 수 있지만, 꼭 해야 하는 것은 아닙니다.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 이렇게 사용할 수 있습니다.

Flight::user()->find(1);
```

## `runway` 메서드

[runway](/awesome-plugins/runway)는 Flight를 위한 CLI 도구로, 이 라이브러리에 대한 사용자 지정 명령어를 가지고 있습니다. 

```bash
# 사용법
php runway make:record database_table_name [class_name]

# 예제
php runway make:record users
```

이것은 `app/records/` 디렉토리에 `UserRecord.php`로 새 클래스를 생성하며, 다음과 같은 내용을 포함합니다:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 테이블을 위한 ActiveRecord 클래스.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations 모델의 관계 설정
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * 생성자
     * @param mixed $databaseConnection 데이터베이스 연결
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. `$id`를 전달하면 해당 값으로 기본 키를 조회합니다. 아무것도 전달하지 않으면 테이블의 첫 번째 레코드를 찾습니다.

또한 테이블을 쿼리하기 위해 다른 도우미 메서드를 전달할 수 있습니다.

```php
// 사전 조건으로 레코드 찾기
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드 찾기
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정된 테이블의 모든 레코드를 찾습니다.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

현재 레코드가 데이터베이스에서 로드(hydrated)되었는지 `true`를 반환합니다.

```php
$user->find(1);
// 데이터가 있는 레코드가 발견되면...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### 텍스트 기반 기본 키

텍스트 기반 기본 키(예: UUID)가 있으면, 삽입 전에 기본 키 값을 두 가지 방법 중 하나로 설정할 수 있습니다.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 또는 $user->save();
```

또는 이벤트를 통해 기본 키를 자동으로 생성할 수 있습니다.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 위 배열 대신 이렇게 기본 키를 설정할 수도 있습니다.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 또는 고유 ID를 생성하는 방법에 따라
	}
}
```

삽입 전에 기본 키를 설정하지 않으면 `rowid`로 설정되고 데이터베이스가 생성하지만, 테이블에 해당 필드가 없으면 지속되지 않습니다. 따라서 이벤트를 사용해 자동으로 처리하는 것이 권장됩니다.

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입하거나 업데이트합니다. 레코드에 id가 있으면 업데이트하고, 없으면 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**참고:** 클래스에 관계가 정의되어 있으면, 정의되고 인스턴스화된 관계 중 업데이트할 더티 데이터가 있는 경우 재귀적으로 저장합니다. (v0.4.0 이상)

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

사전에 검색을 실행하여 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

더티 데이터는 레코드에서 변경된 데이터를 가리킵니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서 아무것도 "더티"하지 않습니다.

$user->email = 'test@example.com'; // 이제 이메일이 변경되어 "더티"로 간주됩니다.
$user->update();
// 이제 업데이트되어 데이터베이스에 지속되었으므로 더티 데이터가 없습니다

$user->password = password_hash()'newpassword'); // 이제 이것이 더티입니다
$user->dirty(); // 아무것도 전달하지 않으면 모든 더티 항목을 지웁니다.
$user->update(); // 아무것도 업데이트되지 않습니다. 더티로 캡처된 것이 없기 때문입니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

`dirty()` 메서드의 별칭입니다. 무엇을 하는지 더 명확합니다.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호가 모두 업데이트됩니다.
```

#### `isDirty(): boolean` (v0.4.0)

현재 레코드가 변경되었는지 `true`를 반환합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 재설정합니다. 루프 유형 동작에서 사용하기 좋습니다.
`true`를 전달하면 현재 객체를 찾는 데 사용된 쿼리 데이터도 재설정합니다 (기본 동작).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 깨끗한 상태로 시작
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, 또는 `save()` 메서드를 실행한 후 생성된 SQL을 가져와 디버깅에 사용할 수 있습니다.

## SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

테이블의 일부 컬럼만 선택할 수 있습니다 (많은 컬럼이 있는 넓은 테이블에서 더 성능이 좋습니다)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

기술적으로 다른 테이블을 선택할 수도 있습니다! 왜 안 되나요?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블에 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 지정 where 인수를 설정할 수 있습니다 (이 where 문에서 params를 설정할 수 없습니다)

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 주의** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`처럼 할 수 있지만, 절대 이렇게 하지 마세요!!! 이것은 SQL 인젝션 공격에 취약합니다. 온라인에 많은 기사가 있습니다. "sql injection attacks php"를 Google하면 이 주제에 대한 많은 기사를 찾을 수 있습니다. 이 라이브러리에서 이를 처리하는 올바른 방법은 `where()` 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`처럼 하는 것입니다. 반드시 이렇게 해야 한다면, `PDO` 라이브러리의 `$pdo->quote($var)`를 사용해 이스케이프하세요. `quote()`를 사용한 후에만 `where()` 문에서 사용할 수 있습니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건으로 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

반환된 쿼리를 특정 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드 수를 제한합니다. 두 번째 int가 주어지면 SQL처럼 offset, limit이 됩니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Where `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Where `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Where `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Where `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Where `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Where `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Where `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Where `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Where `field LIKE $value` 또는 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` 또는 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR 조건

조건을 OR 문으로 감쌀 수 있습니다. `startWrap()`와 `endWrap()` 메서드를 사용하거나 필드와 값 다음에 조건의 3번째 매개변수를 채워서 합니다.

```php
// 방법 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 이는 `id = 1 AND (name = 'demo' OR name = 'test')`로 평가됩니다

// 방법 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 이는 `id = 1 OR name = 'demo'`로 평가됩니다
```

## 관계
이 라이브러스를 사용해 여러 종류의 관계를 설정할 수 있습니다. 테이블 간 one->many 및 one->one 관계를 설정할 수 있습니다. 이는 클래스에서 약간의 추가 설정이 필요합니다.

`$relations` 배열 설정은 어렵지 않지만, 올바른 구문을 추측하는 것은 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키 이름을 원하는 대로 지정할 수 있습니다. ActiveRecord 이름이 좋습니다. 예: user, contact, client
	'user' => [
		// 필수
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 이는 관계 유형입니다

		// 필수
		'Some_Class', // 이는 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		// 관계 유형에 따라 다름
		// self::HAS_ONE = 조인 참조 외래 키
		// self::HAS_MANY = 조인 참조 외래 키
		// self::BELONGS_TO = 조인 참조 로컬 키
		'local_or_foreign_key',
		// 참고로, 이는 "다른" 모델의 기본 키에만 조인됩니다

		// 선택
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 관계 조인 시 원하는 추가 조건
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 선택
		'back_reference_name' // 이 관계를 다시 자신으로 백 참조하려면 예: $user->contact->user;
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

// 관계를 사용해 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대로 할 수 있습니다.
$contact = new Contact();

// 하나의 연락처 찾기
$contact->find();

// 관계를 사용해 사용자 가져오기:
echo $contact->user->name; // 이는 사용자 이름입니다
```

꽤 멋지지 않나요?

### Eager Loading

#### 개요
Eager loading은 관계를 미리 로드하여 N+1 쿼리 문제를 해결합니다. 각 레코드의 관계에 대해 별도의 쿼리를 실행하는 대신, 관계당 하나의 추가 쿼리로 모든 관련 데이터를 가져옵니다.

> **참고:** Eager loading은 v0.7.0 이상에서만 사용할 수 있습니다.

#### 기본 사용법
`with()` 메서드를 사용해 eager load할 관계를 지정하세요:
```php
// N+1 대신 2개의 쿼리로 사용자와 연락처 로드
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // 추가 쿼리 없음!
    }
}
```

#### 다중 관계
한 번에 여러 관계 로드:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### 관계 유형

##### HAS_MANY
```php
// 각 사용자에 대한 모든 연락처 eager load
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts는 이미 배열로 로드됨
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// 각 사용자에 대한 하나의 연락처 eager load
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact는 이미 객체로 로드됨
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// 모든 연락처에 대한 부모 사용자 eager load
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user는 이미 로드됨
    echo $c->user->name;
}
```
##### find()와 함께
Eager loading은 
findAll()
 및 
find()
와 모두 작동합니다:

```php
$user = $user->with('contacts')->find(1);
// 사용자와 모든 연락처가 2개의 쿼리로 로드됨
```
#### 성능 이점
Eager loading 없이 (N+1 문제):
```php
$users = $user->findAll(); // 1 쿼리
foreach ($users as $u) {
    $contacts = $u->contacts; // N 쿼리 (사용자당 하나!)
}
// 총: 1 + N 쿼리
```

Eager loading과 함께:

```php
$users = $user->with('contacts')->findAll(); // 총 2 쿼리
foreach ($users as $u) {
    $contacts = $u->contacts; // 추가 쿼리 0!
}
// 총: 2 쿼리 (사용자 1 + 모든 연락처 1)
```
10명의 사용자에 대해 쿼리를 11개에서 2개로 줄여 82% 감소!

#### 중요한 주의사항
- Eager loading은 완전히 선택적입니다 - lazy loading은 이전처럼 작동합니다
- 이미 로드된 관계는 자동으로 건너뜁니다
- 백 참조는 eager loading과 작동합니다
- 관계 콜백은 eager loading 중 존중됩니다

#### 제한사항
- 중첩 eager loading (예: 
with(['contacts.addresses'])
)은 현재 지원되지 않습니다
- 클로저를 통한 eager load 제약은 이 버전에서 지원되지 않습니다

## 사용자 지정 데이터 설정
때때로 ActiveRecord에 고유한 것을 첨부해야 할 수 있습니다. 예를 들어 템플릿에 전달될 객체에 간단히 첨부할 수 있는 사용자 지정 계산입니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드로 사용자 지정 데이터를 첨부하세요.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

그런 다음 일반 객체 속성처럼 참조하세요.

```php
echo $user->page_view_count;
```

## 이벤트

이 라이브러리의 또 다른 멋진 기능은 이벤트에 관한 것입니다. 이벤트는 호출하는 특정 메서드에 기반해 특정 시점에 트리거됩니다. 데이터를 자동으로 설정하는 데 매우 유용합니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

기본 연결을 설정해야 할 때 매우 유용합니다.

```php
// index.php 또는 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // & 참조를 잊지 마세요
		// 연결을 자동으로 설정하려면 이렇게 할 수 있습니다
		$config['connection'] = Flight::db();
		// 또는 이렇게
		$self->transformAndPersistConnection(Flight::db());
		
		// 테이블 이름도 이렇게 설정할 수 있습니다.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

각 쿼리 조작이 필요할 때만 유용할 것입니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// id >= 0을 항상 실행하려면
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

이 레코드가 가져올 때마다 항상 로직을 실행해야 할 때 더 유용할 것입니다. 무언가를 복호화해야 하나요? 매번 사용자 지정 카운트 쿼리를 실행해야 하나요 (성능이 좋지 않지만 어쨌든)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 무언가 복호화
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 쿼리처럼 사용자 지정 무언가를 저장?
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

각 쿼리 조작이 필요할 때만 유용할 것입니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// id >= 0을 항상 실행하려면
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`와 유사하지만 모든 레코드에 적용할 수 있습니다!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()처럼 멋진 일을 하세요
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

매번 기본 값을 설정해야 할 때 매우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 합리적인 기본값 설정
		if(!$self->created_date) {
			$self->created_date = gmdate('Y-m-d');
		}

		if(!$self->password) {
			$self->password = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

삽입 후 데이터를 변경해야 하는 사용 사례가 있나요?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 당신의 방식대로 하세요
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 또는 무엇이든....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

업데이트 시 매번 기본 값을 설정해야 할 때 매우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 합리적인 기본값 설정
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

업데이트 후 데이터를 변경해야 하는 사용 사례가 있나요?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 당신의 방식대로 하세요
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 또는 무엇이든....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

삽입 또는 업데이트 시 이벤트가 발생하기를 원할 때 유용합니다. 긴 설명은 생략하겠지만, 무엇인지 추측할 수 있을 것입니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

여기서 무엇을 하고 싶은지 모르겠지만, 판단하지 않습니다! 해보세요!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'He was a brave soldier... :cry-face:';
	} 
}
```

## 데이터베이스 연결 관리

이 라이브러리를 사용할 때 데이터베이스 연결을 여러 방식으로 설정할 수 있습니다. 생성자에서 연결을 설정하거나, `$config['connection']` 설정 변수로 설정하거나 `setDatabaseConnection()` (v0.4.1)을 사용할 수 있습니다. 

```php
$pdo_connection = new PDO('sqlite:test.db'); // 예제용
$user = new User($pdo_connection);
// 또는
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 또는
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

액티브 레코드를 호출할 때마다 항상 `$database_connection`을 설정하지 않으려면, 이를 피할 방법이 있습니다!

```php
// index.php 또는 bootstrap.php
// Flight에서 등록된 클래스로 설정
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// 이제 인수 불필요!
$user = new User();
```

> **참고:** 단위 테스트를 계획 중이라면, 이 방식은 단위 테스트에 약간의 도전을 추가할 수 있지만, `setDatabaseConnection()` 또는 `$config['connection']`으로 연결을 주입할 수 있으므로 전체적으로 나쁘지 않습니다.

데이터베이스 연결을 새로 고쳐야 할 때, 예를 들어 장기 실행 CLI 스크립트를 실행 중이고 연결을 주기적으로 새로 고쳐야 한다면, `$your_record->setDatabaseConnection($pdo_connection)`으로 재설정할 수 있습니다.

## 기여

참여해 주세요. :D

### 설정

기여할 때 `composer test-coverage`를 실행해 100% 테스트 커버리지를 유지하세요 (이것은 진짜 단위 테스트 커버리지가 아니라 통합 테스트에 가깝습니다).

또한 `composer beautify`와 `composer phpcs`를 실행해 린팅 오류를 수정하세요.

## 라이선스

MIT