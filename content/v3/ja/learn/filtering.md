# フィルタリング

## 概要

Flight は、[マップされたメソッド](/learn/extending) が呼び出される前と後にフィルタリングを許可します。

## 理解
覚える必要のある事前定義されたフックはありません。デフォルトのフレームワーク メソッドのいずれか、またはマップしたカスタム メソッドのいずれかをフィルタリングできます。

フィルター関数は以下のようになります：

```php
/**
 * @param array $params The parameters passed to the method being filtered.
 * @param string $output (v2 output buffering only) The output of the method being filtered.
 * @return bool Return true/void or don't return to continue the chain, false to break the chain.
 */
function (array &$params, string &$output): bool {
  // Filter code
}
```

渡された変数を使用して、入力パラメータと/または出力を操作できます。

メソッドの前にフィルターを実行するには：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Do something
});
```

メソッドの後にフィルターを実行するには：

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Do something
});
```

任意のメソッドに必要な数のフィルターを追加できます。それらは宣言された順序で呼び出されます。

フィルタリング プロセスの例を以下に示します：

```php
// Map a custom method
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Add a before filter
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulate the parameter
  $params[0] = 'Fred';
  return true;
});

// Add an after filter
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulate the output
  $output .= " Have a nice day!";
  return true;
});

// Invoke the custom method
echo Flight::hello('Bob');
```

これは以下を表示するはずです：

```
Hello Fred! Have a nice day!
```

複数のフィルターを定義している場合、フィルター関数のいずれかで `false` を返すことでチェーンを中断できます：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // This will end the chain
  return false;
});

// This will not get called
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Note:** Core methods such as `map` and `register` cannot be filtered because they
are called directly and not invoked dynamically. See [Extending Flight](/learn/extending) for more information.

## 関連項目
- [Extending Flight](/learn/extending)

## トラブルシューティング
- チェーンを停止したい場合、フィルター関数から `false` を返すようにしてください。何も返さない場合、チェーンは続行されます。

## 変更履歴
- v2.0 - 初回リリース。