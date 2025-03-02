# フィルタリング

Flightは、メソッドが呼び出される前後にフィルタリングを行うことができます。覚える必要のある事前定義されたフックはありません。デフォルトのフレームワークメソッドやマップしたカスタムメソッドのいずれに対してもフィルタリングを行うことができます。

フィルタ関数は次のようになります：

```php
function (array &$params, string &$output): bool {
  // フィルタコード
}
```

渡された変数を使用して、入力パラメータや出力を操作することができます。

メソッドの前にフィルタを実行するには、次のようにします：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // 何かを行う
});
```

メソッドの後にフィルタを実行するには、次のようにします：

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // 何かを行う
});
```

任意のメソッドに複数のフィルタを追加することができます。宣言された順序通りに呼び出されます。

以下はフィルタリングプロセスの例です：

```php
// カスタムメソッドをマップ
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// フィルタを追加
Flight::before('hello', function (array &$params, string &$output): bool {
  // パラメータを操作する
  $params[0] = 'Fred';
  return true;
});

// フィルタを追加
Flight::after('hello', function (array &$params, string &$output): bool {
  // 出力を操作する
  $output .= " Have a nice day!";
  return true;
});

// カスタムメソッドを呼び出す
echo Flight::hello('Bob');
```

これにより次のように表示されます：

```
Hello Fred! Have a nice day!
```

複数のフィルタを定義している場合は、フィルタ関数のいずれかで `false` を返すことで、チェーンを中断することができます：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // これによりチェーンが終了します
  return false;
});

// これは呼び出されません
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

`map` や `register` などのコアメソッドは、直接呼び出されて動的に呼び出されないため、フィルタリングすることはできません。