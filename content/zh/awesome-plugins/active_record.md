# FlightPHP 活动记录

活动记录是将数据库实体映射到 PHP 对象。简单地说，如果您的数据库中有一个用户表，您可以将该表中的一行“转换”为一个 `User` 类和一个在代码库中的 `$user` 对象。参见[基本示例](#基本示例)。

## 基本示例

假设您有以下表：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

现在您可以设置一个新类来表示这个表：

```php
/**
 * 活动记录类通常是单数形式
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

现在让魔法发生吧！

```php
// 对于sqlite
$database_connection = new PDO('sqlite:test.db'); // 这只是一个示例，您可能会使用一个真实的数据库连接

// 对于mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者使用非基于对象的mysqli创建
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
// 这里不能使用 $user->save()，否则它将认为它是一个更新！

echo $user->id; // 2
```

添加新用户就是这么简单！现在数据库中有一个用户行，如何将其提取出来呢？

```php
$user->find(1); // 查找 id = 1 的记录并返回它。
echo $user->name; // 'Bobby Tables'
```

如果要查找所有用户呢？

```php
$users = $user->findAll();
```

在特定条件下呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看，这有多有趣？让我们安装它并开始使用吧！

## 安装

只需使用Composer安装

```php
composer require flightphp/active-record 
```

## 用法

这可以作为一个独立的库或与 Flight PHP 框架一起使用。完全取决于您。

### 独立使用
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是一个示例，您可能会使用一个真实的数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如果您正在使用 Flight PHP 框架，可以将 ActiveRecord 类注册为服务（但是您不必这样做）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后您可以在控制器、函数等中这样使用：

Flight::user()->find(1);
```

## API 参考
### CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其赋值给当前对象。如果传递了某种 `$id`，它将使用该值在主键上执行查找。如果没有传递任何内容，它只会查找表中的第一条记录。

此外，您可以将其他辅助方法传递给它以查询您的表。

```php
//在事先具有某些条件的记录中查找记录
$user->notNull('password')->orderBy('id DESC')->find();

//按特定 id 查找记录
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

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据是指记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

//此时没有数据是“脏”的。

$user->email = 'test@example.com'; // 现在电子邮件被认为是“脏”的，因为已更改。
$user->update();
//现在没有任何数据是“脏”的，因为它已更新并持久保存在数据库中。

$user->password = password_hash()'newpassword'); // 现在这是脏数据
$user->dirty(); // 传递空值将清除所有脏条目。
$user->update(); // 未更新任何内容，因为没有被捕获为脏数据。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name 和 password 都已更新。
```

### SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果需要，您可以仅选择一些表中的列（在具有许多列的宽表上效果更好）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

您还可以选择另一个表！为什么不呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

您甚至可以加入数据库中的另一个表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

您可以设置一些自定义的 where 条件（在此 where 语句中无法设置参数）

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全注意** - 您可能会尝试执行类似 `$user->where("id = '{$id}' AND name = '{$name}'")->find();` 这样的操作。请不要这样做！这是易受到 SQL 注入攻击的。有许多在线文章，请搜索“sql 注入攻击 php”，您将找到很多关于此主题的文章。此库正确处理此事的方法是，不要使用此 `where()` 方法，而应该使用更像 `$user->eq('id', $id)->eq('name', $name)->find();` 这样的方法。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

根据特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

以特定方式对返回的查询结果进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果给定第二个整数，它将是偏移量，就像在 SQL 中一样。

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
您可以使用此库设置几种类型的关系。可以在表之间设置一对多和一对一关系。这在类中需要一些额外的设置。

设置`$relations`数组并不难，但猜测正确的语法可能会令人困惑。

```php
protected array $relations = [
	//您可以将键命名为您喜欢的任何内容。默认情况下，ActiveRecord 的名称可能是不错的选择。例如：user、contact、client
	'whatever_active_record' => [
		// 必需的
		self::HAS_ONE, //这是关系的类型

		//必需的
		'Some_Class', //这是此关系将引用的“其他” ActiveRecord 类

		//必需的
		'local_key', //这是引用连接的 local_key。
		//只是 FYI，这还仅连接到“其他”模型的主键

		//可选的
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], //您想要执行的自定义方法。如果您不需要任何方法，使用 []。

		//可选的
		'back_reference_name' //如果您希望将此关系反向引用到它本身，则需要这样设置。例如：$user->contact->user;
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

现在我们已经设置好引用，因此我们可以非常容易地使用它们！

```php
$user = new User($pdo_connection);

//查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

//使用关系获取联系人：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

//或者我们可以用另一种方式。
$contact = new Contact();

//查找一个联系人
$contact->find();

//使用关系获取用户：
echo $contact->user->name; //这是用户名
```

很酷，对吧？

### 设置自定义数据
有时，您可能需要附加某些唯一的内容到您的 ActiveRecord，比如可能更容易地附加到对象，然后传递到模板中的自定义计算。

#### `setCustomData(string $field, mixed $value)`
您可以使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后您只需像正常对象属性一样引用它。

```php
echo $user->page_view_count;
```

### 事件

关于此库的另一个超级棒功能就是事件。事件在您调用某些方法时，在某些时间点触发。它们非常有助于自动为您设置数据。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果需要设置默认连接或类似内容，这将非常有用。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { //不要忘记 & 引用
		//您可以这样设置以自动设置连接
		$config['connection'] = Flight::db();
		//或这样
		$self->transformAndPersistConnection(Flight::db());
		
		//您还可以这样设置表名。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

仅在每次需要查询时才会有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		//始终运行 id >= 0 ！
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果每次获取此记录时都需要运行某些逻辑，这将很有用。是否需要解密某些内容？是否需要每次都运行自定义计数查询（性能不高但无论如何）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		//解密某些内容
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		//也许存储一些自定义内容如查询？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

仅在每次需要查询时才会有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		//始终运行 id >= 0 ！
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

与 `afterFind()` 类似，但您可以使用它来处理所有记录！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			//做一些酷炫的事情，就像在 afterFind() 中一样。
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

如果需要每次设置一些默认值，这将非常有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		//设置一些合理的默认值
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

也许您有在插入后更改数据的业务情况？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		//您可以这样
		Flight::cache()->set('most_recent_insert_id', $self->id);
		//或其他....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

仅在每次更新时设置一些默认值时非常有用。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		//设置一些合理的默认值
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

也许您有在更新后更改数据的业务情况？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		//您可以这样
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		//或其他....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果您希望在插入或更新发生时都发生事件，这将会非常有用。我会跳过漫长的解释，但我相信您可以猜到它是什么。

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

不确定您希望在这里做些什么，但这里不做评判！尽情去做吧！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '他是一位勇敢的战士... :cry-face:';
	} 
}
```

## 贡献

请吧。

### 设置

当您贡献时，确保运行 `composer test-coverage` 以保持 100% 的测试覆盖率（这不是真正的单元测试覆盖率，更像是集成测试）。

还确保运行 `composer beautify` 和 `composer phpcs` 来修复任何代码格式错误。

## 许可证

MIT