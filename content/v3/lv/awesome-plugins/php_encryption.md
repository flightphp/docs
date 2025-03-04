# PHP Šifrēšana

[defuse/php-encryption](https://github.com/defuse/php-encryption) ir bibliotēka, kas var tikt izmantota datu šifrēšanai un atšifrēšanai. Uzsākšana ir diezgan vienkārša, lai sāktu šifrēt un atšifrēt datus. Viņiem ir lielisks [rokasgrāmata](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md), kas palīdz izskaidrot pamatus par to, kā izmantot bibliotēku, kā arī svarīgu drošības aspektu, kas saistīti ar šifrēšanu.

## Instalēšana

Instalēšana ir vienkārša ar komponistu.

```bash
composer require defuse/php-encryption
```

## Iestatījumi

Pēc tam jums būs jāģenerē šifrēšanas atslēga.

```bash
vendor/bin/generate-defuse-key
```

 Tas izvadīs atslēgu, ko būsiet jāsargā. Jūs varētu saglabāt atslēgu savā `app/config/config.php` failā masīvā faila apakšdaļā. Lai gan tas nav ideāla vieta, tas vismaz ir kaut kas.

## Lietošana

Tagad, kad jums ir bibliotēka un šifrēšanas atslēga, varat sākt šifrēt un atšifrēt datus.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Uzstādiet savā bootstrap vai public/index.php failā
 */

// Šifrēšanas metode
Flight::map('encrypt', function($sastāvs_dati) {
	$šifrēšanas_atslēga = /* $config['encryption_key'] vai file_get_contents no vietas, kur likāt atslēgu */;
	return Crypto::encrypt($sastāvs_dati, Key::loadFromAsciiSafeString($šifrēšanas_atslēga));
});

// Atšifrēšanas metode
Flight::map('decrypt', function($šifrētie_dati) {
	$šifrēšanas_atslēga = /* $config['encryption_key'] vai file_get_contents no vietas, kur likāt atslēgu */;
	try {
		$sastāvs_dati = Crypto::decrypt($šifrētie_dati, Key::loadFromAsciiSafeString($šifrēšanas_atslēga));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Uzbrukums! Vai nu tika ielādēta nepareizā atslēga, vai arī šifrētais teksts ir mainījies, kopš tas tika izveidots - vai nu sabojājies datu bāzē vai nodomāti modificējis Eva, mēģinot veikt uzbrukumu.

		// ... apstrādājiet šo gadījumu tādā veidā, kas ir piemērots jūsu lietojumprogrammai ...
	}
	return $sastāvs_dati;
});

Flight::route('/encrypt', function() {
	$šifrētie_dati = Flight::encrypt('Šis ir noslēpums');
	echo $šifrētie_dati;
});

Flight::route('/decrypt', function() {
	$šifrētie_dati = '...'; // Iegūstiet šifrētos datus no kaut kurienes
	$atšifrētie_dati = Flight::decrypt($šifrētie_dati);
	echo $atšifrētie_dati;
});
```