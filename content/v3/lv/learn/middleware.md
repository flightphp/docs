# Maršruta starpprogrammatūra

Flight atbalsta maršruta un grupas maršruta starpprogrammatūru. Starpprogrammatūra ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atsaukuma. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes jūsu kodā vai lai pārbaudītu, vai lietotājam ir atļauja piekļūt maršrutam.

## Pamata starpprogrammatūra

Šeit ir pamata piemērs:

```php
// Ja jūs norādāt tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atsaukuma. 
// tur nav "pēc" starpprogrammatūras funkciju, izņemot klases (skatiet zemāk)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Tas izvadīs "Middleware first! Here I am!"
```

Ir daži ļoti svarīgi piezīmes par starpprogrammatūru, kas jums jāzina, pirms to izmantojat:
- Starpprogrammatūras funkcijas tiek izpildītas secībā, kādā tās ir pievienotas maršrutam. Izpilde ir līdzīga tam, kā [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirms funkcijas tiek izpildītas pievienošanas secībā, un Pēc funkcijas tiek izpildītas apgrieztā secībā.
- Ja jūsu starpprogrammatūras funkcija atgriež false, visa izpilde tiek pārtraukta un tiek izmetīta 403 Aizliegta kļūda. Jūs, iespējams, vēlaties to apstrādāt graciozāk ar `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir nepieciešami parametri no jūsu maršruta, tie tiks nodoti kā vienots masīvs jūsu starpprogrammatūras funkcijai. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tas, ka jūs varat strukturēt savus parametrus grupās un dažās no šīm grupām jūsu parametri var parādīties citā secībā, kas izjauktu starpprogrammatūras funkciju, atsaucoties uz nepareizo parametru. Šādā veidā jūs tos varat piekļūt pēc nosaukuma, nevis pēc pozīcijas.
- Ja jūs norādāt tikai starpprogrammatūras nosaukumu, tā automātiski tiks izpildīta, izmantojot [dependency injection container](dependency-injection-container), un starpprogrammatūra tiks izpildīta ar parametriem, kas tai nepieciešami. Ja jums nav reģistrēts dependency injection container, tiks nodots `flight\Engine` instances uz `__construct()`.

## Starpprogrammatūras klases

Starpprogrammatūru var reģistrēt arī kā klasi. Ja jums ir nepieciešama "pēc" funkcionalitāte, jums **jāizmanto** klase.

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

Pieņemsim, ka jums ir autentifikācijas starpprogrammatūra un jūs vēlaties novirzīt lietotāju uz pieteikšanās lapu, ja viņš nav autentificēts. Jums ir dažas iespējas, kas pieejamas:

1. Jūs varat atgriezt false no starpprogrammatūras funkcijas un Flight automātiski atgriezīs 403 Aizliegta kļūdu, bet bez pielāgošanas.
1. Jūs varat novirzīt lietotāju uz pieteikšanās lapu, izmantojot `Flight::redirect()`.
1. Jūs varat izveidot pielāgotu kļūdu starpprogrammatūrā un pārtraukt maršruta izpildi.

### Pamata piemērs

Šeit ir vienkāršs return false; piemērs:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// jo tas ir true, viss turpina
	}
}
```

### Novirzīšanas piemērs

Šeit ir piemērs, kā novirzīt lietotāju uz pieteikšanās lapu:
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

### Pielāgotas kļūdas piemērs

Pieņemsim, ka jums ir nepieciešams izmetīt JSON kļūdu, jo jūs veidojat API. Jūs varat to izdarīt šādi:
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

## Grupēšanas starpprogrammatūra

Jūs varat pievienot maršruta grupu, un tad katrs maršruts šajā grupā būs ar to pašu starpprogrammatūru. Tas ir noderīgi, ja jums ir nepieciešams grupēt vairākus maršrutus, piemēram, ar Auth starpprogrammatūru, lai pārbaudītu API atslēgu virsrakstā.

```php

// pievienots grupas metodes beigās
Flight::group('/api', function() {

	// Šis "tukšais" maršruts faktiski atbilst /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Šis atbilst /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Šis atbilst /api/users/1234
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