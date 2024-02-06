# Tracy

Tracyは、Flightと一緒に使用できる素晴らしいエラーハンドラです。アプリケーションのデバッグを支援するためのいくつかのパネルがあります。拡張や独自のパネルの追加も非常に簡単です。Flightチームは、[flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) プラグインを使用してFlightプロジェクト向けにいくつかのパネルを作成しました。

## インストール

Composerを使用してインストールします。Tracyはプロダクションエラーハンドリングコンポーネントとして提供されるため、実際に開発バージョンなしでインストールする必要があります。

```bash
composer require tracy/tracy
```

## 基本的な設定

開始するためのいくつかの基本的な設定オプションがあります。詳細については、[Tracy Documentation](https://tracy.nette.org/en/configuring) を読んでください。

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Tracyを有効にする
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // 明示する必要がある場合もあります(Debugger::PRODUCTIONも同様)
// Debugger::enable('23.75.345.200'); // IPアドレスの配列を指定することもできます

// ここでエラーと例外が記録されます。このディレクトリが存在し、書き込み可能であることを確認してください。
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // すべてのエラーを表示する
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // deprecated noticesを除くすべてのエラー
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // Debuggerバーが表示されている場合、Flightによってcontent-lengthを設定できない

	// これは、Tracy Extension for Flightに固有のもので、それを含めた場合はコメントアウトしてください。
	new TracyExtensionLoader($app);
}
```

## 便利なヒント

コードのデバッグ中に、データを出力するための非常に役立つ関数がいくつかあります。

- `bdump($var)` - これにより変数がTracy Barにダンプされ、別のパネルに表示されます。
- `dumpe($var)` - これにより変数がダンプされ、その後すぐにプログラムが終了します。