# Ghostff/Session

PHP sesiju pārvaldnieks (nelīdzbloķējošs, flash, segmenta, sesiju šifrēšana). Izmanto PHP open_ssl iespējamai sesiju datu šifrēšanai/atšifrēšanai. Atbalsta Failu, MySQL, Redis un Memcached.

## Instalācija

Instalē ar komponistu.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav nepieciešams padot neko, lai izmantotu noklusējuma iestatījumus ar savu sesiju. Lai uzzinātu vairāk par iestatījumiem, izlasiet [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// viena lieta, ko atcerēties, ir tāda, ka jums ir jāapstiprina sava sesija katrā lapas ielādēšanā
// vai jums būs nepieciešams darbināt auto_commit savā konfigurācijā.
```

## Vienkāršs piemērs

Šeit ir vienkāršs piemērs, kā jūs varētu izmantot to.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// veiciet savu pieteikšanās loģiku šeit
	// validējiet paroli, utt.

	// ja pieteikšanās ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā brīdī, kad rakstāt sesijā, jums to ir apzināti jāapstiprina.
	$session->commit();
});

// Šis pārbaude varētu būt ierobežotas lapas loģikā vai apvilktas ar starpniekprogrammu.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// veiciet savu ierobežoto lapas loģiku šeit
});

// vidējais versija
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

Šeit ir vēl sarežģītāks piemērs, kā jūs varētu izmantot to.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// iestatiet pielāgotu ceļu uz sesiju konfigurācijas failu un piešķiriet tam gadījuma virkni sesijas identifikatoram
$app->register('session', Session::class, [ 'ceļš/uz/sesiju_konfigurācijas.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// vai arī manuāli varat pārrakstīt konfigurācijas opcijas
		$session->updateConfiguration([
			// ja vēlaties saglabāt savus sesiju datus datu bāzē (labi, ja vēlaties kaut ko līdzīgu kā, "izlogojiet mani no visiem ierīcēm" funkciju)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mans-īpaši-S3CR3T-soli'), // lūdzu, nomainiet to uz kaut ko citu
			Session::CONFIG_AUTO_COMMIT   => true, // to darīt tikai tad, ja tas ir nepieciešams un/vai ir grūti apstiprināt() jūsu sesiju.
												// papildus jūs varētu veikt Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datu bāzes draiveris priekš PDO dns, piem. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datu bāzes resursdators
				'db_name'   => 'mana_lietotne_datubāze',   # Datu bāzes nosaukums
				'db_table'  => 'sesijas',          # Datu bāzes tabula
				'db_user'   => 'root',              # Datu bāzes lietotājvārds
				'db_pass'   => '',                  # Datu bāzes parole
				'persistent_conn'=> false,          # Izmantojiet pastāvīgu savienojumu, lai ietaupītu laiku un palielinātu datubāzes operāciju ātrumu. ATIETĪGU PAKĀPIENU AUZENES PAŠI
			]
		]);
	}
);
```

## Dokumentācija

Apmeklējiet [Github Readme](https://github.com/Ghostff/Session), lai iegūtu pilnu dokumentāciju. Konfigurācijas opcijas ir [labi dokumentētas faila default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) pašā. Ja vēlētos izpētīt šo pakotni patstāvīgi, kods ir vienkārši saprotams.
```