# FlightPHP/Permissions

これは、複数の役割があり、各役割ごとに異なる機能がある場合にプロジェクトで使用できる権限モジュールです。このモジュールを使用すると、各役割に対する権限を定義し、現在のユーザーが特定のページにアクセスしたり特定のアクションを実行する権限があるかどうかをチェックすることができます。

インストール
-------
`composer require flightphp/permissions` を実行すると、すぐに始められます！

使用法
-------
まず、権限を設定し、その後アプリにその権限が何を意味するかを伝える必要があります。最終的には、`$Permissions->has()`、`->can()`、または`is()`で権限をチェックします。`has()` と`can()` は同機能ですが、コードを読みやすくするために異なる名前が付けられています。

## 基本的な例

アプリケーションにログインしているユーザーかどうかをチェックする機能があるとします。次のように権限オブジェクトを作成できます：

```php
// index.php
require 'vendor/autoload.php';

// いくつかのコード

// おそらく、現在の役割を教えてくれる何かがあるでしょう
// おそらく、現在の役割を定義するセッション変数から現在の役割を取得する何かがあるでしょう
// ログインした後、さもなければ 'guest' または 'public' の役割になります。
$current_role = 'admin';

// 権限を設定
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// おそらくこのオブジェクトをどこかのFlight内に永続化したいと思うでしょう
Flight::set('permission', $permission);
```

その後、どこかのコントローラーに次のようなものがあるかもしれません。

```php
<?php

// いくつかのコントローラー
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// 何かをする
		} else {
			// 他の何かをする
		}
	}
}
```

また、アプリケーション内で特定のことを行う権限があるかどうかを追跡するのにも使用できます。
たとえば、ユーザーがソフトウェア上で投稿とやりとりできる方法がある場合、特定のアクションを実行する権限があるかどうかをチェックできます。

```php
$current_role = 'admin';

// 権限を設定
$permission = new \flight\Permission($current_role);
$permission->defineRule('post', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['create', 'read', 'update', 'delete'];
	} else if($current_role === 'editor') {
		$permissions = ['create', 'read', 'update'];
	} else if($current_role === 'author') {
		$permissions = ['create', 'read'];
	} else if($current_role === 'contributor') {
		$permissions = ['create'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('permission', $permission);
```

その後、どこかのコントローラーで...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// 何かをする
		} else {
			// 他の何かをする
		}
	}
}
```

## 依存関係の注入
権限を定義するクロージャに依存関係を注入することができます。これは、チェックしたいトグル、ID、またはその他のデータポイントがある場合に便利です。同様に、Class->Method 型の呼び出しにも適用されますが、メソッド内で引数を定義します。

### クロージャ

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... コード
});

// コントローラーファイル内
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// 何かをする
	} else {
		// 他の何かをする
	}
}
```

### クラス

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... コード
	}
}
```

## クラスを使用して権限を設定するショートカット
クラスを使用して権限を定義することもできます。コードを整理したい場合に便利です。次のように行うことができます：

```php
<?php

// ブートストラップコード
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// 事前に設定したと仮定
	/** @var \flight\database\PdoWrapper $db */
	$db = Flight::db();
	$allowed_permissions = [ 'read' ]; // すべての人が注文を表示できる
	if($current_role === 'manager') {
		$allowed_permissions[] = 'create'; // マネージャーは注文を作成できる
	}
	$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
	if($some_special_toggle_from_db) {
		$allowed_permissions[] = 'update'; // ユーザーが特別なトグルを持っている場合、注文を更新できる
	}
	if($current_role === 'admin') {
		$allowed_permissions[] = 'delete'; // 管理者は注文を削除できる
	}
	return $allowed_permissions;
}
}
```

Coolな部分は、クラス内のすべてのメソッドを権限にマップできるショートカットもあることです（キャッシュも可能！）。`order()` や `company()` などというメソッド名がある場合、これらは自動的にマップされるため、`$Permissions->has('order.read')` や `$Permissions->has('company.read')` を実行できます。これを定義することは非常に難しいので、ここで一緒に行動しましょう。以下のようにします：

グループ化する権限クラスを作成します。

```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// 権限を決定するコード
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// 権限を決定するコード
		return $permissions_array;
	}
}
```

次に、このライブラリを使用して権限を見つけられるようにします。

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

最後に、コードベースで権限を呼び出してユーザーが与えられた権限を実行できるかどうかをチェックします。

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('注文を作成できません。申し訳ありません！');
		}
	}
}
```

### キャッシュ

キャッシュを有効にするには、簡単な[wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) ライブラリを参照してください。以下に、これを有効にする例を示します。

```php

// この $app はあなたのコードの一部である可能性があります。または
// null を渡すことができ、コンストラクタでFlight::app() から取得します
$app = Flight::app();

// 現時点では、ファイルキャッシュとしてこれを受け入れます。他のものも将来簡単に追加できます
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 はこれをキャッシュする秒数です。キャッシュを使用しない場合はこれを省略してください
```

以上です！