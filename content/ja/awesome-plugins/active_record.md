# FlightPHP アクティブレコード

アクティブレコードはデータベースエンティティをPHPオブジェクトにマッピングするものです。単純に言うと、データベースに`users`テーブルがある場合、そのテーブル内の1行を`User`クラスと`$user`オブジェクトに「翻訳」できます。[基本例](#基本例)をご覧ください。

## 基本例

以下のテーブルがあると仮定します:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

このテーブルを表す新しいクラスを設定できます:

```php
/**
 * アクティブレコードクラスは通常単数形です
 * 
 * ここにテーブルのプロパティをコメントとして追加することが強くお勧めされます
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
		// またはこうすることもできます
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

さあ、魔法の出番です！

```php
// SQLiteの場合
$database_connection = new PDO('sqlite:test.db'); // これは例です。実際にはリアルなデータベース接続を使用するはずです

// MySQLの場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'ユーザー名', 'パスワード');

// またはMySQLi
$database_connection = new mysqli('localhost', 'ユーザー名', 'パスワード', 'test_db');
// またはオブジェクトベースでないMySQLi
$database_connection = mysqli_connect('localhost', 'ユーザー名', 'パスワード', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// または $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ここでは $user->save()を使用することはできません！それは更新と思われるからです！

echo $user->id; // 2
```

新しいユーザーを追加するのはこれだけの簡単さです！では、データベースにユーザー行がある場合、どのようにそれを取得しますか？

```php
$user->find(1); // データベース内のid = 1を見つけて返します。
echo $user->name; // 'Bobby Tables'
```

そして、すべてのユーザーを見つけたい場合はどうするのでしょうか？

```php
$users = $user->findAll();
```

ある条件で検索する場合はどうでしょうか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

楽しいですね！インストールして始めましょう！

## インストール

単純にComposerでインストールしてください。

```php
composer require flightphp/active-record 
```

## 使用法

これはスタンドアロン・ライブラリとして使用するか、Flight PHPフレームワークと一緒に使用できます。完全にあなた次第です。

### スタンドアロン
コンストラクタにPDO接続を渡すだけでOKです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です。実際にはリアルなデータベース接続を使用するはずです

$User = new User($pdo_connection);
```

### Flight PHPフレームワーク
Flight PHPフレームワークを使用している場合、ActiveRecordクラスをサービスとして登録できます（しかし、本当に必要はありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラや関数などで以下のように使用できます。

Flight::user()->find(1);
```

## APIリファレンス
### CRUD関数

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて現在のオブジェクトに割り当てます。`$id`を指定して主キーでルックアップを行います。何も渡さない場合、テーブル内の最初のレコードを検索します。

さらに、他のヘルパーメソッドを使ってテーブルをクエリすることもできます。

```php
// 事前にいくつかの条件でレコードを検索する
$user->notNull('password')->orderBy('id DESC')->find();

// 特定のIDでレコードを検索
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

#### `dirty(array  $dirty = []): ActiveRecord`

「dirtyデータ」とは、レコードで変更されたデータのことです。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「dirty」ではありません。

$user->email = 'test@example.com'; // 今回の電子メールが変更されたので「dirty」とみなされます。
$user->update();
// データが更新され、データベースに格納されたため、「dirty」なデータはありません。

$user->password = password_hash()'newpassword'); // これが「dirty」です
$user->dirty(); // 何も渡さないと、すべての「dirty」エントリがクリアされます。
$user->update(); // 何も「dirty」にキャプチャされないので何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名前とパスワードの両方が更新されます。
```

### SQLクエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

特定のテーブル内のいくつかの列のみを選択することができます（多くの列がある広いテーブルでパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

他のテーブルを選択することもできます！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の他のテーブルに結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムのwhere引数を設定できます（このwhereステートメントにはパラメータを設定できません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`のようなことをしようとするかもしれません。これはSQLインジェクション攻撃に対して脆弱です。多くのオンライン記事がありますので、"sql injection attacks php"をGoogleで検索すると多くの記事が見つかります。このライブラリでは、この`where()`メソッドの代わりに`$user->eq('id', $id)->eq('name', $name)->find();`のようにすることが適切です。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

特定の条件で結果をグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

返されるクエリを特定の方法で並べ替えます。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。2番目の整数が与えられると、オフセット、limitはSQLと同様になります。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`の条件です。

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`の条件です。

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`の条件です。

```php
$user->isNull('id')->find();
```

#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`の条件です。

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`の条件です。

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`の条件です。

```php
$user->lt('id', 1)->find();
```

#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`の条件です。

```php
$user->ge('id', 1)->find();
```

#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`の条件です。

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value`または`field NOT LIKE $value`の条件です。

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)`または`field NOT IN($value)`の条件です。

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1`の条件です。

```php
$user->between('id', [1, 2])->find();
```

### 関係性
このライブラリを使用して、複数の種類の関係を設定できます。テーブル間の1->多および1->1の関係を設定できます。これにはクラスの事前設定が少し必要です。

`$relations`配列を設定することは難しくありませんが、正しい構文を推測することが混乱するかもしれません。

```php
protected array $relations = [
	// キーの名前を任意のものにすることができます。ActiveRecordの名前が良いでしょう。例: user, contact, client
	'whatever_active_record' => [
		// 必須
		self::HAS_ONE, // これが関連のタイプです

		// 必須
		'Some_Class', // これが「他の」ActiveRecordクラスに参照されるものです

		// 必須
		'local_key', // 結合を参照するローカルキーです。
		// ちなみに、これは他のモデルの主キーにのみ結合されます

		// オプション
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // 実行したいカスタムメソッド。。何も求めない場合は[]。

		// オプション
		'back_reference_name' // これは、この関係を自分自身に後戻りさせる場合です Ex: $user->contact->user;
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

// 最新のユーザーを見つけます。
$user->notNull('id')->orderBy('id desc')->find();

// リレーションを使用して連絡先を取得します:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆向きにします。
$contact = new Contact();

// 1つの連絡先を見つける
$contact->find();

// リレーションを使用してユーザーを取得します。
echo $contact->user->name; // これはユーザー名です
```

かなりクールですね？

### カスタムデータの設定
テンプレートに渡すのが簡単なカスタム計算などのActiveRecordに独自のデータを添付する場合があります。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()`メソッドを使用してカスタムデータを添付します。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

その後、通常のオブジェクトプロパティのように参照します。

```php
echo $user->page_view_count;
```

### イベント

このライブラリのもう1つの素晴らしい機能がイ