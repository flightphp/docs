# Flight Active Record

Active Record는 데이터베이스 엔티티를 PHP 객체에 매핑하는 것입니다. 쉽게 말해, 데이터베이스에 users 테이블이 있다면 해당 테이블의 행을 코드베이스의 `User` 클래스와 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#basic-example)를 참조하세요.

GitHub 저장소는 [여기](https://github.com/flightphp/active-record)에서 확인하세요.

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
 * Active Record 클래스는 보통 단수형입니다
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
		// 또는 이렇게
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

이제 마법이 일어나는 것을 지켜보세요!

```php
// sqlite용
$database_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며, 아마 실제 데이터베이스 연결을 사용하게 될 것입니다

// mysql용
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반이 아닌 생성 방식의 mysqli
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
// 여기서 $user->save()를 사용할 수 없습니다. 그렇지 않으면 업데이트로 간주됩니다!

echo $user->id; // 2
```

새 사용자를 추가하는 것이 정말 쉬웠습니다! 이제 데이터베이스에 사용자 행이 있으니 어떻게 꺼낼까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아 반환합니다.
echo $user->name; // 'Bobby Tables'
```

모든 사용자를 찾고 싶다면 어떻게 할까요?

```php
$users = $user->findAll();
```

특정 조건을 사용하려면 어떻게 할까요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

얼마나 재미있는지 보이시나요? 설치하고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이 라이브러리는 독립 실행형 라이브러리로 사용하거나 Flight PHP Framework와 함께 사용할 수 있습니다. 전적으로 선택에 달려 있습니다.

### 독립 실행형
생성자에 PDO 연결을 전달했는지 확인하세요.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며, 아마 실제 데이터베이스 연결을 사용하게 될 것입니다

$User = new User($pdo_connection);
```

> 생성자에서 항상 데이터베이스 연결을 설정하고 싶지 않으신가요? 다른 방법은 [데이터베이스 연결 관리](#database-connection-management)를 참조하세요!

### Flight에 메서드로 등록
Flight PHP Framework를 사용 중이라면 ActiveRecord 클래스를 서비스로 등록할 수 있지만, 솔직히 꼭 그럴 필요는 없습니다.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그러면 컨트롤러, 함수 등에서 이렇게 사용할 수 있습니다.

Flight::user()->find(1);
```

## `runway` 메서드

[runway](/awesome-plugins/runway)는 이 라이브러리를 위한 사용자 정의 명령이 있는 Flight용 CLI 도구입니다.

```bash
# 사용법
php runway make:record database_table_name [class_name]

# 예시
php runway make:record users
```

이렇게 하면 `app/records/` 디렉터리에 다음 내용으로 `UserRecord.php`라는 새 클래스가 생성됩니다:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 테이블을 위한 ActiveRecord 클래스.
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
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
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * 생성자
     * @param mixed $databaseConnection 데이터베이스에 대한 연결
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD 함수

#### `find($id = null) : boolean|ActiveRecord`

하나의 레코드를 찾아 현재 객체에 할당합니다. 어떤 종류의 `$id`를 전달하면 해당 값으로 기본 키를 조회합니다. 아무것도 전달하지 않으면 테이블의 첫 번째 레코드를 찾습니다.

또한 테이블을 쿼리하기 위해 다른 도우미 메서드를 전달할 수 있습니다.

```php
// 미리 몇 가지 조건으로 레코드 찾기
$user->notNull('password')->orderBy('id DESC')->find();

// 특정 id로 레코드 찾기
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

지정한 테이블의 모든 레코드를 찾습니다.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

조건에 일치하는 첫 번째 레코드를 찾습니다. 순서를 설정하지 않았다면 기본 키 오름차순으로 정렬합니다. 일치하는 항목이 없으면 하이드레이션되지 않은 레코드가 반환되므로, 뭔가 반환되었는지 확실하지 않다면 `isHydrated()`를 확인하세요.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

`first()`와 같지만 기본 키 내림차순으로 정렬합니다. "가장 최신 항목을 달라"는 쿼리에 유용합니다.

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

현재 조건과 일치하는 행 수를 계산합니다. 쿼리에 `groupBy()`가 있으면 `count()`는 의도적으로 이를 무시합니다. 단일 스칼라 count는 그룹당 한 행을 나타낼 수 없습니다.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

조건과 일치하는 레코드가 있으면 `true`를 반환합니다. 내부적으로 저렴한 `SELECT 1 ... LIMIT 1`을 실행합니다.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

여러 객체를 하이드레이션하는 대신 한 컬럼의 값으로 이루어진 평면 배열을 반환합니다. 고유 값을 얻으려면 `distinct()`와 함께 사용하세요.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

기본 키에 대한 `pluck()`의 바로 가기입니다.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

현재 레코드가 하이드레이션되었는지(데이터베이스에서 가져왔는지) `true`를 반환합니다.

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

텍스트 기반 기본 키(예: UUID)가 있는 경우, 삽입 전에 두 가지 방법 중 하나로 기본 키 값을 설정할 수 있습니다.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 또는 $user->save();
```

또는 이벤트를 통해 기본 키를 자동으로 생성하게 할 수 있습니다.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 위 배열 대신 이 방식으로 primaryKey를 설정할 수도 있습니다.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 또는 고유 ID를 생성하는 데 필요한 방식대로
	}
}
```

삽입 전에 기본 키를 설정하지 않으면 `rowid`로 설정되고
데이터베이스가 이를 생성하지만, 해당 필드가 테이블에 존재하지 않을 수 있으므로
영구 저장되지 않습니다. 이런 이유로 이벤트를 사용하여 자동으로 처리하는 것이 권장됩니다.

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

로드된 레코드의 단일 컬럼을 업데이트하고 저장합니다. `$user->dirty([ 'name' => $value ])->update()`의 바로 가기입니다. 이 기능을 사용하려면 로드된 레코드가 필요합니다.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입하거나 업데이트합니다. 레코드에 id가 있으면 업데이트하고, 그렇지 않으면 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**참고:** 클래스에 관계가 정의되어 있고, 관계가 정의되고 인스턴스화되었으며 업데이트할 더티 데이터가 있으면 해당 관계도 재귀적으로 저장합니다. (v0.4.0 이상)

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

먼저 검색을 실행한 후 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

조건과 일치하는 모든 레코드를 단일 문으로 업데이트합니다. 레코드가 하이드레이션되지 않고 이벤트도 발생하지 않으며, 바로 이것이 빠른 이유입니다. 영향을 받은 행 수를 반환합니다.

두 번째 인수에 `true`를 전달하지 않으면 WHERE 조건 없이 실행을 거부합니다. 미래의 자신이 고마워할 것입니다.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// 네, 정말로 테이블의 모든 행을 업데이트하려는 것입니다
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

조건과 일치하는 모든 레코드를 단일 문으로 삭제합니다. `updateAll()`과 같습니다: 하이드레이션 없음, 이벤트 없음, 그리고 `true`를 전달하지 않으면 WHERE 조건이 필요합니다. 삭제된 행 수를 반환합니다. 주의해서 사용하세요!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

더티 데이터는 레코드에서 변경된 데이터를 의미합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 이 시점까지는 "더티"한 것이 없습니다.

$user->email = 'test@example.com'; // 이제 email이 변경되었으므로 "더티"로 간주됩니다.
$user->update();
// 이제 데이터가 업데이트되어 데이터베이스에 영구 저장되었으므로 더티 데이터가 없습니다

$user->password = password_hash()'newpassword'); // 이제 이것은 더티입니다
$user->dirty(); // 아무것도 전달하지 않으면 모든 더티 항목이 지워집니다.
$user->update(); // 더티로 캡처된 것이 없으므로 아무것도 업데이트되지 않습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name과 password 모두 업데이트됩니다.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

`dirty()` 메서드의 별칭입니다. 무엇을 하는지 조금 더 명확합니다.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name과 password 모두 업데이트됩니다.
```

#### `isDirty(): boolean` (v0.4.0)

현재 레코드가 변경되었으면 `true`를 반환합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 재설정합니다. 반복문 같은 동작에서 사용하기 정말 좋습니다.
`true`를 전달하면 현재 객체를 찾는 데 사용된 쿼리 데이터도 재설정합니다(기본 동작).

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

`find()`, `findAll()`, `insert()`, `update()` 또는 `save()` 메서드를 실행한 후 빌드된 SQL을 가져와 디버깅 목적으로 사용할 수 있습니다.

## 트랜잭션

모두 함께 성공해야 하는 몇 가지 쓰기 작업을 실행해야 하나요? `transaction()`(v0.8.0)으로 감싸세요. 콜러블을 전달하면 레코드가 인수로 들어옵니다. 콜러블이 예외를 던지면 모든 것이 롤백되고 예외가 다시 throw됩니다. 그렇지 않으면 커밋되고 콜러블이 반환한 값을 그대로 돌려줍니다.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// 아무것도 throw되지 않으면 여기서 커밋이 발생합니다
});
```

중첩 트랜잭션은 지원되지 않으므로(세이브포인트 없음) 평면적으로 유지하세요.

## SQL 쿼리 메서드
#### `select(string $field1 [, string $field2 ... ])`

원한다면 테이블의 일부 컬럼만 선택할 수 있습니다(컬럼이 많은 매우 넓은 테이블에서 더 성능이 좋습니다)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

기술적으로 다른 테이블도 선택할 수 있습니다! 왜 안 되겠어요?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블과 조인할 수도 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 where 인수를 설정할 수 있습니다(이 where 문에서는 매개변수를 설정할 수 없습니다)

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 참고** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();` 같은 것을 하고 싶을 수 있습니다. 제발 이렇게 하지 마세요!!! 이것은 SQL 인젝션 공격으로 알려진 것에 취약합니다. 온라인에 많은 글이 있으니 "sql injection attacks php"를 Google에서 검색하면 이 주제에 관한 많은 글을 찾을 수 있습니다. 이 라이브러리에서 이를 처리하는 올바른 방법은 이 `where()` 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();`와 같이 하는 것입니다. 정말로 이렇게 해야 한다면 `PDO` 라이브러리에 `$pdo->quote($var)`가 있어 이스케이프할 수 있습니다. `quote()`를 사용한 후에만 `where()` 문에서 사용할 수 있습니다.

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

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()`와 `orderBy()`는 원시 SQL 조각을 받으며, `'name DESC'`를 하드코딩할 때는 괜찮습니다. 컬럼 이름이 사용자 입력(예: 정렬 가능한 테이블 헤더)에서 오는 경우에는 대신 `orderByColumn()`을 사용하세요. 일반 컬럼 이름과 `table.column` 경로만 허용되며 방향은 `ASC` 또는 `DESC`여야 하므로 주입할 것이 없습니다.

```php
// $sortColumn은 요청에서 옵니다
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드 수를 제한합니다. 두 번째 int가 주어지면 SQL에서처럼 offset, limit이 됩니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

다음 쿼리에 `DISTINCT`를 추가합니다. 일반 select와 `pluck()`에서 작동합니다. `count()`는 이를 무시합니다. 단일 집계 행에 `DISTINCT`를 넣어도 아무 효과가 없기 때문입니다.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

조건을 OR 문으로 감쌀 수 있습니다. 이는 `startWrap()` 및 `endWrap()` 메서드를 사용하거나 필드와 값 뒤에 조건의 세 번째 매개변수를 채워서 수행합니다.

```php
// 방법 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 이는 `id = 1 AND (name = 'demo' OR name = 'test')`로 평가됩니다

// 방법 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 이는 `id = 1 OR name = 'demo'`로 평가됩니다
```

## 스코프

스코프(v0.8.0)는 재사용 가능한 쿼리 체인으로, 클래스에서 `$this`를 반환하는 일반 인스턴스 메서드로 정의됩니다. 한 번 작성하면 다른 쿼리 메서드처럼 체이닝됩니다.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// 이제 쿼리가 문장처럼 읽힙니다
(new User($pdo_connection))->active()->recent(30)->findAll();
```

`scope()`로 이름을 통해 스코프를 호출할 수도 있습니다. 스코프 이름이 코드의 다른 곳에서 올 때 유용합니다. 메서드가 존재하지 않으면 `BadMethodCallException`을 throw합니다.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## 관계
이 라이브러리를 사용하여 여러 종류의 관계를 설정할 수 있습니다. 테이블 간에 일대다 및 일대일 관계를 설정할 수 있습니다. 이를 위해서는 클래스에서 약간의 추가 설정이 필요합니다.

`$relations` 배열을 설정하는 것은 어렵지 않지만, 올바른 구문을 추측하는 것은 혼란스러울 수 있습니다.

```php
protected array $relations = [
	// 키 이름은 원하는 대로 지정할 수 있습니다. ActiveRecord 이름이 아마 좋습니다. 예: user, contact, client
	'user' => [
		// 필수
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 관계 유형입니다

		// 필수
		'Some_Class', // 이것이 참조할 "다른" ActiveRecord 클래스입니다

		// 필수
		// 관계 유형에 따라
		// self::HAS_ONE = 조인을 참조하는 외래 키
		// self::HAS_MANY = 조인을 참조하는 외래 키
		// self::BELONGS_TO = 조인을 참조하는 로컬 키
		'local_or_foreign_key',
		// 참고로, 이것은 "다른" 모델의 기본 키에만 조인합니다

		// 선택 사항
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 관계를 조인할 때 원하는 추가 조건
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 선택 사항
		'back_reference_name' // 이 관계를 자기 자신으로 역참조하려는 경우입니다. 예: $user->contact->user;
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

이제 참조 설정이 되었으므로 매우 쉽게 사용할 수 있습니다!

```php
$user = new User($pdo_connection);

// 가장 최근 사용자를 찾습니다.
$user->notNull('id')->orderBy('id desc')->find();

// 관계를 사용하여 contacts 가져오기:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 또는 반대 방향으로 갈 수도 있습니다.
$contact = new Contact();

// 하나의 contact 찾기
$contact->find();

// 관계를 사용하여 user 가져오기:
echo $contact->user->name; // 이것은 사용자 이름입니다
```

꽤 멋지죠?

### 즉시 로딩

#### 개요
즉시 로딩은 관계를 미리 로드하여 N+1 쿼리 문제를 해결합니다. 각 레코드의 관계마다 별도 쿼리를 실행하는 대신, 즉시 로딩은 관계당 단 하나의 추가 쿼리로 모든 관련 데이터를 가져옵니다.

> **참고:** 즉시 로딩은 v0.7.0 이상에서만 사용할 수 있습니다.

#### 기본 사용법
즉시 로드할 관계를 지정하려면 `with()` 메서드를 사용하세요:
```php
// N+1 대신 2개의 쿼리로 contacts와 함께 users 로드
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // 추가 쿼리 없음!
    }
}
```

#### 여러 관계
여러 관계를 한 번에 로드합니다:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### 관계 유형

##### HAS_MANY
```php
// 각 사용자에 대해 모든 contacts를 즉시 로드
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts는 이미 배열로 로드되어 있습니다
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// 각 사용자에 대해 하나의 contact를 즉시 로드
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact는 이미 객체로 로드되어 있습니다
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// 모든 contacts에 대해 상위 users를 즉시 로드
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user는 이미 로드되어 있습니다
    echo $c->user->name;
}
```
##### find()와 함께
즉시 로딩은 
findAll()
과 
find()
 모두에서 작동합니다:

```php
$user = $user->with('contacts')->find(1);
// 사용자와 모든 contacts가 2개의 쿼리로 로드됨
```
#### 성능상 이점
즉시 로딩을 사용하지 않는 경우(N+1 문제):
```php
$users = $user->findAll(); // 1개 쿼리
foreach ($users as $u) {
    $contacts = $u->contacts; // N개 쿼리(사용자당 하나!)
}
// 총계: 1 + N 쿼리
```

즉시 로딩을 사용하는 경우:

```php
$users = $user->with('contacts')->findAll(); // 총 2개 쿼리
foreach ($users as $u) {
    $contacts = $u->contacts; // 추가 쿼리 0개!
}
// 총계: 2개 쿼리(users용 1개 + 모든 contacts용 1개)
```
사용자 10명의 경우 쿼리가 11개에서 2개로 줄어듭니다 - 82% 감소입니다!

#### 중요 참고 사항
- 즉시 로딩은 완전히 선택 사항입니다 - 지연 로딩은 이전처럼 계속 작동합니다
- 이미 로드된 관계는 자동으로 건너뜁니다
- 역참조는 즉시 로딩과 함께 작동합니다
- 관계 콜백은 즉시 로딩 중에도 존중됩니다

#### 제한 사항
- 중첩 즉시 로딩(예: 
with(['contacts.addresses'])
)은 현재 지원되지 않습니다
- 클로저를 통한 즉시 로드 제약 조건은 이 버전에서 지원되지 않습니다

## 사용자 정의 데이터 설정
때로는 ActiveRecord에 고유한 무언가를 첨부해야 할 수 있습니다. 예를 들어 템플릿에 전달될 객체에 첨부하는 것이 더 쉬운 사용자 정의 계산 같은 것입니다.

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` 메서드로 사용자 정의 데이터를 첨부합니다.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

그런 다음 일반 객체 속성처럼 참조하면 됩니다.

```php
echo $user->page_view_count;
```

## 타임스탬프

테이블에 `created_at` 및 `updated_at` 컬럼이 있으면 라이브러리가 이를 채워주도록 할 수 있습니다(v0.8.0). 클래스에 `protected bool $timestamps = true;`를 설정하면 삽입할 때 `created_at`과 `updated_at`을 설정하고, 업데이트할 때 `updated_at`을 설정합니다. 형식은 `Y-m-d H:i:s`입니다. 두 컬럼 중 하나를 직접 설정하면 라이브러리는 해당 값을 그대로 둡니다.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

테이블에 실제로 해당 컬럼이 있어야 합니다. 그렇지 않으면 삽입 및 업데이트가 실패합니다.

## 이벤트

이 라이브러리의 또 하나의 굉장한 기능은 이벤트입니다. 이벤트는 호출하는 특정 메서드에 따라 특정 시점에 트리거됩니다. 데이터를 자동으로 설정하는 데 매우 매우 유용합니다.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

기본 연결 같은 것을 설정해야 할 때 정말 유용합니다.

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
		
		// 이 방식으로 테이블 이름을 설정할 수도 있습니다.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

매번 쿼리 조작이 필요할 때만 유용할 것입니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 그게 취향이라면 항상 id >= 0 실행
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

이것은 이 레코드가 가져와질 때마다 항상 어떤 로직을 실행해야 할 때 더 유용할 것입니다. 무언가를 복호화해야 하나요? 매번 사용자 정의 count 쿼리를 실행해야 하나요(성능은 안 좋지만 뭐 어때요)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 무언가 복호화
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 어쩌면 쿼리 같은 사용자 정의 데이터를 저장할 수도???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

매번 쿼리 조작이 필요할 때만 유용할 것입니다.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 그게 취향이라면 항상 id >= 0 실행
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`와 비슷하지만 모든 레코드에 대해 수행할 수 있습니다!

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

매번 일부 기본값을 설정해야 할 때 정말 유용합니다.

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

삽입된 후 데이터를 변경해야 하는 사용 사례가 있나요?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 알아서 하세요
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 아니면 뭐든지....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

업데이트할 때마다 일부 기본값을 설정해야 할 때 정말 유용합니다.

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

업데이트된 후 데이터를 변경해야 하는 사용 사례가 있나요?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 알아서 하세요
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 아니면 뭐든지....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

삽입 또는 업데이트가 발생할 때 모두 이벤트가 발생하게 하려면 유용합니다. 긴 설명은 생략하겠지만, 무엇인지 짐작할 수 있을 것입니다.

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

여기서 무엇을 하고 싶은지 모르겠지만, 판단하지 않겠습니다! 해보세요!

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

이 라이브러리를 사용할 때 데이터베이스 연결을 몇 가지 다른 방식으로 설정할 수 있습니다. 생성자에서 연결을 설정하거나, 구성 변수 `$config['connection']`을 통해 설정하거나, `setDatabaseConnection()`(v0.4.1)을 통해 설정할 수 있습니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 예를 들어
$user = new User($pdo_connection);
// 또는
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 또는
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

active record를 호출할 때마다 항상 `$database_connection`을 설정하는 것을 피하고 싶다면, 방법이 있습니다!

```php
// index.php 또는 bootstrap.php
// Flight에 등록된 클래스로 설정
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// 이제 인수가 필요 없습니다!
$user = new User();
```

> **참고:** 단위 테스트를 계획하고 있다면 이 방식은 단위 테스트에 몇 가지 어려움을 더할 수 있지만, 전반적으로 `setDatabaseConnection()` 또는 `$config['connection']`으로 연결을 주입할 수 있기 때문에 그렇게 나쁘지는 않습니다.

데이터베이스 연결을 새로 고쳐야 하는 경우, 예를 들어 오래 실행되는 CLI 스크립트를 실행 중이고 때때로 연결을 새로 고쳐야 한다면 `$your_record->setDatabaseConnection($pdo_connection)`으로 연결을 다시 설정할 수 있습니다.

## 기여

부탁드립니다. :D

### 설정

기여할 때는 100% 테스트 커버리지를 유지하기 위해 `composer test-coverage`를 실행하세요(이것은 진정한 단위 테스트 커버리지가 아니라 통합 테스트에 가깝습니다).

또한 린팅 오류를 수정하려면 `composer beautify`와 `composer phpcs`를 실행하세요.

## 라이선스

MIT