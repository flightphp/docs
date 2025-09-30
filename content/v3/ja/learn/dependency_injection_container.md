# 依存性注入コンテナ

## 概要

依存性注入コンテナ (DIC) は、アプリケーションの依存関係を管理するための強力な拡張機能です。

## 理解

依存性注入 (DI) は、現代の PHP フレームワークにおける重要な概念であり、オブジェクトのインスタンス化と設定を管理するために使用されます。DIC ライブラリの例として、[flightphp/container](https://github.com/flightphp/container)、[Dice](https://r.je/dice)、[Pimple](https://pimple.symfony.com/)、[PHP-DI](http://php-di.org/)、[league/container](https://container.thephpleague.com/) があります。

DIC は、クラスを作成し管理するための中央集権的な方法を提供する洗練された方法です。これは、同じオブジェクトを複数のクラス（例: コントローラーやミドルウェア）に渡す必要がある場合に便利です。

## 基本的な使用方法

従来の方法は次のようになります：
```php

require 'vendor/autoload.php';

// データベースからユーザーを管理するクラス
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// routes.php ファイル内の記述

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// 他の UserController ルート...

Flight::start();
```

上記のコードでは、新しい `PDO` オブジェクトを作成し、`UserController` クラスに渡しているのがわかります。小規模なアプリケーションではこれで問題ありませんが、アプリケーションが成長するにつれて、同じ `PDO` オブジェクトを複数の場所で作成したり渡したりする必要が出てきます。ここで DIC が役立ちます。

DIC を使用した同じ例（Dice を使用）：
```php

require 'vendor/autoload.php';

// 上記と同じクラス。何も変更なし
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// 新しいコンテナを作成
$container = new \Dice\Dice;

// コンテナが PDO オブジェクトをどのように作成するかを指示するルールを追加
// 以下のように自身に再代入することを忘れずに！
$container = $container->addRule('PDO', [
	// shared は、同じオブジェクトが毎回返されることを意味します
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// これにより、Flight がコンテナを使用することを知るためのコンテナハンドラを登録します。
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// これでコンテナを使用して UserController を作成できます
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

例に多くの追加コードがあると思われるかもしれません。魔法は、`PDO` オブジェクトを必要とする別のコントローラーがある場合に現れます。

```php

// すべてのコントローラーが PDO オブジェクトを必要とするコンストラクタを持っている場合
// 以下の各ルートに自動的に注入されます!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

DIC を利用する追加の利点は、ユニットテストがはるかに簡単になることです。モックオブジェクトを作成し、クラスに渡すことができます。これは、アプリケーションのテストを書く際に大きな利点です！

### 中央集権的な DIC ハンドラの作成

[拡張](/learn/extending) により、アプリケーションを拡張することで、services ファイルに中央集権的な DIC ハンドラを作成できます。例は以下の通りです：

```php
// services.php

// 新しいコンテナを作成
$container = new \Dice\Dice;
// 以下のように自身に再代入することを忘れずに！
$container = $container->addRule('PDO', [
	// shared は、同じオブジェクトが毎回返されることを意味します
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// これで任意のオブジェクトを作成するためのマッピング可能なメソッドを作成できます。
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// これにより、Flight がコントローラー/ミドルウェアで使用することを知るためのコンテナハンドラを登録します
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// コンストラクタで PDO オブジェクトを受け取るサンプルクラスがあると仮定します
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// メールを送信するコード
	}
}

// 最後に、依存性注入を使用してオブジェクトを作成できます
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight には、依存性注入を処理するためのシンプルな PSR-11 準拠コンテナを提供するプラグインがあります。使用方法の簡単な例は以下の通りです：

```php

// 例: index.php
require 'vendor/autoload.php';

use flight\Container;

$container = new Container;

$container->set(PDO::class, fn(): PDO => new PDO('sqlite::memory:'));

Flight::registerContainerHandler([$container, 'get']);

class TestController {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function index() {
    var_dump($this->pdo);
	// これを正しく出力します！
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### flightphp/container の高度な使用方法

依存関係を再帰的に解決することもできます。例は以下の通りです：

```php
<?php

require 'vendor/autoload.php';

use flight\Container;

class User {}

interface UserRepository {
  function find(int $id): ?User;
}

class PdoUserRepository implements UserRepository {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function find(int $id): ?User {
    // 実装 ...
    return null;
  }
}

$container = new Container;

$container->set(PDO::class, static fn(): PDO => new PDO('sqlite::memory:'));
$container->set(UserRepository::class, PdoUserRepository::class);

$userRepository = $container->get(UserRepository::class);
var_dump($userRepository);

/*
object(PdoUserRepository)#4 (1) {
  ["pdo":"PdoUserRepository":private]=>
  object(PDO)#3 (0) {
  }
}
 */
```

### DICE

独自の DIC ハンドラを作成することもできます。これは、PSR-11 (Dice) ではないカスタムコンテナを使用したい場合に便利です。[基本的な使用方法](#basic-usage) セクションでその方法を確認してください。

さらに、Flight を使用する際に生活を楽にするいくつかの便利なデフォルトがあります。

#### Engine インスタンス

コントローラー/ミドルウェアで `Engine` インスタンスを使用している場合の設定方法は以下の通りです：

```php

// ブートストラップファイルのどこかで
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// ここでインスタンスを渡します
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// これでコントローラー/ミドルウェアで Engine インスタンスを使用できます

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### 他のクラスの追加

コンテナに追加したい他のクラスがある場合、Dice ではコンテナによって自動的に解決されるため簡単です。例は以下の通りです：

```php

$container = new \Dice\Dice;
// クラスに依存関係を注入する必要がない場合
// 何も定義する必要はありません！
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'thing';
	}
}

class UserController {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/user', 'UserController->index');
```

### PSR-11

Flight は PSR-11 準拠の任意のコンテナも使用できます。つまり、PSR-11 インターフェースを実装する任意のコンテナを使用できます。League の PSR-11 コンテナを使用した例は以下の通りです：

```php

require 'vendor/autoload.php';

// 上記と同じ UserController クラス

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('user')
	->addArgument('pass');
Flight::registerContainerHandler($container);

Flight::route('/user', [ 'UserController', 'view' ]);

Flight::start();
```

前の Dice の例よりも少し冗長ですが、同じ利点で仕事をしてくれます！

## 関連項目
- [Flight の拡張](/learn/extending) - フレームワークを拡張して独自のクラスに依存性注入を追加する方法を学びます。
- [設定](/learn/configuration) - アプリケーション向けに Flight を設定する方法を学びます。
- [ルーティング](/learn/routing) - アプリケーションのルートを定義する方法と、コントローラーでの依存性注入の動作を学びます。
- [ミドルウェア](/learn/middleware) - アプリケーション向けにミドルウェアを作成する方法と、ミドルウェアでの依存性注入の動作を学びます。

## トラブルシューティング
- コンテナに問題がある場合、コンテナに正しいクラス名を渡していることを確認してください。

## 変更履歴
- v3.7.0 - Flight に DIC ハンドラを登録する機能を追加。