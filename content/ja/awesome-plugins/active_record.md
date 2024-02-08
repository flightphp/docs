# FlightPHP アクティブレコード

アクティブレコードは、データベースエンティティをPHPオブジェクトにマッピングするものです。端的に言えば、データベースに users テーブルがある場合、そのテーブル内の行を `User` クラスと `$user` オブジェクトに"変換"することができます。[基本的な例](#basic-example)を参照してください。

## 基本的な例

以下のテーブルがあると仮定します：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

これを表す新しいクラスを設定できます：

```php
/**
 * アクティブレコードクラスは一般的に単数形です
 * 
 * ここにテーブルのプロパティをコメントとして追加することを強く推奨します
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// このように設定できます
		parent::__construct($database_connection, 'users');
		// またはこのようにもできます
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

さあ、魔法が起こります！

```php
// SQLite の場合
$database_connection = new PDO('sqlite:test.db'); // これは例です、実際には実在するデータベース接続を使用するはずです

// MySQL の場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'ユーザ名', 'パスワード');

// または mysqli の場合
$database_connection = new mysqli('localhost', 'ユーザ名', 'パスワード', 'test_db');
// またはオブジェクトベースでない mysqli の場合
$database_connection = mysqli_connect('localhost', 'ユーザ名', 'パスワード', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// または $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ここでは $user->save() は使用できません！

echo $user->id; // 2
```

新しいユーザを追加するのはこれだけ簡単でした！さて、データベース内にユーザ行があるので、それを取得する方法は？

```php
$user->find(1); // データベース内の id = 1 を見つけ、それを返します。
echo $user->name; // 'Bobby Tables'
```

そして、すべてのユーザを検索したい場合は？

```php
$users = $user->findAll();
```

ある条件で検索したい場合はどうすればいいですか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

楽しいですね！インストールして始めましょう！

## インストール

Composer で簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使用方法

これはスタンドアロンライブラリとしても、Flight PHP Framework と一緒に使用できます。完全にお好みに合わせて選択してください。

### スタンドアロン
単純に PDO 接続をコンストラクタに渡すだけです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です、実際には実在するデータベース接続を使用するはずです

$User = new User($pdo_connection);
```

### Flight PHP Framework
Flight PHP Framework を使用している場合、ActiveRecord クラスをサービスとして登録できます（しかし、実際には必要ありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラや関数などで次のように使用できます。

Flight::user()->find(1);
```

## API リファレンス
### CRUD 関数

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて、現在のオブジェクトに割り当てます。何らかの `$id` を渡すと、その値で主キーを検索します。何も渡されない場合は、テーブル内の最初のレコードだけを検索します。

追加で、テーブルをクエリするための他のヘルパーメソッドを渡すこともできます。

```php
// 事前にいくつかの条件でレコードを検索
$user->notNull('password')->orderBy('id DESC')->find();

// 特定の ID でレコードを検索
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを検索します。

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'デモ';
$user->password = md5('デモ');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベース内で更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Dirty" データとは、レコード内で変更されたデータのことを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も "dirty" ではありません。

$user->email = 'test@example.com'; // 今、email は変更されたため "dirty" と見なされます。
$user->update();
// 今やデータベースに反映されたので、"dirty" データはもうありません。

$user->password = password_hash()'newpassword'); // これは今 "dirty" です
$user->dirty(); // 何も渡さない場合は、すべての "dirty" エントリをクリアします。
$user->update(); // "dirty" なデータがキャプチャされていないため、何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password が両方更新されます。
```

### SQL クエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

テーブル内のいくつかの列だけを選択できます（多くの列を持つ非常に幅広いテーブルではパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

別のテーブルも選択できます！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルにも結合できます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムの where 引数を設定できます（この where 文にはパラメータを設定できません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();` のようにしたくなるかもしれませんが、これは SQL インジェクション攻撃に対して脆弱です。これについて多くの記事がオンラインで見つかりますので、"sql injection attacks php" で Google 検索してみてください。このライブラリでこれを処理する適切な方法は、この `where()` メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();` のようにすることです。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

結果を特定の条件でグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

クエリの結果を特定の方法でソートします。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。2 番目の int が与えられると、SQL と同じようにオフセット、リミットが適用されます。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` を適用します。

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` を適用します。

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL` を適用します。

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL` を適用します。

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value` を適用します。

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value` を適用します。

```php
$user->lt('id', 1)->find();
```

#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value` を適用します。

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value` を適用します。

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` または `field NOT LIKE $value` を適用します。

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` または `field NOT IN($value)` を適用します。

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1` を適用します。

```php
$user->between('id', [1, 2])->find();
```

### 関連性
このライブラリを使用して、さまざまな種類の関係を設定できます。テーブル間での one->many および one->one の関係を設定できます。これにはクラスの事前に少しのセットアップが必要です。

`$relations` 配列を設定することは難しくありませんが、正しい構文を推測することが混乱する可能性があります。

```php
protected array $relations = [
	// キー名は何でもかまいません。ActiveRecord の名前がおそらくいいでしょう。例: user, contact, client
	'whatever_active_record' => [
		// 必須
		self::HAS_ONE, // これは関係の種類です

		// 必須
		'Some_Class', // これは "other" ActiveRecord クラスです

		// 必須
		'local_key', // 参照する join の local_key です。
		// ちなみに、これは "other" モデルの主キーにのみ参加します

		// オプション
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 実行しようとするカスタムメソッド。何も欲しくない場合は [] です。

		// オプション
		'back_reference_name' // これはこの関係を自身に戻す場合の名前です Ex: $user->contact->user;
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

これで参照が設定されましたので、非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最新のユーザを見つける
$user->notNull('id')->orderBy('id desc')->find();

// 関係を使ってコンタクトを取得：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆もできます。
$contact = new Contact();

// コンタクトを 1 つ見つける
$contact->find();

// 関係を使ってユーザを取得：
echo $contact->user->name; // これがユーザー名です
```

すごく面白いですね？

### カスタムデータの設定
場合によっては、テンプレートに渡すのが簡単なカスタム計算など、レコードに独自のものをアタッチする場合があります。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` メソッドを使用して、カスタムデータをアタッチします。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

そして、通常のオブジェクトのプロパティのように参照します。

```php
echo $user->page_view_count;
``# フライトPHP アクティブレコード

アクティブレコードは、データベースエンティティをPHPオブジェクトにマッピングするものです。端的に言えば、データベースに users テーブルがある場合、そのテーブル内の行を `User` クラスと `$user` オブジェクトに"変換"することができます。[基本的な例](#basic-example)を参照してください。

## 基本的な例

以下のテーブルがあると仮定します：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

これを表す新しいクラスを設定できます：

```php
/**
 * アクティブレコードクラスは一般的に単数形です
 * 
 * ここにテーブルのプロパティをコメントとして追加することを強く推奨します
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// このように設定できます
		parent::__construct($database_connection, 'users');
		// またはこのようにもできます
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

さあ、魔法が起こります！

```php
// SQLite の場合
$database_connection = new PDO('sqlite:test.db'); // これは例です、実際には実在するデータベース接続を使用するはずです

// MySQL の場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'ユーザ名', 'パスワード');

// または mysqli の場合
$database_connection = new mysqli('localhost', 'ユーザ名', 'パスワード', 'test_db');
// またはオブジェクトベースでない mysqli の場合
$database_connection = mysqli_connect('localhost', 'ユーザ名', 'パスワード', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// または $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ここでは $user->save() は使用できません！

echo $user->id; // 2
```

新しいユーザを追加するのはこれだけ簡単でした！さて、データベース内にユーザ行があるので、それを取得する方法は？

```php
$user->find(1); // データベース内の id = 1 を見つけ、それを返します。
echo $user->name; // 'Bobby Tables'
```

そして、すべてのユーザを検索したい場合は？

```php
$users = $user->findAll();
```

ある条件で検索したい場合はどうすればいいですか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

楽しいですね！インストールして始めましょう！

## インストール

Composer で簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使用方法

これはスタンドアロンライブラリとしても、Flight PHP Framework と一緒に使用できます。完全にお好みに合わせて選択してください。

### スタンドアロン
単純に PDO 接続をコンストラクタに渡すだけです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です、実際には実在するデータベース接続を使用するはずです

$User = new User($pdo_connection);
```

### Flight PHP Framework
Flight PHP Framework を使用している場合、ActiveRecord クラスをサービスとして登録できます（しかし、実際には必要ありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラや関数などで次のように使用できます。

Flight::user()->find(1);
```

## API リファレンス
### CRUD 関数

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて、現在のオブジェクトに割り当てます。何らかの `$id` を渡すと、その値で主キーを検索します。何も渡されない場合は、テーブル内の最初のレコードだけを検索します。

追加で、テーブルをクエリするための他のヘルパーメソッドを渡すこともできます。

```php
// 事前にいくつかの条件でレコードを検索
$user->notNull('password')->orderBy('id DESC')->find();

// 特定の ID でレコードを検索
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを検索します。

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'デモ';
$user->password = md5('デモ');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベース内で更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Dirty" データとは、レコード内で変更されたデータのことを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も "dirty" ではありません。

$user->email = 'test@example.com'; // 今、email は変更されたため "dirty" と見なされます。
$user->update();
// 今やデータベースに反映されたので、"dirty" データはもうありません。

$user->password = password_hash()'newpassword'); // これは今 "dirty" です
$user->dirty(); // 何も渡さない場合は、すべての "dirty" エントリをクリアします。
$user->update(); // "dirty" なデータがキャプチャされていないため、何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password が両方更新されます。
```

### SQL クエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

テーブル内のいくつかの列だけを選択できます（多くの列を持つ非常に幅広いテーブルではパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

別のテーブルも選択できます！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルにも結合できます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムの where 引数を設定できます（この where 文にはパラメータを設定できません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();` のようにしたくなるかもしれませんが、これは SQL インジェクション攻撃に対して脆弱です。これについて多くの記事がオンラインで見つかりますので、"sql injection attacks php" で Google 検索してみてください。このライブラリでこれを処理する適切な方法は、この `where()` メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();` のようにすることです。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

結果を特定の条件でグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

クエリの結果を特定の方法でソートします。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。2 番目の int が与えられると、SQL と同じようにオフセット、リミットが適用されます。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` を適用します。

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` を適用します。

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL` を適用します。

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL` を適用します。

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value` を適用します。

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value` を適用します。

```php
$user->lt('id', 1)->find();
```

#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value` を適用します。

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value` を適用します。

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` または `field NOT LIKE $value` を適用します。

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` または `field NOT IN($value)` を適用します。

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1` を適用します。

```php
$user->between('id', [1, 2])->find();
```

### 関連性
このライブラリを使用して、さまざまな種類の関係を設定できます。テーブル間での one->many および one->one の関係を設定できます。これにはクラスの事前に少しのセットアップが必要です。

`$relations` 配列を設定することは難しくありませんが、正しい構文を推測することが混乱する可能性があります。

```php
protected array $relations = [
	// キー名は何でもかまいません。ActiveRecord の名前がおそらくいいでしょう。例: user, contact, client
	'whatever_active_record' => [
		// 必須
		self::HAS_ONE, // これは関係の種類です

		// 必須
		'Some_Class', // これは "other" ActiveRecord クラスです

		// 必須
		'local_key', // 参照する join の local_key です。
		// ちなみに、これは "other" モデルの主キーにのみ参加します

		// オプション
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 実行しようとするカスタムメソッド。何も欲しくない場合は [] です。

		// オプション
		'back_reference_name' // これはこの関係を自身に戻す場合の名前です Ex: $user->contact->user;
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

これで参照が設定されましたので、非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最新のユーザを見つける
$user->notNull('id')->orderBy('id desc')->find();

// 関係を使ってコンタクトを取得：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆もできます。
$contact = new Contact();

// コンタクトを 1 つ見つける
$contact->find();

// 関係を使ってユーザを取得：
echo $contact->user->name; // これがユーザー名です
```

すごく面白いですね？

### カスタムデータの設定
場合によっては、テンプレートに渡すのが簡単なカスタム計算など、レコードに独自のものをアタッチする場合があります。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` メソッドを使用して、カスタムデータをアタッチします。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

And then you simply reference it like a normal object property.

```php
echo $user->page_view_count;
```

### イベント

このライブラリについてのもう1つの素晴らしい機能は、イベントについてです。イベントは、特定のメソッドを呼び出したときに特定のタイミングでトリガーされます。自動データの設定などに非常に役立ちます。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

これは、デフォルト接続などを設定する必要がある場合に本当に便利です。

```php
// index.php または bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // & reference を忘れないでください
		// 接続を自動的に設定することができます
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// このようにしてテーブル名を設定することもできます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

これは、常にクエリを変更する必要がある場合以外は有用でしょう。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// それが好きならば常に id >= 0 を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

これは、常に特定のロジックを実行する必要がある場合に便利です。何かを復号化する必要がありますか？カスタムのカウントクエリを毎回実行する必要がありますか（パフォーマンスは良くありませんが、やりたいことでしょう）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化する
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// クエリのようなカスタムデータを保存する可能性もあります
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

これは、常にクエリを変更する必要がある場合以外は有用でしょう。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// それが好きならば常に id >= 0 を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()` と同様ですが、すべてのレコードにそれを適用できます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// `afterFind()` のようなクールなことをします
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

常にいくつかのデフォルト値を設定する必要がある場合に本当に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// サウンドなデフォルトが設定されます
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

挿入後にデータを変更するためのユースケースがあるかもしれません？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなた自身が行う
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または他に何か....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

常に更新時にデフォルト値を設定する必要がある場合に本当に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// サウンドなデフォルトが設定されます
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更するためのユースケースがあるかもしれません？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなた自身が行う
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または他に何か....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

挿入または更新が行われたときの両方でイベントが必要な場合に役立ちます。長い説明は省きますが、何をするかはわかるはずです。

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

ここで何をしたいのかよくわかりませんが、ここでは評価しません！やってみてください！

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

## 貢献

どうぞ。

### セットアップ

貢献する際は、`composer test-coverage` を実行してテストカバレッジを維持することを確認してください（これは真のユニットテストカバレッジではなく、統合テストに近いものです）。

また、`composer beautify` と `composer phpcs` を実行して、リントエラーを修正してください。

## ライセンス

MIT