## Шифрование PHP

[defuse/php-encryption](https://github.com/defuse/php-encryption) - это библиотека, которая может быть использована для шифрования и дешифрования данных. Начать использование довольно просто для начала шифрования и дешифрования данных. У них есть отличное [руководство](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md), которое помогает объяснить основы использования библиотеки, а также важные аспекты безопасности, касающиеся шифрования.

## Установка

Установка проста с помощью композитора.

```bash
composer require defuse/php-encryption
```

## Настройка

Затем вам нужно сгенерировать ключ шифрования.

```bash
vendor/bin/generate-defuse-key
```

Это выдаст ключ, который вам нужно будет хранить в надежном месте. Вы можете сохранить ключ в вашем файле `app/config/config.php` в массиве внизу файла. Хотя это не идеальное место, это хотя бы что-то.

## Использование

Теперь, когда у вас есть библиотека и ключ шифрования, вы можете начать шифровать и дешифровать данные.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Set in your bootstrap or public/index.php file
 */

// Метод шифрования
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key'] or a file_get_contents of where you put the key */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// Метод дешифрования
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key'] or a file_get_contents of where you put the key */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Атака! Загружен неверный ключ или зашифрованный текст был изменен с момента его создания -- либо поврежден в базе данных, либо намеренно изменен Злодеем, пытающимся провести атаку.

		// ... обработайте этот случай так, чтобы он подходил для вашего приложения ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('Это секрет');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // Получите зашифрованные данные откуда-нибудь
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```