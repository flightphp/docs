# 飞行 Active Record

活动记录是将数据库实体映射到 PHP 对象。简单来说，如果您的数据库中有一个用户表，您可以将表中的一行“转换”为 `User` 类和代码库中的 `$user` 对象。请参见[基本示例](#基本示例)。

## 基本示例

假设您有以下表：

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY, 
    name TEXT, 
    password TEXT 
);
```

现在，您可以设置一个新类来表示此表：

```php
/**
 * Active Record 类通常为单数形式
 * 
 * 强烈建议在此处添加表的属性作为注释
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
    public function __construct($database_connection)
    {
        // 这样设置也可以
        parent::__construct($database_connection, 'users');
        // 或者这样设置
        parent::__construct($database_connection, null, [ 'table' => 'users']);
    }
}
```

现在看魔术发生！

```php
// 对于 sqlite
$database_connection = new PDO('sqlite:test.db'); // 这仅为示例，您可能会使用真实的数据库连接

// 对于 mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// 或者 mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// 或者 mysqli 以非对象为基础的创建
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
// 不能在这里使用 $user->save()，否则它会认为这是一个更新！

echo $user->id; // 2
```

添加新用户真是太容易了！既然数据库中有一个用户行，那么如何提取它呢？

```php
$user->find(1); // 查找 id = 1 的记录并返回
echo $user->name; // 'Bobby Tables'
```

如果要查找所有用户呢？

```php
$users = $user->findAll();
```

如果想使用特定条件呢？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

看这有多有趣？让我们安装它并开始吧！

## 安装

只需使用 Composer 安装

```php
composer require flightphp/active-record 
```

## 使用

可以作为独立库或与 Flight PHP 框架一起使用。完全由您决定。

### 独立使用
只需确保将 PDO 连接传递给构造函数。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 这只是一个示例，您可能会使用真实的数据库连接

$User = new User($pdo_connection);
```

### Flight PHP 框架
如果正在使用 Flight PHP 框架，可以将 ActiveRecord 类注册为服务（但实际上您不必这样做）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 然后您可以在控制器、函数等中这样使用。

Flight::user()->find(1);
```

## CRUD 函数

#### `find($id = null) : boolean|ActiveRecord`

查找一条记录并将其分配给当前对象。如果传递某种 `$id`，它将使用该值在主键上执行查找。如果未传递任何内容，它将仅查找表中的第一条记录。

此外，您可以通过传递其他助手方法来对表进行查询。

```php
// 在实际查找记录之前查找具有某些条件的记录
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

#### `isHydrated(): boolean` (v0.4.0)

如果当前记录已被提取（从数据库中检索），则返回 `true`。

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

#### `update(): boolean|ActiveRecord`

更新当前记录到数据库。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

将当前记录插入或更新到数据库。如果记录具有 id，则进行更新，否则进行插入。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** 如果类中定义了关系，且这些关系已定义、实例化并且具有要更新的脏数据，则它将递归保存这些关系。 （从 v0.4.0 版本开始）

#### `delete(): boolean`

从数据库中删除当前记录。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

您还可以在执行搜索前删除多个记录。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

脏数据是指记录中已更改的数据。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 到目前为止没有数据被视为“脏数据”。

$user->email = 'test@example.com'; // 此时 email 被视为“脏数据”因为它已更改。
$user->update();
// 现在没有任何数据被视为“脏数据”，因为数据已更新并持久保存到了数据库中。

$user->password = password_hash()'newpassword'); // 现在这是脏数据
$user->dirty(); // 不传递任何内容将清除所有脏条目。
$user->update(); // 未更新任何内容，因为没有被捕获为脏数据。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名称和密码都将更新。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

这是 `dirty()` 方法的别名。这样更清楚您正在做什么。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名称和密码都将更新。
```

#### `isDirty(): boolean` (v0.4.0)

如果当前记录已更改，则返回 `true`。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

将当前记录重置为其初始状态。在循环类型行为中使用此功能非常好。如果传递 `true`，它还将重置用于查找当前对象的查询数据（默认行为）。

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

在运行 `find()`、`findAll()`、`insert()`、`update()` 或 `save()` 方法后，您可以获取构建的 SQL 并将其用于调试目的。

## SQL 查询方法
#### `select(string $field1 [, string $field2 ... ])`

如果需要，可以仅选择表中的一部分列（对于具有多个列且宽度很大的表格来说性能更好）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

您还可以选择另一个表！为什么不呢？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

还可以加入到数据库中的另一个表。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

您可以设置一些自定义的 Where 参数（在此 Where 语句中不能设置参数）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**安全注意** - 您可能会尝试类似于 `$user->where("id = '{$id}' AND name = '{$name}'")->find();` 这样的操作。请**不要这样做**！这容易受到 SQL 注入攻击。在线上有很多文章，请谷歌搜索“sql 注入攻击 php”后会发现有很多关于这个主题的文章。使用此库的正确方法是，使用 `eq('id', $id)->eq('name', $name)->find();` 这样的方法，而不是 `where()`。如果绝对必须这样做，PDO 库具有 `$pdo->quote($var)` 可以为您转义。只有在使用了 `quote()` 之后，才可以在 `where()` 语句中使用。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

按特定条件对结果进行分组。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

以特定方式对返回的查询结果进行排序。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

限制返回的记录数量。如果给出第二个 int，它将是偏移量，就像在 SQL 中一样。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件
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

## 关系
使用此库可以设置几种类型的关系。可以在表格之间设置一对多和一对一关系。这需要事先在类中进行一些额外的设置。

设置 `$relations` 数组并不困难，但猜测正确的语法可能会令人困惑。

```php
protected array $relations = [
    // 您可以为键指定任何名称。ActiveRecord 的名称可能很好。例如：user、contact、client
    'user' => [
        // 必填
        // self::HAS_MANY、self::HAS_ONE、self::BELONGS_TO
        self::HAS_ONE, // 这是关系类型

        // 必填
        'Some_Class', // 这是“其他” ActiveRecord 类将引用的类

        // 必填
        // 根据关系类型
        // self::HAS_ONE = 引用连接的外键
        // self::HAS_MANY = 引用连接的外键
        // self::BELONGS_TO = 引用连接的本地键
        'local_or_foreign_key',
        // 只是 FYI，这也仅加入到“其他”模型的主键

        // 可选
        [ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 在加入关系时您想要的其他条件
        // $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

        // 可选
        'back_reference_name' // 如果您想将此关系返回到自身的话，将关系反向引用到这里。例如：$user->contact->user;
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

现在我们已经设置了引用，因此可以轻松使用它们！

```php
$user = new User($pdo_connection);

// 查找最近的用户。
$user->notNull('id')->orderBy('id desc')->find();

// 通过使用关系查找联系人：
foreach($user->contacts as $contact) {
    echo $contact->id;
}

// 或者我们也可以换一个方式。
$contact = new Contact();

// 查找一个联系人
$contact->find();

// 通过使用关系查找用户：
echo $contact->user->name; // 这是用户名称
```

很酷吧？

## 设置自定义数据
有时，您可能需要附加一些特殊内容到您的 ActiveRecord 中，例如某种自定义计算。这样可能更容易附加到对象中，然后传递到模板中。

#### `setCustomData(string $field, mixed $value)`
使用 `setCustomData()` 方法附加自定义数据。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

然后您只需像普通对象属性一样引用它。

```php
echo $user->page_view_count;
```

## 事件

关于此库的另一个超级棒功能是事件。事件在特定的时间基于调用的方法触发。根据您调用的某些方法，事件在特定时间触发。根据您调用的某些方法，在特定时机触发事件非常有助于为您自动设置数据。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

如果需要在构造过程中设置默认连接之类的内容，则此功能非常有用。

```php
// index.php 或 bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

    protected function onConstruct(self $self, array &$config) { // 不要忘记 & 引用
        // 您可以这样自动设置连接
        $config['connection'] = Flight::db();
        // 或者这样
        $self->transformAndPersistConnection(Flight::db());
        
        // 您还可以这样设置表名。
        $config['table'] = 'users';
    } 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

如果每次都需要查询操作的调整，这只是有用的。

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeFind(self $self) {
        // 如果这是您的喜好，则始终运行 id >= 0
        $self->gte('id', 0); 
    } 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

如果您需要每次记录检索都运行一些逻辑，这是有用的。

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterFind(self $self) {
        // 解密某些内容
        $self->secret = yourDecryptFunction($self->secret, $some_key);

        // 可能会存储类似查询之类的自定义内容???
        $self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
    } 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

如果每次都需要查询操作的调整，这只是有用的。

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeFindAll(self $self) {
        // 如果这是您的喜好，则始终运行 id >= 0
        $self->gte('id', 0); 
    } 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

类似于 `afterFind()`，但您可以对所有记录进行操作。

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterFindAll(array $results) {

        foreach($results as $self) {
            // 进行类似 afterFind() 的操作
        }
    } 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

如果需要在每次插入时设置默认值，则这很有用。

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeInsert(self $self) {
        // 设置一些默认值
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

也许您有某些用例需要在插入后更改数据？

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterInsert(self $self) {
        // 自己操作
        Flight::cache()->set('most_recent_insert_id', $self->id);
        // 或其他什么东西....
    } 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

如果需要在更新时设置默认值，则这很有用。

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function beforeInsert(self $self) {
        // 设置一些默认值
        if(!$self->updated_date) {
            $self->updated_date = gmdate('Y-m-d');
        }
    } 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

也许您有某些用例需要在更新后更改数据？

```php
class User extends flight\ActiveRecord {
    
    public function __construct($database_connection)
    {
        parent::__construct($database_connection, 'users');
    }

    protected function afterInsert(self $self) {
        // 自己操作
        Flight::cache()->set('most_recently_updated_user_id', $self->id);
        // 或其他什么东西....
    } 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

如果您希望在发生插入或更新时都触发事件，则这是有用的。我将跳过冗长的解释，但我相信您可以猜到这是什么。

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

不确定您希望在这里做什么，但不要有任何判断！去做吧！

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

使用该库时，可以通过几种不同的方式设置数据库连接。您可以在构造函数中设置连接，可以通过配置变量 `$config['connection']` 设置连接，也可以通过 `setDatabaseConnection()`（v0.4.1）设置连接。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例如
$user = new User($pdo_connection);
// 或者
$user = new User(null, [ 'connection' => $pdo_connection ]);
// 或者
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

如果需要刷新数据库连接，例如如果正在运行长时间运行的 CLI 脚本并且需要定期刷新连接，则可以使用 `$your_record->setDatabaseConnection($pdo_connection)` 重新设置连接。

## 贡献

请自便。:D

## 设置

在贡献时，请确保运行 `composer test-coverage` 以保持 100% 的测试覆盖率（这不是真正的单元测试覆盖率，更像是集成测试）。

还要确保运行 `composer beautify` 和 `composer phpcs` 以修复任何代码风格问题。

## 许可证

MIT