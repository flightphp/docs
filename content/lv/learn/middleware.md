# Ceļa starpnieki

Filma atbalsta ceļa un grupas ceļa starpniekus. Starpnieks ir funkcija, kas tiek izpildīta pirms (vai pēc) ceļa atzvana. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes savā kodā vai pārbaudīt, vai lietotājam ir atļauja piekļūt šim ceļam.

## Pamata vidējais

Šeit ir pamata piemērs:

```php
// Ja norādāt tikai anonīmu funkciju, tā tiks izpildīta pirms ceļa atzvana. 
// nav "pēc" starpnieku funkciju, izņemot klases (skatīt zemāk)
Flight::route('/ceļš', function() { echo 'Šeit es esmu!'; })->addMiddleware(function() {
	echo 'Starpposms pirmais!';
});

Flight::start();

// Tas izvadīs "Starpposms pirmais! Šeit es esmu!"
```

Ir daži ļoti svarīgi punkti par vidējiem, par kuriem jums jāzina, pirms tos lietojat:
- Starpnieku funkcijas tiek izpildītas tā, kā tās tiek pievienotas ceļam. Izpildīšana ir līdzīga tam, kā [Slim Framework apstrādā to](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Priekšpēdējie tiek izpildīti kā pievienotajā secībā, un Pēcpusējie tiek izpildīti pretējā secībā.
- Ja jūsu starpnieku funkcija atgriežs false, visa izpilde tiek apturēta un tiek izraisīta 403 Aizliegts kļūda. Jums, visticamāk, būs labi šo apstrādāt eleganti ar `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir nepieciešami parametri no jūsu ceļa, tie tiks padoti vienā masīvā jūsu starpnieku funkcijai. (`function($params) { ... }` or `public function before($params) {}`). Iemesls tam ir tas, ka jūs varat strukturēt savus parametrus grupās, un dažādās šajās grupās jūsu parametri var patiešām parādīties citā secībā, kas pārkāps starpnieku funkciju, vēršoties pie nepareiza parametra. Šādā veidā jūs varat piekļūt tiem pēc nosaukuma, nevis pēc pozīcijas.

## Starpnieku klases

Starpniekus var reģistrēt arī kā klasi. Ja jums nepieciešama "pēc" funkcionalitāte, jums **jā** izmanto klase.

```php
class ManaVidējā {
	public function before($params) {
		echo 'Starpposms pirmais!';
	}

	public function after($params) {
		echo 'Starpposms pēdējais!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/ceļš', function() { echo 'Šeit es esmu! '; })->addMiddleware($MyMiddleware); // arī ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Šis parādīs "Starpposms pirmais! Šeit es esmu! Starpposms pēdējais!"
```

## Grupveida starpnieki

Jūs varat pievienot ceļa grupu, un tad katram ceļam šajā grupā būs vienādi starpnieki. Tas ir noderīgi, ja ir nepieciešams grupēt daudzus ceļus ar, piemēram, Autentifikācijas starpnieku, lai pārbaudītu API atslēgu galvenē.

```php

// pievienots grupas metodes beigās
Flight::group('/api', function() {

	// Šis "tukšais" izskatās tips parastsceļš patiesībā sakrīt /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotāji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotājs_skatīt');
}, [ jauns ApiAuthMiddleware() ]);
```

Ja vēlaties piemērot globālu vidējo visiem savām ceļiem, varat pievienot "tukšu" grupu:

```php

// pievienots grupas metodes beigās
Flight::group('', function() {
	Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotāji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotājs_skatīt');
}, [ jauns ApiAuthMiddleware() ]);
```