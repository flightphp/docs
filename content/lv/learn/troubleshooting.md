# Problemløgšana

Šī lapa palīdzēs jums novērst vispārējas problēmas, ar kurām jūs varat saskarties, izmantojot Flight.

## Parastās problēmas

### 404 Netika Atrasts vai Negaidīta Maršruta Uzvedība

Ja jums tiek rādīta 404 Netika Atrasta kļūda (bet jūs zvērās par savu dzīvi, ka tā patiešām tur ir un tas nav tikai kļūda rakstīšanā), tas varētu būt problēma jums atgriežot vērtību savā maršruta gala punktā, nevis vienkārši to echojot. Šī iemesla dēļ tam ir nodoma, bet dažiem izstrādātājiem tas var nejauši notikt.

```php
 
Flight::route('/hello', function(){
	// Tas var izraisīt 404 Netika Atrasta kļūdu
	return 'Sveika, pasaule!';
});

// To, iespējams, jūs vēlaties
Flight::route('/hello', function(){
	echo 'Sveika, pasaule!';
});
```

Iemesls tam ir īpaša mehānisms, kas iebūvēts maršrutētājā, kas apstrādā atgriezto izvadi kā vienu "pāreju uz nākamo maršrutu". Jūs varat redzēt šādu uzvedību dokumentācijā sadaļā [Maršrutēšana](/learn/routing#passing).

### Klase Netika Atrasta (automašīnas ielāde nedarbojas)

Šai problēmai var būt daži iemesli. Zemāk ir daži piemēri, bet pārliecinieties, ka jūs arī apskatāt sadaļu [automašīna](/learn/autoloading).

#### Nepareizs Faila Nosaukums
Visbiežākais ir tas, ka klases nosaukums nesakrīt ar faila nosaukumu.

Ja jums ir klase ar nosaukumu `ManāKlase`, tad failam vajadzētu būt nosauktam `ManāKlase.php`. Ja jums ir klase ar nosaukumu `ManāKlase` un fails ir nosaukts `manāklase.php`, 
tad automātisks ielādētājs to nespēs atrast.

#### Nepareizs Vardraba
Ja jūs izmantojat vardrabas, tad vardraba atbilst šaurmaiņa struktūrai.

```php
// kods

// ja jūsu ManaisKontrolieris ir app/kontrolieri direktorijā un tas ir vardrabā
// tas nestrādās.
Flight::route('/hello', 'ManaisKontrolieris->sveiki');

// jums būs jāizvēlas viens no šiem variantiem
Flight::route('/hello', 'app\kontrolieri\ManaisKontrolieris->sveiki');
// vai ja jums ir lietošanas paziņojums augšā

lieto app\kontrolieri\ManaisKontrolieris;

Flight::route('/hello', [ ManaisKontrolieris::class, 'sveiki' ]);
// var būt arī rakstīts
Flight::route('/hello', ManaisKontrolieris::class.'->sveiki');
// arī...
Flight::route('/hello', [ 'app\kontrolieri\ManaisKontrolieris', 'sveiki' ]);
```

#### `path()` nav definēta

Skeleta lietotnē, tas ir definēts `config.php` failā, bet, lai jūsu klases tiktu atrastas, jums jāpārliecinās, ka `path()` metode ir definēta 
(probablyto saka visu jūsu direktorijas saknes) pirms jūs mēģināt to izmantot.

```php
 
// Pievienojiet ceļu autoloaderim
Flight::path(__DIR__.'/../');
```