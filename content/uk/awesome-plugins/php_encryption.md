# PHP Шифрування

[defuse/php-encryption](https://github.com/defuse/php-encryption) — це бібліотека, яку можна використовувати для шифрування та дешифрування даних. Запуск і налаштування досить прості, щоб почати шифрування та дешифрування даних. У них є чудовий [посібник](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md), який допомагає пояснити основи використання бібліотеки, а також важливі питання безпеки, пов’язані із шифруванням.

## Встановлення

Встановлення просте за допомогою composer.

```bash
composer require defuse/php-encryption
```

## Налаштування

Потім вам потрібно згенерувати ключ шифрування.

```bash
vendor/bin/generate-defuse-key
```

 Це видасть ключ, який вам потрібно зберегти в безпеці. Ви можете зберегти ключ у вашому `app/config/config.php` файлі в масиві внизу файлу. Хоча це не ідеальне місце, але принаймні щось.

## Використання

Тепер, коли у вас є бібліотека та ключ шифрування, ви можете почати шифрування та дешифрування даних.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Встановіть у вашому bootstrap або public/index.php файлі
 */

// Метод шифрування
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key'] або file_get_contents з того, де ви помістили ключ */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// Метод дешифрування
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key'] або file_get_contents з того, де ви помістили ключ */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Атака! Або був завантажений неправильний ключ, або шифротекст було
		// змінено з моменту його створення — або пошкоджено в базі даних, або
		// навмисно змінено Евой, яка намагається здійснити атаку.

		// ... обробіть цей випадок таким чином, як це підходить для вашого застосунку ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('Це секрет');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // Отримати зашифровані дані звідкись
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```