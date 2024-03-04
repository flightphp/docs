# フライトアクティブレコード

アクティブレコードはデータベースのエンティティをPHPオブジェクトにマッピングするものです。要するに、データベースに`users`テーブルがある場合、そのテーブル内の行を`User`クラスと`$user`オブジェクトに「変換」できます。[基本例](#基本例)を参照してください。

## 基本例

以下のテーブルがあるとします：

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
 * アクティブレコードクラスは通常単数形です
 * 
 * ここにテーブルのプロパティをコメントとして追加することを強くお勧めします
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

さあ、魔法が起こります！

```php
// sqliteの場合
$database_connection = new PDO('sqlite:test.db'); // これは例です。実際には実際のデータベース接続を使用するでしょう

// mysqlの場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// またはmysqliの場合
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// オブジェクトベースでないmysqliの場合
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
// ここでは $user->save() は使用できません！

echo $user->id; // 2
```

新しいユーザーを追加するのはこれほど簡単でした！今やデータベースにユーザー行があるので、それを取得するにはどうすればよいでしょうか？

```php
$user->find(1); // データベース内のid=1を検索して返します。
echo $user->name; // 'Bobby Tables'
```

すべてのユーザーを見つけたい場合はどうすればよいでしょうか？

```php
$users = $user->findAll();
```

特定の条件付きで行う方法は？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

これが楽しいかどうかわかりましたか？インストールして始めましょう！

## インストール

単純にComposerでインストールします

```php
composer require flightphp/active-record 
```

## 使用法

これは単独のライブラリとして使用するか、Flight PHPフレームワークと共に使用できます。完全にあなた次第です。

### 単独
単純にコンストラクタにPDO接続を渡すだけです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です。実際には実際のデータベース接続を使用するでしょう

$User = new User($pdo_connection);
```

### Flight PHPフレームワーク
Flight PHPフレームワークを使用している場合、ActiveRecordクラスをサービスとして登録できます（しかし、必ずしもそうする必要はありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラや関数などで次のように使用できます。

Flight::user()->find(1);
```

## CRUD機能

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて現在のオブジェクトに割り当てます。ある種の`$id`を渡すと、その値の主キーで検索を実行します。何も渡さない場合は、テーブル内の最初のレコードを見つけます。

この他にもテーブルをクエリするためのヘルパーメソッドを渡すこともできます。

```php
// 事前にいくつかの条件でレコードを見つける
$user->notNull('password')->orderBy('id DESC')->find();

// 特定のidでレコードを検索
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

現在のレコードをデータベースに更新します。

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

検索を実行してから複数のレコードを削除することもできます。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

ディープデータとは、レコード内で変更されたデータのことです。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「dirty」ではありません。

$user->email = 'test@example.com'; // 今メールは、「dirty」と見なされます。
$user->update();
// データがdirtyで更新され、データベースに保持されているため、今dirtyなデータはありません

$user->password = password_hash()'newpassword'); // これは今dirtyです
$user->dirty(); // 何も渡さないと、dirtyエントリーをすべてクリアします。
$user->update(); // 何もdirtyとしてキャプチャされなかったので何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nameとpasswordの両方が更新されます。
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。これはループ型の動作で使用するのに非常に適しています。
`true`を渡すと、現在のオブジェクトを見つけるために使用されたクエリデータもリセットされます（デフォルトの動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンな状態から開始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

## SQLクエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

表内の一部の列のみを選択することができます（多くの列がある場合にパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

別のテーブルを選択することもできます！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベースの別のテーブルに結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

一部のカスタムwhere引数を設定できます（このwhereステートメントではパラメーターを設定できません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意:**
`$user->where("id = '{$id}' AND name = '{$name}'")->find();`のようなことをしようとするかもしれませんが、これはSQLインジェクション攻撃に対して脆弱です。オンライン上にたくさんの記事があるので、「sql injection attacks php」で検索すると多くの記事が見つかります。このライブラリでは、この`where()`メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();`のような方法で処理するのが適切です。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

特定の条件で結果をグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

戻されるクエリを特定の方法で並べ替えます。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコード数を制限します。2番目の整数を渡すと、オフセット指定の制限になります。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value`条件です。

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value`条件です。

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL`条件です。

```php
$user->isNull('id')->find();
```

#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL`条件です。

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value`条件です。

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value`条件です。

```php
$user->lt('id', 1)->find();
```

#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value`条件です。

```php
$user->ge('id', 1)->find();
```

#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value`条件です。

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value`または`field NOT LIKE $value`条件です。

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)`または`field NOT IN($value)`条件です。

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1`条件です。

```php
$user->between('id', [1, 2])->find();
```

## リレーションシップ
このライブラリを使用して関係を構築することができます。テーブル間の1対多および1対1の関係を設定できます。これは少しの追加設定が必要です。

`$relations`配列を設定することは難しくありませんが、正しい構文を推測することが混乱することがあります。

```php
protected array $relations = [
	// キーの名前を好きなように設定できます。ActiveRecordの名前が良いでしょう。例: user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY、self::HAS_ONE、self::BELONGS_TO
		self::HAS_ONE, // これは関係のタイプです

		// 必須
		'Some_Class', // これは参照する "他の"ActiveRecordクラスです

		// 必須
		// 関係タイプによって異なります
		// self::HAS_ONE = ジョインを参照する外部キー
		// self::HAS_MANY = ジョインを参照する外部キー
		// self::BELONGS_TO = ジョインを参照するローカルキー
		'local_or_foreign_key',
		// FYI、これも「他の」モデルの主キーにのみジョイントします

		// オプション
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 関連付け時に追加の条件を設定する
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// オプション
		'back_reference_name' // これは自分自身に対してこの関係を逆参照する場合です 例: $user->contact->user;
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

これで参照が設定されたので、簡単に使用できるようになりました！

```php
$user = new User($pdo_connection);

// 最新のユーザーを見つけます。
$user->notNull('id')->orderBy('id desc')->find();

// 関連付けを使用して連絡先を取得します：
foreach($$user->contacts as $contact) {
	echo $contact->id;
}

// または逆も可能です。
$contact = new Contact();

// 1つの連絡先を見つける
$contact->find();

// 関連付けを使用してユーザーを取得します：
echo $contact->user->name; // これはユーザー名です
```

かなりクールですね？

## カスタムデータの設定
場合によっては、テンプレートに渡すオブジェクトにアタッチするようなユニークなものをアクティブレコードにアタッチする必要があるかもしれません。例えば、単純な計算をアタッチする方が簡単かもしれません。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()`メソッドでカスタムデータをアタッチします。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

そして、通常のオブジェクトプロパティのように参照します。

```php
echo $user->page_view_count;
```

## イベント

このライブラリのさらなる素晴らしい機能の1つはイベントについてです。特定のメソッドを呼び出したときに特定のタイミングでイベントがトリガーされます。自動的にデータの設定を行うのに非常に役立ちます。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

デフォルトの接続を設定する必要がある場合に役立ちます。

```php
// index.phpまたはbootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 忘れずに参照をしています
		// 自動的に接続を設定するには次のようにします
		$config['connection'] = Flight::db();
		// または
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名も設定できます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

クエリを実行するたびに有用でしょう。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 通常はid >= 0を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

おそらく、常にレコードを取得した後にロジックを実行する必要がある場合に有用でしょう。何かを復号化する必要がありますか？カスタムカウントクエリを実行する必要がありますか（パフォーマンスは悪いが、どうせですか）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化する
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 何かをカスタムで保存するか？？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

クエリを実行するたびに有用でしょう。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 通常はid >= 0を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`に似ていますが、すべてのレコードにそれを実行できます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()と同じように何かを行います。
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

毎回デフォルトの値を設定する必要がある場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかのデフォルト値を設定します
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

挿入後にデータを変更するユースケースがありますか？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 自由に行います
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何か....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

更新時にデフォルトの値を設定する必要がある場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかのデフォルト値を設定します
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更するユースケースがありますか？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 自由に行います
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何か....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

挿入または更新時にイベントが発生する必要がある場合に便利です。長い説明を省略しますが、何が起こるかは想像つくでしょう。

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

ここで何を行いたいかはわかりませんが、何も判断しません！やってみてください！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '彼は勇敢な兵士だった... :cry-face:';
	} 
}
```

## 貢献

ぜひどうぞ。

## セットアップ

貢献する場合は、`composer test-coverage`を実行して、テストカバレッジが100%を保持していることを確認してください（これは真のユニットテストカバレッジではなく、統合テストです）。

また、`composer beautify`と`composer phpcs`を実行して、すべてのリントエラーを修正してください。

## ライセンス

MIT