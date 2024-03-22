# PHP-Verschlüsselung

[defuse/php-encryption](https://github.com/defuse/php-encryption) ist eine Bibliothek, die zum Verschlüsseln und Entschlüsseln von Daten verwendet werden kann. Das Einrichten und Starten ist ziemlich einfach, um mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen. Sie haben ein großartiges [Tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md), das dabei hilft, die Grundlagen zur Verwendung der Bibliothek sowie wichtige Sicherheitsaspekte in Bezug auf Verschlüsselung zu erklären.

## Installation

Die Installation ist einfach mit Composer.

```bash
composer require defuse/php-encryption
```

## Einrichtung

Dann müssen Sie einen Verschlüsselungsschlüssel generieren.

```bash
vendor/bin/generate-defuse-key
```

Das wird einen Schlüssel ausgeben, den Sie sicher aufbewahren müssen. Sie könnten den Schlüssel in Ihrer `app/config/config.php`-Datei im Array am Ende der Datei aufbewahren. Auch wenn es nicht der perfekte Ort ist, ist es zumindest etwas.

## Verwendung

Nun, da Sie die Bibliothek und einen Verschlüsselungsschlüssel haben, können Sie damit beginnen, Daten zu verschlüsseln und zu entschlüsseln.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * In Ihrer Bootstrap- oder public/index.php-Datei festlegen
 */

// Verschlüsselungsmethode
Flight::map('encrypt', function($rohdaten) {
	$verschlüsselungsschlüssel = /* $config['encryption_key'] oder ein file_get_contents davon, wo Sie den Schlüssel platziert haben */;
	return Crypto::encrypt($rohdaten, Key::loadFromAsciiSafeString($verschlüsselungsschlüssel));
});

// Entschlüsselungsmethode
Flight::map('decrypt', function($verschlüsselte_daten) {
	$verschlüsselungsschlüssel = /* $config['encryption_key'] oder ein file_get_contents davon, wo Sie den Schlüssel platziert haben */;
	try {
		$rohdaten = Crypto::decrypt($verschlüsselte_daten, Key::loadFromAsciiSafeString($verschlüsselungsschlüssel));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Ein Angriff! Entweder der falsche Schlüssel wurde geladen oder der Geheimtext hat sich seit seiner Erstellung geändert -- entweder in der Datenbank korrupt oder absichtlich von Eve modifiziert, um einen Angriff durchzuführen.

		// ... diesen Fall auf eine Art und Weise behandeln, die für Ihre Anwendung geeignet ist ...
	}
	return $rohdaten;
});

Flight::route('/encrypt', function() {
	$verschlüsselte_daten = Flight::encrypt('Das ist ein Geheimnis');
	echo $verschlüsselte_daten;
});

Flight::route('/decrypt', function() {
	$verschlüsselte_daten = '...'; // Verschlüsselte Daten von irgendwoher erhalten
	$entschlüsselte_daten = Flight::decrypt($verschlüsselte_daten);
	echo $entschlüsselte_daten;
});
```