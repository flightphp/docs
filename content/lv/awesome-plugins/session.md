# Ghostff/Session

PHP sesiju pārvaldnieks (nenovēršams, flash, segmēts, sesijas šifrēšana). Izmanto PHP open_ssl sesiju datu pēcējai šifrēšanai/atsifrēšanai. Atbalsta Failu, MySQL, Redis un Memcached.

## Instalācija

Instalējiet ar komponistu.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav nepieciešams nekas padot, lai izmantotu noklusējuma iestatījumus ar jūsu sesiju. Jūs varat lasīt par vairākiem iestatījumiem [Github Readme](https://github.com/Ghostff/Session).

```php

izmantojiet Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// viena lieta, ko atcerēties, ir tā, ka jums jāpieņem jūsu sesija katrā lapas ielādē, vai arī jums būs jāpalaiž auto_commit jūsu konfigurācijā
```

## Vienkāršs piemērs

Šeit ir vienkāršs piemērs, kā jūs varētu to izmantot.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// izpildiet ienākšanas loģiku šeit
	// validējiet paroli, utt.

	// ja ienākšana ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā laikā, kad rakstāt sesijā, jums tas jāpieņem apzināti
	$session->commit();
});

// Šis pārbaude varētu būt ierobežotās lapas loģikā vai iesaiņots ar starpniekprogrammu
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// izpildiet savu ierobežoto lapas loģiku šeit
});

// starpniekprogrammas versija
Flight::route('/some-restricted-page', function() {
	// regulāra lappuses loģika
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Vairāk sarežģīts piemērs

Šeit ir vairāk sarežģīts piemērs, kā jūs varētu to izmantot.

```php

izmantojiet Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// iestatiet pielāgotu ceļu uz jūsu sesijas konfigurācijas failu un piešķiriet tai gadījuma virkni sesijas ID
$app->register('session', Session::class, [ 'ceļš/līdz/sesijas_konfigurācijas.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// vai arī varat manuāli pārrakstīt konfigurācijas opcijas
		$session->updateConfiguration([
			// ja vēlaties saglabāt savus sesijas datus datu bāzē (labi, ja vēlaties kaut ko līdzīgu kā "izlogot mani no visiem ierīcēm" funkcionalitāti
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mans-super-S3CR3T-salt'), // lūdzu, nomainiet šo uz ko citu
			Session::CONFIG_AUTO_COMMIT   => true, // dariet to tikai tad, ja tas ir nepieciešams un/vai ir grūti commit() jūsu sesiju
												// papildus jūs varētu izdarīt Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datu bāzes draiveris priekš PDO dns, piem (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datu bāzes resursdators
				'db_name'   => 'manas_lietotnes_datubāze',   # Datu bāzes nosaukums
				'db_tabula'  => 'sesijas',          # Datu bāzes tabula
				'db_lietotājs'   => 'root',              # Datu bāzes lietotājvārds
				'db_parole'   => '',                  # Datu bāzes parole
				'pastāvīgs savienojums'=> false,          # Izvairieties no jauna savienojuma izveides izmaksu katrai reizei, kad skripts vēlas runāt ar datu bāzi, rezultātā ātrāka tīmekļa lietotne. ATRADĪSIET AIZMUGURĒJIENU PATSTĀVĪGI
			]
		]);
	}
);
```

## Dokumentācija

Apmeklējiet [Github Readme](https://github.com/Ghostff/Session) pilnai dokumentācijai. Konfigurācijas opcijas ir [labi dokumentētas default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) failā pašā. Kods ir vienkārši izprotams, ja jums būtu vēlme pārskatīt šo paketi paši.