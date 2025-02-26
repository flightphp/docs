# Flight アクティブレコード

アクティブレコードは、データベースエンティティをPHPオブジェクトにマッピングします。言い換えれば、データベースにユーザーテーブルがある場合、そのテーブルの行を`User`クラスと`$user`オブジェクトに「翻訳」することができます。 [基本的な例](#basic-example)を参照してください。

GitHubのリポジトリについては[こちら](https://github.com/flightphp/active-record)をクリックしてください。

## 基本的な例

次のテーブルがあるとしましょう：

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
		// このように設定できます
		parent::__construct($database_connection, 'users');
		// またはこのように
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

マジックを見てみましょう！

```php
// sqliteの場合
$database_connection = new PDO('sqlite:test.db'); // これは単なる例であり、実際のデータベース接続を使用するでしょう

// mysqlの場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// またはmysqliの場合
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// または非オブジェクトベースの作成によるmysqli
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'ボビー・テーブルズ';
$user->password = password_hash('クールなパスワード');
$user->insert();
// または$user->save();

echo $user->id; // 1

$user->name = 'ジョセフ・ママ';
$user->password = password_hash('もう一度クールなパスワード');
$user->insert();
// ここで$user->save()は使えません、更新とみなされてしまうので！

echo $user->id; // 2
```

新しいユーザーを追加するのはこれだけ簡単です！データベースにユーザー行があるので、どのようにそれを取得しますか？

```php
$user->find(1); // データベースでid = 1を見つけて返します。
echo $user->name; // 'ボビー・テーブルズ'
```

すべてのユーザーを見つけるにはどうしますか？

```php
$users = $user->findAll();
```

特定の条件でどうですか？

```php
$users = $user->like('name', '%ママ%')->findAll();
```

これがどれほど楽しいか見てみてください！インストールして始めましょう！

## インストール

Composerで単にインストールします。

```php
composer require flightphp/active-record 
```

## 使用法

これはスタンドアロンライブラリとしても、Flight PHPフレームワークと共に使用することもできます。完全にあなた次第です。

### スタンドアロン
コンストラクタにPDO接続を渡すことを確認してください。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは単なる例であり、実際のデータベース接続を使用するでしょう

$User = new User($pdo_connection);
```

> 常にコンストラクタでデータベース接続を設定したくないですか？他のアイデアについては[データベース接続管理](#database-connection-management)を参照してください！

### Flightのメソッドとして登録
Flight PHPフレームワークを使用している場合、アクティブレコードクラスをサービスとして登録できますが、実際には必須ではありません。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラや関数などでこのように使用できます。

Flight::user()->find(1);
```

## `runway`メソッド

[runway](/awesome-plugins/runway)は、FlightのCLIツールで、このライブラリ用のカスタムコマンドがあります。

```bash
# 使用法
php runway make:record database_table_name [class_name]

# 例
php runway make:record users
```

これにより、以下の内容で`app/records/`ディレクトリに新しいクラス`UserRecord.php`が作成されます。

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * usersテーブルのアクティブレコードクラスです。
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
     * @var array $relations モデルの関係を設定します
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

1つのレコードを見つけて現在のオブジェクトに割り当てます。`${id}`のようなものを渡すと、その値で主キーの検索を行います。何も渡さなければ、テーブル内の最初のレコードを単に見つけます。

他のヘルパーメソッドを渡してテーブルをクエリすることもできます。

```php
// 事前にいくつかの条件でレコードを見つける
$user->notNull('password')->orderBy('id DESC')->find();

// 特定のidでレコードを見つける
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを見つけます。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

現在のレコードが水和された（データベースから取得された）場合、`true`を返します。

```php
$user->find(1);
// データがあるレコードが見つかった場合...
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

##### テキストベースの主キー

テキストベースの主キー（UUIDなど）がある場合、挿入する前に以下のいずれかの方法で主キー値を設定できます。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'デモ';
$user->password = md5('デモ');
$user->insert(); // または$user->save();
```

または、イベントを介して主キーを自動的に生成させることもできます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 上の配列の代わりにこのように主キーを設定することもできます。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // またはユニークIDを生成するための方法
	}
}
```

挿入前に主キーを設定しない場合、`rowid`に設定され、データベースが自動的に生成しますが、そのフィールドはテーブルに存在しない場合があるため、永続化されません。このため、イベントを使用して自動的に処理することをお勧めします。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードにidがある場合は更新し、なければ挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'デモ';
$user->password = md5('デモ');
$user->save();
```

**注意:** クラスに関係が定義されている場合、それらの関係も定義され、インスタンス化され、更新するべき「汚れた」データがあると、再帰的に保存されます。（v0.4.0以降）

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

検索を実行することで、複数のレコードを削除することもできます。

```php
$user->like('name', 'ボブ%')->delete();
```

#### `dirty(array $dirty = []): ActiveRecord`

汚れたデータとは、レコード内で変更されたデータを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// 現時点では何も「汚れていません」。

$user->email = 'test@example.com'; // now email is considered "dirty" since it's changed.
$user->update();
// すべてのデータが更新され、データベースに永続化されたので、もはや汚れたデータはありません。

$user->password = password_hash('newpassword'); // これが汚れた状態です
$user->dirty(); // nothing will get cleared.
$user->update(); // nothing will update cause nothing was captured as dirty.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // both name and password are updated.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

これは`dirty()`メソッドの別名です。何をしているのか、少し明確です。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // both name and password are updated.
```

#### `isDirty(): boolean` (v0.4.0)

現在のレコードが変更された場合、`true`を返します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。これは、ループ型の動作に非常に便利です。`true`を渡すと、現在のオブジェクトを見つけるために使用されたクエリデータもリセットされます（デフォルト動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンスレートで開始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`, `findAll()`, `insert()`, `update()`, または `save()`メソッドを実行した後に、構築されたSQLを取得してデバッグ目的で使用することができます。

## SQLクエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

いくつかの列だけを選択できます（非常に多くの列を持つ幅広いテーブルでパフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

技術的には別のテーブルも選択できます！なぜダメなんでしょうか！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の他のテーブルを結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

いくつかのカスタムwhere引数を設定できます（このwhereステートメントでパラメータを設定することはできません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`のようなことをしたくなるかもしれません。これを行わないでください！これはSQLインジェクション攻撃に対して脆弱です。多くのオンライン記事がありますので、「sql injection attacks php」をグーグル検索して、このテーマに関するたくさんの情報が得られます。このライブラリでこれを処理するための適切な方法は、この`where()`メソッドの代わりに、 `$user->eq('id', $id)->eq('name', $name)->find();`のようにすることです。絶対にこれを行う必要がある場合、`PDO`ライブラリには`$pdo->quote($var)`がありますので、それを使用してエスケープできます。`quote()`を使用した後、`where()`ステートメントで使用できます。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

特定の条件で結果をグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

返されたクエリを特定の方法でソートします。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコードの数を制限します。第二のintが指定されると、それはオフセットされ、SQLと同様になります。

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

条件をOR文でラップすることが可能です。これは、`startWrap()`および`endWrap()`メソッドを使用するか、フィールドおよび値の後の3番目のパラメータを埋めることで行います。

```php
// メソッド1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// これは `id = 1 AND (name = 'demo' OR name = 'test')` に評価されます。

// メソッド2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// これは `id = 1 OR name = 'demo'` に評価されます。
```

## 関係
このライブラリを使用して、さまざまな種類の関係を設定できます。テーブル間で1対多と1対1の関係を設定できます。これは、クラス内で少し余分なセットアップが必要です。

`$relations`配列を設定するのは難しくありませんが、正しい構文を推測するのは混乱することがあります。

```php
protected array $relations = [
	// 任意の名前をキーとして使用できます。アクティブレコードの名前が良いでしょう。例: user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // これは関係の種類です

		// 必須
		'Some_Class', // これは参照する「他の」アクティブレコードクラスです

		// 必須
		// 関係のタイプに応じて
		// self::HAS_ONE = 結合を参照する外部キー
		// self::HAS_MANY = 結合を参照する外部キー
		// self::BELONGS_TO = 結合を参照するローカルキー
		'local_or_foreign_key',
		// ご参考までに、これは「他の」モデルの主キーのみに結合します。

		// オプション
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 関係を結合するときに希望する追加条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// オプション
		'back_reference_name' // この関係を自分自身に戻参照する場合 Ex: $user->contact->user;
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

今、参照が設定されたので、それを非常に簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最も最近のユーザーを見つけます。
$user->notNull('id')->orderBy('id desc')->find();

// 関係を介して連絡先を取得します。
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆に行くこともできます。
$contact = new Contact();

// 1つの連絡先を見つける
$contact->find();

// 関係を介してユーザーを取得します。
echo $contact->user->name; // これはユーザーの名前です
```

かっこいいでしょ？

## カスタムデータの設定
時には、独自の計算など、ActiveRecordにユニークなものを付加する必要があるかもしれません。これをオブジェクトに付加して、たとえばテンプレートに渡すのが簡単になることがあります。

#### `setCustomData(string $field, mixed $value)`
カスタムデータは`setCustomData()`メソッドで追加します。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

そして、それを通常のオブジェクトプロパティのように参照するだけです。

```php
echo $user->page_view_count;
```

## イベント

このライブラリのもう一つの素晴らしい機能は、イベントについてです。イベントは、呼び出した特定のメソッドに基づいた特定の時点でトリガーされます。これは、自動的にデータを設定するのに非常に役立ちます。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

これは、デフォルトの接続を設定する必要がある場合に非常に便利です。

```php
// index.phpまたはbootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // 参照を忘れないでください
		// 接続を自動的に設定するためにこれを行うことができます
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// テーブル名をこのように設定することもできます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

これは、おそらく各クエリの操作が必要な場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 常にid >= 0を実行します。
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

このメソッドは、このレコードが取得されるたびにいくつかのロジックを実行する必要がある場合に役立ちます。何かを復号化する必要がありますか？毎回カスタムカウントクエリを実行する必要がありますか（パフォーマンス的には少し悪いですが）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化している
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// おそらく何かをカスタムで保存する
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

これは、おそらく各クエリの操作が必要な場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 常にid >= 0を実行
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`と似ていますが、すべてのレコードに適用することができます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()のようなことをします
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

いつものようにデフォルト値を設定する必要がある場合に非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 標準のデフォルトを設定する
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

挿入後にデータを変更する必要がある場合の使用ケースがあります。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなたのやり方
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

更新ごとにデフォルト値を設定する必要がある場合に非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 標準のデフォルトを設定
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更する必要がある場合の使用ケースがあります。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなたのやり方
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

これは、挿入または更新が行われたときにイベントを発生させたい場合に便利です。長い説明は割愛しますが、その機能はご想像の通りでしょう。

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

少し奇妙なことをするかもしれませんが、こだわる必要はありません！頑張ってください！

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

## データベース接続管理

このライブラリを使用する場合、データベース接続をいくつかの異なる方法で設定できます。コンストラクタ内で接続を設定することができますし、config変数`$config['connection']`を介して設定することも、`setDatabaseConnection()`（v0.4.1）を介して設定することもできます。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例えば
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

毎回アクティブレコードを呼ぶたびに`$database_connection`を設定するのを避けたい場合、何らかの方法があります！

```php
// index.phpまたはbootstrap.php
// Flightにクラスとして登録します
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// そして、引数は必要ありません！
$user = new User();
```

> **注意:** ユニットテストを計画している場合、この方法ではユニットテストにいくつかの困難をもたらす可能性がありますが、接続を`setDatabaseConnection()`または`$config['connection']`で注入できるため、そこまで悪くはありません。

コマンドラインスクリプトを長時間実行してデータベース接続を更新する必要がある場合、`$your_record->setDatabaseConnection($pdo_connection)`を使用して接続を再設定できます。

## コントリビュート

ぜひご参加ください。:D

### セットアップ

貢献する際は、`composer test-coverage`を実行して100％のテストカバレッジを維持してください（これは真のユニットテストカバレッジではなく、むしろ統合テストのようなものです）。

また、`composer beautify`と`composer phpcs`を実行して、すべてのリンティングエラーを修正してください。

## ライセンス

MIT