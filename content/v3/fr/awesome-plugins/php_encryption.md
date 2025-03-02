# Chiffrement PHP

[defuse/php-encryption](https://github.com/defuse/php-encryption) est une bibliothèque qui peut être utilisée pour chiffrer et déchiffrer des données. Il est assez simple de commencer à chiffrer et déchiffrer des données. Ils ont un [didacticiel](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md) qui aide à expliquer les bases de l'utilisation de la bibliothèque ainsi que les implications importantes en matière de sécurité concernant le chiffrement.

## Installation

L'installation est simple avec Composer.

```bash
composer require defuse/php-encryption
```

## Configuration

Ensuite, vous devrez générer une clé de chiffrement.

```bash
vendor/bin/generate-defuse-key
```

Cela affichera une clé que vous devrez conserver en sécurité. Vous pourriez conserver la clé dans votre fichier `app/config/config.php` dans le tableau en bas du fichier. Ce n'est pas l'endroit parfait, mais c'est au moins quelque chose.

## Utilisation

Maintenant que vous avez la bibliothèque et une clé de chiffrement, vous pouvez commencer à chiffrer et déchiffrer des données.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Défini dans votre fichier bootstrap ou public/index.php
 */

// Méthode de chiffrement
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key'] or a file_get_contents of where you put the key */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// Méthode de déchiffrement
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key'] or a file_get_contents of where you put the key */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Une attaque ! Soit la mauvaise clé a été chargée, soit le texte chiffré a
		// changé depuis sa création -- corrompu dans la base de données ou
		// intentionnellement modifié par Eve essayant de mener une attaque.

		// ... gérer ce cas de manière adaptée à votre application ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('Ceci est un secret');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // Obtenir les données chiffrées de quelque part
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```