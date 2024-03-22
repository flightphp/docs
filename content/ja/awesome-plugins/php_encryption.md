## PHP 暗号化

[defuse/php-encryption](https://github.com/defuse/php-encryption) はデータの暗号化と復号を行うために使用できるライブラリです。すぐにデータの暗号化と復号を始めることはかなり簡単です。ライブラリの使用方法や暗号化に関連する重要なセキュリティの問題を説明する素晴らしい[tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md)があります。

## インストール

composerを使用して簡単にインストールします。

```bash
composer require defuse/php-encryption
```

## セットアップ

その後、暗号化キーを生成する必要があります。

```bash
vendor/bin/generate-defuse-key
```

これにより、安全に保持する必要があるキーが生成されます。キーは、ファイルの末尾にある配列内の`app/config/config.php`ファイルに保存できます。完璧な場所ではありませんが、少なくとも何かです。

## 使用方法

ライブラリと暗号化キーがあるので、データの暗号化と復号を開始できます。

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * ブートストラップまたはpublic/index.phpファイルに設定します
 */

// 暗号化メソッド
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key']またはキーを配置した場所のfile_get_contents */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// 復号メソッド
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key']またはキーを配置した場所のfile_get_contents */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// 攻撃! 間違ったキーが読み込まれたか、暗号文が作成されてから変更された可能性があります -- データベースで破損されたか、攻撃を実行しようとするEveによって意図的に変更された可能性があります。

		// ... アプリケーションに適した方法でこのケースを処理します ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('これは秘密です');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // どこかから暗号化されたデータを取得します
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```