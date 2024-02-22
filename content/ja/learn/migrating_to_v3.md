# v3への移行

後方互換性はほとんど保たれていますが、v2からv3へ移行する際に注意すべき変更がいくつかあります。

## 出力バッファリング

[出力バッファリング](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php)とは、PHPスクリプトによって生成された出力がクライアントに送信される前にバッファ（PHP内部）に格納されるプロセスです。これにより、出力がクライアントに送信される前に出力を変更することができます。

MVCアプリケーションにおいて、コントローラーは「マネージャー」として機能し、ビューの動作を管理します。コントローラーの外部（またはFlightsの場合、時々無名関数内）で生成された出力は、MVCパターンを壊してしまいます。この変更は、MVCパターンにより沿うようになり、フレームワークをより予測可能で使いやすくするためのものです。

v2では、出力バッファリングは、独自の出力バッファを一貫して閉じない方法で処理され、[ユニットテスト](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42)や[ストリーミング](https://github.com/flightphp/core/issues/413)がより困難になりました。ほとんどのユーザーにとって、この変更は実際には影響を与えないかもしれません。ただし、コールバックとコントローラーの外でコンテンツをエコーしている場合（たとえばフック内）、問題が発生する可能性があります。フック内やフレームワークの実際の実行より前にコンテンツをエコーすることが過去には機能していたかもしれませんが、今後は機能しません。

### 問題が発生する可能性のある場所
```php
// index.php
require 'vendor/autoload.php';

// 例
define('START_TIME', microtime(true));

function hello() {
	echo 'こんにちは、世界';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// これは実際に問題ありません
	echo '<p>このこんにちは、世界フレーズは「H」という文字によって提供されました</p>';
});

Flight::before('start', function(){
	// これのようなことはエラーを引き起こします
	echo '<html><head><title>私のページ</title></head><body>';
});

Flight::route('/', function(){
	// これは実際には問題ありません
	echo 'こんにちは、世界';

	// これも問題ありません
	Flight::hello();
});

Flight::after('start', function(){
	// こちらはエラーを引き起こします
	echo '<div>あなたのページは'.(microtime(true) - START_TIME).'秒で読み込まれました</div></body></html>';
});
```

### v2のレンダリング動作を有効にする

v3で動作しなくなることなく、古いコードをそのままにしておくことはできますか？はい、できます！ `flight.v2.output_buffering` 構成オプションを `true` に設定することで、v2のレンダリング動作を有効にすることができます。古いレンダリング動作を継続して使用することができますが、将来的に修正することが推奨されています。フレームワークのv4では、このオプションは削除されます。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// これは今では完全に問題ありません
	echo '<html><head><title>私のページ</title></head><body>';
});

// その他のコード
```  