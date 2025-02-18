# Flight 活跃记录

活跃记录是将数据库实体映射到 PHP 对象。简单来说，如果您的数据库中有一个用户表，您可以将该表中的一行“翻译”为 `User` 类和代码库中的 `$user` 对象。参见 [基本示例](#基本示例)。

点击 [这里](https://github.com/flightphp/active-record) 访问 GitHub 上的仓库。

## 基本示例

假设您有以下表：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

现在，您可以设置一个新类来表示该表：

```php
/**
 * 一般情况下，ActiveRecord 类是单数的
 * 
 * 强烈建议在此添加表的属性作为注释
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 您可以这样设置
		parent::__construct($database_connection, 'users');
		// 或者这样
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

现在看看魔法是如何发生的！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这只是一个例子，您可能会使用实际的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者使用非对象创建的 mysqli
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// 或 $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// 在这里不能使用 $user->save() 否则会认为是更新！

echo $user->id; // 2
```

新增用户就是这么简单！现在数据库中有用户行，您该如何提取它？

```php
$user->find(1); // 在数据库中查找 id = 1 并返回。
echo $user->name; // 'Bobby Tables'
```

如果您想查找所有用户呢？

```php
$users = $user->findAll();
```

某个条件下呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看看这有多有趣？让我们安装它并开始吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 用法

这可以作为独立库或与 Flight PHP 框架一起使用。完全由您决定。

### 独立使用
只需确保您将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是一个例子，您可能会使用实际的数据库连接

$User = new User($pdo_connection);
```

> 不想在构造函数中总是设置数据库连接？请查阅 [数据库连接管理](#数据库连接管理) 获取其他想法！

### 在 Flight 中注册为方法
如果您正在使用 Flight PHP 框架，可以将 ActiveRecord 类注册为服务，但实际上您不必这样做。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后您可以在控制器、函数等中像这样使用它。

Flight::user()->find(1);
```

## `runway` 方法

[runway](https://docs.flightphp.com/awesome-plugins/runway) 是一个用于 Flight 的 CLI 工具，为此库提供了一个自定义命令。

```bash
# 用法
php runway make:record database_table_name [class_name]

# 示例
php runway make:record users
```

这将在 `app/records/` 目录中创建一个名为 `UserRecord.php` 的新类，内容如下：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * 用户表的 ActiveRecord 类。
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
     * @var array $relations 设置模型的关系
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * 构造函数
     * @param mixed $databaseConnection 数据库连接
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果您传递某种 `$id`，它将使用该值在主键上执行查找。如果没有传递，它将仅查找表中的第一条记录。

此外，您可以传递其他辅助方法来查询您的表。

```php
// 在此之前查找带有一些条件的记录
$user->notNull('password')->orderBy('id DESC')->find();

// 根据特定 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找您指定的表中的所有记录。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

如果当前记录已经被填充（从数据库中提取），则返回 `true`。

```php
$user->find(1);
// 如果找到带有数据的记录...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

将当前记录插入数据库。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### 基于文本的主键

如果您有一个基于文本的主键（例如 UUID），您可以通过两种方式中的一种在插入之前设置主键值。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 或 $user->save();
```

或者您可以通过事件自动为您生成主键。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 您也可以以这种方式设置主键，而不是上面的数组。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 或者根据需要生成您的唯一 ID
	}
}
```

如果在插入之前未设置主键，它将设置为 `rowid`，并且数据库将为您生成它，但它将无法持久化，因为该字段可能在您的表中不存在。这就是为什么建议使用事件来自动处理这一切。

#### `update(): boolean|ActiveRecord`

将当前记录更新到数据库。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库。如果记录有 id，则将更新；否则将插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果您在类中定义了关系，如果已定义、实例化并有脏数据需要更新，它将递归保存这些关系。（v0.4.0 及更高版本）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

您还可以在执行搜索之前删除多个记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据是指在记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时没有数据是“脏”的。

$user->email = 'test@example.com'; // 现在电子邮件被认为是“脏”的，因为它已更改。
$user->update();
// 现在没有数据是脏的，因为它已更新并持久化到数据库中

$user->password = password_hash()'newpassword'); // 现在这是脏的
$user->dirty(); // 不传递任何内容将清除所有脏条目。
$user->update(); // 不会更新任何内容，因为没有被捕获为脏。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名称和密码都已更新。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

这是 `dirty()` 方法的别名。它稍微更清楚您在做什么。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名称和密码都已更新。
```

#### `isDirty(): boolean` (v0.4.0)

如果当前记录已更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。在循环类型的行为中，这非常好。如果您传递 `true`，它也将重置用于查找当前对象的查询数据（默认行为）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 从干净的状态开始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

在您运行 `find()`、`findAll()`、`insert()`、`update()` 或 `save()` 方法后，您可以获取构建的 SQL，并将其用于调试目的。

## SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果您愿意，可以只选择表中的少数几列（在非常宽且有许多列的表中，这更高效）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

您也可以选择其他表！为什么不呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

您甚至可以在数据库中连接其他表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

您可以设置一些自定义的 where 参数（在此 where 语句中无法设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全提示** - 您可能会被诱使做类似 `$user->where("id = '{$id}' AND name = '{$name}'")->find();`的事情。请不要这样做！这容易受到所谓的 SQL 注入攻击。网上有很多相关文章，请在谷歌中搜索 "sql injection attacks php"，您会找到很多关于这个主题的文章。使用此库的正确处理方式是，与其使用此 `where()` 方法，您可以做像这样更合理的 `$user->eq('id', $id)->eq('name', $name)->find();` 如果您绝对必须这样做，`PDO` 库有 `$pdo->quote($var)` 来为您转义它。仅在使用 `quote()` 后才能在 `where()` 语句中使用它。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

以某种方式对返回的查询进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果给出第二个整数，它将像 SQL 一样偏移和限制。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

哪里 `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

哪里 `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

哪里 `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

哪里 `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

哪里 `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

哪里 `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

哪里 `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

哪里 `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

哪里 `field LIKE $value` 或 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

哪里 `field IN($value)` 或 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

哪里 `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR 条件

可以将条件包装在 OR 语句中。通过使用 `startWrap()` 和 `endWrap()` 方法，或者通过在字段和值之后填写条件的第 3 个参数来实现。

```php
// 方法 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 这将评估为 `id = 1 AND (name = 'demo' OR name = 'test')`

// 方法 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 这将评估为 `id = 1 OR name = 'demo'`
```

## 关系
您可以使用此库设置几种关系。您可以在表之间设置一对多和一对一的关系。 这需要在类中事先做一些额外的设置。

设置 `$relations` 数组并不难，但猜测正确的语法可能会让人困惑。

```php
protected array $relations = [
	// 您可以为键命名为您喜欢的任何内容。ActiveRecord 的名称可能是一个不错的选择。示例：user, contact, client
	'user' => [
		// 必需
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 这是关系的类型

		// 必需
		'Some_Class', // 这是将引用的“其他”ActiveRecord 类

		// 必需
		// 根据关系类型
		// self::HAS_ONE = 引用连接的外键
		// self::HAS_MANY = 引用连接的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 仅供参考，此外， 这仅连接到“其他”模型的主键

		// 可选
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 连接关系时您希望的其他条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 可选
		'back_reference_name' // 如果您希望将此关系反向引用到其自身，例如：$user->contact->user;
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

现在我们设置了引用，可以非常轻松地使用它们！

```php
$user = new User($pdo_connection);

// 找到最新的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 使用关系获取联系信息：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 或我们可以反向进行。
$contact = new Contact();

// 查找一条联系记录
$contact->find();

// 使用关系获取用户：
echo $contact->user->name; // 这是用户名
```

很酷吧？

## 设置自定义数据
有时您可能需要将某些唯一的内容附加到您的 ActiveRecord，例如可能更容易附加到传递给模板的对象的自定义计算。

#### `setCustomData(string $field, mixed $value)`
您可以使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后，您可以像正常对象属性一样引用它。

```php
echo $user->page_view_count;
```

## 事件

此库的一个超级棒的功能是事件。事件在您调用的某些方法的特定时间触发。它们在自动为您设置数据时非常有用。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果您需要设置默认连接或类似的东西，这非常有用。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 别忘了&引用
		// 您可以这样自动设置连接
		$config['connection'] = Flight::db();
		// 或者这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 您还可以通过这种方式设置表名。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

如果您每次都需要查询操作，这可能只有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 始终运行 id >= 0 如果这是你的要求
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果每次获取此记录时都需要运行某些逻辑，这可能更实用。您需要解密某些内容吗？您是否需要每次运行自定义计数查询（性能不佳，但无所谓）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些内容
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 也许存储一些自定义内容，如查询???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

如果您每次都需要查询操作，这可能只有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 始终运行 id >= 0 如果这是你的要求
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

类似于 `afterFind()`，但您可以对所有记录做此操作！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// 做一些酷炫的事情，比如 afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

如果您每次需要设置一些默认值，这个非常有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 设置一些合理的默认值
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

您是否有用户案例在插入后更改数据？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 随你便
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 或其他....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

如果您每次更新时需要设置一些默认值，这个非常有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 设置一些合理的默认值
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

您是否有用户案例在更新后更改数据？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 随你便
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 或其他....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果您希望在插入或更新发生时都发生事件，这很有用。我将省略更长的解释，但我相信您可以猜到它是什么。

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

不确定您想在这里做什么，但没有任何判断！去吧！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '他曾是一个勇敢的士兵... :cry-face:';
	} 
}
```

## 数据库连接管理

使用此库时，您可以通过几种不同方式设置数据库连接。您可以在构造函数中设置连接，您可以通过配置变量 `$config['connection']` 来设置它，或者您可以通过 `setDatabaseConnection()` （v0.4.1） 来设置它。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例如
$user = new User($pdo_connection);
// 或者
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 或者
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

如果您希望在每次调用活跃记录时避免总是设置 `$database_connection`，也有方法可以做到这一点！

```php
// index.php 或 bootstrap.php
// 在 Flight 中注册此类
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// 现在，不需要参数!
$user = new User();
```

> **注意：** 如果您计划进行单元测试，采用这种方式可能会给单元测试带来一些挑战，但总体而言，由于您可以使用 `setDatabaseConnection()` 或 `$config['connection']` 注入连接，这并不会太糟糕。

如果您需要刷新数据库连接，例如如果您正在运行一个长时间运行的 CLI 脚本，并且需要定期刷新连接，您可以使用 `$your_record->setDatabaseConnection($pdo_connection)` 重新设置连接。

## 贡献

请务必这样做。:D

### 设置

当您贡献时，请确保运行 `composer test-coverage` 以保持 100% 的测试覆盖率（这不是实际的单元测试覆盖率，更像是集成测试）。

还请确保运行 `composer beautify` 和 `composer phpcs` 来修复任何格式错误。

## 许可证

MIT