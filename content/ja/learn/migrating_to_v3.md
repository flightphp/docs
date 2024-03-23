# v3への移行

後方互換性は大部分維持されていますが、v2からv3に移行する際に注意すべき変更がいくつかあります。

## 出力バッファリングの挙動（3.5.0）

[出力バッファリング](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) とは、PHPスクリプトによって生成された出力がクライアントに送信される前にバッファ（PHP内部）に保存されるプロセスのことです。これにより、出力がクライアントに送信される前に出力を変更することができます。

MVCアプリケーションにおいて、コントローラーは「管理者」であり、ビューの動作を管理します。コントローラーの外部（またはFlightの場合、時々匿名関数内）で出力を生成することは、MVCパターンを壊します。この変更はMVCパターンにより適合し、フレームワークをより予測可能で使いやすくするためのものです。

v2では、出力バッファリングは、自身の出力バッファを一貫して閉じていなかったため、[ユニットテスト](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) や [ストリーミング](https://github.com/flightphp/core/issues/413) がより困難になっていました。多くのユーザーにとって、この変更は実際には影響しません。ただし、コール可能なものやコントローラーの外でコンテンツをエコーしている場合（たとえばフック内）、問題が発生する可能性があります。フック内やフレームワークが実際に実行される前にコンテンツをエコーしていた場合は過去には機能していたかもしれませんが、今後は機能しなくなります。

### 問題が発生する可能性のある箇所
```php
// index.php
require 'vendor/autoload.php';

// ただの例です
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// これは実際には問題ありません
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// これのようなものはエラーを引き起こします
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// これは実際には大丈夫です
	echo 'Hello World';

	// これも問題ありません
	Flight::hello();
});

Flight::after('start', function(){
	// これはエラーを引き起こします
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2のレンダリング動作を有効にする

v3と互換性のない書き換えを行わずに、古いコードをそのまま使い続けることはできますか？はい、可能です！ `flight.v2.output_buffering` 構成オプションを `true` に設定することで、v2のレンダリング動作を継続して使用できますが、将来的に修正することが推奨されています。フレームワークのv4では、この機能は削除されます。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// こちらは問題ありません
	echo '<html><head><title>My Page</title></head><body>';
});

// さらにコード 
```

## ディスパッチャーの変更（3.7.0）

直接`ディスパッチャ（Dispatcher）`の静的メソッドを呼び出していた場合、`Dispatcher::invokeMethod()`、`Dispatcher::execute()` など、コードを更新してこれらのメソッドを直接呼び出さないようにする必要があります。`ディスパッチャ（Dispatcher）`はオブジェクト指向に変換されており、より容易に依存性注入コンテナを使用できるようになっています。`Dispatcher`と同様の方法でメソッドを呼び出す必要がある場合は、手動で `$result = $class->$method(...$params);` や `call_user_func_array()` のようなものを使用することができます。