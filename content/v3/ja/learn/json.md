# JSON Wrapper

## Overview

Flightの`Json`クラスは、アプリケーションでJSONデータをエンコードおよびデコードするためのシンプルで一貫した方法を提供します。PHPのネイティブJSON関数をより良いエラー処理と便利なデフォルト値でラップしており、JSONの使用をより簡単で安全にします。

## Understanding

JSONの使用は、現代のPHPアプリケーションで非常に一般的です。特にAPIの構築やAJAXリクエストの処理時にそうです。`Json`クラスは、すべてのJSONエンコードとデコードを一元化するため、PHPの組み込み関数からの奇妙なエッジケースや暗号めいたエラーについて心配する必要がありません。

主な機能:
- 一貫したエラー処理（失敗時に例外をスロー）
- エンコード/デコードのデフォルトオプション（例: 未エスケープのスラッシュ）
- プリティプリントと検証のためのユーティリティメソッド

## Basic Usage

### データのJSONエンコード

PHPデータをJSON文字列に変換するには、`Json::encode()`を使用します:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Output: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

エンコードが失敗した場合、役立つエラーメッセージ付きの例外が発生します。

### プリティプリント

JSONを人間が読みやすい形式にしたいですか？ `prettyPrint()`を使用します:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### JSON文字列のデコード

JSON文字列をPHPデータに戻すには、`Json::decode()`を使用します:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Output: Flight
```

オブジェクトではなく連想配列が欲しい場合、2番目の引数に`true`を渡します:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Output: Flight
```

デコードが失敗した場合、明確なエラーメッセージ付きの例外が発生します。

### JSONの検証

文字列が有効なJSONかどうかをチェックします:

```php
if (Json::isValid($json)) {
  // 有効です！
} else {
  // 有効なJSONではありません
}
```

### 最後のエラーの取得

ネイティブPHP関数からの最後のJSONエラーメッセージを確認したい場合:

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Last JSON error: $error";
}
```

## Advanced Usage

より多くの制御が必要な場合、エンコードとデコードのオプションをカスタマイズできます（[PHPのjson_encodeオプション](https://www.php.net/manual/en/json.constants.php)を参照）:

```php
// HEX_TAGオプションでエンコード
$json = Json::encode($data, JSON_HEX_TAG);

// カスタム深さでデコード
$data = Json::decode($json, false, 1024);
```

## See Also

- [Collections](/learn/collections) - JSONに簡単に変換できる構造化データとの作業用。
- [Configuration](/learn/configuration) - Flightアプリの設定方法。
- [Extending](/learn/extending) - 独自のユーティリティを追加したり、コアクラスをオーバーライドしたりする方法。

## Troubleshooting

- エンコードまたはデコードが失敗した場合、例外がスローされます。エラーを優雅に処理したい場合は、呼び出しをtry/catchでラップしてください。
- 予期しない結果が得られた場合、データに循環参照や非UTF8文字がないかを確認してください。
- デコード前に`Json::isValid()`を使用して文字列が有効なJSONかをチェックしてください。

## Changelog

- v3.16.0 - JSONラッパーユーティリティクラスを追加。