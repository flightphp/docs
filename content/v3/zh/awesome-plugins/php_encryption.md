# PHP 加密

[defuse/php-encryption](https://github.com/defuse/php-encryption) 是一个可用于加密和解密数据的库。着手开始加密和解密数据相当简单。他们有一个很棒的[tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md)来帮助解释如何使用该库的基础知识，以及有关加密的重要安全影响。

## 安装

使用 composer 很容易进行安装。

```bash
composer require defuse/php-encryption
```

## 设置

然后，您需要生成一个加密密钥。

```bash
vendor/bin/generate-defuse-key
```

这将输出一个您需要妥善保管的密钥。您可以将密钥保存在您的`app/config/config.php`文件中的数组底部。虽然这不是完美的位置，但至少是一个选项。

## 用法

现在您拥有该库和一个加密密钥，您可以开始加密和解密数据。

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * 在您的引导文件或 public/index.php 中设置
 */

// 加密方法
Flight::map('encrypt', function($原始数据) {
	$加密密钥 = /* $config['encryption_key'] 或者是存放密钥位置的 file_get_contents */;
	return Crypto::encrypt($原始数据, Key::loadFromAsciiSafeString($加密密钥));
});

// 解密方法
Flight::map('decrypt', function($加密数据) {
	$加密密钥 = /* $config['encryption_key'] 或者是存放密钥位置的 file_get_contents */;
	try {
		$原始数据 = Crypto::decrypt($加密数据, Key::loadFromAsciiSafeString($加密密钥));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// 一种攻击！加载了错误的密钥，或者自创建以来，密文已更改--在数据库中已损坏或Eve试图执行攻击时故意修改。

		// ...以适合您的应用程序的方式处理这种情况...
	}
	return $原始数据;
});

Flight::route('/encrypt', function() {
	$加密数据 = Flight::encrypt('这是一个机密');
	echo $加密数据;
});

Flight::route('/decrypt', function() {
	$加密数据 = '...'; // 从某处获取加密数据
	$解密数据 = Flight::decrypt($加密数据);
	echo $解密数据;
});
```