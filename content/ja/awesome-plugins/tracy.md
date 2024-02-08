# Tracy

Tracy は Flight と一緒に使用できる素晴らしいエラーハンドラです。アプリケーションのデバッグに役立つ数々のパネルがあります。拡張して独自のパネルを追加するのも非常に簡単です。Flight チームは、[flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) プラグイン用にいくつかのパネルを作成しました。

## インストール

Composer でインストールします。Tracy は本番用のエラーハンドリングコンポーネントが付属しているため、実際には dev バージョンなしでインストールする必要があります。

```bash
composer require tracy/tracy
```

## 基本設定

開始するための基本的な設定オプションがあります。詳細については、[Tracy ドキュメント](https://tracy.nette.org/en/configuring) を参照してください。

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Tracy を有効にする
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // 明示する必要がある場合もあります（Debugger::PRODUCTION も同様）
// Debugger::enable('23.75.345.200'); // IP アドレスの配列を提供することもできます

// ここにエラーと例外が記録されます。このディレクトリが存在し、書き込み可能であることを確認してください。
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // すべてのエラーを表示
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // ディプリケートされた通知を除くすべてのエラー
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // Debugger バーが表示されている場合、Flight によって content-length が設定できません。

	// これは Flight 用の Tracy 拡張機能に固有のものです。これを含めた場合は有効にしてください。
	new TracyExtensionLoader($app);
}
```

## 便利なヒント

コードのデバッグ中に、データを出力するための非常に役立つ関数がいくつかあります。

- `bdump($var)` - これにより、変数が Tracy バーにダンプされます（別のパネルで表示されます）。
- `dumpe($var)` - これにより、変数がダンプされ、その後すぐにプログラムが停止します。