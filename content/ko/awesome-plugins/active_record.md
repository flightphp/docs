# Flight Active Record

활성 레코드는 데이터베이스 엔터티를 PHP 객체에 매핑하는 것을 의미합니다. 간단히 말해서, 데이터베이스에 사용자 테이블이 있다면 그 테이블의 행을 `User` 클래스 및 `$user` 객체로 "변환"할 수 있습니다. [기본 예제](#기본-예제)를 참조하세요.

GitHub의 리포지토리는 [여기](https://github.com/flightphp/active-record)에서 확인할 수 있습니다.

## 기본 예제

다음과 같은 테이블이 있다고 가정해 봅시다.

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

이제 이 테이블을 나타내는 새로운 클래스를 설정할 수 있습니다.

```php
/**
 * 활성 레코드 클래스는 일반적으로 단수형으로 표현됩니다
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

이제 마법이 벌어집니다!

```php
// sqlite 사용 시
$database_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며 실제 데이터베이스 연결을 사용할 것입니다

// mysql 사용 시
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 또는 mysqli 사용 시
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 또는 객체 기반 생성을 사용하는 mysqli
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
// 여기서는 $user->save()를 사용할 수 없으며 갱신으로 인식합니다!

echo $user->id; // 2
```

새로운 사용자를 추가하는 것이 이렇게 쉬웠습니다! 이제 데이터베이스에 사용자 행이 있으므로 이를 어떻게 가져올까요?

```php
$user->find(1); // 데이터베이스에서 id = 1을 찾아서 반환합니다.
echo $user->name; // 'Bobby Tables'
```

그리고 모든 사용자를 찾으려면 어떻게 해야 할까요?

```php
$users = $user->findAll();
```

어떤 조건으로 찾고 싶을 때는 어떻게 해야 할까요?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

이것이 얼마나 재미있는지 보셨나요? 설치하고 시작해 봅시다!

## 설치

Composer로 간단히 설치하세요

```php
composer require flightphp/active-record 
```

## 사용법

이 라이브러리는 독립형 라이브러리로 사용하거나 Flight PHP 프레임워크와 함께 사용할 수 있습니다. 완전히 사용하는 것은 여러분의 몫입니다.

### 독립적으로
생성자에 PDO 연결을 전달해야 합니다.

```php
$pdo_connection = new PDO('sqlite:test.db'); // 이것은 예시일 뿐이며 실제 데이터베이스 연결을 사용할 것입니다

$User = new User($pdo_connection);
```

> 생성자에서 항상 데이터베이스 연결을 설정하고 싶지 않은가요? 다른 방법은 [데이터베이스 연결 관리](#데이터베이스-연결-관리)를 참조하세요!

### Flight 내 메서드로 등록
Flight PHP 프레임워크를 사용하는 경우 ActiveRecord 클래스를 서비스로 등록할 수 있지만, 실제로 그렇게 할 필요는 없습니다.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 그러면 컨트롤러, 함수 등에서 다음과 같이 사용할 수 있습니다.

Flight::user()->find(1);
```

## `runway` 메서드

[runway](https://docs.flightphp.com/awesome-plugins/runway)는 이 라이브러리에 대한 사용자 정의 명령이 있는 Flight를 위한 CLI 도구입니다.

```bash
# 사용법
php runway make:record database_table_name [class_name]

# 예시
php runway make:record users
```

이로써 `app/records/` 디렉토리에 `UserRecord.php`로 새 클래스가 생성됩니다.

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 테이블을 위한 활성 레코드 클래스.
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

레코드 하나를 찾아 현재 객체에 할당합니다. 어떤 종류의 `$id`를 전달하면 해당 값의 기본 키 조회를 수행합니다. 아무 것도 전달하지 않으면 테이블에서 첫 번째 레코드를 찾습니다.

또한 테이블을 쿼리하는 추가적인 헬퍼 메서드를 전달할 수도 있습니다.

```php
// 조건을 먼저 설정한 후 레코드 찾기
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

현재 레코드가 가져온지 여부를 반환합니다.

```php
$user->find(1);
// 데이터가 있는 경우...
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

UUID와 같은 텍스트 기반 기본 키(`INSERT` 전에 설정해야 함)를 사용하는 경우, 기본 키 값 설정에 두 가지 방법이 있습니다.

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
		// 위의 배열 대신 이렇게 기본 키를 설정할 수도 있습니다.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 또는 고유 ID 생성이 필요한 방식으로 설정
	}
}
```

기본 키를 설정하지 않고 삽입하는 경우, `rowid`로 설정되며 데이터베이스에서 생성되지만 존재하지 않을 수 있습니다.
이런 이유로 이 이벤트를 사용하여 자동으로 처리할 것이 권장됩니다.

#### `update(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에서 업데이트합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

현재 레코드를 데이터베이스에 삽입하거나 업데이트합니다. 레코드에 id가 있다면 업데이트하고, 그렇지 않으면 삽입합니다.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**참고:** 클래스에 관계가 정의된 경우, 정의된 경우 이러한 관계도 업데이트할 때까지 계속 업데이트됩니다.

#### `delete(): boolean`

현재 레코드를 데이터베이스에서 삭제합니다.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

사전에 검색을 실행한 후 여러 레코드를 삭제할 수도 있습니다.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

'변경된' 데이터는 레코드에서 변경된 데이터를 의미합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 현재로는 아무 것도 "변경된" 것이 없습니다.

$user->email = 'test@example.com'; // 이제 email이 변경되었으므로 "변경된"으로 간주됩니다.
$user->update();
// 이제 데이터가 변경되어 '변경된' 데이터가 없어졌습니다(데이터는 업데이트되어 데이터베이스에 저장되었음).

$user->password = password_hash()'newpassword'); // 이제 이것이 변경된 상태입니다.
$user->dirty(); // 아무 것도 전달하지 않으면 모든 변경된 항목이 지워집니다.
$user->update(); // 변경된 데이터가 없으므로 업데이트되지 않습니다.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name과 password가 모두 업데이트됩니다.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

이는 `dirty()` 메서드의 별칭입니다. 하는 작업이 무엇인지 명확히 알 수 있습니다.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name과 password가 모두 업데이트됩니다.
```

#### `isDirty(): boolean` (v0.4.0)

현재 레코드가 변경되었으면 `true`를 반환합니다.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

현재 레코드를 초기 상태로 재설정합니다. 이는 루프 유형 동작에서 아주 유용합니다. `true`를 전달하면 현재 개체를 찾을 때 사용된 쿼리 데이터도 재설정합니다(기본 동작).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 초기 상태로 재설정
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, 또는 `save()` 메서드를 실행한 후에 생성된 SQL을 검색하여 디버깅 목적으로 사용할 수 있습니다.

## SQL 쿼리 메서드

#### `select(string $field1 [, string $field2 ... ])`

원하는 컬럼만 선택할 수 있습니다(많은 컬럼이 있는 넓은 테이블에서 효율적입니다).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

다른 테이블을 선택할 수도 있습니다!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

데이터베이스의 다른 테이블과 조인할 수 있습니다.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

사용자 정의 WHERE 인수를 설정할 수 있습니다(이 WHERE 문에서는 매개변수를 설정할 수 없습니다).

```php
$user->where('id=1 AND name="demo"')->find();
```

**보안 주의사항** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`와 같은 방식으로 하시지 마세요! 이는 SQL 주입 공격에 취약합니다. 온라인으로 많은 문서를 찾을 수 있으니 "php sql injection attacks"를 구글링하면 많은 글을 볼 수 있습니다. 이 라이브러리를 사용하는 경우 이러한 `where()` 메서드 대신 `$user->eq('id', $id)->eq('name', $name)->find();` 같은 방식을 사용하세요. 반드시 해야 한다면 `PDO` 라이브러리가 `$pdo->quote($var)`로 이스케이핑하여 사용 가능하다는 것을 염두에 두세요. `quote()` 사용 후에 그 값이 `where()` 문에서 사용 가능합니다.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

결과를 특정 조건에 따라 그룹화합니다.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

쿼리 결과를 정해진 방식으로 정렬합니다.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

반환되는 레코드의 양을 제한합니다. 두 번째 int가 제공되면 오프셋이 되며 SQL의 `LIMIT`와 같이 작동합니다.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 조건

#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` 조건에 해당합니다.

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` 조건에 해당합니다.

```php
$user->ne'그룹화' 메서드는 find() 호출에서 존재하는 결과를 객체 내의 모든 레코드에 수행합니다.이것은 일반적으로 find()나 findAll() 후에 다른 로직을 적용하려고 하는 경우에 사용됩니다.
### afterFindAll(array<int,ActiveRecord> $results)

이것은 afterFind()와 유사하지만 모든 레코드에 대해서 작용합니다.

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterFindAll(array $results) {

        foreach($results as $self) {
            // "afterFind()"처럼 멋진 작업을 수행합니다.
        }
    } 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)/afterInsert(ActiveRecord $ActiveRecord)`

항상 어떤 값을 설정해야 하는 경우에 유용합니다.

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeInsert(self $self) {
        // 일부 의미있는 기본값 설정
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

데이터 삽입 후 값을 변경하지 않을까요?

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterInsert(self $self) {
        // 자유롭게 해보세요
        Flight::cache()->set('most_recent_insert_id', $self->id);
        // 또는 어떤 작업이든....
    } 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

항상 업데이트 시 일부 기본 값을 설정해야 하는 경우 유용합니다.

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeInsert(self $self) {
        // 일부 의미있는 기본값 설정
        if(!$self->updated_date) {
            $self->updated_date = gmdate('Y-m-d');
        }
    } 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

데이터 업데이트 후 데이터를 변경하는 케이스가 있을 수 있을까요?

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterInsert(self $self) {
        // 어떤 로직이든
        Flight::cache()->set('most_recently_updated_user_id', $self->id);
        // 또는 무슨 작업이든....
    } 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

이는 삽입하거나 업데이트할 때 이벤트가 발생하도록 하는 것입니다. 전부 유사하게 추가할 수 있으며 일반적인 이벤트라는 것을 알 수 있습니다.

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

여기서 하고 싶은 것이 무엇인지는 모르겠지만 괜찮아요! 모두 해주세요!

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeDelete(self $self) {
        echo '그는 용감한 전사였습니다... :cry-face:';
    } 
}
```