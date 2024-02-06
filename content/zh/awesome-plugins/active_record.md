# FlightPHP 活动记录

活动记录是将数据库实体映射到 PHP 对象。简单来说，如果您的数据库中有一个用户表，您可以将该表中的一行“转换”为 `User` 类和代码库中的 `$user` 对象。请参阅 [基本示例](#basic-example)。

## 基本示例

假设您有以下表格：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

现在，您可以设置一个新类来表示这个表格：

```php
/**
 * 活动记录类通常是单数
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
		// 您可以这样设置它
		parent::__construct($database_connection, 'users');
		// 或者这样
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

现在让魔术发生吧！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这仅供参考，您可能会使用一个真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者使用 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者使用基于非对象的 mysqli
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
// 这里不能使用 $user->save()，否则它会认为这是一个更新！

echo $user->id; // 2
```

添加一个新用户就是这么简单！现在数据库中有一个用户行，如何将其取出呢？

```php
$user->find(1); // 查找 id = 1 并返回数据库中的值。
echo $user->name; // 'Bobby Tables'
```

如果您想找到所有用户怎么办？

```php
$users = $user->findAll();
```

如果要在特定条件下查找怎么办？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看，这有多有趣？让我们安装它并开始吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 用法

这可以作为一个独立的库使用，也可以与 Flight PHP 框架一起使用。完全取决于您。

### 独立使用
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这仅供参考，您可能会使用一个真实的数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如果您正在使用 Flight PHP 框架，可以将 ActiveRecord 类注册为一个服务（但实际上您不需要）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后您可以在控制器、函数等中像这样使用。

Flight::user()->find(1);
```

## API 参考
### CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果传递某种 `$id`，它将使用该值对主键执行查找。如果没有传递任何内容，则只会找到表中的第一条记录。

此外，您可以将其他辅助方法传递给它以查询您的表。

```php
// 在查询记录之前查找具有某些条件的记录
$user->notNull('password')->orderBy('id DESC')->find();

// 通过特定 id 查找记录
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

查找您指定的表中的所有记录。

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

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据是指记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时没有任何数据“脏”。

$user->email = 'test@example.com'; // 现在电子邮件被认为是“脏”的，因为它已更改。
$user->update();
// 现在没有脏数据，因为它已更新并保留在数据库中

$user->password = password_hash()'newpassword'); // 现在这是脏数据
$user->dirty(); // 不传递任何内容将清除所有脏条目。
$user->update(); // 未更新任何数据，因为没有被标记为脏数据。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名称和密码都已更新。
```

### SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果需要，您可以仅选择表中的部分列（在具有许多列的宽表格上执行此操作会更有效率）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

您还可以选择另一个表！为什么不呢！？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

您甚至可以加入到数据库中的另一个表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

您可以设置一些自定义的 where 条件（在此 where 语句中不能设置参数）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全提示** - 您可能会想要做类似于 `$user->where("id = '{$id}' AND name = '{$name}'")->find();`，请不要这样做！！！这容易受到所谓的 SQL 注入攻击的影响。有很多在线文章，请搜索“sql 注入攻击 php”，您将会找到许多关于此主题的文章。使用此库处理这种情况的正确方式是，而不是使用 `where()` 方法，您将使用更类似于 `$user->eq('id', $id)->eq('name', $name)->find();` 这样的方法。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

根据特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

按特定方式对返回的查询进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录的数量。如果给出第二个 int 值，则它将是偏移量，limit 就像 SQL 中一样。

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
使用此库，可以设置几种类型的关系。您可以在表之间设置一对多和一对一的关系。这需要在类中之前进行一些额外的设置。

设置 `$relations' 数组并不难，但猜出正确的语法可能会令人困惑。

```php
protected array $relations = [
	// 您可以将键命名为任何您喜欢的内容。ActiveRecord 的名称可能很好。例如: user, contact, client
	'whatever_active_record' => [
		// 必需
		self::HAS_ONE, // 这是关系的类型

		// 必需
		'Some_Class', // 这是此关系将引用的“其他” ActiveRecord 类

		// 必需
		'local_key', // 这个 local_key 引用连接。
		// 只是 FYI，这也只与“other”模型的主键连接

		// 可选
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 您要执行的自定义方法。如果您不需要任何内容，则使用 []。

		// 可选
		'back_reference_name' // 如果您想将此关系的引用反向引用到其自身，这很有用。例如：$user->contact->user;
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

现在我们已经设置了引用，所以我们可以非常轻松地使用它们！

```php
$user = new User($pdo_connection);

// 查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 通过关系获取联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 或者我们可以换个方式。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 通过关系获取用户：
echo $contact->user->name; // 这是用户的名称
```

很酷吧？

### 设置自定义数据
有时您可能需要附加一些唯一的内容到您的 ActiveRecord 中，例如一个可能更容易附加到对象中的自定义计算，然后传递到模板中。

#### `setCustomData(string $field, mixed $value)`
使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后您只需像正常对象属性一样引用它。

```php
echo $user->page_view_count;
```

### 事件

关于此库的一个更超级棒的功能是事件。事件根据您调用的某些方法在某些时间触发。根据某些时间触发的事件建立数据会非常有帮助。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果需要在构造函数中设置默认连接之类的内容，则这将非常有帮助。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 不要忘记 & 引用
		// 您可以这样做来自动设置连接
		$config['connection'] = Flight::db();
		// 或者这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 您还可以通过这种方式设置表名称。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

如果每次需要进行查询操作时都需要进行查询操作，则此方法可能很有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 如果需要始终运行 id >= 0，则运行此代码
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果每次获取此记录时都需要运行某些逻辑，那么此方法可能很有用。您需要解密一些内容吗？您是否需要每次运行自定义计数查询（效率不高但也可以）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些内容
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 也许存储一些类似查询的自定义数据？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

...