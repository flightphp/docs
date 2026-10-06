# Flight 活动记录

活动记录是将数据库实体映射到 PHP 对象。通俗地说，如果你的数据库中有一个 users 表，你可以将该表中的一行“翻译”为 `User` 类和代码库中的 `$user` 对象。参见[基础示例](#basic-example)。

点击[此处](https://github.com/flightphp/active-record)前往 GitHub 仓库。

## 基础示例

假设你有以下数据表：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

现在你可以创建一个新类来表示这个表：

```php
/**
 * ActiveRecord 类通常是单数形式
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

现在来看看奇迹发生吧！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这只是示例，你可能需要使用真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者使用非基于对象的 mysqli 创建方式
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// 或者 $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// 这里不能使用 $user->save()，否则它会认为这是一次更新！

echo $user->id; // 2
```

添加新用户就是这么简单！现在数据库中已经有了一条用户记录，那么如何把它取出来呢？

```php
$user->find(1); // 在数据库中查找 id = 1 并返回该记录。
echo $user->name; // 'Bobby Tables'
```

如果你想查找所有用户呢？

```php
$users = $user->findAll();
```

如果有特定条件呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看到这有多好玩了吗？让我们安装它并开始吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 用法

它可以作为独立库使用，也可以与 Flight PHP 框架一起使用。完全取决于你。

### 独立使用
只需确保将 PDO 连接传递给构造函数即可。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是示例，你可能需要使用真实的数据库连接

$User = new User($pdo_connection);
```

> 不想总是在构造函数中设置数据库连接？请查看[数据库连接管理](#database-connection-management)获取其他思路！

### 注册为 Flight 中的方法
如果你在使用 Flight PHP 框架，你可以将 ActiveRecord 类注册为服务，但老实说你并不是必须这样做。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后你可以在控制器、函数等中使用它，像这样：

Flight::user()->find(1);
```

## `runway` 方法

[runway](/awesome-plugins/runway) 是 Flight 的 CLI 工具，为该库提供了一个自定义命令。

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
 * users 表的 ActiveRecord 类。
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
     * @var array $relations 为模型设置关系
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
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

查找一条记录并将其赋值给当前对象。如果你传入了某种 `$id`，它将根据该值对主键进行查找。如果没有传入任何内容，它只会查找表中的第一条记录。

此外，你还可以传入其他辅助方法来查询你的表。

```php
// 先通过一些条件查找记录
$user->notNull('password')->orderBy('id DESC')->find();

// 根据特定 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找你指定表中的所有记录。

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

查找符合你条件的第一条记录。如果你没有设置排序，它将按主键升序排序。如果没有匹配项，你会得到一个未填充（unhydrated）的记录，所以如果你不确定是否有数据返回，请检查 `isHydrated()`。

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

与 `first()` 相同，但按主键降序排序。非常适合“给我最新一条”的查询。

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

计算符合当前条件的行数。如果查询中有 `groupBy()`，`count()` 会有意忽略它。单个标量计数无法表示每组一行。

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

如果存在任何符合你条件的记录，则返回 `true`。它在底层执行一个廉价的 `SELECT 1 ... LIMIT 1` 查询。

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

返回某一列的值组成的扁平数组，而不是填充一堆对象。可以结合 `distinct()` 获取唯一值。

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

这是对主键执行 `pluck()` 的快捷方式。

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

如果当前记录已被填充（从数据库获取），则返回 `true`。

```php
$user->find(1);
// 如果找到一条包含数据的记录...
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

如果你有一个基于文本的主键（例如 UUID），你可以在插入前通过以下两种方式之一设置主键值。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 或者 $user->save();
```

或者，你可以通过事件让主键自动为你生成。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 你也可以用这种方式设置 primaryKey，而不使用上面的数组。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 或者无论你如何生成唯一 id 都可以
	}
}
```

如果你在插入前没有设置主键，它将被设置为 `rowid`，数据库会为你生成它，但它不会持久化，因为该字段可能不在你的表中。这就是为什么建议使用事件来自动处理这个问题。

#### `update(): boolean|ActiveRecord`

将当前记录更新到数据库中。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

在已加载的记录上更新单个列并保存它。它是 `$user->dirty([ 'name' => $value ])->update()` 的快捷方式。你需要一个已加载的记录才能使用此方法。

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库中。如果记录有 id，则会更新，否则会插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果你在类中定义了关系，那么只要这些关系已被定义、实例化并且有需要更新的脏数据，它也会递归保存这些关系。（v0.4.0 及以上）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

你也可以先执行搜索，然后删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

在单条语句中更新所有符合你条件的记录。不会有记录被填充，也不会触发事件，这正是它速度快的原因。返回受影响的行数。

如果没有 WHERE 条件，它拒绝运行，除非你为第二个参数传递 `true`。未来的你会感谢自己的。

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// 是的，你真的想要更新表中的每一行
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

在单条语句中删除所有符合你条件的记录。与 `updateAll()` 相同：不填充数据、不触发事件，并且需要 WHERE 条件，除非你传递 `true`。返回删除的行数。请谨慎使用！

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据指的是记录中已被更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 到目前为止，没有任何数据是“脏”的。

$user->email = 'test@example.com'; // 现在 email 被视为“脏”的，因为它已更改。
$user->update();
// 现在没有脏数据，因为已经更新并持久化到数据库中了

$user->password = password_hash()'newpassword'); // 现在这是脏的
$user->dirty(); // 不传任何内容将清除所有脏条目。
$user->update(); // 不会更新任何内容，因为没有捕获到脏数据。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都会被更新。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

这是 `dirty()` 方法的别名。它让你更清楚自己在做什么。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都会被更新。
```

#### `isDirty(): boolean` (v0.4.0)

如果当前记录已被更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。这在循环类型的行为中非常有用。如果你传入 `true`，它还会重置用于查找当前对象的查询数据（默认行为）。

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

在运行 `find()`、`findAll()`、`insert()`、`update()` 或 `save()` 方法后，你可以获取构建出的 SQL，并用于调试目的。

## 事务

需要运行几个必须同时成功的写入操作？将它们包装在 `transaction()` 中（v0.8.0）。传入一个可调用对象，记录会作为参数传入。如果可调用对象抛出异常，所有操作都会回滚，并为你重新抛出该异常。否则，它会提交并返回你的可调用对象返回的任何内容。

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// 如果没有抛出异常，将在此处提交
});
```

不支持嵌套事务（没有保存点），所以请保持事务扁平化。

## SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果你愿意，可以只选择表中的几列（在包含许多列的宽表上性能更好）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

从技术上讲，你也可以选择另一张表！为什么不呢？！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

你甚至可以在数据库中连接另一张表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

你可以设置一些自定义的 where 参数（你不能在这个 where 语句中设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全提示** - 你可能会想这样做：`$user->where("id = '{$id}' AND name = '{$name}'")->find();`。请千万不要这样做！！！这容易受到所谓的 SQL 注入攻击。网上有很多相关文章，请谷歌搜索“sql injection attacks php”，你会找到大量关于这个主题的文章。使用这个库的正确方式不是使用 `where()` 方法，而是更像 `$user->eq('id', $id)->eq('name', $name)->find();` 这样。如果你绝对必须这样做，`PDO` 库提供了 `$pdo->quote($var)` 来为你转义。只有在你使用了 `quote()` 之后，才能在 `where()` 语句中使用它。

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

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` 和 `orderBy()` 接受原始 SQL 片段，当你在硬编码 `'name DESC'` 时这没问题。如果列名来自用户输入（例如可排序的表头），请改用 `orderByColumn()`。只允许纯列名和 `table.column` 路径，并且方向必须是 `ASC` 或 `DESC`，因此没有可注入的内容。

```php
// $sortColumn 来自请求
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果提供了第二个整数，它将是 offset，然后在 SQL 中类似 limit。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

为你的下一个查询添加 `DISTINCT`。它适用于普通的 select 以及 `pluck()`。`count()` 会忽略它，因为在单个聚合行上放置 `DISTINCT` 没有任何作用。

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

## WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

条件：`field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

条件：`field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

条件：`field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

条件：`field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

条件：`field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

条件：`field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

条件：`field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

条件：`field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

条件：`field LIKE $value` 或 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

条件：`field IN($value)` 或 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

条件：`field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR 条件

可以将你的条件包裹在 OR 语句中。这可以通过 `startWrap()` 和 `endWrap()` 方法完成，或者在字段和值之后填写条件的第三个参数来完成。

```php
// 方法 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 这将解析为 `id = 1 AND (name = 'demo' OR name = 'test')`

// 方法 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 这将解析为 `id = 1 OR name = 'demo'`
```

## 作用域

作用域（v0.8.0）是可复用的查询链，定义为类上返回 `$this` 的普通实例方法。一旦你写好了作用域，它就可以像任何其他查询方法一样进行链式调用。

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

// 现在你的查询读起来像句子一样
(new User($pdo_connection))->active()->recent(30)->findAll();
```

你也可以通过 `scope()` 按名称调用作用域，当作用域名称来自代码中的其他地方时，这很方便。如果方法不存在，它会抛出 `BadMethodCallException`。

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## 关系

你可以使用这个库设置多种类型的关系。你可以在表之间设置一对多和一对一的关系。这需要事先在类中进行一些额外的设置。

设置 `$relations` 数组并不难，但猜测正确的语法可能会让人困惑。

```php
protected array $relations = [
	// 你可以为键取任何你喜欢的名字。ActiveRecord 的名称可能是个好选择。例如：user, contact, client
	'user' => [
		// 必填
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 这是关系类型

		// 必填
		'Some_Class', // 这是它将引用的“另一个” ActiveRecord 类

		// 必填
		// 取决于关系类型
		// self::HAS_ONE = 引用连接的外键
		// self::HAS_MANY = 引用连接的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 仅供参考，这也只会连接到“另一个”模型的主键

		// 可选
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 连接关系时你想要的附加条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 可选
		'back_reference_name' // 如果你想将这种关系反向引用到自身，例如：$user->contact->user;
	]
];
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

现在我们已经设置好了引用，可以非常轻松地使用它们！

```php
$user = new User($pdo_connection);

// 查找最近的一个用户。
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
echo $contact->user->name; // 这是用户的名字
```

挺酷的吧？

### 预加载

#### 概述

预加载通过提前加载关系来解决 N+1 查询问题。它不是为每条记录的关系执行单独的查询，而是每个关系仅额外执行一次查询来获取所有相关数据。

> **注意：** 预加载仅适用于 v0.7.0 及以上版本。

#### 基本用法

使用 `with()` 方法指定要预加载哪些关系：
```php
// 在 2 次查询中加载用户及其联系人，而不是 N+1 次
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // 没有额外的查询！
    }
}
```

#### 多个关系

一次加载多个关系：
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### 关系类型

##### HAS_MANY
```php
// 为每个用户预加载所有联系人
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts 已经作为一个数组加载好了
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// 为每个用户预加载一个联系人
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact 已经作为一个对象加载好了
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// 为所有联系人预加载其所属用户
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user 已经加载好了
    echo $c->user->name;
}
```
##### 与 find() 一起使用
预加载可以同时用于 `findAll()` 和 `find()`：

```php
$user = $user->with('contacts')->find(1);
// 用户及其所有联系人在 2 次查询中加载完成
```
#### 性能优势
不使用预加载（N+1 问题）：
```php
$users = $user->findAll(); // 1 次查询
foreach ($users as $u) {
    $contacts = $u->contacts; // N 次查询（每个用户一次！）
}
// 总计：1 + N 次查询
```

使用预加载：

```php
$users = $user->with('contacts')->findAll(); // 总共 2 次查询
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 次额外查询！
}
// 总计：2 次查询（1 次用于用户 + 1 次用于所有联系人）
```
对于 10 个用户，这将查询次数从 11 次减少到 2 次——减少了 82%！

#### 重要注意事项
- 预加载完全是可选的——延迟加载仍然像以前一样工作
- 已加载的关系会被自动跳过
- 反向引用可以与预加载一起使用
- 在预加载期间会尊重关系回调

#### 限制
- 嵌套预加载（例如 `with(['contacts.addresses'])`）目前不受支持
- 此版本不支持通过闭包设置预加载约束

## 设置自定义数据

有时你可能需要为你的 ActiveRecord 附加一些独特的内容，例如自定义计算，这可能更容易直接附加到对象上，然后该对象会被传递给模板之类的。

#### `setCustomData(string $field, mixed $value)`
你可以使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后你可以像普通对象属性一样引用它。

```php
echo $user->page_view_count;
```

## 时间戳

如果你的表有 `created_at` 和 `updated_at` 列，你可以让库为你填充它们（v0.8.0）。在你的类上设置 `protected bool $timestamps = true;`，它会在你插入时设置 `created_at` 和 `updated_at`，并在你更新时设置 `updated_at`。格式为 `Y-m-d H:i:s`。如果你自己设置了任一列，库会保留你的值不变。

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

你的表确实需要这些列，否则插入和更新将会失败。

## 事件

这个库另一个超棒的功能是事件。事件会根据你调用的某些方法在特定时间触发。它们对于自动为你设置数据非常有帮助。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果你需要设置默认连接或类似的东西，这非常有帮助。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 别忘了 & 引用
		// 你可以这样做来自动设置连接
		$config['connection'] = Flight::db();
		// 或者这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 你也可以用这种方式设置表名。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

这可能只在每次需要操作查询时有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 如果你喜欢的话，始终执行 id >= 0
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果你每次获取此记录时都需要运行一些逻辑，这个可能更有用。你需要解密某些内容吗？你需要每次运行自定义计数查询吗（性能不好但无所谓）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些内容
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 也许存储一些自定义内容，比如查询？？？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

这可能只在每次需要操作查询时有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 如果你喜欢的话，始终执行 id >= 0
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

与 `afterFind()` 类似，但你可以对所有记录执行操作！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// 做一些像 afterFind() 一样酷的事情
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

如果你每次需要设置一些默认值，这非常有帮助。

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
		// 你按你的方式做
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 或者随便怎样....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

如果你每次更新时都需要设置一些默认值，这非常有帮助。

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
		// 你按你的方式做
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 或者随便怎样....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果你希望在插入或更新时都触发事件，这非常有用。我就不做冗长的解释了，但我相信你能猜到它是什么。

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

不确定你想在这里做什么，但这里不会有任何评判！放手去做吧！

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

## 数据库连接管理

使用这个库时，你可以通过几种不同的方式设置数据库连接。你可以在构造函数中设置连接，也可以通过配置变量 `$config['connection']` 设置，或者通过 `setDatabaseConnection()`（v0.4.1）设置。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例如
$user = new User($pdo_connection);
// 或者
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 或者
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

如果你想避免每次调用活动记录时都设置 `$database_connection`，有办法可以绕过这个问题！

```php
// index.php 或 bootstrap.php
// 将此设置为 Flight 中注册的类
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// 现在，不需要任何参数！
$user = new User();
```

> **注意：** 如果你打算进行单元测试，这样做可能会给单元测试带来一些挑战，但总体来说，因为你可以通过 `setDatabaseConnection()` 或 `$config['connection']` 注入连接，所以还不算太糟。

如果你需要刷新数据库连接，例如你在运行一个长时间运行的 CLI 脚本并需要时不时刷新连接，你可以使用 `$your_record->setDatabaseConnection($pdo_connection)` 重新设置连接。

## 贡献

请贡献吧。:D

### 设置

当你贡献时，请确保运行 `composer test-coverage` 以保持 100% 的测试覆盖率（这并非真正的单元测试覆盖率，更像是集成测试）。

还要确保运行 `composer beautify` 和 `composer phpcs` 来修复任何 lint 错误。

## 许可证

MIT