# Flight 活动记录

活动记录是将数据库实体映射到 PHP 对象。简单地说，如果您在数据库中有一个名为 users 的表，您可以将该表中的一行“转换”为 `User` 类和代码库中的 `$user` 对象。查看[基本示例](#basic-example)。

单击[这里](https://github.com/flightphp/active-record)查看 GitHub 上的存储库。

## 基本示例

假设您有以下表：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

现在，您可以设置一个新类来表示这个表：

```php
/**
 * 活动记录类通常是单数的
 * 
 * 强烈建议在此添加表的属性注释
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
		// 或者这样设置
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

现在，让神奇发生吧！

```php
// 适用于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 仅作示例，您可能会使用真实的数据库连接

// 适用于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者基于非对象的 mysqli 创建
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
// 这里不能使用 $user->save() 否则它会认为这是一个更新！

echo $user->id; // 2
```

添加新用户就是这么简单！现在数据库中有一个用户行，如何获取它呢？

```php
$user->find(1); // 查找 id = 1 的用户并返回它。
echo $user->name; // 'Bobby Tables'
```

如果您想查找所有用户呢？

```php
$users = $user->findAll();
```

有特定条件的情况呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看起来多么有趣？让我们安装它并开始使用吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 用法

这可以作为独立库使用，也可以与 Flight PHP 框架一起使用。完全取决于您。

### 独立使用
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 仅作示例，您可能会使用真实的数据库连接

$User = new User($pdo_connection);
```

> 不想总是在构造函数中设置数据库连接？请参阅[数据库连接管理](#database-connection-management)以获取其他想法！

### 作为 Flight 方法注册
如果您使用 Flight PHP 框架，您可以将 ActiveRecord 类注册为服务，但您其实不必这样做。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后您可以在控制器、函数等中这样使用

Flight::user()->find(1);
```

## `runway` 方法

[runway](https://docs.flightphp.com/awesome-plugins/runway) 是 Flight 的 CLI 工具，为此库提供了自定义命令。

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
 * users 表的活动记录类。
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

查找记录并将其分配给当前对象。如果传递了某种 `$id`，它将使用该值在主键上执行查找。如果未传递任何内容，它将仅查找表中的第一条记录。

此外，您可以在查询表之前传递其他辅助方法给它。

```php
// 在查找记录之前查找一些条件
$user->notNull('password')->orderBy('id DESC')->find();

// 根据特定 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找指定表中的所有记录。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

如果当前记录已填充（从数据库中提取），则返回 `true`。

```php
$user->find(1);
// 如果找到一条记录并有数据...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

将当前记录插入数据库中。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### 基于文本的主键

如果有基于文本的主键（例如 UUID），您可以在插入之前以两种方式设置主键值。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 或 $user->save();
```

或者可以通过事件自动生成主键值。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 您还可以以此方式设置 primaryKey 而不是上面的数组。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 或者您可以根据需要生成唯一 id
	}
}
```

如果在插入之前没有设置主键，它将被设置为 `rowid`，并且数据库将为您生成它，但它不会持久保存，因为该字段可能不存在于您的表中。这就是为什么建议使用事件自动处理这一点的原因。

#### `update(): boolean|ActiveRecord`

更新数据库中的当前记录。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库中。如果记录具有 id，则会执行更新，否则将执行插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果在类中定义了关系，且如果已定义、实例化并具有需要更新的数据，则它还将递归保存那些关系。（v0.4.0 及更高版本）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

您还可以在执行搜索之前删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据是指在记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 这时候没有数据是“脏”的。

$user->email = 'test@example.com'; // 现在电子邮件被认为是“脏”的，因为它已更改。
$user->update();
// 现在没有数据是脏的，因为已更新并持久保存在数据库中。

$user->password = password_hash()'newpassword'); // 现在这是脏的
$user->dirty(); // 传递空将清除所有脏条目。
$user->update(); // 什么都不会更新，因为没有被标记为脏。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名字和密码都已更新。
```

#### `copyFrom(array $data): ActiveRecord`（v0.4.0）

这是 `dirty()` 方法的别名。这更清晰地表明您正在做什么。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名字和密码都已更新。
```

#### `isDirty(): boolean`（v0.4.0）

如果当前记录已更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为初始状态。在循环类型行为中非常有用。如果传递 `true`，它还会重置用于查找当前对象的查询数据（默认行为）。

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

#### `getBuiltSql(): string`（v0.4.1）

在运行 `find()`、`findAll()`、`insert()`、`update()` 或 `save()` 方法后，您可以获取构建的 SQL，并将其用于调试目的。

## SQL 查询方法

#### `select(string $field1 [, string $field2 ... ])`

如果需要，您可以仅选择表中几列（在具有许多列的宽表上性能更好）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

您还可以选择其他表！为何不选呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

您甚至可以加入到数据库中的另一张表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

您可以设置一些自定义的 where 参数（您不能在此 where 语句中设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全提示** - 您可能想要做类似 `$user->where("id = '{$id}' AND name = '{$name}'")->find();`。请不要这样做！这容易受到 SQL 注入攻击。有很多在线文章，请谷歌“sql 注入攻击 php”，您将会找到很多关于这个主题的文章。使用此库的正确方法是，而不是使用 `where()` 方法，您应该使用更像是 `$user->eq('id', $id)->eq('name', $name)->find();` 的方法。如果确实需要这样做，`PDO` 库有 `$pdo->quote($var)` 可以为您转义。仅在使用 `quote()` 后才可以在 `where()` 语句中使用它。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

按特定方式排序返回的查询。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果给定第二个整数，则将偏移，limit 就像在 SQL 中一样。

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件

#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`

```php
$user->isNull('id')->find();
```

#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`

```php
$user->lt('id', 1)->find();
```

#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`

```php
$user->ge('id', 1)->find();
```

#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` 或 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` 或 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## 关系

使用此库，您可以设置几种类型的关系。您可以在表之间设置一对多和一对一关系。这需要事先在类中进行一些额外的设置。

设置 `$relations` 数组并不困难，但猜测正确的语法可能会令人困惑。

```php
protected array $relations = [
	// 您可以使用任何您喜欢的键名。ActiveRecord 的名称可能很好。例如：user、contact、client
	'user' => [
		// 必需
		// self::HAS_MANY、self::HAS_ONE、self::BELONGS_TO
		self::HAS_ONE, // 这是关系的类型

		// 必需
		'Some_Class', // 这是此关系将引用的“其他” ActiveRecord 类

		// 必需
		// 根据关系类型
		// self::HAS_ONE = 引用连接的外键
		// self::HAS_MANY = 引用连接的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 顺便说一下，如果您想回顾或提出任何疑问，请告诉我。