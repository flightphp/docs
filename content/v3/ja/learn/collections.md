# Collections

## 概要

Flight の `Collection` クラスは、データセットを管理するための便利なユーティリティです。配列表記とオブジェクト表記を使用してデータにアクセスおよび操作できるため、コードをよりクリーンで柔軟にします。

## 理解

`Collection` は基本的に配列のラッパーですが、いくつかの追加機能があります。配列のように使用でき、ループ処理、項目のカウント、さらにはオブジェクトのプロパティのように項目にアクセスできます。これは、アプリ内で構造化されたデータを渡す場合や、コードを少し読みやすくする場合に特に便利です。

Collections はいくつかの PHP インターフェースを実装しています：
- `ArrayAccess`（配列構文を使用できるため）
- `Iterator`（`foreach` でループできるため）
- `Countable`（`count()` を使用できるため）
- `JsonSerializable`（JSON に簡単に変換できるため）

## 基本的な使用方法

### Collection の作成

コンストラクタに配列を渡すことでコレクションを作成できます：

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### 項目へのアクセス

配列表記またはオブジェクト表記を使用して項目にアクセスできます：

```php
// 配列表記
echo $collection['name']; // 出力: Flight

// オブジェクト表記
echo $collection->version; // 出力: 3
```

存在しないキーにアクセスしようとすると、エラーの代わりに `null` が返されます。

### 項目の設定

どちらの表記でも項目を設定できます：

```php
// 配列表記
$collection['author'] = 'Mike Cao';

// オブジェクト表記
$collection->license = 'MIT';
```

### 項目の確認と削除

項目が存在するかを確認：

```php
if (isset($collection['name'])) {
  // 何かを実行
}

if (isset($collection->version)) {
  // 何かを実行
}
```

項目を削除：

```php
unset($collection['author']);
unset($collection->license);
```

### Collection のイテレーション

Collections はイテラブルなので、`foreach` ループで使用できます：

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### 項目のカウント

コレクション内の項目数をカウントできます：

```php
echo count($collection); // 出力: 4
```

### すべてのキーまたはデータの取得

すべてのキーを取得：

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

すべてのデータを配列として取得：

```php
$data = $collection->getData();
```

### Collection のクリア

すべての項目を削除：

```php
$collection->clear();
```

### JSON シリアライズ

Collections を簡単に JSON に変換できます：

```php
echo json_encode($collection);
// 出力: {"name":"Flight","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## 高度な使用方法

必要に応じて、内部データ配列を完全に置き換えることができます：

```php
$collection->setData(['foo' => 'bar']);
```

Collections は、コンポーネント間で構造化されたデータを渡す場合や、配列データに対してよりオブジェクト指向のインターフェースを提供する場合に特に便利です。

## 関連項目

- [Requests](/learn/requests) - HTTP リクエストの処理方法と、コレクションをリクエストデータを管理するために使用する方法を学習します。
- [PDO Wrapper](/learn/pdo-wrapper) - Flight の PDO ラッパーの使用方法と、コレクションをデータベース結果を管理するために使用する方法を学習します。

## トラブルシューティング

- 存在しないキーにアクセスしようとすると、エラーの代わりに `null` が返されます。
- コレクションは再帰的ではありません：ネストされた配列は自動的にコレクションに変換されません。
- コレクションをリセットする必要がある場合は、`$collection->clear()` または `$collection->setData([])` を使用してください。

## 変更履歴

- v3.0 - 型ヒントの改善と PHP 8+ のサポート。
- v1.0 - Collection クラスの初回リリース。