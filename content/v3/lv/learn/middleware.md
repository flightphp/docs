# Maršrutu starpprogrammatūra

Flight atbalsta maršrutu un grupu maršrutu starpprogrammatūru. Starpprogrammatūra ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atsaukuma. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes jūsu kodā vai pārbaudīt, vai lietotājam ir atļauja piekļūt maršrutam.

## Pamata starpprogrammatūra

Lūk, pamata piemērs:

```php
// Ja jūs piegādājat tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atsaukuma. 
// tur nav "pēc" starpprogrammatūras funkciju, izņemot klases (skat. zemāk)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Tas izdrukās "Middleware first! Here I am!"
```

Ir daži ļoti svarīgi piezīmes par starpprogrammatūru, kuras jums vajadzētu zināt, pirms to izmantojat:
- Starpprogrammatūras funkcijas tiek izpildītas secībā, kādā tās tiek pievienotas maršrutam. Izpilde ir līdzīga tam, kā [Slim Framework to apstrādā](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirms tiek izpildīti secībā, kā pievienoti, un Pēc tiek izpildīti reversā secībā.
- Ja jūsu starpprogrammatūras funkcija atgriež false, visa izpilde tiek pārtraukta un tiek izmetīta 403 Aizliegts kļūda. Jūs, iespējams, vēlaties to apstrādāt graciozāk ar `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir vajadzīgi parametri no jūsu maršruta, tie tiks nodoti kā viena masīva jūsu starpprogrammatūras funkcijai. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tas, ka jūs varat strukturēt savus parametrus grupās un dažās no šīm grupām jūsu parametri var parādīties citā secībā, kas izjauktu starpprogrammatūras funkciju, atsaucoties uz nepareizo parametru. Tādējādi jūs tos varat piekļūt pēc nosaukuma, nevis pozīcijas.
- Ja jūs pievienojat tikai starpprogrammatūras nosaukumu, tā automātiski tiks izpildīta, izmantojot [dependency injection container](dependency-injection-container), un starpprogrammatūra tiks izpildīta ar parametriem, kas tai vajadzīgi. Ja jums nav reģistrēta dependency injection container, tiks nodots `flight\Engine` instances uz `__construct()`.

## Starpprogrammatūras klases

Starpprogrammatūra var tikt reģistrēta arī kā klase. Ja jums ir vajadzīga "pēc" funkcionalitāte, jums **jāizmanto** klase.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // arī ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Tas parādīs "Middleware first! Here I am! Middleware last!"
```

## Starpprogrammatūras kļūdu apstrāde

Pieņemsim, ka jums ir autentifikācijas starpprogrammatūra un jūs vēlaties pāradresēt lietotāju uz pieteikšanās lapu, ja viņš nav autentificēts. Jums ir dažas opcijas pieejamas:

1. Jūs varat atgriezt false no starpprogrammatūras funkcijas un Flight automātiski atgriezīs 403 Aizliegts kļūdu, bet bez pielāgošanas.
1. Jūs varat pāradresēt lietotāju uz pieteikšanās lapu, izmantojot `Flight::redirect()`.
1. Jūs varat izveidot pielāgotu kļūdu starpprogrammatūrā un pārtraukt maršruta izpildi.

### Pamata piemērs

Lūk, vienkāršs return false; piemērs:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// jo tas ir true, viss turpina darboties
	}
}
```

### Pāradresēšanas piemērs

Lūk, piemērs, kā pāradresēt lietotāju uz pieteikšanās lapu:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Pielāgota kļūda piemērs

Pieņemsim, ka jums ir jāizmet JSON kļūda, jo jūs veidojat API. Jūs to varat izdarīt šādi:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// vai
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// vai
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Grupēšana ar starpprogrammatūru

Jūs varat pievienot maršruta grupu, un tad katrs maršruts šajā grupā būs ar to pašu starpprogrammatūru. Tas ir noderīgi, ja jums ir jāgrupē vairāki maršruti, piemēram, ar Auth starpprogrammatūru, lai pārbaudītu API atslēgu galvenē.

```php

// pievienots grupas metodes beigās
Flight::group('/api', function() {

	// Šis "tukšais" maršruts faktiski atbilst /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Šis atbildīs /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Šis atbildīs /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Ja jūs vēlaties piemērot globālu starpprogrammatūru visiem jūsu maršrutiem, jūs varat pievienot "tukšu" grupu:

```php

// pievienots grupas metodes beigās
Flight::group('', function() {

	// Tas joprojām ir /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Un tas joprojām ir /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // vai [ new ApiAuthMiddleware() ], tas pats
```