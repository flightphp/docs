# v3 への移行

後方互換性は主に維持されていますが、v2 から v3 への移行時に注意すべきいくつかの変更点があります。これらの変更は、デザインパターンとあまりにも対立するため、いくつかの調整が必要でした。

## 出力バッファリングの動作

_v3.5.0_

[出力バッファリング](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) は、PHP スクリプトによって生成された出力がクライアントに送信される前に、PHP 内部のバッファに保存されるプロセスです。これにより、出力がクライアントに送信される前にそれを変更できます。

MVC アプリケーションでは、Controller が「マネージャー」であり、view が何をするかを管理します。Controller の外（または Flight の場合、時には匿名関数）で出力が生成されることは、MVC パターンを破ります。この変更は、MVC パターンに沿うようにし、フレームワークをより予測しやすく使いやすくするためのものです。

v2 では、出力バッファリングは一貫して自身の出力バッファを閉じない方法で処理されており、これが [ユニットテスト](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 
および [ストリーミング](https://github.com/flightphp/core/issues/413) をより困難にしました。ほとんどのユーザーにとって、この変更は実際には影響を与えない可能性があります。ただし、コールバックやコントローラーの外（例: フック内）でコンテンツを出力している場合、問題が発生する可能性が高いです。フック内でコンテンツを出力したり、フレームワークが実際に実行される前に出力したりすることは、過去には動作したかもしれませんが、今後は動作しません。

### 問題が発生する可能性のある箇所
```php
// index.php
require 'vendor/autoload.php';

// 例です
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// これは実際に問題ありません
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// このようなものはエラーを引き起こします
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// これは実際に問題ありません
	echo 'Hello World';

	// これも問題ないはずです
	Flight::hello();
});

Flight::after('start', function(){
	// これはエラーを引き起こします
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2 レンダリング動作の有効化

古いコードを書き換えずに v3 で動作させることはまだ可能ですか？ はい、可能です！ `flight.v2.output_buffering` 構成オプションを `true` に設定することで、v2 レンダリング動作を有効にできます。これにより、古いレンダリング動作を継続して使用できますが、今後修正することを推奨します。フレームワークの v4 では、これが削除されます。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 今度はこれも問題ありません
	echo '<html><head><title>My Page</title></head><body>';
});

// さらにコード
```

## ディスパッチャーの変更

_v3.7.0_

`Dispatcher` の静的メソッド、例えば `Dispatcher::invokeMethod()`、`Dispatcher::execute()` などを直接呼び出していた場合、これらのメソッドを直接呼び出さないようにコードを更新する必要があります。`Dispatcher` は、よりオブジェクト指向的に変更され、Dependency Injection コンテナをより簡単に使用できるようにされました。Dispatcher のようにメソッドを呼び出す必要がある場合、手動で `$result = $class->$method(...$params);` や `call_user_func_array()` を使用できます。

## `halt()` `stop()` `redirect()` および `error()` の変更

_v3.10.0_

3.10.0 以前のデフォルト動作は、ヘッダーとレスポンスボディの両方をクリアするものでした。これを、レスポンスボディのみをクリアするように変更しました。ヘッダーもクリアする必要がある場合、`Flight::response()->clear()` を使用できます。