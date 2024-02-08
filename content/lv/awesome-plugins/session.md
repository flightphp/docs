# Ghostff/Session

PHP sesiju pārvaldnieks (nepiemērots bloķēšanai, flash, segmenta, sesijas šifrēšanai). Izmanto PHP open_ssl sesijas datu opcionālai šifrēšanai/atšifrēšanai. Atbalsta Failu, MySQL, Redis un Memcached.

## Instalācija

Instalēt ar composer.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav nepieciešams nekas padot, lai izmantotu noklusējuma iestatījumus ar savu sesiju. Varat lasīt par vairāk iestatījumiem [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// viens lieta, ko atcerēties, ir tā, ka jums jāapsolībina savu sesiju katrā lapas ielādes laikā
// vai jums būs jāpalaiž auto_commit savā konfigurācijā.
```

## Vienkāršs piemērs

Šeit ir vienkāršs piemērs, kā jūs varētu to izmantot.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// veiciet savu ielogošanās loģiku šeit
	// pārbaudiet paroli, uc.

	// ja ielogošanās ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā laikā, kad rakstāt sesijā, jums jāapsolībina to apzināti.
	$session->commit();
});

// Šī pārbaude varētu būt ierobežotās lapas loģikā vai ietīta ar starpnieku.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// veiciet savu ierobežotās lapas loģiku šeit
});

// starpnieku versija
Flight::route('/some-restricted-page', function() {
	// regulāra lapas loģika
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Vēl sarežģītāks piemērs

Šeit ir vēl sarežģītāks piemērs, kā jūs varētu to izmantot.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// iestatiet pielāgotu ceļu uz jūsu sesijas konfigurācijas failu un piešķiriet tai nejaušu virkni sesijas id
$app->register('session', Session::class, [ 'ceļš/uz/sesijas_konfigurācijas.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// vai arī manuāli varat pārrakstīt konfigurācijas opcijas
		$session->updateConfiguration([
			// ja vēlaties glabāt sesijas datus datubāzē (labi, ja vēlaties kaut ko tādu kā "izlogot mani no visiem ierīces" funkciju)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mans-super-S3CR3T-solis'), // lūdzu, izmainiet to uz kaut ko citu
			Session::CONFIG_AUTO_COMMIT   => true, // dariet to tikai tad, ja tas ir nepieciešams un/vai ir grūti apņemt() savu sesiju.
												// papildus varētu darīt Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datubāzes draiveris priekš PDO dns piemērs (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datubāzes resursdators
				'db_name'   => 'mana_lietotne_datubāze',   # Datubāzes nosaukums
				'db_table'  => 'sesijas',          # Datubāzes tabula
				'db_user'   => 'root',              # Datubāzes lietotājvārds
				'db_pass'   => '',                  # Datubāzes parole
				'persistent_conn'=> false,          # Izmantojiet pastāvīgu savienojumu, lai izvairītos no jauna savienojuma izmaksām katru reizi, kad skripts ir jārunā ar datubāzi, rezultātā ātrākas tīmekļa lietojumprogrammas. MEKLĒJAT AIZMUGURI PAŠI
			]
		]);
	}
);
```

## Dokumentācija

Apmeklēt [Github Readme](https://github.com/Ghostff/Session) pilnu dokumentāciju. Konfigurācijas opcijas ir [labi dokumentētas default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) failā pati. Kods ir viegli saprotams, ja jūs vēlaties paši izpētīt šo paketi.