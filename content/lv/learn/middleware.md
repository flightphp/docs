# Ceļa starpnieki

Flight atbalsta maršruta un grupas maršruta starpniekus. Starpnieks ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atzvanīšanas. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes savā kodā vai pārbaudīt, vai lietotājam ir atļauja piekļūt maršrutam.

## Pamata starpnieki

Šeit ir pamata piemērs:

```php
// Ja nodrošināt tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atzvanīšanas. 
// nav "pēc" starpnieka funkciju, izņemot klases (skatīt zemāk)
Flight::route('/ceļš', function() { echo 'Šeit esmu!'; })->addMiddleware(function() {
	echo 'Pirmkods starpnieki!';
});

Flight::start();

// Tas izvadīs "Pirmkods starpnieki! Šeit esmu!"
```

Ir dažas ļoti svarīgas piezīmes par starpniekiem, par kurām jums vajadzētu zināt, pirms tos izmantojat:
- Starpnieku funkcijas tiek izpildītas tādā secībā, kā tās tiek pievienotas maršrutam. Izpildes secība ir līdzīga tam, kā [Slim Framework apstrādā to](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirms tiek izpildīti tādā secībā, kā tiek pievienoti, un Pēc tiek izpildīti pretējā secībā.
- Ja jūsu starpnieka funkcija atgriež false, tiek apturēta visu izpilde, un tiek izraisīta 403 Aizliegts kļūda. Visticamāk, vēlēsities ar to rīkoties eleganti, izmantojot `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir nepieciešami maršruta parametri, tie tiks nodoti jūsu starpnieka funkcijai vienā masīvā. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tāds, ka jūs varat strukturēt savus parametrus grupās, un dažādās šo grupu daļās jūsu parametri faktiski var parādīties citā secībā, kas bojātu starpnieka funkciju, norādot nepareizo parametru. Šādā veidā varat piekļūt tiem pēc vārda, nevis pēc pozīcijas.

## Starpnieku klases

Starpnieku var reģistrēt arī kā klasi. Ja jums ir nepieciešama "pēc" funkcionalitāte, jums **jā** izmanto klase.

```php
class ManaStarpniekuKlase {
	public function before($params) {
		echo 'Pirmkods starpnieki!';
	}

	public function after($params) {
		echo 'Pēdējais starpnieks!';
	}
}

$ManaStarpniekuKlase = new ManaStarpniekuKlase();
Flight::route('/ceļš', function() { echo 'Šeit esmu! '; })->addMiddleware($ManaStarpniekuKlase); // arī ->addMiddleware([ $ManaStarpniekuKlase, $ManaStarpniekuKlase2 ]);

Flight::start();

// Tas rādīs "Pirmkods starpnieki! Šeit esmu! Pēdējais starpnieks!"
```

## Grupēšanas starpnieki

Jūs varat pievienot maršruta grupu, un tad katra maršruta grupā būs vienādi starpnieki arī. Tas ir noderīgi, ja jums ir nepieciešams grupēt daudz maršrutu, piemēram, pēc Autentifikācijas starpnieka, lai pārbaudītu API atslēgu galvenes daļā.

```php

// pievienots grupas metodes beigās
Flight::group('/api', function() {

	// Šis "tukšais" izskatās maršruts faktiski sakritīs ar /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotaji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotajs_skats');
}, [ new ApiAuthMiddleware() ]);
```

Ja vēlaties piemērot globālu starpnieku visiem saviem maršrutiem, varat pievienot "tukšu" grupu:

```php

// pievienots grupas metodes beigās
Flight::group('', function() {
	Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotaji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotajs_skats');
}, [ new ApiAuthMiddleware() ]);
```