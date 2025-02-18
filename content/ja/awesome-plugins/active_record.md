# Flight アクティブレコード 

アクティブレコードとは、データベースエンティティをPHPオブジェクトにマッピングすることです。簡単に言うと、データベースにユーザーテーブルがある場合、そのテーブルの1行を`User`クラスとコードベース内の`$user`オブジェクトに「変換」できます。詳細は[基本的な例](#basic-example)を参照してください。

GitHubのリポジトリは[こちら](https://github.com/flightphp/active-record)をクリックしてください。

## 基本的な例

次のテーブルがあるとします：

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

これで、このテーブルを表す新しいクラスを設定できます：

```php
/**
 * アクティブレコードクラスは通常単数形です
 * 
 * テーブルのプロパティはここにコメントとして追加することを強くお勧めします
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

魔法のように見てみましょう！

```php
// sqliteの場合
$database_connection = new PDO('sqlite:test.db'); // これは例です。本物のデータベース接続を使用することになるでしょう

// mysqlの場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// またはmysqliの場合
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// または非オブジェクトベースの生成でmysqli
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'ボビー・テーブルズ';
$user->password = password_hash('いくつかのクールなパスワード');
$user->insert();
// または$user->save();

echo $user->id; // 1

$user->name = 'ジョセフ・ママ';
$user->password = password_hash('いくつかのクールなパスワード再び!!!');
$user->insert();
// ここで$user->save()は使えません。更新だとみなされるからです！

echo $user->id; // 2
```

新しいユーザーを追加するのはこれほど簡単でした！データベースにユーザー行がある場合、それを引き出すにはどうしますか？

```php
$user->find(1); // データベースでid=1を見つけて返します。
echo $user->name; // 'ボビー・テーブルズ'
```

特定の条件でユーザー全員を見つけたい場合は？

```php
$users = $user->findAll();
```

特定の条件はどうでしょうか？

```php
$users = $user->like('name', '%ママ%')->findAll();
```

どれだけ楽しそうですか？インストールして始めましょう！

## インストール

Composerを使って簡単にインストールできます。

```php
composer require flightphp/active-record 
```

## 使用方法

これはスタンドアロンライブラリまたはFlight PHPフレームワークと共に使用できます。すべてあなた次第です。

### スタンドアロン
コンストラクタにPDO接続を渡すことを確認してください。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です。本物のデータベース接続を使用することになるでしょう

$User = new User($pdo_connection);
```

> コンストラクタで毎回データベース接続を設定したくないですか？他のアイデアは[データベース接続管理](#database-connection-management)を参照してください！

### Flightのメソッドとして登録
Flight PHPフレームワークを使用している場合、アクティブレコードクラスをサービスとして登録できますが、本当にそうする必要はありません。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラー、関数などでこのように使えます。

Flight::user()->find(1);
```

## `runway` メソッド

[runway](https://docs.flightphp.com/awesome-plugins/runway)は、Flight用のCLIツールで、このライブラリ用のカスタムコマンドがあります。

```bash
# 使用方法
php runway make:record database_table_name [class_name]

# 例
php runway make:record users
```

これは、`app/records/`ディレクトリに`UserRecord.php`として次の内容で新しいクラスを作成します：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * usersテーブル用のアクティブレコードクラス。
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
     * @var array $relations モデルの関係性を設定します
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

1つのレコードを見つけて、現在のオブジェクトに割り当てます。何らかの`$id`を渡すと、その値で主キーを検索します。何も渡さない場合、テーブルの最初のレコードを見つけます。

さらに、テーブルを照会するために他のヘルパーメソッドを渡すことができます。

```php
// あらかじめいくつかの条件でレコードを見つける
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

現在のレコードがハイドレートされている（データベースから取得された）場合は`true`を返します。

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

テキストベースの主キー（UUIDなど）を持っている場合、挿入前に主キー値を次のいずれかの方法で設定できます。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'デモ';
$user->password = md5('デモ');
$user->insert(); // または$user->save();
```

または、イベントを通じて主キーが自動的に生成されるようにすることができます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 上記の配列ではなく、次のように主キーを設定することもできます。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // または一意のIDを必要に応じて生成します
	}
}
```

主キーを挿入前に設定しないと、`rowid`に設定され、データベースが生成しますが、そのフィールドがテーブルに存在しない場合は永続化されません。これが、これを自動的に処理するためにイベントを使用することをお勧めする理由です。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードにIDがある場合は更新され、さもなくば挿入されます。

```php
$user = new User($pdo_connection);
$user->name = 'デモ';
$user->password = md5('デモ');
$user->save();
```

**注意：** クラス内に定義された関係性がある場合、それらの関係も同時に保存されます。これは、定義された場合、インスタンス化され、更新すべきデータがある場合に当てはまります。（v0.4.0以上）

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

事前に検索を実行することで、複数のレコードを削除することもできます。

```php
$user->like('name', 'ボブ%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

ダーティデータとは、レコード内で変更されたデータを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「ダーティ」ではありません。

$user->email = 'test@example.com'; // これでemailは変更されたため「ダーティ」と見なされます。
$user->update();
// 更新され、データベースに永続化されたため、ダーティデータはありません

$user->password = password_hash('newpassword'); // これがダーティです
$user->dirty(); // 何も渡さなければすべてのダーティエントリがクリアされます。
$user->update(); // 何もキャプチャされていないため、何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nameとpasswordの両方が更新されます。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

これは`dirty()`メソッドのエイリアスです。あなたが何をしているのかがより明確です。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nameとpasswordの両方が更新されます。
```

#### `isDirty(): boolean` (v0.4.0)

現在のレコードが変更された場合は`true`を返します。

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

`find()`, `findAll()`, `insert()`, `update()`, または `save()` メソッドを実行した後、構築されたSQLを取得し、デバッグ目的で使用できます。

## SQL クエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

テーブル内のわずかにいくつかの列のみを選択できます（非常に広いテーブルで多くの列がある場合は、パフォーマンスが向上します）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

別のテーブルを選択することもできます！なぜそうしないのですか？

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルに結合することさえできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムのwhere引数を設定できます（このwhereステートメント内でパラメータを設定することはできません）。

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - $user->where("id = '{$id}' AND name = '{$name}'")->find(); のように行いたくなるかもしれませんが、絶対にこれを行わないでください！これはSQLインジェクション攻撃に対して脆弱です。オンラインには多くの記事がありますので、「sql injection attacks php」とグーグル検索すると、このトピックに関するたくさんの記事が見つかるでしょう。このライブラリに対処する適切な方法は、この`where()` メソッドの代わりに、より形を変えた `$user->eq('id', $id)->eq('name', $name)->find();` のように行うことです。これを完全に行う必要がある場合、`PDO`ライブラリには `$pdo->quote($var)` があり、それがあなたのためにエスケープします。`quote()`を使用した後にのみ、`where()` ステートメントで使用できます。

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

返されるレコードの数を制限します。2番目に与えられたintがある場合、それはオフセット、制限はSQLと同じです。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE条件
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Where `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Where `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Where `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Where `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Where `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Where `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Where `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Where `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Where `field LIKE $value` または `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` または `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR条件

条件をORステートメントでラップすることができます。これは、`startWrap()` と `endWrap()` メソッドを使用するか、フィールドと値の後に条件の3番目のパラメータを埋めることで行われます。

```php
// メソッド 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// これは `id = 1 AND (name = 'demo' OR name = 'test')` に評価されます

// メソッド 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// これは `id = 1 OR name = 'demo'` に評価されます
```

## 関係
このライブラリを使用して、さまざまな種類の関係を設定できます。テーブル間のone->manyおよびone->oneの関係を設定できます。これには、事前にクラス内で少し追加の設定が必要です。

`$relations`配列を設定することは難しくはありませんが、正しい構文を推測するのは混乱を招くことがあります。

```php
protected array $relations = [
	// 任意の名前をキーとして付けることができます。アクティブレコードの名前が良いかもしれません。例：user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // これは関係のタイプです

		// 必須
		'Some_Class', // これはリファレンスする「他の」アクティブレコードクラスです

		// 必須
		// 関係の種類に応じて
		// self::HAS_ONE = 結合を参照する外部キー
		// self::HAS_MANY = 結合を参照する外部キー
		// self::BELONGS_TO = 結合を参照するローカルキー
		'local_or_foreign_key',
		// 参考までに、これは「他の」モデルの主キーにのみ結合します

		// 任意
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // 関係を結合する際の追加条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 任意
		'back_reference_name' // これは、この関係を自分に戻すためのものです 例: $user->contact->user;
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

これで、非常に簡単にリファレンスを使用できるようになりました！

```php
$user = new User($pdo_connection);

// もっとも最近のユーザーを見つけます。
$user->notNull('id')->orderBy('id desc')->find();

// 関係を使用して連絡先を取得します：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または反対方向に行くこともできます。
$contact = new Contact();

// 1つの連絡先を見つけます
$contact->find();

// 関係を使用してユーザーを取得します：
echo $contact->user->name; // これはユーザー名です
```

かなりクールですね？

## カスタムデータの設定
時々、アクティブレコードにユニークな何かを添付する必要がある場合があります。たとえば、テンプレートに渡すのが簡単な独自の計算です。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()`メソッドでカスタムデータを添付します。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

それから、通常のオブジェクトプロパティのように参照します。

```php
echo $user->page_view_count;
```

## イベント

このライブラリのもう1つの非常に素晴らしい機能は、イベントに関するものです。イベントは、呼び出す特定のメソッドに基づいて特定のタイミングでトリガーされます。これは、あなたのために自動的にデータを設定するのに非常に便利です。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

デフォルト接続を設定する必要がある場合に非常に便利です。 

```php
// index.php または bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // &リファレンスを忘れないでください
		// 接続を自動的に設定するためにこれを行うことができます
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名を設定することもできます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

これは、毎回クエリ操作が必要な場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// 常にid >= 0を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

このメソッドは、毎回このレコードが取得される際にロジックを実行する必要がある場合にもっと役立ちます。何かを復号化する必要がありますか？毎回カスタムカウントクエリを実行する必要がありますか（パフォーマンスは良くありませんが）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化する
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// カスタムなものを保存する場合、たとえばクエリのように？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

これは、毎回クエリ操作が必要な場合に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// 常にid >= 0を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()`に似ていますが、すべてのレコードに対して実行できます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind()のようなクールな何かを行います
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

各挿入時にデフォルト値を設定する必要がある場合に非常に便利です。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 安全なデフォルトを設定します
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

挿入後にデータを変更する必要があるユースケースがある場合があります。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなた自身のやり方で
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

各更新時に一部のデフォルト値を設定する必要がある場合に非常に便利です。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 安全なデフォルトを設定します
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更するユースケースがある場合があります。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなた自身のやり方で
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

これは、挿入または更新が発生する際にイベントが発生するようにしたい場合に役立ちます。長い説明を省きますが、あなたが何を想像できるかはおそらくわかります。

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

ここで何をしたいのかわからないですが、判断をしません！あなたの好きなようにやってみてください！

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

このライブラリを使用する際、いくつかの異なる方法でデータベース接続を設定できます。コンストラクタで接続を設定することも、`$config['connection']`という設定変数を介して設定することも、または`setDatabaseConnection()`を介して設定することもできます（v0.4.1）。 

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例えば
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

アクティブレコードを呼び出すたびに `$database_connection`を常に設定することを避けたい場合は、その方法があります！

```php
// index.php または bootstrap.php
// Flightに登録クラスとしてこれを設定します
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// そして、引数は不要！
$user = new User();
```

> **注意:** ユニットテストを行う予定がある場合、この方法はユニットテストにいくつかの課題を追加するかもしれませんが、全体的には`setDatabaseConnection()` または `$config['connection']`を介して接続を注入できるため、あまり悪くはありません。

データベース接続を更新する必要がある場合、たとえば、長時間実行されるCLIスクリプトを実行していて、定期的に接続を更新する必要がある場合、`$your_record->setDatabaseConnection($pdo_connection)`を使用して接続を再設定できます。

## コントリビューション

ぜひしてください。 :D

### セットアップ

貢献する際は、`composer test-coverage` を実行して100%のテストカバレッジを維持してください（これは真のユニットテストカバレッジではなく、より統合テストのようなものです）。

また、`composer beautify` や `composer phpcs` を実行して、リントエラーを修正することも忘れないでください。

## ライセンス

MIT