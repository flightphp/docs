# PHP Encryption

[defuse/php-encryption](https://github.com/defuse/php-encryption) is a library that can be used to encrypt and decrypt data. Getting up and running is fairly simple to start encrypting and decrypting data. They have a great [tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md) that helps explain the basics of how to use the library as well as important security implications regarding encryption.

## Installation

Installation is simple with composer.

```bash
composer require defuse/php-encryption
```

## Setup

Then you'll need to generate an encryption key.

```bash
vendor/bin/generate-defuse-key
```

 This will spit out a key that you'll need to keep safe. You could keep the key in your `app/config/config.php` file in the array at the bottom of the file. While it's not the perfect spot, it's at least something.

## Usage

Now that you have the library and an encryption key, you can start encrypting and decrypting data.

```php

use Defuse\Crypto\Crypto;

// Encryption method
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key'] or a file_get_contents of where you put the key */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// Decryption method
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key'] or a file_get_contents of where you put the key */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// An attack! Either the wrong key was loaded, or the ciphertext has
		// changed since it was created -- either corrupted in the database or
		// intentionally modified by Eve trying to carry out an attack.

		// ... handle this case in a way that's suitable to your application ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('This is a secret');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // Get the encrypted data from somewhere
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```