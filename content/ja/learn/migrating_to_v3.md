# v3への移行

後方互換性は大部分維持されていますが、v2からv3に移行する際に注意すべき変更点がいくつかあります。

## 出力バッファリングの動作（3.5.0）

[出力バッファリング](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) は、PHPスクリプトによって生成された出力がクライアントに送信される前に PHP 内部のバッファに格納されるプロセスです。これにより、出力をクライアントに送信する前に出力を変更することができます。

MVC アプリケーションでは、コントローラが「マネージャー」として機能し、ビューの動作を管理します。コントローラの外部で出力を生成する（またはFlightsの場合、時には無名関数で）と、MVC パターンが崩れます。この変更は、MVC パターンにより一致し、フレームワークを予測可能で使いやすくするためです。

v2では、出力バッファリングは、自身の出力バッファを一貫して閉じない方法で処理され、[ユニットテスト](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) や [ストリーミング](https://github.com/flightphp/core/issues/413) が困難になりました。ほとんどのユーザーにとって、この変更は実際には影響しません。ただし、コール可能なものやコントローラの外部でコンテンツを出力している場合（例：フック内で）、問題が発生する可能性があります。フック内やフレームワークの実行前にコンテンツをエコーしていた場合は、過去には機能したかもしれませんが、今後は機能しません。

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

### v2のレンダリング動作をオンにする

古いコードをそのままで v3 と連携させるために書き直すことなく、古いレンダリング動作を維持することはできますか？ はい、可能です！ `flight.v2.output_buffering` 構成オプションを `true` に設定することで、v2のレンダリング動作を有効にすることができます。これにより、古いレンダリング動作を引き続き使用できますが、今後は修正することが推奨されます。フレームワークのv4では、これが削除されます。

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

`Dispatcher::invokeMethod()`、`Dispatcher::execute()` などの `Dispatcher` の静的メソッドを直接呼び出している場合、これらのメソッドを直接呼び出さないようにコードを更新する必要があります。`Dispatcher` は、よりオブジェクト指向に変換され、DI コンテナをより簡単に使用できるようになりました。Dispatcherのようにメソッドを呼び出す必要がある場合は、`$result = $class->$method(...$params);` や `call_user_func_array()` のようなものを手動で使用することができます。