# アップロードされたファイルハンドラー

## 概要

Flight の `UploadedFile` クラスは、アプリケーションでファイルのアップロードを簡単かつ安全に扱うことを可能にします。PHP のファイルアップロードプロセスの詳細をラップし、ファイル情報をアクセスし、アップロードされたファイルを移動するためのシンプルでオブジェクト指向の方法を提供します。

## 理解

ユーザーがフォーム経由でファイルをアップロードすると、PHP は `$_FILES` スーパーグローバルにファイルに関する情報を格納します。Flight では、`$_FILES` に直接アクセスすることはほとんどありません。代わりに、Flight の `Request` オブジェクト（`Flight::request()` 経由でアクセス可能）が `getUploadedFiles()` メソッドを提供し、`UploadedFile` オブジェクトの配列を返します。これにより、ファイルの扱いがはるかに便利で堅牢になります。

`UploadedFile` クラスは以下のメソッドを提供します：
- オリジナルのファイル名、MIME タイプ、サイズ、一時的な場所を取得する
- アップロードエラーをチェックする
- アップロードされたファイルを永続的な場所に移動する

このクラスは、ファイルアップロードの一般的な落とし穴（エラーの扱いやファイルの安全な移動など）を避けるのに役立ちます。

## 基本的な使用方法

### リクエストからアップロードされたファイルにアクセスする

アップロードされたファイルにアクセスする推奨される方法は、リクエストオブジェクト経由です：

```php
Flight::route('POST /upload', function() {
    // <input type="file" name="myFile"> という名前のフォームフィールドの場合
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // これで UploadedFile メソッドを使用できます
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed: " . $file->getError();
    }
});
```

### 複数のファイルアップロードの扱い

フォームが `name="myFiles[]"` を使用して複数のアップロードを行う場合、`UploadedFile` オブジェクトの配列が得られます：

```php
Flight::route('POST /upload', function() {
    // <input type="file" name="myFiles[]"> という名前のフォームフィールドの場合
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Uploaded: " . $file->getClientFilename() . "<br>";
        } else {
            echo "Failed to upload: " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### UploadedFile インスタンスを手動で作成する

通常、`UploadedFile` を手動で作成することはありませんが、必要に応じて作成できます：

```php
use flight\net\UploadedFile;

$file = new UploadedFile(
  $_FILES['myfile']['name'],
  $_FILES['myfile']['type'],
  $_FILES['myfile']['size'],
  $_FILES['myfile']['tmp_name'],
  $_FILES['myfile']['error']
);
```

### ファイル情報のアクセス

アップロードされたファイルの詳細を簡単に取得できます：

```php
echo $file->getClientFilename();   // ユーザーのコンピューターからのオリジナルのファイル名
echo $file->getClientMediaType();  // MIME タイプ（例: image/png）
echo $file->getSize();             // バイト単位のファイルサイズ
echo $file->getTempName();         // サーバー上のテンポラリファイルパス
echo $file->getError();            // アップロードエラーコード（0 はエラーなし）
```

### アップロードされたファイルの移動

ファイルを検証した後、永続的な場所に移動します：

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "File uploaded successfully!";
} catch (Exception $e) {
  echo "Upload failed: " . $e->getMessage();
}
```

`moveTo()` メソッドは、何かがうまくいかない場合（アップロードエラーや権限の問題など）に例外をスローします。

### アップロードエラーの扱い

アップロード中に問題が発生した場合、人間が読めるエラーメッセージを取得できます：

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // エラーコードを使用するか、moveTo() からの例外をキャッチできます
  echo "There was an error uploading the file.";
}
```

## 関連項目

- [Requests](/learn/requests) - HTTP リクエストからアップロードされたファイルにアクセスする方法を学び、ファイルアップロードの例をさらに見てみましょう。
- [Configuration](/learn/configuration) - PHP でアップロード制限とディレクトリを設定する方法。
- [Extending](/learn/extending) - Flight のコアクラスをカスタマイズまたは拡張する方法。

## トラブルシューティング

- ファイルを移動する前に常に `$file->getError()` をチェックしてください。
- アップロードディレクトリがウェブサーバーによって書き込み可能であることを確認してください。
- `moveTo()` が失敗した場合、詳細のために例外メッセージを確認してください。
- PHP の `upload_max_filesize` と `post_max_size` 設定はファイルアップロードを制限できます。
- 複数のファイルアップロードの場合、常に `UploadedFile` オブジェクトの配列をループしてください。

## 変更履歴

- v3.12.0 - リクエストオブジェクトに `UploadedFile` クラスを追加し、ファイルの扱いを容易にしました。