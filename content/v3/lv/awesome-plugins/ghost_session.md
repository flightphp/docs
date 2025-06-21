# Ghostff/Session

PHP Sesijas pārvaldnieks (nebloķējošs, zibspuldze, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai pēc izvēles šifrētu/dešifrētu sesijas datus. Atbalsta File, MySQL, Redis un Memcached.

Noklikšķiniet [here](https://github.com/Ghostff/Session), lai apskatītu kodu.

## Instalācija

Instalējiet ar composer.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav nepieciešams neko nodot, lai izmantotu noklusējuma iestatījumus ar savu sesiju. Vairāk par iestatījumiem varat lasīt [Github Readme](https://github.com/Ghostff/Session).

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// viena lieta, ko atcerēties, ir tā, ka jums ir jāapstiprina sava sesija katrā lapas ielādē
// vai jums būs nepieciešams izpildīt auto_commit savā konfigurācijā. 
```

## Vienkāršs piemērs

Šeit ir vienkāršs piemērs, kā jūs varētu to izmantot.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// veiciet savu pieteikšanās loģiku šeit
	// validējiet paroli utt.

	// ja pieteikšanās ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā laikā, kad jūs rakstāt uz sesiju, jums tas jāapstiprina apzināti.
	$session->commit();
});

// Šo pārbaudi varētu veikt ierobežotās lapas loģikā vai ietīt ar vidējo programmu.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// veiciet savu ierobežotās lapas loģiku šeit
});

// vidējās programmas versija
Flight::route('/some-restricted-page', function() {
	// regulāra lapas loģika
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Sarežģītāks piemērs

Šeit ir sarežģītāks piemērs, kā jūs varētu to izmantot.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// iestatiet pielāgotu ceļu uz savu sesijas konfigurācijas failu kā pirmo argumentu
// vai dodiet tam pielāgotu masīvu
$app->register('session', Session::class, [ 
	[
		// ja vēlaties glabāt savu sesijas datus datu bāzē (labi, ja vēlaties kaut ko tādu kā "izrakstīties no visām ierīcēm" funkcionalitāti)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // lūdzu, mainiet to uz kaut ko citu
		Session::CONFIG_AUTO_COMMIT   => true, // dariet to tikai tad, ja tas ir nepieciešams un/vai grūti izpildīt commit() savu sesiju.
												// turklāt jūs varētu izdarīt Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Datu bāzes draiveris PDO dns, piem. (mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Datu bāzes hosts
			'db_name'   => 'my_app_database',   # Datu bāzes nosaukums
			'db_table'  => 'sessions',          # Datu bāzes tabula
			'db_user'   => 'root',              # Datu bāzes lietotājvārds
			'db_pass'   => '',                  # Datu bāzes parole
			'persistent_conn'=> false,          # Izvairieties no jaunas savienojuma izveidošanas katru reizi, kad skriptam jārunā ar datu bāzi, kas padara tīmekļa lietojumprogrammu ātrāku. ATRASTIET SEVI PAŠI
		]
	] 
]);
```

## Palīdzība! Mana sesijas dati netiek saglabāti!

Vai jūs iestatāt savus sesijas datus un tie netiek saglabāti starp pieprasījumiem? Jūs, iespējams, esat aizmirsis apstiprināt savus sesijas datus. Jūs varat to izdarīt, izsaucot `$session->commit()` pēc tam, kad esat iestatījis savus sesijas datus.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// veiciet savu pieteikšanās loģiku šeit
	// validējiet paroli utt.

	// ja pieteikšanās ir veiksmīga
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// jebkurā laikā, kad jūs rakstāt uz sesiju, jums tas jāapstiprina apzināti.
	$session->commit();
});
```

Cits veids, kā to apiet, ir tad, kad jūs iestatāt savu sesijas servisu, jums ir jāiestata `auto_commit` uz `true` savā konfigurācijā. Tas automātiski apstiprinās jūsu sesijas datus pēc katra pieprasījuma.

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

Turklāt jūs varētu izdarīt `Flight::after('start', function() { Flight::session()->commit(); });`, lai apstiprinātu savus sesijas datus pēc katra pieprasījuma.

## Dokumentācija

Apmeklējiet [Github Readme](https://github.com/Ghostff/Session) pilnai dokumentācijai. Konfigurācijas opcijas ir [labi dokumentētas noklusējuma_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) failā pašā. Kods ir vienkārši saprotams, ja jūs gribētu to izpētīt pats.