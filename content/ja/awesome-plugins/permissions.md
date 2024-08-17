# FlightPHP/Permissions

これは、アプリケーション内に複数のロールがあり、各ロールに少しずつ異なる機能がある場合にプロジェクトで使用できる権限モジュールです。このモジュールは、各ロールに対して権限を定義し、その後現在のユーザーが特定のページにアクセスする権限があるか、または特定のアクションを実行する権限があるかを確認できます。

[こちら](https://github.com/flightphp/permissions)をクリックしてGitHubのリポジトリを確認してください。

インストール
-------
`composer require flightphp/permissions` を実行して、準備完了です！

使用方法
-------
まず、権限を設定し、その後アプリケーションに権限がどういう意味なのかを伝える必要があります。最終的には、`$Permissions->has()`、`->can()`、または`is()` で権限を確認します。`has()` と `can()` には同じ機能があるため、コードをより読みやすくするために名前が異なります。

## 基本例

アプリケーションに、ユーザーがログインしているかどうかをチェックする機能があると仮定してください。次のように権限オブジェクトを作成できます：

```php
// index.php
require 'vendor/autoload.php';

// 一部のコード 

// おそらく誰が現在の役割であるかを示すものがあるでしょう
// 多分現在の役割を定義するセッション変数から現在の役割を取得する何かがあるでしょう、
// これはログイン後に、そうでない場合は「guest」または「public」のロールを持っています。
$current_role = 'admin';

// 権限の設定
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// おそらくこのオブジェクトを Flight にある場所に持たせたいと思うでしょう
Flight::set('permission', $permission);
```

次に、どこかのコントローラーには、次のようなものがあるかもしれません。

```php
<?php

// 一部のコントローラー
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// 何かを実行
		} else {
			// 他の処理を実行
		}
	}
}
```

また、この機能を使用して、アプリケーション内で何かを行う権限があるかどうかを追跡することもできます。
たとえば、ソフトウェア上で投稿とやり取りできる方法がある場合、特定のアクションを実行できる権限を持っているかどうかを確認できます。

```php
$current_role = 'admin';

// 権限の設定
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

次に、どこかのコントローラーには...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// 何かを実行
		} else {
			// 他の処理を実行
		}
	}
}
```

## 依存関係の注入
権限を定義するクロージャに依存関係を注入することができます。これは、チェックするデータポイントとしてトグル、ID、その他のデータポイントを持っている場合に便利です。同じことが Class->Method 型の呼び出しでも機能しますが、引数はメソッド内で定義します。

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
		// 何かを実行
	} else {
		// 他の処理を実行
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

## クラスを使用して権限をセットするショートカット
クラスを使用して権限を定義することもできます。コードをきれいに保ちたい場合に便利です。次のように行うことができます：
```php
<?php

// ブートストラップコード
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// 事前に設定したと仮定します
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // 誰でも注文を表示できます
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // マネージャーは注文を作成できます
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // ユーザーが特別なトグルを持っている場合、注文を更新できます
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // 管理者は注文を削除できます
		}
		return $allowed_permissions;
	}
}
```
クールな部分は、メソッドのすべての権限を自動的にマップするショートカットもあることです（これもキャッシュされる可能性があります!!!）。したがって、`order()` と `company()` というメソッドがある場合、`$Permissions->has('order.read')` や `$Permissions->has('company.read')` を実行することができます。これらを定義することは非常に難しいので、ここで一緒にとどまります。これを行うには、次の手順を行う必要があります：

グループ化したい権限クラスを作成します。
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// 権限を決定するためのコード
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// 権限を決定するためのコード
		return $permissions_array;
	}
}
```

次に、このライブラリを使用して権限を検出できるようにします。

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

最後に、コードベースで権限を呼び出して、ユーザーが与えられた権限を実行できるかどうかを確認します。

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('You can\'t create an order. Sorry!');
		}
	}
}
```

### キャッシュ

キャッシュを有効にするには、単純な[wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache)ライブラリを参照してください。これを有効にする例は以下の通りです。
```php

// この $app はあなたのコードの一部である可能性があり、
// コンストラクター内で Flight::app() から取得されるか
// null を渡すと、それがコンストラクター内で取得されます
$app = Flight::app();

// 現時点では、ファイルキャッシュとしてこれを受け入れます。今後他のものも簡単に追加できます。
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // キャッシュする秒数。キャッシュを使用しない場合はこれをオフにしてください
```