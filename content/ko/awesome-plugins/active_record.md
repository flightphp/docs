# Flight Active Record 

액티브 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 간단히 말하자면, 데이터베이스에 사용자 테이블이 있다면, 해당 테이블의 행을 `User` 클래스와 코드베이스의 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

GitHub에 있는 저장소는 [여기](https://github.com/flightphp/active-record)를 클릭하세요.

## 기본 예제

다음과 같은 테이블이 있다고 가정해 봅시다:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

이제, 이 테이블을 나타내기 위한 새로운 클래스를 설정할 수 있습니다:

```php
/**
 * ActiveRecord 클래스는 보통 단수형입니다
 * 
 * 테이블의 속성을 여기 주석으로 추가하는 것이 강력히 권장됩니다
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
		// 또는 이렇게 할 수 있습니다
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 일어나는 것을 지켜보세요!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이건 단지 예시일 뿐이며, 실제 데이터베이스 연결을 사용할 것입니다

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 비객체 기반 생성으로 mysqli
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
// 여기서 $user->save()를 사용할 수 없습니다. 업데이트로 간주될 것입니다!

echo $user->id; // 2
```

이렇게 쉽게 새로운 사용자를 추가할 수 있었습니다! 데이터베이스에 사용자 행이 생겼으니, 어떻게 꺼낼 수 있을까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

모든 사용자를 찾고 싶다면?

```php
$users = $user->findAll();
```

특정 조건으로는 어떻게 할까요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

이게 얼마나 재미있는지 보셨나요? 설치해보고 시작해봅시다!

## 설치

Composer로 간단히 설치하세요.

```php
composer require flightphp/active-record 
```

## 사용법

이것은 Flight PHP 프레임워크와 함께 사용할 수 있는 독립 라이브러리입니다. 완전히 선택사항입니다.

### 독립형
생성자에 PDO 연결을 전달해야 합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이건 단지 예시일 뿐이며, 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

> 항상 생성자에 데이터베이스 연결을 설정하고 싶지 않나요? 다른 아이디어는 [데이터베이스 연결 관리](#database-connection-management)를 참조하세요!

### Flight의 메서드로 등록하기
Flight PHP 프레임워크를 사용하고 있다면, ActiveRecord 클래스를 서비스로 등록할 수 있지만, 반드시 그럴 필요는 없습니다.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 이렇게 사용할 수 있습니다.

Flight::user()->find(1);
```

## `runway` 메서드

[runway](/awesome-plugins/runway)는 이 라이브러리에 대한 사용자 정의 명령을 가진 Flight의 CLI 도구입니다. 

```bash
# 사용법
php runway make:record database_table_name [class_name]

# 예시
php runway make:record users
```

이 명령은 `app/records/` 디렉토리에 `UserRecord.php`라는 새 클래스를 생성하며, 다음 내용을 포함합니다:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * 사용자 테이블의 ActiveRecord 클래스입니다.
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
     * @var array $relations 모델의 관계를 설정합니다
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

하나의 레코드를 찾아 현재 객체에 할당합니다. 어떤 형태의 `$id`를 전달하면, 해당 값을 사용하여 기본 키에서 조회를 수행합니다. 아무것도 전달되지 않으면, 테이블에서 첫 번째 레코드만 찾습니다.

추가로 테이블을 쿼리하기 위한 다른 헬퍼 메서드를 전달할 수 있습니다.

```php
// 사전에 몇 가지 조건으로 레코드를 찾기
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드를 찾기
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정한 테이블의 모든 레코드를 찾습니다.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

현재 레코드가 수분 공급된 경우 `true`를 반환합니다 (데이터베이스에서 가져옴).

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

UUID와 같은 텍스트 기반 기본 키가 있는 경우, 두 가지 방법으로 삽입 전에 기본 키 값을 설정할 수 있습니다.

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
		// 위 배열 대신 기본 키를 이렇게 설정할 수도 있습니다.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 또는 고유 ID를 생성하는 방법
	}
}
```

삽입하기 전에 기본 키를 설정하지 않으면, `rowid`로 설정되며 데이터베이스가 생성하지만, 테이블에 해당 필드가 존재하지 않기 때문에 유지되지 않습니다. 따라서 이벤트를 사용하여 이를 자동으로 처리하는 것이 좋습니다.

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입하거나 업데이트합니다. 레코드에 ID가 있으면 업데이트되고, 그렇지 않으면 삽입됩니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**주:** 클래스에 관계가 정의되어 있다면, 관계성이 정의되었고 인스턴스화되었으며 업데이트할 더러움 데이터가 있는 경우에도 재귀적으로 그 관계를 저장합니다. (v0.4.0 이상)

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

더러움 데이터는 레코드에서 변경된 데이터를 나타냅니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 현재 상태에서는 아무것도 "더러움"이 없습니다.

$user->email = 'test@example.com'; // 이제 이메일이 변경되었으므로 "더러움"으로 간주됩니다.
$user->update();
// 이제 데이터가 업데이트되어 데이터베이스에 유지되기 때문에 더러움이 없습니다.

$user->password = password_hash('newpassword'); // 이제 이 데이터는 더러움입니다.
$user->dirty(); // 아무것도 전달하지 않으면 모든 더러움 항목이 지워집니다.
$user->update(); // 아무것도 캡처되지 않았기 때문에 업데이트할 것이 없습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호 모두 업데이트됩니다.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

이는 `dirty()` 메서드의 별칭입니다. 하고 있는 작업이 좀 더 명확합니다.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 비밀번호 모두 업데이트됩니다.
```

#### `isDirty(): boolean` (v0.4.0)

현재 레코드가 변경되었으면 `true`를 반환합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 재설정합니다. 이는 반복 행동에서 유용합니다. `true`를 전달하면 현재 객체를 찾는 데 사용된 쿼리 데이터도 재설정됩니다 (기본 동작).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 깨끗한 상태에서 시작합니다
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, 또는 `save()` 메서드를 실행한 후에 빌드된 SQL을 얻을 수 있으며, 이를 디버깅 용도로 사용할 수 있습니다.

## SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 경우 테이블의 몇 컬럼만 선택할 수 있습니다 (정말 넓은 테이블에서 더 성능이 좋습니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

기술적으로 다른 테이블을 선택할 수도 있습니다! 왜 안 됩니까?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블에 조인을 할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다 (이 where 문에서 params를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 주의:** `$user->where("id = '{$id}' AND name = '{$name}'")->find();`와 같은 작업을 시도하고 싶을 수 있습니다. 절대 이렇게 하지 마세요!!! 이건 SQL 인젝션 공격에 취약합니다. 온라인에서 많은 기사를 찾을 수 있으며, "sql injection attacks php"를 구글링하면 이 주제에 대한 많은 기사를 찾을 수 있습니다. 이 라이브러리를 사용하여 처리하는 올바른 방법은 `where()` 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`와 같은 작업을 수행하는 것입니다. 이렇게 해야 한다면 `PDO` 라이브러리에 있는 `$pdo->quote($var)`를 사용하여 이를 이스케이프할 수 있습니다. `quote()`를 사용한 후에야 이를 `where()` 문에서 사용할 수 있습니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건에 따라 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

반환된 쿼리를 특정 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드 수를 제한합니다. 두 번째 정수가 주어지면, SQL과 같이 오프셋, 제한이 됩니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 조건
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`인 경우

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`인 경우

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`인 경우

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`인 경우

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`인 경우

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`인 경우

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`인 경우

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`인 경우

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 또는 `field NOT LIKE $value`인 경우

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` 또는 `field NOT IN($value)`인 경우

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1`인 경우

```php
$user->between('id', [1, 2])->find();
```

### OR 조건

조건을 OR 문으로 감싸는 것이 가능합니다. 이는 `startWrap()` 및 `endWrap()` 메서드 또는 조건의 필드 및 값 뒤에 3번째 매개변수를 채우는 방법으로 수행됩니다.

```php
// 메서드 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 이는 `id = 1 AND (name = 'demo' OR name = 'test')`로 평가됩니다.

// 메서드 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 이는 `id = 1 OR name = 'demo'`로 평가됩니다.
```

## 관계
이 라이브러리를 사용하여 여러 종류의 관계를 설정할 수 있습니다. 테이블 간에 1대 다수 및 1대 1 관계를 설정할 수 있습니다. 이를 위해 클래스에서 약간의 추가 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만, 올바른 구문을 추측하는 것이 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키 이름은 원하는 대로 지정할 수 있습니다. ActiveRecord의 이름이 좋을 것입니다. 예: 사용자, 연락처, 클라이언트
	'user' => [
		// 필수
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 관계 유형입니다

		// 필수
		'Some_Class', // 이는 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		// 관계 유형에 따라
		// self::HAS_ONE = 조인을 참조하는 외래 키
		// self::HAS_MANY = 조인을 참조하는 외래 키
		// self::BELONGS_TO = 조인을 참조하는 로컬 키
		'local_or_foreign_key',
		// FYI, 이는 또한 "다른" 모델의 기본 키에만 조인됩니다.

		// 선택적
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 관계를 조인할 때 추가 조건
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 선택적
		'back_reference_name' // 이는 이 관계를 다시 참조하고 싶을 경우입니다. 예: $user->contact->user;
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

이제 참조가 설정되었으므로 이를 매우 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 가장 최근 사용자를 찾습니다.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대로 갈 수 있습니다.
$contact = new Contact();

// 한 연락처를 찾습니다.
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이건 사용자 이름입니다.
```

정말 멋지지 않은가요?

## 사용자 정의 데이터 설정
때때로 ActiveRecord에 고유한 것을 추가해야 할 필요가 있습니다. 예를 들어, 템플릿에 전달될 수 있도록 쉽게 첨부할 수 있는 사용자 정의 계산이 있습니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드를 사용하여 사용자 정의 데이터를 첨부합니다.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

그 다음에는 일반 객체 속성처럼 이를 참조하면 됩니다.

```php
echo $user->page_view_count;
```

## 이벤트

이 라이브러리의 또 하나의 정말 멋진 기능은 이벤트입니다. 이벤트는 호출하는 특정 메서드에 따라 특정 시간에 트리거됩니다. 자동으로 데이터를 설정하는 데 매우 유용합니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

기본 연결을 설정해야 할 경우 정말 유용합니다.

```php
// index.php 또는 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // & 참조를 잊지 마세요
		// 자동으로 연결을 설정하도록 다음과 같이 할 수 있습니다.
		$config['connection'] = Flight::db();
		// 또는 이렇게
		$self->transformAndPersistConnection(Flight::db());
		
		// 또한 이런 방식으로 테이블 이름을 설정할 수 있습니다.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

매번 쿼리 조작이 필요한 경우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 항상 id >= 0을 실행합니다.
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

이것은 레코드가 검색될 때마다 항상 무언가를 실행해야 하는 경우에 더 유용합니다. 무언가를 복호화할 필요가 있나요? 매번 사용자 정의 카운트 쿼리를 실행할 필요가 있나요 (성능상 좋지 않지만...)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 무언가 복호화 중
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 다시 무언가 사용자 정의로 저장하기 위해서????
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

매번 쿼리 조작이 필요한 경우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 항상 id >= 0을 실행합니다.
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`와 유사하지만 전부에 대해 실행할 수 있습니다!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()처럼 멋진 작업을 수행하세요.
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

매번 기본값을 설정해야 할 경우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 몇 가지 기본값 설정
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

데이터가 삽입된 후 변경해야 하는 경우가 있을 수 있습니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 무언가 하세요
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 또는 무엇이든....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

업데이트마다 기본값을 설정해야 할 경우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 몇 가지 기본값 설정
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

업데이트 후 데이터 변경에 대한 사용 사례가 있을 수 있습니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 무언가 하세요
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 또는 무엇이든....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

이 이벤트는 삽입이나 업데이트가 발생할 때 모두 발생하게 하고 싶을 때 유용합니다. 긴 설명은 생략하겠습니다만, 당신은 무엇인지 이미 아실 것입니다.

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

여기서 무엇을 하실지 모르겠지만, 평가하지는 않겠습니다! 마음껏 하세요!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '그는 용감한 군인이었습니다... :cry-face:';
	} 
}
```

## 데이터베이스 연결 관리

이 라이브러리를 사용할 때 데이터베이스 연결을 몇 가지 방법으로 설정할 수 있습니다. 생성자에 연결을 설정할 수 있고, 구성 변수 `$config['connection']`를 통해 설정할 수 있으며, `setDatabaseConnection()` (v0.4.1)을 통해 설정할 수도 있습니다. 

```php
$pdo_connection = new PDO('sqlite:test.db'); // 예를 위해
$user = new User($pdo_connection);
// 또는
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 또는
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

각 ActiveRecord를 호출할 때마다 `$database_connection`을 항상 설정하는 것을 피하고 싶다면, 방법이 있습니다!

```php
// index.php 또는 bootstrap.php
// Flight에 등록된 클래스로 설정합니다.
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// 이제 인자가 필요 없습니다!
$user = new User();
```

> **주:** 유닛 테스트를 계획하고 있다면, 이렇게 하는 것이 유닛 테스트에 도전 과제를 더할 수 있지만, 전반적으로 `setDatabaseConnection()` 또는 `$config['connection']`로 연결을 주입할 수 있기 때문에 큰 문제는 아닙니다.

클라이언트 스크립트를 실행 중에 데이터베이스 연결을 새로 고쳐야 하는 경우, 예를 들어 오랜 CLI 스크립트를 실행하면서 연결을 새로 고쳐야 할 경우 `$your_record->setDatabaseConnection($pdo_connection)`로 연결을 재설정할 수 있습니다.

## 기여

부디 해주세요. :D

### 설정

기여할 때는 `composer test-coverage`를 실행하여 100% 테스트 커버리지를 유지하세요 (이건 진정한 유닛 테스트 커버리지라기보다는 통합 테스트에 가깝습니다).

또한 `composer beautify`와 `composer phpcs`를 실행하여 모든 린팅 오류를 수정하세요.

## 라이센스

MIT