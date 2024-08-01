# v3への移行

ほとんどの場合、下位互換性は維持されていますが、v2からv3に移行する際に注意すべき変更がいくつかあります。

## 出力バッファリングの動作（3.5.0）

[出力バッファリング](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)は、PHPスクリプトによって生成された出力がクライアントに送信される前にバッファー（PHP内部）に保存されるプロセスです。これにより、出力をクライアントに送信する前に変更できます。

MVCアプリケーションでは、コントローラーが「マネージャー」であり、ビューの動作を管理します。コントローラーの外部（またはFlightの場合、時々無名関数内）で生成された出力は、MVCパターンを壊します。この変更は、MVCパターンにより準拠し、フレームワークを予測可能かつ使いやすくするためです。

v2では、出力バッファリングは、自身の出力バッファーを一貫してクローズしていなかったため、[ユニットテスト](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42)や[ストリーミング](https://github.com/flightphp/core/issues/413)が困難になることがありました。ほとんどのユーザーにとって、この変更は実際には影響しないかもしれません。ただし、コールバックやコントローラーの外部でコンテンツをエコーしている場合（例えば、フック内で）、問題が発生する可能性があります。フック内やフレームワークの実際の実行より前にコンテンツをエコーしても、過去には動作していたかもしれませんが、今後は動作しません。

### 問題が発生する可能性がある場所
```php
// index.php
require 'vendor/autoload.php';

// just an example
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// this will actually be fine
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// things like this will cause an error
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// this is actually just fine
	echo 'Hello World';

	// This should be just fine as well
	Flight::hello();
});

Flight::after('start', function(){
	// this will cause an error
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2のレンダリング動作を有効にする

古いコードを修正せずにv3で機能させるためにはどうすればよいですか？ はい、できます！ `flight.v2.output_buffering`構成オプションを`true`に設定することで、v2のレンダリング動作を有効にできます。これにより、古いレンダリング動作を継続して使用できますが、将来の修正が推奨されています。 フレームワークのv4では、これが削除されます。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Now this will be just fine
	echo '<html><head><title>My Page</title></head><body>';
});

// more code 
```

## ディスパッチャーの変更（3.7.0）

`Dispatcher::invokeMethod()`、`Dispatcher::execute()`などの`Dispatcher`の静的メソッドを直接呼び出している場合、`Dispatcher`がよりオブジェクト指向に変換されたため、これらのメソッドを直接呼び出さないようにコードを更新する必要があります。 依存性注入コンテナをより簡単に使用できるように`Dispatcher`が変更されました。 Dispatcherと同様のメソッドを呼び出す必要がある場合は、手動で`$result = $class->$method(...$params);`または`call_user_func_array()`のようなものを使用することができます。

## `halt()` `stop()` `redirect()` および `error()` の変更（3.10.0）

3.10.0以前のデフォルト動作は、ヘッダーとレスポンスボディの両方をクリアすることでした。これは、レスポンスボディのみをクリアするように変更されました。ヘッダーもクリアする必要がある場合は、`Flight::response()->clear()`を使用できます。