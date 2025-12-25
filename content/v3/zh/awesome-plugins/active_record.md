# Flight Active Record 

Active Record 是将数据库实体映射到 PHP 对象的机制。简单来说，如果你的数据库中有一个 users 表，你可以将该表中的一行“翻译”成代码库中的 `User` 类和 `$user` 对象。请参阅 [基本示例](#basic-example)。

点击 [这里](https://github.com/flightphp/active-record) 查看 GitHub 仓库。

## 基本示例

假设你有以下表：

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
 * ActiveRecord 类通常使用单数形式
 * 
 * 强烈建议在此处将表属性作为注释添加
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

现在看看魔力如何发生！

```php
// 对于 SQLite
$database_connection = new PDO('sqlite:test.db'); // 这只是示例，你可能使用真实的数据库连接

// 对于 MySQL
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者非对象创建的 mysqli
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
// 这里不能使用 $user->save()，否则它会认为这是更新！

echo $user->id; // 2
```

添加新用户就是这么简单！现在数据库中已经有用户行，如何提取它？

```php
$user->find(1); // 在数据库中查找 id = 1 并返回它。
echo $user->name; // 'Bobby Tables'
```

如果你想查找所有用户呢？

```php
$users = $user->findAll();
```

带有特定条件的呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

这有多有趣？让我们安装它并开始吧！

## 安装

使用 Composer 简单安装

```php
composer require flightphp/active-record 
```

## 使用

这可以作为独立库使用，也可以与 Flight PHP Framework 一起使用。完全取决于你。

### 独立使用
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是示例，你可能使用真实的数据库连接

$User = new User($pdo_connection);
```

> 不想每次都在构造函数中设置数据库连接？请参阅 [数据库连接管理](#database-connection-management) 获取其他想法！

### 在 Flight 中注册为方法
如果你正在使用 Flight PHP Framework，你可以将 ActiveRecord 类注册为服务，但诚实地，你不必这么做。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后你可以在控制器、函数等中使用它。

Flight::user()->find(1);
```

## `runway` 方法

[runway](/awesome-plugins/runway) 是 Flight 的 CLI 工具，具有此库的自定义命令。 

```bash
# 使用方法
php runway make:record database_table_name [class_name]

# 示例
php runway make:record users
```

这将在 `app/records/` 目录中创建一个新类 `UserRecord.php`，内容如下：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users 表的 ActiveRecord 类。
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

查找一条记录并将其分配到当前对象。如果你传递某种 `$id`，它将使用该值在主键上执行查找。如果没有传递任何内容，它将仅查找表中的第一条记录。

此外，你可以传递其他辅助方法来查询你的表。

```php
// 在查找前使用某些条件查找记录
$user->notNull('password')->orderBy('id DESC')->find();

// 通过特定 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找你指定的表中的所有记录。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

如果当前记录已从数据库中填充（获取），则返回 `true`。

```php
$user->find(1);
// 如果找到具有数据的记录...
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

如果你有基于文本的主键（例如 UUID），你可以在插入前以两种方式之一设置主键值。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 或者 $user->save();
```

或者你可以通过事件让主键自动生成。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 你也可以用这种方式设置 primaryKey 而不是上面的数组。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 或者你生成唯一 id 的方式
	}
}
```

如果你在插入前没有设置主键，它将被设置为 `rowid`，数据库将为你生成它，但它不会持久化，因为该字段可能不存在于你的表中。这就是为什么推荐使用事件来自动处理这个。

#### `update(): boolean|ActiveRecord`

将当前记录更新到数据库。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库。如果记录有 id，它将更新，否则将插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果你在类中定义了关系，它将递归保存那些已定义、实例化和有脏数据更新的关系。（v0.4.0 及以上）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

你也可以在执行搜索前删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据指的是记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 到目前为止没有任何“脏”数据。

$user->email = 'test@example.com'; // 现在 email 被认为是“脏”的，因为它已更改。
$user->update();
// 现在没有脏数据了，因为它已被更新并持久化到数据库中

$user->password = password_hash()'newpassword'); // 现在这是脏的
$user->dirty(); // 不传递任何内容将清除所有脏条目。
$user->update(); // 不会更新任何内容，因为没有捕获到脏数据。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都会更新。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

这是 `dirty()` 方法的别名。它更清楚你在做什么。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都会更新。
```

#### `isDirty(): boolean` (v0.4.0)

如果当前记录已更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。这在循环类型行为中非常有用。如果你传递 `true`，它还将重置用于查找当前对象的查询数据（默认行为）。

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

在运行 `find()`、`findAll()`、`insert()`、`update()` 或 `save()` 方法后，你可以获取构建的 SQL 并用于调试目的。

## SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果你愿意，你可以仅选择表中的几列（对于具有许多列的非常宽的表，这更高效）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

你也可以选择另一个表！为什么不呢？！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

你甚至可以连接到数据库中的另一个表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

你可以设置一些自定义的 where 参数（在此 where 语句中不能设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全注意** - 你可能会被诱惑做类似 `$user->where("id = '{$id}' AND name = '{$name}'")->find();` 的事情。请千万不要这样做！！！这容易受到已知为 SQL 注入攻击的威胁。网上有很多文章，请 Google “sql injection attacks php”，你会找到很多关于这个主题的文章。使用此库正确处理此问题的正确方式是代替此 `where()` 方法，你应该做类似 `$user->eq('id', $id)->eq('name', $name)->find();` 的操作。如果你绝对必须这样做，`PDO` 库有 `$pdo->quote($var)` 来为你转义它。只有在使用 `quote()` 后，你才能在 `where()` 语句中使用它。

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

限制返回的记录数量。如果给出第二个整数，它将是偏移量、限制，就像在 SQL 中一样。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件
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

Where `field LIKE $value` 或 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` 或 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR 条件

你可以将条件包装在 OR 语句中。这可以通过 `startWrap()` 和 `endWrap()` 方法，或者通过在字段和值后填充条件的第三个参数来完成。

```php
// 方法 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// 这将评估为 `id = 1 AND (name = 'demo' OR name = 'test')`

// 方法 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// 这将评估为 `id = 1 OR name = 'demo'`
```

## 关系
使用此库，你可以设置几种关系。你可以设置表之间的一对多和一对一关系。这需要在类中进行一些额外的设置。

设置 `$relations` 数组并不难，但猜测正确的语法可能会令人困惑。

```php
protected array $relations = [
	// 你可以为键命名任何你喜欢的东西。ActiveRecord 的名称可能不错。例如：user, contact, client
	'user' => [
		// 必需
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 这是关系类型

		// 必需
		'Some_Class', // 这是将引用的“其他” ActiveRecord 类

		// 必需
		// 取决于关系类型
		// self::HAS_ONE = 引用连接的外键
		// self::HAS_MANY = 引用连接的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 顺便说一句，这也仅连接到“其他”模型的主键

		// 可选
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 连接关系时想要的额外条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 可选
		'back_reference_name' // 如果你想反向引用此关系回自身，例如 $user->contact->user;
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

现在我们已经设置了引用，可以非常轻松地使用它们！

```php
$user = new User($pdo_connection);

// 查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 使用关系获取联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 或者我们可以反向进行。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 使用关系获取用户：
echo $contact->user->name; // 这是用户名
```

很酷吧？

### 预加载

#### 概述
预加载通过提前加载关系来解决 N+1 查询问题。预加载不是为每个记录的关系执行单独的查询，而是为每个关系仅执行一个额外的查询来获取所有相关数据。

> **注意：** 预加载仅适用于 v0.7.0 及以上版本。

#### 基本用法
使用 `with()` 方法指定要预加载的关系：
```php
// 使用 2 个查询加载用户及其联系人，而不是 N+1
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
    // $u->contacts 已加载为数组
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
    // $u->contact 已加载为对象
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// 为所有联系人预加载父用户
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user 已加载
    echo $c->user->name;
}
```
##### 与 find()
预加载适用于 
findAll()
 和 
find()
：

```php
$user = $user->with('contacts')->find(1);
// 用户及其所有联系人使用 2 个查询加载
```
#### 性能优势
没有预加载（N+1 问题）：
```php
$users = $user->findAll(); // 1 个查询
foreach ($users as $u) {
    $contacts = $u->contacts; // N 个查询（每个用户一个！）
}
// 总计：1 + N 个查询
```

使用预加载：

```php
$users = $user->with('contacts')->findAll(); // 总计 2 个查询
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 个额外查询！
}
// 总计：2 个查询（1 个用于用户 + 1 个用于所有联系人）
```
对于 10 个用户，这将查询从 11 个减少到 2 个 - 减少 82%！

#### 重要注意事项
- 预加载完全是可选的 - 延迟加载仍按之前工作
- 已加载的关系会自动跳过
- 反向引用与预加载兼容
- 预加载期间尊重关系回调

#### 限制
- 嵌套预加载（例如， 
with(['contacts.addresses'])
）当前不支持
- 通过闭包的预加载约束在此版本中不支持

## 设置自定义数据
有时你可能需要将独特的东西附加到你的 ActiveRecord，例如自定义计算，这可能更容易附加到将传递给模板的对象。

#### `setCustomData(string $field, mixed $value)`
你使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后你只需像正常对象属性一样引用它。

```php
echo $user->page_view_count;
```

## 事件

此库的另一个超级棒的功能是关于事件的。事件在你调用某些方法时基于某些时间触发。它们在自动设置数据方面非常非常有用。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果需要设置默认连接或其他类似的东西，这非常有用。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 不要忘记 & 引用
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

如果每次都需要查询操作，这可能只有用处。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 如果这是你的风格，总是运行 id >= 0
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果每次获取此记录时都需要运行某些逻辑，这个可能更有用。你需要解密某些东西吗？每次都需要运行自定义计数查询吗（不高效，但无所谓）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些东西
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 也许存储自定义的东西，比如查询？？？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

如果每次都需要查询操作，这可能只有用处。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 如果这是你的风格，总是运行 id >= 0
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

类似于 `afterFind()`，但你可以对所有记录执行它！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// 做一些酷的事情，就像 afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

如果每次都需要设置一些默认值，这非常有用。

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
		// 你随意
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 或者其他....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

如果每次更新时都需要设置一些默认值，这非常有用。

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
		// 你随意
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 或者其他....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果希望在插入或更新时都发生事件，这很有用。我就不长篇解释了，但我想你能猜到是什么。

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

不确定你想在这里做什么，但这里没有判断！去吧！

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

使用此库时，你可以以几种不同的方式设置数据库连接。你可以在构造函数中设置它，通过配置变量 `$config['connection']` 设置它，或者通过 `setDatabaseConnection()` 设置它 (v0.4.1)。 

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例如
$user = new User($pdo_connection);
// 或者
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 或者
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

如果你想避免每次调用 active record 时都设置 `$database_connection`，有办法绕过它！

```php
// index.php 或 bootstrap.php
// 在 Flight 中将其设置为注册类
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

> **注意：** 如果你计划进行单元测试，这样做可能会给单元测试带来一些挑战，但总体上，因为你可以使用 `setDatabaseConnection()` 或 `$config['connection']` 注入连接，所以不是太糟糕。

如果你需要刷新数据库连接，例如如果你正在运行一个长时间运行的 CLI 脚本并需要每隔一段时间刷新连接，你可以使用 `$your_record->setDatabaseConnection($pdo_connection)` 重新设置连接。

## 贡献

请贡献。:D

### 设置

贡献时，请确保运行 `composer test-coverage` 以保持 100% 测试覆盖率（这不是真正的单元测试覆盖率，更像是集成测试）。

还要确保运行 `composer beautify` 和 `composer phpcs` 以修复任何 linting 错误。

## 许可证

MIT