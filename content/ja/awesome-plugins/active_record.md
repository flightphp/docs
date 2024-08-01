# フライトアクティブレコード

アクティブレコードは、データベースエンティティをPHPオブジェクトにマッピングするものです。つまり、データベースに`users`テーブルがある場合、そのテーブルの行を`User`クラスと`$user`オブジェクトに「翻訳」することができます。[基本的な例](#basic-example)を参照してください。

GitHubのリポジトリは[こちら](https://github.com/flightphp/active-record)。

## 基本的な例

次のテーブルがあると仮定しましょう。

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

これを表す新しいクラスを設定できます。

```php
/**
 * アクティブレコードクラスは通常単数形
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

さて、魔法が起こります！

```php
// sqlite用
$database_connection = new PDO('sqlite:test.db'); // これは例です。実際はリアルなデータベース接続を使用するはずです

// mysql用
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'ユーザ名', 'パスワード');

// またはmysqli用
$database_connection = new mysqli('localhost', 'ユーザ名', 'パスワード', 'test_db');
// オブジェクトではないベースのmysqliでも
$database_connection = mysqli_connect('localhost', 'ユーザ名', 'パスワード', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// または $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ここでは $user->save() を使用するとアップデートと認識されるので使用できません！

echo $user->id; // 2
```

そして新しいユーザーを追加するのはこれだけでした！データベースにユーザー行があるので、それを取り出すにはどうすればよいでしょうか？

```php
$user->find(1); // データベース内のid = 1を見つけてそれを返します。
echo $user->name; // 'Bobby Tables'
```

そしてすべてのユーザーを見つけたい場合はどうでしょうか？

```php
$users = $user->findAll();
```

特定の条件で見つける場合はどうでしょうか？

```php
$users = $user->like('name', '%mamma%')->findAll();
```

この楽しさがわかりますか？インストールして始めましょう！

## インストール

Composerで簡単にインストールできます

```php
composer require flightphp/active-record 
```

## 使用方法

これはスタンドアロンライブラリとして使用するか、Flight PHPフレームワークと一緒に使用できます。完全にあなた次第です。

### スタンドアロン
単にコンストラクタにPDO接続を渡せばOKです。

```php
$pdo_connection = new PDO('sqlite:test.db'); // これは例です。実際はリアルなデータベース接続を使用するはずです

$User = new User($pdo_connection);
```

Flight PHPフレームワークを使用している場合は、ActiveRecordクラスをサービスとして登録できますが、必ずしも登録する必要はありません。

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// その後は、コントローラや関数などでこれを使うことができます。

Flight::user()->find(1);
```

## `runway`メソッド

[runway](https://docs.flightphp.com/awesome-plugins/runway)は、このライブラリ用にカスタムコマンドを持つFlight用のCLIツールです。 

```bash
# 使用方法
php runway make:record データベースのテーブル名 [クラス名]

# 例
php runway make:record users
```

これにより、`app/records/`ディレクトリに`UserRecord.php`という新しいクラスが作成され、次の内容が含まれます。

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

1つのレコードを見つけて現在のオブジェクトに割り当てます。 `$id`を指定すると、その値で主キーを検索します。何も指定しない場合は、テーブル内の最初のレコードを検索します。

さらに、他のヘルパーメソッドを使ってテーブルをクエリできます。

```php
// 事前に条件を指定してレコードを検索
$user->notNull('password')->orderBy('id DESC')->find();

// 特定のidでレコードを検索
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

指定したテーブル内のすべてのレコードを取得します。

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

現在のレコードが取得済みかどうかを返します。

```php
$user->find(1);
// データが見つかった場合...
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

##### テキストベースのプライマリキー

テキストベースのプライマリキー（UUIDなど）を持っている場合、挿入前にプライマリキーの値を設定する方法が2つあります。

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'デモ';
$user->password = md5('デモ');
$user->insert(); // または $user->save();
```

または、イベントを使用してプライマリキーを自動生成することもできます。

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// このようにprimaryKeyを設定することもできます。上記の配列の代わりに
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // またはユニークなIDを生成する方法に応じて
	}
}
```

プライマリーキーを設定せずに挿入すると、`rowid`に設定され、データベースが生成しますが、それは持続しません。これはテーブルにそのフィールドが存在しないためです。これを自動的に処理するために、イベントを使用してください。

#### `update(): boolean|ActiveRecord`

現在のレコードをデータベースにアップデートします。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

現在のレコードをデータベースに挿入または更新します。レコードにidがある場合は更新し、そうでない場合は挿入します。

```php
$user = new User($pdo_connection);
$user->name = 'デモ';
$user->password = md5('デモ');
$user->save();
```

**注意:** クラス内で関係性が定義されている場合、それらの関係も再帰的に保存されます。関係が定義され、インスタンス化され、アップデートが必要なデータがある場合にのみです。 （v0.4.0以降）

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

データの変更された箇所をdirtyと呼びます。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// この時点では何も"dirty"はありません。

$user->email = 'test@example.com'; // これでemailは"dirty"と見なされる
$user->update();
// 変更されたデータが保存されたため、今はdirtyなデータはありません

$user->password = password_hash()'newpassword'); // これはdirtyです
$user->dirty(); // 何も渡さないとすべてのdirtyエントリーがクリアされます。
$user->update(); // 何も変更されていないため、何も更新されません

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nameとpasswordの両方が更新されます
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

これは`dirty()`メソッドのエイリアスです。何を行なっているかが少しだけわかりやすいです。

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // nameとpasswordの両方が更新されます
```

#### `isDirty(): boolean` (v0.4.0)

この現在のレコードが変更されている場合は`true`を返します。

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

現在のレコードを初期状態にリセットします。これはループ型の動作で使用するのに非常に役立ちます。
`true`を渡すと、現在のオブジェクトを見つけるために使用したクエリデータもリセットされます（デフォルト動作）。

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // クリーンな状態で開始
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

`find()`、`findAll()`、`insert()`、`update()`、または`save()`メソッドを実行した後、構築されたSQLを取得し、デバッグ目的で使用できます。

## SQLクエリメソッド

#### `select(string $field1 [, string $field2 ... ])`

テーブル内の特定の列だけを選択できます（多くの列がある場合などに効果的です）。

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

別の表を選択することもできます。

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

データベース内の他のテーブルにジョインすることも可能です。

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

カスタムのwhere引数を設定できます（このwhere文内ではパラメータを設定できません）

```php
$user->where('id=1 AND name="demo"')->find();
```

**セキュリティ注意** - `$user->where("id = '{$id}' AND name = '{$name}'")->find();`のようなことをしたくなるかもしれません。**絶対にこれをしないでください！**これはSQLインジェクション攻撃に対して脆弱です。インターネットに多くの記事があるので、オンラインで「SQLインジェクション攻撃 PHP」と検索してみてください。このライブラリを使用する場合、この`where()`メソッドではなく、`$user->eq('id', $id)->eq('name', $name)->find();`のようにするのが適切です。必要な場合にのみ、`PDO`ライブラリには`$pdo->quote($var)`があり、あなたに代わってエスケープしてくれます。`quote()`を使用してから`where()`ステートメント内で使用できます。

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

結果を特定の条件でグループ化します。

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

クエリ結果を特定の方法でソートします。

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

返されるレコード数を制限します。2番目のintを指定すると、オフセットとリミットがSQLと同