# Sīkfaili

[overclokk/cookie](https://github.com/overclokk/cookie) ir vienkārša bibliotēka sīkfailu pārvaldībai jūsu lietotnē.

## Instalēšana

Instalēšana ir vienkārša ar komponistu.

```bash
composer require overclokk/cookie
```

## Izmantos̄ana

Lietošana ir tik vienkārša kā jaunas metodes reģistrēšana Flight klases.

```php
use Overclokk\Cookie\Cookie;

/*
 * Iestatiet savā palaišanas vai public/index.php failā
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// Iestatiet sīkfailu

		// jums vajadzētu, lai tas būtu false, tādēļ, lai jūs saņemtu jaunu instanci
		// izmantojiet zemāk esošo komentāru, ja vēlaties autovērstjanu
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // sīkfaila nosaukums
			'1', // vērtība, kuru vēlaties iestatīt
			86400, // cik sekundes sīkfailam būtu jāpastāv
			'/', // ceļš, kurā būs pieejams sīkfails
			'example.com', // domēns, kurā būs pieejams sīkfails
			true, // sīkfails tiks pārraidīts tikai pār šifrētu HTTPS savienojumu
			true // sīkfails būs pieejams tikai caur HTTP protokolu
		);

		// pēc izvēles, ja vēlaties saglabāt noklusējuma vērtības
		// un ātri iestatīt sīkfailu ilgu laiku
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// Pārbaudiet, vai jums ir sīkfails
		if (Flight::cookie()->has('stay_logged_in')) {
			// ielieciet tos piemēram, informācijas panelī.
			Flight::redirect('/dashboard');
		}
	}
}