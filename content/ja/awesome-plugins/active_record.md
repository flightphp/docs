# FlightPHP アクティブレコード

アクティブレコードはデータベースエンティティをPHPオブジェクトにマッピングするものです。単純に言えば、データベースに`users`テーブルがある場合、そのテーブルの行を`User`クラスおよび`$user`オブジェクトに「変換」できます。[基本例](#basic-example)を参照してください。

## 基本的な例

次のテーブルがあると仮定してみましょう:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

これに対応する新しいクラスを設定できます:

```php
/**
 * アクティブレコードクラスは通常単数形です
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
		// この方法で設定できます
		parent::__construct($database_connection, 'users');
		// またはこの方法でも設定できます
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

さて、魔法が起こるのを見ましょう！

```php
// sqliteの場合
$database_connection = new PDO('sqlite:test.db'); // これは例です。実際にはリアルなデータベース接続を使用します

// mysqlの場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// またはmysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// またはオブジェクトベースでないmysqli
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// または $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ここでは$user->save()を使うことはできません！

echo $user->id; // 2
```

新しいユーザーを追加するのはこれだけ簡単でした！では、データベースにユーザー行があるとして、それを取り出す方法はどうすればよいですか？

```php
$user->find(1); // データベースからid = 1を見つけて返す。
echo $user->name; // 'Bobby Tables'
```

すべてのユーザーを見つけたい場合は？

```php
$users = $user->findAll();
```

特定の条件で見つけたい場合は？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

これがどれほど楽しいか見てみましょう？インストールして始めましょう！

## インストール

Composerを使用して簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使用法

これはスタンドアロンのライブラリとして使用するか、Flight PHPフレームワークと組み合わせて使用できます。完全にあなた次第です。

### スタンドアロン
単純にコンストラクタにPDO接続を渡してください。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です。実際にはリアルなデータベース接続を使用します

$User = new User($pdo_connection);
```

### Flight PHPフレームワーク
Flight PHPフレームワークを使用している場合、ActiveRecordクラスをサービスとして登録できます（しかし、正直言って必要はありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 続いて、コントローラや関数などで次のように使用できます。

Flight::user()->find(1);
```

## APIリファレンス
### CRUD関数

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて現在のオブジェクトに割り当てます。 `$id`を指定した場合、その値に対応する主キーでルックアップを実行します。何も渡さない場合、テーブル内の最初のレコードを取得します。

また、他のヘルパーメソッドを使ってテーブルをクエリできます。

```php
// 前もっていくつかの条件でレコードを見つける
$user->notNull('password')->orderBy('id DESC')->find();

// 特定のIDでレコードを見つける
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを見つけます。

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースで更新します。

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

検索前に検索を実行して複数のレコードを削除することもできます。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Dirty"データとは、レコードで変更されたデータのことです。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も "dirty" ではありません。

$user->email = 'test@example.com'; // emailが変更されたので "dirty" と見なされます。
$user->update();
// 今は、データベースに更新され、dirtyなデータがないことになります。

$user->password = password_hash()'newpassword'); // これは今dirtyです
$user->dirty(); // 何も渡さないと、dirtyエントリーがすべてクリアされます。
$user->update(); // 何もdirtyとしてキャプチャされなかったため、更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名前とパスワードが両方更新されます。
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。これはループタイプの挙動で使用するのに非常に役立ちます。
`true` を渡すと、現在のオブジェクトを検索するために使用されたクエリデータもリセットされます（デフォルト動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンなスレートで開始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### SQLクエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

テーブルの列の一部だけを選択することができます（列が多くて幅が広いテーブルではパフォーマンスが向上します）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

他のテーブルも選択できます！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルに結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムのwhere引数を設定できます（このwhereステートメントではパラメータを設定できません）

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`のようなことをすることに誘惑されるかもしれません。これはSQLインジェクション攻撃に対して脆弱です。オンラインで多くの記事があるので、"sql injection attacks php"で検索してください。このライブラリでこれを処理する正しい方法は、この`where()`メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();`のようにすることです。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

特定の条件で結果をグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

特定の方法で返されたクエリを並べ替えます。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。第二引数にintが指定された場合、オフセット、制限があり、SQLと同じように動作します。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

「field = $value」で検索します。

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

「field <> $value」で検索します。

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

「field IS NULL」となる条件で検索します。

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

「field IS NOT NULL」となる条件で検索します。

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

「field > $value」となる条件で検索します。

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

「field < $value」となる条件で検索します。

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

「field >= $value」となる条件で検索します。

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

「field <= $value」となる条件で検索します。

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

「field LIKE $value」または「field NOT LIKE $value」となる条件で検索します。

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

「field IN($value)」または「field NOT IN($value)」となる条件で検索します。

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

「field BETWEEN $value AND $value1」となる条件で検索します。

```php
$user->between('id', [1, 2])->find();
```

### リレーションシップ
このライブラリを使用してさまざまな種類のリレーションシップを設定できます。テーブル間の1対多および1対1のリレーションシップを設定できます。事前にクラスで`$relations`配列を設定する必要がありますが、正しい構文を推測するのはやや混乱するかもしれません。

```php
protected array $relations = [
	// キーの名前は何でもよいです。ActiveRecordの名前がおそらく適しています。例: user, contact, client
	'whatever_active_record' => [
		// 必須
		self::HAS_ONE, // このリレーションシップのタイプ

		// 必須
		'Some_Class', // これが参照するもう一つのActiveRecordクラスです

		// 必須
		'local_key', // ジョインを参照するローカルキーです。
		// ちなみに、これは "other" モデルの主キーにも結合されます

		// カスタムメソッドを実行する場合は、[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ]のように指定します（カスタムメソッドが不要な場合は[]）
		
		// オプション
		'back_reference_name' // このリレーションシップを自身に逆参照する場合（例: $user->contact->user）
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

これで参照が設定されたので、非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最新のユーザーを見つける
$user->notNull('id')->orderBy('id desc')->find();

// リレーションを使って連絡先を取得する
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆に行くこともできます。
$contact = new Contact();

// 1つの連絡先を見つける
$contact->find();

// リレーションを使ってユーザーを取得する
echo $contact->user->name; // これはユーザー名です
```