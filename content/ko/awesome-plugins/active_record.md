# Flight Active Record 

액티브 레코드는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것을 의미합니다. 간단히 말해서 데이터베이스에 사용자 테이블이 있다면 이 테이블의 행을 `User` 클래스와 코드베이스의 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

## 기본 예제

다음 테이블을 가정해 봅시다:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

이제 이 테이블을 표현하는 새 클래스를 설정할 수 있습니다:

```php
/**
 * 액티브레코드 클래스는 일반적으로 단수형으로 지정합니다.
 * 
 * 여기에 테이블의 속성을 주석으로 추가하는 것이 권장됩니다.
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

이제 마법이 벌어집니다!

```php
// sqlite를 사용하는 경우
$database_connection = new PDO('sqlite:test.db'); // 이것은 예시일뿐이라 실제 데이터베이스 연결을 사용해야 합니다

// mysql을 사용하는 경우
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli를 사용하는 경우
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체를 사용하지 않는 mysqli
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
// 여기서 $user->save()를 사용할 수 없습니다. 그렇지 않으면 업데이트로 인식됩니다!

echo $user->id; // 2
```

새 사용자를 추가하는 것이 매우 쉬웠습니다! 이제 데이터베이스에 사용자 행이 있으므로 어떻게 가져올까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

모든 사용자를 찾고 싶으면 어떻게 하나요?

```php
$users = $user->findAll();
```

특정 조건으로 찾고 싶다면?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

재미있는 것을 보셨나요? 이를 설치하고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이 라이브러리를 독립적으로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 선택은 당신에게 달렸습니다.

### 독립적으로
생성자에 PDO 연결을 전달해야 합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예시일뿐이라 실제 데이터베이스 연결을 사용해야 합니다

$User = new User($pdo_connection);
```

### Flight PHP 프레임워크
Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있습니다(하지만 되도록 사용하지 않는 게 좋습니다).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그런 다음 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아서 현재 객체에 할당합니다. 어떤 `$id`를 전달하면 해당 값의 기본 키에서 조회를 실행합니다. 아무것도 전달하지 않은 경우에는 테이블에서 첫 번째 레코드를 찾습니다.

추가로 쿼리 테이블을 조회하는 데 도움이 되는 다른 도우미 메서드를 전달할 수 있습니다.

```php
// 사전에 일부 조건이 지정된 레코드 찾기
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

현재 레코드가 수집되었는지 여부를 반환합니다(데이터베이스에서 가져온 것).

```php
$user->find(1);
// 데이터와 함께 레코드가 검색된 경우...
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

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에서 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입하거나 업데이트합니다. 레코드에 id가 있으면 업데이트되고, 그렇지 않으면 삽입됩니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**참고:** 클래스에서 관계가 정의된 경우, 정의되고 초기화된 경우 해당 관계도 재귀적으로 업데이트됩니다. (v0.4.0 이상)

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

검색 이전에 몇몇 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

변경된 데이터를 참조합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점에서 아무것도 "생기지 않았습니다".

$user->email = 'test@example.com'; // 지금 이메일은 변경됐으므로 "dirty"로 간주됩니다.
$user->update();
// 이제 더티 데이터가 없어졌으므로 변경된 내용은 데이터베이스에 반영되었습니다.

$user->password = password_hash()'newpassword'); // 이제 이것은 더티 상태입니다.
$user->dirty(); // 아무것도 전달하지 않으면 모든 더티 항목이 지워집니다.
$user->update(); // 더티가 캡처되지 않았으므로 아무 것도 업데이트되지 않습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 암호가 모두 업데이트됩니다.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

`dirty()` 메서드의 별칭입니다. 무엇을 수행할지 명확히 할 수 있습니다.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 이름과 암호가 모두 업데이트됩니다.
```

#### `isDirty(): boolean` (v0.4.0)

현재 레코드가 변경되었는지 여부를 반환합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 다시 설정합니다. 이는 루프 형태 동작에서 사용하기에 매우 적합합니다. `true`를 전달하면 현재 객체를 찾는 데 사용된 쿼리 데이터도 재설정됩니다(기본 동작).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 깨끗하게 시작
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, 또는 `save()` 메서드를 실행한 후에 생성된 SQL을 가져와 디버깅 목적으로 사용할 수 있습니다.

## SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원하는 경우 테이블의 일부 열만 선택할 수 있습니다(다수의 열이 있는 경우 성능이 좋습니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

원하는 경우 다른 테이블을 선택할 수도 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스에서 다른 테이블에 조인할 수 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다(이 where 문에서는 params를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 주의** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`와 같은 코드를 작성하고자 할 수 있습니다. 이렇게 하지 마십시오!!! 이는 SQL Injection 공격에 노출될 수 있습니다. 온라인에서 많은 기사가 있습니다. "sql injection attacks php"로 검색하여 해당 주제에 대해 알아보시기 바랍니다. 이 라이브러리에서 이를 처리하는 올바른 방법은 `where()` 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`와 같은 방식을 사용하는 것입니다. 꼭 필요한 경우 `PDO` 라이브러리에는 `$pdo->quote($var)`를 사용하여 이스케이프할 수 있습니다. `quote()`를 사용한 후에만 `where()` 문에서 사용할 수 있습니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

특정 조건에 따라 결과를 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

결과 쿼리를 정해진 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환된 레코드 수를 제한합니다. 두 번째 int를 지정할 경우 오프셋 및 제한이 SQL과 같이 적용됩니다.

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

## 관계

이 라이브러리를 사용하여 여러 종류의 관계를 설정할 수 있습니다. 테이블 간에 일대다 및 일대일 관계를 설정할 수 있습니다. 이전에 클래스에 `$relations` 배열을 설정해야 하지만 올바른 구문을 추측하는 데 어려움이 있습니다.

```php
protected array $relations = [
	// 키 이름을 원하는 대로 설정할 수 있습니다. ActiveRecord의 이름이 좋을 것입니다. 예: user, contact, client
	'user' => [
		// 필수
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 관계 유형

		// 필수
		'Some_Class', // 참조할 "다른" ActiveRecord 클래스

		// 필수
		// 관계 유형에 따라
		// self::HAS_ONE = 조인을 참조하는 외래 키
		// self::HAS_MANY = 조인을 참조하는 외래 키
		// self::BELONGS_TO = 조인을 참조하는 로컬 키
		'local_or_foreign_key',
		// 이는 "다른" 모델의 주요 키

와 함께 조인을 구성하므로 사용할 수 있습니다!

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

이제 참조를 설정했으므로 매우 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 가장 최근 사용자 찾기.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 연락처 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 다른 방법으로도 할 수 있습니다.
$contact = new Contact();

// 하나의 연락처 찾기
$contact->find();

// 관계를 사용하여 사용자 가져오기:
echo $contact->user->name; // 이것은 사용자 이름입니다
```

정말 멋지죠?

## 사용자 정의 데이터 설정

가끔씩 ActiveRecord에 사용자 정의 계산과 같은 고유한 것을 첨부해야 할 수도 있습니다. 이를 템플릿에 전달하기에 더 쉬울 수 있습니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드를 사용하여 사용자 정의 데이터를 첨부할 수 있습니다.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

그런 다음 일반 객체 속성처럼 참조하면 됩니다.

```php
echo $user->page_view_count;
```

## 이벤트

이 라이브러리에 대해 한 가지 더 멋진 기능은 이벤트입니다. 일부 메서드를 호출할 때 특정 시점에서 이벤트가 트리거되며 사용할 수 있습니다. 이런 방식으로 데이터를 자동으로 설정하는 데 매우 효과적입니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

기본 연결을 설정해야 하는 경우에 매우 유용합니다.

```php
// index.php 또는 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // & 참조를 잊지 마세요
		// 연결을 자동으로 설정할 수 있습니다.
		$config['connection'] = Flight::db();
		// 또는 이렇게
		$self->transformAndPersistConnection(Flight::db());
		
		// 이렇게 테이블 이름도 설정할 수 있습니다.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

각 실행 시마다 쿼리 조작이 필요한 경우에 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 항상 id >= 0을 실행하도록합니다
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

검색된 레코드마다 항상 일부 로직을 실행해야하는 경우에 유용합니다. 복사할 암호화가 필요한 경우 등(성능은 떨어지지만 무슨일이야).

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 암호 복호화 중
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 각 회수마다 사용자 정의 카운트 쿼리 실행이 필요한 경우
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

나머지 이벤트에 대해는 한 가지만 이해하면 됩니다! 현재 레코드가 변경되면 일부 논리를 항상 실행해야 하는 지점이 됩니다.

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

각 실행 시마다 쿼리 조작이 필요한 경우에 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 항상 id >= 0을 실행하도록합니다
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`와 유사하지만 모든 레코드에 대해 작동합니다!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()처럼 멋진 일 몇 가지를 수행하기
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

항상 일부 기본값이 설정되어야 하는 경우 매우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 일부 안정적인 기본값 설정
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

삽입 후 데이터를 변경해야 하는 사용 사례가 있는 경우?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 내용을 저장하십시오.
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 또는 무엇이든..
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

更新 시마다 기본값을 설정해야 하는 경우 매우 유용합니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 미래의 경우를 준비하십시오
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

업데이트 후 데이터를 변경해야 하는 사용 사례가 있는 경우?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 내용을 저장하십시오
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 또는 무엇이든..
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

`insert()` 또는 `update()`가 발생할 때 마다 이벤트를 사용하려는 경우 유용합니다. 긴 설명을 삼가합니다.

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

여기에 무엇을 하려는지는 잘 모르겠습니다만, 여기서는 이상이 없습니다! 하세요!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '용감한 전사였습니다... :cry-face:';
	} 
}
```

## 데이터베이스 연결 관리

이 라이브러리를 사용할 때, 데이터베이스 연결을 설정하는 몇 가지 방법이 있습니다. 생성자에서 연결을 설정하거나 설정 변수 `$config['connection']`를 통해 설정하거나 `setDatabaseConnection()`을 통해 설정할 수 있습니다. 

```php
$pdo_connection = new PDO('sqlite:test.db'); // 예시
$user = new User($pdo_connection);
// 또는
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 또는
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

예를 들어 CLI 스크립트를 실행하는 경우 데이터베이스 연결을 주기적으로 새로 고칠 필요가 있는 경우 `$your_record->setDatabaseConnection($pdo_connection)`로 연결을 다시 설정할 수 있습니다. 

## 기여

기여해 주세요. :D

## 설정

기여하는 경우 `composer test-coverage`를 실행하여 100%의 테스트 커버리지를 유지하세요(이는 실제 유닛 테스트 커버리지가 아니라 통합 테스트입니다).

또한 `composer beautify`를 실행하고 `composer phpcs`를 실행하여 린트 오류를 수정하세요.

## 라이선스

MIT