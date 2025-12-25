# Flight Active Record 

アクティブ レコードは、データベース エンティティを PHP オブジェクトにマッピングするものです。簡単に言うと、データベースに users テーブルがある場合、そのテーブルの行をコードベース内の `User` クラスと `$user` オブジェクトに「変換」できます。[基本例](#basic-example) を参照してください。

GitHub のリポジトリは [こちら](https://github.com/flightphp/active-record) をクリックしてください。

## Basic Example

以下のテーブルがあると仮定しましょう：

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
 * ActiveRecord クラスは通常単数形です
 * 
 * テーブルのプロパティをコメントとしてここに追加することを強く推奨します
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

今、マジックが起こります！

```php
// SQLite の場合
$database_connection = new PDO('sqlite:test.db'); // これは単なる例です。本物のデータベース接続を使用するはずです

// MySQL の場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// または mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// またはオブジェクトベースでない mysqli の作成
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
// ここで $user->save() を使用できません。更新と判断されるためです！

echo $user->id; // 2
```

新しいユーザーを追加するのに、これほど簡単だったとは！データベースにユーザーの行が存在する今、それを引き出すにはどうしますか？

```php
$user->find(1); // データベースで id = 1 を検索して返します。
echo $user->name; // 'Bobby Tables'
```

すべてのユーザーを検索したい場合はどうでしょうか？

```php
$users = $user->findAll();
```

特定の条件付きで？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

これがどれほど楽しいかわかりますか？インストールして始めましょう！

## Installation

Composer で簡単にインストールします

```php
composer require flightphp/active-record 
```

## Usage

これはスタンドアロン ライブラリとして使用するか、Flight PHP Framework と共に使用できます。完全にあなた次第です。

### Standalone
コンストラクタに PDO 接続を渡すことを確認してください。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは単なる例です。本物のデータベース接続を使用するはずです

$User = new User($pdo_connection);
```

> コンストラクタでデータベース接続を毎回設定したくない場合、[データベース接続管理](#database-connection-management) を参照して他のアイデアを見てください！

### Flight でメソッドとして登録
Flight PHP Framework を使用している場合、ActiveRecord クラスをサービスとして登録できますが、必須ではありません。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// コントローラー、関数などで以下のように使用できます。

Flight::user()->find(1);
```

## `runway` Methods

[runway](/awesome-plugins/runway) は Flight の CLI ツールで、このライブラリ用のカスタムコマンドがあります。 

```bash
# Usage
php runway make:record database_table_name [class_name]

# Example
php runway make:record users
```

これにより、`app/records/` ディレクトリに `UserRecord.php` という新しいクラスが作成され、以下の内容が含まれます：

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users テーブルの ActiveRecord クラス。
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

## CRUD functions

#### `find($id = null) : boolean|ActiveRecord`

1 つのレコードを検索し、現在のオブジェクトに割り当てます。`$id` を渡すと、主キーに対してその値で検索を実行します。何も渡さない場合、テーブル内の最初のレコードを検索します。

さらに、他のヘルパー メソッドを渡してテーブルをクエリできます。

```php
// 事前に条件を指定してレコードを検索
$user->notNull('password')->orderBy('id DESC')->find();

// 特定の id でレコードを検索
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブルのすべてのレコードを検索します。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

現在のレコードがデータベースから取得（ハイドレート）されている場合に `true` を返します。

```php
$user->find(1);
// データ付きのレコードが見つかった場合...
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

テキストベースの主キー（例: UUID）がある場合、挿入前に主キー値を 2 つの方法のいずれかで設定できます。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // または $user->save();
```

または、イベントを通じて主キーを自動生成できます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 上記の配列の代わりにこの方法で primaryKey を設定することもできます。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // またはユニーク ID を生成する方法
	}
}
```

挿入前に主キーを設定しない場合、`rowid` に設定され、データベースが生成しますが、テーブルにそのフィールドが存在しない場合、永続化されません。これがイベントを使用して自動的に処理することを推奨する理由です。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードに id がある場合更新し、そうでない場合は挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意:** クラスにリレーションシップが定義されている場合、定義、インスタンス化、更新が必要なダーティ データがある場合、それらのリレーションを再帰的に保存します。(v0.4.0 以降)

#### `delete(): boolean`

現在のレコードをデータベースから削除します。

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

事前の検索を実行して複数のレコードを削除することもできます。

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

ダーティ データとは、レコード内で変更されたデータを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「ダーティ」ではありません。

$user->email = 'test@example.com'; // 変更されたので email は「ダーティ」と見なされます。
$user->update();
// 更新されてデータベースに永続化されたので、ダーティ データはなくなります

$user->password = password_hash()'newpassword'); // これがダーティになります
$user->dirty(); // 何も渡さないとすべてのダーティ エントリがクリアされます。
$user->update(); // ダーティとしてキャプチャされたものが何もないので何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password の両方が更新されます。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

これは `dirty()` メソッドのエイリアスです。何をしているのかが少し明確です。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password の両方が更新されます。
```

#### `isDirty(): boolean` (v0.4.0)

現在のレコードが変更されている場合に `true` を返します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。ループ型の動作で使用するのに非常に便利です。`true` を渡すと、現在のオブジェクトを検索するために使用されたクエリ データもリセットされます（デフォルト動作）。

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

#### `getBuiltSql(): string` (v0.4.1)

`find()`、`findAll()`、`insert()`、`update()`、または `save()` メソッドを実行した後、構築された SQL を取得してデバッグに使用できます。

## SQL Query Methods
#### `select(string $field1 [, string $field2 ... ])`

テーブル内の特定の列のみを選択できます（多くの列を持つ広いテーブルでパフォーマンスが向上します）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

技術的には別のテーブルを選択することもできます！なぜそうしないのですか？！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルにジョインすることもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタム where 引数を設定できます（この where 文ではパラメータを設定できません）

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ 注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();` のようなことをしたくなるかもしれません。絶対にこれをしないでください！！！これは SQL インジェクション攻撃の脆弱性があります。オンラインにたくさんの記事があります。「sql injection attacks php」を Google 検索してください。このトピックに関する多くの記事が見つかります。このライブラリでこれを適切に扱う方法は、この `where()` メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();` のようなことを行うことです。絶対にこれをしなければならない場合、`PDO` ライブラリには `$pdo->quote($var)` があり、それをエスケープします。`quote()` を使用した後でのみ、`where()` 文で使用できます。

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

返されるレコードの数を制限します。2 番目の int が与えられた場合、SQL のようにオフセット、リミットになります。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE conditions
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

### OR Conditions

条件を OR 文でラップすることが可能です。これは `startWrap()` と `endWrap()` メソッドを使用するか、フィールドと値の後の条件の 3 番目のパラメータを埋めることで行います。

```php
// Method 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// これは `id = 1 AND (name = 'demo' OR name = 'test')` に評価されます

// Method 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// これは `id = 1 OR name = 'demo'` に評価されます
```

## Relationships
このライブラリを使用して、テーブル間の one->many および one->one リレーションシップを設定できます。これにはクラス内で少し追加の設定が必要です。

`$relations` 配列を設定するのは簡単ですが、正しい構文を推測するのは混乱するかもしれません。

```php
protected array $relations = [
	// キーの名前は任意に付けられます。ActiveRecord の名前が良いでしょう。例: user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // これはリレーションシップのタイプです

		// 必須
		'Some_Class', // これは参照する「他の」ActiveRecord クラスです

		// 必須
		// リレーションシップのタイプによって異なります
		// self::HAS_ONE = ジョインを参照する外部キー
		// self::HAS_MANY = ジョインを参照する外部キー
		// self::BELONGS_TO = ジョインを参照するローカルキー
		'local_or_foreign_key',
		// FYI、これも「他の」モデルの主キーにのみジョインします

		// オプション
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // ジョイン時の追加条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// オプション
		'back_reference_name' // これを自身にバック参照したい場合、例: $user->contact->user;
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

これで参照が設定されたので、簡単に使用できます！

```php
$user = new User($pdo_connection);

// 最新のユーザーを検索。
$user->notNull('id')->orderBy('id desc')->find();

// リレーションを使用して連絡先を取得：
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆方向に。
$contact = new Contact();

// 1 つの連絡先を検索
$contact->find();

// リレーションを使用してユーザー取得：
echo $contact->user->name; // これはユーザー名です
```

かなりクールですね？

### Eager Loading

#### Overview
Eager loading は、N+1 クエリ問題を解決し、リレーションシップを事前にロードします。各レコードのリレーションシップごとに別々のクエリを実行する代わりに、リレーションシップごとに 1 つの追加クエリですべての関連データを取得します。

> **注意:** Eager loading は v0.7.0 以降でのみ利用可能です。

#### Basic Usage
`with()` メソッドを使用して、eager load するリレーションシップを指定します：
```php
// N+1 の代わりに 2 つのクエリでユーザーとその連絡先をロード
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // 追加のクエリなし！
    }
}
```

#### Multiple Relations
複数のリレーションシップを一度にロード：
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Relationship Types

##### HAS_MANY
```php
// 各ユーザーのすべての連絡先を eager load
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts はすでに配列としてロードされています
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// 各ユーザーの 1 つの連絡先を eager load
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact はすでにオブジェクトとしてロードされています
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// すべての連絡先の親ユーザーを eager load
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user はすでにロードされています
    echo $c->user->name;
}
```
##### With find()
Eager loading は 
findAll()
 と 
find()
 の両方で動作します：

```php
$user = $user->with('contacts')->find(1);
// ユーザーとすべての連絡先が 2 つのクエリでロードされます
```
#### Performance Benefits
Eager loading なし（N+1 問題）：
```php
$users = $user->findAll(); // 1 クエリ
foreach ($users as $u) {
    $contacts = $u->contacts; // N クエリ（ユーザーごとに 1 つ！）
}
// 合計: 1 + N クエリ
```

Eager loading あり：

```php
$users = $user->with('contacts')->findAll(); // 合計 2 クエリ
foreach ($users as $u) {
    $contacts = $u->contacts; // 追加のクエリ 0！
}
// 合計: 2 クエリ（ユーザー用 1 + すべての連絡先用 1）
```
10 人のユーザーの場合、クエリが 11 から 2 に減少し、82% の削減です！

#### Important Notes
- Eager loading は完全にオプションです - 遅延ロードは以前通り動作します
- すでにロードされたリレーションシップは自動的にスキップされます
- バック参照は eager loading で動作します
- リレーションコールバックは eager loading 中に尊重されます

#### Limitations
- ネストされた eager loading（例: 
with(['contacts.addresses'])
）は現在サポートされていません
- クロージャによる eager load 制約はこのバージョンでサポートされていません

## Setting Custom Data
時には ActiveRecord にカスタム計算などのユニークなものをアタッチする必要がある場合があります。これをテンプレートに渡されるオブジェクトにアタッチする方が簡単かもしれません。

#### `setCustomData(string $field, mixed $value)`
`setCustomData()` メソッドを使用してカスタム データをアタッチします。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

次に、通常のオブジェクト プロパティのように参照します。

```php
echo $user->page_view_count;
```

## Events

このライブラリのもう一つの超すごい機能はイベントについてです。イベントは特定のメソッドを呼び出す特定のタイミングでトリガーされます。データを自動的に設定するのに非常に役立ちます。

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

デフォルトの接続を設定する必要がある場合に非常に役立ちます。

```php
// index.php または bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // & 参照を忘れずに
		// 接続を自動的に設定するためにこれを実行できます
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名も設定できます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

毎回クエリ操作が必要な場合にのみ有用です。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// それがお好みなら id >= 0 を常に実行
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

このレコードが取得されるたびに常にロジックを実行する必要がある場合に、より有用です。何かを復号化する必要がありますか？毎回カスタム カウント クエリを実行する必要がありますか（パフォーマンスは悪いですが、まあ）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号化
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// クエリのようなカスタムなものを保存？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

毎回クエリ操作が必要な場合にのみ有用です。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// それがお好みなら id >= 0 を常に実行
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

`afterFind()` に似ていますが、すべてのレコードに対して実行できます！

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// afterFind() のように何かクールなことをする
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

毎回デフォルト値を設定する必要がある場合に非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかの健全なデフォルトを設定
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

挿入後にデータを変更するユース ケースがあるかもしれません？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなた次第
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

更新時に毎回デフォルト値を設定する必要がある場合に非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// いくつかの健全なデフォルトを設定
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更するユース ケースがあるかもしれません？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// あなた次第
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

挿入または更新の両方でイベントが発生するようにしたい場合に有用です。長い説明は省きますが、何かわかるはずです。

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

ここで何をしたいかわかりませんが、判断はしません！やってみてください！

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

## Database Connection Management

このライブラリを使用する場合、データベース接続をいくつかの方法で設定できます。コンストラクタで設定するか、`$config['connection']` で設定するか、`setDatabaseConnection()` で設定できます (v0.4.1)。 

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

アクティブ レコードを呼び出すたびに `$database_connection` を常に設定したくない場合、それには方法があります！

```php
// index.php または bootstrap.php
// Flight で登録されたクラスとして設定
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// そして今、引数は不要！
$user = new User();
```

> **注意:** ユニット テストを計画している場合、この方法でいくつかの課題が生じる可能性がありますが、`setDatabaseConnection()` または `$config['connection']` で接続をインジェクトできるため、全体としてそれほど悪くありません。

データベース接続を更新する必要がある場合、例えば長時間実行される CLI スクリプトを実行していて、定期的に接続を更新する必要がある場合、` $your_record->setDatabaseConnection($pdo_connection)` で接続を再設定できます。

## Contributing

ぜひ貢献してください。 :D

### Setup

貢献する場合、`composer test-coverage` を実行して 100% のテスト カバレッジを維持してください（これは真のユニット テスト カバレッジではなく、統合テストに近いです）。

また、`composer beautify` と `composer phpcs` を実行して、リンティング エラーを修正してください。

## License

MIT