# Ghostff/Sesija

PHP sesiju pārvaldnieks (nenorobežojošs, uzrejamā, segmentēšana, sesiju šifrēšana). Izmanto PHP open_ssl, lai pēc nepieciešamības šifrētu/deshifrētu sesiju datus. Atbalsta Failu, MySQL, Redis un Memcached.

## Instalēšana

Instalē ar komponistu.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav obligāti jāpārsniedz nekas, lai izmantotu noklusējuma iestatījumus ar savu sesiju. Par papildu iestatījumiem varat lasīt [Github Readme](https://github.com/Ghostff/Session).

```php

izmantojiet Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->registret('sesija', Session::class);

// viena lieta, ko atcerēties, ir tā, ka jums katru lapu ielādējot ir jāizpilda sesiju
// vai jums jāpalaiž auto_commit jūsu konfigurācijā.
```

## Vienkāršs piemērs

Šeit ir vienkāršs piemērs, kā jūs varētu to izmantot.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// šeit veiciet savu ielogošanās loģiku
	// validējiet paroli, utt.

	// ja ieraksts veiksmīgs
	$session->set('ir_iebūvēts', true);
	$session->set('lietotājs', $lietotājs);

	// jebkurā laikā, kad ierakstāt sesijā, to jāiepilda apzināti.
	$session->commit();
});

// šis pārbaudījums varētu būt ierobežotas lapas loģikā vai ietīts ar starpniekprogrammu.
Flight::route('/daža-ierobežota-lapa', function() {
	$session = Flight::session();

	if(!$session->get('ir_iebūvēts')) {
		Flight::redirect('/ielogoties');
	}

	// šeit veiciet savu ierobežotas lapas loģiku
});

// starpniekprogrammu versija
Flight::route('/daža-ierobežota-lapa', function() {
	// regulārās lapas loģika
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('ir_iebūvēts')) {
		Flight::redirect('/ielogoties');
	}
});
```

## Lielāks piemērs

Šeit ir lielāks piemērs, kā jūs varētu to izmantot.

```php

izmantojiet Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// iestatiet pielāgotu ceļu līdz jūsu sesiju konfigurācijas failam un norādiet gadījuma rindas simbolu sesijas ID
$app->registret('sesija', Session::class, [ 'ceļš/uz/sesijas_konfigurācijas.php', bin2hex(nejauši_ģenerētas_baitu_virknes(32)) ], function(Session $sesija) {
		// vai arī manuāli pārrakstiet konfigurācijas opcijas
		$session->updateConfiguration([
			// ja vēlaties saglabāt sesijas datus datu bāzē (laba izvēle, ja vēlaties kaut ko līdzīgu kā "izlogot visus ierīces" funkciju)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mans-super-S3CR3T-salt'), // lūdzu, mainiet to uz kaut ko citu
			Session::CONFIG_AUTO_COMMIT   => true, // izpildiet tikai tad, ja tas ir nepieciešams un/vai ir grūti izpildīt jūsu sesijas komandu.
												   // papildus varat izpildīt Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datu bāzes draiveris PDO dns piemērs(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datu bāzes resurss
				'db_name'   => 'mana_lietotnes_datubāze',   # Datu bāzes nosaukums
				'db_table'  => 'sesijas',          # Datu bāzes tabula
				'db_user'   => 'root',              # Datu bāzes lietotājvārds
				'db_pass'   => '',                  # Datu bāzes parole
				'persistent_conn'=> false,          # Ietaupot resursus, izveidojot jaunu savienojumu katru reizi, kad skripts ir savienojums ar datu bāzi, rezultātā ātrāka tīmekļa lietojumprogramma. ATRAST JŪS PAŠI
			]
		]);
	}
);
```

## Palīdzība! Mana sesijas dati nav pastāvīgi!

Vai jūs iestatāt savus sesijas datus un tie nepastāv starp pieprasījumiem? Varbūt aizmirsi komitēt savus sesijas datus. To var izdarīt, zvanot `$sesija->commit()` pēc sesijas datu iestatīšanas.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// šeit veiciet savu ielogošanās loģiku
	// validējiet paroli, utt.

	// ja ieraksts veiksmīgs
	$session->set('ir_iebūvēts', true);
	$session->set('lietotājs', $lietotājs);

	// jebkurā laikā, kad ierakstāt sesijā, to jāiepilda apzināti.
	$session->commit();
});
```

Otrais veids, kā to izdarīt, ir, kad iestatāt savu sesiju servisu, jums jāiestata `auto_commit` uz `true` savā konfigurācijā. Tas automātiski komitēs jūsu sesijas datus pēc katra pieprasījuma.

```php

$app->registret('sesija', Session::class, [ 'ceļš/uz/sesijas_konfigurācijas.php', bin2hex(nejauši_ģenerētas_baitu_virknes(32)) ], function(Session $sesija) {
		$sesija->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Papildus tam jūs varat darīt `Flight::after('start', function() { Flight::session()->commit(); });` lai komitētu savus sesijas datus pēc katra pieprasījuma.

## Dokumentācija

Apmeklējiet [Github Readme](https://github.com/Ghostff/Session), lai iegūtu pilnu dokumentāciju. Konfigurācijas opcijas ir [labi dokumentētas default_config.php failā](https://github.com/Ghostff/Session/blob/master/src/default_config.php) patiess. Kods ir viegli saprotams, ja vēlaties pārskatīt šo pakotni pats.