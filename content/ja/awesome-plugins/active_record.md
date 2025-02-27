# Flight アクティブレコード 

アクティブレコードとは、データベースのエンティティをPHPオブジェクトにマッピングすることです。簡単に言えば、データベースにユーザーテーブルがある場合、そのテーブルの行を`User`クラスと`$user`オブジェクトに「翻訳」することができます。 [基本例](#basic-example)を参照してください。

GitHubのリポジトリについては[こちら](https://github.com/flightphp/active-record)をクリックしてください。

## 基本例

次のテーブルがあると仮定しましょう：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

このテーブルを表す新しいクラスを設定できます：

```php
/**
 * アクティブレコードクラスは通常単数です
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
		// またはこのように
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

さあ、魔法が起こるのを見てみましょう！

```php
// sqliteの場合
$database_connection = new PDO('sqlite:test.db'); // これは単なる例ですので、実際のデータベース接続を使用することになります

// mysqlの場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// またはmysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// または非オブジェクト型のmysqli作成
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
// ここで $user->save() を使用することはできません。更新として考えられてしまいます！

echo $user->id; // 2
```

新しいユーザーを追加するのはとても簡単でした！データベースにユーザーロウがあるので、どうやって取り出すことができますか？

```php
$user->find(1); // データベースで id = 1 を探して返します。
echo $user->name; // 'Bobby Tables'
```

すべてのユーザーを見つけたい場合はどうしますか？

```php
$users = $user->findAll();
```

特定の条件で検索する場合はどうですか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

どうですか？楽しいでしょう？インストールして始めましょう！

## インストール

Composerで簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使用法

これはスタンドアロンライブラリとして使用することも、Flight PHPフレームワークと一緒に使用することもできます。完全にあなたの好みです。

### スタンドアロン
コンストラクタにPDO接続を渡すことを確認してください。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは単なる例で、実際にはデータベース接続を使用することになります

$User = new User($pdo_connection);
```

> 常にコンストラクタでデータベース接続を設定したくないですか？他のアイデアについては[データベース接続管理](#database-connection-management)を参照してください！

### Flightでメソッドとして登録
Flight PHPフレームワークを使用している場合、ActiveRecordクラスをサービスとして登録できますが、本当にそうする必要はありません。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// 次に、コントローラや関数などでこのように使用できます。

Flight::user()->find(1);
```

## `runway` メソッド

[runway](/awesome-plugins/runway) は、Flight用のCLIツールで、このライブラリ用のカスタムコマンドがあります。

```bash
# 使用法
php runway make:record database_table_name [class_name]

# 例
php runway make:record users
```

これにより、`app/records/`ディレクトリに`UserRecord.php`という新しいクラスが作成され、次の内容が含まれます：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ユーザーテーブル用のアクティブレコードクラスです。
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations モデルのリレーションシップを設定します
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * コンストラクタ
     * @param mixed $databaseConnection データベースへの接続
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD関数

#### `find($id = null) : boolean|ActiveRecord`

1つのレコードを見つけて、現在のオブジェクトに割り当てます。何らかの`$id`を渡すと、その値でプライマリキーを検索します。何も渡さない場合は、テーブル内の最初のレコードを見つけます。

他のヘルパーメソッドを渡してテーブルをクエリすることもできます。

```php
// あらかじめ条件を付けてレコードを検索
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

#### `isHydrated(): boolean` (v0.4.0)

現在のレコードが水和（データベースから取得）されている場合は`true`を返します。

```php
$user->find(1);
// データが見つかった場合...
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

##### テキストベースのプライマリキー

テキストベースのプライマリキー（例えばUUID）がある場合、挿入前に次の2つの方法でプライマリキーの値を設定できます。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // または $user->save();
```

または、イベントを通じてプライマリキーを自動的に生成させることもできます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 上記の配列の代わりにこのようにプライマリキーを設定することもできます。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // またはユニークIDを生成する必要がある他の方法
	}
}
```

挿入前にプライマリキーを設定しないと、`rowid`に設定され、データベースが自動生成しますが、そのフィールドがテーブルに存在しない場合、持続性がなくなります。したがって、これを定期的に処理するためにイベントを使うことをお勧めします。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードにIDがある場合は更新し、そうでない場合は挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注:** クラスでリレーションシップが定義されている場合、それらの関係も再帰的に保存されます（v0.4.0以降）。

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

事前に検索を実行して複数のレコードを削除することもできます。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

ダーティデータとは、レコード内で変更されたデータを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「ダーティ」ではありません。

$user->email = 'test@example.com'; // これは変更されているので「ダーティ」と見なされます。
$user->update();
// 更新され、データベースに永続化されたので、ダーティデータはありません。

$user->password = password_hash('newpassword'); // これはダーティです。
$user->dirty(); // 引数を何も渡さないと、すべてのダーティエントリがクリアされます。
$user->update(); // 何も捕捉されていないため、何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名前とパスワードの両方が更新されます。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

これは`dirty()`メソッドの別名です。何をしているのかがより明確です。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // 名前とパスワードの両方が更新されます。
```

#### `isDirty(): boolean` (v0.4.0)

現在のレコードが変更された場合、`true`を返します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。これはループ型の動作で使用するのに非常に便利です。
`true`を渡すと、現在のオブジェクトを見つけるために使用されたクエリデータもリセットされます（デフォルトの動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンスレートで開始します
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, または`save()`メソッドを実行した後、生成されたSQLを取得してデバッグ目的で使用できます。

## SQLクエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

必要に応じてテーブル内のいくつかのカラムだけを選択できます（非常に広いテーブルではパフォーマンスが向上します）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

実際には別のテーブルも選択できます！なぜそれをしないのですか？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルを結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムのwhere引数を設定できます（このwhere文にパラメータを設定することはできません）

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティノート** - 何かこうしたくなるかもしれません `$user->where("id = '{$id}' AND name = '{$name}'")->find();`。絶対にこれを実行しないでください！これはSQLインジェクション攻撃の対象です。この件に関する多くのオンライン記事がありますので、"sql injection attacks php"とGoogle検索すれば、多くの記事が見つかります。このライブラリを使用する際は、`where()`メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();`のようにするのが正しい方法です。絶対にこのようにする必要がある場合、`PDO`ライブラリには`$pdo->quote($var)`があり、これがあなたのためにエスケープします。 `quote()`を使用した後でなければ、`where()`文で使用することはできません。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

特定の条件で結果をグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

返されるクエリを特定の方法でソートします。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。二つ目のintが指定された場合、SQLと同様にオフセットを制限します。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

`field = $value` の場合

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

`field <> $value` の場合

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

`field IS NULL` の場合

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

`field IS NOT NULL` の場合

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

`field > $value` の場合

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

`field < $value` の場合

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

`field >= $value` の場合

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

`field <= $value` の場合

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

`field LIKE $value` または `field NOT LIKE $value` の場合

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

`field IN($value)` または `field NOT IN($value)` の場合

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

`field BETWEEN $value AND $value1` の場合

```php
$user->between('id', [1, 2])->find();
```

### OR条件

条件をOR文でラップすることも可能です。これは、`startWrap()`および`endWrap()`メソッドを使用するか、フィールドと値の後に条件の3番目のパラメータを指定することで行います。

```php
// メソッド 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// これは `id = 1 AND (name = 'demo' OR name = 'test')` と評価されます

// メソッド 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// これは `id = 1 OR name = 'demo'` と評価されます
```

## リレーションシップ
このライブラリを使用して、いくつかの種類のリレーションシップを設定できます。一対多および一対一のリレーションシップをテーブル間に設定できます。これには、事前にクラス内で追加のセットアップが必要です。

`$relations`配列の設定は難しくはありませんが、正しい構文を推測することは混乱を招くことがあります。

```php
protected array $relations = [
	// キーの名前は好きなように設定できます。アクティブレコードの名前はおそらく良いでしょう。例：user、contact、client
	'user' => [
		// 必須
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // これはリレーションのタイプです

		// 必須
		'Some_Class', // これは参照する「他の」アクティブレコードクラスです

		// 必須
		// リレーションシップの種類に応じて
		// self::HAS_ONE = 結合を参照する外部キー
		// self::HAS_MANY = 結合を参照する外部キー
		// self::BELONGS_TO = 結合を参照するローカルキー
		'local_or_foreign_key',
		// 他のモデルのプライマリキーにのみ結合しますので、ご注意ください。

		// オプショナル
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // リレーションを結合する際に希望する追加条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// オプショナル
		'back_reference_name' // これは、このリレーションシップを自身に戻して参照したい場合の名前です。例：$user->contact->user;
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

これでリファレンスのセットアップができたので、非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最も最近のユーザーを見つける。
$user->notNull('id')->orderBy('id desc')->find();

// リレーションを使用して連絡先を取得：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または反対側に行くことができます。
$contact = new Contact();

// 1つの連絡先を見つけます
$contact->find();

// リレーションを使用してユーザーを取得：
echo $contact->user->name; // これはユーザー名です
```

すごいですね！

## カスタムデータの設定
場合によっては、アクティブレコードにカスタム計算など、オブジェクトに直接接続したいユニークなものを添付する必要があるかもしれません。

#### `setCustomData(string $field, mixed $value)`
カスタムデータは、`setCustomData()`メソッドを使用して添付します。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

そして、通常のオブジェクトプロパティのように参照します。

```php
echo $user->page_view_count;
```

## イベント

このライブラリのもう一つの素晴らしい機能は、イベントに関するものです。イベントは、呼び出す特定のメソッドに基づいて特定のタイミングでトリガーされます。自動的にデータをセットアップするのに非常に役立ちます。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

これは、デフォルトの接続などを設定するのに非常に便利です。

```php
// index.phpまたはbootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // &参照を忘れずに
		// このように接続を自動的に設定できます
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名を設定することもできます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

おそらく、毎回クエリ操作が必要な場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 常に id >= 0 を実行します。
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

おそらく、レコードが取得されるたびに何らかのロジックを実行する必要がある場合に役立ちます。何かを復号化する必要がありますか？毎回カスタムカウントクエリを実行する必要がありますか（パフォーマンスは良くありませんが、いかがでしょうか）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化する
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// 何かカスタムのストレージを行うかもしれません
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

おそらく、毎回クエリ操作が必要な場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 常に id >= 0 を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()` に似ていますが、すべてのレコードに対して行えます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()のような何かを行います
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

毎回いくつかのデフォルト値を設定するのに非常に便利です。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 有効なデフォルトを設定します
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

挿入後にデータを変更する必要があるユースケースがあるかもしれません。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 自分の好きなように
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何か...
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

毎回更新時にデフォルト値を設定するのに非常に便利です。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeUpdate(self $self) {
		// 有効なデフォルトを設定します
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更する必要があるユースケースがあるかもしれません。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterUpdate(self $self) {
		// 自分の好きなように
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何か...
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

これは、挿入または更新が行われるときに、イベントを発生させたい場合に役立ちます。長い説明は省きますが、何を意味するのかを推測できますよね。

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

ここで何をするかは不明ですが、判断はありません！あなたの好きなようにやってください！

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

このライブラリを使用する際、データベース接続をいくつかの異なる方法で設定できます。コンストラクタ内で接続を設定することも、`$config['connection']`変数を介して設定することも、`setDatabaseConnection()`を使用して設定することもできます（v0.4.1）。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例として
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

アクティブレコードを呼び出すたびに、`$database_connection`を設定するのを避けたい場合は、その方法がいくつかあります！

```php
// index.phpまたはbootstrap.php
// Flightで登録済みのクラスとしてこれを設定します
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// これで、引数は不要です！
$user = new User();
```

> **注:** 単体テストを計画している場合、この方法で行うと単体テストにいくつかの課題が生じる可能性がありますが、全体的には`setDatabaseConnection()`や`$config['connection']`で接続を注入できるため、あまり問題ではありません。

例えば、長時間実行されるCLIスクリプトを実行している場合に接続をリフレッシュする必要がある場合、`$your_record->setDatabaseConnection($pdo_connection)`で接続を再設定することができます。

## 貢献

ぜひご協力ください。 :D

### セットアップ

貢献する際は、`composer test-coverage`を実行して100%のテストカバレッジを維持してください（これは真の単体テストカバレッジではなく、むしろ統合テストのカバレッジです）。

また、`composer beautify`および`composer phpcs`を実行して、すべてのリンティングエラーを修正することを確認してください。

## ライセンス

MIT