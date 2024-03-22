# 依存性注入コンテナ

## 導入

依存性注入コンテナ（DIC）は、アプリケーションの依存関係を管理する強力なツールです。これは、現代のPHPフレームワークにおける主要な概念であり、オブジェクトのインスタンス化と構成を管理するために使用されます。DICのライブラリの例には、[Dice](https://r.je/dice)、[Pimple](https://pimple.symfony.com/)、 [PHP-DI](http://php-di.org/)、および [league/container](https://container.thephpleague.com/) があります。

DICは、クラスを中央集権的に作成および管理できるというファンシーな方法です。これは、同じオブジェクトを複数のクラス（たとえば、コントローラ）に渡す必要がある場合に便利です。単純な例がこれをより理解しやすくするかもしれません。

## 基本的な例

従来のやり方は次のように見えるかもしれません：  
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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

上記のコードからわかるように、新しい`PDO`オブジェクトを作成して`UserController`クラスに渡しています。これは小規模なアプリケーションでは問題ありませんが、アプリケーションが成長すると、複数の場所で同じ`PDO`オブジェクトを作成していることに気づくでしょう。ここでDICが役立ちます。

以下は、DICを使用した同じ例（Diceを使用）です：  
```php

require 'vendor/autoload.php';

// 上記と同じクラス。何も変更されていません
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
// 以下のように再代入するのを忘れないでください！
$container = $container->addRule('PDO', [
	// shared は、同じオブジェクトが毎回返されることを意味します
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// これにより、Flightがそれを使用することを知るようにコンテナハンドラが登録されます。
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// これでコンテナを使用してUserControllerを作成できます
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// または、次のようにルートを定義することもできます
Flight::route('/user/@id', 'UserController->view');
// または
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```