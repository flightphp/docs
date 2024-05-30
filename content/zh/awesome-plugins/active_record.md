# 飞行活动记录

活动记录是将数据库实体映射到 PHP 对象。简单来说，如果你在数据库中有一个用户表，你可以将该表中的一行“转换”为一个`User`类和一个`$user`对象在你的代码库中。请参见[基本示例](#basic-example)。

## 基本示例

假设你有以下表格：

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
 * 通常，活动记录类是单数形式
 * 
 * 强烈建议在这里作为注释添加表的属性
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

现在看魔法发生吧！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 仅为示例，你可能会使用一个真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者 mysqli 与非对象创建
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
// 这里不能使用 $user->save()，否则它将认为这是一个更新！

echo $user->id; // 2
```

添加一个新用户就这么简单！现在数据库中有一个用户行了，那么如何取出它呢？

```php
$user->find(1); // 查找 id = 1 在数据库中并返回
echo $user->name; // 'Bobby Tables'
```

如果你想找到所有用户呢？

```php
$users = $user->findAll();
```

如果有某个特定条件呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看起来很有趣吧？让我们安装并开始吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 用法

这可以作为一个独立的库或与 Flight PHP 框架一起使用。完全取决于你。

### 独立使用
只需确保将一个 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 仅为示例，你可能会使用一个真实的数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如果你在使用 Flight PHP 框架，你可以将 ActiveRecord 类注册为服务（但你实际上并不需要）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后你可以在控制器、函数等中像这样使用。

Flight::user()->find(1);
```

## CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果传递某种 `$id`，它将使用该值进行主键查找。如果没有传递任何内容，它将只查找表中的第一条记录。

此外，你可以传递其他辅助方法来查询表。

```php
// 在事先设置一些条件后查找记录
$user->notNull('password')->orderBy('id DESC')->find();

// 通过具体 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找你指定的表中的所有记录。

```php
$user->findAll();
```

#### `isHydrated(): boolean` （v0.4.0）

如果当前记录已被填充（从数据库中获取），则返回true。

```php
$user->find(1);
// 如果找到具有数据的记录...
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

如果你有基于文本的主键（例如 UUID），你可以在插入之前设置主键的值，有两种方法可选。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // 或 $user->save();
```

或者你可以通过事件自动生成主键的值。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 你也可以像这样设置主键而不是上面的数组。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // 或者以其他方式生成你的唯一标识符
	}
}
```

如果在插入之前不设置主键，它将被设置为 `rowid`，数据库将为你生成它，但它不会持久化，因为该字段可能不存在于你的表中。这就是为什么建议使用事件自动处理这一情况的原因。

#### `update(): boolean|ActiveRecord`

更新当前记录到数据库中。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库中。如果记录具有 id，则会更新，否则将插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果在类中定义了关系，它会递归保存这些关系，如果它们已被定义、实例化并有待更新的脏数据。（v0.4.0 及以上）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

你还可以在事先执行搜索后删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据指已更改记录中的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时没有任何“脏”数据。

$user->email = 'test@example.com'; // 现在 email 被认为是“脏”的，因为它已更改。
$user->update();
// 现在没有任何数据是“脏”的，因为它已被更新并持久化在数据库中

$user->password = password_hash()'newpassword'); // 现在这是“脏”的
$user->dirty(); // 传递空值将清除所有脏的条目。
$user->update(); // 什么也不会更新，因为没有被识别为脏的内容。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都会被更新。
```

#### `copyFrom(array $data): ActiveRecord`（v0.4.0）

这是`dirty()`方法的别名。这样更清晰一些。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都会被更新。
```

#### `isDirty(): boolean`（v0.4.0）

如果当前记录已更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。在循环类型行为中使用这个方法非常好。如果传递 `true`，它还将重置用于查找当前对象的查询数据（默认行为）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 以干净的状态开始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string`（v0.4.1）

当你运行`find()`、`findAll()`、`insert()`、`update()`或`save()`方法后，你可以获取生成的 SQL，并用于调试目的。

## SQL 查询方法

#### `select(string $field1 [, string $field2 ... ])`

如果需要的话，你可以只选择表中的几列（在表列过宽，包含许多列时性能更佳）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

你甚至可以选择另一张表！为什么不呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

你甚至可以连接到数据库中的另一个表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

你可以设置一些自定义的条件（你不能在这个 where 语句中设置参数）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全提示** - 也许你会想做这样的事`$user->where("id = '{$id}' AND name = '{$name}'")->find();`。请不要这样做！这容易受到 SQL 注入攻击的影响。网上有许多文章，请搜索“sql 注入攻击 php”，你会找到很多相关文章。在这个库中处理这种情况的正确方式是，不要使用 `where()` 方法，而是使用类似 `$user->eq('id', $id)->eq('name', $name)->find();` 的方式。如果确实必须这样做，`PDO` 库提供了 `$pdo->quote($var)` 来为你转义参数。只有在使用 `quote()` 后才能在 `where()` 语句中使用。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

以某种方式排序返回的查询结果。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数。如果给出第二个整数，它将是偏移量，就像 SQL 中的 limit 一样。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

当 `field = $value` 时

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

当 `field <> $value` 时

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

当 `field IS NULL` 时

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

当 `field IS NOT NULL` 时

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

当 `field > $value` 时

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

当 `field < $value` 时

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

当 `field >= $value` 时

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

当 `field <= $value` 时

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

当 `field LIKE $value` 或 `field NOT LIKE $value` 时

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

当 `field IN($value)` 或 `field NOT IN($value)` 时

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

当 `field BETWEEN $value AND $value1` 时

```php
$user->between('id', [1, 2])->find();
```

## 关系
你可以使用这个库设置几种关系。你可以在表之间设置一对多和一对一的关系。这需要在类中预先进行一些额外的设置。

设置`$relations`数组并不难，但猜测正确的语法可能会让人困惑。

```php
protected array $relations = [
	// 你可以给 key 取任何名字。ActiveRecord 的名字可能很合适。比如：user, contact, client
	'user' => [
		// 必须
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // 这是关系的类型

		// 必须
		'Some_Class', // 这是关联的“其他”ActiveRecord 类

		// 必须
		// 根据关系类型
		// self::HAS_ONE = 引用连接的外键
		// self::HAS_MANY = 引用连接的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 只是 FYI，这也连接到“其他”模型的主键

		// 可选
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 在连接关系时要进行的其他条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 可选
		'back_reference_name' // 如果你想要将关系返回到自身时使用。例如：$user->contact->user;
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

现在我们已完成整个文档的翻译，请查阅。如果有任何疑问或需要进一步翻译，请随时告诉我。