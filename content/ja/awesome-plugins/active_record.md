# フライト アクティブレコード

アクティブレコードは、データベースのエンティティを PHP オブジェクトにマッピングするものです。簡単に言うと、データベースに users テーブルがある場合、そのテーブルの行を `User` クラスと `$user` オブジェクトに「変換」することができます。[基本例](#basic-example)を参照してください。

## 基本例

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
 * アクティブレコードクラスは通常単数形です
 * 
 * テーブルのプロパティをここにコメントとして追加することを強くお勧めします
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

そこで、魔法が起こります！

```php
// sqlite の場合
$database_connection = new PDO('sqlite:test.db'); // これは例です。実際には実際のデータベース接続を使用するでしょう

// mysql の場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// または mysqli を使用する場合
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// またはオブジェクトを使用しない mysqli の場合
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'ボビー テーブルズ';
$user->password = password_hash('some cool password');
$user->insert();
// または $user->save();

echo $user->id; // 1

$user->name = 'ジョセフ マンマ';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ここでは $user->save() を使用することはできません！

echo $user->id; // 2
```

新しいユーザーを追加するのはとても簡単でしたね！では、データベースにユーザー行があるので、それを取り出すにはどうすればよいでしょうか？

```php
$user->find(1); // データベース内の id = 1 のレコードを見つけて返します。
echo $user->name; // 'ボビー テーブルズ'
```

すべてのユーザーを見つけたい場合はどうすればよいですか？

```php
$users = $user->findAll();
```

特定の条件で見つける場合はどうでしょうか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

楽しいでしょう？インストールして始めましょう！

## インストール

Composer で簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使用法

これは独立したライブラリとして使用するか、Flight PHP フレームワークと共に使用できます。完全にあなた次第です。

### 独立した使用法
コンストラクタに PDO 接続を渡すだけです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です。実際には実際のデータベース接続を使用します

$User = new User($pdo_connection);
```

### Flight PHP フレームワーク
Flight PHP フレームワークを使用している場合、ActiveRecord クラスをサービスとして登録できます（しかし、率直に言って必要はありません）。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 次に、コントローラーや関数などで次のように使用できます。

Flight::user()->find(1);
```

## CRUD 関数

#### `find($id = null) : boolean|ActiveRecord`

1 つのレコードを検索して現在のオブジェクトに割り当てます。何かの `$id` を渡すと、その値と主キーでの検索を実行します。何も渡さない場合、テーブルの最初のレコードを検索します。

その他のヘルパーメソッドを渡してテーブルをクエリできます。

```php
// 事前にいくつかの条件付きレコードを検索
$user->notNull('password')->orderBy('id DESC')->find();

// 特定の id でレコードを検索
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを検索します。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

現在のレコードがハイドレーション（データベースから取得）された場合、`true` を返します。

```php
$user->find(1);
// データが含まれるレコードが見つかると...
$user->isHydrated(); // true
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

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードに id がある場合は更新し、そうでない場合は挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'デモ';
$user->password = md5('デモ');
$user->save();
```

**注意:** クラスで関係が定義されている場合、定義されていて定義され、インスタンス化されてデータが更新されている場合、それらの関係を再帰的に保存します。 (v0.4.0 以降)

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

ダーティデータは、レコード内で変更されたデータを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「dirty」ではありません。

$user->email = 'test@example.com'; // これで email は「dirty」と見なされます
$user->update();
// 今はデータが dirty ではないため、更新と永続化は行われません

$user->password = password_hash()'newpassword'); // これは dirty です
$user->dirty(); // 何も渡さないと、すべての dirty エントリーがクリアされます。
$user->update(); // 何も dirty としてキャプチャされなかったため、何も更新されません

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password の両方が更新されます。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

`dirty()` メソッドの別名です。何をしているかを明示的に示します。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password の両方が更新されます。
```

#### `isDirty(): boolean` (v0.4.0)

現在のレコードが変更された場合、`true` を返します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。これは、ループ型の動作で使用するのに非常に適しています。
`true` を渡すと、現在のオブジェクトを検索するために使用されたクエリデータもリセットされます（デフォルトの動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンスレートから始める
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`、`findAll()`、`insert()`、`update()`、または `save()` メソッドを実行した後、構築された SQL を取得してデバッグ目的に使用できます。

## SQL クエリ関数
#### `select(string $field1 [, string $field2 ... ])`

テーブル内の列のいくつかだけを選択できます（多くの列を持つ非常に広いテーブルではよりパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

他のテーブルを選択することもできます！そういう事ができるのです！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の他のテーブルに結合することができます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムの where 引数を設定できます（この where ステートメントではパラメータを設定できません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティノート** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();` のようなようなことをしたくなるかもしれませんが、**この方法はしないでください**！これは SQL インジェクション攻撃に対して脆弱です。インジェクション攻撃に関する多くの記事がオンラインであり、"php sql injection attacks" などで検索すると、多くの記事が見つかります。このライブラリでこれを扱う正しい方法は、`where()` メソッドの代わりに `$user->eq('id', $id)->eq('name', $name)->find();` のような方法を使用することです。絶対にこのようなことを行う必要がある場合は、`PDO` ライブラリには `$pdo->quote($var)` があるため、それを使用してエスケープします。`quote()` を使用した後に `where()` ステートメント内でそれを使用できます。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

特定の条件で結果をグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

返されたクエリを特定の方法で並べ替えます。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。2 番目の整数が渡されると、オフセット、limit と同じく SQL。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE 条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` が成り立つ場合。

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` が成り立つ場合。

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL` が成り立つ場合。

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL` が成り立つ場合。

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value` が成り立つ場合。

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value` が成り立つ場合。

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value` が成り立つ場合。

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value` が成り立つ場合。

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` または `field NOT LIKE $value` が成り立つ場合。

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` または `field NOT IN($value)` が成り立つ場合。

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1` が成り立つ場合。

```php
$user->between('id', [1, 2])->find();
```

## リレーションシップ
このライブラリを使用してさまざまな種類のリレーションシップを設定できます。テーブル間の oneto-many および one-to-one リレーションシップを設定できます。これには、クラスの事前設定が少し必要ですが、構文を推測することが難しいかもしれません。

`$relations` 配列を設定することは難しくありませんが、正しい構文を推測することは混乱するかもしれません。

```php
protected array $relations = [
	// キーは何でもかまいません。ActiveRecord の名前がおそらく適しています。例: user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY、self::HAS_ONE、self::BELONGS_TO
		self::HAS_ONE, // これがリレーションシップの種類です

		// 必須
		'Some_Class', // これが参照する「他の」ActiveRecord クラスです

		// 必須
		// リレーションシップの種類に応じて
		// self::HAS_ONE = 結合を参照する外部キー
		// self::HAS_MANY = 結合を参照する外部キー
		// self::BELONGS_TO = 結合を示すローカルキー
		'local_or_foreign_key',
		// FYI、これも「他の」モデルの主キーにのみ結合されます

		// オプション
		// リレーションシップを結合する際に追加の条件を追加します
		// 例: $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' => 5 ],

		// オプション
		'back_reference_name' // これは、リレーションシップを逆参照して自分自身に戻りたい場合に使用します。例: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord {
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord {
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

これで参照がセットアップされたので、非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最新のユーザーを検索します。
$user->notNull('id')->orderBy('id desc')->find();

// リレーションを使用して連絡先を取得します：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆の方法を採用できます。
$contact = new Contact();

// 1 つの連絡先を取得します
$contact->find();

// リレーションを使用してユーザーを取得します：
echo $contact->user->name; // これがユーザー名です
```

かなりクールですね？

## カスタムデータの設定
場合によっては、テンプレートに渡すのが簡単なカスタム計算などが記録にアタッチする必要がある場合があります。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` メソッドを使ってカスタムデータをアタッチします。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

そして、通常のオブジェクトプロパティと同様に参照します。

```php
echo $user->page_view_count;
```

## イベント

このライブラリのもう 1 つの素晴らしい機能はイベントについてです。イベントは、特定のメソッドを呼び出すと特定のタイミングでトリガーされます。データを自動的に設定するのに非常に役立ちます。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

これは、デフォルトの接続などを設定する必要がある場合に非常に役立ちます。

```php
// index.php または bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // $config に & 参照を忘れないでください
		// 自動的に接続を設定する場合
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名を設定できます
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

クエリ manipulation が必要な場合にのみ役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 常に id >= 0 を実行する
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

これは、通常、レコードが取得されるたびに特定のロジックを実行する必要がある場合により役立ちます。何かを複合する必要があるでしょうか？ユーザーに計算件数のクエリを毎回実行する必要がありますか（パフォーマンス的には良くありませんが、そうとわります）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// ソルトを復号化している
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// おそらくクエリのようなものをカスタムデータに保存中ですか？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

クエリ manipulation が必要な場合にのみ役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 常に id >= 0 を実行する
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()` と似ていますが、すべてのレコードにそれを適用できます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind() のようなことを行います
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

通常、何らかのデフォルト値を設定する場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかのデフォルト値を設定する
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
		// します。
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// またはその他....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

更新時にデフォルト値を設定する必要がある場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかのデフォルト値を設定する
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
		// します。
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// またはその他....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

挿入または更新が発生する際にイベントが発生する場合に有用です。説明は省略しますが、何を行うかはお分かりかと思います。

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

ここで何をしたいかはわかりませんが、ここでは判断はしません！やりたいことを遂行してください！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo '彼は勇敢な兵士でした... :cry-face:';
	} 
}
```

## データベース接続管理

このライブラリを使用する際、データベース接続をいくつかの異なる方法で設定できます。接続をコンストラクタで設定するか、config 変数 `$config['connection']` を使用するか、`setDatabaseConnection()`（v0.4.1）を使用できます。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

例えば長時間実行される CLI スクリプトを実行していて、定期的に接続を更新する必要がある場合は、`$your_record->setDatabaseConnection($pdo_connection)` で接続を再設定できます。