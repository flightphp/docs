# Ghostff/Session

PHP sesijas pārvaldnieks (nebloķējošs, mirkļa, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai opcijas šifrētu/atšifrētu sesijas datus. Atbalsta failus, MySQL, Redis un Memcached.

Noklikšķiniet [šeit](https://github.com/Ghostff/Session), lai apskatītu kodu.

## Instalācija

Instalējiet ar composer.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav nepieciešams nodot neko, lai izmantotu noklusējuma iestatījumus ar savu sesiju. Jūs varat izlasīt par citiem iestatījumiem [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// viena lieta, kas jāatceras, ir tā, ka jums ir jāpievieno sava sesija katru reizi, kad ielādējat lapu
// vai jums būs jāizpilda auto_commit savā konfigurācijā.
```

## Vienkāršs piemērs

Šeit ir vienkāršs piemērs, kā jūs varētu to lietot.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// šeit paveiciet savu pieteikšanās loģiku
	// validējiet paroli u.c.

	// ja pieteikšanās ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā reizē, kad rakstāt uz sesiju, jums jāpievieno tas apzināti.
	$session->commit();
});

// Šī pārbaude var būt ierobežotajā lapas loģikā vai iesaiņota ar starpprogrammu.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// šeit paveiciet savu ierobežotās lapas loģiku
});

// starpprogrammas versija
Flight::route('/some-restricted-page', function() {
	// parastā lapas loģika
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Vairāk sarežģīts piemērs

Šeit ir sarežģītāks piemērs, kā jūs varētu to lietot.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// norādiet pielāgotu ceļu uz savu sesijas konfigurācijas failu un piešķiriet tam nejaušu virkni sesijas ID
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// vai jūs varat manuāli pārrakstīt konfigurācijas opcijas
		$session->updateConfiguration([
			// ja vēlaties glabāt sesijas datus datubāzē (labs, ja vēlaties kaut ko līdzīgu, "atslēgt mani visās ierīcēs" funkcionalitāte)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // lūdzu, mainiet to uz kaut ko citu
			Session::CONFIG_AUTO_COMMIT   => true, // dariet to tikai, ja tas ir nepieciešams un/vai ir grūti pievienot() jūsu sesiju.
												   // papildus varat veikt Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Datubāzes vadītājs PDO dns piem. (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Datubāzes host
				'db_name'   => 'my_app_database',   # Datubāzes nosaukums
				'db_table'  => 'sessions',          # Datubāzes tabula
				'db_user'   => 'root',              # Datubāzes lietotājvārds
				'db_pass'   => '',                  # Datubāzes parole
				'persistent_conn'=> false,          # Izvairieties no jaunas savienojuma izveides katru reizi, kad skripts vēlas runāt ar datubāzi, kas nodrošinās ātrāku tīmekļa lietojumprogrammu. ATRAST GRŪTI SEV
			]
		]);
	}
);
```

## Palīdzība! Mana sesijas dati netiek saglabāti!

Vai jūs iestatāt savus sesijas datus un tie netiek saglabāti starp pieprasījumiem? Iespējams, ka esat aizmirsis pievienot savus sesijas datus. Jūs varat to izdarīt, izsaucot `$session->commit()`, kad esat iestatījis savus sesijas datus.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// šeit paveiciet savu pieteikšanās loģiku
	// validējiet paroli u.c.

	// ja pieteikšanās ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā reizē, kad rakstāt uz sesiju, jums jāpievieno tas apzināti.
	$session->commit();
});
```

Otra opcija, kā to izdarīt, ir tad, kad jūs iestatāt savu sesijas pakalpojumu, jums jānorāda `auto_commit` kā `true` savā konfigurācijā. Tas automātiski pievienos jūsu sesijas datus pēc katra pieprasījuma.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Turklāt jūs varat veikt `Flight::after('start', function() { Flight::session()->commit(); });`, lai pievienotu savus sesijas datus pēc katra pieprasījuma.

## Dokumentācija

Apmeklējiet [Github Readme](https://github.com/Ghostff/Session), lai iegūtu pilnu dokumentāciju. Konfigurācijas opcijas ir [labs dokumentēts default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) failā. Kods ir vienkāršs saprast, ja vēlaties pats izpētīt šo pakotni.