# Flight 活动记录

活动记录是将数据库实体映射到 PHP 对象。简单来说，如果你在数据库中有一个用户表，你可以将该表中的一行“转换”为一个 `User` 类和一个 `$user` 对象。在你的代码库中。请参见 [基本示例](#basic-example)。

点击 [这里](https://github.com/flightphp/active-record) 查看 GitHub 中的代码库。

## 基本示例

假设你有如下表格：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

现在你可以设置一个新类来表示这个表：

```php
/**
 * 一个 ActiveRecord 类通常是单数
 * 
 * 强烈建议在这里添加表的属性作为注释
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 你可以这样设置
		parent::__construct($database_connection, 'users');
		// 或者这样
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

现在看看魔法发生吧！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这只是示例，你可能会使用真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者使用非对象基础的 mysqli 创建
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
// 这里不能使用 $user->save() 否则会认为这是一次更新！

echo $user->id; // 2
```

就这么简单地添加了一个新用户！现在数据库中有一行用户，怎么提取出来呢？

```php
$user->find(1); // 查找 ID = 1 在数据库中并返回它。
echo $user->name; // 'Bobby Tables'
```

如果你想查找所有用户呢？

```php
$users = $user->findAll();
```

特定条件呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看看这有多有趣？让我们安装并开始吧！

## 安装

简单地使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 用法

这可以作为独立库或与 Flight PHP 框架一起使用。完全根据你的选择。

### 独立
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是示例，你可能会使用真实的数据库连接

$User = new User($pdo_connection);
```

> 不想总是在构造函数中设置数据库连接？请查看 [数据库连接管理](#database-connection-management) 以获取其他想法！

### 在 Flight 中注册为一个方法
如果你正在使用 Flight PHP 框架，你可以将 ActiveRecord 类注册为服务，但你其实不必这样做。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后你可以在控制器、函数等中这样使用它。

Flight::user()->find(1);
```

## `runway` 方法

[runway](/awesome-plugins/runway) 是一个用于 Flight 的 CLI 工具，具有该库的自定义命令。

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
     * @param mixed $databaseConnection 数据库的连接
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果你传递某种 `$id`，它将使用该值在主键上执行查找。如果没有传递，它将只查找表中的第一条记录。

此外，你可以传递其他辅助方法来查询你的表。

```php
// 在预先定义某些条件的情况下查找记录
$user->notNull('password')->orderBy('id DESC')->find();

// 通过特定 ID 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找你指定的表中的所有记录。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

如果当前记录已经被水合（从数据库中获取），则返回 `true`。

```php
$user->find(1);
// 如果找到包含数据的记录...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

将当前记录插入到数据库中。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### 基于文本的主键

如果你有一个基于文本的主键（例如 UUID），你可以通过两种方式在插入前设置主键值。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 或 $user->save();
```

或者你可以通过事件自动为你生成主键。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 你也可以像上面的数组那样这样设置 primaryKey。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 或者按照你需要的方式生成唯一 ID
	}
}
```

如果你在插入之前没有设置主键，它将设置为 `rowid`，而数据库将为你生成它，但它不会持久化，因为该字段可能不存在于你的表中。这就是为什么推荐使用事件自动处理这个。

#### `update(): boolean|ActiveRecord`

将当前记录更新到数据库中。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库中。如果记录有 ID，将更新，否则将插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果你在类中定义了关系，如果它们已被定义、实例化并且有脏数据需要更新，则也会递归保存这些关系。（v0.4.0 及以上）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

你还可以在执行搜索之前删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据是指在记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时没有“脏数据”。

$user->email = 'test@example.com'; // 现在 email 被视为“脏”，因为它已更改。
$user->update();
// 现在没有脏数据，因为它已在数据库中更新并持久化

$user->password = password_hash('newpassword'); // 现在这是脏的
$user->dirty(); // 不传递任何内容将清除所有脏条目。
$user->update(); // 因为没有捕获到脏数据，什么都不会更新。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都被更新。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

这是 `dirty()` 方法的别名。这更清楚你在做什么。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都被更新。
```

#### `isDirty(): boolean` (v0.4.0)

如果当前记录已更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为初始状态。这在循环类型行为中非常好用。
如果你传递 `true`，它还将重置用于查找当前对象的查询数据（默认行为）。

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

在你运行 `find()`、`findAll()`、`insert()`、`update()` 或 `save()` 方法后，你可以获取构建的 SQL 并用于调试目的。

## SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果你愿意，可以仅选择表中的几个列（在列很多的情况下性能更好）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

你也可以选择其他表！为什么不呢？！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

你甚至可以与数据库中的另一张表进行连接。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

你可以设置一些自定义的 where 参数（你不能在这个 where 语句中设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全注意** - 你可能会被诱惑做类似 `$user->where("id = '{$id}' AND name = '{$name}'")->find();` 的事情。请不要这样做！！！这会受到 SQL 注入攻击的影响。网络上有很多文章，请谷歌“sql injection attacks php”，你会找到很多关于这个主题的文章。处理这个库的正确方式是，而不是使用 `where()` 方法，你可以这样做 `$user->eq('id', $id)->eq('name', $name)->find();` 如果你绝对需要这样做，`PDO` 库有 `$pdo->quote($var)` 来帮助你转义。在使用 `quote()` 之后，你才能在 `where()` 语句中使用它。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

以特定方式对返回的查询进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数。如果给定第二个整数，它将作为偏移量，就像在 SQL 中一样。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

WHERE `字段 = $值`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

WHERE `字段 <> $值`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

WHERE `字段 IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

WHERE `字段 IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

WHERE `字段 > $值`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

WHERE `字段 < $值`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

WHERE `字段 >= $值`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

WHERE `字段 <= $值`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

WHERE `字段 LIKE $值` 或 `字段 NOT LIKE $值`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

WHERE `字段 IN($值)` 或 `字段 NOT IN($值)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

WHERE `字段 BETWEEN $值 AND $值1`

```php
$user->between('id', [1, 2])->find();
```

### 或条件

你可以将条件包装在 OR 语句中。这可以通过 `startWrap()` 和 `endWrap()` 方法来完成，或者通过在字段和值之后填充第三个参数。

```php
// 方法 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 这将评估为 `id = 1 AND (name = 'demo' OR name = 'test')`

// 方法 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 这将评估为 `id = 1 OR name = 'demo'`
```

## 关系
使用这个库，你可以设置几种类型的关系。你可以在表之间设置一对多和一对一的关系。这需要在类中事先进行一些额外的设置。

设置 `$relations` 数组并不困难，但猜测正确的语法可能会令人困惑。

```php
protected array $relations = [
	// 你可以将键命名为任何你想要的名称。 ActiveRecord 的名称可能是好的。例如：user、contact、client
	'user' => [
		// 必需
		// self::HAS_MANY，self::HAS_ONE，self::BELONGS_TO
		self::HAS_ONE, // 这是关系的类型

		// 必需
		'Some_Class', // 这是将被引用的“其他”ActiveRecord 类

		// 必需
		// 根据关系类型
		// self::HAS_ONE = 引用连接的外键
		// self::HAS_MANY = 引用连接的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 仅供参考，这也仅连接到“其他”模型的主键

		// 可选
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 连接关系时你想要的额外条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 可选
		'back_reference_name' // 如果你想将此关系反向引用回自身，例如：$user->contact->user；
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

现在我们已经设置好引用，因此可以非常轻松地使用它们！

```php
$user = new User($pdo_connection);

// 查找最新的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 通过关系获取联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 或者我们可以反过来。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 通过关系获取用户：
echo $contact->user->name; // 这是用户名
```

很酷，对吧？

## 设置自定义数据
有时你可能需要将某些唯一的东西附加到你的 ActiveRecord 上，例如一个可能更容易附加到对象的自定义计算，然后再传递给模板。

#### `setCustomData(string $field, mixed $value)`
你可以通过 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后你可以像正常对象属性一样引用它。

```php
echo $user->page_view_count;
```

## 事件

关于这个库的另一个超级酷的功能是事件。事件在你调用的某些方法的特定时间触发。它们在自动为你设置数据时非常有用。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果你需要设置一个默认连接或类似的内容，这真的很有帮助。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 别忘了引用&
		// 你可以这样做来自动设置连接
		$config['connection'] = Flight::db();
		// 或者这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 你也可以这样设置表名。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

如果你每次需要查询操作，这个方法可能会很有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 如果那是你的风格，始终运行 id >= 0
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果你每次需要运行一些逻辑时，这个方法可能更有用。你需要解密一些东西吗？你需要每次运行自定义计数查询吗（不是性能优化的，但无所谓）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些东西
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 也许自定义存储某些内容，比如查询？？？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

如果你每次需要查询操作，这个方法可能会很有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 如果那是你的风格，始终运行 id >= 0
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

与 `afterFind()` 类似，但你可以对所有记录执行！

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

如果你每次都需要设置一些默认值，这会非常有用。

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

也许你有在插入后更改数据的用例？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 你可以自己决定
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 或者其他的....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

如果你每次在更新时需要设置一些默认值，这会非常有用。

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

也许你有在更新后更改数据的用例？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 你可以自己决定
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 或者其他的....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果你希望在插入或更新发生时都进行事件处理，这会很有用。我就不多说了，你肯定可以猜到它的作用。

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

不确定你想在这里做什么，但这里没有判断！尽情发挥吧！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '他是一位勇敢的士兵... :cry-face:';
	} 
}
```

## 数据库连接管理

当你使用这个库时，可以以几种不同的方式设置数据库连接。你可以在构造函数中设置连接，或者通过配置变量 `$config['connection']` 设置它，或者通过 `setDatabaseConnection()`（v0.4.1）设置它。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例如
$user = new User($pdo_connection);
// 或
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 或
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

如果你想避免每次调用活动记录时都要设置 `$database_connection`，也有方法可以解决这个问题！

```php
// index.php 或 bootstrap.php
// 将其作为 Flight 中的注册类设置
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// 现在，不需要参数！
$user = new User();
```

> **注意：** 如果你打算进行单元测试，这种方式可能会给单元测试带来一些挑战，但总体来说，由于你可以使用 `setDatabaseConnection()` 或 `$config['connection']` 注入连接，所以也不是太糟糕。

如果需要刷新数据库连接，例如，如果你运行一个长时间运行的 CLI 脚本并需要定期刷新连接，你可以使用 `$your_record->setDatabaseConnection($pdo_connection)` 重新设置连接。

## 贡献

请做。 :D

### 设置

当你贡献时，请确保运行 `composer test-coverage` 以保持 100% 的测试覆盖率（这不是严格的单元测试覆盖率，更像是集成测试）。

同时确保运行 `composer beautify` 和 `composer phpcs` 来修复任何代码检查错误。

## 许可证

MIT