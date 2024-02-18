# v3への移行

ほとんどの場合、後方互換性は維持されていますが、v2からv3への移行時に気をつける必要があるいくつかの変更があります。

## 出力バッファリング

[出力バッファリング](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) は、PHPスクリプトによって生成された出力がクライアントに送信される前にバッファ（PHP内部）に格納されるプロセスです。これにより、クライアントに送信される前に出力を修正することができます。

MVCアプリケーションでは、コントローラーが"マネージャー"であり、ビューの動作を管理します。コントローラーの外部（またはFlightsの場合、時々匿名関数で）生成される出力は、MVCパターンを壊します。この変更は、MVCパターンにより適合し、フレームワークをより予測可能で使いやすくするためです。

v2では、出力バッファリングは、独自の出力バッファを一貫して閉じなかった方法で処理され、[ユニットテスト](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) や [ストリーミング](https://github.com/flightphp/core/issues/413) がより困難になりました。ほとんどのユーザーにとって、この変更は実際には影響を与えないかもしれません。ただし、コールバックやコントローラー以外でコンテンツを出力している場合（たとえば、フック内で）、問題が発生する可能性が高いです。フック内やフレームワークが実際に実行される前にコンテンツを出力すると、過去には動作していたかもしれませんが、今後は動作しません。

### 問題が発生する可能性のある箇所
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
	// これは実際には問題ありません
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// こういったものはエラーを引き起こします
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// これは実際には問題ありません
	echo 'Hello World';

	// これも問題ありません
	Flight::hello();
});

Flight::after('start', function(){
	// これはエラーを引き起こします
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### v2レンダリング動作を有効にする

v3の動作に修正せずに古いコードをそのまま使用することはできますか？ はい、できます！ `flight.v2.output_buffering` 構成オプションを `true` に設定することで、v2のレンダリング動作を有効にすることができます。これにより、古いレンダリング動作を引き続き使用できますが、今後の修正が推奨されています。フレームワークのv4では、これが削除されます。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// これは問題なく動作します
	echo '<html><head><title>My Page</title></head><body>';
});

// もっと多くのコード
```