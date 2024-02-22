# FlightPHP 活动记录

活动记录是将数据库实体映射到 PHP 对象。简单来说，如果你的数据库中有一个用户表，你可以将表中的一行"翻译"成一个 `User` 类和一个 `$user` 对象在你的代码库中。请参见[基本示例](#basic-example)。

## 基本示例

假设你有以下表：

```sql
CREATE TABLE 用户 (
	id INTEGER 主键, 
	名字 文本, 
	密码 文本 
);
```

现在你可以设置一个新的类来表示这个表：

```php
/**
 * 活动记录类通常是单数
 * 
 * 强烈建议在这里添加表的属性注释
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 你可以这样设置它
		parent::__construct($database_connection, 'users');
		// 或者这样
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

现在看魔术发生！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这只是一个例子，你可能会使用一个真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者基于非对象的创建的 mysqli
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
// 不可以在这里使用 $user->save() 否则它会认为是更新！

echo $user->id; // 2
```

就是这么简单添加一个新用户！现在数据库中有一个用户行，那么如何将其取出呢？

```php
$user->find(1); // 查找 id = 1 的记录并返回它。
echo $user->name; // 'Bobby Tables'
```

如果你想找到所有的用户呢？

```php
$users = $user->findAll();
```

如果想要根据某个条件查找呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看看有多有趣？让我们安装它然后开始吧！

## 安装

只需使用 Composer 安装即可

```php
composer require flightphp/active-record 
```

## 用法

这可以作为一个独立的库，也可以与 Flight PHP 框架一起使用。完全取决于你。

### 独立库
只需确保将一个 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是一个例子，你可能会使用一个真实的数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如果你使用 Flight PHP 框架，你可以将 ActiveRecord 类注册为一个服务（但你其实不需要这么做）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后你可以在控制器、函数等中这样使用

Flight::user()->find(1);
```

## API 参考
### CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果传递了某种 `$id`，它将在主键上执行具有该值的查找。如果什么都没有传递，它就会查找表中的第一条记录。

此外，你可以向它传递其他帮助方法来查询你的表。

```php
// 在事先设置条件的基础上查找记录
$user->notNull('password')->orderBy('id DESC')->find();

// 通过指定的 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找你指定的表中的所有记录。

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

将当前记录插入到数据库中。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

更新当前记录到数据库中。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

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

脏数据指的是记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时没有任何"脏数据"。

$user->email = 'test@example.com'; // 现在邮箱被认为是"脏数据"，因为它已更改。
$user->update();
// 现在没有脏数据，因为已更新并保存在数据库中

$user->password = password_hash()'newpassword'); // 现在这是脏数据
$user->dirty(); // 不传递任何参数将清除所有脏条目。
$user->update(); // 不能更新，因为没有脏数据被捕获。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名字和密码都已更新。
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。这对于循环类型行为非常有用。如果传递 `true`，它还将重置用于查找当前对象的查询数据（默认行为）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 从头开始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果需要，你可以只选择表中的一些列（在拥有许多列的宽表上性能更高）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

你也可以选择其他表！为什么不呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

你甚至可以加入到数据库的另一个表中。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

你可以设置一些自定义的条件（你不能在这个 where 语句中设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全注意** - 你可能会尝试像这样 `$user->where("id = '{$id}' AND name = '{$name}'")->find();`。请不要这样做！这容易受到所谓的 SQL 注入攻击。有很多关于此主题的在线文章，请搜索一下 "sql injection attacks php"，你会找到很多关于这方面的文章。在这个库中处理这样的方法，而不是采用 `where()` 方法，你应该使用 `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按照特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

按照某种方式对返回的查询结果进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果给出第二个整数，它将是偏移量，限制就像在 SQL 中一样。

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
```

### WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

其中 `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

其中 `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

其中 `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

其中 `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

其中 `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

其中 `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

其中 `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

其中 `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

其中 `field LIKE $value` 或 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

其中 `field IN($value)` 或 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

其中 `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### 关系
你可以使用这个库设定几种不同类型的关系。你可以在表之间设定一对多和一对一的关系。这需要在类中额外进行一些设置。

设置 `$relations` 数组并不难，但猜对正确的语法可能会令人困惑。

```php
protected array $relations = [
	// 你可以用任何你喜欢的名称来命名键。ActiveRecord 的名称可能不错。例如: user, contact, client
	'whatever_active_record' => [
		// 必需的
		self::HAS_ONE, // 这是关系的类型

		// 必需的
		'Some_Class', // 这是此关系引用的"其他" ActiveRecord 类

		// 必需的
		'local_key', // 这是引用连接的本地密钥。
		// 顺便说一下，这也仅加入到 "other" 模型的主键

		// 可选的
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 你想执行的自定义方法。如果你不想要任何方法，则使用 []
		
		// 可选的
		'back_reference_name' // 如果你想要将关系返回到自身，这是必要的。例如: $user->contact->user;
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

现在我们已经设置好引用，我们可以非常容易地使用它们！

```php
$user = new User($pdo_connection);

// 查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 通过关系查找联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 或者我们可以从另一个方向进来。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 通过关系查找用户：
echo $contact->user->name; // 这是用户的名字
```

挺酷的吧？

### 设置自定义数据
有时你可能需要附加一些唯一的内容到你的 ActiveRecord，比如一个自定义的计算结果，也许更容易附加到对象中，然后传递给模板等。

#### `setCustomData(string $field, mixed $value)`
你可以使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后你可以像引用普通对象属性一样引用它。

```php
echo $user->page_view_count;
```

### 事件

这个库中的一个非常棒的功能是有关事件。事件在根据你调用的某些方法以及时间触发。它们非常有用，可以自动设置数据。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果需要设置默认连接之类的东西，这将非常有帮助。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 不要忘记引用 &
		// 你可以这样做来自动设置连接
		$config['connection'] = Flight::db();
		// 或这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 你还可以这样设置表名。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

如果每次需要一个查询操作，这可能只对此有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 始终运行 id >= 0 如果你需要的话
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

这可能对每次获取记录时运行一些逻辑更有用！是否需要解密？是否需要每次运行一个自定义计数查询（性能不佳，但随你）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些东西
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 或者存储某些自定义数据像一个查询吗？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

如果每次需要一个查询操作，这可能只对此有用# FlightPHP 活动记录

活动记录既映射数据库实体到 PHP 对象。简单说，若数据库中有用户表，可将表中一行"翻译"为 `User` 类和代码库中的 `$user` 对象。参见[基本示例](#basic-example)。

## 基本示例

设想如下表：

```sql
CREATE TABLE 用户 (
	id INTEGER 主键, 
	名字 TEXT, 
	密码 TEXT 
);
```

现在可设置一个类表示该表：

```php
/**
 * 活动记录类通常为单数
 * 
 * 强烈推荐在此添加表的属性注释
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// 可以这样设置
		parent::__construct($database_connection, 'users');
		// 亦可如此
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

现在观察神奇发生！

```php
// 适用于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 仅为例子，实际会使用真实数据库连接

// 适用于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或没有基于对象的 mysqli 创建
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// 亦可 $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// 此处不可使用 $user->save()，否则将视为更新！

echo $user->id; // 2
```

轻而易举添加新用户！现在数据库中有一个用户行，如何取出？

```php
$user->find(1); // 查找 id = 1 的记录并返回它。
echo $user->name; // 'Bobby Tables'
```

如何找到所有用户？

```php
$users = $user->findAll();
```

有特定条件时如何处理？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看这有趣的功能？赶紧安装开始吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 使用

可作为独立库使用，亦可与 Flight PHP 框架一起。全取决于你。

### 独立库
确保向构造函数传递一个 PDO 连接。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 仅为例子，实际会使用真实数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如使用 Flight PHP 框架，可注册 ActiveRecord 类作为服务（但实际上不必要）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后可以在控制器、函数等中这样使用

Flight::user()->find(1);
```

## API 参考
### 增删改查函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并分配给当前对象。若传递某个 `$id`，将在主键上查找该值。如未传递任何内容，则查找表中第一条记录。

亦可传递其他辅助方法查询表。

```php
// 在先设置条件的基础上查找记录
$user->notNull('password')->orderBy('id DESC')->find();

// 通过指定 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找指定表中的所有记录。

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

将当前记录插入数据库。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

将当前记录更新至数据库。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

还可在搜索之前删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dirty 数据是指记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时没有任何"dirty"数据。

$user->email = 'test@example.com'; // 此时邮箱被认为是"dirty"数据，因为已更改。
$user->update();
// 现在没有任何dirty数据，因为已更新并保存至数据库

$user->password = password_hash()'newpassword'); // 此时是dirty数据
$user->dirty(); // 什么都不传会清除所有dirty条目。
$user->update(); // 什么都不更新，因为没有被捕获为dirty。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名字和密码都被更新。
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。非常适用于循环行为。若传递 `true`，也将重置用于查找当前对象的查询数据（默认行为）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 重置为初始状态
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如需要，可选择表中的某些列（对于列宽表更有效率）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

亦可选择其他表！尝试一下！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

可与数据库中的其他表联接。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

可设置一些自定义 where 参数（无法在此 where 语句中设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全注**：可能尝试像 `$user->where("id = '{$id}' AND name = '{$name}'")->find();`。请千万别这样做！这容易受到所谓的 SQL 注入攻击。有很多在线文章，搜索“sql 注入攻击 php”就能找到很多有关文章。在库中处理此事的方法是，而不是使用 `where()` 方法，应使用像 `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

对返回的查询结果进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回记录的数量。若提供第二个整数，将作偏移，限制就像 SQL 语句那样。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

其中 `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

其中 `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

其中 `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

其中 `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

其中 `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

其中 `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

其中 `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

其中 `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

其中 `field LIKE $value` 或 `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

其中 `field IN($value)` 或 `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

其中 `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### 关系
通过此库，可设置多种关系种类。可在表间设置一对多和一对一关系。在类前需进行一些额外设置。

设置 `$relations` 数组并不复杂，但正确语法可能会让人困惑。

```php
protected array $relations = [
	// 可以使用任何你喜欢的键名。ActiveRecord 的名称可能不错。例如: user, contact, client
	'whatever_active_record' => [
		// 必须的
		self::HAS_ONE, // 这是关系类型

		// 必须的
		'Some_Class', // 这是引用的"其他" ActiveRecord 类

		// 必须的
		'local_key', // 这是引用连接的本地键。
		// 提示一下，这只会连接到"其他"模型的主键

		// 可选的
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 你想执行的自定义方法。无需则使用 []
		
		// 可选的
		'back_reference_name' // 若想回向返回此关系，如: $user->contact->user;
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

现在设置好引用，可轻松使用！

```php
$user = new User($pdo_connection);

// 查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 利用关系查找联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 也可反向获取。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 利用关系查找用户：
echo $contact->user->name; // 这是用户的名字
```

很酷吧？

### 设置自定义数据
有时需连接某种独特内容到你的 ActiveRecord，如一个自定义计算结果可能更容易连接到对象中，然后传递至模板等。

#### `setCustomData(string $field, mixed $value)`
通过 `setCustomData()` 方法连接自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后简单地像正常对象属性一样引用。

```php
echo $user->page_view_count;
```

### 事件

此库的一个超赞功能是关于事件的。事件基于你调用的特定方法和时间而触发。它们在自动设置数据上非常有用。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

若需每次设置默认连接或类似东西，则非常有用。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 不要忘记引用 &
		// 你可以这样设置来自动设置连接
		$config['connection'] = Flight::db();
		// 或这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 你还可以这样设置表名。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

若需每次查询时进行查询操作，则非常有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 始终运行 id >= 0 如果你需要的话
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

若每次获取记录时进行某些逻辑运算很有用！需要解密吗？需要每次运行一个自定义计数查询吗（不太高效，但无所谓）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密一些内容
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 也许存储某些自定义数据像查询吗？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

若每次查询前操作查询很有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 始终运行 id >= 0 如果需要的话
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

类似于 `afterFind()` 但可对所有记录进行操作！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// 做一些很酷的东西像 afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

若每次插入操作时需要设定一些默认值则很有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 设定一些合理的默认值
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

可能会有一些特定操作需要在插入后进行？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 你需要做的操作
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 或其他....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

若每次更新时需要设定一些默认值则很有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 设定一些合理的默认值
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

可能会有一些特定操作需要在更新后进行？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 你需要做的操作
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 或其他....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

若希望在发生插入或更新时触发事件。长话短说，你可以猜到这是什么。

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

不确定你想要在这里做什么，但别客气！尽管去做！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '他是一名勇敢的战士... :cry-face:';
	} 
}
```

## 参与贡献

请尽情。

### 设置

在贡献时，请确保运行 `composer test-coverage` 以保持 100% 的测试覆盖率（这不是真正的单元测试覆盖率，更像集成测试）。

还请运行 `composer beautify` 和 `composer phpcs` 修复任何代码格式错误。

## 许可证

MIT