# Flight Active Record 

活动记录将数据库实体映射到 PHP 对象。简单来说，如果您的数据库中有一个名为 users 的表，您可以将该表中的一行“转换”为 `User` 类和代码库中的 `$user` 对象。请参阅 [基本示例](#basic-example)。

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
 * ActiveRecord 类通常是单数形式
 * 
 * 强烈建议在此处将表的属性作为注释添加
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

现在让魔法发生！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这只是一个示例，您可能会使用一个真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', '用户名', '密码');

// 或者 mysqli
$database_connection = new mysqli('localhost', '用户名', '密码', 'test_db');
// 或者 基于非对象的 mysqli 创建
$database_connection = mysqli_connect('localhost', '用户名', '密码', 'test_db');

$user = new User($database_connection);
$user->name = '鲍比 表';
$user->password = password_hash('一些很酷的密码');
$user->insert();
// 或者 $user->save();

echo $user->id; // 1

$user->name = '约瑟夫 妈妈';
$user->password = password_hash('再次一些很酷的密码！！！');
$user->insert();
// 这里不能使用 $user->save() 否则它会认为这是一次更新！

echo $user->id; // 2
```

添加新用户就是这么简单！现在数据库中有一个用户行，如何将其取出？

```php
$user->find(1); // 查找 id = 1 的记录并返回
echo $user->name; // '鲍比 表'
```

如果您想查找所有用户呢？

```php
$users = $user->findAll();
```

带有特定条件的情况呢？

```php
$users = $user->like('name', '%妈妈%')->findAll();
```

看看这是多么有趣？让我们安装它并开始使用！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 使用

这可以作为一个独立库或与 Flight PHP 框架一起使用。完全取决于您。

### 独立使用
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是一个示例，您可能会使用一个真实的数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如果您正在使用 Flight PHP 框架，您可以将 ActiveRecord 类注册为服务（但您实际上并不需要）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后您可以在控制器、函数等中使用它如下：

Flight::user()->find(1);
```

## CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果您传递某种 `$id`，它将使用该值在主键上执行查找。如果没有传递任何内容，则它只会查找表中的第一条记录。

此外，您可以将其他辅助方法传递给它以查询表格。

```php
// 在先前查找某些条件的记录
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

将当前记录插入数据库。

```php
$user = new User($pdo_connection);
$user->name = '演示';
$user->password = md5('演示');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

更新当前记录到数据库。

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

您还可以在提前执行搜索的情况下删除多条记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dirty 数据是指记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 此时还没有“脏”数据。

$user->email = 'test@example.com'; // 现在电子邮件被认为是“脏”的，因为它已更改。
$user->update();
// 现在没有任何“脏”数据，因为它已被更新并持久化在数据库中

$user->password = password_hash()'newpassword'); // 现在这是“脏”的
$user->dirty(); // 传递空值将清除所有脏数据。
$user->update(); // 由于未捕获任何脏数据，因此不会更新。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名称和密码都已更新。
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。这在循环行为中非常有用。
如果传递 `true`，它还将重置用于查找当前对象的查询数据（默认行为）。

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

## SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

您可以选择表中仅几列（在具有许多列的宽表上执行效率更好）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

您还可以选择其他表！为什么不能呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

您甚至可以加入到数据库中的另一个表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

您可以设置一些自定义的 where 参数（您无法在此 where 语句中设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全提示** - 您可能会想要做类似的事情 `$user->where("id = '{$id}' AND name = '{$name}'")->find();`。请不要这样做！这容易受到所谓的 SQL 注入攻击的影响。有很多在线文章，请搜索一下“sql注入攻击 php”，您会找到很多关于此主题的文章。通过这个库处理这种情况的正确方法是，而不是使用 `where()` 方法，您将执行类似 `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

以特定方式排序返回的查询。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果给出第二个整数，则会偏移，依此类推。

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
您可以使用此库设置几种类型的关系。您可以在表格之间设置一对多和一对一关系。这需要在类之前做一些额外设置。

设置 `$relations` 数组并不困难，但猜测正确的语法可能会混淆。

```php
protected array $relations = [
	// 您可以将键命名为任何您喜欢的内容。ActiveRecord 的名称可能很合适。例如：user、contact、client
	'user' => [
		// 必需的
		// self::HAS_MANY、self::HAS_ONE、self::BELONGS_TO
		self::HAS_ONE, // 这是关系类型

		// 必需的
		'Some_Class', // 这是它将引用的“另一个” ActiveRecord 类

		// 必需的
		// 取决于关系类型
		// self::HAS_ONE = 引用的外键
		// self::HAS_MANY = 引用的外键
		// self::BELONGS_TO = 引用连接的本地键
		'local_or_foreign_key',
		// 只是 FYI，这也仅加入到“其他”模型的主键

		// 可选的
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 在加入关系时需要的其他条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 可选的
		'back_reference_name' // 如果您想要将此关系的反向引用返回给自身，这很有用 例如：$user->contact->user;
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

现在我们已经设置引用，所以我们可以非常轻松地使用它们！

```php
$user = new User($pdo_connection);

// 查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 使用关系获取联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// 或者我们可以倒过来。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 使用关系找到用户：
echo $contact->user->name; // 这是用户名
```

很酷对吧？

## 设置自定义数据
有时您可能需要附加某些唯一的内容到您的 ActiveRecord，例如一个自定义计算可能更容易附加到对象中，然后传递到如模板等。

#### `setCustomData(string $field, mixed $value)`
您使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后您只需像正常对象属性一样引用。

```php
echo $user->page_view_count;
```

## 事件

此库的另一个超级棒的功能是事件。事件在您调用某些方法时基于某些时机触发。在自动为您设置数据方面非常非常有帮助。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果您需要每次设置默认连接等内容，这一点非常有帮助。

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
		// 或这样
		$self->transformAndPersistConnection(Flight::db());
		
		// 您还可以以此方式设置表名称。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

如果您每次都需要执行一些查询操作。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 始终执行 id >= 0 如果这符合您的意愿
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果您每次需要在提取此记录时运行某种逻辑，这可能更有用。您需要解密某些内容吗？您需要每次运行自定义计数查询吗（性能不佳，但无所谓）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 解密某些内容
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 或许存储一些类似查询之类的自定义内容？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

如果您每次都需要执行一些查询操作。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 始终运行 id >= 0 如果这符合您的意愿
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

类似于 `afterFind()`，但您可以对所有记录执行操作！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// 做一些酷炫的事情就像 afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

如果您每次都需要设置一些默认值。

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

也许您有一个情况，在插入后更改数据？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 随心所欲
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// 还是别的....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

如果您每次更新时需要设置某些默认值。

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

也许您有一个情况，在更新后更改数据？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 随心所欲
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// 还是别的....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果您希望在插入或更新时都发生事件。我将跳过冗长的解释，但我相信您可以猜到是什么。

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

不确定您想在这里做什么，但不会有任何批评！尽管去吧！

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

## 贡献

请尽情贡献。

## 设置

在贡献时，请确保运行 `composer test-coverage` 以维持 100% 的测试覆盖率（这不是真正的单元测试覆盖率，更像集成测试）。

还要确保运行 `composer beautify` 和 `composer phpcs` 以修复任何 linting 错误。

## 许可证

MIT