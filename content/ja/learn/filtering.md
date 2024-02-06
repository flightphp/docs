# フィルタリング

Flight は、メソッドが呼び出される前と後にフィルタリングを行うことができます。覚える必要のある事前定義されたフックはありません。デフォルトのフレームワークメソッドやマップしたカスタムメソッドのいずれもフィルタリングすることができます。

フィルタ関数は次のように見えます:

```php
function (array &$params, string &$output): bool {
  // フィルタコード
}
```

渡された変数を使用して、入力パラメータや出力を操作することができます。

メソッドの前にフィルタを実行するには、次のようにします:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // 何かする
});
```

メソッドの後にフィルタを実行するには、次のようにします:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // 何かする
});
```

任意のメソッドに対して複数のフィルタを追加することができます。宣言された順に呼び出されます。

以下はフィルタリングプロセスの例です:

```php
// カスタムメソッドをマップする
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// ビフォーフィルタを追加
Flight::before('hello', function (array &$params, string &$output): bool {
  // パラメータを操作する
  $params[0] = 'Fred';
  return true;
});

// アフターフィルタを追加
Flight::after('hello', function (array &$params, string &$output): bool {
  // 出力を操作する
  $output .= " Have a nice day!";
  return true;
});

// カスタムメソッドを呼び出す
echo Flight::hello('Bob');
```

これにより、以下が表示されます:

```
Hello Fred! Have a nice day!
```

複数のフィルタを定義した場合、いずれかのフィルタ関数で `false` を返すことで、チェーンを途切れさせることができます:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // これにより、チェーンが終了します
  return false;
});

// これは呼び出されません
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

`map` や `register` などのコアメソッドは、直接呼び出され、動的には呼び出されないため、フィルタリングすることはできません。