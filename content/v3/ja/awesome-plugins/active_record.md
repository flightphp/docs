# Flight Active Record

アクティブレコードは、データベースのエンティティを PHP オブジェクトにマッピングするものです。わかりやすく言うと、データベースに users テーブルがある場合、そのテーブルの行をコードベース内の `User` クラスと `$user` オブジェクトに「変換」できます。[基本的な例](#basic-example)を参照してください。

GitHub のリポジトリは[こちら](https://github.com/flightphp/active-record)です。

## 基本的な例

以下のテーブルがあるとします:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

次に、このテーブルを表す新しいクラスを設定できます:

```php
/**
 * ActiveRecord クラスは通常単数形です
 * 
 * ここにテーブルのプロパティをコメントとして追加することを強くおすすめします
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// you can set it this way
		parent::__construct($database_connection, 'users');
		// or this way
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

さあ、魔法が起こるのを見てください！

```php
// sqlite の場合
$database_connection = new PDO('sqlite:test.db'); // これは単なる例です。おそらく実際のデータベース接続を使うでしょう

// mysql の場合
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// または mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// またはオブジェクトベースではない作成方法での mysqli
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
// ここで $user->save() は使えません。使うと更新だと判断されてしまいます！

echo $user->id; // 2
```

たったこれだけで新しいユーザーを追加できました！では、データベースにユーザー行があるので、どうやって取り出しますか？

```php
$user->find(1); // データベース内で id = 1 を検索して返します。
echo $user->name; // 'Bobby Tables'
```

すべてのユーザーを検索したい場合はどうしますか？

```php
$users = $user->findAll();
```

特定の条件を指定したい場合はどうしますか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

これがどれだけ楽しいかわかりますか？インストールして始めましょう！

## インストール

Composer で簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使い方

これはスタンドアロンライブラリとしても、Flight PHP Framework と一緒にも使えます。完全にあなた次第です。

### スタンドアロン

コンストラクタに PDO 接続を渡すようにしてください。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは単なる例です。おそらく実際のデータベース接続を使うでしょう

$User = new User($pdo_connection);
```

> 毎回コンストラクタでデータベース接続を設定したくないですか？他のアイデアについては[データベース接続管理](#database-connection-management)を参照してください！

### Flight のメソッドとして登録する

Flight PHP Framework を使用している場合、ActiveRecord クラスをサービスとして登録できますが、正直なところ必須ではありません。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後、コントローラや関数などでこのように使用できます。

Flight::user()->find(1);
```

## `runway` メソッド

[runway](/awesome-plugins/runway) は Flight 用の CLI ツールで、このライブラリ専用のカスタムコマンドがあります。

```bash
# 使用法
php runway make:record database_table_name [class_name]

# 例
php runway make:record users
```

これにより、`app/records/` ディレクトリに `UserRecord.php` として次の内容の新しいクラスが作成されます:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * users テーブル用の ActiveRecord クラスです。
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
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
     * @var array $relations モデルのリレーションを設定します
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
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

## CRUD 関数

#### `find($id = null) : boolean|ActiveRecord`

1 件のレコードを検索し、現在のオブジェクトに割り当てます。何らかの `$id` を渡すと、その値で主キーを検索します。何も渡さない場合は、テーブル内の最初のレコードを検索するだけです。

さらに、テーブルをクエリするための他のヘルパーメソッドを渡すこともできます。

```php
// 事前にいくつかの条件を指定してレコードを検索
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

#### `first(): ActiveRecord` (v0.8.0)

条件に一致する最初のレコードを検索します。順序を設定していない場合は、主キーの昇順で並べます。何も一致しない場合は、ハイドレートされていないレコードが返るため、何か返ってきたか確信がない場合は `isHydrated()` を確認してください。

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

`first()` と同じですが、主キーの降順で並べます。「最新のものを取得する」クエリに便利です。

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

現在の条件に一致する行を数えます。クエリに `groupBy()` がある場合、`count()` は意図的にそれを無視します。単一のスカラーカウントでは、グループごとに 1 行を表現できないためです。

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

条件に一致するレコードがあれば `true` を返します。内部的には軽量な `SELECT 1 ... LIMIT 1` を実行します。

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

大量のオブジェクトをハイドレートする代わりに、1 つのカラムから値のフラットな配列を返します。一意の値を取得するには `distinct()` と組み合わせてください。

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

主キーに対する `pluck()` のショートカットです。

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

現在のレコードがハイドレートされている（データベースから取得されている）場合に `true` を返します。

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

テキストベースの主キー（UUID など）がある場合、挿入前に主キーの値を設定する方法は 2 つあります。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // または $user->save();
```

または、イベントを通じて主キーを自動生成することもできます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// 上記の配列の代わりに、この方法でも primaryKey を設定できます。
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // または、一意の ID を生成する必要がある方法で
	}
}
```

挿入前に主キーを設定しない場合、`rowid` が設定され、データベースが生成しますが、そのフィールドがテーブルに存在しない可能性があるため永続化されません。そのため、イベントを使用してこれを自動処理することをおすすめします。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースに更新します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

ロード済みのレコードの単一カラムを更新して保存します。これは `$user->dirty([ 'name' => $value ])->update()` のショートカットです。これにはロード済みのレコードが必要です。

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードに id がある場合は更新し、そうでない場合は挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**注意:** クラスでリレーションを定義している場合、それらが定義され、インスタンス化され、更新すべきダーティデータを持っていれば、それらのリレーションも再帰的に保存されます。（v0.4.0 以降）

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

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

条件に一致するすべてのレコードを 1 つのステートメントで更新します。レコードのハイドレートもイベントの発火も行われません。まさにそれが高速な理由です。影響を受けた行数を返します。

2 番目の引数に `true` を渡さない限り、WHERE 条件なしでは実行されません。未来のあなたが感謝するでしょう。

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// はい、本当にテーブル内のすべての行を更新したいのですね
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

条件に一致するすべてのレコードを 1 つのステートメントで削除します。`updateAll()` と同じで、ハイドレートもイベントもなく、`true` を渡さない限り WHERE 条件が必要です。削除された行数を返します。使用には注意してください！

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

ダーティデータとは、レコード内で変更されたデータを指します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も「ダーティ」ではありません。

$user->email = 'test@example.com'; // email が変更されたため、これで「ダーティ」と見なされます。
$user->update();
// 更新されてデータベースに永続化されたため、ダーティなデータはありません

$user->password = password_hash()'newpassword'); // これでこれはダーティです
$user->dirty(); // 何も渡さないと、すべてのダーティエントリがクリアされます。
$user->update(); // ダーティとしてキャプチャされたものがないため、何も更新されません。

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name と password の両方が更新されます。
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

これは `dirty()` メソッドのエイリアスです。何をしているのかがもう少し明確になります。

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

現在のレコードを初期状態にリセットします。これはループ処理のような動作で使うと非常に便利です。
`true` を渡すと、現在のオブジェクトを検索するために使用されたクエリデータもリセットされます（デフォルトの動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンな状態から始める
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`、`findAll()`、`insert()`、`update()`、または `save()` メソッドを実行した後、構築された SQL を取得してデバッグ目的に使用できます。

## トランザクション

一緒に成功する必要がある複数の書き込みを実行する必要がありますか？それらを `transaction()` でラップしてください（v0.8.0）。callable を渡すと、引数としてレコードが渡されます。callable が例外をスローすると、すべてがロールバックされ、例外が再スローされます。そうでなければコミットされ、callable が返したものが返されます。

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// 何も例外をスローしなければここでコミットが行われます
});
```

ネストされたトランザクションはサポートされていません（セーブポイントなし）。そのためフラットに保ってください。

## SQL クエリメソッド
#### `select(string $field1 [, string $field2 ... ])`

テーブル内のいくつかのカラムだけを選択できます（多くのカラムを持つ非常に幅の広いテーブルではパフォーマンスが向上します）

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

技術的には別のテーブルを選ぶこともできます！なぜダメなんでしょう？！

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の別のテーブルに結合することもできます。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムの where 引数を設定できます（この where ステートメントではパラメータを設定できません）

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティに関する注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();` のようなことをしたくなるかもしれません。これは絶対にやらないでください！！！これは SQL インジェクション攻撃として知られるものに対して脆弱です。オンラインにはたくさんの記事があります。「sql injection attacks php」で Google 検索すれば、このテーマに関する多くの記事が見つかります。このライブラリでこれを適切に処理する方法は、この `where()` メソッドの代わりに、`$user->eq('id', $id)->eq('name', $name)->find();` のようなことを行うことです。どうしてもこれを行う必要がある場合、`PDO` ライブラリには `$pdo->quote($var)` があり、エスケープできます。`quote()` を使用した後にのみ、`where()` ステートメントでそれを使用できます。

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

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` と `orderBy()` は生の SQL フラグメントを受け取ります。`'name DESC'` をハードコードしている場合は問題ありません。カラム名がユーザー入力（たとえばソート可能なテーブルヘッダー）から来る場合は、代わりに `orderByColumn()` を使用してください。許可されるのはプレーンなカラム名と `table.column` パスだけであり、方向は `ASC` または `DESC` でなければならないため、インジェクトできるものはありません。

```php
// $sortColumn はリクエストから来ます
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコード数を制限します。2 番目の int が指定された場合、SQL と同様に offset、limit になります。

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

次のクエリに `DISTINCT` を追加します。通常の select と `pluck()` で動作します。`count()` はそれを無視します。単一の集計行に `DISTINCT` を付けても何も起こらないためです。

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

## WHERE 条件
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

### OR 条件

条件を OR ステートメントでラップすることが可能です。これは `startWrap()` と `endWrap()` メソッドを使うか、フィールドと値の後の条件の 3 番目のパラメータを埋めることで行います。

```php
// 方法 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// これは `id = 1 AND (name = 'demo' OR name = 'test')` と評価されます

// 方法 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// これは `id = 1 OR name = 'demo'` と評価されます
```

## スコープ

スコープ（v0.8.0）は再利用可能なクエリチェーンで、クラス上の `$this` を返す通常のインスタンスメソッドとして定義されます。一度書けば、他のクエリメソッドと同じようにチェーンできます。

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// これでクエリが文章のように読めます
(new User($pdo_connection))->active()->recent(30)->findAll();
```

`scope()` を使ってスコープを名前で呼び出すこともできます。スコープ名がコード内の別の場所から来る場合に便利です。メソッドが存在しない場合は `BadMethodCallException` をスローします。

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## リレーション
このライブラリを使用して、いくつかの種類のリレーションを設定できます。テーブル間に one->many および one->one のリレーションを設定できます。これには事前にクラスである程度の追加設定が必要です。

`$relations` 配列の設定は難しくありませんが、正しい構文を推測するのは混乱するかもしれません。

```php
protected array $relations = [
	// キーには好きな名前を付けられます。ActiveRecord の名前がおそらく良いでしょう。例: user, contact, client
	'user' => [
		// 必須
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // これはリレーションの種類です

		// 必須
		'Some_Class', // これは参照する「もう一方の」ActiveRecord クラスです

		// 必須
		// リレーションの種類によって異なります
		// self::HAS_ONE = 結合を参照する外部キー
		// self::HAS_MANY = 結合を参照する外部キー
		// self::BELONGS_TO = 結合を参照するローカルキー
		'local_or_foreign_key',
		// 念のため言うと、これは「もう一方の」モデルの主キーにのみ結合します

		// 任意
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // リレーションを結合するときに必要な追加条件
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// 任意
		'back_reference_name' // このリレーションをそれ自身に逆参照したい場合に使います 例: $user->contact->user;
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

// 最新のユーザーを検索します。
$user->notNull('id')->orderBy('id desc')->find();

// リレーションを使用して連絡先を取得します:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// または逆方向にもできます。
$contact = new Contact();

// 1 件の連絡先を検索します
$contact->find();

// リレーションを使用してユーザーを取得します:
echo $contact->user->name; // これはユーザー名です
```

かなりクールですよね？

### イーガーローディング

#### 概要
イーガーローディングは、リレーションを事前に読み込むことで N+1 クエリ問題を解決します。各レコードのリレーションごとに個別のクエリを実行する代わりに、イーガーローディングはリレーションごとに追加の 1 クエリだけで関連データをすべて取得します。

> **注意:** イーガーローディングは v0.7.0 以降でのみ利用できます。

#### 基本的な使い方
イーガーロードするリレーションを指定するには `with()` メソッドを使用します:
```php
// N+1 ではなく 2 クエリでユーザーとその連絡先を読み込みます
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // 追加クエリはありません！
    }
}
```

#### 複数のリレーション
複数のリレーションを一度に読み込みます:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### リレーションの種類

##### HAS_MANY
```php
// 各ユーザーのすべての連絡先をイーガーロードします
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts はすでに配列として読み込まれています
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// 各ユーザーに 1 件の連絡先をイーガーロードします
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact はすでにオブジェクトとして読み込まれています
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// すべての連絡先の親ユーザーをイーガーロードします
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user はすでに読み込まれています
    echo $c->user->name;
}
```
##### find() との併用
イーガーローディングは 
findAll()
 と 
find()
 の両方で動作します:

```php
$user = $user->with('contacts')->find(1);
// ユーザーとそのすべての連絡先が 2 クエリで読み込まれます
```
#### パフォーマンス上の利点
イーガーローディングなし（N+1 問題）:
```php
$users = $user->findAll(); // 1 クエリ
foreach ($users as $u) {
    $contacts = $u->contacts; // N クエリ（ユーザーごとに 1 つ！）
}
// 合計: 1 + N クエリ
```

イーガーローディングあり:

```php
$users = $user->with('contacts')->findAll(); // 合計 2 クエリ
foreach ($users as $u) {
    $contacts = $u->contacts; // 追加クエリ 0！
}
// 合計: 2 クエリ（ユーザー用 1 + すべての連絡先用 1）
```
10 人のユーザーの場合、これによりクエリ数は 11 から 2 に削減されます - 82% の削減です！

#### 重要な注意
- イーガーローディングは完全に任意です - レイジーローディングは以前と同様に機能します
- すでに読み込まれたリレーションは自動的にスキップされます
- 逆参照はイーガーローディングで機能します
- リレーションコールバックはイーガーローディング中に尊重されます

#### 制限
- ネストされたイーガーローディング（例: 
with(['contacts.addresses'])
）は現在サポートされていません
- クロージャによるイーガーロード制約はこのバージョンではサポートされていません

## カスタムデータの設定
場合によっては、ActiveRecord に一意のもの（たとえばカスタム計算）を添付する必要があり、テンプレートなどに渡されるオブジェクトに単に添付する方が簡単なことがあります。

#### `setCustomData(string $field, mixed $value)`
カスタムデータは `setCustomData()` メソッドで添付します。
```php
$user->setCustomData('page_view_count', $page_view_count);
```

そして、通常のオブジェクトプロパティのように参照するだけです。

```php
echo $user->page_view_count;
```

## タイムスタンプ

テーブルに `created_at` と `updated_at` カラムがある場合、ライブラリに自動で埋めてもらうことができます（v0.8.0）。クラスで `protected bool $timestamps = true;` を設定すると、挿入時に `created_at` と `updated_at` を設定し、更新時に `updated_at` を設定します。形式は `Y-m-d H:i:s` です。どちらかのカラムを自分で設定した場合、ライブラリはあなたの値をそのままにします。

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

テーブルに実際にこれらのカラムが必要です。そうでないと挿入と更新が失敗します。

## イベント

このライブラリのもう 1 つの超素晴らしい機能はイベントです。イベントは、呼び出す特定のメソッドに基づいて特定のタイミングでトリガーされます。データを自動的に設定するのに非常に役立ちます。

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

	protected function onConstruct(self $self, array &$config) { // & 参照を忘れないでください
		// 接続を自動設定するにはこうできます
		$config['connection'] = Flight::db();
		// またはこれ
		$self->transformAndPersistConnection(Flight::db());
		
		// この方法でテーブル名も設定できます。
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

これは、毎回クエリを操作する必要がある場合にのみ役立つでしょう。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// それが好みなら常に id >= 0 を実行します
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

これは、このレコードが取得されるたびに何らかのロジックを常に実行する必要がある場合により役立つでしょう。何かを復号する必要がありますか？毎回カスタムカウントクエリを実行する必要がありますか（パフォーマンスは良くありませんが、まあ）？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// 何かを復号する
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// たぶんクエリのようなカスタムな何かを保存する？？？
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

これは、毎回クエリを操作する必要がある場合にのみ役立つでしょう。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// それが好みなら常に id >= 0 を実行します
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
			// afterFind() のようなクールなことをする
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

毎回設定するデフォルト値が必要な場合に非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 妥当なデフォルトを設定する
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

挿入後にデータを変更するユースケースがあるかもしれません？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 好きにしてください
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

更新時に毎回設定するデフォルト値が必要な場合に非常に役立ちます。

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// 妥当なデフォルトを設定する
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

更新後にデータを変更するユースケースがあるかもしれません？

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// 好きにしてください
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// または何でも....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

これは、挿入または更新が発生したときにイベントを発生させたい場合に便利です。長い説明は省きますが、何のことかは推測できるでしょう。

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

ここで何をしたいのかはわかりませんが、ここでは判断しません！やってみてください！

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

## データベース接続管理

このライブラリを使用しているとき、データベース接続をいくつかの異なる方法で設定できます。コンストラクタで設定するか、設定変数 `$config['connection']` を介して設定するか、`setDatabaseConnection()`（v0.4.1）を介して設定できます。

```php
$pdo_connection = new PDO('sqlite:test.db'); // 例
$user = new User($pdo_connection);
// または
$user = new User(null, [ 'connection' => $pdo_connection ]);
// または
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

アクティブレコードを呼び出すたびに常に `$database_connection` を設定するのを避けたい場合、回避策があります！

```php
// index.php または bootstrap.php
// これを Flight の登録済みクラスとして設定します
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// これで引数は不要です！
$user = new User();
```

> **注意:** ユニットテストを計画している場合、この方法で行うとユニットテストにいくつかの課題が追加される可能性がありますが、全体としては `setDatabaseConnection()` または `$config['connection']` で接続を注入できるため、それほど悪くはありません。

データベース接続を更新する必要がある場合、たとえば長時間実行される CLI スクリプトを実行していて、ときどき接続を更新する必要がある場合、`$your_record->setDatabaseConnection($pdo_connection)` で接続を再設定できます。

## コントリビュート

ぜひお願いします。:D

### セットアップ

コントリビュートするときは、100% のテストカバレッジを維持するために `composer test-coverage` を実行してください（これは真のユニットテストカバレッジではなく、どちらかというと統合テストです）。

また、リンティングエラーを修正するために `composer beautify` と `composer phpcs` を実行してください。

## ライセンス

MIT