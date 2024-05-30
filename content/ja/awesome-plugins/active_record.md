# 飛行アクティブレコード

アクティブレコードはデータベースエンティティをPHPオブジェクトにマッピングするものです。単純に言うと、データベースに`users`テーブルがある場合、そのテーブルの行を`User`クラスと`$user`オブジェクトに「変換」できます。[基本例](#基本例)を参照してください。

## 基本例

以下のテーブルがあると仮定しましょう：

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
		// この方法で設定できます
		parent::__construct($database_connection, 'users');
		// またはこの方法で
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

魔法が起きます！

```php
// sqliteの場合
$database_connection = new PDO('sqlite:test.db'); // これは例です、通常は実際のデータベース接続を使用します

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
// $user->save()はここでは使用できません！

echo $user->id; // 2
```

新しいユーザーを追加するのは簡単でした！データベースにユーザー行があるため、それを取得する方法は？

```php
$user->find(1); // データベースでid = 1を検索して返します。
echo $user->name; // 'Bobby Tables'
```

そして、すべてのユーザーを見つけたい場合はどうすればいいですか？

```php
$users = $user->findAll();
```

特定の条件で探したい場合はどうすればよいですか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

楽しいですよね？インストールして開始しましょう！

## インストール

単にComposerでインストールします

```php
composer require flightphp/active-record 
```

## 使用法

これはスタンドアロンライブラリとして使用するか、Flight PHPフレームワークと併用できます。完全にあなた次第です。

### スタンドアロン
コンストラクタにPDO接続を渡すだけです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です、通常は実際のデータベース接続を使用します

$User = new User($pdo_connection);
```

### Flight PHPフレームワーク
Flight PHPフレームワークを使用している場合、ActiveRecordクラスをサービスとして登録できます（しかし、必ずしも必要ではありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// コントローラー、関数などでこれを使用できます。

Flight::user()->find(1);
```

## CRUD関数

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて現在のオブジェクトに割り当てます。特定の`$id`を渡すと、その値の主キーで検索を実行します。何も渡さない場合は、テーブル内の最初のレコードを検索します。

追加のヘルパーメソッドを使用してテーブルをクエリできます。

```php
// 事前の条件でレコードを検索
$user->notNull('password')->orderBy('id DESC')->find();

// 特定のIDでレコードを検索
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを検索します。

```php
$user->findAll();
```

#### `isHydrated(): boolean`（v0.4.0）

現在のレコードが水分補給された（データベースから取得された）場合は`true`を返します。

```php
$user->find(1);
// データが含まれる場合...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### テキストベースの主キー

テキストベースの主キー（UUIDなど）を持つ場合、挿入前に主キー値を設定する方法が2つあります。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // または $user->save();
```

または、イベントを使用して主キーを自動生成することもできます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// これを使用して主キーを設定することもできます
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // または一意のIDが必要な方法で生成
	}
}
```

挿入前に主キーを設定しない場合、`rowid`に設定され、データベースが自動生成しますが、それは持続しません。これは、イベントを使用して自動的に処理することがお勧めされる理由です。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードにIDがある場合は更新し、それ以外の場合は挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意：** クラスで関係が定義されている場合、定義され、インスタンス化され、更新するデータがあれば、それらの関係も再帰的に保存されます（v0.4.0以降）。

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

また、事前に検索を実行して複数のレコードを削除することもできます。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

データベース内で変更されたデータのことを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では「dirty」データはありません。

$user->email = 'test@example.com'; // これにより「email」が「dirty」に変更された
$user->update();
// `dirty`に保持されているデータが更新、適用されました。

$user->password = password_hash()'newpassword'); // これが 「dirty」になります
$user->dirty(); // 何も渡さないと、すべての「dirty」エントリがクリアされます。
$user->update(); // 「dirty」にキャプチャされたデータがないため、更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 「name」と「password」の両方が更新されました。
```

#### `copyFrom(array $data): ActiveRecord`（v0.4.0）

これは`dirty()`メソッドのエイリアスです。何を行っているかを明確に示しています。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 「name」と「password」が更新されました。
```

#### `isDirty(): boolean`（v0.4.0）

現在のレコードが変更された場合は`true`を返します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。ループ型の動作で使用するのに非常に便利です。`true`を渡すと、現在のオブジェクトを検索するために使用されたクエリデータもリセットされます（デフォルトの動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // 詳細な初期状態に戻す
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string`（v0.4.1）

`find()`、`findAll()`、`insert()`、`update()`、または`save()`メソッドを実行した後、構築されたSQLを取得してデバッグ目的に使用できます。

## SQLクエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

必要に応じてテーブル内の一部の列だけを選択できます（多くの列がある場合にパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

別のテーブルを選択できます！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルに結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

独自のwhere引数を設定できます（このwhereステートメントではパラメータを設定できません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`のようにするかもしれませんが、これをやめてください！これはSQLインジェクション攻撃に対して脆弱です。多くのオンライン記事がありますので、Googleで「sql injection attacks php」と検索してください。このライブラリを使ってこれを行う適切な方法は、`$user->eq('id', $id)->eq('name', $name)->find();`のように、この`where()`メソッドではなく、より適切な方法で行うことです。絶対にこれを行う必要がある場合は、`PDO`ライブラリが`$pdo->quote($var)`を使用してエスケープする方法があることを覚えていてください。`quote()`を使用した後でないと、`where()`ステートメントで使用できません。

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

返されるレコードの数を制限します。2番目のintが与えられると、オフセット、リミットがSQLと同じようになります。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE条件
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

`field IN($value)`または`field NOT IN($value)`の関係を使用して、テーブル間でいくつかの種類の関係を設定できます。テーブル間の1対多および1対1の関係を設定できます。これには、事前にクラスで多少の追加のセットアップが必要です。

`$relations`配列を設定することは難しくありませんが、正しい構文を推測することが複雑になる場合があります。

```php
protected array $relations = [
	// キーは何でもよいですが、ActiveRecordの名前がよいでしょう。例: user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY、self::HAS_ONE、self::BELONGS_TO
		self::HAS_ONE, // これが関係のタイプです

		// 必須
		'Some_Class', // これが参照する「他の」ActiveRecordクラスです

		// 必須
		// 関係の種類に応じて
		// self::HAS_ONE = 結合を参照する外部キー
		// self::HAS_MANY = 結合を参照する外部キー
		// self::BELONGS_TO = 結合を参照するローカルキー
		'local_or_foreign_key',
		// FYI、これは他のモデルの主キーにも結合します

		// オプション
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' => 5 ], // リレーションを結合する際に追加の条件が必要な場合
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// オプション
		'back_reference_name' // これは、このリレーションを自分自身に戻す場合に使用します。例: $user->contact->user;
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

これでセットアップが整ったので、非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最新のユーザーを見つけます。
$user->notNull('id')->orderBy('id desc')->find();

// 関係を使用してお問い合わせを取得する：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆に移動できます。
$contact = new Contact();

// 1つのコンタクトを見つける
$contact->find();

// 関係を使用してユーザーを取得する：
echo $contact->user->name; // これはユーザー名です
```

かなりすごいですね？

## カスタムデータの設定
カスタムな計算など、ActiveRecordに独自の情報を添付する必要がある場合があります。これは、テンプレートに渡すためにオブジェクトに簡単に添付できるかもしれません。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()`メソッドを使用してカスタムデータを添付します。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

その後、通常のオブジェクトのプロパティとして参照します。

```php
echo $user->page_view_count;
```

## イベント

このライブラリのさらに素晴らしい機能の1つは、イベントについてです。イベントは、特定のメソッドを呼び出したときに特定の時間にトリガーされます。一定のデータを自動的に設定するのに非常に便利です。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

これはデフォルトの接続などを設定するのに非常に役立ちます。

```php
// index.phpまたはbootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 忘れずに&参照
		// 接続を自動的に設定する場合があります
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名も設定できます
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

おそらく、各回ごとにクエリの操作が必要な場合があります。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 常にid >= 0が実行されます
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

これは、通常、レコードが取得されるたびに実行する必要がある場合に役立ちます。何かを復号化する必要がありますか？カスタムのcountクエリを頻繁に実行する必要がありますか（効率的ではありませんが、それまで）。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化する
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 何か特別なことを記憶するかもしれませんか？？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

おそらく、各回ごとにクエリの操作が必要な場合があります。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 常にid >= 0が実行されます
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`に似ていますが、すべてのレコードにその処理を適用できます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFindのようなかっこいいことを行います
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

各回ごとにデフォルトの値を設定するのに非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかの素晴らしいデフォルト値を設定します
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

挿入後にデータを変更するためのケースがありますか？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなたはあなた自身についてです
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何か他のこと....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

各回ごとにデフォルトの値を設定するのに非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかの素晴らしいデフォルトを設定します
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更するためのケースがありますか？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなたはあなた自身についてです
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何か他のこと....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

これは、挿入または更新が発生したときの両方のイベントが必要な場合に役立ちます。説明を省略しますが、何が起こるかはおそらく予想できます。

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

ここで何をしたいかよくわかりませんが、何が起こるかはあなた次第です！やってください！

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

## データベース接続の管理

このライブラリを使用する際、データベース接続を数種類の方法で設定できます。コンストラクタで接続を設定するか、設定変数`$config['connection']`で設定するか、`setDatabaseConnection()`（v0.4.1）で設定するかです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

データベース接続をリフレッシュする必要がある場合、たとえば長時間実行されるCLIスクリプトを実行している場合は、`$your_record->setDatabaseConnection($pdo_connection)`で接続を再設定できます。